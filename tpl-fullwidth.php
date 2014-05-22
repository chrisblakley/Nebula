<?php
/**
 * Template Name: Full Width
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<? the_breadcrumb(); ?>
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>
						
						<?php wp_link_pages( array( 'before' => '' . 'Pages:', 'after' => '' ) ); ?>
						<?php edit_post_link( 'Edit', '<p class="edit-link">', '</p>' ); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-## -->
				<?php //comments_template( '', true ); ?>
			<?php endwhile; ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>
