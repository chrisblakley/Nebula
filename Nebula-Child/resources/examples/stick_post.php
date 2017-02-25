<div class="row">
	<div class="col-md-12 sticky-con">
		<?php
   			$sticky = get_option('sticky_posts');
   			$args = array(
   				'posts_per_page' => 1,
   				'post__in'  => $sticky,
   				'caller_get_posts' => 1
   			);
   			query_posts($args); //Note: Use WP_Query if possible
   		?>
		<?php if ( $sticky[0] ): ?>
			<ul>
				<?php while ( have_posts() ): the_post(); ?>
					<li class="sticky-post">
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<h2 class="news-title entry-title sticky-title"><i class="fa fa-thumb-tack"></i> <a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>

							<div class="entry-meta">
								<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
							</div>

							<div class="entry-content sticky-content">
								<?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
							</div>
						</article>
					</li>
				<?php endwhile; ?>
				<?php wp_reset_query(); ?>
			</ul>
		<?php endif; ?>
	</div><!--/col-->
</div><!--/row-->