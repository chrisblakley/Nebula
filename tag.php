<?php
	/**
	 * The template for displaying Tag Archive pages.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		exit;
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
				<h1 class="page-title"><i class="fas fa-fw fa-tag"></i> <?php echo single_tag_title('', false); ?></h1>
				<div class="page-meta"><?php echo tag_description(); ?></div>
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
				<?php get_template_part('loop', 'tag'); ?>
			</div><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>