<?php
/**
 * The template for displaying Tag Archive pages.
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div class="row">
	
	<div class="eleven columns">
		<?php the_breadcrumb(); ?>
		<h1>Tag Archives: <?php echo single_tag_title('', false); ?></h1>
		<?php get_template_part('loop', 'tag'); ?>
	</div><!--/columns-->
	
	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>