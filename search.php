<?php
/**
 * The template for displaying Search Results pages.
 */

if ( !defined('ABSPATH') ) { exit; } //Exit if accessed directly

get_header(); ?>

<div class="row">
	
	<div class="ten columns">
		<?php the_breadcrumb(); ?>
		<?php if ( have_posts() ) : ?>
			<h1>Search Results <?php get_search_query(); ?></h1>
			<?php get_search_form(); ?>
		<?php else : ?>
			<h1>No Results Found</h1>
			<?php get_search_form(); ?>
			
			<?php global $defer; global $async; ?>
			<script>
				var badSearchTerm = jQuery('#s').val();
				ga('send', 'event', 'Internal Search', 'No Results', badSearchTerm);
				Gumby.log('Sending GA event: ' + 'Internal Search', 'No Results', badSearchTerm);
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
		<?php endif; ?>
	</div><!--/columns-->
	
	<div class="five columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>