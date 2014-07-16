<?php
/**
 * Template Name: Homepage
 */

get_header(); ?>

<div id="heroslidercon">
	<div class="nebulashadow inner-top"></div>
	<div class="valign row" style="height: 100%;">
		<div>
			<h3>Nebula</h3>
			<h4>Lorem ipsum dolor sit amet</h4>
		</div>
	</div>
	<div class="nebulashadow inner-bottom"></div>
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