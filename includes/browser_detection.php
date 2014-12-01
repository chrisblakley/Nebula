<?php
/*
Script Name: Full Featured PHP Browser/OS detection
Author: Harald Hope, Website: http://techpatterns.com/
Script Source URI: http://techpatterns.com/downloads/php_browser_detection.php
Version 5.6.0
Copyright (C) 2014-04-08

Special thanks to alanjstr for cleaning up the code, especially on function get_item_version(),
which he improved greatly. Also to Tapio Markula, for his initial inspiration of creating a
useable php browser detector. Also to silver Harloe for his ideas about using associative arrays
to both return and use as main return handler.

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

Get the full text of the GPL here: http://www.gnu.org/licenses/gpl.txt

Coding conventions:
http://cvs.sourceforge.net/viewcvs.py/phpbb/phpBB2/docs/codingstandards.htm?rev=1.3

Requires PHP verson 4.2 or newer.

******************************************
This is currently set to accept these primary parameters, although you can add as many as you want:
Note: all string data is returned as LOWER CASE, so make sure to keep that in mind when testing.
NOTE: as 5.3.0 some of these names have been changed to be more human readable. The old names will
still work, and it is noted where this change has happened like this: [deprecated: moz_version].
This change will will NOT break your existing programming or browser detection function calls.
[NUMBER] = full array numeric index key; ['array_key'] associative array key
******************************************

**************************
FULL ARRAY RETURN OPTIONS:
 Note that mobile/moz/trident/webkit_data are arrays which could contain null data, so always test first
 before assuming the mobile/moz/trident/webkit arrays contain any data, ie, if moz or if webkit, then do..

 To get this return, you use the 'full' or 'full_assoc' value in the function call, like:
 $a_browser_data = browser_detection('full')
 $a_browser_data = browser_detection('full_assoc')
**************************
full - returns this array, listed by array index number:
  [0]  - $browser_working
  [1]  - $browser_number
  [2]  - $ie_version
  [3]  - $b_dom_browser
  [4]  - $b_safe_browser
  [5]  - $os_type
  [6]  - $os_number
  [7]  - $browser_name
  [8]  - $ua_type
  [9]  - $browser_math_number
  [10] - $a_moz_data
  [11] - $a_webkit_data
  [12] - $mobile_test (null or string value)
  [13] - $a_mobile_data (null or array of mobile data)
  [14] - $true_ie_number
  [15] - $run_time
  [16] - $html_type
  [17] - $a_engine_data
  [18] - $a_trident_data

full_assoc - returns all relevant browser information in an associative array, same as above
             only with string indexes instead of numeric.
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

******************************************************************************
ALPHABETICAL DESCRIPTION OF DATA OPTIONS (With 'full' numeric index key first)
  To get these you use either the full/full_assoc arrays with the keys, or you can get
  any of these individually in the function call, like:
  browser_detection('browser_math_number')
  or, if using 'full_assoc':
  $a_browser_data = browser_detection('full_assoc');
  $a_browser_data['browser_math_number']
  or, if using 'full':
  $a_browser_data = browser_detection('full');
  $a_browser_data[9]
******************************************************************************
[9]  ['browser_math_number'] - [deprecated: 'math_number'] returns basic version number, for math
     comparison, ie. 1.2rel2a becomes 1.2. If browser_number is null, browser_math_number = ''
[7]  ['browser_name'] - returns the full browser name string, if available, otherwise returns ''
     (information not available)
[1]  ['browser_number'] - [deprecated: 'number'] returns the browser version number, if available,
     otherwise returns '' (information not available)
[0]  ['browser_working'] [deprecated: 'browser'] - returns the working shorthand browser name:
     ie, op, moz, konq, saf, ns4, webkit, and some others. If not shorthand, it will probably just
     return the full browser name, like lynx
[3]  ['dom'] - returns true/false if it is a basic dom browser, ie >= 5, opera >= 5, all new mozillas,
     safaris, konquerors
[17] ['engine_data'] - Returns array of layout engine data, if present. Opera>=10;MSIE>=7;Gecko;Webkit
       [0] - Layout engine name, eg: Presto (Opera);Trident (MSIE);Gecko;Webkit
       [1] - Layout engine full version string, like: 2.03beta4
       [2] - Layout engine math number, like: 2.03
[16] ['html_type'] - returns following values, numeric rather than t/f, so you can use
     math comparisons for html support, ie: if ( $browser('html_type') > 1 ) then...
     NOTE - DO NOT RELY ON THIS FULLY, ALWAYS TEST FOR SUPPORT ON THE SPECIFIC BROWSER.
     Browser support information here:
     http://en.wikipedia.org/wiki/Comparison_of_layout_engines_%28HTML5%29
     Currently these are the supported return values (as HTML advances, more numbers
     will be added in thefuture):
       1 - covers all HTMLs prior to HTML 5. All non html 5 supporting browsers / bots will return 1
       2 - HTML 5 - Basic HTML 5 support. Canvas, Audio, Video, + some others. Test uses layout
           engine version numbers to determine if it's html5 ready or not. The test does not
           verify actual html 5 support, you will need to do that yourself with more complex html 5.
       3 - Supports HTML 5 Forms reasonably well. along with basic HTML 5
[2]  ['ie_version'] - tests to see what general IE it is.
     Possible return values:
       ie9x - all new msie 9 or greater - note that if in compat mode, 7,8,9 all show as 7
       ie7x - all new msie 7 or greater
       ie5x - msie 5 and 6, not mac
       ieMac - msie 5.x mac release
       ie4 - msie 4
       old - pre msie 4
[12] ['mobile_test'] - returns a string of various mobile id methods, from device to os to browser.
     If string is not null, should be a mobile. Also see 16, 'ua_type', which will be 'mobile'
     if handheld.
[13] ['mobile_data'] - returns an array of data about mobiles. Note the browser/os number data is very
     unreliable so don't count on that. No blackberry version handling done explicitly.
     Make sure to test if this is an array because if it's not mobile it will be null, not an array
     listed by array index number:
       [0] - $mobile_device
       [1] - $mobile_browser
       [2] - $mobile_browser_number
       [3] - $mobile_os
       [4] - $mobile_os_number
       [5] - $mobile_server
       [6] - $mobile_server_number
       [7] - $mobile_device_number (this was added so has to be end of list to not break existing code)
       [8] - $mobile_tablet (test if not null rather than t/f, returns for example: ipad, android 3-4)
             Note that this is barebones, but will catch ~98+% (varies year to year) of current
             tablets at this point. This test will never be perfect due to very little info in tablet ua.
      Note: $mobile_browser only returns if a specifically mobile browser is detected, like minimo.
      Same for mobile os, with the exception of linux. Otherwise the standard script os/browser data
      is used. $mobile_server is a handheld service like docomo, novarro-vision, etc. Sometimes the
      string will contain no other usable data than this to determine if it's handheld or not.
[10] ['moz_data'] [deprecated: 'moz_version'] - returns array of mozilla / gecko information
     Return Array listed by index number:
       [0] - $moz_type [moz version - the specific brand name that is, eg: firefox)
       [1] - $moz_type_number - the full version number of $moz_type (eg: for firefox: 3.6+2b)
       [2] - $moz_rv - the Mozilla rv version number, math comparison version. This tells you what
             gecko engine is running in the browser (eg rv: 1.8)
       [3] - $moz_rv_full - rv number (for full rv, including alpha and beta versions: 1.8.1-b3)
       [4] - $moz_release_date - release date of the browser
[5]  ['os'] - returns which os is being used - win, nt, mac, OR iphone, blackberry, palmos, palmsource,
     symbian, beos, os2, amiga, webtv, linux, unix.
[6]  ['os_number'] - returns windows versions, 95, 98, ce, me, nt: 4; 5 [windows 2000];
     5.1 [windows xp]; 5.2 [Server 2003]; 6.0 [Windows Vista], 6.1 [Windows 7].
     Only win, nt, mac, iphone return os numbers (mac/iphone return 10 if OS X.)
     OR returns linux distro/unix release name, otherwise returns null
[15] ['run_time'] - the time it takes this script to execute from start to point of returning value
     Requires PHP 5 or greater. Returns time in seconds to 8 decimal places: 0.00245687
     Run time does not count the time used by PHP to include/parse the file initially. That total
     time is about 5-10x longer. Because subsequent script run throughs go VERY fast, you will see
     the seconds go from something like 0.00115204 for first time, to something like 0.00004005
     for second and more runs.
[4]  ['safe'] - returns true/false, you can determine what makes the browser be safe lower down,
     currently it's set for ns4 and pre version 1 mozillas not being safe, plus all older browsers
[18] ['trident_data'] - returns array of trident (msie engine) browser data.
     Return Array listed by index number:
       [0] - $trident_type [trident version name (Eg. ucbrowser)]
       [1] - $trident_type_number [trident version number (Eg. UCBrowser 2.9.0.263)]
       [2] - $layout_engine_number [the actual trident version number (Eg. Trident 5.0)]
       [3] - $browser_number [the actual MSIE version number (Eg. MSIE: 9.0)]
[14] ['true_ie_number'] - [deprecated: true_msie_version] returns the true version of msie running,
     ignoring the compat mode version.
     Note that php will turn 7.0 to 8 when adding 1, so keep that in mind in your tests. 7.1
     will become 8.1 as expected, however. This test currently only tests for 7.x -> 8.x
     FYI: in PHP, 7.0 == 7 is true but 7.0 === 7 is NOT true.
     If this is null but set, then it is NOT running in compatibility mode.
     AS OF MSIE 11 this is not present or used, so all > 11 msie browsers will NOT show compat mode
[8]  ['ua_type'] [deprecated: 'type'] - returns one of the following:
       bot (web bot)
       bro (normal browser)
       bbro (simple browser)
       mobile (handheld)
       dow (downloading agent)
       lib (http library)
[11] ['webkit_data'] - [deprecated: 'webkit_version'] returns array of webkit data.
     Return Array listed by index number:
       [0] - $webkit_type [webkit version name (Eg. chrome)]
       [1] - $webkit_type_number [webkit version number (Eg. Chrome's: 1.2)]
       [2] - $browser_number [the actual webkit version number (Eg. Webkit's: 436)]

******************************************
Optional second script parameter, to turn off features if not required. These would be the second
argument used in the function call if used, like: browser_detection( 'full', '1' );
Test Exclusions - switches to turn off various tests, useful if you want to optimize execution
and don't need the test data type excluded.
******************************************
1 - turn off os tests
2 - turn off mobile tests
3 - turn off mobile and os tests
******************************************
Optional third script parameter, pass the script externally derived UA strings, for testing/
processing purposes. Idea from Rui Teixeira
Note: include a blank second arg when you use the 3rd parameter if the second is not set:
example: browser_detection( 'full', '', $test_string_data )
Using third parameter sets $b_repeat to false in other words, if you use this parameter, the script
will do the full processing on the UA string, then switch $b_repeat back to true at the end.

However, be aware that all requests to the script after the last testing value is sent will
use the previous testing value, NOT the actual UA string, so make sure to handle that in your
programming if you require the true UA data to be processed after the final testing value is sent
by resetting that data with the true UA value.
*******************************************/

