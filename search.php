<?php
/**
 * The template for displaying Search Results pages.
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
						<div class="fourteen columns centered searchcon">
							<?php if ( have_posts() ) : ?>
								<h1>Search Results <?php get_search_query(); ?></h1>
								<?php get_search_form(); echo '<script>jQuery("#searchform input#s").focus();</script>' . PHP_EOL; ?>
							<?php else : ?>
								<h1>No Results Found</h1>

								<script>
									var badSearchTerm = jQuery('#searchform input#s').val();
									ga('send', 'event', 'Internal Search', 'No Results', badSearchTerm);
								</script>
							<?php endif; ?>

							<?php if ( have_posts() ) : ?>
								<p>Your search criteria returned
								<?php
									$search_results = &new WP_Query("s=$s&showposts=-1");
									echo $search_results->post_count . ' results.';
									wp_reset_query();
								?>
								</p>
								<?php get_template_part('loop', 'search'); ?>
							<?php else : ?>
								<p>Your search criteria returned 0 results.</p>
								<?php get_search_form(); echo '<script>jQuery("#searchform input#s").focus();</script>' . PHP_EOL; ?>

								<?php //@TODO: List a few popular posts here. ?>

							<?php endif; ?>
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

<?php get_footer(); ?>