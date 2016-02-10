<?php
/**
 * The template for displaying Search Results pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container">
		<div class="row">
			<div class="sixteen columns">
				<?php if ( have_posts() ): ?>
					<h1 class="page-title">Search Results</h1>
					<p>
						Your search for "<?php echo get_search_query(); ?>" returned
						<?php
							if ( file_exists(WP_PLUGIN_DIR . '/relevanssi') && $wp_query->found_posts ){ //If Relevanssi is enabled
								echo $wp_query->found_posts;
							} else {
								$search_results = &new WP_Query("s=$s&showposts=-1");
								echo $search_results->post_count;
								wp_reset_query();
							}
						?>
						results.
					</p>
				<?php else: ?>
					<h1 class="page title">No Results Found</h1>
					<p>Your search for "<?php echo get_search_query(); ?>" returned 0 results.</p>
					<script>
						ga('set', gaCustomDimensions['sessionNotes'], sessionNote('No Search Results'));
						ga('send', 'event', 'Internal Search', 'No Results', jQuery('#s').val(), {'nonInteraction': 1});
					</script>
				<?php endif; ?>

				<?php get_search_form(); ?>
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="breadcrumbbar">
	<div class="row">
		<div class="sixteen columns">
			<?php the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
	<hr />
</div><!--/container-->

<div class="row">
	<div class="eleven columns">
		<?php if ( have_posts() ): ?>
			<div id="searchresults">
				<?php get_template_part('loop', 'search'); ?>
				<?php wp_pagenavi(); ?>
			</div><!--/#searchresults-->
		<?php endif; ?>
	</div><!--/columns-->

	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>