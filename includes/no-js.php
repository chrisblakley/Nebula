<?php
	if ( file_exists('../../../../wp-load.php') ) {

		require_once('../../../../wp-load.php'); //@TODO "Nebula" 0: If these are being used to include separate sections of a template from independent files, then get_template_part() should be used instead.

		/*
			$_GET['h'] is home_url('/');
			$_GET['p'] is nebula_url_components('all');
			$_GET['t'] is urlencode(get_the_title($post->ID));
		*/

		ga_send_pageview($_GET['h'], $_GET['p'], $_GET['t']);
		ga_send_event('JavaScript Disabled', $_SERVER['HTTP_USER_AGENT'], $_GET['t']);

		//Parse detected User Agents here: http://udger.com/resources/online-parser

	} else {
		die('File does not exist.');
	}
?>