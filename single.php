<?php
	/**
	 * The Template for displaying all single posts.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		http_response_code(403);
		die();
	}

	if ( get_post_format() ){
		get_template_part('format', get_post_format());
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<section id="bigheadingcon">
	<div class="custom-color-overlay"></div>

	<?php if ( get_theme_mod('menu_position', 'over') === 'over' ): ?>
		<?php get_template_part('inc/navigation'); ?>
	<?php endif; ?>

	<?php if ( get_theme_mod('title_location', 'hero') === 'hero' ): ?>
		<div class="container title-desc-con">
			<div class="row">
				<div class="col">
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-meta">
						<?php nebula()->post_meta('on'); ?> <?php nebula()->post_meta('by', 0); ?> <?php nebula()->post_meta('cat'); ?> <?php nebula()->post_meta('tags'); ?>
					</div>
				</div><!--/cols-->
			</div><!--/row-->
		</div><!--/container-->

		<div id="breadcrumb-section" class="full inner dark">
			<div class="container">
				<div class="row">
					<div class="col">
						<?php nebula()->breadcrumbs(); ?>
					</div><!--/col-->
				</div><!--/row-->
			</div><!--/container-->
		</div><!--/breadcrumb-section-->
	<?php endif; ?>
</section>

<?php get_template_part('inc/nebula_drawer'); ?>

<div id="content-section">
	<div class="container">
		<?php if ( get_theme_mod('title_location') === 'content' ): ?>
			<div id="breadcrumb-section" class="row">
				<div class="col">
					<?php nebula()->breadcrumbs(); ?>
				</div><!--/col-->
			</div><!--/row-->
		<?php endif; ?>
		<div class="row">
			<div class="col-md" role="main">
				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( has_post_thumbnail() && get_theme_mod('featured_image_location') === 'content' ): ?>
							<?php the_post_thumbnail(); ?>
						<?php endif; ?>

						<?php if ( get_theme_mod('title_location') === 'content' ): ?>
							<h1 class="entry-title"><?php the_title(); ?></h1>

							<div class="entry-meta">
								<?php nebula()->post_meta('on'); ?> <?php nebula()->post_meta('by', 0); ?> <?php nebula()->post_meta('cat'); ?> <?php nebula()->post_meta('tags'); ?>
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
			</div><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>