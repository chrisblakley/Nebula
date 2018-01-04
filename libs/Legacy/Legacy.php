<?php

/*==========================
 This file includes functions that provide limited backwards compatibility for previous versions.
 ===========================*/

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Legacy') ){
	trait Legacy {
		public function hooks(){

		}

		//Renamed function
		public function prerender(){ $this->prebrowsing(); }
	}
}

/*==========================
 Procedural Functions
 ===========================*/


//Renamed function (5/10/2016)
function nebula_breadcrumbs(){ nebula()->breadcrumbs(); }
function the_breadcrumb(){ nebula()->breadcrumbs(); }


//Update old options to new options
add_action('admin_init', 'nebula_legacy_options');
function nebula_legacy_options(){
    //Google Webmaster Tools is now Google Search Console
    if ( get_option('google_webmaster_tools_verification') && !get_option('google_search_console_verification') ){
        update_nebula_option('google_search_console_verification', get_option('google_webmaster_tools_verification'));
    }

    //Google Webmaster Tools is now Google Search Console
    if ( get_option('google_webmaster_tools_url') && !get_option('google_search_console_url') ){
        update_nebula_option('google_search_console_url', get_option('google_webmaster_tools_url'));
    }
}


//Prefer a child theme directory or file. Not declaring a directory will return the theme directory.
//nebula_prefer_child_directory('/assets/img/logo.png');
//This was replaced by: get_theme_file_uri() and get_theme_file_path() in WordPress 4.7
function nebula_prefer_child_directory($directory='', $uri=true){
    if ( $directory[0] != '/' ){
        $directory = '/' . $directory;
    }

    if ( file_exists(get_stylesheet_directory() . $directory) ){
        if ( $uri ){
            return get_stylesheet_directory_uri() . $directory;
        }
        return get_stylesheet_directory() . $directory;
    }

    if ( $uri ){
        return get_template_directory_uri() . $directory;
    }
    return get_template_directory() . $directory;
}


global $wp_version;
if ( $wp_version < 4.7 ){
    function get_theme_file_uri(){
        return nebula_prefer_child_directory();
    }

    function get_theme_file_path(){
        return nebula_prefer_child_directory('', false);
    }
}




//Old Nebula Excerpt that does not use the options array.
function nebula_the_excerpt($postID=0, $more=0, $length=55, $hellip=0){
    $override = apply_filters('pre_nebula_the_excerpt', null, $postID, $more, $length, $hellip);
    if ( isset($override) ){return;}

    if ( $postID && is_int($postID) ){
        $the_post = get_post($postID);
    } else {
        if ( $postID != 0 || is_string($postID) ){
            if ( $length == 0 || $length == 1 ){
                $hellip = $length;
            } else {
                $hellip = false;
            }

            $length = 55;
            if ( is_int($more) ){
                $length = $more;
            }

            $more = $postID;
        }
        $postID = get_the_ID();
        $the_post = get_post($postID);
    }

    $post_text = ( !empty($the_post->post_excerpt) )? $the_post->post_excerpt : $the_post->post_content;

    return nebula_excerpt(array(
        'length' => $length,
        'ellipsis' => $hellip,
        'url' => get_permalink($postID),
        'more' => $more,
        'text' => $post_text,
    ));
}

function nebula_custom_excerpt($text=false, $length=55, $hellip=false, $link=false, $more=false){
    return nebula_excerpt(array(
        'text' => $text,
        'url' => $link,
        'more' => $more,
        'length' => $length,
        'ellipsis' => $hellip
    ));
};





//Retarget users based on prior conversions/leads (Modified to work with new DB storage method)
function nebula_retarget($category=false, $data=null, $strict=true, $return=false){
    $response = nebula_get_visitor_data($category);

    if ( $strict ){
        if ( $response == $data ){
            if ( !empty($return) ){
                return $response;
            }

            return true;
        }
    } else {
        if ( strpos($response, $data) !== false ){
            if ( !empty($return) ){
                return $response;
            }

            return true;
        }
    }

    return false;
}


function nebula_google_font_option(){
    $nebula_options = get_option('nebula_options');
    if ( $nebula_options['google_font_url'] ){
        return preg_replace("/(<link href=')|(' rel='stylesheet' type='text\/css'>)|(@import url\()|(\);)/", '', $nebula_options['google_font_url']);
    } elseif ( $nebula_options['google_font_family'] ) {
        $google_font_family = preg_replace('/ /', '+', $nebula_options['google_font_family']);
        $google_font_weights = preg_replace('/ /', '', $nebula_options['google_font_weights']);
        $response = wp_remote_get('https://fonts.googleapis.com/css?family=' . $google_font_family . ':' . $google_font_weights);
        if ( is_wp_error($response) ){
            return false;
        }
        $google_font_contents = $response['body'];
        if ( $google_font_contents !== false ){
            return $google_font_contents;
        }
    }
    return false;
}

