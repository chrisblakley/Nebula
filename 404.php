<?php
	/**
	 * The template for displaying 404 pages (Not Found).
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

<?php nebula()->timer('404 Template'); ?>
<section id="content-section">
	<div class="container">
		<div class="row">
			<div class="col">
				<?php nebula()->breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
		<div class="row">
			<main id="top" class="col" role="main">
				<article id="post-0" class="post error404 not-found">
					<?php if ( get_theme_mod('title_location') === 'content' ): ?>
						<h1 class="page-title"><?php _e('Not Found', 'nebula'); ?></h1>
						<p><?php _e('The page you requested could not be found.', 'nebula'); ?></p>
					<?php endif; ?>

					<?php echo nebula()->search_form(); ?>
					<?php if ( is_string(nebula()->slug_keywords) && !empty(nebula()->error_query) && nebula()->error_query->have_posts() ): //Check if the error query (from /libs/Functions.php) found any matches ?>
						<div id="error-page-suggestions">
							<h2><?php _e('Suggestions', 'nebula'); ?></h2>
							<?php while ( nebula()->error_query->have_posts() ): ?>
								<?php nebula()->error_query->the_post(); ?>

								<h3 class="suggestion-title entry-title">
									<i class="fa fa-fw fa-chevron-right"></i>
									<?php if ( strpos(get_permalink(), nebula()->slug_keywords) ): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8 ?>
										<strong>
									<?php endif; ?>

									<a class="internal-suggestion" href="<?php echo get_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>

									<?php if ( strpos(get_permalink(), nebula()->slug_keywords) ): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8 ?>
										</strong>
									<?php endif; ?>
								</h3>
							<?php endwhile; ?>
							<p><a href="<?php echo home_url('/'); ?>?s=<?php echo str_replace('-', '+', nebula()->slug_keywords); ?>"><?php _e('View all results', 'nebula'); ?> &raquo;</a></p>
						</div>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>

					<?php do_action('nebula_404_content'); ?>
				</article>
			</main><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</section>
<?php nebula()->timer('404 Template', 'end'); ?>

<?php get_footer(); ?>