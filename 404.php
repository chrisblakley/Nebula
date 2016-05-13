<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<div id="breadcrumb-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php nebula_breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/breadcrumb-section-->

<div class="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<article id="post-0" class="post error404 not-found" role="main">
					<h1 class="page-title">Not Found</h1>
					<p>The page you requested could not be found.</p>
					<?php echo nebula_search_form(); ?>
				</article>
			</div><!--/col-->
			<div class="col-md-4">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div>

<script>
	ga('set', gaCustomDimensions['sessionNotes'], sessionNote('HTTP 404 Page'));
	if ( document.referrer.length ) {
		ga('send', 'event', '404 Not Found', 'Referrer: ' + document.referrer, {'nonInteraction': 1});
	} else {
		ga('send', 'event', '404 Not Found', 'No Referrer (or Unknown)', {'nonInteraction': 1});
	}
</script>

<?php get_footer(); ?>