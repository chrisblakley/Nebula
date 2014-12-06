<?php
	require_once('../../../../wp-load.php'); //@TODO "Nebula" 0: If these are being used to include separate sections of a template from independent files, then get_template_part() should be used instead.
	ga_send_pageview($_GET['h'], $_GET['p'], $_GET['t']);
	ga_send_event('JavaScript Disabled', $_SERVER['HTTP_USER_AGENT'], $_GET['t']);
?>