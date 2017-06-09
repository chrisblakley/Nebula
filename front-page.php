<?php
/**
 * The template for displaying the front page.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	http_response_code(403);
	die();
}

do_action('nebula_preheaders');
get_header(); ?>

<?php if ( !get_theme_mod('nebula_hide_hero') ): ?>
	<div id="hero-section" class="nebulashadow inner-top inner-bottom">
		<div class="nebula-color-overlay"></div>

		<div class="container">
			<div class="row">
				<div class="col">
					<?php if ( get_bloginfo('name') && !get_theme_mod('nebula_hide_blogname') ): ?>
						<h1><?php echo get_bloginfo('name'); ?></h1>
					<?php endif; ?>

					<?php if ( get_bloginfo('description') != '' && !get_theme_mod('nebula_hide_blogdescription') ): ?>
						<h2><?php echo get_bloginfo('description'); ?></h2>
					<?php endif; ?>

					<?php if ( !get_theme_mod('nebula_hide_hero_search') ): ?>
						<?php echo nebula()->hero_search(); ?>
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
				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php endwhile; ?>
			</div><!--/col-->
			<div class="col-md-3 offset-md-1" role="complementary">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>