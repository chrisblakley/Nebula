<?php
/**
 * The template for displaying Author Archive pages.
 */

get_header(); ?>

<div class="row">
	<div class="eleven columns">

		<?php if ( have_posts() ) { the_post(); } //Queue the first post then reset it before the loop. ?>
			<h1>Author Archives: <a href="<?php get_author_posts_url( get_the_author_meta('ID') ); ?>"><?php echo get_the_author(); ?></a></h1>
		<?php
		
		if ( get_the_author_meta('description') ) : ?>
		
			<?php echo get_avatar( get_the_author_meta('user_email'), apply_filters('boilerplate_author_bio_avatar_size', 60) ); //Update author bio avatar image name ?>
			<h2>About <?php echo get_the_author(); ?></h2>
			<?php the_author_meta('description'); ?>
		
		<?php endif; ?>
		
		<?php
			rewind_posts();
			get_template_part('loop', 'author');
		?>

	</div><!--/columns-->
	
	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>