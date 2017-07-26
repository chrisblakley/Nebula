<?php
/**
 * The template for displaying Archive pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	http_response_code(403);
	die();
}

do_action('nebula_preheaders');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container title-desc-con">
		<div class="row">
			<div class="col">
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
			</div><!--/cols-->
		</div><!--/row-->
	</div><!--/container-->

	<div id="breadcrumb-section" class="full inner dark">
		<div class="container">
			<div class="row">
				<div class="col">
					<?php nebula()->breadcrumbs(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div><!--/breadcrumb-section-->
</section>

<?php get_template_part('inc/nebula_drawer'); ?>

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8" role="main">
				<?php get_template_part('loop', 'archive'); ?>
				<?php wp_pagenavi(); ?>
			</div><!--/col-->
			<div class="col-md-3 offset-md-1 <?php echo ( get_theme_mod('sidebar_position') === 'left' )? 'flex-first' : ''; ?>" role="complementary">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div>

<?php get_footer(); ?>