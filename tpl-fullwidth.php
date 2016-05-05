<?php
/**
 * Template Name: Full Width
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<div class="container">
	<div class="row">
		<div class="col-md-12">
			<?php the_breadcrumb(); ?>
			<hr />
		</div><!--/col-->
	</div><!--/row-->
</div><!--/container-->

<div class="fullcontentcon">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1 class="page-title"><?php the_title(); ?></h1>

						<div class="entry-social">
							<?php nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), is_dev()); ?>
						</div>

						<div class="entry-content">
							<?php the_content(); ?>
						</div><!-- .entry-content -->
					</article><!-- #post-## -->

					<?php comments_template(); ?>
				<?php endwhile; ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div>

<?php get_footer(); ?>