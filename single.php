<?php
/**
 * The Template for displaying all single posts.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

if ( get_post_format() ){
	get_template_part('format', get_post_format());
	exit;
}

do_action('nebula_preheaders');
get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr />
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">
		<div class="eleven columns">
			<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php if ( has_post_thumbnail() ): ?>
						<?php the_post_thumbnail(); ?>
					<?php endif; ?>

					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-meta">
						<?php nebula_meta('on'); ?> <?php nebula_meta('by', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('tags'); ?>
					</div>

					<div class="entry-social">
						<?php nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), is_dev()); ?>
					</div>

					<div class="entry-content">
						<?php the_content(); ?>

						<div class="row prevnextcon">
							<?php if ( get_previous_post_link() ): ?>
								<div class="<?php echo ( get_next_post_link() )? 'eight' : 'sixteen'; ?> columns prev-link-con">
									<p class="prevnext-post-heading prev-post-heading">Previous Post</p>
		                        	<div class="prevnext-post-link prev-post-link"><?php previous_post_link(); ?></div>
								</div><!--/columns-->
							<?php endif; ?>

							<?php if ( get_next_post_link() ): ?>
								<div class="<?php echo ( get_previous_post_link() )? 'eight' : 'sixteen'; ?> columns next-link-con">
									<p class="prevnext-post-heading next-post-heading">Next Post</p>
		                        	<div class="prevnext-post-link next-post-link"><?php next_post_link(); ?></div>
								</div><!--/columns-->
							<?php endif; ?>
						</div><!--/row-->
					</div><!-- .entry-content -->
				</article><!-- #post-## -->

				<?php comments_template(); ?>
			<?php endwhile; ?>
		</div><!--/columns-->
		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>