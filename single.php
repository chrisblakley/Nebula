<?php
	/**
	 * The template for displaying all single posts.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		exit;
	}

	if ( get_post_format() ){
		get_template_part('format', get_post_format());
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<?php get_template_part('inc/headercontent'); ?>
<?php get_template_part('inc/nebula_drawer'); ?>

<section id="content-section">
	<div class="container">
		<div class="row">
			<div class="col">
				<?php nebula()->breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
		<div class="row">
			<main id="top" class="col" role="main">
				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( has_post_thumbnail() && get_theme_mod('featured_image_location') === 'content' ): ?>
							<?php the_post_thumbnail(); ?>
						<?php endif; ?>

						<?php if ( get_theme_mod('title_location') === 'content' ): ?>
							<h1 class="entry-title"><?php the_title(); ?></h1>

							<div class="entry-meta">
								<?php echo nebula()->post_date(); ?> <?php echo nebula()->post_author(); ?> <?php echo nebula()->post_categories(); ?> <?php echo nebula()->post_tags(); ?>
							</div>
						<?php endif; ?>

						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>

					<?php if ( is_active_sidebar('single-post-widget-area') ): ?>
						<div id="single-post-widget-area">
							<?php dynamic_sidebar('single-post-widget-area'); ?>
						</div>
					<?php endif; ?>

					<?php comments_template(); ?>
				<?php endwhile; ?>
			</main><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</section>

<?php get_footer(); ?>