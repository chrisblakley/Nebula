<section id="bigheadingcon">
	<div class="custom-color-overlay"></div>

	<?php if ( get_theme_mod('menu_position', 'over') === 'over' ): ?>
		<?php get_template_part('inc/navigation'); ?>
	<?php endif; ?>

	<?php if ( get_theme_mod('title_location', 'hero') === 'hero' ): ?>
		<div class="container title-desc-con">
			<div class="row">
				<div class="col">
					<?php if ( is_404() ): //404 ?>
						<h1 class="page-title"><?php _e('Not Found', 'nebula'); ?></h1>
						<p class="page-meta"><?php _e('The page you requested could not be found.', 'nebula'); ?></p>
					<?php elseif ( is_category() ): //Category ?>
						<h1 class="page-title"><i class="fas fa-fw fa-bookmark"></i> <?php echo single_cat_title('', false); ?></h1>
						<div class="page-meta"><?php echo category_description(); ?></div>
					<?php elseif ( is_tag() ): //Tags ?>
						<h1 class="page-title"><i class="fas fa-fw fa-tag"></i> <?php echo single_tag_title('', false); ?></h1>
						<div class="page-meta"><?php echo tag_description(); ?></div>
					<?php elseif ( is_archive() ): //Archive ?>
						<?php if ( have_posts() ){ the_post(); } //Queue the first post, then reset before running the loop. ?>
							<h1 class="page-title">
								<?php if ( is_day() ): ?>
									<?php
										//header('Location: ' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/');
										//exit;
									?>
									<i class="far fa-fw fa-calendar"></i> <?php echo get_the_date(); ?>
								<?php elseif ( is_month() ): ?>
									<i class="far fa-fw fa-calendar"></i> <?php echo get_the_date('F Y'); ?>
								<?php elseif ( is_year() ): ?>
									<i class="far fa-fw fa-calendar"></i> <?php echo get_the_date('Y'); ?>
								<?php else: ?>
									<?php _e('Archives', 'nebula'); ?>
								<?php endif; ?>
							</h1>
						<?php rewind_posts(); //Reset the queue before running the loop. ?>
					<?php elseif ( is_attachment() ): //Attachment posts ?>
						<h1 class="entry-title">
							<?php if ( wp_attachment_is_image() ): ?>
								<i class="archiveicon fa fa-photo"></i>
							<?php endif; ?>
							<?php the_title(); ?>
						</h1>
					<?php elseif ( is_author() ): //Author archive ?>
						<h1 class="page-title articles-by"><?php _e('Articles by', 'nebula'); ?> <strong><?php echo ( get_the_author_meta('first_name') !== '' )? get_the_author_meta('first_name') : get_the_author_meta('display_name'); ?></strong></h1>
					<?php elseif ( is_search() ): //Search results ?>
						<?php if ( have_posts() ): ?>
							<h1 class="page-title"><?php _e('Search Results', 'nebula'); ?></h1>
							<p class="page-meta"><?php _e('Your search for', 'nebula'); ?> <span class="search-term">"<?php echo get_search_query(); ?>"</span> <?php printf(_n('returned %s result.', 'returned %s results.', $wp_query->found_posts, 'nebula'), number_format_i18n($wp_query->found_posts)); ?></p>
						<?php else: ?>
							<h1 class="page title"><?php _e('No Results Found', 'nebula'); ?></h1>
							<p><?php _e('Your search for', 'nebula'); ?> <span class="search-term">"<?php echo get_search_query(); ?>"</span> <?php printf(_n('returned %s result.', 'returned %s results.', $wp_query->found_posts, 'nebula'), number_format_i18n($wp_query->found_posts)); ?></p>
							<script>
								ga('send', 'event', 'Internal Search', 'No Results', jQuery('#s').val(), {'nonInteraction': true});
							</script>
						<?php endif; ?>
						<?php echo nebula()->search_form(); ?>
					<?php elseif ( is_singular() && !is_page() ): //Single posts (and custom post types) but not pages. Attachments are singular, but are handled above. ?>
						<h1 class="entry-title"><?php the_title(); ?></h1>

						<div class="entry-meta">
							<?php echo nebula()->post_date(); ?> <?php echo nebula()->post_author(); ?> <?php echo nebula()->post_categories(); ?> <?php echo nebula()->post_tags(); ?>
						</div>
					<?php else: //Page and any other templates ?>
						<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	<?php endif; ?>
</section>