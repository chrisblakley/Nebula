<div class="row">
	<div class="sixteen columns">

		<?php query_posts( array('showposts' => 4, 'paged' => get_query_var('paged')) ); ?>
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		        <h2 class="news-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>

		        <div class="entry-meta">
		        	<hr/>
		        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
		        	<hr/>
		        </div>

		        <div class="entry-content">
		            <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>

		            <?php if ( current_user_can('manage_options') ) : ?>
						<div class="container entry-manage">
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
									<hr/>
								</div><!--/columns-->
							</div>
						</div>
					<?php endif; ?>

		        </div><!-- .entry-content -->
		    </article><!-- #post-## -->

	    <?php endwhile; ?>

	</div><!--/columns-->
</div><!--/row-->

<?php if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ) : ?>
	<?php wp_pagenavi(); ?>
<?php else : ?>
	<?php
		global $wp_query;
		$big = 999999999; //An unlikely integer
		echo '<div class="wp-pagination">' . paginate_links(array(
			'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
			'format' => '?paged=%#%',
			'current' => max(1, get_query_var('paged')),
			'total' => $wp_query->max_num_pages
		)) . '</div>';
	?>
<?php endif; ?>

<?php wp_reset_query(); ?>