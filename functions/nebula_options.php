<?php

/*==========================
 Global Nebula Options Conditional Functions
 ===========================*/

//If the desired option is an enabled/disabled dropdown check against that, else check for the option and return the default.
//Dropdowns: $operand is what to check against (typically 'enabled' or 'disabled').
//Texts: $operand is the default value to return if option is false.
function nebula_option($option, $operand=false){
	$data = get_option($option);
	$requested_dropdown = in_array(strtolower($operand), array('enabled', 'disabled'));
	$data_dropdown = in_array(strtolower($data), array('enabled', 'disabled'));

	if ( empty($data) ){
		if ( $requested_dropdown ){
			return false;
		} else {
			return $operand;
		}
	} else {
		if ( $requested_dropdown ){ //If $operand suggests a dropdown option, match $data against it.
			if ( strtolower($data) == strtolower($operand) ){
				return true;
			} else {
				return false;
			}
		} elseif ( $data_dropdown ){ //If $operand is a default fallback, but $data suggests a dropdown option.
			if ( strtolower($data) == 'enabled' ){
				return true;
			} else {
				return $operand;
			}
		} else {
			return $data;
		}
	}
}

/*==========================
 Specific Options Functions
 When using in templates these simplify the syntax to be less confusing.
 ===========================*/

function nebula_full_address($encoded=false){
	if ( !get_option('nebula_street_address') ){
		return false;
	}

	$full_address = get_option('nebula_street_address') . ', ' . get_option('nebula_locality') . ', ' . get_option('nebula_region') . ' ' . get_option('nebula_postal_code');
    if ( $encoded ){
	    $full_address = str_replace(array(', ', ' '), '+', $full_address);
    }
	return $full_address;
}

//Check if specific Nebula options are enabled
function nebula_is_option_enabled($option=false){
	$option = str_replace(array(' ', '_'), '', $option);

	switch ($option){
		case ('adminbar'):
			return nebula_option('nebula_admin_bar', 'enabled');
			break;

		case ('authors'):
		case ('author'):
		case ('authorbios'):
			return !nebula_option('nebula_author_bios', 'disabled');
			break;

		case ('displayfeatures'):
		case ('display'):
			return !nebula_option('nebula_ga_displayfeatures', 'disabled');
			break;

		case ('linkid'):
		case ('enhancedlinkattribution'):
			return !nebula_option('nebula_ga_displayfeatures', 'disabled');
			break;

		case ('comments'):
			return !nebula_option('nebula_comments', 'disabled');
			break;

		case ('wireframing'):
			return !nebula_option('nebula_wireframing', 'disabled');
			break;

		default:
			return false;
			break;
	}
	return false;
}

function nebula_google_font_option(){
	if ( get_option('nebula_google_font_url') ){
		return preg_replace("/(<link href=')|(' rel='stylesheet' type='text\/css'>)|(@import url\()|(\);)/", '', get_option('nebula_google_font_url'));
	} elseif ( get_option('nebula_google_font_family') ) {
		$google_font_family = preg_replace('/ /', '+', get_option('nebula_google_font_family', 'Open Sans'));
		$google_font_weights = preg_replace('/ /', '', get_option('nebula_google_font_weights', '400,800'));
		$google_font = 'https://fonts.googleapis.com/css?family=' . $google_font_family . ':' . $google_font_weights;
		$google_font_contents = @file_get_contents($google_font); //@TODO "Nebula" 0: Consider using: FILE_SKIP_EMPTY_LINES (works with file() dunno about file_get_contents())
		if ( $google_font_contents !== false ){
			return $google_font;
		}
	} else {
		return 'https://fonts.googleapis.com/css?family=Open+Sans:400,800';
	}
}

//Initialize the Nebula Submenu
if ( is_admin() ){
	add_action('admin_menu', 'nebula_sub_menu');
	add_action('admin_init', 'register_nebula_options');
}

//Create the Nebula Submenu
function nebula_sub_menu(){
	add_theme_page('Nebula Options', 'Nebula Options', 'manage_options', 'nebula_options', 'nebula_options_page');
}

