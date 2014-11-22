<?php
/**
 * The main template file.
 */

get_header(); ?>

<?php get_template_part( 'loop', 'index' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>