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

<div id="herocon">
	<div class="herobgcolor"></div>
	<div class="nebulashadow inner-top bulging"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1><?php echo get_bloginfo('name'); ?></h1>
				<?php if ( get_bloginfo('description') != '' ): ?>
					<h2><?php echo get_bloginfo('description'); ?></h2>
				<?php endif; ?>
				<?php nebula_hero_search(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
	<div class="nebulashadow inner-bottom bulging"></div>
</div><!--/heroslidercon-->

<div class="fullcontentcon">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php if ( !empty($nebula['user']['sessions']['last']) ): ?>
					<h2>Welcome back!</h2>
				<?php endif; ?>

				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div><!-- .entry-content -->
					</article><!-- #post-## -->
				<?php endwhile; ?>
			</div><!--/col-->
			<div class="col-md-4">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/fullcontentcon-->

<?php get_footer(); ?>