<?php
	/**
	 * The template for displaying search results pages.
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

<?php nebula()->timer('Search Template'); ?>
<section id="content-section">
	<div class="container">
		<div class="row">
			<div class="col">
				<?php nebula()->breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
		<div class="row">
			<main id="top" class="col" role="main">
				<?php if ( have_posts() ): ?>
					<div id="searchresults">
						<?php get_template_part('loop', 'search'); ?>
					</div>

					<?php do_action('nebula_after_search_results'); ?>
				<?php else: ?>
					<p class="no-search-results"><?php _e('No search results.', 'nebula'); ?></p>
					<?php do_action('nebula_no_search_results'); ?>
				<?php endif; ?>
			</main><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</section>
<?php nebula()->timer('Search Template', 'end'); ?>

<?php get_footer(); ?>
