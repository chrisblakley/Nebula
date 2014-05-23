<div id="phg-welcome" class="welcome-panel-content gearside-welcome">
	
	<div id="welcome-content">
		<div class="logocon">
			<a href="<?php echo home_url(); ?>" target="_blank">
				<img src="<?php bloginfo('template_directory');?>/images/logo.svg" onerror="this.onerror=null; this.src=""<?php bloginfo('template_directory');?>/images/logo.png" alt="<?php bloginfo('name'); ?>"/>
			</a>
		</div>
		
		<h3><a href="<?php echo home_url(); ?>" target="_blank"><?php bloginfo('name'); ?></a></h3>
		<p class="about-description">Designed and Developed by <a href="http://www.pinckneyhugo.com/">Pinckney Hugo Group</a>.</p>
		
		<hr/>
		
		<div class="welcome-panel-column-container">
			
			<div class="welcome-panel-column">
				<h4>Information</h4>
				
				
				<?php
					//$user_ID = get_current_user_id();
					$user_info = get_userdata( get_current_user_id() );
				?>
				
				<ul>
					<li>User: <strong class="admin-user-info admin-user-name"><?php echo $user_info->display_name; ?></strong></li>
					<li>Role: <strong class="admin-user-info admin-user-role"><?php echo array_shift($user_info->roles); ?></strong></li>
					<li>Your IP Address: <strong class="admin-user-info admin-user-ip"><?php echo $_SERVER["REMOTE_ADDR"]; ?></strong></li>
				</ul>
				<a class="button button-primary button-hero analytics" href="<?php echo home_url(); ?>" target="_blank">Visit Site</a>
			</div>
			
			<?php if ( 1==1 ) : //If user is an administrator ?>
			<div class="welcome-panel-column">
				<h4>Administration</h4>
				<ul>
					<li><a class="welcome-icon welcome-widgets-menus" href="#" target="_blank">cPanel</a></li>
					<li><a class="welcome-icon welcome-widgets-menus" href="#" target="_blank">Hosting</a></li>
					<li><a class="welcome-icon welcome-widgets-menus" href="#" target="_blank">Domain</a></li>
				</ul>
			</div>
			<?php endif; ?>
			
			<div class="welcome-panel-column">
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