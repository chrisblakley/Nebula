<?php

/*==========================
 Custom child theme functions
 Use this file for project-specific functions.

 See instructions for template directories and function overrides in ../functions.php
 ===========================*/

//When adding potentially time consuming functionality, consider using nebula()->timer('Task Name') to monitor performance: https://nebula.gearside.com/functions/timer/

//Add new image sizes
//Certain sizes (like FB Open Graph sizes) are already added, so only add extra sizes that are needed.
//add_action('after_setup_theme', function(){
	//Add/remove post formats as needed - http://codex.wordpress.org/Post_Formats
	//add_theme_support('post-formats', array('aside', 'chat', 'status', 'gallery', 'link', 'image', 'quote', 'video', 'audio'));

	//add_image_size('example', 32, 32, 1);
//});

//This hook is used when author bios (Nebula Option) is disabled. It will redirect to the About page instead.
//add_filter('nebula_no_author_redirect', function($url){
	//return get_permalink(99999); //Use ID for the About Us page here
//});

/*
//Register custom post types
add_action('init', function(){
	register_post_type('events', array( //This is the text that appears in the URL
		'labels' => array( //https://developer.wordpress.org/reference/functions/get_post_type_labels/
			'name' => 'Events', //Plural <-- Change this to match your desired post type name
			'singular_name' => 'Event', // <-- Change this to match your desired post type name
		),
		'description' => 'Upcoming webinars and conferences.',
		'menu_icon' => 'dashicons-calendar', //https://developer.wordpress.org/resource/dashicons/
		'has_archive' => true,
		'taxonomies' => array('category', 'post_tag'),
		'rewrite' => array(
			'slug' => 'events', // <-- Change this to match your desired post type name
			'with_front' => false //This prevents any modification to the global permalink structure from also appearing on this CPT permalink URL
		),
		'supports' => array('title', 'editor', 'revisions', 'excerpt', 'thumbnail'),
		'public' => true,
		'publicly_queryable' => true, //Keep this to prevent 404s on the front-end (unless it should not be available to visitors)
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

//Override Nebula parent page templates
// add_filter('theme_page_templates', function($templates){
// 	unset( $templates['page-templates/fullwidth.php'] );
// 	return $templates;
// });




//Example hooks to add the feedback system to all posts, pages, and custom post types
//add_action('loop_end', 'show_nebula_feedback_system');
//add_action('nebula_after_search_results', 'show_nebula_feedback_system');
//add_action('nebula_no_search_results', 'show_nebula_feedback_system');
//add_action('nebula_404_content', 'show_nebula_feedback_system');
function show_nebula_feedback_system(){
	//Ignore WP admin pages
	if ( nebula()->is_admin_page() ){
		return false;
	}

	//Ignore certain pages
	if ( is_page(99999) || is_page(99999) || is_page(99999) ){
		return false;
	}

	//Ignore the home page
	if ( is_front_page() ){
		return false;
	}

	//Ignore non-single posts/pages, but allow search and 404 templates
	if ( !is_singular() && !is_search() && !is_404() ){
		return false;
	}

	//Only use the nebula_404_content action for 404 feedback (this is to prevent the feedback system from appearing multiple times from the error_query)
	if ( current_action() === 'loop_end' && is_404() ){
		return false;
	}

	//Only use the nebula_after_search_results and nebula_no_search_results for search results listings (to prevent the feedback system from appearing in the head)
	if ( current_action() === 'loop_end' && is_search() ){
		return false;
	}

	nebula()->feedback(99999); //Show the feedback system with a custom CF7 form ID
}






?>