// main script, uses two other functions, get_os_data() and get_item_version() as needed
// Optional $test_excludes is either null or one of the above values

function browser_detection( $which_test, $test_excludes='', $external_ua_string='' )
{
	/*
	uncomment the global variable declaration if you want the variables to be available on
	a global level throughout your php page, make sure that php is configured to support
	the use of globals first!
	Use of globals should be avoided however, and they are not necessary with this script
	/*
	/*
	global $a_full_assoc_data, $a_mobile_data, $a_moz_data, $a_engine_data, $a_webkit_data, $b_dom_browser, $b_repeat, $b_safe_browser, $browser_name, $browser_number, $browser_math_number, $browser_user_agent, $browser_working, $html_type, $ie_version, $mobile_test, $moz_type_number, $moz_rv, $moz_rv_full, $moz_release_date, $moz_type, $os_number, $os_type, $layout_engine, $layout_engine_nu, $layout_engine_nu_full, $true_ie_number, $ua_type, $webkit_type, $webkit_type_number;
	*/
	script_time(); // set script timer to start timing

	static $a_full_assoc_data, $a_khtml_data, $a_mobile_data, $a_moz_data, $a_engine_data, $a_trident_data, $a_webkit_data, $b_dom_browser, $b_repeat, $b_safe_browser, $browser_name, $browser_number, $browser_math_number, $browser_user_agent, $browser_working, $html_type, $ie_version, $khtml_type, $khtml_type_number, $mobile_test, $moz_type_number, $moz_rv, $moz_rv_full, $moz_release_date, $moz_type, $os_number, $os_type, $layout_engine, $layout_engine_nu, $layout_engine_nu_full, $trident_type, $trident_type_number, $true_ie_number, $ua_type, $webkit_type, $webkit_type_number;

	// switch off the optimization for external ua string testing.
	if ( $external_ua_string ) {
		$b_repeat = false;
	}

	/*
	this makes the test only run once no matter how many times you call it since
	all the variables are filled on the first run through, it's only a matter of
	returning the the right ones
	*/
	if ( !$b_repeat ) {
		//initialize all variables with default values to prevent error
		$a_browser_math_number = '';
		$a_full_assoc_data = '';
		$a_full_data = '';
		$a_khtml_data = '';
		$a_mobile_data = '';
		$a_moz_data = '';
		$a_os_data = '';
		$a_trident_data = '';
		$a_unhandled_browser = '';
		$a_webkit_data = '';
		$b_dom_browser = false;
		$b_os_test = true;
		$b_mobile_test = true;
		$b_safe_browser = false;
		$b_success = false;// boolean for if browser found in main test
		$browser_math_number = '';
		$browser_temp = '';
		$browser_working = '';
		$browser_number = '';
		$html_type = '';
		$html_type_browser_nu = '';
		$ie_version = '';
		$layout_engine = '';
		$layout_engine_nu = '';
		$layout_engine_nu_full = '';
		$khtml_type = '';
		$khtml_type_number = '';
		$mobile_test = '';
		$moz_release_date = '';
		$moz_rv = '';
		$moz_rv_full = '';
		$moz_type = '';
		$moz_type_number = '';
		$os_number = '';
		$os_type = '';
		$run_time = '';
		$trident_type = '';
		$trident_type_number = '';
		$true_ie_number = '';
		$ua_type = 'bot';// default to bot since you never know with bots
		$webkit_type = '';
		$webkit_type_number = '';

		// set the excludes if required
		if ( $test_excludes ) {
			switch ( $test_excludes ) {
				case '1':
					$b_os_test = false;
					break;
				case '2':
					$b_mobile_test = false;
					break;
				case '3':
					$b_os_test = false;
					$b_mobile_test = false;
					break;
				default:
					die( 'Error: bad $test_excludes parameter 2 used: ' . $test_excludes );
					break;
			}
		}

		/*
		make navigator user agent string lower case to make sure all versions get caught
		isset protects against blank user agent failure. tolower also lets the script use
		strstr instead of stristr, which drops overhead slightly.
		*/
		if ( $external_ua_string ) {
			$browser_user_agent = strtolower( $external_ua_string );
		}
		elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$browser_user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
		}
		else {
			$browser_user_agent = '';
		}

		/*
		pack the browser type array, in this order
		the order is important, because opera must be tested first, then omniweb [which has safari
		data in string], same for konqueror, then safari, then gecko, since safari navigator user
		agent id's with 'gecko' in string.
		Note that $b_dom_browser is set for all  modern dom browsers, this gives you a default to use.

		array[0] = id string for useragent, array[1] is if dom capable, array[2] is working name
		for browser, array[3] identifies navigator useragent type

		Note: all browser strings are in lower case to match the strtolower output, this avoids
		possible detection errors

		Note: These are the navigator user agent types:
		bro - modern, css supporting browser.
		bbro - basic browser, text only, table only, defective css implementation
		bot - search type spider
		dow - known download agent
		lib - standard http libraries
		mobile - handheld or mobile browser, set using $mobile_test
		*/
		// known browsers, list will be updated routinely, check back now and then
		$a_browser_types = array(
		array( 'msie', true, 'ie', 'bro' ),
		array( 'trident', true, 'ie', 'bro' ),
		// opera blink: OPR/<number>, no opera in ua
		array( 'opr/', true, 'op', 'bro' ),
		// webkit before gecko because some webkit ua strings say: like gecko
		// and before khtml because some still use khtml with webkit
		array( 'webkit', true, 'webkit', 'bro' ),
		# note: 2013 sees opera moving to webkit, so needs to go after webkit
		array( 'opera', true, 'op', 'bro' ),
		// konq seems to be sticking with khtml still
		array( 'khtml', true, 'khtml', 'bro' ),
		// covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
		array( 'gecko', true, 'moz', 'bro' ),
		array( 'netpositive', false, 'netp', 'bbro' ),// beos browser
		array( 'lynx', false, 'lynx', 'bbro' ), // command line browser
		array( 'elinks ', false, 'elinks', 'bbro' ), // new version of links
		array( 'elinks', false, 'elinks', 'bbro' ), // alternate id for it
		array( 'links2', false, 'links2', 'bbro' ), // alternate links version
		array( 'links ', false, 'links', 'bbro' ), // old name for links
		array( 'links', false, 'links', 'bbro' ), // alternate id for it
		array( 'w3m', false, 'w3m', 'bbro' ), // open source browser, more features than lynx/links
		array( 'webtv', false, 'webtv', 'bbro' ),// junk ms webtv
		array( 'amaya', false, 'amaya', 'bbro' ),// w3c browser
		array( 'dillo', false, 'dillo', 'bbro' ),// linux browser, basic table support
		array( 'ibrowse', false, 'ibrowse', 'bbro' ),// amiga browser
		array( 'icab', false, 'icab', 'bro' ),// mac browser
		array( 'crazy browser', true, 'ie', 'bro' ),// uses ie rendering engine

		// search engine spider bots, primary:
		array( 'answerbus', false, 'answerbus', 'bot' ),// http://www.answerbus.com/, web questions
		array( 'ask jeeves', false, 'ask', 'bot' ), //jeeves/teoma
		array( 'teoma', false, 'ask', 'bot' ),//jeeves teoma - leave in this order
		array( 'baiduspider', false, 'baidu', 'bot' ),// Baiduspider asian search spider
		array( 'bingbot', false, 'bing', 'bot' ),// bing
		array( 'boitho.com-dc', false, 'boitho', 'bot' ),//norwegian search engine
		array( 'exabot', false, 'exabot', 'bot' ),// exabot
		array( 'fast-webcrawler', false, 'fast', 'bot' ),// Fast AllTheWeb
		array( 'ia_archiver', false, 'ia_archiver', 'bot' ),// ia archiver
		array( 'googlebot', false, 'google', 'bot' ),// google
		array( 'google web preview', false, 'googlewp', 'bot' ),// google preview
		array( 'mediapartners-google', false, 'adsense', 'bot' ),// google adsense
		array( 'msnbot', false, 'msn', 'bot' ),// msn search
		array( 'objectssearch', false, 'objectsearch', 'bot' ),// open source search engine
		array( 'scooter', false, 'scooter', 'bot' ),// altavista
		// leave the yahoo/slurp bots in this order to get right detections
		array( 'yahoo-verticalcrawler', false, 'yahoo', 'bot' ),// old yahoo bot
		array( 'yahoo! slurp', false, 'yahoo', 'bot' ), // new yahoo bot
		array( 'yahoo-mm', false, 'yahoomm', 'bot' ), // gets Yahoo-MMCrawler and Yahoo-MMAudVid bots
		array( 'inktomi', false, 'inktomi', 'bot' ), // inktomi bot
		array( 'slurp', false, 'inktomi', 'bot' ), // inktomi bot
		array( 'zyborg', false, 'looksmart', 'bot' ),// looksmart

		// misc bots
		array( 'almaden', false, 'ibm', 'bot' ),// ibm almaden web crawler
		array( 'comodospider', false, 'comodospider', 'bot' ),
		array( 'gigabot', false, 'gigabot', 'bot' ),// gigabot crawler
		array( 'iltrovatore-setaccio', false, 'il-set', 'bot' ),
		array( 'lexxebotr', false, 'lexxebotr', 'bot' ),
		array( 'magpie-crawlero', false, 'magpie-crawler', 'bot' ),
		array( 'naverbot', false, 'naverbot', 'bot' ),// naverbot crawler, bad bot, block
		array( 'omgilibot', false, 'omgilibot', 'bot' ),
		array( 'openbot', false, 'openbot', 'bot' ),// openbot, from taiwan
		array( 'psbot', false, 'psbot', 'bot' ),// psbot image crawler
		array( 'sogou', false, 'sogou', 'bot' ), // asian bot
		array( 'sosospider', false, 'sosospider', 'bot' ), // http://help.soso.com/webspider.htm
		array( 'sohu-search', false, 'sohu', 'bot' ),// chinese media company, search component
		array( 'surveybot', false, 'surveybot', 'bot' ),//
		array( 'vbseo', false, 'vbseo', 'bot' ),

		// various http utility libaries
		array( 'w3c_validator', false, 'w3c', 'lib' ), // uses libperl, make first
		array( 'wdg_validator', false, 'wdg', 'lib' ), //
		array( 'libwww-perl', false, 'libwww-perl', 'lib' ),
		array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' ),
		array( 'python-urllib', false, 'python-urllib', 'lib' ),

		// download apps
		array( 'getright', false, 'getright', 'dow' ),
		array( 'wget', false, 'wget', 'dow' ),// open source downloader, obeys robots.txt

		// netscape 4 and earlier tests, put last so spiders don't get caught
		array( 'mozilla/4.', false, 'ns', 'bbro' ),
		array( 'mozilla/3.', false, 'ns', 'bbro' ),
		array( 'mozilla/2.', false, 'ns', 'bbro' )
		);

		//array( '', false ); // browser array template
		// note: not using this because chrome < 28 = webkit, >=28 == blink, so can't do normal handling
		// for now doing a case by case for layout engine
		$a_blink_types = array('chrome', 'opr/');
		/*
		moz types array
		note the order, netscape6 must come before netscape, which  is how netscape 7 id's itself.
		rv comes last in case it is plain old mozilla. firefox/netscape/seamonkey need to be later
		Thanks to: http://www.zytrax.com/tech/web/firefox-history.html
		*/
		$a_gecko_types = array( 'bonecho', 'camino', 'conkeror', 'epiphany', 'fennec', 'firebird', 'flock', 'galeon', 'iceape', 'icecat', 'k-meleon', 'minimo', 'multizilla', 'phoenix', 'skyfire', 'songbird', 'swiftfox', 'seamonkey', 'shadowfox', 'shiretoko', 'iceweasel', 'firefox', 'minefield', 'netscape6', 'netscape', 'rv' );

		$a_khtml_types = array( 'konqueror', 'khtml' );

		$a_trident_types = array( 'ucbrowser', 'ucweb', 'msie' );

		/*
		webkit types, this is going to expand over time as webkit browsers spread
		konqueror is probably going to move to webkit, so this is preparing for that
		It will now default to khtml. gtklauncher is the temp id for epiphany, might
		change. Defaults to applewebkit, and will all show the webkit number.
		uc browsers are webkit need to be before safari; puffin before chrome
		*/
		$a_webkit_types = array( 'arora', 'bolt', 'beamrise', 'chromium', 'puffin', 'chrome', 'crios', 'dooble', 'epiphany', 'gtklauncher', 'icab', 'konqueror', 'maxthon',  'midori', 'omniweb', 'opera', 'qupzilla', 'rekonq', 'rocketmelt', 'silk', 'uzbl', 'ucbrowser', 'ucweb', 'shiira', 'sputnik', 'steel', 'teashark', 'safari',  'applewebkit', 'webos', 'xxxterm', 'webkit' );

		/*
		run through the browser_types array, break if you hit a match, if no match, assume old browser
		or non dom browser, assigns false value to $b_success.
		*/
		$i_count = count( $a_browser_types );
		for ( $i = 0; $i < $i_count; $i++ ) {
			//unpacks browser array, assigns to variables, need to not assign til found in string
			$browser_temp = $a_browser_types[$i][0];// text string to id browser from array

			if ( strstr( $browser_user_agent, $browser_temp ) ) {
				/*
				it defaults to true, will become false below if needed
				this keeps it easier to keep track of what is safe, only
				explicit false assignment will make it false.
				*/
				$b_safe_browser = true;
				$browser_name = $browser_temp;// text string to id browser from array

				// assign values based on match of user agent string
				$b_dom_browser = $a_browser_types[$i][1];// hardcoded dom support from array
				$browser_working = $a_browser_types[$i][2];// working name for browser
				$ua_type = $a_browser_types[$i][3];// sets whether bot or browser

				switch ( $browser_working ) {
					// this is modified quite a bit, now will return proper netscape version number
					// check your implementation to make sure it works
					case 'ns':
						$b_safe_browser = false;
						$browser_number = get_item_version( $browser_user_agent, 'mozilla' );
						break;
					case 'khtml':
						// note that this is the KHTML version number
						$browser_number = get_item_version( $browser_user_agent, $browser_name );
						// assign rendering engine data
						$layout_engine = 'khtml';
						$layout_engine_nu = get_item_math_number( $browser_number );
						$layout_engine_nu_full = $browser_number;

						// this is to pull out specific khtml versions, konqueror
						$j_count = count( $a_khtml_types );
						for ( $j = 0; $j < $j_count; $j++ ) {
							if ( strstr( $browser_user_agent, $a_khtml_types[$j] ) ) {
								$khtml_type = $a_khtml_types[$j];
								$khtml_type_number = get_item_version( $browser_user_agent, $khtml_type );
								$browser_name = $a_khtml_types[$j];
								$browser_number = get_item_version( $browser_user_agent, $browser_name );
								break;
							}
						}

						break;
					case 'moz':
						/*
						note: The 'rv' test is not absolute since the rv number is very different on
						different versions, for example Galean doesn't use the same rv version as Mozilla,
						neither do later Netscapes, like 7.x. For more on this, read the full mozilla
						numbering conventions here: http://www.mozilla.org/releases/cvstags.html
						*/
						// this will return alpha and beta version numbers, if present
						get_set_count( 'set', 0 );
						$moz_rv_full = get_item_version( $browser_user_agent, 'rv:' );
						// this slices them back off for math comparisons
						$moz_rv = floatval( $moz_rv_full );

						// this is to pull out specific mozilla versions, firebird, netscape etc..
						$j_count = count( $a_gecko_types );
						for ( $j = 0; $j < $j_count; $j++ ) {
							if ( strstr( $browser_user_agent, $a_gecko_types[$j] ) ) {
								$moz_type = $a_gecko_types[$j];
								$moz_type_number = get_item_version( $browser_user_agent, $moz_type );
								break;
							}
						}
						/*
						this is necesary to protect against false id'ed moz'es and new moz'es.
						this corrects for galeon, or any other moz browser without an rv number
						*/
						if ( !$moz_rv ) {
							// you can use this if you are running php >= 4.2
							$moz_rv = floatval( $moz_type_number );
							$moz_rv_full = $moz_type_number;
						}
						// this corrects the version name in case it went to the default 'rv' for the test
						if ( $moz_type == 'rv' ) {
							$moz_type = 'mozilla';
						}
						//the moz version will be taken from the rv number, see notes above for rv problems
						$browser_number = $moz_rv;
						// gets the actual release date, necessary if you need to do functionality tests
						get_set_count( 'set', 0 );
						$moz_release_date = get_item_version( $browser_user_agent, 'gecko/' );
						// assign rendering engine data
						$layout_engine = 'gecko';
						$layout_engine_nu = $moz_rv;
						$layout_engine_nu_full = $moz_rv_full;
						/*
						Test for mozilla 0.9.x / netscape 6.x
						test your javascript/CSS to see if it works in these mozilla releases, if it
						does, just default it to: $b_safe_browser = true;
						*/
						if ( ( $moz_release_date < 20020400 ) || ( $moz_rv < 1 ) ) {
							$b_safe_browser = false;
						}
						break;
					case 'ie':
						$b_gecko_ua = false;
						/*
						note we're adding in the trident/ search to return only first instance in case
						of msie 8, and we're triggering the  break last condition in the test, as well
						as the test for a second search string, trident/
						Sample: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0)

						Handle the new msie 11 ua syntax (search for rv:), sample:
						Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko
						so assign msie value back here
						*/
						if ( strstr($browser_user_agent, 'rv:' ) ) {
							$browser_name = 'msie';
							$b_gecko_ua = true;
							get_set_count( 'set', 0 );
							$browser_number = get_item_version( $browser_user_agent, 'rv:', '', '' );
						}
						else {
							$browser_number = get_item_version( $browser_user_agent, $browser_name, true, 'trident/' );

						}
						get_set_count( 'set', 0 );

						$layout_engine_nu_full = get_item_version( $browser_user_agent, 'trident/', '', '' );
						// construct the proper real number. For example, trident 4 is msie 8
						if ( $layout_engine_nu_full  ) {
							$layout_engine_nu = get_item_math_number( $layout_engine_nu_full );
							$layout_engine = 'trident';
							// in compat mode, browser shows as msie 7, for now, check in future msie
							// versions. Note that this isn't used in new gecko type ua, so no compat mode switch
							if ( strstr( $browser_number, '7.' ) && !$b_gecko_ua ) {
								$true_ie_number = get_item_math_number( $browser_number ) + ( intval( $layout_engine_nu ) - 3 );
							}
							else {
								$true_ie_number = $browser_number;
							}
							// this is to pull out specific trident versions, ucbrowser, etc..
							$j_count = count( $a_trident_types );
							for ( $j = 0; $j < $j_count; $j++ ) {
								if ( strstr( $browser_user_agent, $a_trident_types[$j] ) ) {
									$trident_type = $a_trident_types[$j];
									$trident_type_number = get_item_version( $browser_user_agent, $trident_type );
									break;
								}
							}
							// note the string msie does not appear in gecko type msie useragents
							if ( !$trident_type && $b_gecko_ua ) {
								$trident_type = 'msie';
								$trident_type_number = $browser_number;
							}
						}
						// note: trident is engine from ie 4 onwards, but only shows after ie 8
						// but msie 7 is trident 3.1, and no trident numbers are known for earlier
						elseif ( intval( $browser_number ) <= 7 && intval( $browser_number ) >= 4 ) {
							$layout_engine = 'trident';
							if ( intval( $browser_number ) == 7 ) {
								$layout_engine_nu_full = '3.1';
								$layout_engine_nu = '3.1';
							}
						}

						// the 9 series is finally standards compatible, html 5 etc, so worth a new id
						if ( $browser_number >= 9 ) {
							$ie_version = 'ie9x';
						}
						// 7/8 were not yet quite to standards levels but getting there
						elseif ( $browser_number >= 7 ) {
							$ie_version = 'ie7x';
						}
						// then test for IE 5x mac, that's the most problematic IE out there
						elseif ( strstr( $browser_user_agent, 'mac') ) {
							$ie_version = 'ieMac';
						}
						// ie 5/6 are both very weak in standards compliance
						elseif ( $browser_number >= 5 ) {
							$ie_version = 'ie5x';
						}
						elseif ( ( $browser_number > 3 ) && ( $browser_number < 5 ) ) {
							$b_dom_browser = false;
							$ie_version = 'ie4';
							// this depends on what you're using the script for, make sure this fits your needs
							$b_safe_browser = true;
						}
						else {
							$ie_version = 'old';
							$b_dom_browser = false;
							$b_safe_browser = false;
						}
						break;
					case 'op':
						if ( $browser_name == 'opr/' ) {
							$browser_name = 'opr';
						}
						$browser_number = get_item_version( $browser_user_agent, $browser_name );
						// opera is leaving version at 9.80 (or xx) for 10.x - see this for explanation
						// http://dev.opera.com/articles/view/opera-ua-string-changes/
						if ( strstr( $browser_number, '9.' )
						     && strstr( $browser_user_agent, 'version/' ) ) {
							get_set_count( 'set', 0 );
							$browser_number = get_item_version( $browser_user_agent, 'version/' );
						}
						get_set_count( 'set', 0 );
						$layout_engine_nu_full = get_item_version( $browser_user_agent, 'presto/' );
						if ( $layout_engine_nu_full ) {
							$layout_engine = 'presto';
							$layout_engine_nu = get_item_math_number( $layout_engine_nu_full );
						}
						if ( ! $layout_engine_nu_full && $browser_name == 'opr' ) {
							if ( strstr($browser_user_agent, 'blink') ) {
								$layout_engine_nu_full = get_item_version( $browser_user_agent, 'blink' );
							}
							else {
								$layout_engine_nu_full = get_item_version( $browser_user_agent, 'webkit' );
							}
							$layout_engine_nu = get_item_math_number( $layout_engine_nu_full );
							// assign rendering engine data
							$layout_engine = 'blink';
							$browser_name = 'opera';
						}
						// opera 4 wasn't very useable.
						if ( $browser_number < 5 ) {
							$b_safe_browser = false;
						}

						break;
					/*
					note: webkit returns always the webkit version number, not the specific user
					agent version, ie, webkit 583, not chrome 0.3
					*/
					case 'webkit':
						// note that this is the Webkit version number
						$browser_number = get_item_version( $browser_user_agent, $browser_name );
						// assign rendering engine data
						$layout_engine = 'webkit';
						$layout_engine_nu = get_item_math_number( $browser_number );
						$layout_engine_nu_full = $browser_number;
						// this is to pull out specific webkit versions, safari, google-chrome etc..
						$j_count = count( $a_webkit_types );
						for ( $j = 0; $j < $j_count; $j++ ) {
							if ( strstr( $browser_user_agent, $a_webkit_types[$j] ) ) {
								$webkit_type = $a_webkit_types[$j];
								// fixes a glitch: new safaris uses version/x.x.x for the safari number
								// however because safari number is NOT the same as webkit number, going
								// to keep returning the safari number, not the version/ number.
								/*
								if ( $a_webkit_types[$j] == 'safari'
								     && strstr( $browser_user_agent, 'version/' ) ) {
									get_set_count( 'set', 0 );
									$webkit_type_number = get_item_version( $browser_user_agent, 'version/' );
								}
								else {
								*/
								/*
								and this is the webkit type version number, like: chrome 1.2
								if omni web, we want the count 2, not default 1
								*/
								if ( $webkit_type == 'omniweb' ) {
								  get_set_count( 'set', 2 );
								}
								$webkit_type_number = get_item_version( $browser_user_agent, $webkit_type );

								// }
								// epiphany hack
								if ( $a_webkit_types[$j] == 'gtklauncher' ) {
									$browser_name = 'epiphany';
								}
								else {
									$browser_name = $a_webkit_types[$j];
								}
								if ( $a_webkit_types[$j] == 'chrome' && get_item_math_number( $webkit_type_number ) >= 28  ) {
									if ( strstr($browser_user_agent, 'blink') ) {
										$layout_engine_nu_full = get_item_version( $browser_user_agent, 'blink' );
										$layout_engine_nu = get_item_math_number( $layout_engine_nu_full );
									}
									// assign rendering engine data
									$layout_engine = 'blink';
								}
								$browser_number = get_item_version( $browser_user_agent, $browser_name );
								break;
							}
						}
						break;
					default:
						$browser_number = get_item_version( $browser_user_agent, $browser_name );
						break;
				}
				// the browser was id'ed
				$b_success = true;
				break;
			}
		}

		//assigns defaults if the browser was not found in the loop test
		if ( !$b_success ) {
			/*
			this will return the first part of the browser string if the above id's failed
			usually the first part of the browser string has the navigator useragent name/version in it.
			This will usually correctly id the browser and the browser number if it didn't get
			caught by the above routine.
			If you want a '' to do a if browser == '' type test, just comment out all lines below
			except for the last line, and uncomment the last line. If you want undefined values,
			the browser_name is '', you can always test for that
			*/
			// delete this part if you want an unknown browser returned
			$browser_name = substr( $browser_user_agent, 0, strcspn( $browser_user_agent , '();') );
			// this extracts just the browser name from the string, if something usable was found
			if ( $browser_name
			     && preg_match( '/[^0-9][a-z]*-*\ *[a-z]*\ *[a-z]*/', $browser_name, $a_unhandled_browser ) ) {
				$browser_name = $a_unhandled_browser[0];

				if ( $browser_name == 'blackberry' ) {
					get_set_count( 'set', 0 );
				}
				$browser_number = get_item_version( $browser_user_agent, $browser_name );
			}
			else {
				$browser_name = 'NA';
				$browser_number = 'NA';
			}

			// then uncomment this part
			//$browser_name = '';//deletes the last array item in case the browser was not a match
		}
		// get os data, mac os x test requires browser/version information, this is a change from older scripts
		if ( $b_os_test ) {
			$a_os_data = get_os_data( $browser_user_agent, $browser_working, $browser_number );
			$os_type = $a_os_data[0];// os name, abbreviated
			$os_number = $a_os_data[1];// os number or version if available
		}
		/*
		this ends the run through once if clause, set the boolean
		to true so the function won't retest everything
		*/
		$b_repeat = true;
		/*
		pulls out primary version number from more complex string, like 7.5a,
		use this for numeric version comparison
		*/
		$browser_math_number = get_item_math_number( $browser_number );

		if ( $b_mobile_test ) {
			$mobile_test = check_is_mobile( $browser_user_agent );
			if ( $mobile_test ) {
				$a_mobile_data = get_mobile_data( $browser_user_agent );
				$ua_type = 'mobile';
			}
		}
	}
	//$browser_number = $_SERVER["REMOTE_ADDR"];
	/*
	This is where you return values based on what parameter you used to call the function
	$which_test is the passed parameter in the initial browser_detection('os') for example returns
	the os version only.

	Update deprecated parameter names to new names
	*/
	switch ( $which_test ) {
		case 'math_number':
			$which_test = 'browser_math_number';
			break;
		case 'number':
			$which_test = 'browser_number';
			break;
		case 'browser':
			$which_test = 'browser_working';
			break;
		case 'moz_version':
			$which_test = 'moz_data';
			break;
		case 'true_msie_version':
			$which_test = 'true_ie_number';
			break;
		case 'type':
			$which_test = 'ua_type';
			break;
		case 'webkit_version':
			$which_test = 'webkit_data';
			break;
	}
	/*
	assemble these first so they can be included in full return data, using static variables
	Note that there's no need to keep repacking these every time the script is called
	*/

	if ( !$a_engine_data ) {
		$a_engine_data = array( $layout_engine, $layout_engine_nu_full, $layout_engine_nu );
	}
	if ( !$a_khtml_data ) {
		$a_khtml_data = array( $khtml_type, $khtml_type_number, $browser_number );
	}
	if ( !$a_moz_data ) {
		$a_moz_data = array( $moz_type, $moz_type_number, $moz_rv, $moz_rv_full, $moz_release_date );
	}
	if ( !$a_webkit_data ) {
		$a_webkit_data = array( $webkit_type, $webkit_type_number, $browser_number );
	}
	if ( !$a_trident_data ) {
		$a_trident_data = array( $trident_type, $trident_type_number, $layout_engine_nu, $browser_number );
	}

	$run_time = script_time();
	// now send the actual engine number to the html type function
	if ( $layout_engine_nu ) {
		$html_type = get_html_level( $layout_engine, $layout_engine_nu );
	}
	// then pack the primary data array
	if ( !$a_full_assoc_data ) {
		$a_full_assoc_data = array(
			'browser_working' => $browser_working,
			'browser_number' => $browser_number,
			'ie_version' => $ie_version,
			'dom' => $b_dom_browser,
			'safe' => $b_safe_browser,
			'os' => $os_type,
			'os_number' => $os_number,
			'browser_name' => $browser_name,
			'ua_type' => $ua_type,
			'browser_math_number' => $browser_math_number,
			'moz_data' => $a_moz_data,
			'webkit_data' => $a_webkit_data,
			'mobile_test' => $mobile_test,
			'mobile_data' => $a_mobile_data,
			'true_ie_number' => $true_ie_number,
			'run_time' => $run_time,
			'html_type' => $html_type,
			'engine_data' => $a_engine_data,
			'trident_data' => $a_trident_data
		);
	}
	// return parameters, either full data arrays, or by associative array index key
	switch ( $which_test ) {
		// returns all relevant browser information in an array with standard numeric indexes
		case 'full':
			$a_full_data = array(
				$browser_working,
				$browser_number,
				$ie_version,
				$b_dom_browser,
				$b_safe_browser,
				$os_type,
				$os_number,
				$browser_name,
				$ua_type,
				$browser_math_number,
				$a_moz_data,
				$a_webkit_data,
				$mobile_test,
				$a_mobile_data,
				$true_ie_number,
				$run_time,
				$html_type,
				$a_engine_data,
				$a_trident_data
			);
			// print_r( $a_full_data );
			return $a_full_data;
			break;
		// returns all relevant browser information in an associative array
		case 'full_assoc':
			return $a_full_assoc_data;
			break;
		case 'header_data':
			// check for xhtml support, note that this test can only run if headers have
			// NOT already been sent.
			if ( !headers_sent() ) {
				if ( stristr( $_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ) {

				}
				else {

				}
			}
			break;
		default:
			# check to see if the data is available, otherwise it's user typo of unsupported option
			if ( isset( $a_full_assoc_data[$which_test] ) ) {
				return $a_full_assoc_data[$which_test];
			}
			else {
				die( "You passed the browser detector an unsupported option for parameter 1: " . $which_test );
			}
			break;
	}
}

