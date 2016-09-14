<?php
/**
 * The template for displaying Search Results pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
nebula_increment_visitor('no_search_results');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php if ( have_posts() ): ?>
					<h1 class="page-title">Search Results</h1>
					<p>
						Your search for "<?php echo get_search_query(); ?>" returned
						<?php
							if ( file_exists(WP_PLUGIN_DIR . '/relevanssi') && $wp_query->found_posts ){ //If Relevanssi is enabled
								echo $wp_query->found_posts;
							} else {
								$search_results = new WP_Query("s=$s&showposts=-1");
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
						ga('send', 'event', 'Internal Search', 'No Results', jQuery('#s').val(), {'nonInteraction': 1});
					</script>
				<?php endif; ?>

				<?php echo nebula_search_form(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div id="breadcrumb-section" class="full">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php nebula_breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/breadcrumb-section-->

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php if ( have_posts() ): ?>
					<div id="searchresults">
						<?php get_template_part('loop', 'search'); ?>

						<?php if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ): ?>
							<?php wp_pagenavi(); ?>
						<?php else: ?>
							<?php
								global $wp_query;
								$big = 999999999; //An unlikely integer
								echo '<div class="wp-pagination">';
									echo paginate_links(array(
										'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
										'format' => '?paged=%#%',
										'current' => max(1, get_query_var('paged')),
										'total' => $wp_query->max_num_pages
									));
								echo '</div>';
							?>
						<?php endif; ?>
					</div><!--/#searchresults-->
				<?php else: ?>
					<p>No search results.</p>
				<?php endif; ?>
			</div><!--/col-->
			<div class="col-md-3 offset-md-1">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>