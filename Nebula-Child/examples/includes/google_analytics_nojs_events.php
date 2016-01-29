<noscript>
	<style>
		/* Logo Click (Example) */
		.no-js .logocon:active {background-image: url('http://www.google-analytics.com/__utm.gif?utmac=<?php echo $GLOBALS['ga']; ?>&utmt=event&utmwv=1&utmdt=<?php urlencode(get_the_title()); ?>&utmhn=<?php echo nebula_url_components('hostname'); ?>&utmp=<?php echo nebula_url_components('filepath'); ?>&utmn=<?php echo rand(pow(10, 10-1), pow(10, 10)-1); ?><?php echo ( $_SERVER['HTTP_REFERER'] )? '&utmr=' . $_SERVER['HTTP_REFERER']: ''; ?>&utme=5(Logo*Click*No-JS%20clicked%20logo.)');}
	</style>
</noscript>

<?php //@TODO "Nebula" 0: Come up with a function that makes this easier. Maybe function_name($selector, $pseudo, $category, $action, $label, $value); Find the minimum parameters needed for the gif. ?>