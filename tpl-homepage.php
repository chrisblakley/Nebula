<?php
/**
 * Template Name: Homepage
 */

get_header(); ?>

<div id="heroslidercon">
	<h3>Nebula</h3>
</div><!--/heroslidercon-->

<div class="row">
	<div class="eleven columns">
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
					
					<?php if ( current_user_can('manage_options') ) : ?>
						<hr/>
						<span class="entry-manage-edit"><?php nebula_manage('edit'); ?></span> <?php nebula_manage('modified'); ?>
						<hr/>
					<?php endif; ?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->
		<?php endwhile; ?>
	</div><!--/columns-->
	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>