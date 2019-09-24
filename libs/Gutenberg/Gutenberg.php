<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Gutenberg') ){
	trait Gutenberg {
		public function hooks(){
			if ( function_exists('register_block_type') ){ //There may be a better way to check Gutenberg support. Other frameworks use: defined('GUTENBERG_VERSION')
				add_filter('block_categories', array($this, 'nebula_block_categories'), 3, 2);

				add_action('init', array($this, 'youtube_gutenberg_block'));
				add_action('init', array($this, 'vimeo_gutenberg_block'));

/*
				add_action('init', array($this, 'gutenberg_hello_world_block'));
				add_action('init', array($this, 'gutenberg_style_test_block'));
				add_action('init', array($this, 'gutenberg_latest_posts_block'));
*/
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

		//Nebula Youtube Block
		public function youtube_gutenberg_block(){
			if ( function_exists('register_block_type') ){
				//Editor Script
				wp_register_script(
					'nebula-youtube-block',
					get_template_directory_uri() . '/libs/Gutenberg/blocks/youtube/youtube.js',
					array('wp-blocks', 'wp-i18n', 'wp-element') //I dont think wp-i18n is needed here
				);

				register_block_type('nebula/youtube', array(
					'editor_script' => 'nebula-youtube-block',
					//'script' => 'nebula-youtube-block', //Use this to create the element in the "save" object of the block JS file
					'render_callback' => array($this, 'nebula_youtube_block_frontend_output'), //Use this to create the element in PHP (With attributes passed as a parameter)
				));
			}
		}








		//Nebula Youtube Block front-end
		public function nebula_youtube_block_frontend_output($attribites){

			//var_dump($attribites);

			//How to get "additional css classes" here?

			$youtube_data = nebula()->video_meta('youtube', $attribites['videoID']);

			return '<div class="nebula-youtube embed-responsive embed-responsive-16by9 ' . $attributes['className'] . '"><iframe id="' . $youtube_data['safetitle'] . '" class="youtube embed-responsive-item" width="1024" height="768" src="//www.youtube.com/embed/' . $youtube_data['id'] . '?wmode=transparent&enablejsapi=1&rel=0&t=' . $attribites['videoTimestamp'] . '" frameborder="0" allowfullscreen loading="lazy"></iframe></div>';
		}







		//Nebula Vimeo Block
		public function vimeo_gutenberg_block(){
			if ( function_exists('register_block_type') ){
				//Editor Script
				wp_register_script(
					'nebula-vimeo-block',
					get_template_directory_uri() . '/libs/Gutenberg/blocks/vimeo/vimeo.js',
					array('wp-blocks', 'wp-i18n', 'wp-element') //I dont think wp-i18n is needed here
				);

				register_block_type('nebula/vimeo', array(
					'editor_script' => 'nebula-vimeo-block',
					//'script' => 'nebula-vimeo-block', //Use this to create the element in the "save" object of the block JS file
					'render_callback' => array($this, 'nebula_vimeo_block_frontend_output'), //Use this to create the element in PHP (With attributes passed as a parameter)
				));
			}
		}

		//Nebula Vimeo Block front-end
		public function nebula_vimeo_block_frontend_output($attribites){
			$vimeo_data = nebula()->video_meta('vimeo', $attribites['videoID']);

			return '<div class="embed-responsive embed-responsive-16by9"><iframe class="vimeo embed-responsive-item" data-vimeo-id="' . $vimeo_data['id'] . '" src="https://player.vimeo.com/video/' . $vimeo_data['id'] . '" width="560" height="315" loading="lazy"></iframe></div>';
		}








		//Hello World Test
		public function gutenberg_hello_world_block(){
			//Editor Script
			wp_register_script(
				'nebula-hello-world-editor',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/hello-world/hello-world.js',
				array( 'wp-blocks', 'wp-element' )
			);

			//Editor Style
			wp_register_style(
				'nebula-hello-world-editor',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/hello-world/hello-world-editor.css',
				array( 'wp-edit-blocks' ),
				null
			);

			//Front-End Style
			wp_register_style(
				'nebula-hello-world',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/hello-world/hello-world.css',
				array(),
				null
			);

			register_block_type( 'nebula/hello-world', array(
				'editor_script' => 'nebula-hello-world-editor',
				'editor_style' => 'nebula-hello-world-editor',
				'style' => 'nebula-hello-world',
			) );
		}




		//Style Test
		public function gutenberg_style_test_block(){
			//Editor Script
			wp_register_script(
				'nebula-style-test',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/style-test/style-test.js',
				array( 'wp-blocks', 'wp-element' )
			);

			//Editor Style
			wp_register_style(
				'nebula-style-test',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/style-test/style-test.css',
				array( 'wp-edit-blocks' ),
				null
			);

			register_block_type( 'nebula/style-test', array(
				'editor_script' => 'nebula-style-test',
				'editor_style' => 'nebula-style-test',
				'style' => 'nebula-style-test',
			) );
		}




		//Latest Posts Block
		public function gutenberg_latest_posts_block(){
			//Editor Script
			wp_register_script(
				'nebula-latest-posts-block',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/latest/latest.js',
				array('wp-blocks', 'wp-element')
			);

			//Editor Style
			wp_register_style(
				'nebula-latest-posts-block',
				get_template_directory_uri() . '/libs/Gutenberg/blocks/latest/latest.css',
				array('wp-edit-blocks'),
				null
			);

			register_block_type('nebula/latest-posts', array(
				'editor_script' => 'nebula-latest-posts-block',
				'editor_style' => 'nebula-latest-posts-block',
				'style' => 'nebula-latest-posts-block',
				'render_callback' => 'nebula_get_latest_post',
			));
		}

		//function for the front-end
		public function nebula_get_latest_post($attributes){
			echo '<h1>testing</h1>';
			return 'this would be posts! yay!'; //this never appears

			$recent_posts = wp_get_recent_posts( array(
				'numberposts' => 3,
				'post_status' => 'publish',
			) );

			if ( count( $recent_posts ) === 0 ) {
				return 'No posts';
			}

			$post = $recent_posts[0];
			$post_id = $post['ID'];

			return sprintf(
				'<a class="wp-block-nebula-latest-post" href="%1$s">%2$s</a>',
				esc_url( get_permalink( $post_id ) ),
				esc_html( get_the_title( $post_id ) )
			);
		}



















	} //close of trait


} //close of if gutenberg is active conditional