<?php
	/*
		Note: Consider using WP_Query as shown in the basic query example.
		Also note: Using Bootstrap, creating a new row may not be necessary. You could continue to repeat columns with no adverse affects.
	*/
?>

<?php query_posts(array('category_name' => 'Documentation', 'showposts' => 4, 'paged' => get_query_var('paged'))); ?>
<?php if ( have_posts() ): ?>
	<?php $count = 0; ?>
	<div class="row multi-column-query">
		<?php while ( have_posts() ): the_post(); ?>
	        <?php if ( $count%2 == 0 && $count != 0 ): //Not exactly necessary with Bootstrap, but doesn't hurt. ?>
	            </div><!--/row-->
	            <div class="row multi-column-query">
	        <?php endif; ?>

	        <div class="col-md-6">
			    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			        <h2 class="news-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h2>

			        <div class="entry-meta">
			        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
			        </div>

			        <div class="entry-content">
			            <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
			        </div>
			    </article>
			</div><!--/col-->

	        <?php $count++; ?>
	    <?php endwhile; ?>
	</div><!--/row-->

	<?php if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ): ?>
		<?php wp_pagenavi(); ?>
	<?php else: ?>
		<?php
			global $wp_query;
			$big = 999999999; //An unlikely integer
			echo '<div class="wp-pagination">';
				echo paginate_links(array(
					'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
					'format' => '?paged=%#%',
					'current' => max(1, get_query_var('paged')),
					'total' => $wp_query->max_num_pages
				));
			echo '</div>';
		?>
	<?php endif; ?>

	<?php wp_reset_query(); ?>
<?php endif; ?>