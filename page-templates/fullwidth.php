<?php
	/**
	 * Template Name: Full Width
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<?php nebula()->timer('Full Width Template'); ?>
<section id="bigheadingcon">
	<div class="custom-color-overlay"></div>

	<?php if ( get_theme_mod('menu_position', 'over') === 'over' ): ?>
		<?php get_template_part('inc/navigation'); ?>
	<?php endif; ?>

	<?php if ( get_theme_mod('title_location', 'hero') === 'hero' ): ?>
		<div class="container title-desc-con">
			<div class="row">
				<div class="col">
					<h1 class="entry-title"><?php echo esc_html(get_the_title()); ?></h1>
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
	<?php endif; ?>
</section>

<?php get_template_part('inc/nebula_drawer'); ?>

<section id="content-section">
	<div class="container">
		<?php if ( get_theme_mod('title_location') === 'content' ): ?>
			<div id="breadcrumb-section" class="row">
				<div class="col">
					<?php nebula()->breadcrumbs(); ?>
				</div><!--/col-->
			</div><!--/row-->
		<?php endif; ?>
		<div class="row">
			<main id="top" class="col" role="main">
				<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>

					<?php if ( is_active_sidebar('single-post-widget-area') ): ?>
						<div id="single-post-widget-area">
							<?php dynamic_sidebar('single-post-widget-area'); ?>
						</div>
					<?php endif; ?>

					<?php comments_template(); ?>
				<?php endwhile; ?>
			</main><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</section>
<?php nebula()->timer('Full Width Template', 'end'); ?>

<?php get_footer(); ?>