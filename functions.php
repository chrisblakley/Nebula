<?php
/**
 * Functions
 */

require_once('nebula.php');
nebula();



//delete this... Does this even do anything?
function nebula_get_latest_post($attributes){
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




//Close functions.php. DO NOT add anything after this closing tag!! ?>