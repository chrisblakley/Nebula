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

		Twitter Card Validator: https://cards-dev.twitter.com/validator
		Facebook Linter: https://developers.facebook.com/tools/debug/
	*/
?>

<?php //Keywords and Description ?>
<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') ): ?>
	<meta name="description" content="<?php echo nebula_the_excerpt('', 100, 0); ?>" />
	<meta name="keywords" content="<?php echo nebula_option('keywords'); ?>" />
	<?php if ( function_exists('get_field') && get_field('news_keywords') ): //News keywords are <=10 comma separated keywords. ?>
		<meta name="news_keywords" content="<?php echo get_field('news_keywords'); ?>" />
	<?php endif; ?>
	<?php if ( nebula_option('author_bios', 'enabled') ): ?>
		<meta name="author" content="<?php echo nebula_the_author(); ?>" />
	<?php endif; ?>
<?php endif; ?>


<?php //Open Graph ?>
<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') || is_front_page() ): ?>
	<?php if ( nebula_option('google_search_console_verification') ): ?>
		<meta name="google-site-verification" content="<?php echo nebula_option('google_search_console_verification'); ?>" />
	<?php endif; ?>
	<meta property="og:type" content="business.business" />
	<meta property="og:locale" content="<?php echo str_replace('-', '_', get_bloginfo('language')); ?>" />
	<meta property="og:title" content="<?php echo get_the_title(); ?>" />
	<meta property="og:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
	<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') ) : ?>
		<meta property="og:url" content="<?php the_permalink(); ?>" />
	<?php endif; ?>
	<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />

	<link rel="canonical" href="<?php the_permalink(); ?>" />

	<meta property="business:contact_data:website" content="<?php echo home_url('/'); ?>" />
	<meta property="business:contact_data:phone_number" content="+<?php echo nebula_option('phone_number'); ?>" />
	<meta property="business:contact_data:fax_number" content="+<?php echo nebula_option('fax_number'); ?>" />
	<meta property="business:contact_data:street_address" content="<?php echo nebula_option('street_address'); ?>" />
	<meta property="business:contact_data:locality" content="<?php echo nebula_option('locality'); ?>" />
	<meta property="business:contact_data:region" content="<?php echo nebula_option('region'); ?>" />
	<meta property="business:contact_data:postal_code" content="<?php echo nebula_option('postal_code'); ?>" />
	<meta property="business:contact_data:country_name" content="<?php echo nebula_option('country_name'); ?>" />

	<?php if ( has_post_thumbnail($post->ID) ): ?>
		<?php if ( get_the_post_thumbnail($post->ID, 'open_graph_large') ): ?>
			<meta property="og:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'open_graph_large'); ?>" />
		<?php else: ?>
			<meta property="og:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'open_graph_small'); ?>" />
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
<?php if ( file_exists(nebula_prefer_child_directory('/images/meta', false) . '/og-thumb.png') ): ?>
	<meta property="og:image" content="<?php echo $image_meta_directory; ?>/og-thumb.png<?php echo $cache_query; ?>" />
<?php endif; ?>
<?php if ( file_exists(nebula_prefer_child_directory('/images/meta', false) . '/og-thumb2.png') ): ?>
	<meta property="og:image" content="<?php echo $image_meta_directory; ?>/og-thumb2.png<?php echo $cache_query; ?>" />
<?php endif; ?>

<?php //Business hours of operation. ?>
<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ): ?>
	<?php if ( nebula_option('business_hours_' . $weekday . '_enabled') && nebula_option('business_hours_' . $weekday . '_open') != '' && nebula_option('business_hours_' . $weekday . '_close') != '' ) : ?>
		<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
		<meta property="business:hours:start" content="<?php echo nebula_option('business_hours_' . $weekday . '_open'); ?>" />
		<meta property="business:hours:end" content="<?php echo nebula_option('business_hours_' . $weekday . '_close'); ?>" />
	<?php endif; ?>
<?php endforeach; ?>


