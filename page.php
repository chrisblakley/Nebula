<?php
/**
 * The template for displaying all pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container">
		<div class="row">
			<div class="sixteen columns">
				<h1 class="page-title"><?php the_title(); ?></h1>
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="breadcrumbbar">
	<div class="row">
		<div class="sixteen columns">
			<?php the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
	<hr />
</div><!--/container-->

<div class="container fullcontentcon">
	<div class="row">
		<div class="eleven columns">
			<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php if ( has_post_thumbnail() ): ?>
						<?php the_post_thumbnail(); ?>
					<?php endif; ?>

					<div class="entry-social">
						<?php nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), is_dev()); ?>
					</div>

					<div class="entry-content">
						<?php the_content(); ?>
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