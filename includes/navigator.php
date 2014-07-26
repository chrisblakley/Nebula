<?php query_posts( array('post_type' => 'page', 'pagename' => $requested_page, 'showposts' => 1) ); ?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<?php echo get_permalink(); ?>
	<?php //return get_permalink(); ?>
<?php endwhile; ?>
<?php wp_reset_query(); ?>