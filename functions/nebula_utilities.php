<?php

$GLOBALS['ga_v'] = 1; //Version
$GLOBALS['ga_cid'] = gaParseCookie(); //Anonymous Client ID

//Handle the parsing of the _ga cookie or setting it to a unique identifier
function gaParseCookie(){
	if (isset($_COOKIE['_ga'])){
		list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
		$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
		$cid = $contents['cid'];
	} else {
		$cid = gaGenerateUUID();
	}
	return $cid;
}

//Generate UUID v4 function (needed to generate a CID when one isn't available)
function gaGenerateUUID(){
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), //32 bits for "time_low"
		mt_rand(0, 0xffff), //16 bits for "time_mid"
		mt_rand(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
		mt_rand(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) //48 bits for "node"
	);
}

//Send Data to Google Analytics
//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
function gaSendData($data){
	$getString = 'https://ssl.google-analytics.com/collect';
	$getString .= '?payload_data&';
	$getString .= http_build_query($data);
	$result = wp_remote_get($getString);
	return $result;
}

//Send Pageview Function for Server-Side Google Analytics
function ga_send_pageview($hostname=null, $path=null, $title=null){
	if ( empty($GLOBALS['ga_v']) ){
		$GLOBALS['ga_v'] = 1;
	}

	if ( empty($GLOBALS['ga_cid']) ){
		$GLOBALS['ga_cid'] = gaParseCookie();
	}

	if ( empty($hostname) ){
		$hostname = nebula_url_components('hostname');
	}

	if ( empty($path) ){
		$path = nebula_url_components('path');
	}

	if ( empty($title) ){
		$title = get_the_title();
	}

	$data = array(
		'v' => $GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $GLOBALS['ga_cid'],
		't' => 'pageview',
		'dh' => $hostname, //Document Hostname "gearside.com"
		'dp' => $path, //Path "/something"
		'dt' => $title, //Title
		'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
	);
	gaSendData($data);
}

