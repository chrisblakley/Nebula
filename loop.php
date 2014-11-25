<?php
/**
 * The loop that displays posts.
 */
?>

<?php /* Display navigation to next/previous pages when applicable @TODO "Nebula" 0: REMOVE THIS AND ADD PAGENAVI. Check if pagenavi exists, and fall back to this method! */ ?>
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
	<nav id="nav-above" class="navigation">
		<div class="nav-previous"><?php next_posts_link('<span class="meta-nav">&larr;</span> Older posts'); ?></div>
		<div class="nav-next"><?php previous_posts_link('Newer posts <span class="meta-nav">&rarr;</span>'); ?></div>
	</nav><!-- #nav-above -->
<?php endif; ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( !have_posts() ) : ?>
	<article id="post-0" class="post error404 not-found">
		<h1 class="entry-title">Not Found</h1>
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

<?php while ( have_posts() ) : the_post();?>

	<?php //Display posts in a Gallery ?>
	<?php if ( in_category('gallery') ) : ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<div class="entry-meta">
				<hr/>
				<?php nebula_meta('on'); ?> <?php nebula_meta('in'); ?>
				<hr/>
			</div>

			<div class="entry-content">
				<?php if ( post_password_required() ) : ?>
					<?php the_content(); ?>
				<?php else : ?>
					<?php $images = get_children( array('post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999) ); ?>
					<?php if ($images) : ?>
						<?php
							$total_images = count($images);
							$image = array_shift($images);
							$image_img_tag = wp_get_attachment_image($image->ID, 'thumbnail');
						?>

						<div class="gallery-thumb">
							<a class="size-thumbnail" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
						</div>

						<p><em><?php printf( '<i class="fa fa-picture-o"></i> <a %1$s>%2$s photos</a>.', 'href="' . get_permalink() . '"', $total_images); ?></em></p>

					<?php endif; // if $images ?>

				<?php echo nebula_the_excerpt('Read More &raquo;', 50, 1); ?>

				<?php endif; //post_password_required. ?>
			</div>

			<footer class="entry-utility">
				<a href="<?php echo get_term_link('gallery', 'category'); ?>">More Galleries</a>

				<?php
					if ( nebula_settings_conditional('nebula_comments', 'disabled') ) {
						comments_popup_link('Leave a comment', '1 Comment', '% Comments');
					}
				?>

				<?php if ( current_user_can('manage_options') ) : ?>
					<div class="container entry-manage">
						<div class="row">
							<hr/>
							<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
							<hr/>
						</div>
					</div>
				<?php endif; ?>
			</footer><!-- .entry-utility -->
		</article><!-- #post-## -->

	<?php //Display all other posts (Non-Gallery) ?>
	<?php else : ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<?php if ( !in_array("page", get_post_class()) ) : //Do not display entry meta for pages ?>
			<div class="entry-meta">
				<hr/>
				<?php nebula_meta('on'); ?> <?php nebula_meta('in'); ?> <?php nebula_meta('tags'); ?>
				<hr/>
			</div>
			<?php endif; ?>

			<?php if ( is_archive() || is_search() ) : ?>
				<div class="entry-summary">
					<?php echo nebula_the_excerpt('', 50, 1); ?>
				</div>
				<a href="<?php the_permalink(); ?>">Read more &raquo;</a>
			<?php else : ?>
				<div class="entry-content">
					<?php echo nebula_the_excerpt('Read More &raquo;', 70, 1); ?>
					<?php wp_link_pages( array('before' => '<div class="page-link">' . 'Pages:', 'after' => '</div>') ); //@TODO "Nebula" 0: Pagenavi ?>
				</div>
			<?php endif; ?>

			<?php if ( current_user_can('manage_options') ) : ?>
				<div class="container entry-manage">
					<div class="row">
						<hr/>
						<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
						<hr/>
					</div>
				</div>
			<?php endif; ?>
		</article>

		<?php if ( nebula_settings_conditional('nebula_comments', 'disabled') ) : ?>
			<div id="nebulacommentswrapper">
				<?php comments_template('', true); ?>
			</div><!--/nebulacommentswrapper-->
		<?php endif; ?>

	<?php endif; //End if in Gallery ?>

<?php endwhile; ?>

<?php
	/* ==========================================================================
	   End the Loop
	   ========================================================================== */
?>


<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
	<nav id="nav-below" class="navigation">
		<?php next_posts_link( '&larr; Older posts' ); ?>
		<?php previous_posts_link( 'Newer posts &rarr;' ); ?>
	</nav><!-- #nav-below -->
<?php endif; ?>