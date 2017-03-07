<?php
/**
 * Template Name: Advanced Search
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	http_response_code(403);
	die();
}

do_action('nebula_preheaders');
get_header(); ?>

<?php
	/*******************
		NOTE: This template is still in progress!
	*******************/
?>

<section id="bigheadingcon">
	<div class="container title-desc-con">
		<div class="row">
			<div class="col">
				<h1 class="page-title"><?php the_title(); ?></h1>
			</div><!--/cols-->
		</div><!--/row-->
	</div><!--/container-->

	<div id="breadcrumb-section" class="full inner dark">
		<div class="container">
			<div class="row">
				<div class="col">
					<?php nebula()->breadcrumbs(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div><!--/breadcrumb-section-->
</section>

<?php get_template_part('inc/nebula_drawer'); ?>

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div><!-- .entry-content -->

						<form id="advanced-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
							<a id="metatoggle" href="#">Metadata</a>
							<div id="advanced-search-meta" class="row">
								<div class="col-md-6 left-side">
									<div class="advanced-search-group">
										<span class="contact-form-heading">Date Range</span>
										<div class="form-group">
											<input id="advanced-search-date-start" class="form-control advanced-search-date" type="text" name="advanced-search-date-start" max="<?php echo date('Y-m-d', time()); ?>" placeholder="From"> - <input id="advanced-search-date-end" class="advanced-search-date input" type="text" name="advanced-search-date-end" max="<?php echo date('Y-m-d', time()); ?>" placeholder="To">
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
								</div><!--/col-->
								<div class="col-md-6 right-side">
									<div class="advanced-search-group">
										<?php if ( nebula()->option('author_bios', 'enabled') ): ?>
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
								</div><!--/col-->
							</div><!--/row-->
							<div class="row">
								<div class="col">
									<span class="contact-form-heading">Keyword Filter</span>
									<div class="form-group" style="margin-bottom: 0;">
										<input type="text" name="s" id="s" class="form-control no-autocomplete advanced-search-keyword" placeholder="Keyword filter" />
									</div>
									<input type="submit" id="searchsubmit" class="btn primary medium" name="submit" value="Search" style="float: right; position: absolute; left: -9999px;" />
								</div><!--/col-->
							</div><!--/row-->
						</form>
						<div style="text-align: right;">
							<a href="#" class="resetfilters"><i class="fa fa-times"></i> Reset Filters</a>
						</div>

						<div id="advanced-search-indicator"></div>
						<div id="advanced-search-results"></div>

						<!-- @TODO: These should be hidden by default -->
						<div class="row">
							<div class="col-md-6">
								<a id="load-prev-events" class="more-or-prev-events primary btn no-prev-events" href="#">&laquo; Previous results</a>
							</div><!--/col-->
							<div class="col-md-6">
								<a id="load-more-events" class="more-or-prev-events primary btn" href="#">More results &raquo;</a>
							</div><!--/col-->
						</div><!--/row-->
					</article>
				<?php endwhile; ?>
			</div><!--/col-->
			<div class="col-md-3 offset-md-1">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>