function get_item_math_number( $pv_browser_number )
{
	$browser_math_number = '';
	if ( $pv_browser_number
		   && preg_match( '/^[0-9]*\.*[0-9]*/', $pv_browser_number, $a_browser_math_number ) ) {
		$browser_math_number = $a_browser_math_number[0];
		//print_r($a_browser_math_number);
	}
	return $browser_math_number;
}

// gets which os from the browser string
function get_os_data ( $pv_browser_string, $pv_browser_name, $pv_version_number  )
{
	// initialize variables
	$os_working_type = '';
	$os_working_number = '';
	/*
	packs the os array. Use this order since some navigator user agents will put 'macintosh'
	in the navigator user agent string which would make the nt test register true
	*/
	$a_mac = array( 'intel mac', 'OS X', 'ppc mac', 'mac68k' );// this is not used currently
	// same logic, check in order to catch the os's in order, last is always default item
	$a_unix_types = array( 'dragonfly', 'freebsd', 'openbsd', 'netbsd', 'bsd', 'unixware', 'solaris', 'sunos', 'sun4', 'sun5', 'suni86', 'sun', 'irix5', 'irix6', 'irix', 'hpux9', 'hpux10', 'hpux11', 'hpux', 'hp-ux', 'aix1', 'aix2', 'aix3', 'aix4', 'aix5', 'aix', 'sco', 'unixware', 'mpras', 'reliant', 'dec', 'sinix', 'unix' );
	// only sometimes will you get a linux distro to id itself...
	$a_linux_distros = array( ' cros ', 'ubuntu', 'kubuntu', 'xubuntu', 'mepis', 'xandros', 'linspire', 'winspire', 'jolicloud', 'sidux', 'kanotix', 'debian', 'opensuse', 'suse', 'fedora', 'redhat', 'slackware', 'slax', 'mandrake', 'mandriva', 'gentoo', 'sabayon', 'linux' );
	$a_linux_process = array ( 'i386', 'i586', 'i686', 'x86_64' );// not use currently
	// note, order of os very important in os array, you will get failed ids if changed
	$a_os_types = array( 'android', 'blackberry', 'iphone', 'palmos', 'palmsource', 'symbian', 'beos', 'os2', 'amiga', 'webtv', 'macintosh', 'mac_', 'mac ', 'nt', 'win', $a_unix_types, $a_linux_distros );

	//os tester
	$i_count = count( $a_os_types );
	for ( $i = 0; $i < $i_count; $i++ ) {
		// unpacks os array, assigns to variable $a_os_working
		$os_working_data = $a_os_types[$i];
		/*
		assign os to global os variable, os flag true on success
		!strstr($pv_browser_string, "linux" ) corrects a linux detection bug
		*/
		if ( !is_array( $os_working_data )
		     && strstr( $pv_browser_string, $os_working_data )
		     && !strstr( $pv_browser_string, "linux" ) ) {
			$os_working_type = $os_working_data;

			switch ( $os_working_type ) {
				// most windows now uses: NT X.Y syntax
				case 'nt':
					// This returns either a number, like 3, or 5.1. It does not
					// return any alpha/beta type data for the os version.
					preg_match ( '/nt ([0-9]+[\.]?[0-9]?)/', $pv_browser_string, $a_nt_matches );
					if ( isset( $a_nt_matches[1] ) ) {
						$os_working_number = $a_nt_matches[1];
					}
					break;
				case 'win':
					// windows vista, for opera ID
					if ( strstr( $pv_browser_string, 'vista' ) ) {
						$os_working_number = 6.0;
						$os_working_type = 'nt';
					}
					// windows xp, for opera ID
					elseif ( strstr( $pv_browser_string, 'xp' ) ) {
						$os_working_number = 5.1;
						$os_working_type = 'nt';
					}
					// windows server 2003, for opera ID
					elseif ( strstr( $pv_browser_string, '2003' ) ) {
						$os_working_number = 5.2;
						$os_working_type = 'nt';
					}
					// windows CE
					elseif ( strstr( $pv_browser_string, 'windows ce' ) ) {
						$os_working_number = 'ce';
						$os_working_type = 'nt';
					}
					elseif ( strstr( $pv_browser_string, '95' ) ) {
						$os_working_number = '95';
					}
					elseif ( ( strstr( $pv_browser_string, '9x 4.9' ) )
					     || ( strstr( $pv_browser_string, ' me' ) ) ) {
						$os_working_number = 'me';
					}
					elseif ( strstr( $pv_browser_string, '98' ) ) {
						$os_working_number = '98';
					}
					// windows 2000, for opera ID
					elseif ( strstr( $pv_browser_string, '2000' ) ) {
						$os_working_number = 5.0;
						$os_working_type = 'nt';
					}
					break;
				case 'mac ':
				case 'mac_':
				case 'macintosh':
					$os_working_type = 'mac';
					if ( strstr( $pv_browser_string, 'os x' ) ) {
						// if it doesn't have a version number, it is os x;
						if ( strstr( $pv_browser_string, 'os x ' ) ) {
							// numbers are like: 10_2.4, others 10.2.4
							$os_working_number = str_replace( '_', '.', get_item_version( $pv_browser_string, 'os x' ) );
						}
						else {
							$os_working_number = 10;
						}
					}
					/*
					this is a crude test for os x, since safari, camino, ie 5.2, & moz >= rv 1.3
					are only made for os x
					*/
					elseif ( $pv_browser_name == 'saf'
					         || $pv_browser_name == 'cam'
					         || ( ( $pv_browser_name == 'moz' ) && ( $pv_version_number >= 1.3 ) )
					         || ( ( $pv_browser_name == 'ie' ) && ( $pv_version_number >= 5.2 ) ) ) {
						$os_working_number = 10;
					}
					break;
				case 'iphone':
					$os_working_number = 10;
					break;
				default:
					break;
			}
			break;
		}
		/*
		check that it's an array, check it's the second to last item
		in the main os array, the unix one that is
		*/
		elseif ( is_array( $os_working_data ) && ( $i == ( $i_count - 2 ) ) ) {
			$j_count = count($os_working_data);
			for ($j = 0; $j < $j_count; $j++) {
				if ( strstr( $pv_browser_string, $os_working_data[$j] ) ) {
					$os_working_type = 'unix'; //if the os is in the unix array, it's unix, obviously...
					$os_working_number = ( $os_working_data[$j] != 'unix' ) ? $os_working_data[$j] : '';// assign sub unix version from the unix array
					break;
				}
			}
		}
		/*
		check that it's an array, check it's the last item
		in the main os array, the linux one that is
		*/
		elseif ( is_array( $os_working_data ) && ( $i == ( $i_count - 1 ) ) ) {
			$j_count = count($os_working_data);
			for ($j = 0; $j < $j_count; $j++) {
				if ( strstr( $pv_browser_string, $os_working_data[$j] ) ) {
					$os_working_type = 'lin';
					// assign linux distro from the linux array, there's a default
					//search for 'lin', if it's that, set version to ''
					$os_working_number = ( $os_working_data[$j] != 'linux' ) ? $os_working_data[$j] : '';
					break;
				}
			}
		}
	}

	// pack the os data array for return to main function
	$a_os_data = array( $os_working_type, $os_working_number );

	return $a_os_data;
}

