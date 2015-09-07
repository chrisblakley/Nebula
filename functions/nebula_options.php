<?php

//Store global strings as needed
add_action('init', 'global_nebula_vars');
add_action('admin_init', 'global_nebula_vars');
function global_nebula_vars(){
    $GLOBALS['admin_user'] = get_userdata(1); //@TODO "Nebula" 0: Consider removing this altogether
    $GLOBALS['full_address'] = get_option('nebula_street_address') . ', ' . get_option('nebula_locality') . ', ' . get_option('nebula_region') . ' ' . get_option('nebula_postal_code');
    $GLOBALS['enc_address'] = get_option('nebula_street_address') . ' ' . get_option('nebula_locality') . ' ' . get_option('nebula_region') . ' ' . get_option('nebula_postal_code');
    $GLOBALS['enc_address'] = str_replace(' ', '+', $GLOBALS['enc_address']);
}

/*==========================
 Global Nebula Options Conditional Functions
 ===========================*/

//Determine if a function should be used based on several Nebula Options conditions (for text inputs).
function nebula_options_conditional_text($option, $default=''){
	if ( strtolower(get_option('nebula_overall')) == 'enabled' && get_option($option) ){
		return get_option($option);
	} else {
		return $default;
	}
}

//Determine if a function should be used based on several Nebula Options conditions (for text inputs).
function nebula_options_conditional_text_bool($option, $true=true, $false=false){
	if ( strtolower(get_option('nebula_overall')) == 'enabled' && get_option($option) ){
		return $true;
	} else {
		return $false;
	}
}

//Determine if a function should be used based on several Nebula Options conditions (for select inputs).
function nebula_options_conditional($option, $default='enabled'){
	if ( strtolower(get_option('nebula_overall')) == 'override' || strtolower(get_option('nebula_overall')) == 'disabled' ){
		return true;
	}

	if ( (strtolower(get_option($option)) == 'default') || (strtolower(get_option($option)) == strtolower($default)) ){
		return true;
	} else {
		return false;
	}
}

/*==========================
 Specific Options Functions
 When using in templates these simplify the syntax to be less confusing.
 ===========================*/

function nebula_admin_bar_enabled(){
	return nebula_options_conditional('nebula_admin_bar', 'enabled');
}

function nebula_author_bios_enabled(){
	return !nebula_options_conditional('nebula_author_bios', 'disabled');
}

function nebula_adwords_enabled(){
	return !nebula_options_conditional('nebula_adwords', 'disabled');
}

function nebula_comments_enabled(){
	return !nebula_options_conditional('nebula_comments', 'disabled');
}

function nebula_wireframing_enabled(){
	return !nebula_options_conditional('nebula_wireframing', 'disabled');
}

