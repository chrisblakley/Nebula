<?php
/**
 * Device_Detection
 *
 * @package     Nebula\Device_Detection
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

use DeviceDetector\DeviceDetector;

// TODO: This class could be Nebula_Device or Nebula_Device_Info?

if( !class_exists( 'Nebula_Device_Detection' ) ) {

    class Nebula_Device_Detection {

        public function __construct() {
            /*==========================
             User Agent Parsing Functions/Helpers
             ===========================*/

            //Device Detection - https://github.com/piwik/device-detector
            //Be careful when updating this library. DeviceDetector.php requires modification to work without Composer!
            add_action('init', array( $this, 'detect' ) );
        }

        /*==========================
         User Agent Parsing Functions/Helpers
         ===========================*/

        // TODO: If we creates the class Nebula_Device_Detection, this method should be detect()
        public function detect(){
            if ( nebula_option('device_detection') ){
                require_once(get_template_directory() . '/includes/libs/device-detector/DeviceDetector.php'); // TODO: If we move classes to includes, includes/libs should go on libs
                $GLOBALS["device_detect"] = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
                $GLOBALS["device_detect"]->discardBotInformation(); //If called, getBot() will only return true if a bot was detected (speeds up detection a bit)
                $GLOBALS["device_detect"]->parse();
            }
        }

        //Boolean return if the user's device is mobile.
        public function is_mobile(){
            $override = apply_filters('pre_nebula_is_mobile', false);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( $GLOBALS["device_detect"]->isMobile() ){
                    return true;
                }
            }

            global $is_iphone;
            if ( $is_iphone ){
                return true;
            }

            return false;
        }

        //Boolean return if the user's device is a tablet.
        public function is_tablet(){
            $override = apply_filters('pre_nebula_is_tablet', false);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( $GLOBALS["device_detect"]->isTablet() ){
                    return true;
                }
            }

            return false;
        }

        //Boolean return if the user's device is a desktop.
        public function is_desktop(){
            $override = apply_filters('pre_nebula_is_desktop', false);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( $GLOBALS["device_detect"]->isDesktop() ){
                    return true;
                }
            }

            return false;
        }

        //Returns the requested information of the operating system of the user's device.
        public function get_os($info='full'){
            $override = apply_filters('pre_nebula_get_os', false, $info);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                $os = $GLOBALS["device_detect"]->getOs();
                switch ( strtolower($info) ){
                    case 'full':
                        return $os['name'] . ' ' . $os['version'];
                        break;
                    case 'name':
                        return $os['name'];
                        break;
                    case 'version':
                        return $os['version'];
                        break;
                    default:
                        return false;
                        break;
                }
            }

            global $is_iphone;
            switch ( strtolower($info) ){
                case 'full':
                case 'name':
                    if ( $is_iphone ){
                        return 'ios';
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }

        //Check to see how the operating system version of the user's device compares to a passed version number.
        public function is_os($os=null, $version=null, $comparison='=='){
            $override = apply_filters('pre_nebula_is_os', false, $os, $version, $comparison);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( empty($os) ){
                    trigger_error('nebula_is_os requires a parameter of requested operating system.');
                    return false;
                }

                switch ( strtolower($os) ){
                    case 'macintosh':
                        $os = 'mac';
                        break;
                    case 'win':
                        $os = 'windows';
                        break;
                }

                $actual_os = $GLOBALS["device_detect"]->getOs();
                $actual_version = explode('.', $actual_os['version']);
                $version_parts = explode('.', $version);
                if ( strpos(strtolower($actual_os['name']), strtolower($os)) !== false ){
                    if ( !empty($version) ){
                        if ( nebula_compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //If major version matches
                            if ( $version_parts[1] && $version_parts[1] != 0 ){ //If minor version exists and is not 0
                                if ( nebula_compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //If minor version matches
                                    return true;
                                } else {
                                    return false;
                                }
                            } else {
                                return true;
                            }
                        }
                    } else {
                        return true;
                    }
                }
            }

            return false;
        }

        //Returns the requested information of the model of the user's device.
        public function get_device($info='model'){
            $override = apply_filters('pre_nebula_get_device', false, $info);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                $info = str_replace(' ', '', $info);
                switch ( strtolower($info) ){
                    case 'full':
                        return $GLOBALS["device_detect"]->getBrandName() . ' ' . $GLOBALS["device_detect"]->getModel();
                        break;
                    case 'brand':
                    case 'brandname':
                    case 'make':
                        return $GLOBALS["device_detect"]->getBrandName();
                        break;
                    case 'model':
                    case 'version':
                    case 'name':
                        return $GLOBALS["device_detect"]->getModel();
                        break;
                    case 'type':
                        return $GLOBALS["device_detect"]->getDeviceName();
                        break;
                    case 'formfactor':
                        if ( nebula_is_mobile() ){
                            return 'mobile';
                        } elseif ( nebula_is_tablet() ){
                            return 'tablet';
                        } else {
                            return 'desktop';
                        }
                    default:
                        return false;
                        break;
                }
            }

            global $is_iphone;
            $info = str_replace(' ', '', $info);
            switch ( strtolower($info) ){
                case 'brand':
                case 'brandname':
                case 'make':
                case 'model':
                case 'name':
                case 'type':
                    if ( $is_iphone ){
                        return 'iphone';
                    }
                    break;
                case 'formfactor':
                    if ( $is_iphone ){
                        return 'mobile';
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }

        //Returns the requested information of the browser being used.
        public function get_client($info){ return get_browser($info); }
        public function get_browser($info='name'){
            $override = apply_filters('pre_nebula_get_browser', false, $info);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                $client = $GLOBALS["device_detect"]->getClient();
                switch ( strtolower($info) ){
                    case 'full':
                        return $client['name'] . ' ' . $client['version'];
                        break;
                    case 'name':
                    case 'browser':
                    case 'client':
                        return $client['name'];
                        break;
                    case 'version':
                        return $client['version'];
                        break;
                    case 'engine':
                        return $client['engine'];
                        break;
                    case 'type':
                        return $client['type'];
                        break;
                    default:
                        return false;
                        break;
                }
            }

            global $is_gecko, $is_IE, $is_opera, $is_safari, $is_chrome;
            switch ( strtolower($info) ){
                case 'full':
                case 'name':
                case 'browser':
                case 'client':
                    if ( $is_IE ){return 'internet explorer';}
                    elseif ( $is_opera ){return 'opera';}
                    elseif ( $is_safari ){return 'safari';}
                    elseif ( $is_chrome ){return 'chrome';}
                    break;
                case 'engine':
                    if ( $is_gecko ){return 'gecko';}
                    elseif ( $is_safari ){return 'webkit';}
                    elseif ( $is_IE ){return 'trident';}
                    break;
                default:
                    return false;
                    break;
            }
        }

        //Check to see how the browser version compares to a passed version number.
        public function is_browser($browser=null, $version=null, $comparison='=='){
            $override = apply_filters('pre_nebula_is_browser', false, $browser, $version, $comparison);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( empty($browser) ){
                    trigger_error('nebula_is_browser requires a parameter of requested browser.');
                    return false;
                }

                switch ( strtolower($browser) ){
                    case 'ie':
                        $browser = 'internet explorer';
                        break;
                    case 'ie7':
                        $browser = 'internet explorer';
                        $version = '7';
                        break;
                    case 'ie8':
                        $browser = 'internet explorer';
                        $version = '8';
                        break;
                    case 'ie9':
                        $browser = 'internet explorer';
                        $version = '9';
                        break;
                    case 'ie10':
                        $browser = 'internet explorer';
                        $version = '10';
                        break;
                    case 'ie11':
                        $browser = 'internet explorer';
                        $version = '11';
                        break;
                }

                $actual_browser = $GLOBALS["device_detect"]->getClient();
                $actual_version = explode('.', $actual_browser['version']);
                $version_parts = explode('.', $version);
                if ( strpos(strtolower($actual_browser['name']), strtolower($browser)) !== false ){
                    if ( !empty($version) ){
                        if ( nebula_compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //Major version comparison
                            if ( !empty($version_parts[1]) ){ //If minor version exists and is not 0
                                if ( nebula_compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //Minor version comparison
                                    return true;
                                } else {
                                    return false;
                                }
                            } else {
                                return true;
                            }
                        }
                    } else {
                        return true;
                    }
                }
            }

            return false;
        }

        //Check to see if the rendering engine matches a passed parameter.
        public function is_engine($engine=null){
            $override = apply_filters('pre_nebula_is_engine', false, $engine);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( empty($engine) ){
                    trigger_error('nebula_is_engine requires a parameter of requested engine.');
                    return false;
                }

                switch ( strtolower($engine) ){
                    case 'ie':
                    case 'internet explorer':
                        $engine = 'trident';
                        break;
                    case 'web kit':
                        $engine = 'webkit';
                        break;
                }

                $actual_browser = $GLOBALS["device_detect"]->getClient();
                if ( strpos(strtolower($actual_browser['engine']), strtolower($engine)) !== false ){
                    return true;
                }
            }

            return false;
        }

        //Check for bot/crawler traffic
        //UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
        public function is_bot(){
            $override = apply_filters('pre_nebula_is_bot', false);
            if ( $override !== false ){return $override;}

            if ( nebula_option('device_detection') ){
                if ( $GLOBALS["device_detect"]->isBot() ){
                    return true;
                }
            }

            $bots = array('bot', 'crawl', 'spider', 'feed', 'slurp', 'tracker', 'http');
            foreach( $bots as $bot ){
                if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $bot) !== false ){
                    return true;
                    break;
                }
            }

            return false;
        }

    }

}