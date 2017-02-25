<?php
/**
 * Google_Analytics
 *
 * @package     Nebula\Google_Analytics
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Google_Analytics' ) ) {

    class Nebula_Google_Analytics {

        public function __construct() {
            //Sends events to Google Analytics via AJAX (used if GA is blocked via JavaScript)
            add_action('wp_ajax_nebula_ga_event_ajax', array( $this, 'event_ajax' ) );
            add_action('wp_ajax_nopriv_nebula_ga_event_ajax', array( $this, 'event_ajax' ) );
        }

        //Handle the parsing of the _ga cookie or setting it to a unique identifier
        public function parse_cookie(){
            $override = apply_filters('pre_ga_parse_cookie', false);
            if ( $override !== false ){return $override;}

            if ( isset($_COOKIE['_ga']) ){
                list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
                $contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
                $cid = $contents['cid'];
            } else {
                $cid = $this->generate_UUID();
            }
            return $cid;
        }

        //Generate UUID v4 function (needed to generate a CID when one isn't available)
        public function generate_UUID(){
            $override = apply_filters('pre_ga_generate_UUID', false);
            if ( $override !== false ){return $override;}

            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), //32 bits for "time_low"
                mt_rand(0, 0xffff), //16 bits for "time_mid"
                mt_rand(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
                mt_rand(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) //48 bits for "node"
            );
        }

        //Generate Domain Hash
        public function generate_domain_hash($domain){
            $override = apply_filters('pre_ga_generate_domain_hash', false, $domain);
            if ( $override !== false ){return $override;}

            if ( empty($domain) ){
                $domain = nebula_url_components('domain');
            }

            $a = 0;
            for ( $i = strlen($domain)-1; $i >= 0; $i-- ){
                $ascii = ord($domain[$i]);
                $a = (($a<<6)&268435455)+$ascii+($ascii<<14);
                $c = $a&266338304;
                $a = ( $c != 0 )? $a^($c>>21) : $a;
            }
            return $a;
        }

        //Generate the full path of a Google Analytics __utm.gif with necessary parameters.
        //https://developers.google.com/analytics/resources/articles/gaTrackingTroubleshooting?csw=1#gifParameters
        public function UTM_gif($user_cookies=array(), $user_parameters=array()){
            $override = apply_filters('pre_ga_UTM_gif', false, $user_cookies, $user_parameters);
            if ( $override !== false ){return $override;}

            //@TODO "Nebula" 0: Make an AJAX function in Nebula (plugin) to accept a form for each parameter then renders the __utm.gif pixel.

            $domain = nebula_url_components('domain');
            $cookies = array(
                'utma' => $this->generate_domain_hash($domain) . '.' . mt_rand(1000000000, 9999999999) . '.' . time() . '.' . time() . '.' . time() . '.1', //Domain Hash . Random ID . Time of First Visit . Time of Last Visit . Time of Current Visit . Session Counter ***Absolutely Required***
                'utmz' => $this->generate_domain_hash($domain) . '.' . time() . '.1.1.', //Campaign Data (Domain Hash . Time . Counter . Counter)
                'utmcsr' => '-', //Campaign Source "google"
                'utmccn' => '-', //Campaign Name "(organic)"
                'utmcmd' => '-', //Campaign Medium "organic"
                'utmctr' => '-', //Campaign Terms (for paid search)
                'utmcct' => '-', //Campaign Content Description
            );
            $cookies = array_merge($cookies, $user_cookies);

            $data = array(
                'utmwv' => '5.3.8', //Tracking code version *** REQUIRED ***
                'utmac' => nebula_option('ga_tracking_id'), //Account string, appears on all requests *** REQUIRED ***
                'utmdt' => get_the_title(), //Page title, which is a URL-encoded string *** REQUIRED ***
                'utmp' => nebula_url_components('filepath'), //Page request of the current page (current path) *** REQUIRED ***
                'utmcc' => '__utma=' . $cookies['utma'] . ';+', //Cookie values. This request parameter sends all the cookies requested from the page. *** REQUIRED ***

                'utmhn' => nebula_url_components('hostname'), //Host name, which is a URL-encoded string
                'utmn' => rand(pow(10, 10-1), pow(10, 10)-1), //Unique ID generated for each GIF request to prevent caching of the GIF image
                'utms' => '1', //Session requests. Updates every time a __utm.gif request is made. Stops incrementing at 500 (max number of GIF requests per session).
                'utmul' => str_replace('-', '_', get_bloginfo('language')), //Language encoding for the browser. Some browsers don’t set this, in which case it is set to '-'
                'utmje' => '0', //Indicates if browser is Java enabled. 1 is true.
                'utmhid' => mt_rand(1000000000, 9999999999), //A random number used to link the GA GIF request with AdSense
                'utmr' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '-', //Referral, complete URL. If none, it is set to '-'
                'utmu' => 'q~', //This is a new parameter that contains some internal state that helps improve ga.js
            );
            $data = array_merge($data, $user_parameters);

            //Append Campaign Data to the Cookie parameter
            if ( !empty($cookies['utmcsr']) && !empty($cookies['utmcsr']) && !empty($cookies['utmcsr']) ){
                $data['utmcc'] = '__utma=' . $cookies['utma'] . ';+__utmz=' . $cookies['utmz'] . 'utmcsr=' . $cookies['utmcsr'] . '|utmccn=' . $cookies['utmccn'] . '|utmcmd=' . $cookies['utmcmd'] . '|utmctr=' . $cookies['utmctr'] . '|utmcct=' . $cookies['utmcct'] . ';+';
            }

            return 'https://ssl.google-analytics.com/__utm.gif?' . str_replace('+', '%20', http_build_query($data));
        }

        //Send Data to Google Analytics
        //https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
        public function send_data($data){
            $override = apply_filters('pre_ga_send_data', false, $data);
            if ( $override !== false ){return $override;}

            $result = wp_remote_get('https://ssl.google-analytics.com/collect?payload_data&' . http_build_query($data));
            return $result;
        }

        //Send Pageview Function for Server-Side Google Analytics
        public function send_pageview($hostname=null, $path=null, $title=null, $array=array()){
            $override = apply_filters('pre_ga_send_pageview', false, $hostname, $path, $title, $array);
            if ( $override !== false ){return $override;}

            if ( empty($hostname) ){
                $hostname = nebula_url_components('hostname');
            }

            if ( empty($path) ){
                $path = nebula_url_components('path');
            }

            if ( empty($title) ){
                $title = get_the_title();
            }

            //GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
            //GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
            $data = array(
                'v' => 1,
                'tid' => nebula_option('ga_tracking_id'),
                'cid' => $this->parse_cookie(),
                't' => 'pageview',
                'dh' => $hostname, //Document Hostname "gearside.com"
                'dp' => $path, //Path "/something"
                'dt' => $title, //Title
                'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
            );

            $data = array_merge($data, $array);
            $this->send_data($data);
        }

        //Send Event Function for Server-Side Google Analytics
        public function send_event($category=null, $action=null, $label=null, $value=null, $ni=1, $array=array()){
            $override = apply_filters('pre_ga_send_event', false, $category, $action, $label, $value, $ni, $array);
            if ( $override !== false ){return $override;}

            //GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
            //GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
            $data = array(
                'v' => 1,
                'tid' => nebula_option('ga_tracking_id'),
                'cid' => $this->parse_cookie(),
                't' => 'event',
                'ec' => $category, //Category (Required)
                'ea' => $action, //Action (Required)
                'el' => $label, //Label
                'ev' => $value, //Value
                'ni' => $ni, //Non-Interaction
                'dh' => nebula_url_components('hostname'), //Document Hostname "gearside.com"
                'dp' => nebula_url_components('path'),
                'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
            );

            $data = array_merge($data, $array);
            $this->send_data($data);
        }

        //Send custom data to Google Analytics. Must pass an array of data to this function:
        //ga_send_custom(array('t' => 'event', 'ec' => 'Category Here', 'ea' => 'Action Here', 'el' => 'Label Here'));
        //https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
        public function send_custom($array=array()){ //@TODO "Nebula" 0: Add additional parameters to this function too (like above)!
            $override = apply_filters('pre_ga_send_custom', false, $array);
            if ( $override !== false ){return $override;}

            //GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
            //GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
            $defaults = array(
                'v' => 1,
                'tid' => nebula_option('ga_tracking_id'),
                'cid' => $this->parse_cookie(),
                't' => '',
                'ni' => 1,
                'dh' => nebula_url_components('hostname'), //Document Hostname "gearside.com"
                'dp' => nebula_url_components('path'),
                'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
            );

            $data = array_merge($defaults, $array);

            if ( !empty($data['t']) ){
                $this->send_data($data);
            } else {
                trigger_error("ga_send_custom() requires an array of values. A Hit Type ('t') is required! See documentation here for accepted parameters: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters", E_USER_ERROR);
                return;
            }
        }

        public function event_ajax(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            if ( !nebula_is_bot() ){ //Is this conditional preventing this from working at times?
                $this->send_event(sanitize_text_field($_POST['data'][0]['category']), sanitize_text_field($_POST['data'][0]['action']), sanitize_text_field($_POST['data'][0]['label']), sanitize_text_field($_POST['data'][0]['value']), sanitize_text_field($_POST['data'][0]['ni']));
            }
            wp_die();
        }

    }

}// End if class_exists check