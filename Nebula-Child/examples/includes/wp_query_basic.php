<div class="row">
	<div class="col-md-12">
		<?php
			//Example "Event" post type query sorted by event time
			$args = array('post_type' => array('event'), 'meta_key' => 'event_date', 'orderby' => 'meta_value_num', 'order' => 'ASC', 'showposts' => 6, 'paged' => get_query_var('paged'));
		?>


		<?php
			/*
				Example using loop.php
				This allows all post listings to be consistent.
			*/

			//query_posts($args);
			//get_template_part('loop');
		?>


		<?php //Example using a custom loop with query_posts (avoid if possible in favor of WP_Query) ?>
		<?php query_posts($args); ?>
		<?php while ( have_posts() ): the_post(); ?>
		    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		        <?php if ( has_post_thumbnail() ): ?>
					<a href="<?php echo get_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
				<?php endif; ?>

		        <h2 class="news-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h2>

		        <div class="entry-meta">
		        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
		        </div>

		        <div class="entry-content">
		            <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
		        </div>
		    </article>
	    <?php endwhile; ?>


		<?php //Example using a custom loop with WP_Query ?>
		<?php $example_query = new WP_Query($args); ?>
		<?php //Example to get just the first post ID: $example_query->posts[0]->ID; ?>
		<?php while ( $example_query->have_posts() ): $example_query->the_post(); ?>
		    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		        <?php if ( has_post_thumbnail() ): ?>
					<a href="<?php echo get_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
				<?php endif; ?>

		        <h2 class="news-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h2>

		        <div class="entry-meta">
		        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
		        </div>

		        <div class="entry-content">
		            <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
		        </div>
		    </article>
	    <?php endwhile; ?>


		<?php
			//If paginating, Pagenavi is recommended:
			if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ){
				wp_pagenavi(); //query_posts
				//wp_pagenavi(array('query' => $cached_query)); //wp_query
			}
		?>


		<?php
			//Example using Nebula Infinite Load (see also infinite_load.php)
			//nebula_infinite_load_query($args);
		?>


		<?php wp_reset_query(); //Always reset queries! ?>
	</div><!--/col-->
</div><!--/row-->