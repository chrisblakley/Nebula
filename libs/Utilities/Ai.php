<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Ai') ){
	trait Ai {
		public function hooks(){
			add_action('wp_ajax_nebula_openai_prompt', array($this, 'openai_prompt'));
			add_action('wp_ajax_nopriv_nebula_openai_prompt', array($this, 'openai_prompt'));
		}

		//Get the preferred AI Name from the individual user's setting (not global Nebula Options)
		public function get_preferred_ai(){
			$preferred_ai_name = $this->get_user_info('preferred_ai'); //Get the user's setting

			if ( isset($preferred_ai_name) ){
				return $preferred_ai_name;
			}

			return 'ChatGPT'; //Default to ChatGPT if user preference is not set
		}

		//Get the preferred AI URL from the individual user's setting (not global Nebula Options)
		public function get_preferred_ai_url(){
			$preferred_ai_name = strtolower($this->get_preferred_ai()); //Get the user's setting

			$ai_platforms = array(
				'chatgpt' => 'https://chatgpt.com/',
				'gemini' => 'https://gemini.google.com/app',
				'claude' => 'https://claude.ai/new'
			);

			if ( isset($ai_platforms[$preferred_ai_name]) ){
				return $ai_platforms[$preferred_ai_name];
			}

			return $ai_platforms['chatgpt']; //Default to ChatGPT if user preference is not set
		}

		public function openai_ajax_prompt(){
			if ( empty($this->get_option('openai_api_key')) ){
				return false;
			}

			//Estimate the token usage and consider a limit
			//Run the prompt
			//echo the result
		}

		//Estimate the number of tokens needed to process a prompt
		public function openai_estimate_tokens($prompt){
			$prompt = trim($prompt); // Remove leading/trailing whitespace
			$char_count = strlen($prompt);

			//Base estimate: 1 token ≈ 4 characters, adjusted with better heuristic
			$estimated_tokens = ceil($char_count/4);

			//Add a small fixed overhead to account for chat message structure, roles, etc.
			$estimated_tokens += 4; //Roughly accounts for {"role": "user", "content": ...} structure

			//Clamp to minimum of 1 token (some endpoints reject 0)
			return max(1, $estimated_tokens);
		}

		//Prompt the OpenAI API
		private function openai_prompt($parameters){
			if ( !$this->get_option('ai_features') || !$this->get_option('openai_api_key') ){
				return false;
			}

			$user_prompt = $parameters['prompt'] ?? null; //This parameter is required

			//If only a string is passed, use that as the prompt and default parameters for everything else
			if ( is_string($parameters) ){
				$user_prompt = $parameters;
			}

			if ( empty($user_prompt) ){
				return false;
			}

			//OpenAI Model Comparison: https://platform.openai.com/docs/models
			$model = $parameters['model'] ?? 'gpt-4.1-mini';
			//Examples:
				//'gpt-3.5-turbo' (cheap, lower quality. i would avoid it as it is unhelpful.)
				//'gpt-4' (super expensive. avoid)
				//'gpt-4o' (somewhat expensive)
				//'gpt-4o-mini' (smarter than 3.5-turbo but still inexpensive)
				//'gpt-4.1-mini' (i like the idea of this one, but haven't gotten it to work yet due to timeouts)
				//'gpt-4.1-nano' (dont love this one)
				//'o4-mini-2025-04-16' is better for code review. More expensive than 'gpt-4.1-mini'
				//'gpt-5-mini-2025-08-07'

			$system_prompt = $parameters['system_prompt'] ?? '';

			$data = array(
				'model' => $model,
				'messages' => array(
					array(
						'role' => 'system',
						'content' => $system_prompt
					),
					array(
						'role' => 'user',
						'content' => $user_prompt,
					),
				)
			);

			$temperature = $parameters['temperature'] ?? null; //Remember: not all models accept temperatures. The ones that don't will fail if a temperature is provided!

			if ( !empty($temperature) ){
				$data['temperature'] = $temperature; //Lower number is more deterministic
			}

			if ( is_user_logged_in() ){
				$data['user'] = 'wp_user_' . get_current_user_id();
			}

			$response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->get_option('openai_api_key'),
				),
				'body'    => json_encode($data),
				'timeout' => 120,
			));

			if ( is_wp_error($response) ){
				return 'Request failed: ' . $response->get_error_message();
			}

			$response_code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);

			if ( $response_code !== 200 ){
				return 'OpenAI API error: HTTP status ' . $response_code . ' - ' . $body;
			}

			$response_data = json_decode($body, true);
			if ( json_last_error() !== JSON_ERROR_NONE ){
				return 'Failed to decode API response.';
			}

			if ( isset( $response_data['choices'][0]['message']['content'] ) ){
				return array(
					'created' => $response_data['created'],
					'model' => $response_data['model'],
					'content' => trim($response_data['choices'][0]['message']['content']),
					'total_tokens' => $response_data['usage']['total_tokens'],
				);
			}

			return 'Unexpected API response structure.';
		}

		//Choose a function to review, generate the prompt, and then call the AI endpoint
		public function ajax_ai_code_review(){
			if ( !is_user_logged_in() || !$this->is_dev() || !$this->get_option('ai_features') || !$this->get_option('ai_code_review') ){
				return false;
			}

			$code_review_data = get_transient('nebula_ai_code_review');

			$this->super->globals['ai_code_review_status'] = '';

			if ( !$code_review_data ){
				$code_review_data = $this->transient('nebula_ai_code_review', function(){
					$function_data = $this->get_random_function();

					if ( empty($function_data) ){
						$this->super->globals['ai_code_review_status'] = 'Could not choose a random function';
						return;
					}

					$prompt = $this->ai_prepare_code_review_prompt($function_data['function']);
					$token_estimate = $this->openai_estimate_tokens($prompt);

					if ( $token_estimate === false || $token_estimate >= apply_filters('nebula_ai_code_review_token_limit', 3000) ){
						$this->super->globals['ai_code_review_status'] = 'The selected function ("' . $function_data['name'] . '" from ' . $function_data['filename'] . ') would use too many tokens to review. Reload to try a different function.';
						return;
					}

					$response = $this->openai_prompt(array(
						'prompt' => $prompt,
						'model' => 'o4-mini-2025-04-16' //Eventually use this when it works: gpt-5-mini-2025-08-07
					));

					if ( is_string($response) ){
						$this->super->globals['ai_code_review_status'] = 'AI Response: ' . $response;
						return;
					}

					return array(
						'token_estimate' => $token_estimate,
						'function' => $function_data,
						'response' => $response,
					);
				}, (strtotime('today 23:59:59', time())-time()), false);
			}

			if ( empty($code_review_data) ){
				$code_review_data = 'Error: ' . $this->super->globals['ai_code_review_status'];
			}

			$this->render_code_review($code_review_data, true);
		}

		//Prepare the code review prompt for OpenAI API
		private function ai_prepare_code_review_prompt($function_code){
			$prompt = '';

			$prompt .= "You are a senior WordPress engineer and security consultant.\n\n";
			$prompt .= "Your task is to review the following function used in a WordPress theme or plugin. Unless otherwise noted, assume all functions, methods, and variables referenced in the code exist elsewhere in the theme or plugin.\n";
			$prompt .= "Focus on feasibility and meaningful concerns rather than being nitpicky. Use a professional, concise tone. Avoid verbosity, filler, or overly academic language.\n";
			$prompt .= "Consider this a peer code review: be constructive, not pedantic. Assume the developer is skilled and seeking clarity on any significant issues.\n\n";

			$prompt .= "## Summary – Always format this section exactly as follows:\n";
			$prompt .= "<h2>Summary</h2>\n";
			$prompt .= "<p>[Priority line: e.g., 'Moderate Priority: <strong>3 issues</strong> <small>(0 critical, 1 major, 2 minor)</small>']</p>\n";
			$prompt .= "<p>[Very short paragraph description of what the function does, including notable strengths. Aim for 50 words or fewer.]</p>\n";
			$prompt .= "Do not combine the priority line with the descriptive sentence. Each must be in its own <p> tag. Do not include the priority line inside the <h2> tag. Priorities should be 'High' if any critical issues are found, 'Moderate' if any major issues, or 'Low' if only minor issues are found.\n";

			$prompt .= "### 🚨 Critical Issues – Identify any catastrophic problems. If none exist, do not include this section at all (not even the heading). Do not output 'N/A'.\n";

			$prompt .= "### Findings – Describe concerns about logic, security, performance, or general code quality. Avoid minor stylistic preferences or subjective opinions unless they have a meaningful impact.\n";
			$prompt .= "Prefix each finding bullet point with '<strong>Critical: </strong> ', '<strong>Moderate: </strong> ', or '<strong>Minor: </strong> '.\n";
			$prompt .= "Consider whether the function lacks critical comments, but do not comment on trivial issues. Do not worry about a lack of PHPDoc comments.\n";
			$prompt .= "Ignore compatibility with older PHP versions, older browsers, the global `nebula` object, and the absence of unit or integration tests.\n";
			$prompt .= "Limit findings to a maximum of 5. If more exist, prioritize only the most important ones. However, do not use an ordered list to output. If listing items, use an unordered list for bullets.\n";

			$prompt .= "### Recommendations – Provide specific improvements or fixes for the findings, including brief code examples if helpful. Be concise. Do not repeat the finding text.\n";

			$prompt .= "#### WordPress Best Practices – Suggest replacing or improving custom functionality with native WordPress functions, constants, or patterns *only if relevant*. If none apply, omit this section entirely (including the heading).\n";

			$prompt .= "### Improved Version – If appropriate, provide a revised version of the function that addresses the issues and follows best practices. Include a brief DocBlock summarizing the function and its parameters.\n\n";

			$prompt .= "Respond in simple markdown format using only basic elements: headings, paragraphs, bold, code, and bullet lists. Do not use italics.\n\n";

			$prompt .= "Here is the function:\n\n";
			$prompt .= "```php\n" . $function_code . "\n```";

			return $prompt;
		}
	}
}