/*
Function Info:
function returns browser number, gecko rv number, or gecko release date
function get_item_version( $browser_user_agent, $search_string, $substring_length )
$pv_extra_search='' allows us to set an additional search/exit loop parameter, but we
only want this running when needed
*/
function get_item_version( $pv_browser_user_agent, $pv_search_string, $pv_b_break_last='', $pv_extra_search='' )
{
	// 12 is the longest that will be required, handles release dates: 20020323; 0.8.0+
	$substring_length = 15;
	$start_pos = 0; // set $start_pos to 0 for first iteration
	//initialize browser number, will return '' if not found
	$string_working_number = '';
	/*
	use the passed parameter for $pv_search_string
	start the substring slice right after these moz search strings
	there are some cases of double msie id's, first in string and then with then number
	$start_pos = 0;
	this test covers you for multiple occurrences of string, only with ie though
	with for example google bot you want the first occurance returned, since that's where the
	numbering happens
	*/
	for ( $i = 0; $i < 4; $i++ ) {
		//start the search after the first string occurrence
		if ( strpos( $pv_browser_user_agent, $pv_search_string, $start_pos ) !== false ) {
			// update start position if position found
			$start_pos = strpos( $pv_browser_user_agent, $pv_search_string, $start_pos ) + strlen( $pv_search_string );
			/*
			msie (and maybe other userAgents requires special handling because some apps inject
			a second msie, usually at the beginning, custom modes allow breaking at first instance
			if $pv_b_break_last $pv_extra_search conditions exist. Since we only want this test
			to run if and only if we need it, it's triggered by caller passing these values.
			*/
			if ( !$pv_b_break_last
			     || ( $pv_extra_search && strstr( $pv_browser_user_agent, $pv_extra_search ) ) ) {
				break;
			}
		}
		else
		{
			break;
		}
	}
	/*
	Handles things like extra omniweb/v456, gecko/, blackberry9700
	also corrects for the omniweb 'v'
	*/
	$start_pos += get_set_count( 'get' );
	$string_working_number = substr( $pv_browser_user_agent, $start_pos, $substring_length );

	// Find the space, ;, or parentheses that ends the number
	$string_working_number = substr( $string_working_number, 0, strcspn($string_working_number, ' );/') );

	//make sure the returned value is actually the id number and not a string
	// otherwise return ''
	// strcspn( $string_working_number, '0123456789.') == strlen( $string_working_number)
	//	if ( preg_match("/\\d/", $string_working_number) == 0 )
 	if ( !is_numeric( substr( $string_working_number, 0, 1 ) ) ) {
		$string_working_number = '';
	}
	//$string_working_number = strrpos( $pv_browser_user_agent, $pv_search_string );
	return $string_working_number;
}

