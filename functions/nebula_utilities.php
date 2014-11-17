<?php


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
        if ( stripos($str,$a) !== false ) {
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

