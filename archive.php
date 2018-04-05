<?php
	/**
	 * The template for displaying Archive pages.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		exit;;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<section id="bigheadingcon">
	<div class="custom-color-overlay"></div>

	<?php if ( get_theme_mod('menu_position', 'over') === 'over' ): ?>
		<?php get_template_part('inc/navigation'); ?>
	<?php endif; ?>

	<div class="container title-desc-con">
		<div class="row">
			<div class="col">
				<?php if ( have_posts() ){ the_post(); } //Queue the first post, then reset before running the loop. ?>
				<h1 class="page-title">
					<?php if ( is_day() ): ?>
						<?php
							//header('Location: ' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/');
							//exit;
						?>
						<i class="far fa-fw fa-calendar"></i> <?php echo get_the_date(); ?>
					<?php elseif ( is_month() ): ?>
						<i class="far fa-fw fa-calendar"></i> <?php echo get_the_date('F Y'); ?>
					<?php elseif ( is_year() ): ?>
						<i class="far fa-fw fa-calendar"></i> <?php echo get_the_date('Y'); ?>
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
			<div class="col-md" role="main">
				<?php get_template_part('loop', 'archive'); ?>
			</div><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</div>

<?php get_footer(); ?>