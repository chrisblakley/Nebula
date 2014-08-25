<?php
	
	//Initialize the Nebula Submenu
	if ( is_admin() ) {
		add_action('admin_menu', 'nebula_sub_menu');
		add_action('admin_init', 'register_nebula_settings');
	}
	
	//Create the Nebula Submenu
	function nebula_sub_menu() {
		add_options_page('Nebula Settings', 'Nebula Settings', 'manage_options', 'nebula_settings', 'nebula_settings_page');
	}
	
	//Register each option
	function register_nebula_settings() {
		register_setting('nebula_settings_group', 'nebula_overall');
		
		register_setting('nebula_settings_group', 'nebula_contact_email');
		register_setting('nebula_settings_group', 'nebula_ga_tracking_id');
		register_setting('nebula_settings_group', 'nebula_keywords');
		register_setting('nebula_settings_group', 'nebula_news_keywords');
		register_setting('nebula_settings_group', 'nebula_phone_number');
		register_setting('nebula_settings_group', 'nebula_fax_number');
		register_setting('nebula_settings_group', 'nebula_latitude');
		register_setting('nebula_settings_group', 'nebula_longitude');
		register_setting('nebula_settings_group', 'nebula_street_address');
		register_setting('nebula_settings_group', 'nebula_locality');
		register_setting('nebula_settings_group', 'nebula_region');
		register_setting('nebula_settings_group', 'nebula_postal_code');
		register_setting('nebula_settings_group', 'nebula_country_name');
		
		register_setting('nebula_settings_group', 'nebula_business_hours_sunday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_sunday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_sunday_close');
		register_setting('nebula_settings_group', 'nebula_business_hours_monday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_monday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_monday_close');
		register_setting('nebula_settings_group', 'nebula_business_hours_tuesday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_tuesday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_tuesday_close');
		register_setting('nebula_settings_group', 'nebula_business_hours_wednesday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_wednesday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_wednesday_close');
		register_setting('nebula_settings_group', 'nebula_business_hours_thursday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_thursday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_thursday_close');
		register_setting('nebula_settings_group', 'nebula_business_hours_friday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_friday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_friday_close');
		register_setting('nebula_settings_group', 'nebula_business_hours_saturday_enabled');
		register_setting('nebula_settings_group', 'nebula_business_hours_saturday_open');
		register_setting('nebula_settings_group', 'nebula_business_hours_saturday_close');
		
		register_setting('nebula_settings_group', 'nebula_facebook_url');
		register_setting('nebula_settings_group', 'nebula_facebook_app_id');
		register_setting('nebula_settings_group', 'nebula_facebook_app_secret');
		register_setting('nebula_settings_group', 'nebula_facebook_access_token');
		register_setting('nebula_settings_group', 'nebula_google_plus_url');
		register_setting('nebula_settings_group', 'nebula_twitter_url');
		register_setting('nebula_settings_group', 'nebula_linkedin_url');
		register_setting('nebula_settings_group', 'nebula_youtube_url');
		register_setting('nebula_settings_group', 'nebula_instagram_url');
		
		register_setting('nebula_settings_group', 'nebula_admin_bar');
		register_setting('nebula_settings_group', 'nebula_comments');
		register_setting('nebula_settings_group', 'nebula_wp_core_updates_notify');
		register_setting('nebula_settings_group', 'nebula_phg_plugin_update_warning');
		register_setting('nebula_settings_group', 'nebula_phg_welcome_panel');
		register_setting('nebula_settings_group', 'nebula_unnecessary_metaboxes');
		register_setting('nebula_settings_group', 'nebula_phg_metabox');
		register_setting('nebula_settings_group', 'nebula_console_css');
		register_setting('nebula_settings_group', 'nebula_cse_id');
		register_setting('nebula_settings_group', 'nebula_cse_api_key');
		
		register_setting('nebula_settings_group', 'nebula_dev_ip');
		register_setting('nebula_settings_group', 'nebula_dev_email_domain');
		register_setting('nebula_settings_group', 'nebula_cpanel_url');
		register_setting('nebula_settings_group', 'nebula_hosting_url');
		register_setting('nebula_settings_group', 'nebula_registrar_url');
		register_setting('nebula_settings_group', 'nebula_ga_url');
		register_setting('nebula_settings_group', 'nebula_google_webmaster_tools_url');
	}
	
	//Output the settings page
	function nebula_settings_page(){
?>
		
		<style>
			.dependent.override,
			.mobiletitle.override {opacity: 0.4; pointer-events: none;}
			.form-table th {width: 250px;}
			a {-webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
			a.help {text-decoration: none; color: #ccc;}
				a.help:hover,
				a.help.active {color: #0074a2;}
			a.reset {text-decoration: none; color: red;}
			p.helper {display: none; color: #777;}
				p.helper.active {display: block;}
				
			input[type="text"],
			input[type="password"] {width: 206px; font-size: 12px;}
			
			.businessday span,
			.businessday input {-webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
				.businessday.closed span,
				.businessday.closed input {opacity: 0.4; pointer-events: none;}
				.businessday input[type="checkbox"] {opacity: 1 !important; pointer-events: all;}
			
			.mobiletitle {display: none;}
						
			@media only screen and (max-width: 782px) {
			
				.form-table th {width: 100%;}
				input[type="text"] {width: 100% !important;}
			
			}
			
			@media only screen and (max-width: 400px) {
				.nav-tab-wrapper {display: none;}
				.mobiletitle {display: block;}
				.form-table.dependent {display: block !important;}
				
				.businessday span {font-size: 12px; width: 80px !important;}
				input.business-hour {width: 23% !important; display: inline-block !important; font-size: 12px !important;}
			}
		</style>
		
		<script>
			jQuery(document).ready(function() {
				
				toggleDependents();
				
				jQuery('a.help').on('click', function(){
					jQuery(this).toggleClass('active');
					jQuery(this).parents('tr').find('p.helper').animate({
			        	height: 'toggle',
						opacity: 'toggle'
			        }, 250);
					return false;
				});
				
				jQuery('#nebula_overall').on('change', function(){
					toggleDependents();
				});
				
				function toggleDependents() {
					if ( jQuery('#nebula_overall').val() == 'disabled' || jQuery('#nebula_overall').val() == 'override' ) {
						jQuery('.dependent, .mobiletitle').addClass('override');
					} else {
						jQuery('.dependent, .mobiletitle').removeClass('override');
					}
				}
				
				jQuery('.nav-tab').on('click', function(){
					var tabID = jQuery(this).attr('id');
					jQuery('.nav-tab-active').removeClass('nav-tab-active').addClass('nav-tab-inactive');
					jQuery('#' + tabID).removeClass('nav-tab-inactive').addClass('nav-tab-active');
					jQuery('table.form-table.dependent').each(function(){
						if ( !jQuery(this).hasClass(tabID) ) {
							jQuery(this).fadeOut(250);
						} else {
							jQuery(this).fadeIn(250);
						}
					});
					return false;
				});
				
				businessHoursCheck();
				jQuery('.businessday input[type="checkbox"]').on('click', function(){
					businessHoursCheck();
				});
				
				function businessHoursCheck() {
					jQuery('.businessday input[type="checkbox"]').each(function(){
						if ( jQuery(this).prop('checked') ) {
							jQuery(this).parents('.businessday').removeClass('closed');
						} else {
							jQuery(this).parents('.businessday').addClass('closed');
						}
					});
				}
				
			});
		</script>
		
		
		<div class="wrap">			
			<h2>Nebula Settings</h2>
			<?php
				if (!current_user_can('manage_options')) {
				    wp_die('You do not have sufficient permissions to access this page.');
				}
			?>
			<p>These settings are optional overrides to the functions set by Nebula. This page is for convenience and is not needed if you feel like just modifying the functions.php file. It can also be disabled below, or overridden via functions.php if that makes you feel better.</p>
			
			<?php if ( get_option('nebula_overall') == 'override' ) : ?>
				<div id="setting-error-settings_updated" class="error settings-error"> 
					<p><strong>Override!</strong><br/>These settings have been overridden using functions.php. Remove the override to re-enable use of this page!</p>
				</div>
			<?php endif; ?>
					
			<hr/>
			
			<form method="post" action="options.php">
				<?php
					settings_fields('nebula_settings_group');
					do_settings_sections('nebula_settings_group');
				?>
				
				<?php
					//http://www.smashingmagazine.com/2011/10/20/create-tabs-wordpress-settings-pages/ //@TODO: Create tabs.
					//http://wordpress.stackexchange.com/questions/127493/wordpress-settings-api-implementing-tabs-on-custom-menu-page
				?>
				<table class="form-table global">
			        <tr valign="top">
			        	<th scope="row">Nebula Settings&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_overall" id="nebula_overall">
								<option value="enabled" <?php selected('enabled', get_option('nebula_overall')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_overall')); selected('override', get_option('nebula_overall')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Enable/Disable this settings page. If disabled, all settings will use <strong>default values</strong> and can only be edited via functions.php! This <strong>does not</strong> disable all settings!</small></p>
						</td>
			        </tr>
			    </table>
								
				<h2 class="nav-tab-wrapper">
		            <a id="metadata" class="nav-tab nav-tab-active" href="#">Metadata</a>
		            <a id="functions" class="nav-tab nav-tab-inactive" href="#">Functions</a>
		            <a id="administration" class="nav-tab nav-tab-inactive" href="#">Administration</a>
		        </h2>
				
				<h2 class="mobiletitle">Metadata</h2>
				<hr class="mobiletitle"/>
				
				<table class="form-table dependent metadata">
			        <tr valign="top">
			        	<th scope="row">Contact Email&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_contact_email" value="<?php echo get_option('nebula_contact_email'); ?>" placeholder="<?php echo get_option('admin_email', $GLOBALS['admin_user']->user_email); ?>" />
							<p class="helper"><small>The main contact email address. If left empty, the admin email address will be used (shown by placeholder).</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Google Analytics Tracking ID&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_ga_tracking_id" value="<?php echo get_option('nebula_ga_tracking_id'); ?>" placeholder="UA-00000000-1" />
							<p class="helper"><small>This will add the tracking number to the appropriate locations. If left empty, the tracking ID will need to be entered in <strong>functions.php</strong>.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Keywords&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_keywords" value="<?php echo get_option('nebula_keywords'); ?>" placeholder="Keywords" style="width: 392px;"/>
							<p class="helper"><small>Comma-separated list of keywords (without quotes) that will be used as keyword metadata.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">News Keywords&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_news_keywords" value="<?php echo get_option('nebula_news_keywords'); ?>" placeholder="News Keywords" style="width: 392px;"/>
							<p class="helper"><small>Comma-separated list of news events (without quotes) that will be used as news keyword metadata. Currently, this is a global setting. In the future it should be overwritten by a per-post custom field (or pull from Yoast or likewise). <a href="https://support.google.com/news/publisher/answer/68297" target="_blank">More information &raquo;</a></small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Phone Number&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_phone_number" value="<?php echo get_option('nebula_phone_number'); ?>" placeholder="1-315-478-6700" />
							<p class="helper"><small>The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Fax Number&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_fax_number" value="<?php echo get_option('nebula_fax_number'); ?>" placeholder="1-315-426-1392" />
							<p class="helper"><small>The fax number used for Open Graph data. Use the format: "1-315-426-1392".</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Geolocation&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							Lat: <input type="text" name="nebula_latitude" value="<?php echo get_option('nebula_latitude'); ?>" placeholder="43.0536854" style="width: 100px;"/>
							Long: <input type="text" name="nebula_longitude" value="<?php echo get_option('nebula_longitude'); ?>" placeholder="-76.1654569" style="width: 100px;"/>
							<p class="helper"><small>The latitude and longitude of the physical location (or headquarters if multiple locations). Use the format "43.0536854".</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Address&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_street_address" value="<?php echo get_option('nebula_street_address'); ?>" placeholder="760 West Genesee Street" style="width: 392px;"/><br/>
							<input type="text" name="nebula_locality" value="<?php echo get_option('nebula_locality'); ?>" placeholder="Syracuse"  style="width: 194px;"/>
							<input type="text" name="nebula_region" value="<?php echo get_option('nebula_region'); ?>" placeholder="NY"  style="width: 40px;"/>
							<input type="text" name="nebula_postal_code" value="<?php echo get_option('nebula_postal_code'); ?>" placeholder="13204"  style="width: 70px;"/>
							<input type="text" name="nebula_country_name" value="<?php echo get_option('nebula_country_name'); ?>" placeholder="USA"  style="width: 70px;"/>
							<p class="helper"><small>The address of the location (or headquarters if multiple locations).</small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">Business Hours&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_sunday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_sunday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Sunday:</span> <input class="business-hour" type="text" name="nebula_business_hours_sunday_open" value="<?php echo get_option('nebula_business_hours_sunday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_sunday_close" value="<?php echo get_option('nebula_business_hours_sunday_close'); ?>" style="width: 75px;"/>
							</div>
							
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_monday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_monday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Monday:</span> <input class="business-hour" type="text" name="nebula_business_hours_monday_open" value="<?php echo get_option('nebula_business_hours_monday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_monday_close" value="<?php echo get_option('nebula_business_hours_monday_close'); ?>" style="width: 75px;"/>
							</div>
							
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_tuesday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_tuesday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Tuesday:</span> <input class="business-hour" type="text" name="nebula_business_hours_tuesday_open" value="<?php echo get_option('nebula_business_hours_tuesday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_tuesday_close" value="<?php echo get_option('nebula_business_hours_tuesday_close'); ?>" placeholder="5:30 pm" style="width: 75px;"/>
							</div>
							
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_wednesday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_wednesday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Wednesday:</span> <input class="business-hour" type="text" name="nebula_business_hours_wednesday_open" value="<?php echo get_option('nebula_business_hours_wednesday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_wednesday_close" value="<?php echo get_option('nebula_business_hours_wednesday_close'); ?>" style="width: 75px;"/>
							</div>
							
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_thursday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_thursday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Thursday:</span> <input class="business-hour" type="text" name="nebula_business_hours_thursday_open" value="<?php echo get_option('nebula_business_hours_thursday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_thursday_close" value="<?php echo get_option('nebula_business_hours_thursday_close'); ?>" style="width: 75px;"/>
							</div>
							
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_friday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_friday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Friday:</span> <input class="business-hour" type="text" name="nebula_business_hours_friday_open" value="<?php echo get_option('nebula_business_hours_friday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_friday_close" value="<?php echo get_option('nebula_business_hours_friday_close'); ?>" style="width: 75px;"/>
							</div>
							
							<div class="businessday">
								<input type="checkbox" name="nebula_business_hours_saturday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_saturday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Saturday:</span> <input class="business-hour" type="text" name="nebula_business_hours_saturday_open" value="<?php echo get_option('nebula_business_hours_saturday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_saturday_close" value="<?php echo get_option('nebula_business_hours_saturday_close'); ?>" style="width: 75px;"/>
							</div>
							
							<p class="helper"><small>Open/Close times. Times should be in the format "5:30 pm" or "17:30". Uncheck all to disable this meta.</small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">Facebook&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							URL: <input type="text" name="nebula_facebook_url" value="<?php echo get_option('nebula_facebook_url'); ?>" placeholder="http://www.facebook.com/PinckneyHugo" style="width: 358px;"/><br/>
							App ID: <input type="text" name="nebula_facebook_app_id" value="<?php echo get_option('nebula_facebook_app_id'); ?>" placeholder="000000000000000" style="width: 153px;"/><br/>
							App Secret: <input type="password" name="nebula_facebook_app_secret" value="<?php echo get_option('nebula_facebook_app_secret'); ?>" placeholder="00000000000000000000000000000000" style="width: 311px;"/><br/>
							Access Token: <input type="text" name="nebula_facebook_access_token" value="<?php echo get_option('nebula_facebook_access_token'); ?>" placeholder="000000000000000|000000000000000000000000000" style="width: 295px;"/>
							<p class="helper"><small>The URL and App ID of the associated Facebook page/app. This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Google+&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							URL: <input type="text" name="nebula_google_plus_url" value="<?php echo get_option('nebula_google_plus_url'); ?>" placeholder="https://plus.google.com/106644717328415684498/about" style="width: 358px;"/>
							<p class="helper"><small>The URL of the associated Google+ page.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Twitter&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							URL: <input type="text" name="nebula_twitter_url" value="<?php echo get_option('nebula_twitter_url'); ?>" placeholder="https://twitter.com/pinckneyhugo" style="width: 358px;"/>
							<p class="helper"><small>The URL of the associated Twitter page.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">LinkedIn&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							URL: <input type="text" name="nebula_linkedin_url" value="<?php echo get_option('nebula_linkedin_url'); ?>" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" style="width: 358px;"/>
							<p class="helper"><small>The URL of the associated LinkedIn page.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Youtube&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							URL: <input type="text" name="nebula_youtube_url" value="<?php echo get_option('nebula_youtube_url'); ?>" placeholder="https://www.youtube.com/user/pinckneyhugo" style="width: 358px;"/>
							<p class="helper"><small>The URL of the associated YouTube page.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Instagram&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							URL: <input type="text" name="nebula_instagram_url" value="<?php echo get_option('nebula_instagram_url'); ?>" placeholder="https://www.instagram.com/pinckneyhugo" style="width: 358px;"/>
							<p class="helper"><small>The URL of the associated Instagram page.</small></p>
						</td>
			        </tr>
			        
			    </table>
				
				<h2 class="mobiletitle">Functions</h2>
				<hr class="mobiletitle"/>
				
				<table class="form-table dependent functions" style="display: none;">
			        <tr valign="top">
			        	<th scope="row">Admin Bar&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_admin_bar">
								<option value="default" <?php selected('default', get_option('nebula_admin_bar')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_admin_bar')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_admin_bar')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Control the Wordpress Admin bar globally on the frontend for all users. <em>(Default: Disabled)</em></small></p>
						</td>
			        </tr>
			         
			        <tr valign="top">
			        	<th scope="row">Comments&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_comments">
								<option value="default" <?php selected('default', get_option('nebula_comments')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_comments')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_comments')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Ability to force disable comments. If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles). <em>(Default: Disabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">Wordpress Core Update Notification&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_wp_core_updates_notify">
								<option value="default" <?php selected('default', get_option('nebula_wp_core_updates_notify')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_wp_core_updates_notify')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_wp_core_updates_notify')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Control whether or not the Wordpress Core update notifications show up on the admin pages. <em>(Default: Disabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">PHG Plugin Update Warning&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_phg_plugin_update_warning">
								<option value="default" <?php selected('default', get_option('nebula_phg_plugin_update_warning')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_phg_plugin_update_warning')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_phg_plugin_update_warning')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Control whether or not the plugin update warning appears on admin pages. <em>(Default: Enabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">PHG Welcome Panel&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_phg_welcome_panel">
								<option value="default" <?php selected('default', get_option('nebula_phg_welcome_panel')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_phg_welcome_panel')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_phg_welcome_panel')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Control the PHG Welcome Panel with useful links related to the project. <em>(Default: Enabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">Remove Unnecessary Metaboxes&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_unnecessary_metaboxes">
								<option value="default" <?php selected('default', get_option('nebula_unnecessary_metaboxes')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_unnecessary_metaboxes')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_unnecessary_metaboxes')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Remove metaboxes on the Dashboard that are not necessary for most users. <em>(Default: Enabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">PHG Developer Metabox&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_phg_metabox">
								<option value="default" <?php selected('default', get_option('nebula_phg_metabox')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_phg_metabox')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_phg_metabox')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Control the PHG Developer Metabox with useful server information. Requires a user with a matching email address domain to the "Developer Email Domains" setting (under the Administration tab). <em>(Default: Enabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">Console CSS&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_console_css">
								<option value="default" <?php selected('default', get_option('nebula_console_css')); ?>>Default</option>
								<option value="enabled" <?php selected('enabled', get_option('nebula_console_css')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_console_css')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Adds CSS to the browser console.<em>(Default: Enabled)</em></small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">CSE Engine ID&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_cse_id" value="<?php echo get_option('nebula_cse_id'); ?>" placeholder="000000000000000000000:aaaaaaaa_aa" style="width: 392px;" />
							<p class="helper"><small>Google Custom Search Engine ID (for <a href="http://gearside.com/nebula/documentation/bundled/page-suggestions/" target="_blank">page suggestions</a> on 404 and No Search Results pages). <a href="https://www.google.com/cse/manage/all">Register here</a>, then select "Add", input your website's URL in "Sites to Search". Then click the one you just made and click the "Search Engine ID" button.</small></p>
						</td>
			        </tr>
			        
			        <tr valign="top">
			        	<th scope="row">CSE API Key&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_cse_api_key" value="<?php echo get_option('nebula_cse_api_key'); ?>" style="width: 392px;" />
							<p class="helper"><small>Google Custom Search Engine API Key (for <a href="http://gearside.com/nebula/documentation/bundled/page-suggestions/" target="_blank">page suggestions</a> on 404 and No Search Results pages). On the <a href="https://console.developers.google.com/project">Developers Console</a> make a new project (if you don't have one yet). Then on the "APIs" page, find "Custom Search API" and toggle it on. Then under "Credentials" create a new key, choose "Browser Key".</small></p>
						</td>
			        </tr>
			    </table>
				
				<h2 class="mobiletitle">Administration</h2>
				<hr class="mobiletitle"/>
				
				<table class="form-table dependent administration" style="display: none;">
			        <tr valign="top">
			        	<th scope="row">Developer IPs&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_dev_ip" value="<?php echo get_option('nebula_dev_ip'); ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" style="width: 392px;" />
							<p class="helper"><small>Comma and space separated IP addresses of the developer to enable specific console logs and other dev info. Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<?php
			        		$current_user = wp_get_current_user();
							list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
						?>
			        	
			        	<th scope="row">Developer Email Domains&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_dev_email_domain" value="<?php echo get_option('nebula_dev_email_domain'); ?>" placeholder="<?php echo $current_user_domain; ?>" style="width: 392px;" />
							<p class="helper"><small>Comma and space separated domains of the developer emails (without the "@") to enable specific console logs and other dev info. Your email domain is: <strong><?php echo $current_user_domain; ?></strong></small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Control Panel&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<?php
								$serverProtocol = 'http://';
								if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ) {
									$serverProtocol = 'https://';
								}
							?>
							<input type="text" name="nebula_cpanel_url" value="<?php echo get_option('nebula_cpanel_url'); ?>" placeholder="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" style="width: 392px;" />
							<p class="helper"><small>Link to the control panel of the hosting account. cPanel on this domain would be <a href="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" target="_blank"><?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082</a>.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Hosting&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<?php
								$hostURL = explode(".", gethostname());
							?>
							<input type="text" name="nebula_hosting_url" value="<?php echo get_option('nebula_hosting_url'); ?>" placeholder="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>/" style="width: 392px;" />
							<p class="helper"><small>Link to the server host for easy access to support and other information. Server detected as <a href="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>" target="_blank">http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?></a>.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Domain Registrar&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<?php
								//$whois = simplexml_load_string(file_get_contents('http://whomsy.com/api/' . $_SERVER['SERVER_NAME'] . '?output=xml'));
								//$whois = file_get_contents('http://api.sudostuff.com/whois/' . $_SERVER['SERVER_NAME']);
								//var_dump($whois);
							?>
							<input type="text" name="nebula_registrar_url" value="<?php echo get_option('nebula_registrar_url'); ?>" placeholder="http://" style="width: 392px;" />
							<p class="helper"><small>Link to the domain registrar used for access to pointers, forwarding, and other information.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Google Analytics URL&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_ga_url" value="<?php echo get_option('nebula_ga_url'); ?>" placeholder="http://www.google.com/analytics/..." style="width: 392px;" />
							<p class="helper"><small>Link directly to this project's Google Analytics report.</small></p>
						</td>
			        </tr>
			        <tr valign="top">
			        	<th scope="row">Google Webmaster Tools URL&nbsp;<a class="help" href="#"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" name="nebula_google_webmaster_tools_url" value="<?php echo get_option('nebula_google_webmaster_tools_url'); ?>" placeholder="https://www.google.com/webmasters/tools/..." style="width: 392px;" />
							<p class="helper"><small>Direct link to this project's Google Webmaster Tools.</small></p>
						</td>
			        </tr>
			    </table>
				
				<?php if (1==2) : //Examples of different field types ?>
					<input type="checkbox" name="some_other_option" value="<?php echo get_option('some_other_option_check'); ?>" <?php checked('1', get_option('some_other_option_check')); ?> />
				<?php endif; ?>
				
				<?php submit_button(); ?>
				
			</form>
		</div><!--/wrap-->
<?php } ?>