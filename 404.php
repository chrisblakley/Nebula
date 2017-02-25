<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	http_response_code(403);
	die();
}

do_action('nebula_preheaders');
nebula_vdb_increment_visitor_data('page_not_found');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container title-desc-con">
		<div class="row">
			<div class="col">
				<h1 class="page-title">Not Found</h1>
				<p>The page you requested could not be found.</p>
			</div><!--/cols-->
		</div><!--/row-->
	</div><!--/container-->

	<div id="breadcrumb-section" class="full inner dark">
		<div class="container">
			<div class="row">
				<div class="col">
					<?php nebula_breadcrumbs(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div><!--/breadcrumb-section-->
</section>

<?php get_template_part('inc/nebula_drawer'); ?>

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<article id="post-0" class="post error404 not-found">
					<?php echo nebula_search_form(); ?>

					<?php if ( !empty($error_query) && $error_query->have_posts() ): //$error_query is defined in nebula_functions.php ?>
						<div id="error-page-suggestions">
							<h2>Suggestions</h2>
							<?php while ( $error_query->have_posts() ): ?>
								<?php $error_query->the_post(); ?>

								<h3 class="suggestion-title entry-title">
									<?php if ( strpos(get_permalink(), $slug_keywords) ): ?>
										<i class="fa fa-fw fa-star" title="Exact match"></i>
									<?php endif; ?>

									<a class="internal-suggestion" href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
						    <?php endwhile; ?>
							<p><a href="<?php echo home_url('/'); ?>?s=<?php echo str_replace('-', '+', $slug_keywords); ?>">View all results &raquo;</a></p>
						</div>
					<?php endif; ?>
					<?php wp_reset_query(); ?>
				</article>
			</div><!--/col-->
			<div class="col-md-3 offset-md-1">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div>

<script>
	if ( document.referrer.length ) {
		ga('send', 'event', '404 Not Found', 'Referrer: ' + document.referrer, {'nonInteraction': true});
	} else {
		ga('send', 'event', '404 Not Found', 'No Referrer (or Unknown)', {'nonInteraction': true});
	}
</script>

<?php get_footer(); ?>