<?php
/**
 * Template Name: Homepage
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_header');
get_header(); ?>

<div id="heroslidercon">
	<div class="herobgcolor"></div>
	<div class="nebulashadow inner-top bulging"></div>
	<div class="valign row" style="height: 100%; text-align: center;">
		<div>
			<h1><?php echo get_bloginfo('name'); ?></h3>
			<h2><?php echo (get_bloginfo('description')) ? get_bloginfo('description') : 'Lorem Ipsum Dolor Sit Amet'; ?></h4>
			<?php nebula_hero_search(); ?>
		</div>
	</div>
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
									<hr/>
									<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
									<hr/>
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
<?php do_action('nebula_footer'); ?>