<?php
/**
 * Template Name: Advanced Search
 */

if ( !defined('ABSPATH') ) { exit; } //Exit if accessed directly

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
				
				<?php
					/* @TODO:
						- When an autocomplete selection is made, trigger that page location instead of using the search. Could the datalist not be powerful enough for this? Maybe we need to AJAX into a div with links?
						
					*/
				?>
				
				<form class="search" method="get" action="<?php echo home_url('/'); ?>">
					<input type="text" list="advancedsearch" name="s" style="width: 100%; font-size: 28px; padding: 2px 10px; outline: none;" placeholder="Search" required/>
					<datalist id="advancedsearch">
						<?php //@TODO: These will be dynamically created. ?>
						<option value="Page title example">
						<option value="This is an example post title">
						<option value="Menu Item Here">
						<option value="Category 1">
						<option value="Category 2">
						<option value="A Tag">
						<option value="Another Tag Here">
						<option value="This is yet another tag">
					</datalist>
				</form>
					
				<div class="entry-content">
					<?php the_content(); ?>
					
					<?php if ( current_user_can('manage_options') ) : ?>
						<div class="container entry-manage">
							<div class="row">
								<hr/>
								<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
								<hr/>
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