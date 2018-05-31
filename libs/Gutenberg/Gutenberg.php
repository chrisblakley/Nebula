<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Gutenberg') ){
	trait Gutenberg {
		public function hooks(){
			if ( function_exists('register_block_type') ){ //There may be a better way to check Gutenberg support
				add_action('init', array($this, 'gutenberg_hello_world_block'));
				add_action('init', array($this, 'gutenberg_style_test_block'));
				add_action('init', array($this, 'gutenberg_latest_posts_block'));
			}
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
		        'editor_style'  => 'nebula-hello-world-editor',
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
		        'editor_style'  => 'nebula-style-test',
		        'style' => 'nebula-style-test',
		    ) );
		}




		//Latest Posts Block
		public function gutenberg_latest_posts_block(){
			//Editor Script
			wp_register_script(
		        'nebula-latest-posts-block',
		        get_template_directory_uri() . '/libs/Gutenberg/blocks/latest/latest.js',
		        array( 'wp-blocks', 'wp-element' )
		    );

			//Editor Style
		    wp_register_style(
		        'nebula-latest-posts-block',
		        get_template_directory_uri() . '/libs/Gutenberg/blocks/latest/latest.css',
		        array( 'wp-edit-blocks' ),
		        null
		    );

		    register_block_type('nebula/latest-posts', array(
		        'editor_script' => 'nebula-latest-posts-block',
		        'editor_style'  => 'nebula-latest-posts-block',
		        'style' => 'nebula-latest-posts-block',
		        'render_callback' => 'nebula_get_latest_post',
		    ) );
		}



	}

	if ( function_exists('register_block_type') ){
		register_block_type('hiRoy/serverSide', array(
			'render_callback' => 'hi_roy_render_callback',
			'attributes' => array(
				'images' => array(
					'type' => 'array'
				)
			)
		));
	}

	function hi_roy_render_callback($attributes){
		$images = $attributes['images'];
		return '<div><!-- put image gallery here--></div>';
	}
}