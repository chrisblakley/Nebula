<?php
/**
 * The template for displaying Search Results pages.
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div class="ten columns">
			<? the_breadcrumb(); ?>
			<?php if ( have_posts() ) : ?>
				<h1><?php printf( __( 'Search Results', 'boilerplate' ), '' . get_search_query() . '' ); ?></h1>
				<?php get_search_form(); ?>
			<?php else : ?>
				<h1><?php _e( 'No Results Found', 'boilerplate' ); ?></h1>
				<?php get_search_form(); ?>
			<?php endif; ?>
			<?php if ( have_posts() ) : ?>
				<?php get_template_part( 'loop', 'search' ); ?>
			<?php else : ?>
				<p><?php _e( 'Your search criteria returned 0 results.', 'boilerplate' ); ?></p>
			<?php endif; ?>
		</div><!--/columns-->
		<div class="five columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>