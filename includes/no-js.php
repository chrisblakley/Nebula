<?php
	require_once('../../../../wp-load.php'); //@TODO "Nebula" 0: If these are being used to include separate sections of a template from independent files, then get_template_part() should be used instead.

	$nojs_mobile = '';
	if ( $GLOBALS["mobile_detect"]->isMobile() ) {
		$nojs_mobile = ' (Mobile - ';

		if ( $GLOBALS["mobile_detect"]->isiOS() ) {
			$nojs_mobile .= 'iOS';
		} elseif ( $GLOBALS["mobile_detect"]->isAndroidOS() ) {
			$nojs_mobile .= 'Android';
		} else {
			$nojs_mobile .= 'Unknown OS';
		}

		if ( $GLOBALS["mobile_detect"]->isTablet() ) {
			$nojs_mobile .= ' Tablet';
		} else {
			$nojs_mobile .= ' Device';
		}

		$nojs_mobile .= ')';
	}

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
		'ea' => $GLOBALS['browser_detect']['browser_name'] . $nojs_mobile, //Action (Required)
		'el' => $_GET['t'] //Label
	);
	gaSendData($data);
?>