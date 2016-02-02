<?php
/**
 * The loop that displays posts.
 */
?>

<?php if ( !have_posts() ): //If there are no posts to display, such as an empty archive page ?>
	<article id="post-0" class="post error404 not-found">
		<h2 class="entry-title">Not Found</h2>
		<div class="entry-content">
			<p>No results were found for the requested archive.</p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</article><!-- #post-0 -->
<?php endif; ?>

<?php
	/* ==========================================================================
	   Begin the Loop
	   ========================================================================== */
?>

<?php while ( have_posts() ): the_post(); ?>
	<?php if ( in_category('gallery') ): //Display posts in a Gallery ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-meta">
				<hr />
				<?php nebula_meta('on'); ?> <?php nebula_meta('in'); ?>
				<hr />
			</div>

			<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<div class="entry-content">
				<?php if ( post_password_required() ): ?>
					<?php the_content(); ?>
				<?php else : ?>
					<?php $images = get_children(array('post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999)); ?>
					<?php if ( $images ): ?>
						<?php
							$total_images = count($images);
							$image = array_shift($images);
							$image_img_tag = wp_get_attachment_image($image->ID, 'thumbnail');
						?>
						<div class="gallery-thumb">
							<a class="size-thumbnail" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
						</div>
						<p><em><?php printf('<i class="fa fa-picture-o"></i> <a %1$s>%2$s photos</a>.', 'href="' . get_permalink() . '"', $total_images); ?></em></p>
					<?php endif; ?>

					<?php echo nebula_the_excerpt('Read More &raquo;', 50, 1); ?>
				<?php endif; ?>
			</div>

			<footer class="entry-utility">
				<a href="<?php echo get_term_link('gallery', 'category'); ?>">More Galleries</a>
			</footer><!-- .entry-utility -->
		</article><!-- #post-## -->
	<?php else : //Display all other posts (Non-Gallery) ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( !in_array("page", get_post_class()) ): //Do not display entry meta for pages ?>
				<div class="entry-meta">
					<?php nebula_meta('on'); ?> <?php if ( !is_author() ){ nebula_meta('by'); } ?> <?php nebula_meta('in'); ?> <?php nebula_meta('tags'); ?>
				</div>
			<?php endif; ?>

			<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><?php //Semantically this should be H2, but visually needs to be smaller. ?>

			<?php if ( is_archive() ): ?>
				<div class="entry-summary">
					<?php echo nebula_the_excerpt('Read more &raquo;', 50, 1); ?>
				</div>
			<?php elseif ( is_search() ): ?>
				<?php if ( file_exists(WP_PLUGIN_DIR . '/relevanssi') && $post->relevance_score ): ?>
					<div class="entry-summary score-<?php echo str_replace('.', '_', $post->relevance_score); ?>">
						<?php echo the_excerpt(); //Relevanssi creates a custom excerpt for search results to highlight the hit area. This requires using the_excerpt(). ?>
					</div>
				<?php else: ?>
					<div class="entry-summary">
						<?php echo nebula_the_excerpt('', 50, 1); ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="entry-content">
					<?php echo nebula_the_excerpt('Read More &raquo;', 70, 1); ?>
					<?php wp_link_pages(array('before' => '<div class="page-link">' . 'Pages:', 'after' => '</div>')); //@TODO "Nebula" 0: Pagenavi ?>
				</div>
			<?php endif; ?>
		</article>

		<?php if ( nebula_option('nebula_comments', 'disabled') ): ?>
			<div id="nebulacommentswrapper">
				<?php comments_template('', true); ?>
			</div><!--/nebulacommentswrapper-->
		<?php endif; ?>
	<?php endif; ?>
<?php endwhile; ?>

<?php
	/* ==========================================================================
	   End the Loop
	   ========================================================================== */
?>