function get_set_count( $pv_type, $pv_value='' )
{
	static $slice_increment;
	$return_value = '';
	switch ( $pv_type ) {
		case 'get':
			// set if unset, ie, first use. note that empty and isset are not good tests here
			if ( is_null( $slice_increment ) ) {
				$slice_increment = 1;
			}
			$return_value = $slice_increment;
			$slice_increment = 1; // reset to default
			return $return_value;
			break;
		case 'set':
			$slice_increment = $pv_value;
			break;
	}
}

/*
Special ID notes:
Novarra-Vision is a Content Transformation Server (CTS)
Some interesting notes on detection of actual mobile devices
*/
function check_is_mobile( $pv_browser_user_agent )
{
	$mobile_working_test = '';
	/*
	these will search for basic mobile hints, this should catch most of them, first check
	known hand held device os, then check device names, then mobile browser names
	This list is almost the same but not exactly as the 4 arrays in function below
	*/
	$a_mobile_search = array(
	/*
	Make sure to use only data here that always will be a mobile, so this list is not
	identical to the list of get_mobile_data
	*/
	// os
	'android', 'blackberry', 'epoc', 'linux armv', 'palmos', 'palmsource', 'windows ce', 'windows phone os', 'symbianos', 'symbian os', 'symbian', 'webos',
	// devices - ipod before iphone or fails
	'benq', 'blackberry', 'danger hiptop', 'ddipocket', ' droid', 'ipad', 'ipod', 'iphone', 'kindle', 'kobo', 'lge-cx', 'lge-lx', 'lge-mx', 'lge vx', 'lge ', 'lge-', 'lg;lx', 'nexus', 'nintendo wii', 'nokia', 'nook', 'palm', 'pdxgw', 'playstation', 'rim', 'sagem', 'samsung', 'sec-sgh', 'sharp', 'sonyericsson', 'sprint', 'zune', 'j-phone', 'n410', 'mot 24', 'mot-', 'htc-', 'htc_', 'htc ', 'playbook', 'sec-', 'sie-m', 'sie-s', 'spv ', 'touchpad', 'vodaphone', 'smartphone', 'armv', 'midp', 'mobilephone',
	// browsers
	'avantgo', 'blazer', 'elaine', 'eudoraweb', 'fennec', 'iemobile',  'minimo', 'mobile safari', 'mobileexplorer', 'opera mobi', 'opera mini', 'netfront', 'opwv', 'polaris', 'puffin', 'semc-browser', 'skyfire', 'up.browser', 'ucweb', 'ucbrowser', 'webpro/', 'wms pie', 'xiino',
	// services - astel out of business
	'astel', 'docomo', 'novarra-vision', 'portalmmm', 'reqwirelessweb', 'vodafone'
	);

	// then do basic mobile type search, this uses data from: get_mobile_data()
	$j_count = count( $a_mobile_search );
	for ($j = 0; $j < $j_count; $j++) {
		if ( strstr( $pv_browser_user_agent, $a_mobile_search[$j] ) ) {
			# this handles compat/pre msie 9 mode zune embedded in ua via registry
			if ( $a_mobile_search[$j] != 'zune' || strstr( $pv_browser_user_agent, 'iemobile' ) ) {
				$mobile_working_test = $a_mobile_search[$j];
				break;
			}
		}
	}

	return $mobile_working_test;
}