function nebula_google_font_option(){
	if ( nebula_options_conditional_text_bool('nebula_google_font_url') ){
		return preg_replace("/(<link href=')|(' rel='stylesheet' type='text\/css'>)|(@import url\()|(\);)/", '', nebula_options_conditional_text('nebula_google_font_url', 'http://fonts.googleapis.com/css?family=Open+Sans:400,800'));
	} elseif ( nebula_options_conditional_text_bool('nebula_google_font_family') ) {
		$google_font_family = preg_replace('/ /', '+', nebula_options_conditional_text('nebula_google_font_family', 'Open Sans'));
		$google_font_weights = preg_replace('/ /', '', nebula_options_conditional_text('nebula_google_font_weights', '400,800'));
		$google_font = 'http://fonts.googleapis.com/css?family=' . $google_font_family . ':' . $google_font_weights;

		$google_font_contents = @file_get_contents($google_font); //@TODO "Nebula" 0: Consider using: FILE_SKIP_EMPTY_LINES (works with file() dunno about file_get_contents())
		if ( $google_font_contents !== false ){
			return $google_font;
		}
	}
	return false;
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
		'nebula_overall' => 'Enabled',
		'nebula_initialized' => '',
		'nebula_edited_yet' => 'false',
		'nebula_domain_expiration_alert' => 'Default',

		//Metadata Tab
		'nebula_site_owner' => '',
		'nebula_contact_email' => '',
		'nebula_hostnames' => '',
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
		'nebula_facebook_custom_audience_pixel_id' => '',
		'nebula_google_plus_url' => '',
		'nebula_twitter_url' => '',
		'nebula_linkedin_url' => '',
		'nebula_youtube_url' => '',
		'nebula_instagram_url' => '',

		//Functions Tab
		'nebula_wireframing' => 'Default',
		'nebula_admin_bar' => 'Default',
		'nebula_admin_notices' => 'Default',
		'nebula_adwords' => 'Default',
		'nebula_facebook_custom_audience_pixel' => 'Default',
		'nebula_author_bios' => 'Default',
		'nebula_comments' => 'Default',
		'nebula_wp_core_updates_notify' => 'Default',
		'nebula_plugin_update_warning' => 'Default',
		'nebula_welcome_panel' => 'Default',
		'nebula_unnecessary_metaboxes' => 'Default',
		'nebula_dev_metabox' => 'Default',
		'nebula_todo_metabox' => 'Default',
		'nebula_domain_exp' => 'Default',
		'nebula_scss' => 'Default',
		'nebula_dev_stylesheets' => 'Default',
		'nebula_console_css' => 'Default',

		//APIs Tab
		'nebula_ga_tracking_id' => '',
		'nebula_google_font_family' => '',
		'nebula_google_font_weights' => '',
		'nebula_google_font_url' => '',
		'nebula_google_webmaster_tools_verification' => '',
		'nebula_google_server_api_key' => '',
		'nebula_google_browser_api_key' => '',
		'nebula_cse_id' => '',
		'nebula_google_maps_api' => '',
		'nebula_disqus_shortname' => '',
		'nebula_facebook_app_id' => '',
		'nebula_twitter_consumer_key' => '',
		'nebula_twitter_consumer_secret' => '',
		'nebula_twitter_bearer_token' => '',

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

	foreach ( $GLOBALS['nebula_options_fields'] as $nebula_options_field => $default ){
		register_setting('nebula_options_group', $nebula_options_field);
	}
}

