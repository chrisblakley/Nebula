<?php
/**
 * The template for displaying all pages.
 */

if ( !defined('ABSPATH') ) { //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_header');

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">

		<div class="eleven columns">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>

						<?php if ( current_user_can('manage_options') ) : ?>
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

				<?php comments_template(); ?>

			<?php endwhile; ?>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>

<?php do_action('nebula_footer'); ?>