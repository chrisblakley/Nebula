<?php
/**
 * The template for displaying attachments.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr />
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">
		<div class="sixteen columns">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title">
						<?php if ( wp_attachment_is_image() ): ?>
							<i class="archiveicon fa fa-photo"></i>
						<?php endif; ?>
						<?php the_title(); ?>
					</h1>

					<div class="entry-meta">
						<?php nebula_meta('on'); ?> <?php nebula_meta('dimensions'); ?> <?php nebula_meta('exif'); ?>

						<span class="nebulasocialcon">
			        		<?php
				        		if ( is_dev() ) {
					        		nebula_meta('social', 1);
				        		} else {
					        		nebula_meta('social', 0);
				        		}
				        	?>
			        	</span>
					</div><!-- .entry-meta -->

					<div class="entry-content">
						<div class="entry-attachment">
							<?php if ( wp_attachment_is_image() ): ?>
								<?php
									$attachments = array_values(get_children(array('post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID')));
									foreach ( $attachments as $k => $attachment ) {
										if ( $attachment->ID == $post->ID ) {
											break;
										}
									}

									$k++;
									if ( count($attachments) > 1 ) { //If there is more than 1 image attachment in a gallery
										if ( isset($attachments[$k]) ) {
											$next_attachment_url = get_attachment_link( $attachments[$k]->ID ); //Get the URL of the next image attachment
										} else {
											$next_attachment_url = get_attachment_link( $attachments[0]->ID ); //Or get the URL of the first image attachment
										}
									} else { //Or, if there's only 1 image attachment, get the URL of the image
										$next_attachment_url = wp_get_attachment_url();
									}
								?>

								<div class="mediacon">
									<a href="<?php echo $next_attachment_url; ?>" title="<?php echo get_the_title(); ?>"><?php echo wp_get_attachment_image($post->ID, array($content_width, 9999)); ?></a>
								</div>

								<?php if (1==2): //Might be needed for a gallery page. ?>
									<nav id="nav-below" class="navigation">
										<div class="nav-previous"><?php previous_image_link(false); ?></div>
										<div class="nav-next"><?php next_image_link(false); ?></div>
									</nav><!-- #nav-below -->
								<?php endif; ?>
							<?php else : ?>
								<a href="<?php echo wp_get_attachment_url(); ?>" title="<?php echo get_the_title(); ?>" ><?php echo basename(get_permalink()); ?></a>
							<?php endif; ?>
						</div><!-- .entry-attachment -->

						<div class="entry-caption">
							<?php if ( !empty($post->post_excerpt) ): ?>
								<?php echo nebula_the_excerpt(); ?>
							<?php endif; ?>
						</div>

						<?php the_content(); ?>

						<?php if ( current_user_can('manage_options') ): ?>
							<div class="container entry-manage">
								<div class="row">
									<hr />
									<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
									<hr />
								</div>
							</div>
						<?php endif; ?>

						<?php comments_template(); ?>
					</div><!-- .entry-content -->
				</article>
			<?php endwhile; ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>