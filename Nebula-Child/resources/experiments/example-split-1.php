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
 */

if ( !defined('ABSPATH') ) { //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header();

/* Edit the code below to match the theme, or duplicate the desired template and rename the template (above). Remember to make the desired variations to test individually on each template! */
?>

<div id="breadcrumb-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php nebula_breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/breadcrumb-section-->

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( has_post_thumbnail() ): ?>
							<?php the_post_thumbnail(); ?>
						<?php endif; ?>

						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php endwhile; ?>
			</div><!--/col-->
			<div class="col-md-4">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>
<?php do_action('nebula_footer'); ?>