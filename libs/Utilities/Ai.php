<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Ai') ){
	trait Ai {
		public function hooks(){
			add_action('wp_ajax_nebula_openai_prompt', array($this, 'openai_prompt'));
			add_action('wp_ajax_nopriv_nebula_openai_prompt', array($this, 'openai_prompt'));
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
		public function openai_prompt($parameters){
			if ( empty($this->get_option('openai_api_key')) ){
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

			$system_prompt = $parameters['system_prompt'] ?? '';
			$model = $parameters['model'] ?? 'gpt-4.1-mini'; //OpenAI Model Comparison: https://platform.openai.com/docs/models

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

			$response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->get_option('openai_api_key'),
				),
				'body'    => json_encode($data),
				'timeout' => 30,
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
	}
}