<?php

/*==========================
 Global Nebula Options Conditional Functions
 Options are for customizing Nebula functionality to the needs of each project.
 Data is for values that are used by Nebula functionality to check for reference and conditions.
 ===========================*/

//If the desired option is an enabled/disabled dropdown check against that, else check for the option and return the default.
//Dropdowns: $operand is what to check against (typically 'enabled' or 'disabled').
//Texts: $operand is the default value to return if option is false.
function nebula_option($option, $operand=false){
	$nebula_options = get_option('nebula_options');
	$requested_dropdown = in_array(strtolower($operand), array('enabled', 'disabled'));

	if ( empty($nebula_options[$option]) ){
		if ( $requested_dropdown ){
			return false;
		}
		return $operand;
	}

	$data = $nebula_options[$option];
	$data_dropdown = ( is_string($data) )? in_array(strtolower($data), array('enabled', 'disabled')) : false;

	if ( $requested_dropdown ){ //If $operand suggests a dropdown option, match $data against it.
		if ( strtolower($data) == strtolower($operand) ){
			return true;
		}
		return false;
	} elseif ( $data_dropdown ){ //If $operand is a default fallback, but $data suggests a dropdown option.
		if ( strtolower($data) == 'enabled' ){
			return true;
		}
		return $operand;
	}
	return $data;
}

//Update Nebula options outside of the Nebula Options page
function nebula_update_option($option, $value){
	$nebula_data = get_option('nebula_options');
	if ( $nebula_data[$option] != $value ){
		$nebula_data[$option] = $value;
		update_option('nebula_options', $nebula_data);
	}
}

//Retrieve non-option Nebula data
function nebula_data($option){
	$nebula_data = get_option('nebula_data');
	if ( empty($nebula_data[$option]) ){
		return false;
	}
	return $nebula_data[$option];
}

//Update data outside of the Nebula Options page
function nebula_update_data($option, $value){
	$nebula_data = get_option('nebula_data');
	if ( $nebula_data[$option] != $value ){
		$nebula_data[$option] = $value;
		update_option('nebula_data', $nebula_data);
	}
}

/*==========================
 Specific Options Functions
 When using in templates these simplify the syntax to be less confusing.
 ===========================*/

function nebula_full_address($encoded=false){
	$nebula_options = get_option('nebula_options');

	if ( !$nebula_options['street_address'] ){
		return false;
	}

	$full_address = $nebula_options['street_address'] . ', ' . $nebula_options['locality'] . ', ' . $nebula_options['region'] . ' ' . $nebula_options['postal_code'];
    if ( $encoded ){
	    $full_address = str_replace(array(', ', ' '), '+', $full_address);
    }
	return $full_address;
}

//Get the full Twitter URL for a user
function nebula_twitter_url($username=false){
	if ( empty($username) ){
		$username = nebula_option('twitter_username');
	}

	if ( !empty($username) ){
		return 'https://twitter.com/' . $username;
	}

	return false;
}

//Register the requested version of Bootstrap.
function nebula_bootrap_version($lang=false){
	if ( nebula_option('bootstrap_version') === 'bootstrap3' ){
		//Bootstrap 3 (IE8+ Support)
		if ( $lang === 'css' ){
			return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css', null, '3.3.7', 'all');
		} elseif ( $lang === 'js' ){
			return nebula_register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', 'defer', null, '3.3.7', true);
		}
	} elseif ( nebula_option('bootstrap_version') === 'bootstrap4a5' ){
		//Bootstrap 4 alpha 5 (IE9+ Support)
		if ( $lang === 'css' ){
			return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/css/bootstrap.min.css', null, '4.0.0a5', 'all');
		} elseif ( $lang === 'js' ){
			return nebula_register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/js/bootstrap.min.js', 'defer', null, '4.0.0a5', true);
		}
	}

	//Latest (IE10+)
	if ( $lang === 'css' ){
		return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', null, '4.0.0a6', 'all');
	} elseif ( $lang === 'js' ){
		return nebula_register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/js/bootstrap.min.js', 'defer', null, '4.0.0a6', true);
	}
}

//Prepare default data values
function nebula_default_data(){
	$nebula_data_defaults = array(
		'initialized' => '',
		'scss_last_processed' => 0,
		'next_version' => '',
		'current_version' => nebula_version('raw'),
		'current_version_date' => nebula_version('date'),
		'version_legacy' => 'false',
		'users_status' => '',
	);
	return $nebula_data_defaults;
}

//Prepare default option values
function nebula_default_options(){
	$nebula_options_defaults = array(
		'edited_yet' => 'false',

		//Metadata Tab
		'site_owner' => '',
		'contact_email' => '',
		'phone_number' => '',
		'fax_number' => '',
		'latitude' => '',
		'longitude' => '',
		'street_address' => '',
		'locality' => '',
		'region' => '',
		'postal_code' => '',
		'country_name' => '',
		'business_hours_sunday_enabled' => '',
		'business_hours_sunday_open' => '',
		'business_hours_sunday_close' => '',
		'business_hours_monday_enabled' => '',
		'business_hours_monday_open' => '',
		'business_hours_monday_close' => '',
		'business_hours_tuesday_enabled' => '',
		'business_hours_tuesday_open' => '',
		'business_hours_tuesday_close' => '',
		'business_hours_wednesday_enabled' => '',
		'business_hours_wednesday_open' => '',
		'business_hours_wednesday_close' => '',
		'business_hours_thursday_enabled' => '',
		'business_hours_thursday_open' => '',
		'business_hours_thursday_close' => '',
		'business_hours_friday_enabled' => '',
		'business_hours_friday_open' => '',
		'business_hours_friday_close' => '',
		'business_hours_saturday_enabled' => '',
		'business_hours_saturday_open' => '',
		'business_hours_saturday_close' => '',
		'business_hours_closed' => '',
		'facebook_url' => '',
		'facebook_page_id' => '',
		'facebook_admin_ids' => '',
		'facebook_app_secret' => '',
		'facebook_access_token' => '',
		'google_plus_url' => '',
		'twitter_username' => '',
		'linkedin_url' => '',
		'youtube_url' => '',
		'instagram_url' => '',

		//Functions Tab
		'bootstrap_version' => 'latest',
		'prototype_mode' => 'disabled',
		'wireframe_theme' => '',
		'staging_theme' => '',
		'production_theme' => '',
		'admin_bar' => 'enabled',
		'admin_notices' => 'enabled',
		'dev_info_metabox' => 'enabled',
		'todo_manager_metabox' => 'enabled',
		'author_bios' => 'disabled',
		'comments' => 'disabled',
		'device_detection' => 'disabled',
		'ip_geolocation' => 'disabled',
		'adblock_detect' => 'disabled',
		'domain_blacklisting' => 'enabled',
		'weather' => 'disabled',
		'theme_update_notification' => 'enabled',
		'wp_core_updates_notify' => 'enabled',
		'plugin_update_warning' => 'enabled',
		'unnecessary_metaboxes' => 'enabled',
		'visitors_db' => 'disabled',
		'scss' => 'disabled',
		'minify_css' => 'disabled',
		'dev_stylesheets' => 'disabled',
		'console_css' => 'enabled',

		//Analytics Tab
		'ga_tracking_id' => '',
		'ga_wpuserid' => 'disabled',
		'ga_displayfeatures' => 'disabled',
		'ga_linkid' => 'enabled',
		'adwords_remarketing_conversion_id' => '',
		'google_optimize_id' => '',
		'hostnames' => '',
		'google_search_console_verification' => '',
		'facebook_custom_audience_pixel_id' => '',
		'cd_author' => '',
		'cd_businesshours' => '',
		'cd_categories' => '',
		'cd_tags' => '',
		'cd_contactmethod' => '',
		'cd_formtiming' => '',
		'cd_firstinteraction' => '',
		'cd_geolocation' => '',
		'cd_geoname' => '',
		'cd_geoaccuracy' => '',
		'cd_notablepoi' => '',
		'cd_relativetime' => '',
		'cd_scrolldepth' => '',
		'cd_maxscroll' => '',
		'cd_sessionid' => '',
		'cd_timestamp' => '',
		'cd_windowtype' => '',
		'cd_userid' => '',
		'cd_fbid' => '',
		'cd_role' => '',
		'cd_videowatcher' => '',
		'cd_eventintent' => '',
		'cd_woocart' => '',
		'cd_woocustomer' => '',
		'cd_weather' => '',
		'cd_temperature' => '',
		'cd_publishyear' => '',
		'cd_wordcount' => '',
		'cd_adblocker' => '',
		'cd_mqbreakpoint' => '',
		'cd_mqresolution' => '',
		'cd_mqorientation' => '',

		'cm_formviews' => '',
		'cm_formstarts' => '',
		'cm_formsubmissions' => '',
		'cm_notabledownloads' => '',
		'cm_engagedreaders' => '',
		'cm_videoplaytime' => '',
		'cm_videostarts' => '',
		'cm_videocompletions' => '',
		'cm_autocompletesearches' => '',
		'cm_autocompletesearchclicks' => '',
		'cm_pagevisible' => '',
		'cm_pagehidden' => '',
		'cm_wordcount' => '',

		//APIs Tab
		'google_font_url' => '',
		'gcm_sender_id' => '',
		'google_server_api_key' => '',
		'google_browser_api_key' => '',
		'cse_id' => '',
		'hubspot_api' => '',
		'hubspot_portal' => '',
		'disqus_shortname' => '',
		'facebook_app_id' => '',
		'twitter_consumer_key' => '',
		'twitter_consumer_secret' => '',
		'twitter_bearer_token' => '',
		'instagram_user_id' => '',
		'instagram_access_token' => '',
		'instagram_client_id' => '',
		'instagram_client_secret' => '',

		//Administration Tab
		'dev_ip' => '',
		'dev_email_domain' => '',
		'client_ip' => '',
		'client_email_domain' => '',
		'notableiplist' => '',
		'cpanel_url' => '',
		'hosting_url' => '',
		'registrar_url' => '',
		'ga_url' => '',
		'google_search_console_url' => '',
		'google_adsense_url' => '',
		'google_adwords_url' => '',
		'mention_url' => '',
		'notes' => '',
	);
	return $nebula_options_defaults;
}

