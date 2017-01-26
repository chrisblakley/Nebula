<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		http_response_code(403);
		die();
	}
?>

<?php if ( footer_widget_counter() != 0 ) : //If no active footer widgets, then this section does not generate. ?>
	<div class="container footerwidgets">
		<div class="row">
			<?php if ( footerWidgetCounter() == 4 ): ?>
				<div class="col-md-3">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('First Footer Widget Area') ): ?>
						<?php //First Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/col-->
				<div class="col-md-3">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Second Footer Widget Area') ): ?>
						<?php //Second Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/col-->
				<div class="col-md-3">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Third Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/col-->
				<div class="col-md-3">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Fourth Footer Widget Area') ): ?>
						<?php //Fourth Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/col-->
			<?php elseif ( footerWidgetCounter() == 3 ): ?>
				<div class="col-md-3">
					<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/col-->
				<div class="col-md-3">
					<?php if ( dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/col-->
				<div class="col-md-6">
					<?php if ( dynamic_sidebar('Fourth Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/col-->
			<?php elseif ( footerWidgetCounter() == 2 ): ?>
				<div class="col-md-6">
					<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds (between 1-3). ?>
					<?php endif; ?>
				</div><!--/col-->
				<div class="col-md-6">
					<?php if ( dynamic_sidebar('Fourth Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds (between 4-2). ?>
					<?php endif; ?>
				</div><!--/col-->
			<?php else : //1 Active Widget ?>
				<div class="col-md-12">
					<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Fourth Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/col-->
			<?php endif; ?>
		</div><!--/row-->
	</div><!--/container-->
<?php endif; ?>