//Old Visitor DB functions
//function nebula_get_visitor_data($data){return nebula_vdb_get_visitor_datapoint($data);}
//function nebula_increment_visitor($data){return nebula_vdb_increment_visitor_data($data);}
//function nebula_append_visitor($data){return nebula_vdb_append_visitor_data($data);}

/*==========================
    Hubspot CRM Integration Functions

    @TODO "Nebula" 0: Expand this functionality to include Salesforce and Marketo too.
    https://github.com/chrisblakley/Nebula/issues/1182

    Salesforce Documentation:
    Marketo Documentation: http://developers.marketo.com/javascript-api/web-personalization/
        - JavaScript API, so this would be done right from main.js
        - So, nv() data would probably just make the call to the Marketo function too.
 ===========================*/

//Send data to Hubspot CRM via PHP curl
function nebula_hubspot_curl($url, $content=null){
    $sep = ( strpos($url, '?') === false )? '?' : '&';
    $get_url = $url . $sep . 'hapikey=' . nebula_option('hubspot_api');

    if ( !empty($content) ){
        /*
            @TODO "Nebula" 0: 409 Conflict response happening. Was probably happening with cURL and just never noticed.
                - Because the fields already exist, Hubspot is responding with "409 Conflict".
                - This happens ~14 times since each property is sent individually.
                - I'm pretty sure the data is still transferring just fine.
                - Query Monitor is going red due to the 400-level response.
                - This is a Hubspot CRM issue, not WordPress or Nebula (as far as I can tell)
        */

        $response = wp_remote_post($get_url, array(
            'headers'  => array('Content-Type' => 'application/json'),
            'body' => $content,
        ));
    } else {
        $response = wp_remote_get($get_url);
    }

    if ( !is_wp_error($response) ){
        return $response['body'];
    }

    set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', $get_url)), 'Unavailable', MINUTE_IN_SECONDS*5);
    return false;
}

//Get all existing Hubspot CRM contact properties in the Nebula group
function get_nebula_hubspot_properties(){
    $all_hubspot_properties = nebula_hubspot_curl('https://api.hubapi.com/contacts/v2/properties');
    $all_hubspot_properties = json_decode($all_hubspot_properties, true);

    $existing_nebula_properties = array();
    foreach ( $all_hubspot_properties as $property ){
        if ( $property['groupName'] == 'nebula' ){
            $existing_nebula_properties[] = $property['name'];
        }
    }

    return $existing_nebula_properties;
}

//Create Custom Properties
function nebula_create_hubspot_properties($columns=null){
    if ( nebula_option('hubspot_portal') ){
        //Create the Nebula group of properties
        $content = '{
            "name": "nebula",
            "displayName": "Nebula",
            "displayOrder": 5
        }';
        nebula_hubspot_curl('http://api.hubapi.com/contacts/v2/groups?portalId=' . nebula_option('hubspot_portal'), $content);

        //Get an array of all existing Hubspot CRM contact properties
        $existing_nebula_properties = get_nebula_hubspot_properties();

        //Create Nebula IP custom property within the Nebula group
        if ( !in_array('nebula_ip', $existing_nebula_properties) ){
            $content = '{
                "name": "nebula_ip",
                "label": "IP Address (Nebula)",
                "description": "The IP address.",
                "groupName": "nebula",
                "type": "string",
                "fieldType": "text",
                "formField": true,
                "displayOrder": 6,
                "options": []
            }';
            nebula_hubspot_curl('https://api.hubapi.com/contacts/v2/properties', $content);
        }

        //Create a property from the passed array
        //@todo "Nebula" 0: This loop is accounting for 1 second of server time (about 0.14s per call)! Need to find a way to optimize...
        if ( !empty($columns) ){
            if ( is_string($columns) ){
                $columns = array($columns);
            }
            foreach ( $columns as $column ){
                $column_label = ucwords(str_replace('_', ' ', $column));
                if ( !in_array($column, $existing_nebula_properties) ){
                    //Create custom property within the Nebula group
                    $content = '{
                        "name": "' . $column . '",
                        "label": "' . $column_label . '",
                        "description": "' . $column_label . ' (Parsed from Visitor DB).",
                        "groupName": "nebula",
                        "type": "string",
                        "fieldType": "text",
                        "formField": true,
                        "displayOrder": 6,
                        "options": []
                    }';
                    nebula_hubspot_curl('https://api.hubapi.com/contacts/v2/properties', $content);
                }
            }
        }
    }
}

