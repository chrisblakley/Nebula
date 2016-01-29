<?php
	/*
		Request Beta access here (need a project ID from below): https://docs.google.com/forms/d/1qfRFysCikpgCMGqgF3yXdUyQW4xAlLyjKuOoOEFN2Uw/viewform
		
		Instructions:
			Go to: https://console.developers.google.com/
			Create Project
			APIs & auth > APIs: Make sure Real Time Reporting API is "ON"
			Credentials > 
			
			Full instructions here: https://developers.google.com/analytics/devguides/reporting/realtime/v3/authorization
			Test it here: https://developers.google.com/apis-explorer/#p/analytics/v3/analytics.data.realtime.get
	*/
?>

<?php
	/* Not sure if this stuff is needed yet or not
		$client = new Google_Client();
		$client->setApplicationName("My Application");
		$client->setDeveloperKey(MY_SIMPLE_API_KEY);
		
		$service = new Google_Service_Books($client);
	*/
	
	/*
$optParams = array(
		'dimensions' => 'rt:medium'
	);
	
	try {
		$results = $analytics->data_realtime->get(
			'ga:#####',
			'rt:activeUsers',
			$optParams
		);
		// Success.
		echo $results;
	} catch (apiServiceException $e) {
		// Handle API service exceptions.
		$error = $e->getMessage();
	}
*/
	
?>