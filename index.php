<?php
/**
 * The main template file.
 */

if ( !defined('ABSPATH') ) { //Log and redirect if accessed directly
	ga_send_event('Direct Template Access', 'Template: ' . end(explode('/', $template)), basename($_SERVER['PHP_SELF']));
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")));
	exit;
}

get_header(); ?>

<?php get_template_part( 'loop', 'index' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>