<?php //Favicons ?>
<link rel="shortcut icon" href="<?php echo $image_meta_directory; ?>/favicon.ico<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $image_meta_directory; ?>/favicon-16x16.png<?php echo $cache_query; ?>" >
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $image_meta_directory; ?>/favicon-32x32.png<?php echo $cache_query; ?>">
<link rel="icon" type="image/png" sizes="96x96" href="<?php echo $image_meta_directory; ?>/favicon-96x96.png<?php echo $cache_query; ?>">
<link rel="mask-icon" href="<?php echo $image_meta_directory; ?>/safari-pinned-tab.svg<?php echo $cache_query; ?>" color="<?php echo nebula_sass_color('primary'); ?>">


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


<?php //Facebook Metadata ?>
<meta property="fb:app_id" content="<?php echo nebula_option('facebook_app_id'); ?>" />
<meta property="fb:page_id" content="<?php echo nebula_option('facebook_page_id'); //Is this even used anymore? ?>" />
<meta property="fb:pages" content="<?php echo nebula_option('facebook_page_id'); ?>" />
<meta property="fb:admins" content="<?php echo get_option('facebook_admin_ids'); ?>" />


<?php //Twitter Metadata ?>
<?php if ( has_post_thumbnail($post->ID) ): ?>
	<?php if ( get_the_post_thumbnail($post->ID, 'twitter_large') ): ?>
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'twitter_large'); ?>?<?php echo uniqid(); ?>" />
	<?php else: ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'twitter_small'); ?>?<?php echo uniqid(); ?>" />
	<?php endif; ?>
<?php else: ?>
	<?php if ( file_exists(nebula_prefer_child_directory('/images/meta', false) . '/twitter-card_large.png') ): ?>
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:image" content="<?php echo $image_meta_directory; ?>/twitter-card_large.png?<?php echo uniqid(); ?>" />
	<?php else: ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:image" content="<?php echo $image_meta_directory; ?>/twitter-card.png?<?php echo uniqid(); ?>" />
	<?php endif; ?>
<?php endif; ?>
<meta name="twitter:title" content="<?php the_title(); ?>" />
<meta name="twitter:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
<?php if ( nebula_option('twitter_user') ): ?>
	<meta name="twitter:site" content="<?php echo nebula_option('twitter_user'); ?>" />
<?php endif; ?>
<?php if ( nebula_option('author_bios', 'enabled') && get_the_author_meta('twitter', $user->ID) ): ?>
	<meta name="twitter:creator" content="@<?php echo get_the_author_meta('twitter', $user->ID); ?>" />
<?php endif; ?>


<?php //Windows Tiles ?>
<meta name="application-name" content="<?php bloginfo('name') ?>" />
<meta name="msapplication-TileColor" content="#0098d7" />
<meta name="msapplication-square70x70logo" content="<?php echo $image_meta_directory; ?>/mstile-70x70.png<?php echo $cache_query; ?>" />
<meta name="msapplication-square150x150logo" content="<?php echo $image_meta_directory; ?>/mstile-150x150.png<?php echo $cache_query; ?>" />
<meta name="msapplication-wide310x150logo" content="<?php echo $image_meta_directory; ?>/mstile-310x150.png<?php echo $cache_query; ?>" />
<meta name="msapplication-square310x310logo" content="<?php echo $image_meta_directory; ?>/mstile-310x310.png<?php echo $cache_query; ?>" />
<meta name="msapplication-notification" content="frequency=30;polling-uri=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=1;polling-uri2=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=2;polling-uri3=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=3;polling-uri4=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=4;polling-uri5=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=5; cycle=1" />


<?php //Local/Geolocation Metadata ?>
<meta name="geo.placename" content="<?php echo nebula_option('locality'); ?>, <?php echo nebula_option('region'); ?>" />
<meta name="geo.position" content="<?php echo nebula_option('latitude'); ?>;<?php echo nebula_option('longitude'); ?>" />
<meta name="geo.region" content="<?php echo bloginfo('language'); ?>" />
<meta name="ICBM" content="<?php echo nebula_option('latitude'); ?>, <?php echo nebula_option('longitude'); ?>" />
<meta property="place:location:latitude" content="<?php echo nebula_option('latitude'); ?>" />
<meta property="place:location:longitude" content="<?php echo nebula_option('longitude'); ?>" />