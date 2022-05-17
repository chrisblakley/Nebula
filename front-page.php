<?php
	/**
	 * The template for displaying the static front page.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<?php nebula()->timer('Front Page Template'); ?>
<?php if ( get_theme_mod('nebula_hero', true) ): ?>
	<section id="hero-section" class="nebulashadow inner-top inner-bottom" aria-label="hero">
		<?php if ( get_theme_mod('nebula_hero_overlay_color') || get_theme_mod('nebula_hero_overlay_opacity') ): ?>
			<div class="custom-color-overlay"></div>
		<?php else: ?>
			<div class="nebula-color-overlay"></div>
		<?php endif; ?>

		<?php if ( get_theme_mod('menu_position', 'over') === 'over' ): ?>
			<?php get_template_part('inc/navigation'); ?>
		<?php endif; ?>

		<div id="hero-content" class="container">
			<div class="row">
				<div class="col">
					<?php if ( get_theme_mod('nebula_show_hero_title', true) ): ?>
						<h1><?php echo ( get_theme_mod('nebula_hero_custom_title') )? get_theme_mod('nebula_hero_custom_title') : get_bloginfo('name'); ?></h1>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_show_hero_description', true) ): ?>
						<h2><?php echo ( get_theme_mod('nebula_hero_custom_description') )? get_theme_mod('nebula_hero_custom_description') : get_bloginfo('description'); ?></h2>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_search', true) ): ?>
						<?php echo nebula()->hero_search(); ?>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_fg_image') ): ?>
						<?php if ( get_theme_mod('nebula_hero_fg_image_link') ): ?>
							<a href="<?php echo get_theme_mod('nebula_hero_fg_image_link'); ?>">
						<?php endif; ?>

						<img src="<?php echo get_theme_mod('nebula_hero_fg_image'); ?>" alt="Hero Foreground" />

						<?php if ( get_theme_mod('nebula_hero_fg_image_link') ): ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_youtube_id') ): ?>
						<?php $youtube_data = nebula()->video_meta('youtube', get_theme_mod('nebula_hero_youtube_id')); ?>
						<div class="ratio ratio-16x9">
							<iframe class="youtube" title="<?php echo $youtube_data['title']; ?>" width="560" height="315" src="//www.youtube.com/embed/<?php echo $youtube_data['id']; ?>?wmode=transparent&enablejsapi=1&rel=0"></iframe>
						</div>
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->

			<?php if ( is_active_sidebar('hero-widget-area') ): ?>
				<div id="hero-widget-area" class="row justify-content-center">
					<?php dynamic_sidebar('hero-widget-area'); ?>
				</div><!--/row-->
			<?php endif; ?>

			<?php if ( get_theme_mod('nebula_hero_cta_btn_1_text') || get_theme_mod('nebula_hero_cta_btn_2_text') ): ?>
				<div class="row hero-cta">
					<div class="col">
						<?php if ( get_theme_mod('nebula_hero_cta_btn_1_text') && get_theme_mod('nebula_hero_cta_btn_1_url') ): ?>
							<a class="btn btn-lg btn-brand" href="<?php echo get_theme_mod('nebula_hero_cta_btn_1_url'); ?>"><?php echo get_theme_mod('nebula_hero_cta_btn_1_text'); ?></a>
						<?php endif; ?>

						<?php if ( get_theme_mod('nebula_hero_cta_btn_2_text') && get_theme_mod('nebula_hero_cta_btn_2_url') ): ?>
							<a class="btn btn-lg btn-light ms-4" href="<?php echo get_theme_mod('nebula_hero_cta_btn_2_url'); ?>"><?php echo get_theme_mod('nebula_hero_cta_btn_2_text'); ?></a>
						<?php endif; ?>
					</div><!--/col-->
				</div><!--/row-->
			<?php endif; ?>
		</div><!--/container-->
	</section>
<?php endif; ?>

<?php get_template_part('inc/nebula_drawer'); ?>

<section id="content-section">
	<div class="container">
		<div class="row">
			<main id="top" class="col" role="main">
				<?php if ( get_option('show_on_front') === 'posts' ): //"Your latest posts" ?>
					<?php get_template_part('loop', 'index'); ?>
				<?php else: //"A static page" ?>
					<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<div class="entry-content">
								<?php the_content(); ?>
							</div>
						</article>
					<?php endwhile; ?>
				<?php endif; ?>
			</main><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</section>
<?php nebula()->timer('Front Page Template', 'end'); ?>

<?php get_footer(); ?>