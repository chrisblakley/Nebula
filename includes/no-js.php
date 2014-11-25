<?php
	require_once('../../../../wp-load.php'); //@TODO "Nebula" 0: If these are being used to include separate sections of a template from independent files, then get_template_part() should be used instead.

	//Send Pageview
	$data = array(
		'v' => $_GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $_GLOBALS['ga_cid'],
		't' => 'pageview',
		'dh' => $_GET['h'], //Document Hostname "gearside.com"
		'dp' => $_GET['p'], //Page "/something"
		'dt' => $_GET['t'] //Title
	);
	gaSendData($data);

	//Send Event
	$data = array(
		'v' => $_GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $_GLOBALS['ga_cid'],
		't' => 'event',
		'ec' => 'JavaScript Disabled', //Category (Required)
		'ea' => $_GET['t'], //Action (Required)
		//'el' => 'label' //Label (browser info here)
	);
	gaSendData($data);
?>