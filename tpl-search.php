<?php
/**
 * Template Name: Advanced Search
 */

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	
	<div class="eleven columns">
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
											
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
	
	<div class="four columns push_one">
		<h3>Contact Us</h3>
		<?php if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) : ?>
			<ul id="cform7-container">
				<?php echo do_shortcode('[contact-form-7 id="384" title="Contact Form 7 Documentation"]'); ?>
			</ul>
		<?php else : ?>
			<div class="row">
				<div class="sixteen columns">
					<?php nebula_backup_contact_form(); ?>
				</div><!--/columns-->
			</div><!--/row-->
		<?php endif; ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>