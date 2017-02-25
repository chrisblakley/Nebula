<div class="row">
	<div class="col-md-12">
		<?php
			//Note: You must set a new transient for each page of the query.
			$cached_query = get_transient('example_cached_query' . get_query_var('paged'));
			if ( empty($cached_query) || is_debug() ){
			    $cached_query = new WP_Query(array(
			        'post_type' => 'event',
			        'category_name' => 'concert',
			        'showposts' => 2,
			        'paged' => get_query_var('paged')
			    ));
			    set_transient('example_cached_query' . get_query_var('paged'), $cached_query, 60*60); //1 hour cache
			}
			while ( $cached_query->have_posts() ): $cached_query->the_post();
		?>
		    <div class="home-feed-item event-feed-item">
		        <h3><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
		        <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
		    </div>
		<?php endwhile; ?>

		<?php
			if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ){
				wp_pagenavi(array('query' => $cached_query));
			}
		?>
	</div><!--/col-->
</div><!--/row-->