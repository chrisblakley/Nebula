<?php
/**
 * The template for displaying 404 pages (Not Found).
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
			<article id="post-0" class="post error404 not-found" role="main">
				<h1>Not Found</h1>
				<p>The page you requested could not be found.</p>

				<?php get_search_form(); echo '<script>document.getElementById(\'s\') && document.getElementById(\'s\').focus();</script>' . PHP_EOL; ?>
			</article>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<script>
	if ( document.referrer.length ) {
		ga('send', 'event', '404 Not Found', 'Referrer: ' + document.referrer, {'nonInteraction': 1});
	} else {
		ga('send', 'event', '404 Not Found', 'No Referrer (or Unknown)', {'nonInteraction': 1});
	}
</script>

<?php get_footer(); ?>

<?php do_action('nebula_footer'); ?>