<?php
/**
 * Template Name: Advanced Search
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_header');
get_header(); ?>



<?php
	/*******************
		NOTE: This template is still in progress!
	*******************/
?>



<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr />
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">

		<div class="eleven columns">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-content">
						<?php the_content(); ?>
					</div><!-- .entry-content -->

					<form method="get" id="advanced-search-form" action="<?php echo esc_url(home_url('/')); ?>">
						<a id="metatoggle" href="#">Metadata</a>
						<div id="advanced-search-meta" class="row">
							<div class="eight columns left-side">
								<div class="advanced-search-group">
									<span class="contact-form-heading">Date Range</span>
									<div class="field">
										<input id="advanced-search-date-start" class="advanced-search-date input" type="text" name="advanced-search-date-start" max="<?php echo date('Y-m-d', strtotime('now')); ?>" placeholder="From"> - <input id="advanced-search-date-end" class="advanced-search-date input" type="text" name="advanced-search-date-end" max="<?php echo date('Y-m-d', strtotime('now')); ?>" placeholder="To">
									</div>
									<input id="advanced-search-date-start-alt" class="hidden" type="text">
									<input id="advanced-search-date-end-alt" class="hidden" type="text">
								</div>

								<div class="advanced-search-group">
									<span class="contact-form-heading">Categories &amp; Tags</span>
									<select id="advanced-search-catstags" class="chosen-select" multiple data-placeholder="Select categories and tags...">
										<?php $taxonomies = get_object_taxonomies('post'); ?>
										<?php foreach($taxonomies as $tax): ?>
											<?php
												$terms = get_terms($tax);
												$tax_human = ( $tax == 'category' )? 'Categories' : $tax;
												$tax_human = ( $tax == 'post_tag' )? 'Tags' : $tax;
												$tax = ( $tax == 'post_tag' )? 'tag' : $tax;
											?>
											<optgroup label="<?php echo $tax_human; ?>">
												<?php foreach ($terms as $term): ?>
													<option value="<?php echo $tax . '__' . str_replace(' ', '-', strtolower($term->name)); ?>"><?php echo $term->name; ?></option>
												<?php endforeach; ?>
											</optgroup>
										<?php endforeach; ?>
									</select>
								</div>
							</div><!--/columns-->
							<div class="eight columns right-side">
								<div class="advanced-search-group">
									<?php if ( nebula_author_bios_enabled() ): ?>
										<span class="contact-form-heading">Post Author</span>
										<select id="advanced-search-author" class="chosen-select" data-placeholder="Select author...">
											<option></option>
											<?php $users = get_users(); ?>
											<?php foreach ($users as $user): ?>
												<?php if ( count_user_posts($user->ID) >= 1 ): ?>
													<option value="<?php echo $user->ID; ?>"><?php echo $user->display_name; ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										</select>
									<?php endif; ?>
								</div>

								<div class="advanced-search-group">
									<span class="contact-form-heading">Post Type</span>
									<select id="advanced-search-type" class="chosen-select" multiple data-placeholder="Select post types...">
										<?php $post_types = get_post_types('', 'names'); ?>
										<?php foreach ( $post_types as $post_type ): ?>
											<?php if ( $post_type == 'revision' || $post_type == 'nav_menu_item' || $post_type == 'acf' || $post_type == 'wpcf7_contact_form' ) { continue; } ?>
											<option value="<?php echo $post_type; ?>"><?php echo ucfirst($post_type); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div><!--/columns-->
						</div><!--/row-->
						<div class="row">
							<div class="sixteen columns">
								<span class="contact-form-heading">Keyword Filter</span>
								<div class="field" style="margin-bottom: 0;">
									<input type="text" name="s" id="s" class="no-autocomplete input advanced-search-keyword" placeholder="Keyword filter" />
								</div>
								<input type="submit" id="searchsubmit" class="btn primary medium" name="submit" value="Search" style="float: right; position: absolute; left: -9999px;" />
							</div><!--/columns-->
						</div><!--/row-->
					</form>
					<div style="text-align: right;">
						<a href="#" class="resetfilters"><i class="fa fa-times"></i> Reset Filters</a>
					</div>

					<div id="advanced-search-indicator"></div>
					<div id="advanced-search-results"></div>

					<!-- @TODO: These should be hidden by default -->
					<div class="row">
						<div class="eight columns">
							<a id="load-prev-events" class="more-or-prev-events primary btn no-prev-events" href="#">&laquo; Previous results</a>
						</div><!--/columns-->
						<div class="eight columns">
							<a id="load-more-events" class="more-or-prev-events primary btn" href="#">More results &raquo;</a>
						</div><!--/columns-->
					</div><!--/row-->

					<?php if ( current_user_can('manage_options') ): ?>
						<div class="container entry-manage">
							<div class="row">
								<hr />
								<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
								<hr />
							</div>
						</div>
					<?php endif; ?>
				</article><!-- #post-## -->
			<?php endwhile; ?>
		</div><!--/columns-->

		<div class="four columns push_one">
			<h3>Contact Us</h3>
			<?php if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ): ?>
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