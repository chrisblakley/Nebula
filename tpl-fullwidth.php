<?php
/**
 * Template Name: Full Width
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
					
					<?php wp_link_pages( array( 'before' => '' . 'Pages:', 'after' => '' ) ); ?>
					<?php if ( current_user_can('manage_options') ) : ?>
						<div class="container entry-manage">
							<div class="row">
								<hr/>
								<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
								<hr/>
							</div>
						</div>
					<?php endif; ?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->
			
			<?php get_template_part('comments'); ?>
			
		<?php endwhile; ?>
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>