<?php if ( !is_search() && (array_key_exists('s', nebula()->super->get) || array_key_exists('rs', nebula()->super->get)) ): ?>
	<div id="nebula-drawer" class="single-result-redirect">
		<div class="container">
			<div class="row">
				<div class="col">
					<a class="close" href="<?php echo get_the_permalink(); ?>">&times;</a>

					<h6><?php _e('Your search returned only one result. You have been automatically redirected.', 'nebula'); ?></h6>
					<?php echo nebula()->search_form(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
<?php elseif ( (is_page('search') || is_page_template('tpl-search.php')) && array_key_exists('invalid', nebula()->super->get) ): ?>
	<div id="nebula-drawer" class="invalid">
		<div class="container">
			<div class="row">
				<a class="close" href="<?php echo get_the_permalink(); ?>">&times;</a>
				<div class="col">
					<h6><?php _e('Your search was invalid. Please try again.', 'nebula'); ?></h6>
					<?php echo nebula()->search_form(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
<?php elseif ( is_404() || !have_posts() || array_key_exists('s', nebula()->super->get) ): ?>
	<div id="nebula-drawer" class="container-fluid suggestedpage" style="display: <?php echo ( !empty(nebula()->error_404_exact_match) )? 'block' : 'none'; ?>">
		<div class="container">
			<div class="row">
				<div class="col">
					<a class="close" href="<?php echo get_the_permalink(); ?>">&times;</a>

					<h3><?php _e('Did you mean?', 'nebula'); ?></h3>

					<?php if ( !empty(nebula()->error_404_exact_match) ): ?>
						<p><a class="internal-suggestion" href="<?php echo get_permalink(nebula()->error_404_exact_match->ID); ?>"><?php echo esc_html(get_the_title(nebula()->error_404_exact_match->ID)); ?></a></p>
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
<?php endif; ?>