<?php
/**
 * Template Name: Example Split 1
 *
 * Instructions:
 *		- For posts, simply make a new post for each variation to be tested.
 *
 *		- For pages, use the following instructions:
 *		- This file needs to be updated based on the theme! Likely, you'll need to copy over the original template guts with the test variation(s).
 * 		- Duplicate this template for each variation to be tested. Rename it (above). Then, make a new page for each of those variations and select the appropriate template when publishing the page.
 *
 *		- Once the page(s) or post(s) have been made, create the Experiment in Google Analytics (Behavior > Experiments) and copy the URLs into the appropriate fields.
 *		- Then copy the script it generates, and paste it in the nebula_ga_experiments() function in functions.php (Avoid using header.php to keep it clean).
 *
 */

if ( !defined('ABSPATH') ) { //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_header');

get_header();

/* Edit the code below to match the theme, or duplicate the desired template and rename the template (above). Remember to make the desired variations to test individually on each template! */

?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">
		<div class="sixteen columns">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>

						<?php wp_link_pages( array( 'before' => '' . 'Pages:', 'after' => '' ) ); ?>
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
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>

<?php do_action('nebula_footer'); ?>