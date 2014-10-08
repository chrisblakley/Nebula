<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div class="row">
	
	<div class="eleven columns">
		<?php the_breadcrumb(); ?>
		<article id="post-0" class="post error404 not-found" role="main">
			<h1>Not Found</h1>
			<p>The page you requested could not be found.</p>
			
			<?php get_search_form(); echo '<script>document.getElementById(\'s\') && document.getElementById(\'s\').focus();</script>'.PHP_EOL; ?>
		</article>
	</div><!--/columns-->
	
	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<script>
	if ( document.referrer.length ) {
		nebula_event('404 Not Found', 'Request: ' + document.URL, 'Referrer: ' + document.referrer, {'nonInteraction': 1});
	} else {
		nebula_event('404 Not Found', 'Request: ' + document.URL, 'No Referrer or Unknown', {'nonInteraction': 1});
	}
</script>

<?php get_footer(); ?>