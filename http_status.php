<?php
/**
 * The template for displaying error pages (Besides 404).
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

if ( $GLOBALS['http'] >= 400 ){
	$http_type = 'Error';
} else {
	$http_type = 'Status';
}

switch ( $GLOBALS['http'] ){
    case 100:
    	$http_name = 'Continue';
    	$http_description = "";
    	break;
    case 101:
    	$http_name = 'Switching Protocols';
    	$http_description = "";
    	break;
    case 200:
    	$http_name = 'OK';
    	$http_description = "";
    	break;
    case 201:
    	$http_name = 'Created';
    	$http_description = "";
    	break;
    case 202:
    	$http_name = 'Accepted';
    	$http_description = "";
    	break;
    case 203:
    	$http_name = 'Non-Authoritative Information';
    	$http_description = "";
    	break;
    case 204:
    	$http_name = 'No Content';
    	$http_description = "";
    	break;
    case 205:
    	$http_name = 'Reset Content';
    	$http_description = "";
    	break;
    case 206:
    	$http_name = 'Partial Content';
    	$http_description = "";
    	break;
    case 300:
    	$http_name = 'Multiple Choices';
    	$http_description = "";
    	break;
    case 301:
    	$http_name = 'Moved Permanently';
    	$http_description = "";
    	break;
    case 302:
    	$http_name = 'Moved Temporarily';
    	$http_description = "";
    	break;
    case 303:
    	$http_name = 'See Other';
    	$http_description = "";
    	break;
    case 304:
    	$http_name = 'Not Modified';
    	$http_description = "";
    	break;
    case 305:
    	$http_name = 'Use Proxy';
    	$http_description = "";
    	break;
    case 400:
    	$http_name = 'Bad Request';
    	$http_description = "Your browser sent a request that this server could not understand.";
    	break;
    case 401:
    	$http_name = 'Unauthorized';
    	$http_description = "Authorization Required: This server could not verify that you are authorized to access the document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required.";
    	break;
    case 402:
    	$http_name = 'Payment Required';
    	$http_description = "";
    	break;
    case 403:
    	$http_name = 'Forbidden';
    	$http_description = "The website declined to show this webpage.";
    	break;
    case 404:
    	$http_name = 'Not Found';
    	$http_description = "The requested URL " . nebula_requested_url() . " was not found on this server.";
    	break;
    case 405:
    	$http_name = 'Method Not Allowed';
    	$http_description = "The requested method is not allowed";
    	break;
    case 406:
    	$http_name = 'Not Acceptable';
    	$http_description = "";
    	break;
    case 407:
    	$http_name = 'Proxy Authentication Required';
    	$http_description = "";
    	break;
    case 408:
    	$http_name = 'Request Time-out';
    	$http_description = "";
    	break;
    case 409:
    	$http_name = 'Conflict';
    	$http_description = "";
    	break;
    case 410:
    	$http_name = 'Gone';
    	$http_description = "";
    	break;
    case 411:
    	$http_name = 'Length Required';
    	$http_description = "";
    	break;
    case 412:
    	$http_name = 'Precondition Failed';
    	$http_description = "";
    	break;
    case 413:
    	$http_name = 'Request Entity Too Large';
    	$http_description = "";
    	break;
    case 414:
    	$http_name = 'Request-URI Too Large';
    	$http_description = "";
    	break;
    case 415:
    	$http_name = 'Unsupported Media Type';
    	$http_description = "";
    	break;
    case 500:
    	$http_name = 'Internal Server Error';
    	$http_description = "The server encountered an internal error or misconfiguration and was unable to complete your request.";
    	break;
    case 501:
    	$http_name = 'Not Implemented';
    	$http_description = "Method not supported.";
    	break;
    case 502:
    	$http_name = 'Bad Gateway';
    	$http_description = "The proxy server received an invalid response from an upstream server.";
    	break;
    case 503:
    	$http_name = 'Service Unavailable';
    	$http_description = "The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.";
    	break;
    case 504:
    	$http_name = 'Gateway Time-out';
    	$http_description = "The proxy server did not receive a timely response from the upstream server.";
    	break;
    case 505:
    	$http_name = 'HTTP Version not supported';
    	$http_description = "The server encountered an internal error or misconfiguration and was unable to complete your request.";
    	break;
    default:
        $http_name = 'Unknown';
    	$http_description = "An unknown error occurred.";
    break;
}

do_action('nebula_preheaders');
get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr />
	</div><!--/columns-->
</div><!--/row-->

<div class="container fullcontentcon">
	<div class="row">

		<div class="eleven columns">
			<article id="post-0" class="post error<?php echo $GLOBALS['http']; ?>" role="main">
				<h1><?php echo $http_type; ?> <?php echo $GLOBALS['http']; ?>: <?php echo $http_name; ?></h1>
				<p><?php echo $http_description; ?></p>

				<?php get_search_form(); ?>
			</article>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<script>
	ga('set', gaCustomDimensions['sessionNotes'], sessionNote('HTTP <?php echo $GLOBALS['http']; ?> Page'));
	if ( document.referrer.length ){
		ga('send', 'event', 'HTTP Status Page', '<?php echo $http_type . ' ' . $GLOBALS['http'] . ' (' . $http_name . ')'; ?>', 'Referrer: ' + document.referrer, {'nonInteraction': 1});
	} else {
		ga('send', 'event', 'HTTP Status Page', '<?php echo $http_type . ' ' . $GLOBALS['http'] . ' (' . $http_name . ')'; ?>', 'No Referrer (or Unknown)', {'nonInteraction': 1});
	}

	var thisURL = [location.protocol, '//', location.host, location.pathname].join('');
	history.replaceState(null, document.title, thisURL);
</script>

<?php get_footer(); ?>