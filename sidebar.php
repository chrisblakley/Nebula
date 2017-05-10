<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<div id="sidebar-section">
	<ul class="xoxo">
		<?php do_action('nebula_sidebar_open'); //When using this hook remember it is in a UL! ?>

		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Primary Widget Area') ): ?>
			<?php //Primary Widget Area ?>
		<?php endif; ?>

		<li class="widget-container">
			<?php if ( has_nav_menu('sidebar') ): ?>
				<?php
					wp_nav_menu();
					//wp_nav_menu(array('theme_location' => 'sidebar'));
				?>
			<?php endif; ?>
		</li>

		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Secondary Widget Area') ): ?>
			<?php //Secondary Widget Area ?>
		<?php endif; ?>

		<?php do_action('nebula_sidebar_close'); //When using this hook remember it is in a UL! ?>
	</ul>
</div>