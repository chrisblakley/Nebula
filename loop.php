<?php
/**
 * The loop that displays posts.
 */
?>

<div class="loop-section">
	<?php if ( !have_posts() ): //If there are no posts to display (such as an empty archive page). ?>
		<article id="post-0" class="post error404 not-found">
			<h3 class="entry-title">Not Found</h3>
			<div class="entry-summary">
				<p>No results were found for the requested archive.</p>
				<?php echo nebula_search_form(); ?>
			</div>
		</article>
	<?php else: //Begin the loop. ?>
		<?php while ( have_posts() ): the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

				<div class="entry-meta">
					<?php if ( is_search() ): ?>
						<?php //nebula_meta('type') . ' '; //Enable this to show post types in search results. ?>
					<?php endif; ?>

					<?php if ( !in_array("page", get_post_class()) ): ?>
						<?php nebula_meta('on'); ?> <?php if ( nebula_option('author_bios', 'enabled') && !is_author() ){ nebula_meta('by'); } ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('tags'); ?>
					<?php endif; ?>
				</div>

				<?php if ( is_search() && is_plugin_active('relevanssi/relevanssi.php') && $post->relevance_score ): ?>
					<div class="entry-summary score-<?php echo str_replace('.', '_', $post->relevance_score); ?>">
						<?php echo the_excerpt(); //Relevanssi creates a custom excerpt for search results to highlight the hit area. This requires using the_excerpt(). ?>
					</div>
				<?php else: ?>
					<div class="entry-summary">
						<?php echo nebula_excerpt(array('length' => 70)); ?>
					</div>
				<?php endif; ?>
			</article>
		<?php endwhile; ?>
	<?php endif; ?>
</div><!--/loop-section-->