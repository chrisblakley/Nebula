<?php

$_GLOBALS['ga_v'] = 1; //Version
$_GLOBALS['ga_cid'] = gaParseCookie(); //Anonymous Client ID

//Handle the parsing of the _ga cookie or setting it to a unique identifier
function gaParseCookie() {
	if (isset($_COOKIE['_ga'])) {
		list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
		$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
		$cid = $contents['cid'];
	} else {
		$cid = gaGenerateUUID();
	}
	return $cid;
}

//Generate UUID v4 function - needed to generate a CID when one isn't available
function gaGenerateUUID() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), //32 bits for "time_low"
		mt_rand(0, 0xffff), //16 bits for "time_mid"
		mt_rand(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
		mt_rand(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) //48 bits for "node"
	);
}

//Send Data to Google Analytics
//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
function gaSendData($data) {
	$getString = 'https://ssl.google-analytics.com/collect';
	$getString .= '?payload_data&';
	$getString .= http_build_query($data);
	$result = wp_remote_get($getString);
	return $result;
}


//Get the full URL. Not intended for secure use ($_SERVER var can be manipulated by client/server).
function nebula_requested_url($host="HTTP_HOST") { //Can use "SERVER_NAME" as an alternative to "HTTP_HOST".
	$protocol = ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ) ? 'https' : 'http';
	$full_url = $protocol . '://' . $_SERVER["$host"] . $_SERVER["REQUEST_URI"];
	return $full_url;
}


//Separate a URL into it's components.
function nebula_url_components($segment="all", $url=null) {
	if ( !$url ) {
		$url = nebula_requested_url();
	}

	$url_compontents = parse_url($url);
	if ( empty($url_compontents['host']) ) {
		return;
	}
	$host = explode('.', $url_compontents['host']);

	//Best way to get the domain so far. Probably a better way by checking against all known TLDs.
	preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);
	$sld = substr($domain[0], 0, strpos($domain[0], '.'));
	$tld = substr($domain[0], strpos($domain[0], '.'));

	switch ($segment) {
		case ('all') :
			return $url;
			break;

		case ('protocol') : //Protocol and Scheme are aliases and return the same value.
		case ('scheme') : //Protocol and Scheme are aliases and return the same value.
			if ( $url_compontents['scheme'] != '' ) {
				return $url_compontents['scheme'];
			} else {
				return false;
			}
			break;

		case ('host') : //In http://something.example.com the host is "something.example.com"
			return $url_compontents['host'];
			break;

		case ('www') :
			if ( $host[0] == 'www' ) {
				return 'www';
			} else {
				return false;
			}
			break;

		case ('subdomain') :
		case ('sub_domain') :
			if ( $host[0] != 'www' && $host[0] != $sld ) {
				return $host[0];
			} else {
				return false;
			}
			break;

		case ('domain') : //In http://example.com the domain is "example.com"
			return $domain[0];
			break;

		case ('basedomain') : //In http://example.com/something the basedomain is "http://example.com"
		case ('base_domain') :
			return $url_compontents['scheme'] . '://' . $domain[0];
			break;

		case ('sld') : //In example.com the sld is "example"
		case ('second_level_domain') :
		case ('second-level_domain') :
			return $sld;
			break;

		case ('tld') : //In example.com the tld is ".com"
		case ('top_level_domain') :
		case ('top-level_domain') :
			return $tld;
			break;

		case ('filepath') : //Filepath will be both path and file/extension
			return $url_compontents['path'];
			break;

		case ('file') : //Filename will be just the filename/extension.
		case ('filename') :
			if ( contains(basename($url_compontents['path']), array('.')) ) {
				return basename($url_compontents['path']);
			} else {
				return false;
			}
			break;

		case ('path') : //Path should be just the path without the filename/extension.
			if ( contains(basename($url_compontents['path']), array('.')) ) { //@TODO "Nebula" 0: This will possibly give bad data if the directory name has a "." in it
				return str_replace(basename($url_compontents['path']), '', $url_compontents['path']);
			} else {
				return $url_compontents['path'];
			}
			break;

		case ('query') :
		case ('queries') :
			return $url_compontents['query'];
			break;

		default :
			return $url;
			break;
	}
}


