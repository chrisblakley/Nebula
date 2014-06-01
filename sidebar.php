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
		
<?php if (1==2) {  //Sample Contact Form 7 form - FORM SECTION in WP Admin (If using this snippet for non-sidebar cForm, wrap it in a <ul>) 
	/*
	<li class="field">
		[text* name class:text class:input class:cform7-name placeholder "Your Name*"]
	</li>
	<li class="field">
		[email* email class:email class:input class:cform7-email placeholder "Email Address*"]
	</li>
	<li class="field">
		[text phone class:numeric class:input class:cform7-phone placeholder "Phone Number"]
	</li>
	</li>
	<li class="field">
		[text bday class:numeric class:input class:cform7-bday placeholder "Date of Birth"]
	</li>
	<li class="field">
		[textarea* message class:textarea class:input class:cform7-message placeholder "Enter your message here.*"]
	</li>
	<li class="field">
		[file file-154 limit:6000000 filetypes:pdf|doc|docx|jpg|jpeg|gif|png|tiff]
	</li>
	<!-- CAPTCHA requires the plugin Really Simple CAPTCHA
		<li class="field">
			[captchac captcha-946 id:captcha-image size:m]
			[captchar captcha-946 id:captcha-input class:input placeholder "Enter above CAPTCHA text here"]
		</li>
	-->
	<li class="field">
		[submit class:medium class:primary class:btn class:submit submit"Send"]
	</li>
	
	//Sample Contact Form 7 form - MESSAGE BODY in WP Admin. Add [file-154] to "File attachments".
	From: [name] <[email]>
	[phone]
	[bday]
	
	Message Body:
	[message]
	
	
	This e-mail was sent from a contact form on Website Name (http://domain.com)
	
	
	//Sample Contact Form 7 form - ADDITIONAL SETTINGS in WP Admin. Change "sidebar" to the name of the form being used.
	on_sent_ok: "cFormSuccess('sidebar');"
	*/
}

?>

<?php if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) : ?>
	<h3>Contact Us</h3>
	<ul id="cform7-container">
		<?php echo do_shortcode('[contact-form-7 id="161" title="Sample Message"]'); ?>
	</ul>
<?php endif; ?>
		
		
		
	</li>

</ul>