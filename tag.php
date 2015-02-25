<?php
/**
 * The template for displaying Tag Archive pages.
 */

if ( !defined('ABSPATH') ) { //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_header');

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">

		<div class="eleven columns">
			<h1><i class="archiveicon fa fa-tag"></i> <?php echo single_tag_title('', false); //@TODO "Nebula" 0: Come up with a way to have multiple tag archives (and use fa-tags icon). ?></h1>
			<?php get_template_part('loop', 'tag'); ?>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>

<?php do_action('nebula_footer'); ?>