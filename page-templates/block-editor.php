<?php
	/**
	 * Template Name: Block Editor
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<section id="bigheadingcon">
	<div class="custom-color-overlay"></div>

	<?php get_template_part('inc/navigation'); ?>
</section>

<section id="content-section">
	<main id="top" role="main">
		<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<?php if ( has_post_thumbnail() ): ?>
						<div class="block-featured-image alignwide">
							<?php the_post_thumbnail(); ?>
						</div>
					<?php endif; ?>

					<div class="block-breadcrumbs alignwide">
						<?php nebula()->breadcrumbs(); //maybe this becomes a block? ?>
					</div>

					<h1 class="entry-title"><?php echo esc_html(get_the_title()); ?></h1>

					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</main><!--/col-->
</section>

<?php get_footer(); ?>