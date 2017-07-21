<?php
/**
 * The loop that displays posts.
 */
?>

<div class="loop-section">
	<?php if ( !have_posts() ): //If there are no posts to display (such as an empty archive page). ?>
		<article id="post-0" class="post error404 not-found">
			<h2 class="entry-title">Not Found</h2>
			<div class="entry-summary">
				<p>No results were found for the requested archive.</p>
				<?php echo nebula()->search_form(); ?>
			</div>
		</article>
	<?php else: //Begin the loop. ?>
		<?php while ( have_posts() ): the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="row">
					<?php if ( has_post_thumbnail() ): ?>
						<div class="col-md-4">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
						</div><!--/col-->
					<?php endif; ?>

					<div class="col">
						<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

						<div class="entry-meta">
							<?php if ( is_search() ): ?>
								<?php //nebula()->meta('type') . ' '; //Enable this to show post types in search results. ?>
							<?php endif; ?>

							<?php if ( !in_array("page", get_post_class()) ): ?>
								<?php nebula()->post_meta('on'); ?> <?php if ( nebula()->get_option('author_bios') && !is_author() ){ nebula()->post_meta('by'); } ?> <?php nebula()->post_meta('cat'); ?> <?php nebula()->post_meta('tags'); ?>
							<?php endif; ?>
						</div>

						<?php if ( is_search() && is_plugin_active('relevanssi/relevanssi.php') && $post->relevance_score ): ?>
							<div class="entry-summary score-<?php echo str_replace('.', '_', $post->relevance_score); ?>">
								<?php echo the_excerpt(); //Relevanssi creates a custom excerpt for search results to highlight the hit area. This requires using the_excerpt(). ?>
							</div>
						<?php else: ?>
							<div class="entry-summary">
								<?php echo nebula()->excerpt(array('words' => 35)); ?>
							</div>
						<?php endif; ?>
					</div><!--/col-->
				</div><!--/row-->
			</article>
		<?php endwhile; ?>
	<?php endif; ?>
</div><!--/loop-section-->