/*
thanks to this page: http://www.zytrax.com/tech/web/mobile_ids.html
for data used here
*/
function get_mobile_data( $pv_browser_user_agent )
{
	$mobile_browser = '';
	$mobile_browser_number = '';
	$mobile_device = '';
	$mobile_device_number = '';
	$mobile_os = ''; // will usually be null, sorry
	$mobile_os_number = '';
	$mobile_server = '';
	$mobile_server_number = '';
	$mobile_tablet = '';

	// browsers, show it as a handheld, but is not the os
	// note: crios is actuall chrome on ios, uc need to be before safari
	$a_mobile_browser = array( 'avantgo', 'blazer', 'crios', 'elaine', 'eudoraweb', 'fennec', 'iemobile',  'minimo', 'ucweb', 'ucbrowser', 'mobile safari', 'mobileexplorer', 'opera mobi', 'opera mini', 'netfront', 'opwv', 'polaris', 'puffin', 'semc-browser', 'silk', 'steel', 'ultralight', 'up.browser', 'webos', 'webpro/', 'wms pie', 'xiino' );
	/*
	This goes from easiest to detect to hardest, so don't use this for output unless you
	clean it up more is my advice.
	Special Notes: do not include milestone in general mobile type test above, it's too generic
	Note: we can safely now test for zune because the initial test shows zune with iemobile in ua
	*/
	$a_mobile_device = array( 'benq', 'blackberry', 'danger hiptop', 'ddipocket', ' droid', 'htc_dream', 'htc espresso', 'htc hero', 'htc halo', 'htc huangshan', 'htc legend', 'htc liberty', 'htc paradise', 'htc supersonic', 'htc tattoo', 'ipad', 'ipod', 'iphone', 'kindle', 'kobo', 'lge-cx', 'lge-lx', 'lge-mx', 'lge vx', 'lg;lx', 'nexus', 'nintendo wii', 'nokia', 'nook', 'palm', 'pdxgw', 'playstation', 'sagem', 'samsung', 'sec-sgh', 'sharp', 'sonyericsson', 'sprint', 'j-phone', 'milestone', 'n410', 'mot 24', 'mot-', 'htc-', 'htc_',  'htc ', 'lge ', 'lge-', 'sec-', 'sie-m', 'sie-s', 'spv ', 'smartphone', 'armv', 'midp', 'mobilephone', 'wp', 'zunehd', 'zune'  );
	/*
	note: linux alone can't be searched for, and almost all linux devices are armv types
	ipad 'cpu os' is how the real os number is handled
	*/
	$a_mobile_os = array( 'android', 'blackberry', 'epoc', 'cpu os', 'iphone os', 'palmos', 'palmsource', 'windows phone os', 'windows ce', 'symbianos', 'symbian os', 'symbian', 'webos', 'linux armv'  );

	// sometimes there is just no other id for the unit that the CTS type service/server
	$a_mobile_server = array( 'astel', 'docomo', 'novarra-vision', 'portalmmm', 'reqwirelessweb', 'vodafone' );

	/*
	basic tablet detection. Note, android 3 was a tablet only release, android 4 is
	mobile/tablet. gt-p is samsung galaxy tablet (eg, gt-p = gt-p1000); verizon galaxy: SCH-I(xxx)
	note: android 4 is a special case, and is only a tablet if the word 'mobile' is NOT in the string.
	Rather than loop through everything we'll test this manually below and only run the loop if not found.
	NOTE that silk can only be tested for AFTER it's determined it's an android device, changed below to kindle
	*/
	$a_mobile_tablet = array( 'ipad', 'android 3', ' gt-p', 'kindle', 'kobo', 'nook', 'playbook', 'silk', 'touchpad', ' sch-i' );

	$k_count = count( $a_mobile_browser );
	for ( $k = 0; $k < $k_count; $k++ ) {
		if ( strstr( $pv_browser_user_agent, $a_mobile_browser[$k] ) ) {
			$mobile_browser = $a_mobile_browser[$k];
			// this may or may not work, highly unreliable because mobile ua strings are random
			$mobile_browser_number = get_item_version( $pv_browser_user_agent, $mobile_browser );
			break;
		}
	}
	$k_count = count( $a_mobile_device );
	for ( $k = 0; $k < $k_count; $k++ ) {
		if ( strstr( $pv_browser_user_agent, $a_mobile_device[$k] ) ) {
			$mobile_device = trim ( $a_mobile_device[$k], '-_' ); // but not space trims yet
			if ( $mobile_device == 'blackberry' ) {
				get_set_count( 'set', 0 );
			}
			$mobile_device_number = get_item_version( $pv_browser_user_agent, $mobile_device );
			$mobile_device = trim( $mobile_device ); // some of the id search strings have white space
			break;
		}
	}
	$k_count = count( $a_mobile_os );
	for ( $k = 0; $k < $k_count; $k++ ) {
		if ( strstr( $pv_browser_user_agent, $a_mobile_os[$k] ) ) {
			$mobile_os = $a_mobile_os[$k];
			if ( $mobile_os != 'blackberry' ) {
				// this may or may not work, highly unreliable
				$mobile_os_number = str_replace( '_', '.', get_item_version( $pv_browser_user_agent, $mobile_os ) );
         }
         else {
				$mobile_os_number = str_replace( '_', '.', get_item_version( $pv_browser_user_agent, 'version' ) );
				// eg: BlackBerry9000/5.0.0.93 Profile/M....
				if ( empty( $mobile_os_number ) ) {
					get_set_count( 'set', 5 );
					$mobile_os_number = str_replace( '_', '.', get_item_version( $pv_browser_user_agent, $mobile_os ) );
				}
			}
			break;
		}
	}
	$k_count = count( $a_mobile_server );
	for ( $k = 0; $k < $k_count; $k++ ) {
		if ( strstr( $pv_browser_user_agent, $a_mobile_server[$k] ) ) {
			$mobile_server = $a_mobile_server[$k];
			// this may or may not work, highly unreliable
			$mobile_server_number = get_item_version( $pv_browser_user_agent, $mobile_server );
			break;
		}
	}
	// special case, google isn't showing tablet in the UA, but if it does not say 'mobile' in the ua,
	// the device is tablet. This will probably change over time since mobile ua's are not settled.
	// using regex (?!mobile) did not work in my tests, not sure why.
	$pattern = '/android[[:space:]]*[4-9]/';
	if( preg_match( $pattern, $pv_browser_user_agent ) && !stristr($pv_browser_user_agent, 'mobile') ) {
		$mobile_tablet = 'android tablet';
	}
	else {
		$k_count = count( $a_mobile_tablet );
		for ( $k = 0; $k < $k_count; $k++ ) {
			if ( strstr( $pv_browser_user_agent, $a_mobile_tablet[$k] ) ) {
				$mobile_tablet = trim( $a_mobile_tablet[$k] );
				if ( $mobile_tablet == 'gt-p' || $mobile_tablet == 'sch-i' ) {
					$mobile_tablet = 'galaxy-' . $mobile_tablet;
				}
				elseif ( $mobile_tablet == 'silk' ) {
					$mobile_tablet = 'kindle fire';
				}
				break;
			}
		}
	}
	// just for cases where we know it's a mobile device already
	if ( !$mobile_os
	     && ( $mobile_browser || $mobile_device || $mobile_server )
	     && strstr( $pv_browser_user_agent, 'linux' ) ) {
		$mobile_os = 'linux';
		$mobile_os_number = get_item_version( $pv_browser_user_agent, 'linux' );
	}

	$a_mobile_data = array( $mobile_device, $mobile_browser, $mobile_browser_number, $mobile_os, $mobile_os_number, $mobile_server, $mobile_server_number, $mobile_device_number, $mobile_tablet );
	return $a_mobile_data;
}

