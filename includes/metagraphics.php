<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		die('Error 403: Forbidden.');
	}

	$image_meta_directory = nebula_prefer_child_directory('/images/meta');
	$cache_query = ( is_debug() )? '?nocache' . mt_rand(1000, 99999) . '=debug' . mt_rand(1000, 99999) : ''; //Add a random query string when debugging to force-clear the cache.

	/*
		Use http://realfavicongenerator.net to generate metagraphics.

		Notes:
			- Safari Pinned Tab and msapplication-TileColor color must be set individually.
			- OG Thumbnails and Twitter Card must be manually created.
	*/
?>


<?php //Favicons ?>
<link rel="shortcut icon" href="<?php echo $image_meta_directory; ?>/favicon.ico<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $image_meta_directory; ?>/favicon-16x16.png<?php echo $cache_query; ?>" >
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $image_meta_directory; ?>/favicon-32x32.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="96x96" href="<?php echo $image_meta_directory; ?>/favicon-96x96.png<?php echo $cache_query; ?>">
<link rel="mask-icon" href="<?php echo $image_meta_directory; ?>/safari-pinned-tab.svg<?php echo $cache_query; ?>" color="#0098d7">


<?php //Apple iOS ?>
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-57x57.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-60x60.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-72x72.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-76x76.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-114x114.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-120x120.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-144x144.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-152x152.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-180x180.png<?php echo $cache_query; ?>">
<link rel="apple-touch-icon-precomposed" sizes="180x180" href="<?php echo $image_meta_directory; ?>/apple-touch-icon-precomposed.png<?php echo $cache_query; ?>">


<?php //Android/Chrome ?>
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $image_meta_directory; ?>/favicon-16x16.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $image_meta_directory; ?>/favicon-32x32.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="36x36" href="<?php echo $image_meta_directory; ?>/android-chrome-36x36.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="48x48" href="<?php echo $image_meta_directory; ?>/android-chrome-48x48.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="72x72" href="<?php echo $image_meta_directory; ?>/android-chrome-72x72.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="96x96" href="<?php echo $image_meta_directory; ?>/android-chrome-96x96.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="144x144" href="<?php echo $image_meta_directory; ?>/android-chrome-144x144.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo $image_meta_directory; ?>/android-chrome-192x192.png<?php echo $cache_query; ?>">


<?php //Open Graph ?>
<?php if ( has_post_thumbnail($post->ID) ): ?>
	<meta property="og:image" content="<?php get_the_post_thumbnail($post->ID, 'open_graph_large'); ?>" />
	<meta property="og:image" content="<?php get_the_post_thumbnail($post->ID, 'open_graph_small'); ?>" />
<?php endif; ?>
<meta property="og:image" content="<?php echo $image_meta_directory; ?>/og-thumb.png<?php echo $cache_query; ?>" />
<meta property="og:image" content="<?php echo $image_meta_directory; ?>/og-thumb2.png<?php echo $cache_query; ?>" />


<?php //Twitter ?>
<meta name="twitter:image" content="<?php echo $image_meta_directory; ?>/twitter-card.png<?php echo $cache_query; ?>" />


<?php //Windows Tiles ?>
<meta name="application-name" content="<?php bloginfo('name') ?>" />
<meta name="msapplication-TileColor" content="#0098d7" />
<meta name="msapplication-square70x70logo" content="<?php echo $image_meta_directory; ?>/mstile-70x70.png<?php echo $cache_query; ?>" />
<meta name="msapplication-square150x150logo" content="<?php echo $image_meta_directory; ?>/mstile-150x150.png<?php echo $cache_query; ?>" />
<meta name="msapplication-wide310x150logo" content="<?php echo $image_meta_directory; ?>/mstile-310x150.png<?php echo $cache_query; ?>" />
<meta name="msapplication-square310x310logo" content="<?php echo $image_meta_directory; ?>/mstile-310x310.png<?php echo $cache_query; ?>" />
<meta name="msapplication-notification" content="frequency=30;polling-uri=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=1;polling-uri2=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=2;polling-uri3=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=3;polling-uri4=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=4;polling-uri5=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=5; cycle=1" />