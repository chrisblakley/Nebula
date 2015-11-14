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

<div id="heroslidercon">
	<div class="herobgcolor"></div>
	<div class="nebulashadow inner-top bulging"></div>
	<div class="row">
		<div class="sixteen columns">
			<h1><?php echo get_bloginfo('name'); ?></h1>
			<?php if ( get_bloginfo('description') != '' ): ?>
				<h2><?php echo get_bloginfo('description'); ?></h2>
			<?php endif; ?>
			<div class="text-center"><?php nebula_hero_search(); ?></div>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="nebulashadow inner-bottom bulging"></div>
</div><!--/heroslidercon-->

<div class="container fullcontentcon">
	<div class="row">
		<div class="eleven columns">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<?php the_content(); ?>

						<?php if ( current_user_can('manage_options') ): ?>
							<div class="container entry-manage">
								<div class="row">
									<hr />
									<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
									<hr />
								</div>
							</div>
						<?php endif; ?>
					</div><!-- .entry-content -->
				</article><!-- #post-## -->
			<?php endwhile; ?>
		</div><!--/columns-->
		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>