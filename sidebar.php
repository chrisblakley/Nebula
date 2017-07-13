<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<div id="sidebar-section">
	<ul class="xoxo">
		<?php do_action('nebula_sidebar_open'); //When using this hook remember it is in a UL! ?>

		<?php if ( is_active_sidebar('Primary Widget Area') ): ?>
			<?php dynamic_sidebar('Primary Widget Area'); ?>
		<?php endif; ?>

		<li class="widget-container">
			<?php if ( has_nav_menu('sidebar') ): ?>
				<nav id="sidebarnav" role="navigation">
					<?php
						wp_nav_menu();
						//wp_nav_menu(array('theme_location' => 'sidebar'));
					?>
				</nav>
			<?php endif; ?>
		</li>

		<?php if ( is_active_sidebar('Secondary Widget Area') ): ?>
			<?php dynamic_sidebar('Secondary Widget Area'); ?>
		<?php endif; ?>

		<?php do_action('nebula_sidebar_close'); //When using this hook remember it is in a UL! ?>
	</ul>
</div>