<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<?php if ( get_theme_mod('sidebar_position') !== 'off' ): ?>
	<div class="col-md-3 <?php echo ( get_theme_mod('sidebar_position') === 'left' )? 'flex-first' : 'offset-md-1'; ?>" role="complementary">
		<div id="sidebar-section">
			<div class="row">
				<div class="col">
					<?php do_action('nebula_sidebar_open'); ?>
				</div><!--/col-->
			</div><!--/row-->

			<div class="row">
				<div class="col">
					<?php if ( is_active_sidebar('primary-widget-area') ): ?>
						<ul class="xoxo">
							<?php dynamic_sidebar('primary-widget-area'); ?>
						</ul>
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->

			<div class="row">
				<div class="col">
					<?php do_action('nebula_sidebar_close'); ?>
				</div><!--/col-->
			</div><!--/row-->
		</div>
	</div><!--/col-->
<?php endif; ?>