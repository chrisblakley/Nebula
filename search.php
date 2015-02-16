<?php
/**
 * The template for displaying Search Results pages.
 */

if ( !defined('ABSPATH') ) { //Log and redirect if accessed directly
	ga_send_event('Security Precaution', 'Direct Template Access Prevention', 'Template: ' . end(explode('/', $template)), basename($_SERVER['PHP_SELF']));
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")));
	exit;
}

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="row fullcontentcon">

	<div class="ten columns">
		<?php if ( have_posts() ) : ?>
			<h1>Search Results <?php get_search_query(); ?></h1>
			<?php get_search_form(); ?>

			<p>Your search criteria returned
			<?php
				$search_results = &new WP_Query("s=$s&showposts=-1");
				echo $search_results->post_count . ' results.';
				wp_reset_query();
			?>
			</p>
			<?php get_template_part('loop', 'search'); ?>
		<?php else : ?>
			<h1>No Results Found</h1>
			<?php get_search_form(); ?>

			<p>Your search criteria returned 0 results.</p>

			<script>
				var badSearchTerm = jQuery('#s').val();
				ga('send', 'event', 'Internal Search', 'No Results', badSearchTerm, {'nonInteraction': 1});
			</script>
		<?php endif; ?>
	</div><!--/columns-->

	<div class="five columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->

</div><!--/row-->

<?php get_footer(); ?>