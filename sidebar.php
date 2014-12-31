<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<ul class="xoxo">

	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Primary Widget Area') ) : ?>
		<?php //Primary Widget Area ?>
	<?php endif; ?>

	<li class="widget-container">
		<?php if ( has_nav_menu('sidebar') ) : ?>
			<h3>Features</h3>
			<?php wp_nav_menu(array('theme_location' => 'sidebar')); ?>
		<?php endif; ?>
	</li>

	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Secondary Widget Area') ) : ?>
		<?php //Secondary Widget Area ?>
	<?php endif; ?>

	<li class="widget-container">
		<h3>Search</h3>
		<form class="search" method="get" action="<?php echo home_url('/'); ?>">
			<ul>
				<li class="append field">
				    <input class="xwide text input search" type="text" name="s" placeholder="Search" />
				    <input type="submit" class="medium primary btn submit" value="Go" />
			    </li>
			</ul>
		</form><!--/search-->
	</li>

	<li class="widget-container">
		<h3>Contact Us</h3>
		<?php nebula_facebook_link(); ?>
		<?php if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) : ?>
			<div id="cform7-container">
				<?php echo do_shortcode('[contact-form-7 id="384" title="Contact Form 7 Documentation"]'); ?>
			</div>
		<?php else : ?>
			<div class="row">
				<div class="sixteen columns">
					<?php nebula_backup_contact_form(); ?>
				</div><!--/columns-->
			</div><!--/row-->
		<?php endif; ?>
	</li>

</ul>