<?

//We use already made Twitter OAuth library
//https://github.com/mynetx/codebird-php
require_once ('codebird.php');

//Twitter OAuth Settings, enter your settings here:
//Create an app here: https://apps.twitter.com/
//Be sure the domain matches the one you'll be using when live!
//On the API Keys tab, obtain an API key and API secret (enter as $CONSUMER_... variables)
//On the API Keys tab, obtain an Access token and Access token secret
$CONSUMER_KEY = 'klSoUGkVOC9EBZhQthDiAg';
$CONSUMER_SECRET = '3AReaNnIIUDNzb2oZRavnKIhIGpojje4ArtEmHbqg';
$ACCESS_TOKEN = '14097461-gBsWJJGwtrvYqKn8NQP0OZNow9lzZJxbBdEAJWlmZ';
$ACCESS_TOKEN_SECRET = 'ZOvvlPAxIYukUpxGb7iWJKc6cgp89a5eHcP8lqvneo';

//Get authenticated
Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken($ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);


//retrieve posts
$q = (isset($_REQUEST['q']) && $_REQUEST['q'] !='')?$_REQUEST['q']:'';
$count = (isset($_REQUEST['count']) && $_REQUEST['count'] !='')?$_REQUEST['count']:'';
$api = (isset($_REQUEST['api']) && $_REQUEST['api'] !='')?$_REQUEST['api']:'';
$callback = (isset($_REQUEST['callback']) && $_REQUEST['callback'] !='')?$_REQUEST['callback']:'readyTweets';

//https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
//https://dev.twitter.com/docs/api/1.1/get/search/tweets
$params = array(
	'screen_name' => $q,
	'q' => $q,
	'count' => $count
);

// var_dump($cb->$api($params)); die();

//Make the REST call
$data = (array) $cb->$api($params);

if(isset($_REQUEST['callback']) && $_REQUEST['callback'] !=''){
	//Output jsonP with callback!
	echo $callback . '(' . json_encode($data) . ')';
} else{
	//Output result in JSON, getting it ready for jQuery to process
	echo json_encode($data);
}

?>