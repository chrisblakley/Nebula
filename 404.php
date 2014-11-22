<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div id="maincontentareawrap" class="row">
	<div class="thirteen columns">

		<section class="sixteen colgrid">
			<div class="container">

				<div id="bcrumbscon" class="row">
					<?php the_breadcrumb(); ?>
				</div><!--/row-->

				<div class="contentbg">
					<div class="corner-left"></div>
					<div class="corner-right"></div>

					<br/><br/>

					<div class="row">
						<div class="fourteen columns centered">

							<article id="post-0" class="post error404 not-found" role="main">
								<h1>Not Found</h1>
								<p>The page you requested could not be found.</p>

								<p style="color: maroon;"><strong>I recently launched the 2015 version of Gearside Creative.</strong><br/> If you're seeing this I apologize, but the page you're looking for is around here somewhere! I have noted this error, but if you'd like to specify what you were looking for feel free to <a href="http://gearside.com/about/contact/">contact me</a>!</p>

								<?php get_search_form(); echo '<script>jQuery("#searchform input#s").focus();</script>' . PHP_EOL; ?>
							</article>

						</div><!--/columns-->
					</div><!--/row-->

				</div><!--/contentbg-->
				<div class="nebulashadow floating"></div>
			</div><!--/container-->
		</section><!--/colgrid-->

	</div><!--/columns-->
	<div class="three columns">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
</div><!--/row-->

<script>
	if ( document.referrer.length ) {
		ga('send', 'event', '404 Not Found', 'Request: ' + document.URL, 'Referrer: ' + document.referrer);
	} else {
		ga('send', 'event', '404 Not Found', 'Request: ' + document.URL, 'No Referrer or Unknown');
	}
</script>

<?php get_footer(); ?>