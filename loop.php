<?php nebula()->timer('Loop'); ?>
<div class="loop-section">
	<?php if ( !have_posts() ): //If there are no posts to display (such as an empty archive page). ?>
		<article id="post-0" class="post error404 not-found">
			<h2 class="entry-title"><?php _e('Not Found', 'nebula'); ?></h2>
			<div class="entry-summary">
				<p><?php _e('No results were found for the requested archive.', 'nebula'); ?></p>
				<?php echo nebula()->search_form(); ?>
			</div>
		</article>
	<?php else: //Begin the loop. ?>
		<?php $text_fragment = ( is_search() )? '#:~:text=' . rawurlencode(get_search_query()) : ''; //Add a scroll-to-text-fragment on search listings ?>
		<?php while ( have_posts() ): the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="row">
					<div class="col">
						<h2 class="entry-title"><a href="<?php echo get_the_permalink() . $text_fragment; ?>"><?php echo esc_html(get_the_title()); ?></a></h2>

						<div class="entry-meta">
							<?php if ( is_search() ): //If this is a search results listing ?>
								<?php nebula()->post_meta('type') . ' '; ?>
							<?php endif; ?>

							<?php if ( !in_array("page", get_post_class()) ): //If the post is not a page ?>
								<?php nebula()->post_meta('on'); ?> <?php if ( !is_author() ){ nebula()->post_meta('by'); } ?> <?php nebula()->post_meta('cat'); ?> <?php nebula()->post_meta('tags'); ?>
							<?php endif; ?>
						</div>

						<?php if ( has_post_thumbnail() && get_theme_mod('featured_image_location') !== 'disabled' ): //If the featured image exists (and is not disabled in the Customizer) ?>
							<a class="featured-image" href="<?php echo get_the_permalink() . $text_fragment; ?>">
								<?php the_post_thumbnail(); ?>
							</a>
						<?php endif; ?>

						<?php if ( is_search() && is_plugin_active('relevanssi/relevanssi.php') && $post->relevance_score ): ?>
							<div class="entry-summary score-<?php echo str_replace('.', '_', $post->relevance_score); ?>">
								<p><?php echo the_excerpt(); //Relevanssi creates a custom excerpt for search results to highlight the hit area. This requires using the_excerpt(). ?></p>
							</div>
						<?php else: ?>
							<div class="entry-summary">
								<p><?php echo nebula()->excerpt(); ?></p>
							</div>
						<?php endif; ?>
					</div><!--/col-->
				</div><!--/row-->
			</article>
		<?php endwhile; ?>

		<?php nebula()->paginate(); ?>
	<?php endif; ?>
</div>
<?php nebula()->timer('Loop', 'end'); ?>