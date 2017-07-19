<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<div id="sidebar-section">
	<ul class="xoxo">
		<?php do_action('nebula_sidebar_open'); //When using this hook remember it is in a UL! ?>

		<?php if ( is_active_sidebar('sidebar-widget-area') ): ?>
			<?php dynamic_sidebar('sidebar-widget-area'); ?>
		<?php endif; ?>

		<?php do_action('nebula_sidebar_close'); //When using this hook remember it is in a UL! ?>
	</ul>
</div>