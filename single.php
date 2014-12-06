<?php
/**
 * The Template for displaying all single posts.
 */

if ( !defined('ABSPATH') ) { //Log and redirect if accessed directly
	ga_send_event('Direct Template Access', 'Template: Single', basename($_SERVER['PHP_SELF']));
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

<div class="row">

	<div class="eleven columns">
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>

				<div class="entry-meta">
					<hr/>
		        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?> <span class="nebulasocialcon"><?php nebula_meta('social', 0); ?></span>
		        	<hr/>
		        </div>

				<div class="entry-content">
					<?php the_content(); ?>

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

	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->

</div><!--/row-->

<?php get_footer(); ?>