//Register each option
function register_nebula_options(){
	$GLOBALS['nebula_options_fields'] = array( //@TODO "Nebula" 0: How can I avoid $GLOBALS here?
		'nebula_initialized' => '',
		'nebula_edited_yet' => 'false',
		'nebula_domain_expiration_last' => 'Never',
		'nebula_scss_last_processed' => '0',

		//Metadata Tab
		'nebula_site_owner' => '',
		'nebula_contact_email' => '',
		'nebula_keywords' => '',
		'nebula_phone_number' => '',
		'nebula_fax_number' => '',
		'nebula_latitude' => '',
		'nebula_longitude' => '',
		'nebula_street_address' => '',
		'nebula_locality' => '',
		'nebula_region' => '',
		'nebula_postal_code' => '',
		'nebula_country_name' => '',
		'nebula_business_hours_sunday_enabled' => '',
		'nebula_business_hours_sunday_open' => '',
		'nebula_business_hours_sunday_close' => '',
		'nebula_business_hours_monday_enabled' => '',
		'nebula_business_hours_monday_open' => '',
		'nebula_business_hours_monday_close' => '',
		'nebula_business_hours_tuesday_enabled' => '',
		'nebula_business_hours_tuesday_open' => '',
		'nebula_business_hours_tuesday_close' => '',
		'nebula_business_hours_wednesday_enabled' => '',
		'nebula_business_hours_wednesday_open' => '',
		'nebula_business_hours_wednesday_close' => '',
		'nebula_business_hours_thursday_enabled' => '',
		'nebula_business_hours_thursday_open' => '',
		'nebula_business_hours_thursday_close' => '',
		'nebula_business_hours_friday_enabled' => '',
		'nebula_business_hours_friday_open' => '',
		'nebula_business_hours_friday_close' => '',
		'nebula_business_hours_saturday_enabled' => '',
		'nebula_business_hours_saturday_open' => '',
		'nebula_business_hours_saturday_close' => '',
		'nebula_business_hours_closed' => '',
		'nebula_facebook_url' => '',
		'nebula_facebook_page_id' => '',
		'nebula_facebook_admin_ids' => '',
		'nebula_facebook_app_secret' => '',
		'nebula_facebook_access_token' => '',
		'nebula_google_plus_url' => '',
		'nebula_twitter_url' => '',
		'nebula_linkedin_url' => '',
		'nebula_youtube_url' => '',
		'nebula_instagram_url' => '',

		//Functions Tab
		'nebula_wireframing' => 'disabled',
		'nebula_admin_bar' => 'enabled',
		'nebula_admin_notices' => 'enabled',
		'nebula_author_bios' => 'disabled',
		'nebula_comments' => 'disabled',
		'nebula_wp_core_updates_notify' => 'disabled',
		'nebula_plugin_update_warning' => 'enabled',
		'nebula_welcome_panel' => 'enabled',
		'nebula_unnecessary_metaboxes' => 'enabled',
		'nebula_ataglance_metabox' => 'enabled',
		'nebula_dev_metabox' => 'enabled',
		'nebula_todo_metabox' => 'enabled',
		'nebula_domain_exp' => 'enabled',
		'nebula_scss' => 'enabled',
		'nebula_minify_css' => 'disabled',
		'nebula_dev_stylesheets' => 'enabled',
		'nebula_appcache_manifest' => 'disabled',
		'nebula_console_css' => 'enabled',

		//Analytics Tab
		'nebula_ga_tracking_id' => '',
		'nebula_ga_displayfeatures' => 'disabled',
		'nebula_ga_linkid' => 'disabled',
		'nebula_hostnames' => '',
		'nebula_google_webmaster_tools_verification' => '',
		'nebula_facebook_custom_audience_pixel_id' => '',
		'nebula_cd_author' => '',
		'nebula_cd_businesshours' => '',
		'nebula_cd_categories' => '',
		'nebula_cd_tags' => '',
		'nebula_cd_contactmethod' => '',
		'nebula_cd_geolocation' => '',
		'nebula_cd_geoname' => '',
		'nebula_cd_geoaccuracy' => '',
		'nebula_cd_notablebrowser' => '',
		'nebula_cd_relativetime' => '',
		'nebula_cd_scrolldepth' => '',
		'nebula_cd_sessionid' => '',
		'nebula_cd_timestamp' => '',
		'nebula_cd_userid' => '',
		'nebula_cd_staff' => '',
		'nebula_cd_videowatcher' => '',
		'nebula_cd_eventintent' => '',
		'nebula_cd_weather' => '',
		'nebula_cd_temperature' => '',
		'nebula_cd_publishyear' => '',
		'nebula_cd_adblocker' => '',
		'nebula_cm_notableformviews' => '',
		'nebula_cm_notableformsubmissions' => '',
		'nebula_cm_notabledownloads' => '',
		'nebula_cm_engagedreaders' => '',
		'nebula_cm_videoplaytime' => '',
		'nebula_cm_videostarts' => '',
		'nebula_cm_videocompletions' => '',
		'nebula_cm_pagesuggestions' => '',
		'nebula_cm_pagesuggestionsaccepted' => '',
		'nebula_cm_autocompletesearches' => '',
		'nebula_cm_autocompletesearchclicks' => '',

		//APIs Tab
		'nebula_google_font_family' => '',
		'nebula_google_font_weights' => '',
		'nebula_google_font_url' => '',
		'nebula_google_server_api_key' => '',
		'nebula_google_browser_api_key' => '',
		'nebula_cse_id' => '',
		'nebula_google_maps_api' => '',
		'nebula_disqus_shortname' => '',
		'nebula_facebook_app_id' => '',
		'nebula_twitter_consumer_key' => '',
		'nebula_twitter_consumer_secret' => '',
		'nebula_twitter_bearer_token' => '',
		'nebula_instagram_user_id' => '',
		'nebula_instagram_access_token' => '',
		'nebula_instagram_client_id' => '',
		'nebula_instagram_client_secret' => '',

		//Administration Tab
		'nebula_dev_ip' => '',
		'nebula_dev_email_domain' => '',
		'nebula_client_ip' => '',
		'nebula_client_email_domain' => '',
		'nebula_cpanel_url' => '',
		'nebula_hosting_url' => '',
		'nebula_registrar_url' => '',
		'nebula_ga_url' => '',
		'nebula_google_webmaster_tools_url' => '',
		'nebula_google_adsense_url' => '',
		'nebula_google_adwords_url' => '',
		'nebula_mention_url' => '',
	);

	foreach ( $GLOBALS['nebula_options_fields'] as $nebula_option_name => $default_value ){
		register_setting('nebula_options_group', $nebula_option_name);
	}
}