//Detect Device //@TODO "Nebula" 0: It would be unfeasible to try to keep this up-to-date... Maybe there is an XML/JSON we can use? If so, may need to keep the more unique ones (like game consoles) here.
function nebula_device_detect($user_agent=''){
	if ( $user_agent == '' ) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	}

	$user_device = "Unknown Device";

	//The order of this array is important!
	$device_array = array(
		'/samsung-sgh-i337/i' => 'Samsung Galaxy S4',
		'/lumia 928/i' => 'Nokia Lumia 928',
		'/iphone1c2/i' => 'Apple iPhone 3G',
		'/iPhone2C1/i' => 'Apple iPhone 3GS',
		'/iPhone3C1/i' => 'Apple iPhone 4',
		'/iPhone3C3/i' => 'Apple iPhone 4 CDMA',
		'/iPhone4C1/i' => 'Apple iPhone 4S',
		'/iPhone5C1/i' => 'Apple iPhone 5',
		'/iPhone5C2/i' => 'Apple iPhone 5 CDMA',
		'/iPhone5C3/i' => 'Apple iPhone 5C GSM',
		'/iPhone5C4/i' => 'Apple iPhone 5C CDMA',
		'/iPhone6C1/i' => 'Apple iPhone 5S GSM',
		'/iPhone6C2/i' => 'Apple iPhone 5S CDMA',
		'/iPad2C1/i' => 'Apple iPad 2 (WiFi only)',
		'/iPad2C2/i' => 'Apple iPad 2 (WiFi + 3G GSM)',
		'/iPad2C3/i' => 'Apple iPad 2 (WiFi + 3G CDMA)',
		'/iPad3C1/i' => 'Apple iPad (3rd Generation) (WiFi only)',
		'/iPad3C2/i' => 'Apple iPad (3rd Generation) (WiFi + 4G Verizon)',
		'/iPad3C3/i' => 'Apple iPad (3rd Generation) (WiFi + 4G AT&T)',
		'/iPad1C1/i' => 'Apple iPad 1',
		'/iPad4C1/i' => 'Apple iPad Air',
		'/cros/i' => 'ChromeBook',
		'/regex_here/i' => 'Return_Value_Here', //windows && phone && iemobile
		'/xbox/i' => 'Microsoft Xbox',
		'/xbox one/i' => 'Microsoft Xbox One',
		'/nintendo/i' => 'Nintendo',
		'/wii/i' => 'Nintendo Wii',
		'/wiiu/i' => 'Nintendo WiiU',
		'/3DS/i' => 'Nintendo 3DS',
		'/playstation 4/i' => 'Sony Playstation 4',
		'/playstation 3/i' => 'Sony Playstation 3',
		'/regex_here/i' => 'Return_Value_Here', //playstation && psp && portable
		'/ipod/i' => 'Apple iPod Touch',
		'/regex_here/i' => 'Return_Value_Here', //linux && apple safari && (is mobile device...)
	);

	foreach ( $device_array as $regex => $value ) {
		if ( preg_match($regex, $user_agent) ) {
			$user_device = $value;
		}
	}
	return $user_device;

}

//Detect Operating System
function nebula_os_detect($user_agent='') {
	if ( $user_agent == '' ) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	}

	$os_platform    =   "Unknown OS Platform";

    $os_array       =   array(
                            '/windows nt 6.3/i'     =>  'Windows 8.1',
                            '/windows nt 6.2/i'     =>  'Windows 8',
                            '/windows nt 6.1/i'     =>  'Windows 7',
                            '/windows nt 6.0/i'     =>  'Windows Vista',
                            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                            '/windows nt 5.1/i'     =>  'Windows XP',
                            '/windows xp/i'         =>  'Windows XP',
                            '/windows nt 5.0/i'     =>  'Windows 2000',
                            '/windows me/i'         =>  'Windows ME',
                            '/win98/i'              =>  'Windows 98',
                            '/win95/i'              =>  'Windows 95',
                            '/win16/i'              =>  'Windows 3.11',
                            '/macintosh|mac os x/i' =>  'Mac OS X',
                            '/mac_powerpc/i'        =>  'Mac OS 9',
                            '/linux/i'              =>  'Linux',
                            '/ubuntu/i'             =>  'Ubuntu',
                            '/iphone/i'             =>  'iPhone',
                            '/ipod/i'               =>  'iPod',
                            '/ipad/i'               =>  'iPad',
                            '/android/i'            =>  'Android',
                            '/blackberry/i'         =>  'BlackBerry',
                            '/webos/i'              =>  'Mobile'
                        );

    foreach ($os_array as $regex => $value) {

        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }

    }

    return $os_platform;
}


//Use WordPress core browser detection
//@TODO "Nebula" 0: Look into using this in addition to a more powerful library.
function wp_browser_detect(){
	//Browsers
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	//$browser = get_browser(null, true); //@TODO "Nebula" 0: Find a server this works on and then wrap in if $browser, then echo the version number too
	//@TODO "Nebula" 0: Also look into the function wp_check_browser_version().

    if ( $is_lynx ) {
    	return 'Lynx';
    } elseif ( $is_gecko ) {
    	return 'Gecko';
    } elseif ( $is_opera ) {
    	return 'Opera';
    } elseif ( $is_NS4 ) {
    	return 'NS4';
    } elseif ( $is_safari ) {
    	return 'Safari';
    } elseif ( $is_chrome ) {
    	return 'Chrome';
    } elseif ( $is_IE ) {
    	return 'IE';
    } else {
    	return 'Unknown Browser';
    }
}




