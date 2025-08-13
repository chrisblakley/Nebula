<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Gutenberg') ){
	trait Gutenberg {
		public function hooks(){
			if ( function_exists('register_block_type') ){ //There may be a better way to check Gutenberg support. Other frameworks use: defined('GUTENBERG_VERSION')
				add_filter('block_categories_all', array($this, 'nebula_block_categories'), 3, 2);

				add_action('init', array($this, 'vimeo_gutenberg_block'));
				add_action('init', array($this, 'youtube_gutenberg_block'));

				add_action('init', array($this, 'code_gutenberg_block'));

				//add_action('init', array($this, 'breadcrumbs_gutenberg_block'));

/*
				add_action('init', array($this, 'gutenberg_hello_world_block'));
				add_action('init', array($this, 'gutenberg_style_test_block'));
				add_action('init', array($this, 'gutenberg_latest_posts_block'));
*/

				if ( $this->get_option('openai_api_key') ){
					add_action('init', array($this, 'ai_content_gutenberg_block'));
					add_action('rest_api_init', function(){
						register_rest_route('nebula/v1', '/generate-content', array(
							'methods'  => 'POST',
							'callback' => array($this, 'nebula_generate_ai_content'),
							'permission_callback' => function(){
								return current_user_can('edit_posts');
							}
						));
					});
				}

			}
		}

		//Create custom Nebula block category
		public function nebula_block_categories($categories, $post){
			return array_merge(
				$categories,
				array(
					array(
						'slug' => 'nebula',
						'title' => 'Nebula',
						//'icon' => 'wordpress', //Places a Dashicon next to the category name
					)
				)
			);
		}
























		//Nebula Vimeo Block
		public function vimeo_gutenberg_block(){
			if ( function_exists('register_block_type') ){
				//Editor Script
				wp_register_script(
					'nebula-vimeo-block',
					get_template_directory_uri() . '/libs/Gutenberg/blocks/vimeo/vimeo.js',
					array('wp-blocks', 'wp-element'), //I dont think wp-i18n is needed here
					$this->version('full')
				);

				register_block_type('nebula/vimeo', array(
					'editor_script' => 'nebula-vimeo-block',
					//'script' => 'nebula-vimeo-block', //Use this to create the element in the "save" object of the block JS file
					'render_callback' => function($attribites, $content){ //Use this to create the element in PHP (With attributes passed as a parameter)
						$vimeo_data = nebula()->video_meta('vimeo', $attribites['videoID']);

						return '<div class="ratio ratio-16x9"><iframe class="vimeo" data-vimeo-id="' . $vimeo_data['id'] . '" src="https://player.vimeo.com/video/' . $vimeo_data['id'] . '" width="560" height="315" loading="lazy"></iframe></div>';
					},
				));
			}
		}

















		//Nebula Youtube Block
		public function youtube_gutenberg_block(){
			if ( function_exists('register_block_type') ){
				//Editor Script
				wp_register_script(
					'nebula-youtube-block',
					get_template_directory_uri() . '/libs/Gutenberg/blocks/youtube/yt.js', //Something doesn't like "youtube.js" so avoid that filename
					array('wp-blocks', 'wp-element'),
					$this->version('full')
				);

				register_block_type('nebula/youtube', array(
					'editor_script' => 'nebula-youtube-block',
					//'script' => 'nebula-youtube-block', //Use this to create the element in the "save" object of the block JS file
					'render_callback' => function($attributes){ //Use this to create the element in PHP (With attributes passed as a parameter)
						$youtube_data = nebula()->video_meta('youtube', $attributes['videoID']);

						$class_name = ( isset($attributes['className']) )? $attributes['className'] : '';
						$video_timestamp = ( isset($attributes['videoTimestamp']) )? $attributes['videoTimestamp'] : '';

						return '<div class="nebula-youtube ratio ratio-16x9 ' . $class_name . '"><iframe id="' . $youtube_data['safetitle'] . '" class="youtube" width="1024" height="768" src="//www.youtube.com/embed/' . $youtube_data['id'] . '?wmode=transparent&enablejsapi=1&rel=0&t=' . $video_timestamp . '" frameborder="0" allowfullscreen loading="lazy"></iframe></div>';
					},
				));
			}
		}








		public function code_gutenberg_block(){
			if ( function_exists('register_block_type') ){
				wp_register_script(
					'nebula-code-block',
					get_template_directory_uri() . '/libs/Gutenberg/blocks/code/code.js',
					array('wp-blocks', 'wp-element'),
					$this->version('full')
				);

				register_block_type('nebula/code', array(
					'editor_script' => 'nebula-code-block',
					'render_callback' => function($attributes){
						//wp_enqueue_style('nebula-pre'); //Ensure the dependent CSS is enqueued? Is this necessary?

						$content = isset($attributes['content']) ? $attributes['content'] : '';
						$language = isset($attributes['language']) ? $attributes['language'] : '';

						// Wrap the output in a div with class 'nebula-code-con' and output the language class in the 'codetitle' div
						$output = '<div class="nebula-code-con nebula-code-block">';

						// Add the 'codetitle' div with the language class
						$output .= '<div class="nebula-code codetitle ' . esc_attr(strtolower($language)) . '">';
						$output .= ( $language )? $language : '';
						$output .= '</div>';

						// Add the 'pre' element with the 'nebula-code' class and language-specific class
						$output .= '<pre class="nebula-code ' . esc_attr(strtolower($language)) . '" data-language="' . esc_attr($language) . '">';
						$output .= esc_html($content);
						$output .= '</pre>';

						$output .= '</div>'; // Close the 'nebula-code-con' div

						return $output;
					}
				));
			}
		}













		//Nebula AI Content Block
		public function ai_content_gutenberg_block(){
			if ( function_exists('register_block_type') ){
				wp_register_script(
					'nebula-ai-content-block',
					get_template_directory_uri() . '/libs/Gutenberg/blocks/ai-content/ai-content.js?ver=' . $this->version('full'),
					array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-block-editor', 'wp-api-fetch'),
					'1.0.0',
					true
				);

				register_block_type('nebula/aicontent', array(
					'editor_script' => 'nebula-ai-content-block',
					'render_callback' => array($this, 'render_nebula_aicontent_block'),
				));
			}
		}

		//Actually generate the AI content from the WP editor
		public function nebula_generate_ai_content($request){
			$prompt = sanitize_text_field($request->get_param('prompt'));

			if ( empty($prompt) ){
				return new WP_Error('no_prompt', 'Prompt is required', ['status' => 400]);
			}

			$post_title = sanitize_text_field($request->get_param('post_title'));
			$post_content = wp_strip_all_tags($request->get_param('post_content'));

			$system_prompt = 'You are writing content for a website. Be sure to consider SEO. Output only the generated content. Do not include any preamble, explanation, or commentary. Do not wrap the generated content in fencing. The post title is "' . $post_title . '". Here is the rest of the post content: ' . $post_content;

			$estimated_tokens = $this->openai_estimate_tokens($prompt);

			if ( $estimated_tokens > 5000 ){
				return new WP_Error('exceeds_token_limit', 'This prompt (' . $estimated_tokens . ') exceeds the token limit (5000).', ['status' => 400]);
			}

			$response = $this->openai_prompt(array(
				'prompt' =>$prompt,
				'system_prompt' => $system_prompt
			));

			return rest_ensure_response([
				'data' => $response['content']
			]);
		}

		//Output the AI content on the front-end
		public function render_nebula_aicontent_block($attributes){
			$content = ( isset($attributes['content']) )? $attributes['content'] : '';

			if ( empty($content) ){
				return '';
			}

			$formatted_content = nl2br(esc_html($content));

			return sprintf(
				'<p class="wp-block-nebula-aicontent ai-content-output">%s</p>',
				$formatted_content
			);
		}
















		//Nebula Breadcrumbs
// 		public function breadcrumbs_gutenberg_block(){
// 			if ( function_exists('register_block_type') ){
// 				//Editor Script
// 				wp_register_script(
// 					'nebula-breadcrumbs-block',
// 					get_template_directory_uri() . '/libs/Gutenberg/blocks/breadcrumbs/breadcrumbs.js',
// 					array('wp-blocks', 'wp-element'),
//					$this->version('full')
// 				);
//
// 				register_block_type('nebula/breadcrumbs', array(
// 					'editor_script' => 'nebula-breadcrumbs-block',
// 					//'script' => 'nebula-breadcrumbs-block', //Use this to create the element in the "save" object of the block JS file
// 					'render_callback' => array($this, 'nebula_breadcrumbs_block_frontend_output'), //Use this to create the element in PHP
// 				));
// 			}
// 		}
//
// 		//Nebula Breadcrumbs Block front-end
// 		public function nebula_breadcrumbs_block_frontend_output($attribites){
// 			if ( !nebula()->is_admin_page() ){
// 				return nebula()->breadcrumbs();
// 			}
// 		}












		//Hello World Test
// 		public function gutenberg_hello_world_block(){
// 			//Editor Script
// 			wp_register_script(
// 				'nebula-hello-world-editor',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/hello-world/hello-world.js',
// 				array( 'wp-blocks', 'wp-element' )
// 			);
//
// 			//Editor Style
// 			wp_register_style(
// 				'nebula-hello-world-editor',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/hello-world/hello-world-editor.css',
// 				array( 'wp-edit-blocks' ),
// 				null
// 			);
//
// 			//Front-End Style
// 			wp_register_style(
// 				'nebula-hello-world',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/hello-world/hello-world.css',
// 				array(),
// 				null
// 			);
//
// 			register_block_type( 'nebula/hello-world', array(
// 				'editor_script' => 'nebula-hello-world-editor',
// 				'editor_style' => 'nebula-hello-world-editor',
// 				'style' => 'nebula-hello-world',
// 			) );
// 		}




		//Style Test
// 		public function gutenberg_style_test_block(){
// 			//Editor Script
// 			wp_register_script(
// 				'nebula-style-test',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/style-test/style-test.js',
// 				array( 'wp-blocks', 'wp-element' )
// 			);
//
// 			//Editor Style
// 			wp_register_style(
// 				'nebula-style-test',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/style-test/style-test.css',
// 				array( 'wp-edit-blocks' ),
// 				null
// 			);
//
// 			register_block_type( 'nebula/style-test', array(
// 				'editor_script' => 'nebula-style-test',
// 				'editor_style' => 'nebula-style-test',
// 				'style' => 'nebula-style-test',
// 			) );
// 		}




		//Latest Posts Block
// 		public function gutenberg_latest_posts_block(){
// 			//Editor Script
// 			wp_register_script(
// 				'nebula-latest-posts-block',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/latest/latest.js',
// 				array('wp-blocks', 'wp-element')
// 			);
//
// 			//Editor Style
// 			wp_register_style(
// 				'nebula-latest-posts-block',
// 				get_template_directory_uri() . '/libs/Gutenberg/blocks/latest/latest.css',
// 				array('wp-edit-blocks'),
// 				null
// 			);
//
// 			register_block_type('nebula/latest-posts', array(
// 				'editor_script' => 'nebula-latest-posts-block',
// 				'editor_style' => 'nebula-latest-posts-block',
// 				'style' => 'nebula-latest-posts-block',
// 				'render_callback' => 'nebula_get_latest_post',
// 			));
// 		}

		//function for the front-end
// 		public function nebula_get_latest_post($attributes){
// 			echo '<h1>testing</h1>';
// 			return 'this would be posts! yay!'; //this never appears
//
//
// 			$recent_posts = wp_get_recent_posts( array(
// 				'numberposts' => 3,
// 				'post_status' => 'publish',
// 			) );
//
// 			if ( count( $recent_posts ) === 0 ) {
// 				return 'No posts';
// 			}
//
// 			$post = $recent_posts[0];
// 			$post_id = $post['ID'];
//
// 			return sprintf(
// 				'<a class="wp-block-nebula-latest-post" href="%1$s">%2$s</a>',
// 				esc_url(get_permalink($post_id)),
// 				esc_html(get_the_title($post_id))
// 			);
//
// 		}



















	} //close of trait


} //close of if gutenberg is active conditional