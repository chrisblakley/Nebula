<?php
/**
 * The template for displaying Tag Archive pages.
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div class="ten columns">
			<? the_breadcrumb(); ?>
			<h1><?php printf( 'Tag Archives: %s', '' . single_tag_title( '', false ) . '' ); ?></h1>
			<?php get_template_part( 'loop', 'tag' ); ?>
		</div><!--/columns-->
		<div class="five columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>
