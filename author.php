<?php
/**
 * The template for displaying Author Archive pages.
 */

get_header(); ?>

<?php if ( have_posts() ) { the_post(); } //Queue the first post then reset it before the loop. ?>
	<h1>Author Archives: <a href="<?php get_author_posts_url( get_the_author_meta('ID') ); ?>"><?php echo get_the_author(); ?></a></h1>
<?php

if ( get_the_author_meta('description') ) : ?>

	<?php echo get_avatar( get_the_author_meta('user_email'), apply_filters('boilerplate_author_bio_avatar_size', 60) ); //Update author bio avatar image name ?>
	<h2>About <?php echo get_the_author(); //@TODO: Need to echo? ?></h2>
	<?php the_author_meta('description'); ?>

<?php endif; ?>

<?php
	rewind_posts();
	get_template_part('loop', 'author');
?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
