<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri(); ?>/css/font-awesome.min.css" />

<div id="phg-welcome" class="welcome-panel-content gearside-welcome">
	
	<div id="welcome-content">
		<div class="logocon">
			<a href="<?php echo home_url(); ?>" target="_blank">
				<img src="<?php bloginfo('template_directory');?>/images/logo.svg" onerror="this.onerror=null; this.src=""<?php bloginfo('template_directory');?>/images/logo.png" alt="<?php bloginfo('name'); ?>"/>
			</a>
		</div>
		
		<h3><a href="<?php echo home_url(); ?>" target="_blank"><?php bloginfo('name'); ?></a></h3>
		<p class="about-description">Designed and Developed by <a href="http://www.pinckneyhugo.com/" target="_blank" style="color: #0098d7;"><img src="<?php echo get_bloginfo('template_directory'); ?>/images/phg/phg-symbol.png" onerror="this.onerror=null; this.src=""<?php echo get_bloginfo('template_directory'); ?>/images/phg/phg-symbol.png" alt="Pinckney Hugo Group" style="max-width: 14px;"/> Pinckney Hugo Group</a>.</p>
		
		<hr/>
		
		<div class="welcome-panel-column-container">
			
			<div class="welcome-panel-column">
				<h4>Your Information</h4>
				
				<?php $user_info = get_userdata( get_current_user_id() ); ?>
				
				<ul>
					<li><i class="fa fa-user fa-fw"></i> User: <strong class="admin-user-info admin-user-name"><?php echo $user_info->display_name; ?></strong></li>
					<?php
						switch ($user_info->roles[0]) {
						    case 'administrator' : $fa_role = 'fa-key'; break;
						    case 'editor' : $fa_role = 'fa-scissors'; break;
						    case 'author' : $fa_role = 'fa-pencil-square'; break;
						    case 'contributor' : $fa_role = 'fa-send'; break;
						    case 'subscriber' : $fa_role = 'fa-ticket'; break;
						    default : $fa_role = 'fa-recycle'; break;
						}
					?>
					<li><i class="fa <?php echo $fa_role; ?> fa-fw"></i> Role: <strong class="admin-user-info admin-user-role"><?php echo $user_info->roles[0]; ?></strong></li>
					<li>
						<?php if ($_SERVER["REMOTE_ADDR"] == '72.43.235.106'): ?>
							<img src="<?php echo get_bloginfo('template_directory'); ?>/images/phg/phg-symbol.png" onerror="this.onerror=null; this.src=""<?php echo get_bloginfo('template_directory'); ?>/images/phg/phg-symbol.png" alt="Pinckney Hugo Group" style="max-width: 14px;"/>
						<?php else: ?>
							<i class="fa fa-laptop fa-fw"></i>
						<?php endif; ?>
						IP Address: <strong class="admin-user-info admin-user-ip"><?php echo $_SERVER["REMOTE_ADDR"]; ?></strong>
					</li>
				</ul>
			</div>
			
			<?php if ( current_user_can('manage_options') ) : //If user is an administrator ?>
			<div class="welcome-panel-column">
				<h4>Administration</h4>
				<ul>
					<li><i class="fa fa-gears fa-fw"></i> <a href="#" target="_blank">cPanel</a></li>
					<li><i class="fa fa-hdd-o fa-fw"></i> <a href="#" target="_blank">Hosting</a></li>
					<li><i class="fa fa-globe fa-fw"></i> <a href="#" target="_blank">Registrar</a></li>
					<li><i class="fa fa-bar-chart-o fa-fw"></i> <a href="#" target="_blank">Google Analytics</a></li>
					<li><i class="fa fa-google fa-fw"></i> <a href="#" target="_blank">Google Webmaster Tools</a></li>
				</ul>
			</div>
			<?php endif; ?>
			
			<div class="welcome-panel-column">
				<h4>Social</h4>
				<ul>
					<li><i class="fa fa-facebook-square fa-fw"></i> <a href="#" target="_blank">Facebook</a></li>
					<li><i class="fa fa-twitter-square fa-fw"></i> <a href="#" target="_blank">Twitter</a></li>
					<li><i class="fa fa-google-plus-square fa-fw"></i> <a href="#" target="_blank">Google+</a></li>
					<li><i class="fa fa-linkedin-square fa-fw"></i> <a href="#" target="_blank">LinkedIn</a></li>
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
	jQuery(document).ready(function() {	
		if ( jQuery('.index-php').length ) {
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
		}
	});
	
	jQuery(window).on('load', function(){
		setTimeout(function(){
			jQuery('.jsinfo').removeClass('jsinfo');
		}, 350);
	});
</script>