<?php
/**
 * The template for displaying the front page.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<div id="hero-section" class="nebulashadow inner-top inner-bottom">
	<div class="herobgcolor"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1><?php echo get_bloginfo('name'); ?></h1>
				<?php if ( get_bloginfo('description') != '' ): ?>
					<h2><?php echo get_bloginfo('description'); ?></h2>
				<?php endif; ?>
				<?php echo nebula_hero_search(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/hero-section-->

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div><!-- .entry-content -->
					</article>
				<?php endwhile; ?>
			</div><!--/col-->
			<div class="col-md-3 col-md-offset-1">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>