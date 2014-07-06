<?php
/**
 * The template for displaying attachments.
 */

get_header(); ?>

<div class="row">
	
	<div class="sixteen columns">
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<?php if ( ! empty( $post->post_parent ) ) : ?>
				<p class="page-title"><a href="<?php echo get_permalink( $post->post_parent ); ?>" title="<?php esc_attr( printf( 'Return to %s', get_the_title( $post->post_parent ) ) ); ?>" rel="gallery"><?php
					/* translators: %s - title of parent post */
					printf( '<span class="meta-nav">&larr;</span> %s', get_the_title( $post->post_parent ) );
				?></a></p>
			<?php endif; ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				
				<div class="entry-meta">
					<hr/>
					<?php nebula_meta('by'); ?>
					<?php nebula_meta('on'); ?>
					<?php nebula_meta('dimensions'); ?>
					<hr/>
				</div><!-- .entry-meta -->
		
				<div class="entry-content">
					<div class="entry-attachment">
						<?php if ( wp_attachment_is_image() ) :
							$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
							foreach ( $attachments as $k => $attachment ) {
								if ( $attachment->ID == $post->ID )
									break;
								}
								$k++;
								// If there is more than 1 image attachment in a gallery
								if ( count( $attachments ) > 1 ) {
									if ( isset( $attachments[ $k ] ) )
										// get the URL of the next image attachment
										$next_attachment_url = get_attachment_link( $attachments[$k]->ID );
									else
										// or get the URL of the first image attachment
										$next_attachment_url = get_attachment_link( $attachments[0]->ID );
								} else {
									// or, if there's only 1 image attachment, get the URL of the image
									$next_attachment_url = wp_get_attachment_url();
								}
							?>
							<p class="isitthisone"><a href="<?php echo $next_attachment_url; ?>" title="<?php echo get_the_title(); ?>">
								<?php
									$attachment_size = apply_filters( 'boilerplate_attachment_size', 900 );
									echo wp_get_attachment_image( $post->ID, array( $attachment_size, 9999 ) ); // filterable image width with, essentially, no limit for image height.
								?>
							</a></p>
			
							<nav id="nav-below" class="navigation">
								<div class="nav-previous"><?php previous_image_link( false ); ?></div>
								<div class="nav-next"><?php next_image_link( false ); ?></div>
							</nav><!-- #nav-below -->
							
						<?php else : ?>
						
							<a href="<?php echo wp_get_attachment_url(); ?>" title="<?php echo get_the_title(); ?>" ><?php echo basename( get_permalink() ); ?></a>
							
						<?php endif; ?>
					</div><!-- .entry-attachment -->
					<div class="entry-caption"><?php if ( !empty( $post->post_excerpt ) ) the_excerpt(); ?></div>
		
					<?php the_content( 'Continue reading &rarr;' ); ?>
			
					<footer class="entry-utility">
						<?php if ( current_user_can('manage_options') ) : ?>
							<hr/>
							<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
							<br/>
							<?php nebula_manage('meta'); ?>
							<hr/>
						<?php endif; ?>
					</footer><!-- .entry-utility -->
		
					<?php //comments_template(); ?>
				</div><!-- .entry-content -->
			</article>
		
		<?php endwhile; ?>
	</div><!--/columns-->
			
</div><!--/row-->

<?php get_footer(); ?>