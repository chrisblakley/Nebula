<?php
/**
 * The template for displaying Search Results pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
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

<div class="row fullcontentcon">
	<div class="eleven columns">
		<?php if ( have_posts() ): ?>
			<h1>Search Results <?php get_search_query(); ?></h1>
			<?php get_search_form(); ?>

			<div id="searchresults">
				<p>
					Your search criteria returned
					<?php if ( file_exists(WP_PLUGIN_DIR . '/relevanssi') && $wp_query->found_posts ) : //if Relevanssi is enabled ?>
						<?php echo $wp_query->found_posts; ?>
					<?php else: ?>
						<?php
							$search_results = &new WP_Query("s=$s&showposts=-1");
							echo $search_results->post_count;
							wp_reset_query();
						?>
					<?php endif; ?>
					&nbsp;results.
				 </p>
				<?php get_template_part('loop', 'search'); ?>
			</div><!--/#searchresults-->
		<?php else : ?>
			<h1>No Results Found</h1>
			<?php get_search_form(); ?>

			<div id="searchresults">
				<p>Your search criteria returned 0 results.</p>

				<script>
					var badSearchTerm = jQuery('#s').val();
					ga('send', 'event', 'Internal Search', 'No Results', badSearchTerm, {'nonInteraction': 1});
				</script>
			</div><!--/#searchresults-->
		<?php endif; ?>
	</div><!--/columns-->

	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>