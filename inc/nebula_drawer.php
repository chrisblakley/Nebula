<?php if ( !is_search() && (array_key_exists('s', $_GET) || array_key_exists('rs', $_GET)) ): ?>
	<div id="nebula-drawer" class="single-result-redirect">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<a class="close" href="<?php echo get_the_permalink(); ?>">&times;</a>

					<h5>Your search returned only one result. You have been automatically redirected.</h5>
					<?php echo nebula_search_form(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
<?php elseif ( (is_page('search') || is_page_template('tpl-search.php')) && array_key_exists('invalid', $_GET) ): ?>
	<div id="nebula-drawer" class="invalid">
		<div class="container">
			<div class="row">
				<a class="close" href="<?php echo get_the_permalink(); ?>">&times;</a>
				<div class="col-md-12">
					<h5>Your search was invalid. Please try again.</h5>
					<?php echo nebula_search_form(); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
<?php elseif ( is_404() || !have_posts() || array_key_exists('s', $_GET) ): ?>
	<?php global $error_404_exact_match; ?>
	<div id="nebula-drawer" class="container-fluid suggestedpage" style="display: <?php echo ( !empty($error_404_exact_match) )? 'block' : 'none'; ?>">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h3>Did you mean?</h3>

					<?php if ( !empty($error_404_exact_match) ): ?>
						<p><a class="internal-suggestion" href="<?php echo get_permalink($error_404_exact_match->ID); ?>"><?php echo get_the_title($error_404_exact_match->ID); ?></a></p>
					<?php else: ?>
						<p><a class="gcse-suggestion" href="#"></a></p>
					<?php endif; ?>

					<a class="close" href="<?php echo get_the_permalink(); ?>">&times;</a>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
<?php endif; ?>