<?php
/**
 * Hubspot_CRM
 *
 * @package     Nebula\Hubspot_CRM
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Hubspot') ){
    trait Hubspot {
        public function hooks(){
            /*==========================
                Hubspot CRM Integration Functions

                @TODO "Nebula" 0: Expand this functionality to include Salesforce and Marketo too.
                https://github.com/chrisblakley/Nebula/issues/1182

                Salesforce Documentation:
                Marketo Documentation: http://developers.marketo.com/javascript-api/web-personalization/
                    - JavaScript API, so this would be done right from main.js
                    - So, nv() data would probably just make the call to the Marketo function too.
             ===========================*/

            // TODO: Move into Nebula_Hubspot_CRM or Nebula_Utilities_Hubspot_CRM (I prefer move this to an external plugin)

            //Create/Update Contact in Hubspot CRM
            add_action('wp_ajax_nebula_ajax_send_to_hubspot', array($this, 'ajax_send_to_hubspot'));
            add_action('wp_ajax_nopriv_nebula_ajax_send_to_hubspot', array($this, 'ajax_send_to_hubspot'));
        }

        //Send data to Hubspot CRM via PHP curl
        public function hubspot_curl($url, $content=null){
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

            set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', $get_url)), 'Unavailable', 60*5); //5 minute expiration
            return false;
        }

        //Get all existing Hubspot CRM contact properties in the Nebula group
        public function get_hubspot_properties(){
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
        public function create_hubspot_properties($columns=null){
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
        public function ajax_send_to_hubspot(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            $data = array(
                'properties' => $_POST['properties'],
            );

            echo nebula_send_to_hubspot($data);
            wp_die();
        }

        public function send_to_hubspot($data=array()){
            if ( nebula_option('hubspot_api') ){
                //Determine if we'll be using email_address or hubspot_vid for our send. Prefer VID.
                if ( empty($data['hubspot_vid']) && empty($data['email_address']) ){ //If calling from AJAX we must lookup VID or Email Address
                    global $wpdb;
                    $nebula_id = get_nebula_id();
                    $vid_and_email = $wpdb->get_results("SELECT hubspot_vid, email_address FROM nebula_visitors WHERE nebula_id LIKE '" . $nebula_id . "' AND email_address <> '' OR hubspot_vid <> ''"); //here
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
                            $this->update_visitor(array('hubspot_vid' => $hubspot_vid), false); //Update visitor withour re-sending to Hubspot CRM
                            return $hubspot_vid;
                        }
                    } else {
                        $response = nebula_hubspot_curl('https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/' . $email_address . '/', json_encode($content)); //Create or update the contact using their Email
                        if ( strpos($response, 'error') != false ){ //There was an error
                            return false;
                        } else {
                            $response = (array) json_decode($response);
                            $this->update_visitor(array('hubspot_vid' => $response['vid']), false); //Update visitor withour re-sending to Hubspot CRM
                            return $response['vid'];
                        }
                    }
                }
            }

            return false;
        }

        //Get contact data from Hubspot
        //Set this to a variable to avoid multiple calls, then parse it like this: $hubspot_data['properties']['firstname']['value']
        public function get_hubspot_contact($vid=null, $property=''){
            if ( empty($vid) ){
                $vid = $this->get_visitor_data('hubspot_vid');
                if ( empty($vid) ){
                    return false;
                }
            }

            if ( !empty($property) ){
                $property = '&property=' . $property;
            }

            $response = wp_remote_get('https://api.hubapi.com/contacts/v1/contact/vid/' . $vid . '/profile?hapikey=' . nebula_option('hubspot_api') . $property);
            if ( is_wp_error($response) ){
                set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'https://api.hubapi.com/')), 'Unavailable', 60*5); //5 minute expiration
                return false;
            }
            return json_decode($response['body'], true);
        }

    }

}// End if class_exists check