<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		die('Error 403: Forbidden.');
	}
?>

<?php if ( footerWidgetCounter() != 0 ) : //If no active footer widgets, then this section does not generate. ?>
	<div class="container footerwidgets">
		<div class="row">
			<?php if ( footerWidgetCounter() == 4 ): ?>
				<div class="four columns">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('First Footer Widget Area') ): ?>
						<?php //First Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/columns-->
				<div class="four columns">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Second Footer Widget Area') ): ?>
						<?php //Second Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/columns-->
				<div class="four columns">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Third Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/columns-->
				<div class="four columns">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Fourth Footer Widget Area') ): ?>
						<?php //Fourth Footer Widget Area ?>
					<?php endif; ?>
				</div><!--/columns-->
			<?php elseif ( footerWidgetCounter() == 3 ): ?>
				<div class="four columns">
					<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/columns-->
				<div class="four columns">
					<?php if ( dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/columns-->
				<div class="eight columns">
					<?php if ( dynamic_sidebar('Fourth Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/columns-->
			<?php elseif ( footerWidgetCounter() == 2 ): ?>
				<div class="eight columns">
					<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds (between 1-3). ?>
					<?php endif; ?>
				</div><!--/columns-->
				<div class="eight columns">
					<?php if ( dynamic_sidebar('Fourth Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds (between 4-2). ?>
					<?php endif; ?>
				</div><!--/columns-->
			<?php else : //1 Active Widget ?>
				<div class="sixteen columns">
					<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Fourth Footer Widget Area') ): ?>
						<?php //Outputs the first active widget area it finds. ?>
					<?php endif; ?>
				</div><!--/columns-->
			<?php endif; ?>
		</div><!--/row-->
	</div><!--/container-->
<?php endif; ?>