//Text limiter by words
function string_limit_words($string, $word_limit){
	$limited[0] = $string;
	$limited[1] = 0;
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){
		array_pop($words);
		$limited[0] = implode(' ', $words);
		$limited[1] = 1;
	}
	return $limited;
}


//Word limiter by characters
function word_limit_chars($string, $charlimit, $continue=false){
	// 1 = "Continue Reading", 2 = "Learn More"
	if ( strlen(strip_tags($string, '<p><span><a>')) <= $charlimit ){
		$newString = strip_tags($string, '<p><span><a>');
	} else {
		$newString = preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($string, '<p><span><a>'), 0, ($charlimit + 1)));
		if ( $continue == 1 ){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Continue reading <span class="meta-nav">&rarr;</span></a>';
		} elseif( $continue == 2 ){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Learn more &raquo;</a>';
		} else {
			$newString = $newString . '&hellip;';
		}
	}
	return $newString;
}


//Traverse multidimensional arrays
function in_array_r($needle, $haystack, $strict = true) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

//Recursive Glob
function glob_r($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_r($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

//Add up the filesizes of files in a directory (and it's sub-directories)
function foldersize($path) {
	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/') . '/';
	foreach($files as $t) {
		if ($t<>"." && $t<>"..") {
			$currentFile = $cleanPath . $t;
			if (is_dir($currentFile)) {
				$size = foldersize($currentFile);
				$total_size += $size;
			} else {
				$size = filesize($currentFile);
				$total_size += $size;
			}
		}
	}
	return $total_size;
}

//Checks to see if an array contains a string.
function contains($str, array $arr) {
    foreach( $arr as $a ) {
        if ( stripos($str, $a) !== false ) {
        	return true;
        }
    }
    return false;
}

//Generate a random integer between two numbers with an exclusion array
//Call it like: random_number_between_but_not(1, 10, array(5, 6, 7, 8));
function random_number_between_but_not($min=null, $max=null, $butNot=null) {
    if ( $min > $max ) {
        return 'Error: min is greater than max.'; //@TODO "Nebula" 0: If min is greater than max, swap the variables.
    }
    if ( gettype($butNot) == 'array' ) {
        foreach( $butNot as $key => $skip ){
            if( $skip > $max || $skip < $min ){
                unset($butNot[$key]);
            }
        }
        if ( count($butNot) == $max-$min+1 ) {
            return 'Error: no number exists between ' . $min .' and ' . $max .'. Check exclusion parameter.';
        }
        while ( in_array(($randnum = rand($min, $max)), $butNot));
    } else {
        while (($randnum = rand($min, $max)) == $butNot );
    }
    return $randnum;
}


//Automatically convert HEX colors to RGB.
function hex2rgb($color) {
	if ( $color[0] == '#' ) {
		$color = substr($color, 1);
	}
	if ( strlen($color) == 6 ) {
		list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
	} elseif ( strlen($color) == 3 ) {
		list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array('r' => $r, 'g' => $g, 'b' => $b);
}




/*==========================
 Libraries
 ===========================*/


//PHP-Mobile-Detect - https://github.com/serbanghita/Mobile-Detect/wiki/Code-examples
//Before running conditions using this, you must have $detect = new Mobile_Detect(); before the logic. In this case we are using the global variable $GLOBALS["mobile_detect"].
//Logic can fire from "$GLOBALS["mobile_detect"]->isMobile()" or "$GLOBALS["mobile_detect"]->isTablet()" or "$GLOBALS["mobile_detect"]->is('AndroidOS')".
require_once TEMPLATEPATH . '/includes/Mobile_Detect.php'; //@TODO "Nebula" 0: try changing TEMPLATEPATH to get_template_directory()
$GLOBALS["mobile_detect"] = new Mobile_Detect();



//Browser Detection
//http://techpatterns.com/downloads/browser_detection.php
//Documentation: http://techpatterns.com/downloads/scripts/browser_detection_php_ar.txt
//$GLOBALS["browser_detect"] is an associative array with the following structure:
/*
	['browser_working'] - $browser_working,
	['browser_number'] - $browser_number,
	['ie_version'] - $ie_version,
	['dom'] - $b_dom_browser,
	['safe'] - $b_safe_browser,
	['os'] - $os_type,
	['os_number'] - $os_number,
	['browser_name'] - $browser_name,
	['ua_type'] - $ua_type,
	['browser_math_number'] - $browser_math_number,
	['moz_data'] - $a_moz_data,
	['webkit_data'] - $a_webkit_data,
	['mobile_test'] - $mobile_test,
	['mobile_data'] - $a_mobile_data,
	['true_ie_number'] - $true_ie_number,
	['run_time'] - $run_time,
	['html_type'] - $html_type,
	['engine_data'] - $a_engine_data,
	['trident_data'] - $a_trident_data
*/
require_once TEMPLATEPATH . '/includes/browser_detection.php';
$GLOBALS["browser_detect"] = browser_detection('full_assoc');