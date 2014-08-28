<?php
	require_once('../../../../wp-load.php'); //@TODO: If these are being used to include separate sections of a template from independent files, then get_template_part() should be used instead.
	
	$data = array(
		'v' => 1,
		'tid' => $GLOBALS['ga'],
		'cid' => gaParseCookie(),
		't' => 'pageview',
		'dh' => $_GET['h'], //Document Hostname "gearside.com"
		'dp' => $_GET['p'], //Page "/something"
		'dt' => $_GET['t'] //Title
	);
	gaSendData($data);
	
	//echo '<br/><br/>Pageview: <br/>';
	//var_dump($data);
	//echo '<br/><br/>';
	
	$data = array(
		'v' => 1,
		'tid' => $GLOBALS['ga'],
		'cid' => gaParseCookie(),
		't' => 'event',
		'ec' => 'JavaScript Disabled', //Category (Required)
		'ea' => $_GET['t'], //Action (Required)
		//'el' => 'label' //Label (browser info here)
	);
	gaSendData($data);
	
	//echo '<br/><br/>Event: <br/>';
	//var_dump($data);
	//echo '<br/><br/>';
?>