//Output the options page
function nebula_options_page(){
?>
	<script>
		jQuery(document).ready(function(){
			jQuery('a.help').on('click', function(){
				jQuery(this).toggleClass('active');
				jQuery(this).parents('tr').find('p.helper').animate({
		        	height: 'toggle',
					opacity: 'toggle'
		        }, 250);
				return false;
			});

			jQuery('.nav-tab').on('click', function(){
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

			businessHoursCheck();
			jQuery('.businessday input[type="checkbox"]').on('click', function(){
				businessHoursCheck();
			});

			function businessHoursCheck(){
				jQuery('.businessday input[type="checkbox"]').each(function(){
					if ( jQuery(this).prop('checked') ){
						jQuery(this).parents('.businessday').removeClass('closed');
					} else {
						jQuery(this).parents('.businessday').addClass('closed');
					}
				});
			}

			//Pull content from full meta tag HTML (Google Webmaster Tools)
			jQuery('#nebula_google_webmaster_tools_verification').on('paste change blur', function(){
				var gwtInputValue = jQuery('#nebula_google_webmaster_tools_verification').val();
				if ( gwtInputValue.indexOf('<meta') >= 0 ){
					var gwtContent = gwtInputValue.slice(gwtInputValue.indexOf('content="')+9, gwtInputValue.indexOf('" />'));
					jQuery('#nebula_google_webmaster_tools_verification').val(gwtContent);
				}
			});

			//Pull content from full Google Fonts HTML
			jQuery('#nebula_google_font_url').on('paste change blur', function(){
				var gfInputValue = jQuery('#nebula_google_font_url').val();
				if ( gfInputValue.indexOf('<link href=') >= 0 ){
					var gfContent = gfInputValue.replace(/(<link href=')|(' rel='stylesheet' type='text\/css'>)|(@import url\()|(\);)/g, '');
					jQuery('#nebula_google_font_url').val(gfContent);
				}

				if ( gfInputValue.trim() ){
					jQuery('#nebula_google_font_family, #nebula_google_font_weights').addClass('override');
				} else {
					jQuery('#nebula_google_font_family, #nebula_google_font_weights').removeClass('override');
				}
			});
			if ( jQuery('#nebula_google_font_url').val().trim() ){
				jQuery('#nebula_google_font_family, #nebula_google_font_weights').addClass('override');
			} else {
				jQuery('#nebula_google_font_family, #nebula_google_font_weights').removeClass('override');
			}

			//Validate custom dimension IDs
			jQuery('input.dimension').on('blur keyup paste change', function(){
				if ( jQuery(this).val().match(/^dimension([0-9]{1,3})$/i) || jQuery(this).val() == '' ){
					jQuery(this).removeClass('error');
				} else {
					jQuery(this).addClass('error');
				}
			});
			jQuery('input.metric').on('blur keyup paste change', function(){
				if ( jQuery(this).val().match(/^metric([0-9]{1,3})$/i) || jQuery(this).val() == '' ){
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

		<?php if ( $_GET['settings-updated'] == 'true' ): ?>
			<div class="updated notice is-dismissible">
				<p><strong>Nebula Options</strong> have been updated. All SCSS files have been re-processed.</p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php
				settings_fields('nebula_options_group');
				do_settings_sections('nebula_options_group');
			?>

			<table class="form-table global">
		        <tr class="hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Initialized?&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo date('F j, Y @ g:ia', get_option('nebula_initialized')); ?>" disabled/>
						<p class="helper"><small>Shows the date of the initial Nebula Automation if it has run yet, otherwise it is empty. If you are viewing this page, it should probably always be set.</small></p>
					</td>
		        </tr>
		        <tr class="hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Edited Yet?&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" name="nebula_edited_yet" value="true" disabled/>
						<p class="helper"><small>Has any user saved the Nebula Options on this DB yet (Basically, has the save button on this page been clicked)? This will always be "true" on this page (even if it is not saved yet)! Note: This is a string, not a boolean!</small></p>
					</td>
		        </tr>
		        <tr class="hidden" valign="top" style="display: none; visibility: hidden; opacity: 0;">
		        	<th scope="row">Last Domain Expiration Alert&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
		        	<td>
						<input type="text" value="<?php echo ( strtotime(get_option('nebula_domain_expiration_last')) )? date('F j, Y @ g:ia', get_option('nebula_domain_expiration_last')) : get_option('nebula_domain_expiration_last'); ?>" disabled/>
						<p class="helper"><small>Shows the date of the last domain expiration alert that was sent.</small></p>
					</td>
		        </tr>
		    </table>

			<h2 class="nav-tab-wrapper">
	            <a id="metadata" class="nav-tab nav-tab-active" href="#">Metadata</a>
	            <a id="functions" class="nav-tab nav-tab-inactive" href="#">Functions</a>
	            <a id="analytics" class="nav-tab nav-tab-inactive" href="#">
		            Analytics
					<?php if ( !get_option('nebula_ga_tracking_id') ): ?>
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
						<input type="text" name="nebula_site_owner" value="<?php echo get_option('nebula_site_owner'); ?>" placeholder="<?php echo bloginfo('name'); ?>" />
						<p class="helper"><small>The name of the company (or person) who this website is for. This is used when using nebula_the_author(0) with author names disabled.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Contact Email&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_contact_email" value="<?php echo get_option('nebula_contact_email'); ?>" placeholder="<?php echo get_option('admin_email', get_userdata(1)->user_email); ?>" />
						<p class="helper"><small>The main contact email address. If left empty, the admin email address will be used (shown by placeholder).</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Keywords&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_keywords" value="<?php echo get_option('nebula_keywords'); ?>" placeholder="Keywords" style="width: 392px;" />
						<p class="helper"><small>Comma-separated list of keywords (without quotes) that will be used as keyword metadata. Note: This meta is rarely used by site crawlers.</small></p>
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
						<input type="text" name="nebula_phone_number" value="<?php echo get_option('nebula_phone_number'); ?>" placeholder="1-315-478-6700" />
						<p class="helper"><small>The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Fax Number&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_fax_number" value="<?php echo get_option('nebula_fax_number'); ?>" placeholder="1-315-426-1392" />
						<p class="helper"><small>The fax number used for Open Graph data. Use the format: "1-315-426-1392".</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Geolocation&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Lat: <input type="text" name="nebula_latitude" value="<?php echo get_option('nebula_latitude'); ?>" placeholder="43.0536854" style="width: 100px;" />
						Long: <input type="text" name="nebula_longitude" value="<?php echo get_option('nebula_longitude'); ?>" placeholder="-76.1654569" style="width: 100px;" />
						<p class="helper"><small>The latitude and longitude of the physical location (or headquarters if multiple locations). Use the format "43.0536854".</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Address&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_street_address" value="<?php echo get_option('nebula_street_address'); ?>" placeholder="760 West Genesee Street" style="width: 392px;" /><br />
						<input type="text" name="nebula_locality" value="<?php echo get_option('nebula_locality'); ?>" placeholder="Syracuse"  style="width: 194px;" />
						<input type="text" name="nebula_region" value="<?php echo get_option('nebula_region'); ?>" placeholder="NY"  style="width: 40px;" />
						<input type="text" name="nebula_postal_code" value="<?php echo get_option('nebula_postal_code'); ?>" placeholder="13204"  style="width: 70px;" />
						<input type="text" name="nebula_country_name" value="<?php echo get_option('nebula_country_name'); ?>" placeholder="USA"  style="width: 70px;" />
						<p class="helper"><small>The address of the location (or headquarters if multiple locations).</small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Business Hours&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_sunday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_sunday_enabled')); ?> /> <span style="display: inline-block; width: 90px;">Sunday:</span> <input class="business-hour" type="text" name="nebula_business_hours_sunday_open" value="<?php echo get_option('nebula_business_hours_sunday_open'); ?>" style="width: 75px;" /> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_sunday_close" value="<?php echo get_option('nebula_business_hours_sunday_close'); ?>" style="width: 75px;"  />
						</div>

						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_monday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_monday_enabled')); ?>/> <span style="display: inline-block; width: 90px;">Monday:</span> <input class="business-hour" type="text" name="nebula_business_hours_monday_open" value="<?php echo get_option('nebula_business_hours_monday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_monday_close" value="<?php echo get_option('nebula_business_hours_monday_close'); ?>" style="width: 75px;"/>
						</div>

						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_tuesday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_tuesday_enabled')); ?>/> <span style="display: inline-block; width: 90px;">Tuesday:</span> <input class="business-hour" type="text" name="nebula_business_hours_tuesday_open" value="<?php echo get_option('nebula_business_hours_tuesday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_tuesday_close" value="<?php echo get_option('nebula_business_hours_tuesday_close'); ?>" style="width: 75px;"/>
						</div>

						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_wednesday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_wednesday_enabled')); ?>/> <span style="display: inline-block; width: 90px;">Wednesday:</span> <input class="business-hour" type="text" name="nebula_business_hours_wednesday_open" value="<?php echo get_option('nebula_business_hours_wednesday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_wednesday_close" value="<?php echo get_option('nebula_business_hours_wednesday_close'); ?>" style="width: 75px;"/>
						</div>

						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_thursday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_thursday_enabled')); ?>/> <span style="display: inline-block; width: 90px;">Thursday:</span> <input class="business-hour" type="text" name="nebula_business_hours_thursday_open" value="<?php echo get_option('nebula_business_hours_thursday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_thursday_close" value="<?php echo get_option('nebula_business_hours_thursday_close'); ?>" style="width: 75px;"/>
						</div>

						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_friday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_friday_enabled')); ?>/> <span style="display: inline-block; width: 90px;">Friday:</span> <input class="business-hour" type="text" name="nebula_business_hours_friday_open" value="<?php echo get_option('nebula_business_hours_friday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_friday_close" value="<?php echo get_option('nebula_business_hours_friday_close'); ?>" style="width: 75px;"/>
						</div>

						<div class="businessday">
							<input type="checkbox" name="nebula_business_hours_saturday_enabled" value="1" <?php checked('1', get_option('nebula_business_hours_saturday_enabled')); ?>/> <span style="display: inline-block; width: 90px;">Saturday:</span> <input class="business-hour" type="text" name="nebula_business_hours_saturday_open" value="<?php echo get_option('nebula_business_hours_saturday_open'); ?>" style="width: 75px;"/> &ndash; <input class="business-hour" type="text" name="nebula_business_hours_saturday_close" value="<?php echo get_option('nebula_business_hours_saturday_close'); ?>" style="width: 75px;"/>
						</div>

						<p class="helper"><small>Open/Close times. Times should be in the format "5:30 pm" or "17:30". Uncheck all to disable this meta.</small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Days Off&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<textarea name="nebula_business_hours_closed"><?php echo get_option('nebula_business_hours_closed'); ?></textarea>
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
						URL: <input type="text" name="nebula_facebook_url" value="<?php echo get_option('nebula_facebook_url'); ?>" placeholder="http://www.facebook.com/PinckneyHugo" style="width: 358px;"/><br />
						Page ID: <input type="text" name="nebula_facebook_page_id" value="<?php echo get_option('nebula_facebook_page_id'); ?>" placeholder="000000000000000" style="width: 153px;"/><br />
						Admin IDs: <input type="text" name="nebula_facebook_admin_ids" value="<?php echo get_option('nebula_facebook_admin_ids'); ?>" placeholder="0000, 0000, 0000" style="width: 153px;"/><br />
						<p class="helper"><small>The URL (and optional page ID and admin IDs) of the associated Facebook page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google+ URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_plus_url" value="<?php echo get_option('nebula_google_plus_url'); ?>" placeholder="https://plus.google.com/106644717328415684498/about" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated Google+ page. It is important to register with <a href="http://www.google.com/business/" target="_blank">Google Business</a> for the geolocation benefits (among other things)!</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Twitter URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_twitter_url" value="<?php echo get_option('nebula_twitter_url'); ?>" placeholder="https://twitter.com/pinckneyhugo" style="width: 358px;"/><br />
						<p class="helper"><small>The URL of the associated Twitter page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">LinkedIn URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_linkedin_url" value="<?php echo get_option('nebula_linkedin_url'); ?>" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated LinkedIn page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Youtube URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_youtube_url" value="<?php echo get_option('nebula_youtube_url'); ?>" placeholder="https://www.youtube.com/user/pinckneyhugo" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated YouTube page.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Instagram URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_instagram_url" value="<?php echo get_option('nebula_instagram_url'); ?>" placeholder="https://www.instagram.com/pinckneyhugo" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated Instagram page.</small></p>
					</td>
		        </tr>
		    </table>



			<h2 class="mobiletitle">Functions</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent functions" style="display: none;">
				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Front-End</h3>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Wireframe Mode&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_wireframing">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_wireframing')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_wireframing')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>When prototyping, enable this setting to use the greyscale stylesheet. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Author Bios&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_author_bios">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_author_bios')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_author_bios')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Allow authors to have bios that show their info (and post archives). This also enables searching by author, and displaying author names on posts. If disabled, the author page attempts to redirect to an About Us page. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Comments&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_comments">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_comments')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_comments')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Ability to force disable comments. If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles). <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">App Cache Manifest&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_appcache_manifest">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_appcache_manifest')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_appcache_manifest')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Enabled the appcache manifest for offline "app" storage. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Console CSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_console_css">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_console_css')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_console_css')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Adds CSS to the browser console. <em>(Default: Enabled)</em></small></p>
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
						<select name="nebula_scss">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_scss')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_scss')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Enable the bundled SCSS compiler. Save Nebula Options to manually process all SCSS files. Last processed: <strong><?php echo ( get_option('nebula_scss_last_processed') )? date('l, F j, Y - g:ia', get_option('nebula_scss_last_processed')) : 'Never'; ?></strong>. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Minify CSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_minify_css">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_minify_css')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_minify_css')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Minify the compiled CSS. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Developer Stylesheets&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_dev_stylesheets">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_dev_stylesheets')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_dev_stylesheets')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Combines CSS files within /stylesheets/css/dev/ into /stylesheets/css/dev.css to allow multiple developers to work on a project without overwriting each other while maintaining a small resource footprint. <em>(Default: Enabled)</em></small></p>
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
						<select name="nebula_admin_notices">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_admin_notices')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_admin_notices')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Show Nebula-specific admin notices (Note: This does not toggle WordPress core, or plugin, admin notices). <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Wordpress Core Update Notification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_wp_core_updates_notify">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_wp_core_updates_notify')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_wp_core_updates_notify')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control whether or not the Wordpress Core update notifications show up on the admin pages. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Plugin Update Warning&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_plugin_update_warning">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_plugin_update_warning')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_plugin_update_warning')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control whether or not the plugin update warning appears on admin pages. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Domain Expiration Email&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_domain_exp">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_domain_exp')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_domain_exp')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Send an email to all site admins if the detected domain expiration date is within one week. <em>(Default: Enabled)</em></small></p>
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
						<select name="nebula_admin_bar">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_admin_bar')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_admin_bar')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Wordpress Admin bar globally on the frontend for all users. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Welcome Panel&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_welcome_panel">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_welcome_panel')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_welcome_panel')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Welcome Panel with useful links related to the project. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Remove Unnecessary Metaboxes&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_unnecessary_metaboxes">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_unnecessary_metaboxes')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_unnecessary_metaboxes')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Remove metaboxes on the Dashboard that are not necessary for most users. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Nebula At a Glance Metabox&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_ataglance_metabox">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_ataglance_metabox')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_ataglance_metabox')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Replaces the core WordPress "At a Glance" metabox with more information. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Developer Info Metabox&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_dev_metabox">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_dev_metabox')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_dev_metabox')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Developer Information Metabox with useful server information. Requires a user with a matching email address domain to the "Developer Email Domains" setting (under the Administration tab). <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">TODO Manager Metabox&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_todo_metabox">
							<option disabled>Default: Enabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_todo_metabox')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_todo_metabox')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Finds TODO messages in theme files to track open issues. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		    </table>


			<h2 class="mobiletitle">Analytics</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent analytics" style="display: none;">

				<tr valign="top">
		        	<th scope="row">
			        	<?php if ( !get_option('nebula_ga_tracking_id') ): ?>
			        		<strong style="color: red;">
			        	<?php endif; ?>
			        	Google Analytics Tracking ID&nbsp;
			        	<?php if ( !get_option('nebula_ga_tracking_id') ): ?>
			        		</strong>
			        	<?php endif; ?>
			        	<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a>
			        </th>
					<td>
						<input type="text" name="nebula_ga_tracking_id" value="<?php echo get_option('nebula_ga_tracking_id'); ?>" placeholder="UA-00000000-1" />
						<p class="helper"><small>This will add the tracking number to the appropriate locations. If left empty, the tracking ID will need to be entered in <strong>functions.php</strong>.</small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Display Features&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_ga_displayfeatures">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_ga_displayfeatures')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_ga_displayfeatures')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">Google display features</a> in the analytics tag to enable remarketing integration with Google Analytics. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Enhanced Link Attribution (Link ID)&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_ga_displayfeatures">
							<option disabled>Default: Disabled</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_ga_linkid')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_ga_linkid')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Toggle the <a href="https://support.google.com/analytics/answer/2558867?hl=en" target="_blank">Enhanced Link Attribution</a> in the Property Settings of the Google Analytics Admin to enable more accurate In-Page Analytics. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Valid Hostnames&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_hostnames" value="<?php echo get_option('nebula_hostnames'); ?>" placeholder="<?php echo nebula_url_components('domain'); ?>" style="width: 392px;" />
						<p class="helper"><small>
							These help generate regex patterns for Google Analytics filters. It is also used for the is_site_live() function! Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)!<br />
							Include this RegEx pattern for a filter/segment <a href="http://gearside.com/nebula/documentation/utilities/domain-regex-generators/" target="_blank">(Learn how to use this)</a>: <input type="text" value="<?php echo nebula_valid_hostname_regex(); ?>" readonly style="width: 50%;" />
						</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Google Webmaster Tools Verification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_webmaster_tools_verification" type="text" name="nebula_google_webmaster_tools_verification" value="<?php echo get_option('nebula_google_webmaster_tools_verification'); ?>" placeholder="AAAAAA..." style="width: 392px;" />
						<p class="helper"><small>This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank">Google Webmaster Tools</a>. Note: Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Facebook Custom Audience Pixel ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_facebook_custom_audience_pixel_id" value="<?php echo get_option('nebula_facebook_custom_audience_pixel_id'); ?>" placeholder="000000000000000" style="width: 295px;"/><br />
						<p class="helper"><small>Toggle the <a href="https://developers.facebook.com/docs/facebook-pixel" target="_blank">Facebook Custom Audience Pixel</a> tracking.<em>(Default: Disabled)</em></small></p>
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
						<input class="dimension" type="text" name="nebula_cd_author" value="<?php echo get_option('nebula_cd_author'); ?>" />
						<p class="helper"><small>Tracks the article author's name on single posts. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Categories&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_categories" value="<?php echo get_option('nebula_cd_categories'); ?>" />
						<p class="helper"><small>Sends a string of all the post's categories to the pageview hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Tags&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_tags" value="<?php echo get_option('nebula_cd_tags'); ?>" />
						<p class="helper"><small>Sends a string of all the post's tags to the pageview hit. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Word Count&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_wordcount" value="<?php echo get_option('nebula_cd_wordcount'); ?>" />
						<p class="helper"><small>Sends word count range for single posts. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Publish Year&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_publishyear" value="<?php echo get_option('nebula_cd_publishyear'); ?>" />
						<p class="helper"><small>Sends the year the post was published. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Scroll Depth&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_scrolldepth" value="<?php echo get_option('nebula_cd_scrolldepth'); ?>" />
						<p class="helper"><small>Information tied to the event such as "Scanner" or "Reader". <em>This dimension is tied to events, so pageviews will not have data (use the Top Event report).</em> <strong>Scope: Hit</strong></small></p>
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
						<input class="dimension" type="text" name="nebula_cd_businesshours" value="<?php echo get_option('nebula_cd_businesshours'); ?>" />
						<p class="helper"><small>Passes "During Business Hours", or "Non-Business Hours" if business hours metadata has been entered. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Relative Time&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_relativetime" value="<?php echo get_option('nebula_cd_relativetime'); ?>" />
						<p class="helper"><small>Sends the relative time (Ex: "Late Morning", "Early Evening", etc.) based on the business timezone (via WordPress settings). <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Weather&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_weather" value="<?php echo get_option('nebula_cd_weather'); ?>" />
						<p class="helper"><small>Sends the current weather conditions (at the business location) as a dimension. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Temperature&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_temperature" value="<?php echo get_option('nebula_cd_temperature'); ?>" />
						<p class="helper"><small>Sends temperature ranges (at the business location) in 5&deg;F intervals. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>



				<tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>User Data</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Staff&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_staff" value="<?php echo get_option('nebula_cd_staff'); ?>" />
						<p class="helper"><small>Sends "Developer" or "Client" for associated users. <em>Note: Session ID does contain this information, but this is explicitly more human readable.</em> <strong>Scope: User</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Session ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_sessionid" value="<?php echo get_option('nebula_cd_sessionid'); ?>" />
						<p class="helper"><small>ID system so that you can group hits into specific user sessions. This ID is not personally identifiable and therefore fits within the <a href="https://support.google.com/analytics/answer/2795983" target="_blank">Google Analytics ToS</a> for PII. <strong>Scope: Session</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">User ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_userid" value="<?php echo get_option('nebula_cd_userid'); ?>" />
						<p class="helper"><small>If allowing visitors to create WordPress accounts, this will send user IDs to Google Analytics. <strong>Scope: User</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">&raquo; Timestamp&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_timestamp" value="<?php echo get_option('nebula_cd_timestamp'); ?>" />
						<p class="helper"><small>Adds an ISO timestamp (in the user's local time) with timezone offset <em>(Ex: "2015-10-27T17:25:27.466-04:00")</em>. <strong>Scope: Hit</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Geolocation*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_geolocation" value="<?php echo get_option('nebula_cd_geolocation'); ?>" />
						<p class="helper"><small>Allows latitude and longitude coordinates to be sent after being detected. <em>*Note: Additional code is required for this to work!</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Geolocation Accuracy*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_geoaccuracy" value="<?php echo get_option('nebula_cd_geoaccuracy'); ?>" />
						<p class="helper"><small>Allows geolocation accuracy to be sent after being detected. <em>*Note: Additional code is required for this to work!</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Geolocation Name*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_geoname" value="<?php echo get_option('nebula_cd_geoname'); ?>" />
						<p class="helper"><small>Allows named location information to be sent after being detected using map polygons. <em>*Note: Additional code is required for this to work!</em> <strong>Scope: Session</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Ad Blocker&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_adblocker" value="<?php echo get_option('nebula_cd_adblocker'); ?>" />
						<p class="helper"><small>Detects if the user is blocking ads. This can be used even if not intending to serve ads on this site. <strong>Scope: Hit</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row"><em>Notable Browser</em>&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_notablebrowser" value="<?php echo get_option('nebula_cd_notablebrowser'); ?>" />
						<p class="helper"><small>Sends data when notable browser info is detected (such as notable bot traffic or JavaScript disabled). <strong>Scope: Session</strong><br /><em>&raquo; This dimension is currently experimental.</em></small></p>
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
						<input class="dimension" type="text" name="nebula_cd_eventintent" value="<?php echo get_option('nebula_cd_eventintent'); ?>" />
						<p class="helper"><small>Log whether the event was true, or just a possible intention. <strong>Scope: Hit</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">&raquo; Contact Method&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_contactmethod" value="<?php echo get_option('nebula_cd_contactmethod'); ?>" />
						<p class="helper"><small>If the user triggers a contact event, the method of contact is stored here. <strong>Scope: Session</strong><br /><em>&raquo; This dimension is strongly recommended.</em></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Video Watcher&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="dimension" type="text" name="nebula_cd_videowatcher" value="<?php echo get_option('nebula_cd_videowatcher'); ?>" />
						<p class="helper"><small>Sets a dimension when videos are started and finished. <strong>Scope: Session</strong></small></p>
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
						<input class="metric" type="text" name="nebula_cm_notabledownloads" value="<?php echo get_option('nebula_cm_notabledownloads'); ?>" />
						<p class="helper"><small>Tracks when a user downloads a notable file. Note: To use, add the class "notable" to either the &lt;a&gt; or its parent. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Notable Form Views*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_notableformviews" value="<?php echo get_option('nebula_cm_notableformviews'); ?>" />
						<p class="helper"><small>Tracks when a user views a page containing a notable form. Note: To use, add the class "notable-form" to either the &lt;form&gt; or an element inside it. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Notable Form Submissions*&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_notableformsubmissions" value="<?php echo get_option('nebula_cm_notableformsubmissions'); ?>" />
						<p class="helper"><small>Tracks when a user submits a notable form. Note: To use, add the class "notable-form" to either the &lt;form&gt; or an element inside it. Also note that this metric can not account for validation errors. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Engaged Readers&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_engagedreaders" value="<?php echo get_option('nebula_cm_engagedreaders'); ?>" />
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
						<input class="metric" type="text" name="nebula_cm_videostarts" value="<?php echo get_option('nebula_cm_videostarts'); ?>" />
						<p class="helper"><small>Tracks when a user begins playing a video. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Video Play Time&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_videoplaytime" value="<?php echo get_option('nebula_cm_videoplaytime'); ?>" />
						<p class="helper"><small>Tracks playing duration when a user pauses or completes a video. <strong>Scope: Hit, Format: Time</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Video Completions&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_videocompletions" value="<?php echo get_option('nebula_cm_videocompletions'); ?>" />
						<p class="helper"><small>Tracks when a user completes playing a video. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr valign="top">
					<td colspan="2" style="padding-left: 0; padding-right: 0;">
						<h3>Miscellaneous</h3>
					</td>
		        </tr>

				<tr class="short" valign="top">
		        	<th scope="row">Autocomplete Searches&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_autocompletesearches" value="<?php echo get_option('nebula_cm_autocompletesearches'); ?>" />
						<p class="helper"><small>Tracks when a set of autocomplete search results is returned to the user (count is the search, not the result quantity). <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Autocomplete Search Clicks&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_autocompletesearchclicks" value="<?php echo get_option('nebula_cm_autocompletesearchclicks'); ?>" />
						<p class="helper"><small>Tracks when a user clicks an autocomplete search result. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Page Suggestions&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_pagesuggestions" value="<?php echo get_option('nebula_cm_pagesuggestions'); ?>" />
						<p class="helper"><small>Tracks when a page is suggested to the user. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Page Suggestions Accepted&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input class="metric" type="text" name="nebula_cm_pagesuggestionsaccepted" value="<?php echo get_option('nebula_cm_pagesuggestionsaccepted'); ?>" />
						<p class="helper"><small>Tracks when a user clicks a page suggestion. <strong>Scope: Hit, Format: Integer</strong></small></p>
					</td>
		        </tr>
		    </table>



			<h2 class="mobiletitle">APIs</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent apis" style="display: none;">

		        <tr valign="top">
		        	<th scope="row">Google Font&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_font_family" type="text" name="nebula_google_font_family" value="<?php echo get_option('nebula_google_font_family'); ?>" placeholder="Open Sans" /><input id="nebula_google_font_weights" type="text" name="nebula_google_font_weights" value="<?php echo get_option('nebula_google_font_weights'); ?>" placeholder="400,800" style="width: 150px;" /><br />
						or: <input id="nebula_google_font_url" type="text" name="nebula_google_font_url" value="<?php echo get_option('nebula_google_font_url'); ?>" placeholder="http://fonts.googleapis.com/css?family=Open+Sans:400,800" style="width: 400px;" />
						<p class="helper"><small>Choose which <a href="https://www.google.com/fonts" target="_blank">Google Font</a> is used by default for this site (weights should be comma-separated). Or, paste the entire font URL. Defaults: Open Sans 400,800</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Google Public API&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Browser Key: <input type="text" name="nebula_google_browser_api_key" value="<?php echo get_option('nebula_google_browser_api_key'); ?>" style="width: 392px;" /><br />
						Server Key: <input type="text" name="nebula_google_server_api_key" value="<?php echo get_option('nebula_google_server_api_key'); ?>" style="width: 392px;" />
						<p class="helper"><small>In the <a href="https://console.developers.google.com/project" target="_blank">Developers Console</a> make a new project (if you don't have one yet). Under "Credentials" create a new key.<br />Your current server IP address is <strong><?php echo gethostbyname(gethostname()); ?></strong> <em>(for server key whitelisting)</em>. Do not use the Server Key in JavaScript or any client-side code!</small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Google Custom Search Engine&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Engine ID: <input type="text" name="nebula_cse_id" value="<?php echo get_option('nebula_cse_id'); ?>" placeholder="000000000000000000000:aaaaaaaa_aa" style="width: 392px;" /><br />
						<p class="helper"><small>Google Custom Search Engine (for <a href="http://gearside.com/nebula/documentation/bundled/page-suggestions/" target="_blank">page suggestions</a> on 404 and No Search Results pages). <a href="https://www.google.com/cse/manage/all">Register here</a>, then select "Add", input your website's URL in "Sites to Search". Then click the one you just made and click the "Search Engine ID" button.</small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Google Maps&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_maps_api" type="text" name="nebula_google_maps_api" value="<?php echo get_option('nebula_google_maps_api'); ?>" placeholder="AAAAAA..." style="width: 392px;" />
						<p class="helper"><small>The Google Maps API key from the <a href="https://console.developers.google.com/project" target="_blank">Developers Console</a>. This is needed for any Google Maps integration.</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Disqus Shortname&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_disqus_shortname" value="<?php echo get_option('nebula_disqus_shortname'); ?>" style="width: 392px;" />
						<p class="helper"><small> Enter your Disqus shortname here. <a href="https://disqus.com/admin/create/" target="_blank">Sign-up for an account here</a>. In your <a href="https://<?php echo get_option('nebula_disqus_shortname'); ?>.disqus.com/admin/settings/" target="_blank">Disqus account settings</a> (where you will find your shortname), please uncheck the "Discovery" box.</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Facebook&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						App ID: <input type="text" name="nebula_facebook_app_id" value="<?php echo get_option('nebula_facebook_app_id'); ?>" placeholder="000000000000000" style="width: 153px;"/><br />
						App Secret: <input type="text" name="nebula_facebook_app_secret" value="<?php echo get_option('nebula_facebook_app_secret'); ?>" placeholder="00000000000000000000000000000000" style="width: 311px;"/><br />
						Access Token: <input type="text" name="nebula_facebook_access_token" value="<?php echo get_option('nebula_facebook_access_token'); ?>" placeholder="000000000000000|000000000000000000000000000" style="width: 295px;"/><br />
						<p class="helper"><small>The App ID of the associated Facebook page/app. This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Twitter&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Consumer Key: <input type="text" name="nebula_twitter_consumer_key" value="<?php echo get_option('nebula_twitter_consumer_key'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Consumer Secret: <input type="text" name="nebula_twitter_consumer_secret" value="<?php echo get_option('nebula_twitter_consumer_secret'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Bearer Token: <input type="text" name="nebula_twitter_bearer_token" value="<?php echo get_option('nebula_twitter_bearer_token'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/>
						<p class="helper"><small>The bearer token is for creating custom Twitter feeds: <a href="http://gearside.com/nebula/documentation/utilities/twitter-bearer-token-generator/" target="_blank">Generate a bearer token here</a></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Instagram&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						User ID: <input type="text" name="nebula_instagram_user_id" value="<?php echo get_option('nebula_instagram_user_id'); ?>" placeholder="00000000" style="width: 296px;"/><br />
						Access Token: <input type="text" name="nebula_instagram_access_token" value="<?php echo get_option('nebula_instagram_access_token'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						Client ID: <input type="text" name="nebula_instagram_client_id" value="<?php echo get_option('nebula_instagram_client_id'); ?>" placeholder="00000000" style="width: 296px;"/><br />
						Client Secret: <input type="text" name="nebula_instagram_client_secret" value="<?php echo get_option('nebula_instagram_client_secret'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/><br />
						<p class="helper"><small>The user ID and access token are used for creating custom Instagram feeds. Here are instructions for <a href="http://www.otzberg.net/iguserid/" target="_blank">finding your User ID</a>, or <a href="http://jelled.com/instagram/access-token" target="_blank">generating your access token</a>. <a href="https://smashballoon.com/instagram-feed/token/" target="_blank">This tool can retrieve both at once</a> by connecting to your Instagram account.<br />For client ID and client secret, register an application using the <a href="https://instagram.com/developer/" target="_blank">Instagram API</a> platform then Register a new Client ID.</small></p>
					</td>
		        </tr>

		        <?php if ( 1==2 ): //@TODO "Nebula" 0: Get this integrated into Nebula before enabling. ?>
		        <tr valign="top">
		        	<th scope="row">YouTube&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Data: <input type="text" name="nebula_youtube_todo" value="<?php echo get_option('nebula_youtube_todo'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/>
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
						<h3>Staff</h3>
					</td>
		        </tr>

		        <tr class="short" valign="top">
		        	<th scope="row">Developer IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_dev_ip" value="<?php echo get_option('nebula_dev_ip'); ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" style="width: 392px;" />
						<p class="helper"><small>Comma-separated IP addresses of the developer to enable specific console logs and other dev info. Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<?php
		        		$current_user = wp_get_current_user();
						list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
					?>

		        	<th scope="row">Developer Email Domains&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_dev_email_domain" value="<?php echo get_option('nebula_dev_email_domain'); ?>" placeholder="<?php echo $current_user_domain; ?>" style="width: 392px;" />
						<p class="helper"><small>Comma separated domains of the developer emails (without the "@") to enable specific console logs and other dev info. Your email domain is: <strong><?php echo $current_user_domain; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Client IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_client_ip" value="<?php echo get_option('nebula_client_ip'); ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" style="width: 392px;" />
						<p class="helper"><small>Comma-separated IP addresses of the client to enable certain features. Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<?php
		        		$current_user = wp_get_current_user();
						list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
					?>

		        	<th scope="row">Client Email Domains&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_client_email_domain" value="<?php echo get_option('nebula_client_email_domain'); ?>" placeholder="<?php echo $current_user_domain; ?>" style="width: 392px;" />
						<p class="helper"><small>Comma separated domains of the developer emails (without the "@") to enable certain features. Your email domain is: <strong><?php echo $current_user_domain; ?></strong></small></p>
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
						<input type="text" name="nebula_cpanel_url" value="<?php echo get_option('nebula_cpanel_url'); ?>" placeholder="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" style="width: 392px;" />
						<p class="helper"><small>Link to the control panel of the hosting account. cPanel on this domain would be <a href="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" target="_blank"><?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082</a>.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Hosting&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<?php
							$hostURL = explode(".", gethostname());
						?>
						<input type="text" name="nebula_hosting_url" value="<?php echo get_option('nebula_hosting_url'); ?>" placeholder="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>/" style="width: 392px;" />
						<p class="helper"><small>Link to the server host for easy access to support and other information. Server detected as <a href="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>" target="_blank">http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?></a>.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Domain Registrar&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_registrar_url" value="<?php echo get_option('nebula_registrar_url'); ?>" placeholder="http://<?php echo whois_info('registrar_url'); ?><?php echo ( whois_info('reseller') )? '*' : ''; ?>" style="width: 392px;" />
						<p class="helper"><small>Link to the domain registrar used for access to pointers, forwarding, and other information. <?php if ( whois_info('registrar') ) : ?> Registrar detected as <a href="http://<?php echo whois_info('registrar_url'); ?>"><?php echo whois_info('registrar'); ?></a><?php echo ( whois_info('reseller') )? ' *(via ' . whois_info('reseller') . ')' : ''; ?></small><?php endif; ?></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google Analytics&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_ga_url" value="<?php echo get_option('nebula_ga_url'); ?>" placeholder="http://www.google.com/analytics/..." style="width: 392px;" />
						<p class="helper"><small>Link directly to this project's <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a> report.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google Webmaster Tools&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_webmaster_tools_url" value="<?php echo get_option('nebula_google_webmaster_tools_url'); ?>" placeholder="https://www.google.com/webmasters/tools/..." style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/webmasters/tools/" target="_blank">Google Webmaster</a> Tools.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google AdSense&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_adsense_url" value="<?php echo get_option('nebula_google_adsense_url'); ?>" placeholder="https://www.google.com/adsense/app" style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/adsense/" target="_blank">Google AdSense</a> account.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Google AdWords&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_adwords_url" value="<?php echo get_option('nebula_google_adwords_url'); ?>" placeholder="https://www.google.com/adwords/" style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/adwords/" target="_blank">Google AdWords</a> account.</small></p>
					</td>
		        </tr>
		        <tr class="short" valign="top">
		        	<th scope="row">Mention&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_mention_url" value="<?php echo get_option('nebula_mention_url'); ?>" placeholder="https://web.mention.com/" style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://mention.com/" target="_blank">Mention</a> account.</small></p>
					</td>
		        </tr>
		    </table>

			<?php if (1==2) : //Examples of different field types ?>
				<input type="checkbox" name="some_other_option" value="<?php echo get_option('some_other_option_check'); ?>" <?php checked('1', get_option('some_other_option_check')); ?> />
			<?php endif; ?>

			<?php submit_button(); ?>
		</form>
	</div><!--/wrap-->
<?php }