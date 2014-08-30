<?php

//Include Nebula Settings page
if ( is_admin() ) {
	include_once('includes/nebula-settings.php');	
}

//Uncomment to force override the Nebula Settings. This will disable changes made from the Settings page, and only allow edits from this functions file (to revert, comment out and choose "Enabled" in the Nebula Settings page):
/* update_option('nebula_overall', 'override'); */

//Store global strings as needed
add_action('init', 'global_nebula_vars');
add_action('admin_init', 'global_nebula_vars');
function global_nebula_vars(){
    $GLOBALS['admin_user'] = get_userdata(1);
    $GLOBALS['full_address'] = get_option('nebula_street_address') . ', ' . get_option('nebula_locality') . ', ' . get_option('nebula_region') . ' ' . get_option('nebula_postal_code');
    $GLOBALS['enc_address'] = get_option('nebula_street_address') . '+' . get_option('nebula_locality') . '+' . get_option('nebula_region') . '+' . get_option('nebula_postal_code');
    $GLOBALS['enc_address'] = str_replace(' ', '+', $GLOBALS['enc_address']);
}

//Determine if a function should be used based on several Nebula Settings conditions (for text inputs).
function nebula_settings_conditional_text($setting, $default = ''){
	if ( get_option('nebula_overall') == 'enabled' && get_option($setting) ) {
		return get_option($setting);
	} else {
		return $default;
	}
}

//Determine if a function should be used based on several Nebula Settings conditions (for text inputs).
function nebula_settings_conditional_text_bool($setting, $true = true, $false = false){
	if ( get_option('nebula_overall') == 'enabled' && get_option($setting) ) {
		return $true;
	} else {
		return $false;
	}
}

//Determine if a function should be used based on several Nebula Settings conditions (for select inputs).
function nebula_settings_conditional($setting, $default='enabled') {
	if ( get_option('nebula_overall') == 'override' || get_option('nebula_overall') == 'disabled' || (get_option('nebula_overall') == 'enabled' && get_option($setting) == 'default') || (get_option('nebula_overall') == 'enabled' && get_option($setting) == $default) ) {
		return 1;
	} else {
		return 0;
	}
}