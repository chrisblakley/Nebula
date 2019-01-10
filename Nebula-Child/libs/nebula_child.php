<?php

/*==========================
 Custom child theme functions
 Use this file for project-specific functions.

 See instructions for template directories and function overrides in ../functions.php
 ===========================*/

//Add/remove post formats as needed - http://codex.wordpress.org/Post_Formats
//add_theme_support('post-formats', array('aside', 'chat', 'status', 'gallery', 'link', 'image', 'quote', 'video', 'audio'));

//Add new image sizes
//Certain sizes (like FB Open Graph sizes) are already added, so only add extra sizes that are needed.
//add_action('after_setup_theme', function(){
	//add_image_size('example', 32, 32, 1);
//});

//This hook is used when author bios (Nebula Option) is disabled. It will redirect to the About page instead.
//add_filter('nebula_no_author_redirect', function($url){
	//return get_permalink(99999); //Use ID for the About Us page here
//});


/*
//Register custom post types
add_action('init', function(){
	register_post_type('events', array(
		'labels' => array(
			'name' => 'Events', //Plural
			'singular_name' => 'Event',
		),
		'description' => 'Upcoming webinars and conferences.',
		'public' => true,
		'menu_icon' => 'dashicons-calendar', //https://developer.wordpress.org/resource/dashicons/
		'has_archive' => true,
		'supports' => array('title', 'editor', 'revisions', 'excerpt', 'thumbnail'),
	));

	//If you get a 404 on a custom post type try flush_rewrite_rules() but only use it temporarily (it is an expensive operation)
});
*/

//Create custom block category for this website
add_filter('block_categories', function($categories, $post){
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'nebula-child', //Change this
				'title' => 'Nebula Child', //Change this
				//'icon' => 'wordpress', //Places a Dashicon next to the category name
			)
		)
	);
}, 3, 2);




?>