<?php

/*==========================
 This file includes functions that are currently being developed and tested. Once finished they will be moved to the appropriate function file.
 It is recommended that the require_once() for this file remain commented out from functions.php (or feel free to delete the entire line).
 ===========================*/



/*
//For automatically setting certain "Screen Options" settings by default.
add_action('admin_init', 'set_user_metaboxes');
function set_user_metaboxes($user_id=NULL) {
    //css-classes-hide

    echo 'bacon. user meta keys: ';
    var_dump(  );
    //var_dump(meta_box_prefs($screen)); //$screen needs to be the admin screen id or something
}
*/







/*
//This is the standard/basic search autocomplete function.
//@TODO "Nebula" 0: Before going too far into customizing this, research if the JavaScript API in WordPress 4.1 would allow for better autocomplete via JS.
//The problem I foresee with this is trying to add all post types, categories, tags, and especially custom fields to the results.
//If continuing with this AJAX function, be sure to re-format the layout and everything, and change the myprefix to nebula.

add_action('wp_loaded', 'myprefix_autocomplete_init');
function myprefix_autocomplete_init() {
    // Register our jQuery UI style and our custom javascript file
    wp_register_style('myprefix-jquery-ui','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
    //wp_register_script( 'my_acsearch', get_template_directory_uri() . '/js/myacsearch.js', array('jquery','jquery-ui-autocomplete'),null,true);
	//wp_register_script('myprefix-jquery-ui-min','https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js');

    // Function to fire whenever search form is displayed
    add_action( 'get_search_form', 'myprefix_autocomplete_search_form' );

    // Functions to deal with the AJAX request - one for logged in users, the other for non-logged in users.
    add_action( 'wp_ajax_myprefix_autocompletesearch', 'myprefix_autocomplete_suggestions' );
    add_action( 'wp_ajax_nopriv_myprefix_autocompletesearch', 'myprefix_autocomplete_suggestions' );
}

function myprefix_autocomplete_search_form(){
    //wp_enqueue_script( 'my_acsearch' );
    wp_enqueue_style( 'myprefix-jquery-ui' );
    //wp_enqueue_script('myprefix-jquery-ui-min');
}

function myprefix_autocomplete_suggestions(){
    // Query for suggestions
    $posts = get_posts( array(
        's' =>$_REQUEST['term'],
    ) );

    // Initialise suggestions array
    $suggestions=array();

    global $post;
    foreach ($posts as $post): setup_postdata($post); //@TODO "Nebula" 0: Push all posts, pages, categories, tags, and custom fields into suggestion array... and prepend each with an appropriate icon. Could probably use the voice-recognition navigator to do this for everything except custom fields.
        // Initialise suggestion array
        $suggestion = array();
        $suggestion['label'] = esc_html($post->post_title);
        $suggestion['link'] = get_permalink();

        // Add suggestion to suggestions array
        $suggestions[]= $suggestion;
    endforeach;

    // JSON encode and echo
    $response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
    echo $response;

    // Don't forget to exit!
    exit;
}
*/
