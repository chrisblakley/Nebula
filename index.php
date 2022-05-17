<?php
	/**
	 * The main template file.
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<?php nebula()->timer('Index Template'); ?>
<section id="bigheadingcon">
	<div class="custom-color-overlay"></div>

	<?php if ( get_theme_mod('menu_position', 'over') === 'over' ): ?>
		<?php get_template_part('inc/navigation'); ?>
	<?php endif; ?>

	<div class="container title-desc-con">
		<div class="row">
			<div class="col">
				<h1 class="page-title"><?php echo esc_html(get_the_title()); ?></h1>
			</div><!--/cols-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<section id="content-section">
	<div class="container">
		<div class="row">
			<div class="col">
				<?php nebula()->breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
		<div class="row">
			<main id="top" class="col-md" role="main">
				<?php get_template_part('loop', 'archive'); ?>
			</main><!--/col-->

			<?php get_sidebar(); ?>
		</div><!--/row-->
	</div><!--/container-->
</section>
<?php nebula()->timer('Index Template', 'end'); ?>

<?php get_footer(); ?>