//Output the options page
function nebula_options_page(){
?>
	<style>
		h2 .nav-tab.nav-tab-inactive {font-weight: 400;}

		.override {opacity: 0.4; pointer-events: none;}
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

		@media only screen and (max-width: 782px){
			.form-table th {width: 100%;}
			input[type="text"] {width: 100% !important;}

		}

		@media only screen and (max-width: 400px){
			.nav-tab-wrapper {display: none;}
			.mobiletitle {display: block;}
			.form-table.dependent {display: block !important;}

			.businessday span {font-size: 12px; width: 80px !important;}
			input.business-hour {width: 23% !important; display: inline-block !important; font-size: 12px !important;}
		}
	</style>

	<script>
		jQuery(document).ready(function(){
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

			function toggleDependents(){
				if ( jQuery('#nebula_overall').val() == 'disabled' || jQuery('#nebula_overall').val() == 'override' ){
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
				<p><strong>Nebula Options</strong> have been updated.</p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
		<?php endif; ?>

		<p>These settings are optional overrides to the functions set by Nebula. This page is for convenience and is not needed if you feel like just modifying the functions.php file. It can also be disabled below, or overridden via functions.php if that makes you feel better.</p>

		<?php if ( get_option('nebula_overall') == 'override' ): ?>
			<div id="setting-error-settings_updated" class="error settings-error">
				<p><strong>Override!</strong><br />These options have been overridden using functions.php. Remove the override to re-enable use of this page!</p>
			</div>
		<?php endif; ?>

		<hr />

		<form method="post" action="options.php">
			<?php
				settings_fields('nebula_options_group');
				do_settings_sections('nebula_options_group');
			?>

			<table class="form-table global">

		        <?php if ( is_dev() ): ?>
			        <tr valign="top">
			        	<th scope="row">Nebula Options&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
						<td>
							<select name="nebula_overall" id="nebula_overall">
								<option value="enabled" <?php selected('enabled', get_option('nebula_overall')); ?>>Enabled</option>
								<option value="disabled" <?php selected('disabled', get_option('nebula_overall')); selected('override', get_option('nebula_overall')); ?>>Disabled</option>
							</select>
							<p class="helper"><small>Enable/Disable this options page. If disabled, all options will use <strong style="text-transform: uppercase;">default values</strong> and can only be edited via functions.php! This <strong style="text-transform: uppercase;">does not</strong> disable all options!</small></p>
						</td>
			        </tr>
		        <?php endif; ?>

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
						<input type="text" value="<?php echo ( strtotime(get_option('nebula_domain_expiration_alert')) )? date('F j, Y @ g:ia', get_option('nebula_domain_expiration_alert')) : get_option('nebula_domain_expiration_alert'); ?>" disabled/>
						<p class="helper"><small>Shows the date of the last domain expiration alert that was sent.</small></p>
					</td>
		        </tr>
		    </table>

			<h2 class="nav-tab-wrapper">
	            <a id="metadata" class="nav-tab nav-tab-active" href="#">Metadata</a>
	            <a id="functions" class="nav-tab nav-tab-inactive" href="#">Functions</a>
	            <a id="apis" class="nav-tab nav-tab-inactive" href="#">APIs</a>
	            <a id="administration" class="nav-tab nav-tab-inactive" href="#">Administration</a>
	        </h2>

			<h2 class="mobiletitle">Metadata</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent metadata">
		        <tr valign="top">
		        	<th scope="row">Site Owner&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_site_owner" value="<?php echo get_option('nebula_site_owner'); ?>" placeholder="<?php echo bloginfo('name'); ?>" />
						<p class="helper"><small>The name of the company (or person) who this website is for. This is used when using nebula_the_author(0) with author names disabled.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Contact Email&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_contact_email" value="<?php echo get_option('nebula_contact_email'); ?>" placeholder="<?php echo get_option('admin_email', $GLOBALS['admin_user']->user_email); ?>" />
						<p class="helper"><small>The main contact email address. If left empty, the admin email address will be used (shown by placeholder).</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Valid Hostnames&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_hostnames" value="<?php echo get_option('nebula_hostnames'); ?>" placeholder="<?php echo nebula_url_components('domain'); ?>" style="width: 392px;" />
						<p class="helper"><small>
							These help generate regex patterns for Google Analytics filters. Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)!<br />
							Include this RegEx pattern for a filter/segment <a href="http://gearside.com/nebula/documentation/utilities/domain-regex-generators/" target="_blank">(Learn how to use this)</a>: <input type="text" value="<?php echo nebula_valid_hostname_regex(); ?>" readonly style="width: 50%;" />
						</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Keywords&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_keywords" value="<?php echo get_option('nebula_keywords'); ?>" placeholder="Keywords" style="width: 392px;" />
						<p class="helper"><small>Comma-separated list of keywords (without quotes) that will be used as keyword metadata.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Phone Number&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_phone_number" value="<?php echo get_option('nebula_phone_number'); ?>" placeholder="1-315-478-6700" />
						<p class="helper"><small>The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Fax Number&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_fax_number" value="<?php echo get_option('nebula_fax_number'); ?>" placeholder="1-315-426-1392" />
						<p class="helper"><small>The fax number used for Open Graph data. Use the format: "1-315-426-1392".</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Geolocation&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Lat: <input type="text" name="nebula_latitude" value="<?php echo get_option('nebula_latitude'); ?>" placeholder="43.0536854" style="width: 100px;" />
						Long: <input type="text" name="nebula_longitude" value="<?php echo get_option('nebula_longitude'); ?>" placeholder="-76.1654569" style="width: 100px;" />
						<p class="helper"><small>The latitude and longitude of the physical location (or headquarters if multiple locations). Use the format "43.0536854".</small></p>
					</td>
		        </tr>
		        <tr valign="top">
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

		        <tr valign="top">
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

				<tr valign="top">
		        	<th scope="row">Days Off&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<textarea name="nebula_business_hours_closed"><?php echo get_option('nebula_business_hours_closed'); ?></textarea>
						<p class="helper"><small>Comma-separated list of special days the business is closed (like holidays). These can be date formatted, or day of the month (Ex: "7/4" for Independence Day, or "Last Monday of May" for Memorial Day, or "Fourth Thursday of November" for Thanksgiving). <a href="http://mistupid.com/holidays/" target="_blank">Here is a good reference for holiday occurrences.</a><br /><strong>Note:</strong> This function assumes days off that fall on weekends are observed the Friday before or the Monday after.</small></p>
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
		        <tr valign="top">
		        	<th scope="row">Google+ URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_plus_url" value="<?php echo get_option('nebula_google_plus_url'); ?>" placeholder="https://plus.google.com/106644717328415684498/about" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated Google+ page. It is important to register with <a href="http://www.google.com/business/" target="_blank">Google Business</a> for the geolocation benefits (among other things)!</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Twitter URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_twitter_url" value="<?php echo get_option('nebula_twitter_url'); ?>" placeholder="https://twitter.com/pinckneyhugo" style="width: 358px;"/><br />
						<p class="helper"><small>The URL of the associated Twitter page.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">LinkedIn URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_linkedin_url" value="<?php echo get_option('nebula_linkedin_url'); ?>" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated LinkedIn page.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Youtube URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_youtube_url" value="<?php echo get_option('nebula_youtube_url'); ?>" placeholder="https://www.youtube.com/user/pinckneyhugo" style="width: 358px;"/>
						<p class="helper"><small>The URL of the associated YouTube page.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
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
		        	<th scope="row">Wireframe Mode&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_wireframing">
							<option value="default" <?php selected('default', get_option('nebula_wireframing')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_wireframing')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_wireframing')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>When prototyping, enable this setting to use the greyscale stylesheet. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Admin Bar&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_admin_bar">
							<option value="default" <?php selected('default', get_option('nebula_admin_bar')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_admin_bar')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_admin_bar')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Wordpress Admin bar globally on the frontend for all users. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Nebula Admin Notices&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_admin_notices">
							<option value="default" <?php selected('default', get_option('nebula_admin_notices')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_admin_notices')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_admin_notices')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Show Nebula-specific admin notices (Note: This does not toggle WordPress core, or plugin, admin notices). <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Google AdWords&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_adwords">
							<option value="default" <?php selected('default', get_option('nebula_adwords')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_adwords')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_adwords')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">Google display features</a> in the analytics tag to integrate with Google AdWords. This can be used instead of pasting the Google AdWords snippet into the header! <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Facebook Custom Audience Pixel&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_facebook_custom_audience_pixel">
							<option value="default" <?php selected('default', get_option('nebula_facebook_custom_audience_pixel')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_facebook_custom_audience_pixel')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_facebook_custom_audience_pixel')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Toggle the <a href="https://www.facebook.com/ads/manage/pixels/" target="_blank">Facebook Custom Audience Pixel</a> tracking. Be sure to add the pixel ID under the APIs tab! <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Author Bios&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_author_bios">
							<option value="default" <?php selected('default', get_option('nebula_author_bios')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_author_bios')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_author_bios')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Allow authors to have bios that show their info (and post archives). This also enables searching by author, and displaying author names on posts. If disabled, the author page attempts to redirect to an About Us page. <em>(Default: Disabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Comments&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
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
		        	<th scope="row">Wordpress Core Update Notification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
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
		        	<th scope="row">Plugin Update Warning&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_plugin_update_warning">
							<option value="default" <?php selected('default', get_option('nebula_plugin_update_warning')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_plugin_update_warning')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_plugin_update_warning')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control whether or not the plugin update warning appears on admin pages. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Welcome Panel&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_welcome_panel">
							<option value="default" <?php selected('default', get_option('nebula_welcome_panel')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_welcome_panel')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_welcome_panel')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Welcome Panel with useful links related to the project. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Remove Unnecessary Metaboxes&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
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
		        	<th scope="row">Developer Info Metabox&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_dev_metabox">
							<option value="default" <?php selected('default', get_option('nebula_dev_metabox')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_dev_metabox')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_dev_metabox')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Control the Developer Information Metabox with useful server information. Requires a user with a matching email address domain to the "Developer Email Domains" setting (under the Administration tab). <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">TODO Manager Metabox&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_todo_metabox">
							<option value="default" <?php selected('default', get_option('nebula_todo_metabox')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_todo_metabox')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_todo_metabox')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Finds TODO messages in theme files to track open issues. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Domain Expiration Email&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_domain_exp">
							<option value="default" <?php selected('default', get_option('nebula_domain_exp')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_domain_exp')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_domain_exp')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Send an email to all site admins if the detected domain expiration date is within one week. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">SCSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_scss">
							<option value="default" <?php selected('default', get_option('nebula_scss')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_scss')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_scss')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Enable the bundled SCSS compiler. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Developer Stylesheets&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_dev_stylesheets">
							<option value="default" <?php selected('default', get_option('nebula_dev_stylesheets')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_dev_stylesheets')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_dev_stylesheets')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Combines CSS files within /stylesheets/css/dev/ into /stylesheets/css/dev.css to allow multiple developers to work on a project without overwriting each other while maintaining a small resource footprint. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Console CSS&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<select name="nebula_console_css">
							<option value="default" <?php selected('default', get_option('nebula_console_css')); ?>>Default</option>
							<option value="enabled" <?php selected('enabled', get_option('nebula_console_css')); ?>>Enabled</option>
							<option value="disabled" <?php selected('disabled', get_option('nebula_console_css')); ?>>Disabled</option>
						</select>
						<p class="helper"><small>Adds CSS to the browser console. <em>(Default: Enabled)</em></small></p>
					</td>
		        </tr>

		    </table>



			<h2 class="mobiletitle">APIs</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent apis" style="display: none;">

				<tr valign="top">
		        	<th scope="row">Google Analytics Tracking ID&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_ga_tracking_id" value="<?php echo get_option('nebula_ga_tracking_id'); ?>" placeholder="UA-00000000-1" />
						<p class="helper"><small>This will add the tracking number to the appropriate locations. If left empty, the tracking ID will need to be entered in <strong>functions.php</strong>.</small></p>
					</td>
		        </tr>

		        <tr valign="top">
		        	<th scope="row">Google Font&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_font_family" type="text" name="nebula_google_font_family" value="<?php echo get_option('nebula_google_font_family'); ?>" placeholder="Open Sans" /><input id="nebula_google_font_weights" type="text" name="nebula_google_font_weights" value="<?php echo get_option('nebula_google_font_weights'); ?>" placeholder="400,800" style="width: 150px;" /><br />
						or: <input id="nebula_google_font_url" type="text" name="nebula_google_font_url" value="<?php echo get_option('nebula_google_font_url'); ?>" placeholder="http://fonts.googleapis.com/css?family=Open+Sans:400,800" style="width: 400px;" />
						<p class="helper"><small>Choose which <a href="https://www.google.com/fonts" target="_blank">Google Font</a> is used by default for this site (weights should be comma-separated). Or, paste the entire font URL. Defaults: Open Sans 400,800</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Google Webmaster Tools Verification&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input id="nebula_google_webmaster_tools_verification" type="text" name="nebula_google_webmaster_tools_verification" value="<?php echo get_option('nebula_google_webmaster_tools_verification'); ?>" placeholder="AAAAAA..." style="width: 392px;" />
						<p class="helper"><small>This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank">Google Webmaster Tools</a>. Note: Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</small></p>
					</td>
		        </tr>

				<tr valign="top">
		        	<th scope="row">Google Public API&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Browser Key: <input type="text" name="nebula_google_browser_api_key" value="<?php echo get_option('nebula_google_browser_api_key'); ?>" style="width: 392px;" /><br />
						Server Key: <input type="text" name="nebula_google_server_api_key" value="<?php echo get_option('nebula_google_server_api_key'); ?>" style="width: 392px;" />
						<p class="helper"><small>In the <a href="https://console.developers.google.com/project">Developers Console</a> make a new project (if you don't have one yet). Under "Credentials" create a new key.<br />Your current server IP address is <strong><?php echo gethostbyname(gethostname()); ?></strong> <em>(for server key whitelisting)</em>. Do not use the Server Key in JavaScript or any client-side code!</small></p>
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
						<p class="helper"><small>The Google Maps API key from the <a href="https://console.developers.google.com/project">Developers Console</a>. This is needed for any Google Maps integration.</small></p>
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
						Custom Audience Pixel ID: <input type="text" name="nebula_facebook_custom_audience_pixel_id" value="<?php echo get_option('nebula_facebook_custom_audience_pixel_id'); ?>" placeholder="000000000000000" style="width: 295px;"/><br />
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

		        <?php if ( 1==2 ): //@TODO "Nebula" 0: Get this integrated into Nebula before enabling. ?>
		        <tr valign="top">
		        	<th scope="row">YouTube&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						Data: <input type="text" name="nebula_youtube_todo" value="<?php echo get_option('nebula_youtube_todo'); ?>" placeholder="000000000000000000000000000000" style="width: 296px;"/>
						<p class="helper"><small>The bearer token is for creating custom Twitter feeds: <a href="http://gearside.com/nebula/documentation/utilities/twitter-bearer-token-generator/" target="_blank">Generate a bearer token here</a></small></p>
					</td>
		        </tr>
		        <?php endif; ?>

		    </table>




			<h2 class="mobiletitle">Administration</h2>
			<hr class="mobiletitle"/>

			<table class="form-table dependent administration" style="display: none;">
		        <tr valign="top">
		        	<th scope="row">Developer IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_dev_ip" value="<?php echo get_option('nebula_dev_ip'); ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" style="width: 392px;" />
						<p class="helper"><small>Comma-separated IP addresses of the developer to enable specific console logs and other dev info. Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
					</td>
		        </tr>
		        <tr valign="top">
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
		        <tr valign="top">
		        	<th scope="row">Client IPs&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_client_ip" value="<?php echo get_option('nebula_client_ip'); ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" style="width: 392px;" />
						<p class="helper"><small>Comma-separated IP addresses of the client to enable certain features. Your current IP address is <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></small></p>
					</td>
		        </tr>
		        <tr valign="top">
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
		        <tr valign="top">
		        	<th scope="row">Hosting&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<?php
							$hostURL = explode(".", gethostname());
						?>
						<input type="text" name="nebula_hosting_url" value="<?php echo get_option('nebula_hosting_url'); ?>" placeholder="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>/" style="width: 392px;" />
						<p class="helper"><small>Link to the server host for easy access to support and other information. Server detected as <a href="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>" target="_blank">http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?></a>.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Domain Registrar&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_registrar_url" value="<?php echo get_option('nebula_registrar_url'); ?>" placeholder="http://<?php echo whois_info('registrar_url'); ?><?php echo ( whois_info('reseller') )? '*' : ''; ?>" style="width: 392px;" />
						<p class="helper"><small>Link to the domain registrar used for access to pointers, forwarding, and other information. <?php if ( whois_info('registrar') ) : ?> Registrar detected as <a href="http://<?php echo whois_info('registrar_url'); ?>"><?php echo whois_info('registrar'); ?></a><?php echo ( whois_info('reseller') )? ' *(via ' . whois_info('reseller') . ')' : ''; ?></small><?php endif; ?></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Google Analytics URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_ga_url" value="<?php echo get_option('nebula_ga_url'); ?>" placeholder="http://www.google.com/analytics/..." style="width: 392px;" />
						<p class="helper"><small>Link directly to this project's <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a> report.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Google Webmaster Tools URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_webmaster_tools_url" value="<?php echo get_option('nebula_google_webmaster_tools_url'); ?>" placeholder="https://www.google.com/webmasters/tools/..." style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/webmasters/tools/" target="_blank">Google Webmaster</a> Tools.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Google AdSense URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_adsense_url" value="<?php echo get_option('nebula_google_adsense_url'); ?>" placeholder="https://www.google.com/adsense/app" style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/adsense/" target="_blank">Google AdSense</a> account.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Google AdWords URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
					<td>
						<input type="text" name="nebula_google_adwords_url" value="<?php echo get_option('nebula_google_adwords_url'); ?>" placeholder="https://www.google.com/adwords/" style="width: 392px;" />
						<p class="helper"><small>Direct link to this project's <a href="https://www.google.com/adwords/" target="_blank">Google AdWords</a> account.</small></p>
					</td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row">Mention URL&nbsp;<a class="help" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a></th>
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