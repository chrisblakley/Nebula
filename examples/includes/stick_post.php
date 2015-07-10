<div class="row">
	<div class="sixteen columns sticky-con">
		<?php
   			$sticky = get_option('sticky_posts');
   			$args = array(
   				'posts_per_page' => 1,
   				'post__in'  => $sticky,
   				'caller_get_posts' => 1
   				);
   			query_posts($args);
   		?>
		<?php if ($sticky[0]) : ?>
			<ul>
				<?php while ( have_posts() ) : the_post(); ?>
					<li class="sticky-post">
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<h2 class="news-title entry-title sticky-title"><i class="fa fa-thumb-tack"></i> <a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>

							<div class="entry-meta">
								<hr/>
								<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
								<hr/>
							</div>

							<div class="entry-content sticky-content">
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
					</li>
				<?php endwhile; ?>
				<?php wp_reset_query(); ?>
			</ul>
		<?php endif; ?>
	</div><!--/columns-->
</div><!--/row-->