//Register all Nebula Options
add_action('current_screen', 'register_nebula_options');
function register_nebula_options(){
	$current_screen = get_current_screen();
	if ( $current_screen->base === 'appearance_page_nebula_options' || $current_screen->base === 'options' ){
		register_setting('nebula_options', 'nebula_options');
	}
}

//Output the options page
//This is called from nebula_admin.php
function nebula_options_page(){
?>
	<script>
		jQuery(document).ready(function(){
			jQuery('a.help').on('click tap touch', function(){
				jQuery(this).toggleClass('active').parents('tr').find('p.helper').animate({
		        	height: 'toggle',
					opacity: 'toggle'
		        }, 250);
				return false;
			});

			jQuery('.nav-tab').on('click tap touch', function(){
				var tabID = jQuery(this).attr('id');
				jQuery('.nav-tab-active').removeClass('nav-tab-active').addClass('nav-tab-inactive');
				jQuery('#' + tabID).removeClass('nav-tab-inactive').addClass('nav-tab-active');
				jQuery('table.form-table.dependent').each(function(){
					if ( !jQuery(this).hasClass(tabID) ){
						jQuery(this).fadeOut(250);
					} else {
						jQuery(this).fadeIn(250);
					}
				});
				return false;
			});

			wireframeModeToggle();
			jQuery('#prototypemodeselect').on('change', function(){
				wireframeModeToggle();
			});
			function wireframeModeToggle(){
				if ( jQuery('#prototypemodeselect').val() === 'enabled' ){
					jQuery('.wireframerequired').css('opacity', '1').find('select').css('pointer-events', 'all');
				} else {
					jQuery('.wireframerequired').css('opacity', '0.5').find('select').css('pointer-events', 'none');
				}
			}

			//Pull content from full meta tag HTML (Google Search Console)
			jQuery('#nebula_google_search_console_verification').on('paste change blur', function(){
				var gwtInputValue = jQuery('#nebula_google_search_console_verification').val();
				if ( gwtInputValue.indexOf('<meta') >= 0 ){
					var gwtContent = gwtInputValue.slice(gwtInputValue.indexOf('content="')+9, gwtInputValue.indexOf('" />'));
					jQuery('#nebula_google_search_console_verification').val(gwtContent);
				}
			});

			//Pull content from full Google Fonts HTML
			jQuery('#nebula_google_font_url').on('paste change blur', function(){
				var gfInputValue = jQuery('#nebula_google_font_url').val();
				if ( gfInputValue.indexOf('<link href=') >= 0 ){
					var gfContent = gfInputValue.replace(/(<link href=')|(' rel='stylesheet' type='text\/css'>)|(@import url\()|(\);)/g, '');
					jQuery('#nebula_google_font_url').val(gfContent);
				}
			});

			//Validate custom dimension IDs
			jQuery('input.dimension').on('blur keyup paste change', function(){
				var usedDimensions = new Array();
				jQuery('input.dimension').not(this).each(function(){
					if ( jQuery(this).val().match(/^dimension([0-9]{1,3})$/i) && jQuery(this).val() != '' ){
						usedDimensions.push(jQuery(this).val());
					}
				});

				if ( (jQuery(this).val().match(/^dimension([0-9]{1,3})$/i) && usedDimensions.indexOf(jQuery(this).val()) < 0) || jQuery(this).val() == '' ){
					jQuery(this).removeClass('error');
				} else {
					jQuery(this).addClass('error');
				}
			});

			//Validate custom metric IDs
			jQuery('input.metric').on('blur keyup paste change', function(){
				var usedMetrics = new Array();
				jQuery('input.metric').not(this).each(function(){
					if ( jQuery(this).val().match(/^metric([0-9]{1,3})$/i) && jQuery(this).val() != '' ){
						usedMetrics.push(jQuery(this).val());
					}
				});

				if ( (jQuery(this).val().match(/^metric([0-9]{1,3})$/i) && usedMetrics.indexOf(jQuery(this).val()) < 0) || jQuery(this).val() == '' ){
					jQuery(this).removeClass('error');
				} else {
					jQuery(this).addClass('error');
				}
			});

		});
	</script>

	<div class="wrap">
		<h2>Nebula Options</h2>
		<?php
			if ( !current_user_can('manage_options') && !is_dev() ){
				wp_die('You do not have sufficient permissions to access this page.');
			}
		?>

		<?php if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' ): ?>
			<div class="updated notice is-dismissible">
				<p><strong>Nebula Options</strong> have been updated. All SCSS files have been re-processed.</p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php
				settings_fields('nebula_options');
				do_settings_sections('nebula_options');
				$nebula_data = get_option('nebula_data');
				$nebula_options = get_option('nebula_options');
			?>

			<table class="form-table global">
		        <tr class="hidden" valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Diagnostics</h3>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Initialized?&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo $nebula_data['initialized']; ?>" readonly />
						<p><small>Initialized on <strong><?php echo date('F j, Y \a\t g:ia', $nebula_data['initialized']); ?></strong> (<?php echo $years_ago = number_format((time()-$nebula_data['initialized'])/31622400, 2); ?> <?php echo ( $years_ago == 1 )? 'year' : 'years'; ?> ago).</small></p>
						<p class="helper"><small>Shows the date of the initial Nebula Automation if it has run yet, otherwise it is empty.</small></p>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Edited Yet?&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" name="nebula_options[edited_yet]" value="true" readonly />
						<p class="helper"><small>This is pre-set to "true" so that when the user clicks "Save Changes" it becomes stored in the DB. Therefore, this will always say "true" even if it hasn't actually been saved yet!<br/>Has it <em>actually</em> been saved yet? <strong><?php echo ( $nebula_options['edited_yet'] )? 'Yes' : 'No'; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Current Version Number&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo $nebula_data['current_version']; ?>" readonly />
						<p class="helper"><small>This is the Nebula version number when it was last saved. It should match: <strong><?php echo nebula_version('raw'); ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Last Version Date&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo $nebula_data['current_version_date']; ?>" readonly />
						<p class="helper"><small>This is the Nebula version date when it was last saved. It should match: <strong><?php echo nebula_version('date'); ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Legacy Version?&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo $nebula_data['version_legacy']; ?>" readonly />
						<p class="helper"><small>If a future version is deemed incompatible with previous versions, this will become true, and theme update checks will be disabled. Incompatible versions will be labeled with a "u" at the end of the version number.</small></p>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Latest Github Version&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" name="nebula_options[next_version]" value="<?php echo $nebula_data['next_version']; ?>" readonly />
						<p class="helper"><small>The latest version available on Github. Re-checks with <a href="/update-core.php">theme update check</a> <strong>only when Nebula Child is activated</strong>.</small></p>
					</td>
		        </tr>
		        <tr class="short hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Online Users&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo nebula_online_users(); ?>" readonly />
						<p class="helper"><small>Currently online and last seen times of logged in users. A value of 1 or greater indicates it is working.</small></p>
					</td>
		        </tr>
		    </table>

			<h2 class="nav-tab-wrapper">
	            <a id="metadata" class="nav-tab nav-tab-active" href="#">Metadata</a>
	            <a id="functions" class="nav-tab nav-tab-inactive" href="#">Functions</a>
	            <a id="analytics" class="nav-tab nav-tab-inactive" href="#">
		            Analytics
					<?php if ( !$nebula_options['ga_tracking_id'] ): ?>
		        		<i class="fa fa-exclamation-circle" title="Warning: No Google Analytics Tracking ID!" style="cursor: help;"></i>
		        	<?php endif; ?>
		        </a>
	            <a id="apis" class="nav-tab nav-tab-inactive" href="#">APIs</a>
	            <a id="administration" class="nav-tab nav-tab-inactive" href="#">Administration</a>
	        </h2>

			<h2 class="mobiletitle">Metadata</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent metadata">
		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Site Information</h3>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Site Owner&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[site_owner]" value="<?php echo nebula_option('site_owner'); ?>" placeholder="<?php echo bloginfo('name'); ?>" />
						<p class="helper"><small>The name of the company (or person) who this website is for. This is used when using nebula_the_author(0) with author names disabled.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Contact Email&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[contact_email]" value="<?php echo $nebula_options['contact_email']; ?>" placeholder="<?php echo get_option('admin_email', get_userdata(1)->user_email); ?>" />
						<p class="helper"><small>The main contact email address. If left empty, the admin email address will be used (shown by placeholder).</small></p>
					</td>
		        </tr>


		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Business Information</h3>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Phone Number&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[phone_number]" value="<?php echo $nebula_options['phone_number']; ?>" placeholder="1-315-478-6700" />
						<p class="helper"><small>The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Fax Number&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[fax_number]" value="<?php echo $nebula_options['fax_number']; ?>" placeholder="1-315-426-1392" />
						<p class="helper"><small>The fax number used for Open Graph data. Use the format: "1-315-426-1392".</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Geolocation&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Lat: <input type="text" name="nebula_options[latitude]" value="<?php echo $nebula_options['latitude']; ?>" placeholder="43.0536854" style="width: 120px;" />
						Long: <input type="text" name="nebula_options[longitude]" value="<?php echo $nebula_options['longitude']; ?>" placeholder="-76.1654569" style="width: 120px;" />
						<p class="helper"><small>The latitude and longitude of the physical location (or headquarters if multiple locations). Use the format "43.0536854".</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Address&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[street_address]" value="<?php echo $nebula_options['street_address']; ?>" placeholder="760 West Genesee Street" /><br />
						<input type="text" name="nebula_options[locality]" value="<?php echo $nebula_options['locality']; ?>" placeholder="Syracuse"  style="width: 194px;" />
						<input type="text" name="nebula_options[region]" value="<?php echo $nebula_options['region']; ?>" placeholder="NY"  style="width: 70px;" />
						<input type="text" name="nebula_options[postal_code]" value="<?php echo $nebula_options['postal_code']; ?>" placeholder="13204"  style="width: 70px;" />
						<input type="text" name="nebula_options[country_name]" value="<?php echo $nebula_options['country_name']; ?>" placeholder="USA"  style="width: 70px;" />
						<p class="helper"><small>The address of the location (or headquarters if multiple locations).</small></p>
					</td>
		        </tr>

				<?php $weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'); ?>

		        <tr class="short" valign="top">
		        	<th scope="row">Business Hours&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<?php foreach ( $weekdays as $weekday ): ?>
							<div class="businessday">
								<input type="checkbox" name="nebula_options[business_hours_<?php echo $weekday; ?>_enabled]" value="1" <?php checked('1', !empty($nebula_options['business_hours_' . $weekday . '_enabled'])); ?> />
								<span style="display: inline-block; width: 85px;"><?php echo ucfirst($weekday); ?>:</span>
								<input class="business-hour" type="text" name="nebula_options[business_hours_<?php echo $weekday; ?>_open]" value="<?php echo $nebula_options['business_hours_' . $weekday . '_open']; ?>" style="width: 60px;" />
								&nbsp;&ndash;&nbsp;
								<input class="business-hour" type="text" name="nebula_options[business_hours_<?php echo $weekday; ?>_close]" value="<?php echo $nebula_options['business_hours_' . $weekday . '_close']; ?>" style="width: 60px;"  />
							</div>
						<?php endforeach; ?>
						<p class="helper"><small>Open/Close times. Times should be in the format "5:30 pm" or "17:30". Uncheck all to disable this meta.</small></p>
					</td>
		        </tr>

				<tr id="daysoff" class="short" valign="top">
		        	<th scope="row">Days Off&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<textarea name="nebula_options[business_hours_closed]"><?php echo $nebula_options['business_hours_closed']; ?></textarea>
						<p class="helper"><small>Comma-separated list of special days the business is closed (like holidays). These can be date formatted, or day of the month (Ex: "7/4" for Independence Day, or "Last Monday of May" for Memorial Day, or "Fourth Thursday of November" for Thanksgiving). <a href="http://mistupid.com/holidays/" target="_blank">Here is a good reference for holiday occurrences.</a><br /><strong>Note:</strong> This function assumes days off that fall on weekends are observed the Friday before or the Monday after.</small></p>
					</td>
		        </tr>

				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Social Networks</h3>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Facebook&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						URL: <input type="text" name="nebula_options[facebook_url]" value="<?php echo $nebula_options['facebook_url']; ?>" placeholder="http://www.facebook.com/PinckneyHugo" style="width: 358px;"/><br />
						Page ID: <input type="text" name="nebula_options[facebook_page_id]" value="<?php echo $nebula_options['facebook_page_id']; ?>" placeholder="000000000000000" style="width: 153px;"/><br />
						Admin IDs: <input type="text" name="nebula_options[facebook_admin_ids]" value="<?php echo $nebula_options['facebook_admin_ids']; ?>" placeholder="0000, 0000, 0000" style="width: 153px;"/><br />
						<p class="helper"><small>The URL (and optional page ID and admin IDs) of the associated Facebook page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google+ URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[google_plus_url]" value="<?php echo $nebula_options['google_plus_url']; ?>" placeholder="https://plus.google.com/106644717328415684498/about" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated Google+ page. It is important to register with <a href="http://www.google.com/business/" target="_blank">Google Business</a> for the geolocation benefits (among other things)!</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Twitter Username&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[twitter_username]" value="<?php echo $nebula_options['twitter_username']; ?>" placeholder="@pinckneyhugo" style="width: 358px;"/><br />
						<p class="helper"><small>The username of the associated Twitter profile (<strong>with</strong> the @ symbol).</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">LinkedIn URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[linkedin_url]" value="<?php echo $nebula_options['linkedin_url']; ?>" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated LinkedIn page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Youtube URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[youtube_url]" value="<?php echo $nebula_options['youtube_url']; ?>" placeholder="https://www.youtube.com/user/pinckneyhugo" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated YouTube page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Instagram URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[instagram_url]" value="<?php echo $nebula_options['instagram_url']; ?>" placeholder="https://www.instagram.com/pinckneyhugo" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated Instagram page.</small></p>
					</td>
		        </tr>
		    </table>



			<h2 class="mobiletitle">Functions</h2>
			<hr class="mobiletitle"/>

			<?php $nebula_options_defaults = nebula_default_options(); ?>

			<table class="form-table dependent functions" style="display: none;">
				<tr class="short" valign="top">
		        	<th scope="row">Bootstrap Version&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select id="bootstrapversionselect" name="nebula_options[bootstrap_version]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['bootstrap_version']); ?></option>
							<option value="latest" <?php selected('latest', $nebula_options['bootstrap_version']); ?>>Latest</option>
							<option value="bootstrap4a5" <?php selected('bootstrap4a5', $nebula_options['bootstrap_version']); ?>>Bootstrap 4 alpha 5</option>
							<option value="bootstrap3" <?php selected('bootstrap3', $nebula_options['bootstrap_version']); ?>>Bootstrap 3</option>
						</select>
						<p class="helper"><small>Bootstrap 3 will support IE8+. Bootstrap 4 alpha 5 will support IE9+. Bootstrap latest only supports IE10+. <em>(Default: <?php echo ucwords($nebula_options_defaults['bootstrap_version']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Prototyping</h3>
					</td>
		        </tr>
				<tr class="short" valign="top">
		        	<th scope="row">Prototype Mode&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select id="prototypemodeselect" name="nebula_options[prototype_mode]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['prototype_mode']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['prototype_mode']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['prototype_mode']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>When prototyping, enable this setting. Use the wireframe theme and production theme settings to develop the site while referencing the prototype. Use the staging theme to edit the site or develop new features while the site is live. If the staging theme is the active theme, use the Advanced Setting dropdown for "Theme For Everything" and choose a theme there for general visitors (<em>Note: If using this setting, you may need to select that same theme for the admin-ajax option too!</em>). <em>(Default: <?php echo ucwords($nebula_options_defaults['prototype_mode']); ?>)</em></small></p>
					</td>
		        </tr>

		        <?php $themes = wp_get_themes(); ?>
		        <tr class="short wireframerequired" valign="top">
		        	<th scope="row">Wireframe Theme&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select id="wireframetheme" name="nebula_options[wireframe_theme]">
							<option value="" <?php selected('', $nebula_options['wireframe_theme']); ?>>None</option>
							<?php foreach ( $themes as $key => $value ): ?>
								<option value="<?php echo $key; ?>" <?php selected($key, $nebula_options['wireframe_theme']); ?>><?php echo $value->get('Name') . ' (' . $key . ')'; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="helper"><small>The theme to use as the wireframe. Viewing this theme will trigger a greyscale view.</small></p>
					</td>
		        </tr>

		        <tr class="short wireframerequired" valign="top">
		        	<th scope="row">Staging Theme&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select id="stagingtheme" name="nebula_options[staging_theme]">
							<option value="" <?php selected('', $nebula_options['staging_theme']); ?>>None</option>
							<?php foreach ( $themes as $key => $value ): ?>
								<option value="<?php echo $key; ?>" <?php selected($key, $nebula_options['staging_theme']); ?>><?php echo $value->get('Name') . ' (' . $key . ')'; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="helper"><small>The theme to use for staging new features. This is useful for site development after launch.</small></p>
					</td>
		        </tr>

		        <tr class="short wireframerequired" valign="top">
		        	<th scope="row">Production/Live Theme&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select id="productiontheme" name="nebula_options[production_theme]">
							<option value="" <?php selected('', $nebula_options['production_theme']); ?>>None</option>
							<?php foreach ( $themes as $key => $value ): ?>
								<option value="<?php echo $key; ?>" <?php selected($key, $nebula_options['production_theme']); ?>><?php echo $value->get('Name') . ' (' . $key . ')'; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="helper"><small>The theme to use for production/live. This theme will become the live site.</small></p>
					</td>
		        </tr>

				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Front-End</h3>
					</td>
		        </tr>
				<tr class="short" valign="top">
		        	<th scope="row">Author Bios&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[author_bios]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['author_bios']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['author_bios']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['author_bios']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Allow authors to have bios that show their info (and post archives). This also enables searching by author, and displaying author names on posts. If disabled, the author page attempts to redirect to an About Us page. <em>(Default: <?php echo ucwords($nebula_options_defaults['author_bios']); ?>)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Comments&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[comments]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['comments']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['comments']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['comments']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Ability to force disable comments. If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles). <em>(Default: <?php echo ucwords($nebula_options_defaults['comments']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Device Detection&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[device_detection]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['device_detection']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['device_detection']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['device_detection']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Detect information about the user's device and browser. Useful for cross-browser support. <em>(Default: <?php echo ucwords($nebula_options_defaults['device_detection']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">IP Geolocation&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[ip_geolocation]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['ip_geolocation']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['ip_geolocation']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['ip_geolocation']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Lookup the country, region, and city of the user based on their IP address. This can be used for content as well as analytics (including Visitors Database) <em>(Default: <?php echo ucwords($nebula_options_defaults['ip_geolocation']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Domain Blacklisting&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[domain_blacklisting]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['domain_blacklisting']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['domain_blacklisting']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['domain_blacklisting']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Block traffic from known spambots and other illegitimate domains. <em>(Default: <?php echo ucwords($nebula_options_defaults['domain_blacklisting']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Ad Block Detection&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[adblock_detect]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['adblock_detect']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['adblock_detect']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['adblock_detect']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Detect if visitors are using ad blocking software. To track in Google Analytics, add a dimension index under the "Analytics" tab. <em>(Default: <?php echo ucwords($nebula_options_defaults['adblock_detect']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Console CSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[console_css]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['console_css']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['console_css']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['console_css']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Adds CSS to the browser console. <em>(Default: <?php echo ucwords($nebula_options_defaults['console_css']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Weather Detection&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[weather]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['weather']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['weather']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['weather']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Lookup weather conditions for locations. Can be used for changing content as well as analytics. <em>(Default: <?php echo ucwords($nebula_options_defaults['weather']); ?>)</em></small></p>
					</td>
		        </tr>


				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Stylesheets</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">SCSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[scss]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['scss']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['scss']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['scss']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Enable the bundled SCSS compiler. Save Nebula Options to manually process all SCSS files. Last processed: <strong><?php echo ( $nebula_data['scss_last_processed'] )? date('l, F j, Y - g:ia', $nebula_data['scss_last_processed']) : 'Never'; ?></strong>. <em>(Default: <?php echo ucwords($nebula_options_defaults['scss']); ?>)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Minify CSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[minify_css]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['minify_css']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['minify_css']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['minify_css']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Minify the compiled CSS. <em>(Default: <?php echo ucwords($nebula_options_defaults['minify_css']); ?>)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Developer Stylesheets&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[dev_stylesheets]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['dev_stylesheets']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['dev_stylesheets']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['dev_stylesheets']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Combines CSS files within /stylesheets/css/dev/ into /stylesheets/css/dev.css to allow multiple developers to work on a project without overwriting each other while maintaining a small resource footprint. <em>(Default: <?php echo ucwords($nebula_options_defaults['dev_stylesheets']); ?>)</em></small></p>
					</td>
		        </tr>



				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Admin Notifications</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Nebula Admin Notices&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[admin_notices]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['admin_notices']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['admin_notices']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['admin_notices']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Show Nebula-specific admin notices (Note: This does not toggle WordPress core, or plugin, admin notices). <em>(Default: <?php echo ucwords($nebula_options_defaults['admin_notices']); ?>)</em></small></p>
					</td>
		        </tr>

				<?php if ( $nebula_data['version_legacy'] == 'false' || !$nebula_data['version_legacy'] ): ?>
					<tr class="short" valign="top">
			        	<th scope="row">Nebula Theme Update Notification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_options[theme_update_notification]">
								<option disabled>Default: <?php echo ucwords($nebula_options_defaults['theme_update_notification']); ?></option>
								<option value="enabled" <?php selected('enabled', $nebula_options['theme_update_notification']); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', $nebula_options['theme_update_notification']); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Enable easy updates to the Nebula theme. <strong>Child theme must be activated to work!</strong> <em>(Default: <?php echo ucwords($nebula_options_defaults['theme_update_notification']); ?>)</em></small></p>
						</td>
			        </tr>
			    <?php else: ?>
			    	<tr class="short" valign="top">
			        	<th scope="row">Nebula Theme Update Notification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<input type="text" value="Future updates have been deemed incompatible." readonly />
							<p class="helper"><small>A future version of Nebula was deemed incompatible for automated updates. Nebula would need to be manually updated.</small></p>
						</td>
			        </tr>
		        <?php endif; ?>

		        <tr class="short" valign="top">
		        	<th scope="row">Wordpress Core Update Notification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[wp_core_updates_notify]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['wp_core_updates_notify']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['wp_core_updates_notify']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['wp_core_updates_notify']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control whether or not the Wordpress Core update notifications show up on the admin pages. <em>(Default: <?php echo ucwords($nebula_options_defaults['wp_core_updates_notify']); ?>)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Plugin Update Warning&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[plugin_update_warning]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['plugin_update_warning']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['plugin_update_warning']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['plugin_update_warning']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control whether or not the plugin update warning appears on admin pages. <em>(Default: <?php echo ucwords($nebula_options_defaults['plugin_update_warning']); ?>)</em></small></p>
					</td>
		        </tr>






				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Admin References</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Admin Bar&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[admin_bar]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['admin_bar']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['admin_bar']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['admin_bar']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Wordpress Admin bar globally on the frontend for all users. <strong>Note:</strong> When enabled, the Admin Bar can be temporarily toggled using the keyboard shortcut <strong>Alt+A</strong> without needing to disable it permanently for all users. <em>(Default: <?php echo ucwords($nebula_options_defaults['admin_bar']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Visitors Database&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[visitors_db]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['visitors_db']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['visitors_db']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['visitors_db']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Adds a table to the database to store visitor usage information. This data can be used for insight as well as retargeting/personalization. General events are automatically captured, but refer to the Nebula documentation for instructions on how to interact with data in both JavaScript and PHP. <a href="http://www.hubspot.com/products/crm" target="_blank">Sign up for Hubspot CRM</a> (free) and add your API key to Nebula Options (under the APIs tab) to send known user data automatically. This integration can cultivate their <a href="http://www.hubspot.com/products/marketing" target="_blank">full marketing automation service</a>. <em>(Default: <?php echo ucwords($nebula_options_defaults['visitors_db']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Remove Unnecessary Metaboxes&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[unnecessary_metaboxes]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['unnecessary_metaboxes']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['unnecessary_metaboxes']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['unnecessary_metaboxes']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Remove metaboxes on the Dashboard that are not necessary for most users. <em>(Default: <?php echo ucwords($nebula_options_defaults['unnecessary_metaboxes']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Developer Info Metabox&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[dev_info_metabox]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['dev_info_metabox']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['dev_info_metabox']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['dev_info_metabox']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Show theme and server information useful to developers. Note: Developer information must be added to Administrative tab to appear (if enabled). <em>(Default: <?php echo ucwords($nebula_options_defaults['dev_info_metabox']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">TODO Manager&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[todo_manager_metabox]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['todo_manager_metabox']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['todo_manager_metabox']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['todo_manager_metabox']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Aggregate @TODO comments in code. Note: Developer information must be added to Administrative tab to appear (if enabled). <em>(Default: <?php echo ucwords($nebula_options_defaults['todo_manager_metabox']); ?>)</em></small></p>
					</td>
		        </tr>
		    </table>


			<h2 class="mobiletitle">Analytics</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent analytics" style="display: none;">

				<tr valign="top">
		        	<th scope="row">
			        	<?php if ( !$nebula_options['ga_tracking_id'] ): ?>
			        		<strong style="color: red;">
			        	<?php endif; ?>
			        	Google Analytics Tracking ID&nbsp;
			        	<?php if ( !$nebula_options['ga_tracking_id'] ): ?>
			        		</strong>
			        	<?php endif; ?>
			        	<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a>
			        </th>
					<td>
						<input type="text" name="nebula_options[ga_tracking_id]" value="<?php echo $nebula_options['ga_tracking_id']; ?>" placeholder="UA-00000000-1" />
						<p class="helper"><small>This will add the tracking number to the appropriate locations. If left empty, the tracking ID will need to be entered in <strong>functions.php</strong>.</small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Use WordPress User ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[ga_wpuserid]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['ga_wpuserid']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['ga_wpuserid']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['ga_wpuserid']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Use the WordPress User ID as the Google Analytics User ID. This allows more accurate user reporting. <strong>Note:</strong> Users who share accounts (including developers/clients) can cause inaccurate reports! This functionality is most useful when opening sign-ups to the public. <em>(Default: <?php echo ucwords($nebula_options_defaults['ga_wpuserid']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Display Features&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[ga_displayfeatures]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['ga_displayfeatures']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['ga_displayfeatures']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['ga_displayfeatures']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">Google display features</a> in the analytics tag to enable Advertising Features in Google Analytics, such as Remarketing, Demographics and Interest Reporting, and more.. <em>(Default: <?php echo ucwords($nebula_options_defaults['ga_displayfeatures']); ?>)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Enhanced Link Attribution (Link ID)&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_options[ga_linkid]">
							<option disabled>Default: <?php echo ucwords($nebula_options_defaults['ga_linkid']); ?></option>
							<option value="enabled" <?php selected('enabled', $nebula_options['ga_linkid']); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', $nebula_options['ga_linkid']); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-link-attribution" target="_blank">Enhanced Link Attribution</a> in the Property Settings of the Google Analytics Admin to improve the accuracy of your In-Page Analytics report by automatically differentiating between multiple links to the same URL on a single page by using link element IDs. <em>(Default: <?php echo ucwords($nebula_options_defaults['ga_linkid']); ?>)</em></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">AdWords Remarketing Conversion ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[adwords_remarketing_conversion_id]" value="<?php echo $nebula_options['adwords_remarketing_conversion_id']; ?>" placeholder="000000000" />
						<p class="helper"><small>This conversion ID is used to enable the Google AdWords remarketing tag.</small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Google Optimize ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[google_optimize_id]" value="<?php echo $nebula_options['google_optimize_id']; ?>" placeholder="GTM-0000000" />
						<p class="helper"><small>The ID used by <a href="https://optimize.google.com/optimize/home/" target="_blank">Google Optimize</a> to enable tests. Entering the ID here will enable both the Google Analytics require tag and the style tag hiding snippet in the head.</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Valid Hostnames&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[hostnames]" value="<?php echo $nebula_options['hostnames']; ?>" placeholder="<?php echo nebula_url_components('domain'); ?>" />
						<p class="helper"><small>
							These help generate regex patterns for Google Analytics filters. It is also used for the is_site_live() function! Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)!<br />
							Include this RegEx pattern for a filter/segment <a href="https://gearside.com/nebula/utilities/domain-regex-generator/?utm_campaign=documentation&utm_medium=options&utm_source=valid+hostnames%20help" target="_blank">(Learn how to use this)</a>: <input type="text" value="<?php echo nebula_valid_hostname_regex(); ?>" readonly style="width: 50%;" />
						</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Google Search Console Verification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_search_console_verification" type="text" name="nebula_options[google_search_console_verification]" value="<?php echo $nebula_options['google_search_console_verification']; ?>" placeholder="AAAAAA..." />
						<p class="helper"><small>This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank">Google Search Console</a>. Note: Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Facebook Custom Audience Pixel ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[facebook_custom_audience_pixel_id]" value="<?php echo $nebula_options['facebook_custom_audience_pixel_id']; ?>" placeholder="000000000000000" style="width: 295px;"/><br />
						<p class="helper"><small>Toggle the <a href="https://developers.facebook.com/docs/facebook-pixel" target="_blank">Facebook Custom Audience Pixel</a> tracking.</small></p>
					</td>
		        </tr>

				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h2>Custom Dimensions</h2>
						<p>These are optional dimensions that can be passed into Google Analytics which allows for 20 custom dimensions (or 200 for Google Analytics Premium). To set these up, define the Custom Dimension in the Google Analytics property, then paste the dimension index string ("dimension1", "dimension12", etc.) into the appropriate input field below. The scope for each dimension is noted in their respective help sections. Dimensions that require additional code are marked with a *.</p>
					</td>
		        </tr>


				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Post Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Author&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_author]" value="<?php echo $nebula_options['cd_author']; ?>" />
						<p class="helper"><small>Tracks the article author's name on single posts. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Categories&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_categories]" value="<?php echo $nebula_options['cd_categories']; ?>" />
						<p class="helper"><small>Sends a string of all the post's categories to the pageview hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Tags&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_tags]" value="<?php echo $nebula_options['cd_tags']; ?>" />
						<p class="helper"><small>Sends a string of all the post's tags to the pageview hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Word Count&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_wordcount]" value="<?php echo $nebula_options['cd_wordcount']; ?>" />
						<p class="helper"><small>Sends word count range for single posts. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Publish Year&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_publishyear]" value="<?php echo $nebula_options['cd_publishyear']; ?>" />
						<p class="helper"><small>Sends the year the post was published. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Scroll Depth&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_scrolldepth]" value="<?php echo $nebula_options['cd_scrolldepth']; ?>" />
						<p class="helper"><small>Information tied to the event such as "Scanner" or "Reader". <em>This dimension is tied to events, so pageviews will not have data (use the Top Event report).</em> <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Max Scroll Percent&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_maxscroll]" value="<?php echo $nebula_options['cd_maxscroll']; ?>" />
						<p class="helper"><small>Calculates the maximum scroll percent the user reached before triggering an event. <em>This dimension is tied to events, so pageviews will not have data (use the Top Event report).</em> <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Business Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Business Hours&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_businesshours]" value="<?php echo $nebula_options['cd_businesshours']; ?>" />
						<p class="helper"><small>Passes "During Business Hours", or "Non-Business Hours" if business hours metadata has been entered. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Relative Time&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_relativetime]" value="<?php echo $nebula_options['cd_relativetime']; ?>" />
						<p class="helper"><small>Sends the relative time (Ex: "Late Morning", "Early Evening", etc.) based on the business timezone (via WordPress settings). <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Weather&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_weather]" value="<?php echo $nebula_options['cd_weather']; ?>" />
						<p class="helper"><small>Sends the current weather conditions (at the business location) as a dimension. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Temperature&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_temperature]" value="<?php echo $nebula_options['cd_temperature']; ?>" />
						<p class="helper"><small>Sends temperature ranges (at the business location) in 5&deg;F intervals. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>



				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>User Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Role&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_role]" value="<?php echo $nebula_options['cd_role']; ?>" />
						<p class="helper"><small>Sends the current user's role (as well as staff affiliation if available) for associated users. <em>Note: Session ID does contain this information, but this is explicitly more human readable (and scoped to the user).</em> <strong>Scope: User</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Session ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_sessionid]" value="<?php echo $nebula_options['cd_sessionid']; ?>" />
						<p class="helper"><small>ID system so that you can group hits into specific user sessions. This ID is not personally identifiable and therefore fits within the <a href="https://support.google.com/analytics/answer/2795983" target="_blank">Google Analytics ToS</a> for PII. <a href="https://gearside.com/nebula/functions/nebula_session_id/?utm_campaign=documentation&utm_medium=options&utm_source=session+id%20help" target="_blank">Session ID Documentation &raquo;</a> <strong>Scope: Session</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">User ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_userid]" value="<?php echo $nebula_options['cd_userid']; ?>" />
						<p class="helper"><small>If allowing visitors to sign up to create WordPress accounts, this will send user IDs to Google Analytics. <em>User IDs are also passed in the Session ID, but this scope is tied more specifically to the user (it can often capture data even when they are not currently logged in).</em> <strong>Scope: User</strong></small></p>
					</td>
		        </tr>

		         <tr class="short" valign="top">
		        	<th scope="row">Facebook ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_fbid]" value="<?php echo $nebula_options['cd_fbid']; ?>" />
						<p class="helper"><small>Send Facebook ID to Google Analytics when using Facebook Connect API. Add the ID to this URL to view it: <code>https://www.facebook.com/app_scoped_user_id/</code> <strong>Scope: User</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Local Timestamp&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_timestamp]" value="<?php echo $nebula_options['cd_timestamp']; ?>" />
						<p class="helper"><small>Adds a timestamp (in the user's local time) with timezone offset <em>(Ex: "1449332547 (2015/12/05 11:22:26.886 UTC-05:00)")</em>. <em>Can be compared to the server time stored in the Session ID.</em> <strong>Scope: Hit</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">First Interaction&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_firstinteraction]" value="<?php echo $nebula_options['cd_firstinteraction']; ?>" />
						<p class="helper"><small>Stores a timestamp for the first time the user visited the site.</em> <strong>Scope: User</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Window Type&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_windowtype]" value="<?php echo $nebula_options['cd_windowtype']; ?>" />
						<p class="helper"><small>Stores the type of window the site is being accessed from (Ex: Iframe or Standalone App). <em>Note: The only records alternate window types (non-standard browser windows).</em></em> <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Geolocation*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_geolocation]" value="<?php echo $nebula_options['cd_geolocation']; ?>" />
						<p class="helper"><small>Allows latitude and longitude coordinates to be sent after being detected. <em>*Note: Additional code is required for this to work!</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Geolocation Accuracy*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_geoaccuracy]" value="<?php echo $nebula_options['cd_geoaccuracy']; ?>" />
						<p class="helper"><small>Allows geolocation accuracy to be sent after being detected. <em>*Note: Additional code is required for this to work!</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Geolocation Name*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_geoname]" value="<?php echo $nebula_options['cd_geoname']; ?>" />
						<p class="helper"><small>Allows named location information to be sent after being detected using map polygons. <em>*Note: Additional code is required for this to work!</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Ad Blocker&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_adblocker]" value="<?php echo $nebula_options['cd_adblocker']; ?>" />
						<p class="helper"><small>Detects if the user is blocking ads. This can be used even if not intending to serve ads on this site. <em>It is important that this dimension is <strong>not</strong> set to the "hit" scope.</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Media Query: Breakpoint&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_mqbreakpoint]" value="<?php echo $nebula_options['cd_mqbreakpoint']; ?>" />
						<p class="helper"><small>Detect which media query breakpoint is associated with this hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Media Query: Resolution&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_mqresolution]" value="<?php echo $nebula_options['cd_mqresolution']; ?>" />
						<p class="helper"><small>Detect the resolution factor associated with this hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Media Query: Orientation&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_mqorientation]" value="<?php echo $nebula_options['cd_mqorientation']; ?>" />
						<p class="helper"><small>Detect the device orientation associated with this hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Notable POI&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_notablepoi]" value="<?php echo $nebula_options['cd_notablepoi']; ?>" />
						<p class="helper"><small>Stores named IP addresses (from the Administration tab). Also passes data using the <code>?poi</code> query string (useful for email marketing using personalization within links). Also sends value of input fields with class "nebula-poi" on form submits (when applicable). <strong>Scope: User</strong></small></p>
					</td>
		        </tr>


				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Conversion Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Event Intent&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_eventintent]" value="<?php echo $nebula_options['cd_eventintent']; ?>" />
						<p class="helper"><small>Log whether the event was true, or just a possible intention. <strong>Scope: Hit</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">&raquo; Contact Method&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_contactmethod]" value="<?php echo $nebula_options['cd_contactmethod']; ?>" />
						<p class="helper"><small>If the user triggers a contact event, the method of contact is stored here. <strong>Scope: Session</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Form Timing&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_formtiming]" value="<?php echo $nebula_options['cd_formtiming']; ?>" />
						<p class="helper"><small>Timings are automatically sent to Google Analytics in Nebula, but are sampled in the User Timings report, so they can also be sent along with the each form submission as a Custom Dimension here. Data will be in milliseconds. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Video Watcher&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_videowatcher]" value="<?php echo $nebula_options['cd_videowatcher']; ?>" />
						<p class="helper"><small>Sets a dimension when videos are started and finished. <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Ecommerce Cart&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_woocart]" value="<?php echo $nebula_options['cd_woocart']; ?>" />
						<p class="helper"><small>If the user has any product(s) in their cart. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Ecommerce Customer&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_options[cd_woocustomer]" value="<?php echo $nebula_options['cd_woocustomer']; ?>" />
						<p class="helper"><small>Sets a dimension when a user completes the checkout process in WooCommerce. Appears in Google Analytics as "Order Received". <strong>Scope: User</strong></small></p>
					</td>
		        </tr>







				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h2>Custom Metrics</h2>
						<p>These are optional metrics that can be passed into Google Analytics which allows for 20 custom metrics (or 200 for Google Analytics Premium). To set these up, define the Custom Metric in the Google Analytics property, then paste the metric index string ("metric1", "metric12", etc.) into the appropriate input field below. The scope and format for each metric is noted in their respective help sections. Metrics that require additional code are marked with a *. These are useful for manual interpretation of data, or to be included in Calculated Metrics formulas.</p>
					</td>
		        </tr>


				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Conversion Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Notable Downloads*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_notabledownloads]" value="<?php echo $nebula_options['cm_notabledownloads']; ?>" />
						<p class="helper"><small>Tracks when a user downloads a notable file. Note: To use, add the class "notable" to either the &lt;a&gt; or its parent. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Form Views*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_formviews]" value="<?php echo $nebula_options['cm_formviews']; ?>" />
						<p class="helper"><small>Tracks when a user views a page containing a form. <em>To ignore a form, add the class "ignore-form" to the form or somewhere inside it.</em> <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Form Starts*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_formstarts]" value="<?php echo $nebula_options['cm_formstarts']; ?>" />
						<p class="helper"><small>Tracks when a user begins entering a form. <em>To ignore a form, add the class "ignore-form" to the form or somewhere inside it.</em> <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Form Submissions*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_formsubmissions]" value="<?php echo $nebula_options['cm_formsubmissions']; ?>" />
						<p class="helper"><small>Tracks when a user submits a form. <em>To ignore a form, add the class "ignore-form" to the form or somewhere inside it.</em> <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Engaged Readers&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_engagedreaders]" value="<?php echo $nebula_options['cm_engagedreaders']; ?>" />
						<p class="helper"><small>Counts when a user has completed reading an article (and is not determined to be a "scanner"). <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Video Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Video Starts&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_videostarts]" value="<?php echo $nebula_options['cm_videostarts']; ?>" />
						<p class="helper"><small>Tracks when a user begins playing a video. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Video Play Time&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_videoplaytime]" value="<?php echo $nebula_options['cm_videoplaytime']; ?>" />
						<p class="helper"><small>Tracks playing duration when a user pauses or completes a video. <strong>Scope: Hit, Format: Time</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Video Completions&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_videocompletions]" value="<?php echo $nebula_options['cm_videocompletions']; ?>" />
						<p class="helper"><small>Tracks when a user completes playing a video. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Miscellaneous</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Word Count&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_wordcount]" value="<?php echo $nebula_options['cm_wordcount']; ?>" />
						<p class="helper"><small>Sends word count for single posts. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Autocomplete Searches&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_autocompletesearches]" value="<?php echo $nebula_options['cm_autocompletesearches']; ?>" />
						<p class="helper"><small>Tracks when a set of autocomplete search results is returned to the user (count is the search, not the result quantity). <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Autocomplete Search Clicks&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_autocompletesearchclicks]" value="<?php echo $nebula_options['cm_autocompletesearchclicks']; ?>" />
						<p class="helper"><small>Tracks when a user clicks an autocomplete search result. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Page Visible&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_pagevisible]" value="<?php echo $nebula_options['cm_pagevisible']; ?>" />
						<p class="helper"><small>The amount of time (in seconds) the page was in the visible state (tab/window visible) <strong>Scope: Hit, Format: Time</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Page Hidden&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_options[cm_pagehidden]" value="<?php echo $nebula_options['cm_pagehidden']; ?>" />
						<p class="helper"><small>The amount of time (in seconds) the page was in the hidden state (tab/window not visible) <strong>Scope: Hit, Format: Time</strong></small></p>
					</td>
		        </tr>
		    </table>



			<h2 class="mobiletitle">APIs</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent apis" style="display: none;">

		        <tr valign="top">
		        	<th scope="row">Google Font&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_font_url" type="text" name="nebula_options[google_font_url]" value="<?php echo $nebula_options['google_font_url']; ?>" placeholder="http://fonts.googleapis.com/css?family=Open+Sans:400,800" style="width: 400px;" />
						<p class="helper"><small>Choose which <a href="https://www.google.com/fonts" target="_blank">Google Font</a> is used by default for this site by pasting the entire font URL. Defaults: Open Sans 400,800</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Google Public API&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Browser Key: <input type="text" name="nebula_options[google_browser_api_key]" value="<?php echo $nebula_options['google_browser_api_key']; ?>" /><br />
						Server Key: <input type="text" name="nebula_options[google_server_api_key]" value="<?php echo $nebula_options['google_server_api_key']; ?>" />
						<p class="helper"><small>In the <a href="https://console.developers.google.com/project" target="_blank">Developers Console</a> make a new project (if you don't have one yet). Under "Credentials" create a new key.<br />Your current server IP address is <strong><?php echo gethostbyname(gethostname()); ?></strong> <em>(for server key whitelisting)</em>. Do not use the Server Key in JavaScript or any client-side code!</small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Google Custom Search Engine&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Engine ID: <input type="text" name="nebula_options[cse_id]" value="<?php echo $nebula_options['cse_id']; ?>" placeholder="000000000000000000000:aaaaaaaa_aa" /><br />
						<p class="helper"><small>Google Custom Search Engine (for <a href="https://gearside.com/nebula/functions/pagesuggestion/?utm_campaign=documentation&utm_medium=options&utm_source=gcse+help" target="_blank">page suggestions</a> on 404 and No Search Results pages). <a href="https://www.google.com/cse/manage/all">Register here</a>, then select "Add", input your website's URL in "Sites to Search". Then click the one you just made and click the "Search Engine ID" button.</small></p>
					</td>
		        </tr>

				<tr valign="top" class="hidden">
		        	<th scope="row">Google Cloud Messaging Sender ID*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_gcm_sender_id" type="text" name="nebula_options[gcm_sender_id]" value="<?php echo $nebula_options['gcm_sender_id']; ?>" placeholder="000000000000" />
						<p class="helper"><small>The Google Cloud Messaging (GCM) Sender ID from the <a href="https://console.developers.google.com/project" target="_blank">Developers Console</a>. This is the "Project number" within the project box on the Dashboard. Do not include parenthesis or the "#" symbol. This is used for push notifications. <strong>*Note: This feature is still in development and not currently active!</strong></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Hubspot&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						API Key: <input type="text" name="nebula_options[hubspot_api]" value="<?php echo $nebula_options['hubspot_api']; ?>" /><br/>
						Portal ID: <input type="text" name="nebula_options[hubspot_portal]" value="<?php echo $nebula_options['hubspot_portal']; ?>" />
						<p class="helper"><small>Enter your Hubspot API key here. It can be obtained from your <a href="https://app.hubspot.com/hapikeys">API Keys page under Integrations in your account</a>. Your Hubspot Portal ID (or Hub ID) is located in the upper right of your account screen.</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Disqus Shortname&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[disqus_shortname]" value="<?php echo $nebula_options['disqus_shortname']; ?>" />
						<p class="helper"><small>Enter your Disqus shortname here. <a href="https://disqus.com/admin/create/" target="_blank">Sign-up for an account here</a>. In your <a href="https://<?php echo $nebula_options['disqus_shortname']; ?>.disqus.com/admin/settings/" target="_blank">Disqus account settings</a> (where you will find your shortname), please uncheck the "Discovery" box.</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Facebook&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						App ID: <input type="text" name="nebula_options[facebook_app_id]" value="<?php echo $nebula_options['facebook_app_id']; ?>" placeholder="000000000000000" style="width: 153px;"/><br />
						App Secret: <input type="text" name="nebula_options[facebook_app_secret]" value="<?php echo $nebula_options['facebook_app_secret']; ?>" placeholder="00000000000000000000000000000000" style="width: 311px;"/><br />
						Access Token: <input type="text" name="nebula_options[facebook_access_token]" value="<?php echo $nebula_options['facebook_access_token']; ?>" placeholder="000000000000000|000000000000000000000000000" style="width: 295px;"/><br />
						<p class="helper"><small>The App ID of the associated Facebook page/app. This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Twitter&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Consumer Key: <input type="text" name="nebula_options[twitter_consumer_key]" value="<?php echo $nebula_options['twitter_consumer_key']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Consumer Secret: <input type="text" name="nebula_options[twitter_consumer_secret]" value="<?php echo $nebula_options['twitter_consumer_secret']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Bearer Token: <input type="text" name="nebula_options[twitter_bearer_token]" value="<?php echo $nebula_options['twitter_bearer_token']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/>
						<p class="helper"><small>The bearer token is for creating custom Twitter feeds: <a href="https://gearside.com/nebula/utilities/twitter-bearer-token-generator/?utm_campaign=documentation&utm_medium=options&utm_source=twitter+help" target="_blank">Generate a bearer token here</a></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Instagram&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						User ID: <input type="text" name="nebula_options[instagram_user_id]" value="<?php echo $nebula_options['instagram_user_id']; ?>" placeholder="00000000" style="width: 296px;"/><br />
						Access Token: <input type="text" name="nebula_options[instagram_access_token]" value="<?php echo $nebula_options['instagram_access_token']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Client ID: <input type="text" name="nebula_options[instagram_client_id]" value="<?php echo $nebula_options['instagram_client_id']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Client Secret: <input type="text" name="nebula_options[instagram_client_secret]" value="<?php echo $nebula_options['instagram_client_secret']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						<p class="helper"><small>The user ID and access token are used for creating custom Instagram feeds. Here are instructions for <a href="http://www.otzberg.net/iguserid/" target="_blank">finding your User ID</a>, or <a href="http://jelled.com/instagram/access-token" target="_blank">generating your access token</a>. <a href="https://smashballoon.com/instagram-feed/token/" target="_blank">This tool can retrieve both at once</a> by connecting to your Instagram account.<br />For client ID and client secret, register an application using the <a href="https://instagram.com/developer/" target="_blank">Instagram API</a> platform then Register a new Client.</small></p>
					</td>
		        </tr>

		        <?php if ( 1==2 ): //@TODO "Nebula" 0: Get this integrated into Nebula before enabling. ?>
		        <tr valign="top">
		        	<th scope="row">YouTube&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Data: <input type="text" name="nebula_options[youtube_todo]" value="<?php echo $nebula_options['youtube_todo']; ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/>
						<p class="helper"><small>Coming soon...</a></small></p>
					</td>
		        </tr>
		        <?php endif; ?>

		    </table>




			<h2 class="mobiletitle">Administration</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent administration" style="display: none;">
		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Staff and Notable Users</h3>

						<?php
			        		$current_user = wp_get_current_user();
							list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
						?>
						<p><small>Your current public IP address is <code><?php echo $_SERVER['REMOTE_ADDR']; ?></code> and your current email domain is <code><?php echo $current_user_domain; ?></code></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Developer IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[dev_ip]" value="<?php echo $nebula_options['dev_ip']; ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />
						<p class="helper"><small>Comma-separated IP addresses of the developer to enable specific console logs and other dev info. RegEx may also be used here. Ex: <code>/192\.168\./i</code><br />Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Developer Email Domains&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[dev_email_domain]" value="<?php echo $nebula_options['dev_email_domain']; ?>" placeholder="<?php echo $current_user_domain; ?>" />
						<p class="helper"><small>Comma separated domains of the developer emails (without the "@") to enable specific console logs and other dev info. RegEx may also be used here. Ex: <code>/@pinckneyhugo\./i</code><br />Your email domain is: <strong><?php echo $current_user_domain; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Client IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[client_ip]" value="<?php echo $nebula_options['client_ip']; ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />
						<p class="helper"><small>Comma-separated IP addresses of the client to enable certain features. RegEx may also be used here. Ex: <code>/192\.168\./i</code><br />Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<?php
		        		$current_user = wp_get_current_user();
						list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
					?>

		        	<th scope="row">Client Email Domains&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[client_email_domain]" value="<?php echo $nebula_options['client_email_domain']; ?>" placeholder="<?php echo $current_user_domain; ?>" />
						<p class="helper"><small>Comma separated domains of the developer emails (without the "@") to enable certain features. RegEx may also be used here. Ex: <code>/@pinckneyhugo\./i</code><br />Your email domain is: <strong><?php echo $current_user_domain; ?></strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Notable IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<textarea name="nebula_options[notableiplist]" placeholder="192.168.0.1 Name Here"><?php echo $nebula_options['notableiplist']; ?></textarea>
						<p class="helper"><small>A list of named IP addresses. Name IPs by location to avoid <a href="https://support.google.com/analytics/answer/2795983" target="_blank">Personally Identifiable Information (PII)</a> issues (Do not use peoples' names). Enter each IP (or RegEx to match) on a new line with a space separating the IP address and name. <strong>Be sure to set up a Custom Dimension in Google Analytics and add the dimension index in the <strong>Analytics</strong> tab!</strong><br/><strong>Tip:</strong> IP data is sent with <a href="https://gearside.com/nebula/examples/contact-form-7/?utm_campaign=documentation&utm_medium=options&utm_source=notable+ips%20help" target="_blank">Nebula contact forms</a>!</small></p>
					</td>
		        </tr>

		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Useful Links</h3>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Server Control Panel&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<?php
							$serverProtocol = 'http://';
							if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
								$serverProtocol = 'https://';
							}
						?>
						<input type="text" name="nebula_options[cpanel_url]" value="<?php echo $nebula_options['cpanel_url']; ?>" placeholder="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" />
						<p class="helper"><small>Link to the control panel of the hosting account. cPanel on this domain would be <a href="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" target="_blank"><?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082</a>.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Hosting&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<?php
							$hostURL = explode(".", gethostname());
						?>
						<input type="text" name="nebula_options[hosting_url]" value="<?php echo $nebula_options['hosting_url']; ?>" placeholder="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>/" />
						<p class="helper"><small>Link to the server host for easy access to support and other information. Server detected as <a href="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>" target="_blank">http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?></a>.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Domain Registrar&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[registrar_url]" value="<?php echo $nebula_options['registrar_url']; ?>" />
						<p class="helper"><small>Link to the domain registrar used for access to pointers, forwarding, and other information.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google Analytics&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[ga_url]" value="<?php echo $nebula_options['ga_url']; ?>" placeholder="http://www.google.com/analytics/..." />
						<p class="helper"><small>Link directly to this project's <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a> report.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google Search Console&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[google_search_console_url]" value="<?php echo $nebula_options['google_search_console_url']; ?>" placeholder="https://www.google.com/webmasters/tools/..." />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/webmasters/tools/" target="_blank">Google Search Console</a>.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google AdSense&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[google_adsense_url]" value="<?php echo $nebula_options['google_adsense_url']; ?>" placeholder="https://www.google.com/adsense/app" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/adsense/" target="_blank">Google AdSense</a> account.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google AdWords&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[google_adwords_url]" value="<?php echo $nebula_options['google_adwords_url']; ?>" placeholder="https://www.google.com/adwords/" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/adwords/" target="_blank">Google AdWords</a> account.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Mention&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_options[mention_url]" value="<?php echo $nebula_options['mention_url']; ?>" placeholder="https://web.mention.com/" />
						<p class="helper"><small>Direct link to this project's <a href="https://mention.com/" target="_blank">Mention</a> account.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Notes&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<textarea name="nebula_options[notes]"><?php echo $nebula_options['notes']; ?></textarea>
						<p class="helper"><small>This area can be used to keep notes. It is not used anywhere on the front-end.</small></p>
					</td>
		        </tr>
		    </table>

			<?php if (1==2) : //Examples of different field types ?>
				<input type="checkbox" name="some_other_option" value="<?php echo $nebula_options['some_other_option_check']; ?>" <?php checked('1', $nebula_options['some_other_option_check']); ?> />
			<?php endif; ?>

			<?php submit_button(); ?>
		</form>
	</div><!--/wrap-->
<?php }