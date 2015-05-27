<?php
/**
 * Template Name: Advanced Search
 */

if ( !defined('ABSPATH') ) { //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_header');

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">

		<div class="eleven columns">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<hr/><br/><br/>

					<form method="get" id="advanced-search-form" action="<?php echo esc_url(home_url('/')); ?>">

						<?php if ( nebula_author_bios_enabled() ) : ?>
							<select class="chosen-select advanced-author" data-placeholder="Select author...">

							</select>
						<?php endif; ?>

						<select class="chosen-select advanced-post-type" multiple data-placeholder="Select post types...">
							<?php $post_types = get_post_types('', 'names'); ?>
							<?php foreach ( $post_types as $post_type ) : ?>
								<?php if ( $post_type == 'revision' || $post_type == 'nav_menu_item' || $post_type == 'acf' || $post_type == 'wpcf7_contact_form' ) { continue; } ?>
								<option value="<?php echo $post_type; ?>"><?php echo ucfirst($post_type); ?></option>
							<?php endforeach; ?>
						</select>

						<br/><br/>
						From:
						<input class="advanced-date-from" type="date" name="advanced-date-from" max="<?php echo date('Y-m-d', strtotime('now')); ?>" placeholder="From">

						&nbsp;&nbsp;&nbsp;&nbsp;To:
						<input class="advanced-date-to" type="date" name="advanced-date-to" max="<?php echo date('Y-m-d', strtotime('now')); ?>" placeholder="To">

						<br/><br/>

						<select class="advanced-catstags chosen-select" multiple data-placeholder="Select categories and tags...">
							<?php $taxonomies = get_object_taxonomies('post'); ?>
							<?php foreach($taxonomies as $tax) : ?>
								<?php
									$terms = get_terms($tax);

									var_dump($terms);

									$tax_human = ( $tax == 'category' ) ? 'Categories' : $tax;
									$tax_human = ( $tax == 'post_tag' ) ? 'Tags' : $tax;
									$tax = ( $tax == 'post_tag' ) ? 'tag' : $tax;
								?>
								<optgroup label="<?php echo $tax_human; ?>">
									<?php foreach ($terms as $term) : ?>
										<option value="<?php echo $tax . '__' . $term->term_id; ?>"><?php echo $term->name; ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>


						<br/><br/>


						    <input type="text" name="s" id="s" class="no-autocomplete" placeholder="Search" style="width: 100%; font-size: 32px; font-weight: 300; padding: 0 10px; outline: none;" />
						    <br/><br/>
<!-- 						    <input type="submit" id="searchsubmit" class="btn primary medium" name="submit" value="Search" style="float: right;" /> -->
					</form>

					<i id="advanced-search-indicator" class="fa fa-spin fa-spinner"></i>






					<br/><br/>




					<div id="advanced-search-results" style="display: none;"></div>


					<br/><br/>







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
</div><!--/container-->

<?php get_footer(); ?>

<?php do_action('nebula_footer'); ?>