//Create/Update Contact in Hubspot CRM
add_action('wp_ajax_nebula_ajax_send_to_hubspot', 'nebula_ajax_send_to_hubspot');
add_action('wp_ajax_nopriv_nebula_ajax_send_to_hubspot', 'nebula_ajax_send_to_hubspot');
function nebula_ajax_send_to_hubspot(){
    if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

    $data = array(
        'properties' => $_POST['properties'],
    );

    echo nebula_send_to_hubspot($data);
    wp_die();
}
function nebula_send_to_hubspot($data=array()){
    if ( nebula_option('hubspot_api') ){
        //Determine if we'll be using email_address or hubspot_vid for our send. Prefer VID.
        if ( empty($data['hubspot_vid']) && empty($data['email_address']) ){ //If calling from AJAX we must lookup VID or Email Address
            global $wpdb;
            $nebula_id = get_nebula_id();
	    $sql = $wpdb->prepare("SELECT hubspot_vid, email_address FROM nebula_visitors WHERE nebula_id LIKE '"%d"' AND email_address <> '' OR hubspot_vid <> ''", $nebula_id);
            $vid_and_email = $wpdb->get_results($sql);
            $vid_and_email = (array) $vid_and_email[0];
            $hubspot_vid = $vid_and_email['hubspot_vid'];
            $email_address = $vid_and_email['email_address'];
        } else { //Calling directly from another PHP function
            if ( !empty($data['hubspot_vid']) ){
                $hubspot_vid = $data['hubspot_vid'];
            }
            if ( !empty($data['email_address']) ){
                $email_address = $data['email_address'];
            }
        }

        if ( !empty($hubspot_vid) || !empty($email_address) ){ //If visitor has hubspot_vid or email_address
            //Create the properties array
            $content = array('properties' => array());
            $needed_properties = array();

            //Loop through provided properties
            foreach ( $data['properties'] as $group ){
                $needed_properties[] = $group['property'];

                $content['properties'][] = array(
                    'property' => $group['property'],
                    'value' => $group['value']
                );
            }

            nebula_create_hubspot_properties($needed_properties); //Check and create existing properties

            if ( !empty($hubspot_vid) ){
                $response = nebula_hubspot_curl('https://api.hubapi.com/contacts/v1/contact/vid/' . $hubspot_vid . '/profile', json_encode($content)); //Update the existing contact using their VID

                if ( strpos($response, 'error') != false ){ //There was an error
                    return false;
                } elseif ( is_string($response) && $response == '' ){ //This API reponse is simply a 200 when it succeeds (empty string).
                    nebula_update_visitor(array('hubspot_vid' => $hubspot_vid), false); //Update visitor withour re-sending to Hubspot CRM
                    return $hubspot_vid;
                }
            } else {
                $response = nebula_hubspot_curl('https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/' . $email_address . '/', json_encode($content)); //Create or update the contact using their Email
                if ( strpos($response, 'error') != false ){ //There was an error
                    return false;
                } else {
                    $response = (array) json_decode($response);
                    nebula_update_visitor(array('hubspot_vid' => $response['vid']), false); //Update visitor withour re-sending to Hubspot CRM
                    return $response['vid'];
                }
            }
        }
    }

    return false;
}

//Get contact data from Hubspot
//Set this to a variable to avoid multiple calls, then parse it like this: $hubspot_data['properties']['firstname']['value']
function nebula_get_hubspot_contact($vid=null, $property=''){
    global $nebula;
    if ( empty($vid) ){
        $vid = nebula_get_visitor_data('hubspot_vid');
        if ( empty($vid) ){
            return false;
        }
    }

    if ( !empty($property) ){
        $property = '&property=' . $property;
    }

    $response = wp_remote_get('https://api.hubapi.com/contacts/v1/contact/vid/' . $vid . '/profile?hapikey=' . nebula_option('hubspot_api') . $property);
    if ( is_wp_error($response) ){
        set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'https://api.hubapi.com/')), 'Unavailable', MINUTE_IN_SECONDS*5);
        return false;
    }
    return json_decode($response['body'], true);
}
