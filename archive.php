<?php
/**
 * The template for displaying Archive pages.
 */

if ( !defined('ABSPATH') ) { exit; } //Exit if accessed directly

get_header(); ?>

<div class="row">
	
	<div class="eleven columns">
		<?php if ( have_posts() ) { the_post(); } //Queue the first post, then reset before running the loop. ?>
		<h1 class="page-title">
			<?php if ( is_day() ) : ?>
				<?php //header('Location: ' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/') ; //This does not work on all servers (because it's called after headers are already sent). Uncomment to test if will work on your server. ?>
				Archive for <?php echo get_the_date(); ?>
			<?php elseif ( is_month() ) : ?>
				Archive for <?php echo get_the_date('F Y'); ?>
			<?php elseif ( is_year() ) : ?>
				Archive for <?php echo get_the_date('Y'); ?>
			<?php else : ?>
				Archives
			<?php endif; ?>
		</h1>
		<?php
			rewind_posts(); //Reset the queue before running the loop.
			get_template_part('loop', 'archive');
		?>
	</div><!--/columns-->
	
	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>