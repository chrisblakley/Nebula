<div id="phg-welcome" class="welcome-panel-content gearside-welcome">

	<div id="welcome-content">
		<div class="logocon">
			<a href="<?php echo home_url(); ?>" target="_blank">
				<img class="welcome-logo" src="<?php echo get_template_directory_uri();?>/images/meta/favicon-48x48.png" alt="<?php bloginfo('name'); ?>"/>
			</a>
		</div>

		<h3><a href="<?php echo home_url(); ?>" target="_blank"><?php bloginfo('name'); ?></a></h3>
		<p class="about-description">Designed and Developed by <?php pinckneyhugogroup(1); ?>.</p>

		<hr/>

		<div class="welcome-panel-column-container">

			<div class="welcome-panel-column">
				<h4>Your Information</h4>

				<?php $user_info = get_userdata( get_current_user_id() ); ?>

				<ul>
					<li>
						<?php
							$headshotURL = esc_attr(get_the_author_meta('headshot_url', get_current_user_id()));
							$headshot_thumbnail = str_replace('.jpg', '-150x150.jpg' , $headshotURL);
						?>

						<?php if ( $headshot_thumbnail ) : ?>
							<img src="<?php echo esc_attr($headshot_thumbnail); ?>" style="max-width: 14px; border-radius: 100px; outline: 1px solid #fff; box-shadow: 0 0 4px 0 rgba(0,0,0,0.2);" />&nbsp;
						<?php else: ?>
							<i class="fa fa-user fa-fw"></i>&nbsp;
						<?php endif; ?>

						User: <strong class="admin-user-info admin-user-name"><?php echo $user_info->display_name; ?></strong>
					</li>

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
					<li>
						<i class="fa <?php echo $fa_role; ?> fa-fw"></i> Role: <strong class="admin-user-info admin-user-role"><?php echo $user_info->roles[0]; ?></strong>
						<?php if ( is_dev() ) : ?>
							<small>(Dev)</small>
						<?php endif; ?>
					</li>
					<li>
						<?php if ($_SERVER["REMOTE_ADDR"] == '72.43.235.106'): ?>
							<img src="<?php echo get_template_directory_uri(); ?>/images/phg/phg-symbol.png" onerror="this.onerror=null; this.src=""<?php echo get_template_directory_uri(); ?>/images/phg/phg-symbol.png" alt="Pinckney Hugo Group" style="max-width: 14px;"/>
						<?php else: ?>
							<i class="fa fa-laptop fa-fw"></i>
						<?php endif; ?>
						IP Address: <strong class="admin-user-info admin-user-ip"><?php echo $_SERVER["REMOTE_ADDR"]; ?></strong>
					</li>
				</ul>
			</div>

			<?php if ( current_user_can('manage_options') ) : ?>
				<div class="welcome-panel-column">
					<h4>Administration</h4>
					<ul>
						<?php if ( get_option('nebula_cpanel_url') != '' ) : ?>
							<li><i class="fa fa-gears fa-fw"></i> <a href="<?php echo get_option('nebula_cpanel_url'); ?>" target="_blank">Control Panel</a></li>
						<?php endif; ?>

						<?php if ( get_option('nebula_hosting_url') != '' ) : ?>
							<li><i class="fa fa-hdd-o fa-fw"></i> <a href="<?php echo get_option('nebula_hosting_url'); ?>" target="_blank">Hosting</a></li>
						<?php endif; ?>

						<?php if ( get_option('nebula_registrar_url') != '' ) : ?>
							<li><i class="fa fa-globe fa-fw"></i> <a href="<?php echo get_option('nebula_registrar_url'); ?>" target="_blank">Registrar</a></li>
						<?php endif; ?>

						<?php if ( get_option('nebula_ga_url') != '' ) : ?>
							<li><i class="fa fa-bar-chart-o fa-fw"></i> <a href="<?php echo get_option('nebula_ga_url'); ?>" target="_blank">Google Analytics</a></li>
						<?php endif; ?>

						<?php if ( get_option('nebula_google_webmaster_tools_url') != '' ) : ?>
							<li><i class="fa fa-google fa-fw"></i> <a href="<?php echo get_option('nebula_google_webmaster_tools_url'); ?>" target="_blank">Google Webmaster Tools</a></li>
						<?php endif; ?>

						<?php if ( get_option('nebula_mention_url') != '' ) : ?>
							<li><i class="fa fa-star fa-fw"></i> <a href="<?php echo get_option('nebula_mention_url'); ?>" target="_blank">Mention</a></li>
						<?php endif; ?>
					</ul>

					<?php if ( get_option('nebula_cpanel_url') == '' && get_option('nebula_hosting_url') == '' && get_option('nebula_registrar_url') == '' && get_option('nebula_ga_url') == '' && get_option('nebula_google_webmaster_tools_url') == '' && get_option('nebula_mention_url') == '' ) : ?>
						<p><em>Add administrative links to <strong><a href="themes.php?page=nebula_settings">Nebula Settings</a></strong> to see them here.</em></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="welcome-panel-column">
				<h4>Social</h4>
				<ul>
					<?php if ( get_option('nebula_facebook_url') != '' ) : ?>
						<li><i class="fa fa-facebook-square fa-fw"></i> <a href="<?php echo get_option('nebula_facebook_url'); ?>" target="_blank">Facebook</a></li>
					<?php endif; ?>

					<?php if ( get_option('nebula_twitter_url') != '' ) : ?>
						<li><i class="fa fa-twitter-square fa-fw"></i> <a href="<?php echo get_option('nebula_twitter_url'); ?>" target="_blank">Twitter</a></li>
					<?php endif; ?>

					<?php if ( get_option('nebula_google_plus_url') != '' ) : ?>
						<li><i class="fa fa-google-plus-square fa-fw"></i> <a href="<?php echo get_option('nebula_google_plus_url'); ?>" target="_blank">Google+</a></li>
					<?php endif; ?>

					<?php if ( get_option('nebula_linkedin_url') != '' ) : ?>
						<li><i class="fa fa-linkedin-square fa-fw"></i> <a href="<?php echo get_option('nebula_linkedin_url'); ?>" target="_blank">LinkedIn</a></li>
					<?php endif; ?>

					<?php if ( get_option('nebula_youtube_url') != '' ) : ?>
						<li><i class="fa fa-youtube-square fa-fw"></i> <a href="<?php echo get_option('nebula_youtube_url'); ?>" target="_blank">Youtube</a></li>
					<?php endif; ?>

					<?php if ( get_option('nebula_instagram_url') != '' ) : ?>
						<li><i class="fa fa-instagram fa-fw"></i> <a href="<?php echo get_option('nebula_instagram_url'); ?>" target="_blank">Instagram</a></li>
					<?php endif; ?>

					<?php if ( get_option('nebula_disqus_shortname') != '' ) : ?>
						<li><i class="fa fa-comments-o fa-fw"></i> <a href="https://<?php echo get_option('nebula_disqus_shortname'); ?>.disqus.com/admin/moderate/" target="_blank">Disqus</a></li>
					<?php endif; ?>
				</ul>

				<?php if ( get_option('nebula_facebook_url') == '' && get_option('nebula_twitter_url') == '' && get_option('nebula_google_plus_url') == '' && get_option('nebula_linkedin_url') == '' && get_option('nebula_youtube_url') == '' && get_option('nebula_instagram_url') == '' && get_option('nebula_disqus_shortname') == '' ) : ?>
					<?php if ( current_user_can('manage_options') ) : ?>
						<p>Add social links to <strong><a href="themes.php?page=nebula_settings">Nebula Settings</a></strong> to see them here.</em></p>
					<?php else : ?>
						<p><i class="fa fa-frown-o fa-fw"></i> <em>No social links are set up.</em></p>
					<?php endif; ?>
				<?php endif; ?>
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
					jQuery('.maptoggle').html("&laquo; Back");
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