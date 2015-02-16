<?php
	/*
	*	!!! Before testing this file: Create a new directory in the same directory as this file called "cache". Inside that directory, create a file (with no extension) called "twitter-cache".
	*	Change the bearer token below to be the same as the one generated from the previous file!
	*	Change the username below to be the desired Twitter username, and change the number of tweets as needed.
	*	Default cache is 10 minutes (600), but can be changed.
	*	Either run this file directly, or trigger it via JavaScript (the next file).
	*/

	if ( file_exists('../../../../wp-load.php') ) { require_once('../../../../wp-load.php'); } //@TODO: Remove this conditional. It is only needed for the Nebula example!

	error_reporting(0); //Prevent PHP errors from being cached.


	/*** Settings ***/

	$username = 'Great_Blakes';
	$listname = 'nebula'; //Only used for list feeds
	$number_tweets = 5;
	$include_retweets = 1; //1: Yes, 0: No

	//Feed Type. Comment or delete undesired feed types.
	//$feed = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=$username&count=$number_tweets&include_rts=$include_retweets"; //Single Username Feed
	$feed = "https://api.twitter.com/1.1/lists/statuses.json?slug=$listname&owner_screen_name=$username&count=$number_tweets&include_rts=$include_retweets"; //List

	$bearer = $_GLOBALS['nebula_bearer']; //@TODO "Social" 2: Replace $nebula_bearer variable your bearer token (string)!

	$cache_file = dirname(__FILE__) . '/cache/twitter-cache';
	$interval = 600; //In seconds. Ten minutes = 600



	/*** Do not edit below this line! ***/

	$modified = filemtime($cache_file);
	$now = time();

	if ( !$modified || (($now-$modified) > $interval) ) {
		$context = stream_context_create(array(
			'http' => array(
				'method'=>'GET',
				'header'=>"Authorization: Bearer " . $bearer
			)
		));

		$json = file_get_contents($feed, false, $context);

		if ( $json ) {
			$cache_static = fopen($cache_file, 'w');
			fwrite($cache_static, $json);
			fclose($cache_static);
		}
	}

	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$json = file_get_contents($cache_file);
	echo $json;