//Send Event Function for Server-Side Google Analytics
//@TODO "Nebula" 0: "WordPress" is still appearing in Google Analytics browser reports for these events!
function ga_send_event($category=null, $action=null, $label=null, $value=null, $ni=1){
	if ( empty($GLOBALS['ga_v']) ){
		$GLOBALS['ga_v'] = 1;
	}

	if ( empty($GLOBALS['ga_cid']) ){
		$GLOBALS['ga_cid'] = gaParseCookie();
	}

	$data = array(
		'v' => $GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $GLOBALS['ga_cid'],
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
	gaSendData($data);
}

//Send custom data to Google Analytics. Must pass an array of data to this function:
//ga_send_custom(array('t' => 'event', 'ec' => 'Category Here', 'ea' => 'Action Here', 'el' => 'Label Here'));
//https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
function ga_send_custom($array){ //@TODO "Nebula" 0: Add additional parameters to this function too (like above)!
	$defaults = array(
		'v' => $GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $GLOBALS['ga_cid'],
		't' => '',
		'ni' => 1,
		'dh' => nebula_url_components('hostname'), //Document Hostname "gearside.com"
		'dp' => nebula_url_components('path'),
		'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
	);

	$data = array_merge($defaults, $array);

	if ( !empty($data['t']) ){
		gaSendData($data);
	} else {
		trigger_error("ga_send_custom() requires an array of values. A Hit Type ('t') is required! See documentation here for accepted parameters: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters", E_USER_ERROR);
		return;
	}
}

//Check if user is using the debug query string.
//$strict requires the user to be a developer or client. Passing 2 to $strict requires the dev or client to be logged in too.
function is_debug($strict=false){
	$very_strict = ( $strict > 1 )? $strict : false;
	if ( array_key_exists('debug', $_GET) ){
		if ( !empty($strict) ){
			if ( is_dev($very_strict) || is_client($very_strict) ){
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	return false;
}

//Check if a user has been online in the last 15 minutes
function nebula_is_user_online($id){
	$logged_in_users = get_transient('users_status');
	return isset($logged_in_users[$id]) && ($logged_in_users[$id] > (time()-(15*60))); //15 Minutes
}

function nebula_user_last_online($id){
	$logged_in_users = get_transient('users_status');
	if ( isset($logged_in_users[$id]) ){
		return $logged_in_users[$id];
	} else {
		return false;
	}
}

//Check if the current IP address matches any of the dev IP address from Nebula Options
//Passing $strict bypasses IP check, so user must be a dev and logged in.
//Note: This should not be used for security purposes since IP addresses can be spoofed.
function is_dev($strict=false){
	if ( empty($strict) ){
		$devIPs = explode(',', get_option('nebula_dev_ip'));
		foreach ( $devIPs as $devIP ){
			if ( trim($devIP) == $_SERVER['REMOTE_ADDR'] ){
				return true;
			}
		}
	}

	//Check if the current user's email domain matches any of the dev email domains from Nebula Options
	$current_user = wp_get_current_user();
	list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO "Nebula" 0: If $current_user->user_email is not empty?

	$devEmails = explode(',', get_option('nebula_dev_email_domain'));
	foreach ( $devEmails as $devEmail ){
		if ( trim($devEmail) == $current_user_domain ){
			return true;
		}
	}

	return false;
}

//Check if the current IP address matches any of the client IP address from Nebula Options
//Passing $strict bypasses IP check, so user must be a client and logged in.
//Note: This should not be used for security purposes since IP addresses can be spoofed.
function is_client($strict=false){
	if ( empty($strict) ){
		$clientIPs = explode(',', get_option('nebula_client_ip'));
		foreach ( $clientIPs as $clientIP ){
			if ( trim($clientIP) == $_SERVER['REMOTE_ADDR'] ){
				return true;
			}
		}
	}

	//Check if the current user's email domain matches any of the dev email domains from Nebula Options
	$current_user = wp_get_current_user();
	list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO "Nebula" 0: If $current_user->user_email is not empty?

	$clientEmails = explode(',', get_option('nebula_client_email_domain'));
	foreach ( $clientEmails as $clientEmail ){
		if ( trim($clientEmail) == $current_user_domain ){
			return true;
		}
	}

	return false;
}

//Check if the current IP address matches Pinckney Hugo Group.
//Note: This should not be used for security purposes since IP addresses can be spoofed.
function is_at_phg(){
	if ( $_SERVER['REMOTE_ADDR'] == '72.43.235.106' ){
		return true;
	} else {
		return false;
	}
}


//Get the full URL. Not intended for secure use ($_SERVER var can be manipulated by client/server).
function nebula_requested_url($host="HTTP_HOST"){ //Can use "SERVER_NAME" as an alternative to "HTTP_HOST".
	$protocol = ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 )? 'https' : 'http';
	$full_url = $protocol . '://' . $_SERVER["$host"] . $_SERVER["REQUEST_URI"];
	return $full_url;
}

//Separate a URL into it's components.
function nebula_url_components($segment="all", $url=null){
	if ( !$url ){
		$url = nebula_requested_url();
	}

	$url_compontents = parse_url($url);
	if ( empty($url_compontents['host']) ){
		return;
	}
	$host = explode('.', $url_compontents['host']);

	//Best way to get the domain so far. Probably a better way by checking against all known TLDs.
	preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);
	$sld = substr($domain[0], 0, strpos($domain[0], '.'));
	$tld = substr($domain[0], strpos($domain[0], '.'));

	switch ($segment){
		case ('all') :
		case ('href') :
			return $url;
			break;

		case ('protocol') : //Protocol and Scheme are aliases and return the same value.
		case ('scheme') : //Protocol and Scheme are aliases and return the same value.
		case ('schema') :
			if ( $url_compontents['scheme'] != '' ){
				return $url_compontents['scheme'];
			} else {
				return false;
			}
			break;

		case ('port') :
			if ( $url_compontents['port'] ){
				return $url_compontents['port'];
			} else {
				switch( $url_compontents['scheme'] ){
	                case ('http') :
	                    return 80; //Default for http
	                    break;
	                case 'https':
	                    return 443; //Default for https
	                    break;
	                case 'ftp':
	                    return 21; //Default for ftp
	                    break;
	                case 'ftps':
	                    return 990; //Default for ftps
	                    break;
	                default:
	                    return false;
	                    break;
	            }
			}
			break;

		case ('user') : //Returns the username from this type of syntax: https://username:password@gearside.com/
		case ('username') :
			if ( $url_compontents['user'] ){
				return $url_compontents['user'];
			} else {
				return false;
			}
			break;

		case ('pass') : //Returns the password from this type of syntax: https://username:password@gearside.com/
		case ('password') :
			if ( $url_compontents['pass'] ){
				return $url_compontents['pass'];
			} else {
				return false;
			}
			break;

		case ('authority') :
			if ( $url_compontents['user'] && $url_compontents['pass'] ){
				return $url_compontents['user'] . ':' . $url_compontents['pass'] . '@' . $url_compontents['host'] . ':' . nebula_url_components('port', $url);
			} else {
				return false;
			}
			break;

		case ('host') : //In http://something.example.com the host is "something.example.com"
		case ('hostname') :
			return $url_compontents['host'];
			break;

		case ('www') :
			if ( $host[0] == 'www' ){
				return 'www';
			} else {
				return false;
			}
			break;

		case ('subdomain') :
		case ('sub_domain') :
			if ( $host[0] != 'www' && $host[0] != $sld ){
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
		case ('origin') :
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
		case ('pathname') :
			return $url_compontents['path'];
			break;

		case ('file') : //Filename will be just the filename/extension.
		case ('filename') :
			if ( contains(basename($url_compontents['path']), array('.')) ){
				return basename($url_compontents['path']);
			} else {
				return false;
			}
			break;

		case ('extension') : //The extension only.
		    if ( contains(basename($url_compontents['path']), array('.')) ){
		        $file_parts = explode('.', $url_compontents['path']);
		        return $file_parts[1];
		    } else {
		        return false;
		    }
		    break;

		case ('path') : //Path should be just the path without the filename/extension.
			if ( contains(basename($url_compontents['path']), array('.')) ){ //@TODO "Nebula" 0: This will possibly give bad data if the directory name has a "." in it
				return str_replace(basename($url_compontents['path']), '', $url_compontents['path']);
			} else {
				return $url_compontents['path'];
			}
			break;

		case ('query') :
		case ('queries') :
		case ('search') :
			return $url_compontents['query'];
			break;

		case ('fragment') :
		case ('fragments') :
		case ('anchor') :
		case ('hash') :
		case ('hashtag') :
		case ('id') :
			return $url_compontents['fragment'];
			break;

		default :
			return $url;
			break;
	}
}

//Fuzzy meta sub key finder (Used to query ACF nested repeater fields).
//Example: 'key' => 'dates_%_start_date',
add_filter('posts_where' , 'nebula_fuzzy_posts_where');
function nebula_fuzzy_posts_where($where){
	if ( strpos($where, '_%_') > -1 ){
		$where = preg_replace("/meta_key = ([\'\"])(.+)_%_/", "meta_key LIKE $1$2_%_", $where);
	}
	return $where;
}


//Use WordPress core browser detection
//@TODO "Nebula" 0: Look into using this in addition to a more powerful library.
function wp_browser_detect(){
	//Browsers
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	//$browser = get_browser(null, true); //@TODO "Nebula" 0: Find a server this works on and then wrap in if $browser, then echo the version number too
	//@TODO "Nebula" 0: Also look into the function wp_check_browser_version().

    if ( $is_lynx ){
    	return 'Lynx';
    } elseif ( $is_gecko ){
    	return 'Gecko';
    } elseif ( $is_opera ){
    	return 'Opera';
    } elseif ( $is_NS4 ){
    	return 'NS4';
    } elseif ( $is_safari ){
    	return 'Safari';
    } elseif ( $is_chrome ){
    	return 'Chrome';
    } elseif ( $is_IE ){
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
function in_array_r($needle, $haystack, $strict = true){
    foreach ($haystack as $item){
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))){
            return true;
        }
    }
    return false;
}

//Recursive Glob
function glob_r($pattern, $flags = 0){
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
        $files = array_merge($files, glob_r($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

//Add up the filesizes of files in a directory (and it's sub-directories)
function foldersize($path){
	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/') . '/';
	foreach ( $files as $t ){
		if ( $t <> "." && $t <> ".."){
			$currentFile = $cleanPath . $t;
			if ( is_dir($currentFile) ){
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
function contains($str, array $arr){
    foreach ( $arr as $a ){
        if ( stripos($str, $a) !== false ){
        	return true;
        }
    }
    return false;
}

//Check if a website is available
function nebula_is_available($domain){
	$curlInit = curl_init($domain);
	curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curlInit, CURLOPT_HEADER, true);
	curl_setopt($curlInit, CURLOPT_NOBODY, true);
	curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($curlInit);
	curl_close($curlInit);

	if ( $response ){
		return true;
	}
	return false;
}

//Generate a random integer between two numbers with an exclusion array
//Call it like: random_number_between_but_not(1, 10, array(5, 6, 7, 8));
function random_number_between_but_not($min=null, $max=null, $butNot=null){
    if ( $min > $max ){ //If min is greater than max, swap variables
		$tmp = $min;
		$min = $max;
		$max = $tmp;
    }
    if ( gettype($butNot) == 'array' ){
        foreach( $butNot as $key => $skip ){
            if( $skip > $max || $skip < $min ){
                unset($butNot[$key]);
            }
        }
        if ( count($butNot) == $max-$min+1 ){
            trigger_error('Error: no number exists between ' . $min .' and ' . $max .'. Check exclusion parameter.', E_USER_ERROR);
            return false;
        }
        while ( in_array(($randnum = rand($min, $max)), $butNot));
    } else {
        while (($randnum = rand($min, $max)) == $butNot );
    }
    return $randnum;
}

//Call a placeholder image from Unsplash.it
function unsplash_it($width=800, $height=600, $raw=false, $random=false){
	$skip_list = array(31, 35, 224, 285, 312, 16, 403, 172, 268, 267, 349, 69, 103, 24, 140, 47, 219, 222, 184, 306, 70, 371, 385, 45, 211, 95, 83, 150, 233, 275, 343, 317, 278, 429, 383, 296, 292, 193, 299, 195, 298, 68, 148, 151, 129, 277, 333, 85, 48, 128, 365, 138, 155, 257, 37, 288, 407);
	if ( !is_int($random) ){
		$randID = random_number_between_but_not(0, 800, $skip_list); //Update the second number here periodically as more Unsplash.it photos become available.
	} else {
		$randID = $random;
	}

	//Check if unsplash.it is online
	if ( !nebula_is_available('https://unsplash.it') ){
		ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Unsplash.it Not Available');
		if ( $raw ){
			return placehold_it($width, $height, 'Unsplash.it Unavailable', 'ca3838');
		} else {
			return placehold_it($width, $height, 'Unsplash.it Unavailable', 'ca3838') . '" title="Unsplash.it is not available.';
		}
	}

	$image_path = 'https://unsplash.it/' . $width . '/' . $height . '?image=' . $randID;
	$check_image = nebula_is_available($image_path); //Ignore errors (because that's what we're looking for)

	$i++;
	while ( !$check_image ){
		if ( !$random || $i >= 5 ){
			ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Image Not Found (ID: ' . $randID . ')');
			if ( $raw ){
				placehold_it($width, $height, 'ID+' . $randID . '+Not+Found', 'f6b83f');
			} else {
				return placehold_it($width, $height, 'ID+' . $randID . '+Not+Found', 'f6b83f') . '" title="Unsplash image with ID ' . $randID . ' not found.';
			}
		}

	    $skip_list[] = $randID;
	    ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Image Not Found (ID: ' . $randID . ')');
	    $randID = random_number_between_but_not(0, 615, $skipList);
	    $image_path = 'https://unsplash.it/' . $width . '/' . $height . '?image=' . $randID;
	    $check_image = nebula_is_available($image_path); //Ignore errors (because that's what we're looking for)
	    $i++;
	}

	if ( $raw ){
		return $image_path;
	} else {
		return $image_path . '" title="Unsplash ID #' . $randID;
	}
}

//Call a placeholder image from Placehold.it
function placehold_it($width=800, $height=600, $text=false, $color=false){
	if ( nebula_is_available('https://placehold.it') ){
		$text = ( $text )? '?text=' . str_replace(' ', '+', $text) : '';
		$color = ( $color )? str_replace('#', '', $color) . '/' : '';
		return 'https://placehold.it/' . $width . 'x' . $height . '/' . $color . $text;
	} else {
		return get_template_directory_uri() . '/images/x.png'; //Placehold.it is not available.
	}
}


//Automatically convert HEX colors to RGB.
function hex2rgb($color){
	if ( $color[0] == '#' ){
		$color = substr($color, 1);
	}
	if ( strlen($color) == 6 ){
		list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
	} elseif ( strlen($color) == 3 ){
		list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array('r' => $r, 'g' => $g, 'b' => $b);
}

//Check the brightness of a color. 0=darkest, 255=lightest, 256=false
function nebula_color_brightness($hex){
	//@TODO "Nebula" 0: If an rgb value is passed, (create then) run an rgb2hex() function
	if ( strpos($hex, '#') !== false ){
		preg_match("/#(?:[0-9a-fA-F]{3,6})/i", $hex, $hex_colors);

		if ( strlen($hex_colors[0]) == 4 ){
			$values = str_split($hex_colors[0]);
			$full_hex = '#' . $values[1] . $values[1] . $values[2] . $values[2] . $values[3] . $values[3];
		} else {
			$full_hex = $hex_colors[0];
		}

		$hex = str_replace('#', '', $full_hex);
		$hex_r = hexdec(substr($hex, 0, 2));
		$hex_g = hexdec(substr($hex, 2, 2));
		$hex_b = hexdec(substr($hex, 4, 2));

		return (($hex_r*299)+($hex_g*587)+($hex_b*114))/1000;
	} else {
		return 256;
	}
}

//Attempt to get WHOIS information from domain
function whois_info($data, $domain=''){
	if ( $domain == '' ){
		$whois = getwhois(nebula_url_components('sld'), ltrim(nebula_url_components('tld'), '.'));
	} else {
		$whois = getwhois(nebula_url_components('sld', $domain), ltrim(nebula_url_components('tld', $domain), '.'));
		$whois = preg_replace('!\s+!', ' ', $whois);
	}

	switch ( $data ){
		case 'expiration':
		case 'expiration_date':
		case 'domain_expiration':
			if ( contains($whois, array('Registrar Registration Expiration Date: ')) ){
				return trim(substr($whois, strpos($whois, "Registrar Registration Expiration Date: ")+40, 10));
			} elseif ( contains($whois, array('Registry Expiry Date: ')) ){
				return trim(substr($whois, strpos($whois, "Registry Expiry Date: ")+22, 10));
			} elseif ( contains($whois, array('Relevant dates: ')) ){
				return trim(substr($whois, strpos($whois, "Expiry date:")+13, 11));
			} elseif ( contains($whois, array('Expiry date: ')) ){
				return trim(substr($whois, strpos($whois, "Expiry date:")+13, 10));
			} elseif ( contains($whois, array('Domain expires: ')) ){
				return trim(substr($whois, strpos($whois, "Domain expires: ")+16, 11));
			}
			return false;
			break;
		case 'registrar':
		case 'registrar_name':
			$domain_registrar_start = '';
			$domain_registrar_stop = '';
			if ( contains($whois, array('Registrar: ')) && contains($whois, array('Sponsoring Registrar IANA ID:')) ){
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Sponsoring Registrar IANA ID:")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar: ')) && contains($whois, array('Registrar IANA ID: ')) ){
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Registrar IANA ID: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar: ')) && contains($whois, array('Registrar IANA ID: ')) ){
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Registrar IANA ID: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Sponsoring Registrar:')) && contains($whois, array('Sponsoring Registrar IANA ID:')) ){
				$domain_registrar_start = strpos($whois, "Sponsoring Registrar:")+21;
				$domain_registrar_stop = strpos($whois, "Sponsoring Registrar IANA ID:")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar:')) && contains($whois, array('Number: ')) ){
				$domain_registrar_start = strpos($whois, "Registrar:")+17;
				$domain_registrar_stop = strpos($whois, "Number: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar:')) && contains($whois, array('URL:')) ){ //co.uk
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "URL: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			}
			return false;
			break;
		case 'registrar_url':
			if ( contains($whois, array('Registrar URL: ')) && contains($whois, array('Updated Date: ')) ){
				$domain_registrar_url_start = strpos($whois, "Registrar URL: ")+15;
				$domain_registrar_url_stop = strpos($whois, "Updated Date: ")-$domain_registrar_url_start;
				return trim(substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop));
			} elseif ( contains($whois, array('Registrar URL: ')) && contains($whois, array('Update Date: ')) ){
				$domain_registrar_url_start = strpos($whois, "Registrar URL: ")+15;
				$domain_registrar_url_stop = strpos($whois, "Update Date: ")-$domain_registrar_url_start;
				return trim(substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop));
			} elseif ( contains($whois, array('URL: ')) && contains($whois, array('Relevant dates:')) ){ //co.uk
				$domain_registrar_url_start = strpos($whois, "URL: ")+5;
				$domain_registrar_url_stop = strpos($whois, "Relevant dates: ")-$domain_registrar_url_start;
				return trim(substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop));
			}
			return false;
			break;
		case 'reseller':
		case 'reseller_name':
			$domain_reseller = '';
			if ( contains($whois, array('Reseller: ')) && contains($whois, array('Domain Status: ')) ){
				$reseller1 = strpos($whois, 'Reseller: ');
				$reseller2 = strpos($whois, 'Reseller: ', $reseller1 + strlen('Reseller: '));
				if ( $reseller2 ){
					$domain_reseller_start = strpos($whois, "Reseller: ")+10;
					$domain_reseller_stop = $reseller2-$domain_reseller_start;
					return trim(substr($whois, $domain_reseller_start, $domain_reseller_stop));
				} else {
					$domain_reseller_start = strpos($whois, "Reseller: ")+10;
					$domain_reseller_stop = strpos($whois, "Domain Status: ")-$domain_reseller_start;
					return trim(substr($whois, $domain_reseller_start, $domain_reseller_stop));
				}
			}
			return false;
			break;
	}
}

//Returns WHOIS information from the passed domain.
function getwhois($domain, $tld){
	if ( empty($domain) ){
		$domain = nebula_url_components('sld'); //Default value is current domain
	}
	if ( empty($tld) ){
		$tld = nebula_url_components('tld'); //Default value is current domain
	}

	require_once(get_template_directory() . "/includes/libs/class-whois.php");
	$whois = new Whois();

	if( !$whois->ValidDomain($domain . '.' . $tld) ){
		return 'Sorry, "' . $domain . '.' . $tld . '" is not valid or not supported.';
	}

	if ( $whois->Lookup($domain . '.' . $tld) ){
		return $whois->GetData(1);
	} else {
		return 'A WHOIS error occurred.';
	}
}

//Compare values using passed parameters
function nebula_compare_operator($a=null, $b=null, $c='=='){
	if ( empty($a) || empty($b) ){
		trigger_error('nebula_compare_operator requires values to compare.');
		return false;
	}

	switch ( $c ){
        case "=":
        case "==":
        case "e":
        	return $a == $b;
        case ">=":
        case "=>":
        case "gte":
        case "ge":
        	return $a >= $b;
        case "<=":
        case "=<":
        case "lte":
        case "le":
        	return $a <= $b;
        case ">":
        case "gt":
        	return $a > $b;
        case "<":
        case "lt":
        	return $a < $b;
		default:
			trigger_error('nebula_compare_operator does not allow "' . $c . '".');
			return false;
    }
}

//Check the current (or passed) PHP version against the PHP support timeline.
function nebula_php_version_support($php_version=PHP_VERSION){
	$php_timeline_json_file = get_template_directory() . '/includes/data/php_timeline.json';
	$php_timeline = get_transient('nebula_php_timeline');
	if ( empty($php_timeline) || is_debug() ){
		$php_timeline = @file_get_contents('https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/php_timeline.json');
		if ( !empty($php_timeline) ){
			if ( is_writable(get_template_directory()) ){
				file_put_contents($php_timeline_json_file, $php_timeline); //Store it locally.
			}
			set_transient('nebula_php_timeline', $php_timeline, 60*60*24*30); //1 month cache
		} else {
			$php_timeline = file_get_contents($php_timeline_json_file);
		}
	}
	$php_timeline = json_decode($php_timeline);

	foreach ( $php_timeline[0] as $php_timeline_version => $php_timeline_dates ){
		if ( version_compare(PHP_VERSION, $php_timeline_version) >= 0 ){
			$output = array();
			if ( !empty($php_timeline_dates->security) && time() < strtotime($php_timeline_dates->security) ){
				$output['lifecycle'] = 'active';
			} elseif ( !empty($php_timeline_dates->security) && (time() >= strtotime($php_timeline_dates->security) && time() < strtotime($php_timeline_dates->end)) ){
				$output['lifecycle'] = 'security';
			} elseif ( time() >= strtotime($php_timeline_dates->end) ) {
				$output['lifecycle'] = 'end';
			} else {
				$output['lifecycle'] = 'unknown'; //An error of some kind has occurred.
			}
			$output['security'] = strtotime($php_timeline_dates->security);
			$output['end'] = strtotime($php_timeline_dates->end);
			return $output;
			break;
		}
	}
}


/*==========================
	SCSS Compiling
	http://leafo.net/scssphp/docs/
 ===========================*/

if ( nebula_option('nebula_scss') ){
	if ( is_writable(get_template_directory()) ){
		add_action('init', 'nebula_render_scss');
		add_action('admin_init', 'nebula_render_scss');
	} else {
		echo '<!-- Directory is not writable for SCSS! -->';
	}
}
function nebula_render_scss($specific_scss=null){
	require_once(get_template_directory() . '/includes/libs/scssphp/scss.inc.php'); //scssphp is a compiler for SCSS 3.x
	$scss = new \Leafo\ScssPhp\Compiler(); //This can't be the proper way to invoke this... but it works.
	$scss->addImportPath(get_template_directory() . '/stylesheets/scss/partials/');
	$scss->setFormatter('Leafo\ScssPhp\Formatter\Compact');
	if ( is_debug() ){
		$scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
	}

	if ( empty($specific_scss) || $specific_scss == 'all' ){
		$latest_partial = 0;
		foreach ( glob(get_template_directory() . '/stylesheets/scss/partials/*') as $partial_file ){
			if ( filemtime($partial_file) > $latest_partial ){
				$latest_partial = filemtime($partial_file);
			}
		}

		foreach ( glob(get_template_directory() . '/stylesheets/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
			$file_path_info = pathinfo($file);

			if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){ //If file esits, and has .scss extension, and doesn't begin with "_".
				$file_counter++;
				$css_filepath = ( $file_path_info['filename'] == 'style' )? get_template_directory() . '/style.css' : get_template_directory() . '/stylesheets/css/' . $file_path_info['filename'] . '.css';

				if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_partial > filemtime($css_filepath) || is_debug() || $specific_scss == 'all' ){ //If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
					ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
					$existing_css_contents = ( file_exists($css_filepath) )? file_get_contents($css_filepath) : '';
					if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){ //If the correlating .css file doesn't contain a comment to prevent overwriting
						$this_scss_contents = file_get_contents($file); //Copy SCSS file contents
						$compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
						$enhanced_css = nebula_scss_variables($compiled_css); //Compile server-side variables into SCSS
						file_put_contents($css_filepath, $enhanced_css); //Save the rendered CSS
					}
				}
			}
		}
	} else {
		if ( file_exists($specific_scss) ){ //If $specific_scss is a filepath
			$scss_contents = file_get_contents($specific_scss); //Copy SCSS file contents
			$compiled_css = $scss->compile($scss_contents); //Compile the SCSS
			$enhanced_css = nebula_scss_variables($compiled_css); //Compile server-side variables into SCSS
			file_put_contents(str_replace('.scss', '.css', $specific_scss), $enhanced_css); //Save the rendered CSS in the same directory
		} else { //If $scss_file is raw SCSS string
			$compiled_css = $scss->compile($specific_scss);
			return nebula_scss_variables($compiled_css); //Return the rendered CSS
		}
	}
}

//Compile server-side variables into SCSS
function nebula_scss_variables($scss){
	$scss = preg_replace("(<%template_directory%>)", get_template_directory_uri(), $scss); //Template Directory
	$scss = preg_replace("(" . str_replace('/', '\/', get_template_directory()) . ")", '', $scss); //Reduce theme path
	$scss .= '/* Processed on ' . date('l, F j, Y \a\t g:ia', time()) . ' */';
	update_option('nebula_scss_last_processed', time());
	return $scss;
}

//If SASS should be manually re-generated
if ( nebula_option('nebula_scss') ){
	if ( is_writable(get_template_directory()) ){
		add_action('init', 'nebula_sass_manual_trigger');
		add_action('admin_init', 'nebula_sass_manual_trigger');
	} else {
		echo '<!-- Directory is not writable for SCSS! -->';
	}
}
function nebula_sass_manual_trigger(){
	if ( nebula_option('nebula_scss', 'enabled') && (isset($_GET['sass']) || isset($_GET['scss']) || $_GET['settings-updated'] == 'true') && (is_dev() || is_client()) ){
		nebula_render_scss('all'); //Re-render all SCSS files.
	}
}

/*==========================
 User Agent Parsing Functions/Helpers
 ===========================*/

//Boolean return if the user's device is mobile.
function nebula_is_mobile(){
	if ( $GLOBALS["device_detect"]->isMobile() ){
		return true;
	}
	return false;
}

//Boolean return if the user's device is a tablet.
function nebula_is_tablet(){
	if ( $GLOBALS["device_detect"]->isTablet() ){
		return true;
	}
	return false;
}

//Boolean return if the user's device is a desktop.
function nebula_is_desktop(){
	if ( $GLOBALS["device_detect"]->isDesktop() ){
		return true;
	}
	return false;
}

//Returns the requested information of the operating system of the user's device.
function nebula_get_os($info='full'){
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

//Check to see how the operating system version of the user's device compares to a passed version number.
function nebula_is_os($os=null, $version=null, $comparison='=='){
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
	return false;
}

//Returns the requested information of the model of the user's device.
function nebula_get_device($info='model'){
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

//Returns the requested information of the browser being used.
function nebula_get_client($info){ return get_browser($info); }
function nebula_get_browser($info='name'){
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

//Check to see how the browser version compares to a passed version number.
function nebula_is_browser($browser=null, $version=null, $comparison='=='){
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
				if ( $version_parts[1] && $version_parts[1] != 0 ){ //If minor version exists and is not 0
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
	return false;
}

//Check to see if the rendering engine matches a passed parameter.
function nebula_is_engine($engine=null){
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

	$actual_engine = $GLOBALS["device_detect"]->getClient();
	if ( strpos(strtolower($actual_browser['engine']), strtolower($engine)) !== false ){
		return true;
	}
	return false;
}

//Check for bot/crawler traffic
//UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
function nebula_is_bot(){
	$bots = array('bot', 'crawl', 'spider', 'feed', 'slurp', 'tracker', 'http');
	foreach( $bots as $bot ){
		if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $bot) !== false ){
			return true;
			break;
		}
	}

	if ( $GLOBALS["device_detect"]->isBot() ){ //This might work fine on it's own without the above foreach loop
		return true;
		break;
	}
	return false;
}

//Device Detection v3.3 - https://github.com/piwik/device-detector
//Be careful when updating this library. DeviceDetector.php requires modification to work without Composer!
require_once(get_template_directory() . '/includes/libs/device-detector/DeviceDetector.php');
use DeviceDetector\DeviceDetector;
$GLOBALS["device_detect"] = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
$GLOBALS["device_detect"]->discardBotInformation(); //If called, getBot() will only return true if a bot was detected (speeds up detection a bit)
$GLOBALS["device_detect"]->parse();