function get_html_level( $pv_render_engine, $pv_render_engine_nu )
{
	$html_return = 1;
	$engine_nu = $pv_render_engine_nu;
	/*
	Until further notice, this is the primary comparison table/data used for determining
	browser support: http://en.wikipedia.org/wiki/Comparison_of_layout_engines_%28HTML5%29

	array holding start of browser support types.
	note; gecko/webkit we know about, trident is msie >= 8 , presto opera >= 10
	trident numbers are msie 8 or more number - 4; presto is just what it is for that release
	these are all multiplied by ten to avoid locale math/decimal errors below
	http://w3c-test.org/html/tests/harness/harness.htm

	NOTE: presto numbers went from 2.8 to 2.12, so you can't use this method, set to 20
	*/
	$a_html5_basic = array(
	'blink' => 10,
	'gecko' => 20,
	'khtml' => 45,
	'presto' => 20, // 26
	'trident' => 50,
	'webkit' => 5250
	);
	$a_html5_forms = array(
	'blink' => 10,
	'gecko' => 20,
	'khtml' => 50,
	'presto' => 20, // 28
	'trident' => 60,
	'webkit' => 5280
	);

	// floatval is not locale aware, so it will spit out a . type decimal separator
	// but php says that internally it should work fine as intended, ie, locale agnostic
	// floatval/locales: https://bugs.php.net/bug.php?id=40653
	$engine_nu = intval( 10 * floatval( $engine_nu ) );

	if ( array_key_exists( $pv_render_engine, $a_html5_forms )
	     && $a_html5_forms[$pv_render_engine] <= $engine_nu ) {
		$html_return = 3;
	}
	elseif ( array_key_exists( $pv_render_engine, $a_html5_basic )
	         && $a_html5_basic[$pv_render_engine] <= $engine_nu ) {
		$html_return = 2;
	}
	return $html_return;
}

// track total script execution time
function script_time()
{
	static $script_time;
	$elapsed_time = '';
	/*
	note that microtime(true) requires php 5 or greater for microtime(true)
	*/
	if ( sprintf("%01.1f", phpversion() ) >= 5 ) {
		if ( is_null( $script_time) ) {
			$script_time = microtime(true);
		}
		else {
			// note: (string)$var is same as strval($var)
			// $elapsed_time = (string)( microtime(true) - $script_time );
			$elapsed_time = ( microtime(true) - $script_time );
			$elapsed_time = sprintf("%01.8f", $elapsed_time );
			$script_time = NULL; // can't unset a static variable
			return $elapsed_time;
		}
	}
}

/*
Here are some navigator.userAgent strings so you can see where the data comes from
UserAgent Data:
Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; http://bsalsa.com) ; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30618)
Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)
Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.5) Gecko/20031007 Firebird/0.7
Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:0.9.4) Gecko/20011128 Netscape6/6.2.1
Uzbl (Webkit 1.1.17) (GNU/Linux i686 [i686]) (Commit 1958b52d41cba96956dc1995660de49525ed1047)
*/
?>