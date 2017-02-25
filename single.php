<?php
/**
 * The Template for displaying all single posts.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	http_response_code(403);
	die();
}

if ( get_post_format() ){
	get_template_part('format', get_post_format());
	exit;
}

do_action('nebula_preheaders');
get_header(); ?>

<div id="breadcrumb-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php nebula_breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/breadcrumb-section-->

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
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
									<div class="<?php echo ( get_next_post_link() )? 'col-md-6' : 'col-md-12'; ?> prev-link-con">
										<p class="prevnext-post-heading prev-post-heading">Previous Post</p>
			                        	<div class="prevnext-post-link prev-post-link"><?php previous_post_link(); ?></div>
									</div><!--/col-->
								<?php endif; ?>

								<?php if ( get_next_post_link() ): ?>
									<div class="<?php echo ( get_previous_post_link() )? 'col-md-6' : 'col-md-12'; ?> next-link-con">
										<p class="prevnext-post-heading next-post-heading">Next Post</p>
			                        	<div class="prevnext-post-link next-post-link"><?php next_post_link(); ?></div>
									</div><!--/col-->
								<?php endif; ?>
							</div><!--/row-->
						</div>
					</article>

					<?php comments_template(); ?>
				<?php endwhile; ?>
			</div><!--/col-->
			<div class="col-md-3 offset-md-1">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>