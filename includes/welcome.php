<div id="phg-welcome" class="welcome-panel-content gearside-welcome">
	
	<div id="welcome-content">
		<h3>
			<a href="http://domain.com" target="_blank" style="padding-left: 45px;"><img src="<?php bloginfo('template_directory');?>/images/logo.svg" onerror="this.onerror=null; this.src=""<?php bloginfo('template_directory');?>/images/logo.png" alt="#" style="position: absolute; margin-left: -45px; margin-top: -5px; max-width: 36px;"/></a><br/>
			<a href="http://domain.com" target="_blank">Site Name</a>
		</h3>
		<p class="about-description">Designed and Developed by <a href="http://www.pinckneyhugo.com/">Pinckney Hugo Group</a></p>
		<div class="welcome-panel-column-container">
			<div class="welcome-panel-column">
				<h4>Information</h4>
				<ul>
					<li>User: Name</li>
					<li>Role: Role</li>
					<li>Your IP Address: 000.00.00.000</li>
				</ul>
				<h4>Heading</h4>
				<a class="button button-primary button-hero analytics" href="https://www.google.com/analytics/web/#report/visitors-overview/a36461517w64548753p66272886/" target="_blank">Button</a>
				<ul>
					<li><a class="welcome-icon welcome-view-site" href="https://www.google.com/webmasters/tools/" target="_blank">Webmaster Tools</a></li>
					<li><a class="welcome-icon welcome-view-site" href="https://www.google.com/adsense/app#viewreports" target="_blank">AdSense</a></li>
					<li><a class="welcome-icon welcome-view-site" href="https://adwords.google.com/express/plus/" target="_blank">AdWords</a></li>
				</ul>
			</div>
			<div class="welcome-panel-column">
				<h4>Administration</h4>
				<ul>
					<li><a class="welcome-icon welcome-widgets-menus" href="#" target="_blank">cPanel</a></li>
					<li><a class="welcome-icon welcome-widgets-menus" href="#" target="_blank">Hosting</a></li>
					<li><a class="welcome-icon welcome-widgets-menus" href="#" target="_blank">Domain</a></li>
				</ul>
				<h4>Social</h4>
				<ul>
					<li><a class="welcome-icon welcome-comments" href="#" target="_blank">Facebook</a></li>
					<li><a class="welcome-icon welcome-comments" href="#" target="_blank">Twitter</a></li>
					<li><a class="welcome-icon welcome-comments" href="#" target="_blank">Google+</a></li>
				</ul>
			</div>
		</div>
	</div>
	
	
	<div id="welcome-photo">
		<p>
			<strong>Pinckney Hugo Group</strong>
			-Address
			-Phone
		</p>
		<p>
			-Twitter feed?
		</p>
	</div>
</div>

<script>
	jQuery('#welcome-panel').removeClass('hidden');
	jQuery('.welcome-panel-close').addClass('hidden');
	jQuery('#wp_welcome_panel-hide').parent().addClass('hidden').css('display', 'none');
</script>