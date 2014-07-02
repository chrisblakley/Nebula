<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<ul class="xoxo">
	
	<?php if ( is_author() ) : ?>
		<li>
			<h3>About the Author</h3>
		</li>
	<?php endif; ?>
	
	
	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Primary Widget Area') ) : ?>
		<?php //Primary Widget Area ?>
	<?php endif; ?>

	<li>
		<form class="search" method="get" action="<?php echo home_url('/'); ?>">
			<ul>
				<li class="append field">
				    <input class="xwide text input search" type="text" name="s" placeholder="Search" />
				    <input type="submit" class="medium primary btn submit" value="Go" />
			    </li>
			</ul>
		</form><!--/search-->
	</li>
	
	<li>
		<?php 
			if ( has_nav_menu('sidebar') ) {
				wp_nav_menu(array('theme_location' => 'sidebar'));
			} elseif (has_nav_menu('header') ) {
				wp_nav_menu(array('theme_location' => 'header'));
			} else {
				echo '<p>@TODO: Set a default menu or something</p>';
			}
		?>
	</li>
		
	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Secondary Widget Area') ) : ?>
		<?php //Secondary Widget Area ?>
	<?php endif; ?>
	
	<li>
		
	<h3>Contact Us</h3>
	<?php if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) : ?>
		<ul id="cform7-container">
			<?php echo do_shortcode('[contact-form-7 id="161"]'); ?>
		</ul>
	<?php else : ?>
		<div class="container">
			<div class="row">
				<div class="sixteen columns">
					<?php //@TODO: Eventually change this to use WP Mail with a hard-coded form. ?>
					<?php $admin_user = get_userdata(1); ?>
					<div class="medium primary btn icon-left entypo icon-mail">
						<a class="cform-disabled" href="mailto:<?php echo get_option('admin_email', $admin_user->user_email); ?>?subject=Email%20submission%20from%20<?php the_permalink(); ?>" target="_blank">Email Us</a>
					</div><!--/button-->
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
	<?php endif; ?>

	</li>

</ul>
