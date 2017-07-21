<?php
/**
 * The template for displaying the static front page.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	http_response_code(403);
	die();
}

do_action('nebula_preheaders');
get_header(); ?>

<?php if ( get_theme_mod('nebula_hero', true) ): ?>
	<div id="hero-section" class="nebulashadow inner-top inner-bottom">
		<?php if ( get_theme_mod('nebula_hero_overlay_color') || get_theme_mod('nebula_hero_overlay_opacity') ): ?>
			<?php
				$hero_overlay = 'style="';
				$hero_overlay .= ( get_theme_mod('nebula_hero_overlay_color') )? 'background: ' . get_theme_mod('nebula_hero_overlay_color') . ';' : '';
				$hero_overlay .= ( !is_null(get_theme_mod('nebula_hero_overlay_opacity')) )? 'opacity: ' . get_theme_mod('nebula_hero_overlay_opacity') . ';' : '';
				$hero_overlay .= 'animation: none;"';
			?>
			<div class="custom-color-overlay" <?php echo $hero_overlay; ?>></div>
		<?php else: ?>
			<div class="nebula-color-overlay"></div>
		<?php endif; ?>

		<div class="container">
			<div class="row">
				<div class="col">
					<?php $hero_text_color = ( get_theme_mod('nebula_hero_text_color') )? 'style="color:' . get_theme_mod('nebula_hero_text_color') . ';"' : ''; ?>
					<?php if ( get_theme_mod('nebula_show_hero_title', true) ): ?>
						<h1 <?php echo $hero_text_color; ?>><?php echo ( get_theme_mod('nebula_hero_custom_title') )? get_theme_mod('nebula_hero_custom_title') : get_bloginfo('name'); ?></h1>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_show_hero_description', true) ): ?>
						<h2 <?php echo $hero_text_color; ?>><?php echo ( get_theme_mod('nebula_hero_custom_description') )? get_theme_mod('nebula_hero_custom_description') : get_bloginfo('description'); ?></h2>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_search', true) ): ?>
						<?php echo nebula()->hero_search(); ?>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_fg_image') ): ?>
						<?php if ( get_theme_mod('nebula_hero_fg_image_link') ): ?>
							<a href="<?php echo get_theme_mod('nebula_hero_fg_image_link'); ?>">
						<?php endif; ?>

						<img src="<?php echo get_theme_mod('nebula_hero_fg_image'); ?>" />

						<?php if ( get_theme_mod('nebula_hero_fg_image_link') ): ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_youtube_id') ): ?>
						<?php $youtube_data = nebula()->video_meta('youtube', get_theme_mod('nebula_hero_youtube_id')); ?>
						<div class="embed-responsive embed-responsive-16by9">
							<iframe class="youtube embed-responsive-item" width="560" height="315" src="//www.youtube.com/embed/<?php echo $youtube_data['id']; ?>?wmode=transparent&enablejsapi=1&rel=0"></iframe>
						</div>
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->
			<div class="row hero-cta">
				<div class="col">
					<?php if ( get_theme_mod('nebula_hero_cta_btn_1_text') && get_theme_mod('nebula_hero_cta_btn_1_url') ): ?>
						<a class="btn btn-lg btn-primary" href="<?php echo get_theme_mod('nebula_hero_cta_btn_1_url'); ?>"><?php echo get_theme_mod('nebula_hero_cta_btn_1_text'); ?></a>
					<?php endif; ?>

					<?php if ( get_theme_mod('nebula_hero_cta_btn_2_text') && get_theme_mod('nebula_hero_cta_btn_2_url') ): ?>
						<a class="btn btn-lg btn-secondary ml-4" href="<?php echo get_theme_mod('nebula_hero_cta_btn_2_url'); ?>"><?php echo get_theme_mod('nebula_hero_cta_btn_2_text'); ?></a>
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div><!--/hero-section-->
<?php endif; ?>

<?php get_template_part('inc/nebula_drawer'); ?>

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8" role="main">
				<?php if ( get_option('show_on_front') == 'posts' ): //"Your latest posts" ?>
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
			</div><!--/col-->
			<div class="col-md-3 offset-md-1" role="complementary">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>