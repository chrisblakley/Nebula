<div id="phg-welcome" class="welcome-panel-content gearside-welcome">
	
	<div id="welcome-content">
		<div class="logocon">
			<a href="<?php echo home_url(); ?>" target="_blank">
				<img src="<?php bloginfo('template_directory');?>/images/logo.svg" onerror="this.onerror=null; this.src=""<?php bloginfo('template_directory');?>/images/logo.png" alt="<?php bloginfo('name'); ?>"/>
			</a>
		</div>
		
		<h3><a href="<?php echo home_url(); ?>" target="_blank"><?php bloginfo('name'); ?></a></h3>
		<p class="about-description">Designed and Developed by <a href="http://www.pinckneyhugo.com/" target="_blank">Pinckney Hugo Group</a>.</p>
		
		<hr/>
		
		<div class="welcome-panel-column-container">
			
			<div class="welcome-panel-column">
				<h4>Your Information</h4>
				
				<?php $user_info = get_userdata( get_current_user_id() ); ?>
				
				<ul>
					<li>User: <strong class="admin-user-info admin-user-name"><?php echo $user_info->display_name; ?></strong></li>
					<li>Role: <strong class="admin-user-info admin-user-role"><?php echo array_shift($user_info->roles); ?></strong></li>
					<li>
						IP Address: <strong class="admin-user-info admin-user-ip"><?php echo $_SERVER["REMOTE_ADDR"]; ?></strong>
						<?php if ($_SERVER["REMOTE_ADDR"] == '72.43.235.106'): ?>
							<small> (PHG)</small>
						<?php endif; ?>
					</li>
				</ul>
			</div>
			
			<?php if ( is_admin() ) : //If user is an administrator ?>
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
		<div class="phg-info maininfo no-map jsinfo">
			<h4><a href="http://www.pinckneyhugo.com/" target="_blank">Pinckney Hugo Group</a></h4>
			<p class="addressphone"><a class="maptoggle showmap" href="#">760 West Genesee Street, Syracuse, NY 13204</a> <span class="hideformap">&bull; (315) 478-6700</span></p>
		</div>
		<div class="welcome-photo-bg"></div>
		
		<div class="weclome-map">
			<iframe
				width="100%"
				height="100%"
				frameborder="0" style="border:0"
				src="https://www.google.com/maps/embed/v1/place
				?key=AIzaSyArNNYFkCtWuMJOKuiqknvcBCyfoogDy3E
				&q=Pinckney+Hugo+Group
				&zoom=14
				&maptype=roadmap">
			</iframe>
		</div>
	</div>
</div>

<script>
	jQuery(window).on('load', function(){
		setTimeout(function(){
			jQuery('.jsinfo').removeClass('jsinfo');
		}, 350);
	});
	
	jQuery('#welcome-panel').removeClass('myhidden');
	jQuery('.welcome-panel-close').addClass('myhidden');
	jQuery('#wp_welcome_panel-hide').parent().addClass('myhidden').css('display', 'none');
	
	jQuery('.showmap').hover(function(){
		jQuery('.welcome-photo-bg').stop().fadeOut();
	}, function(){
		jQuery('.welcome-photo-bg').stop().fadeIn();
	});
	
	var address = jQuery('.maptoggle').text();
	jQuery('.maptoggle').on('click', function(e){
		if ( jQuery('.maininfo').hasClass('no-map') ) {
			jQuery('.maininfo').removeClass('no-map');
			jQuery('.welcome-photo-bg').addClass('myhidden');
			jQuery('.maptoggle').text('« Back');
			jQuery('.maininfo h4, .hideformap').addClass('noheight');
		} else {
			jQuery('.maininfo').addClass('no-map');
			jQuery('.welcome-photo-bg').removeClass('myhidden');
			jQuery('.maptoggle').text(address);
			jQuery('.maininfo h4, .hideformap').removeClass('noheight');
		}
		return false;
	});	
</script>
