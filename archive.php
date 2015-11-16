<?php
/**
 * The template for displaying Archive pages.
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
				<?php if ( have_posts() ){ the_post(); } //Queue the first post, then reset before running the loop. ?>
				<h1 class="page-title">
					<?php if ( is_day() ): ?>
						<?php //header('Location: ' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/'); ?>
						<i class="fa fa-fw fa-calendar-o"></i> <?php echo get_the_date(); ?>
					<?php elseif ( is_month() ): ?>
						<i class="fa fa-fw fa-calendar-o"></i> <?php echo get_the_date('F Y'); ?>
					<?php elseif ( is_year() ): ?>
						<i class="fa fa-fw fa-calendar-o"></i> <?php echo get_the_date('Y'); ?>
					<?php else: ?>
						Archives
					<?php endif; ?>
				</h1>
				<?php rewind_posts(); //Reset the queue before running the loop. ?>
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
			<?php get_template_part('loop', 'archive'); ?>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>