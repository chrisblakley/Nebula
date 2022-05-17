<?php
	/**
	 * The template for displaying Tag Archive pages.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<?php get_template_part('inc/headercontent'); ?>
<?php get_template_part('inc/nebula_drawer'); ?>

<?php nebula()->timer('Tag Template'); ?>
<section id="content-section">
	<div class="container">
		<div class="row">
			<div class="col">
				<?php nebula()->breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
		<div class="row">
			<div id="top" class="col-md" role="main">
				<?php get_template_part('loop', 'tag'); ?>
			</div><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</section>
<?php nebula()->timer('Tag Template', 'end'); ?>

<?php get_footer(); ?>