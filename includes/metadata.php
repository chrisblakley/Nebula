<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		http_response_code(403);
		die();
	}

	global $post;
	$image_meta_directory = get_theme_file_uri('/images/meta');
	$cache_query = ( is_debug() )? '?nocache' . mt_rand(1000, mt_getrandmax()) . '=debug' . mt_rand(1000, mt_getrandmax()) : ''; //Add a random query string when debugging to force-clear the cache.

	/*
		Use http://realfavicongenerator.net to generate metagraphics.

		Notes:
			- Safari Pinned Tab and msapplication-TileColor color must be set individually.
			- OG Thumbnails and Twitter Card must be manually created.

		Twitter Card Validator: https://cards-dev.twitter.com/validator
		Facebook Linter: https://developers.facebook.com/tools/debug/
	*/
?>

<?php if ( nebula_option('google_search_console_verification') ): ?>
	<meta name="google-site-verification" content="<?php echo nebula_option('google_search_console_verification'); ?>" />
<?php endif; ?>

<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') ): //If Yoast SEO is not active ?>
	<meta name="description" content="<?php echo nebula_excerpt(array('length' => 100, 'more' => '', 'ellipsis' => false, 'strip_tags' => true)); ?>" />
	<link rel="canonical" href="<?php the_permalink(); ?>" />
<?php endif; ?>

<?php $wpseo_social = get_option('wpseo_social'); ?>
<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') || (!empty($wpseo_social) && !$wpseo_social['opengraph']) ): //If Yoast SEO is not active, or if it is and the Open Graph settings are disabled ?>
	<meta property="og:type" content="business.business" />
	<meta property="og:locale" content="<?php echo str_replace('-', '_', get_bloginfo('language')); ?>" />
	<meta property="og:title" content="<?php echo get_the_title(); ?>" />
	<meta property="og:description" content="<?php echo nebula_excerpt(array('length' => 30, 'more' => '', 'ellipsis' => false, 'strip_tags' => true)); ?>" />
	<meta property="og:url" content="<?php the_permalink(); ?>" />
	<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>" />

	<meta property="business:contact_data:website" content="<?php echo home_url('/'); ?>" />
	<meta property="business:contact_data:phone_number" content="+<?php echo nebula_option('phone_number'); ?>" />
	<meta property="business:contact_data:fax_number" content="+<?php echo nebula_option('fax_number'); ?>" />
	<meta property="business:contact_data:street_address" content="<?php echo nebula_option('street_address'); ?>" />
	<meta property="business:contact_data:locality" content="<?php echo nebula_option('locality'); ?>" />
	<meta property="business:contact_data:region" content="<?php echo nebula_option('region'); ?>" />
	<meta property="business:contact_data:postal_code" content="<?php echo nebula_option('postal_code'); ?>" />
	<meta property="business:contact_data:country_name" content="<?php echo nebula_option('country_name'); ?>" />

	<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ): //Business hours of operation. ?>
		<?php if ( nebula_option('business_hours_' . $weekday . '_enabled') && nebula_option('business_hours_' . $weekday . '_open') != '' && nebula_option('business_hours_' . $weekday . '_close') != '' ) : ?>
			<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
			<meta property="business:hours:start" content="<?php echo nebula_option('business_hours_' . $weekday . '_open'); ?>" />
			<meta property="business:hours:end" content="<?php echo nebula_option('business_hours_' . $weekday . '_close'); ?>" />
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ( !empty($post) && has_post_thumbnail($post->ID) ): ?>
		<?php if ( get_the_post_thumbnail($post->ID, 'open_graph_large') ): ?>
			<meta property="og:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'open_graph_large'); ?>" />
		<?php else: ?>
			<meta property="og:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'open_graph_small'); ?>" />
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>

<?php //Open Graph Thumbnails ?>
<?php if ( file_exists(get_theme_file_path('/images/meta') . '/og-thumb.png') ): ?>
	<meta property="og:image" content="<?php echo $image_meta_directory; ?>/og-thumb.png<?php echo $cache_query; ?>" />
<?php endif; ?>
<?php for ( $i = 2; file_exists(get_theme_file_path('/images/meta') . '/og-thumb-' . $i . '.png'); $i++ ): //Check for additional Open Graph thumbnail images named "og-thumb-#.png" ?>
	<meta property="og:image" content="<?php echo $image_meta_directory; ?>/og-thumb-<?php echo $i; ?>.png<?php echo $cache_query; ?>" />
<?php endfor; ?>

<?php //Favicons ?>
<link rel="shortcut icon" type="image/png" href="<?php echo $image_meta_directory; ?>/favicon.ico<?php echo $cache_query; ?>" />
<link rel="shortcut icon" type="image/png" sizes="16x16" href="<?php echo $image_meta_directory; ?>/favicon-16x16.png<?php echo $cache_query; ?>" />
<link rel="shortcut icon" type="image/png" sizes="32x32" href="<?php echo $image_meta_directory; ?>/favicon-32x32.png<?php echo $cache_query; ?>" />

<?php if ( nebula_get_browser('name') == 'Safari' ): //Safari ?>
	<link rel="mask-icon" href="<?php echo $image_meta_directory; ?>/safari-pinned-tab.svg<?php echo $cache_query; ?>" color="<?php echo nebula_sass_color('primary'); ?>" />
<?php endif; ?>

<?php if ( nebula_get_os('name') == 'iOS' ): //Apple iOS ?>
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $image_meta_directory; ?>/apple-touch-icon.png<?php echo $cache_query; ?>" />
<?php endif; ?>

<?php if ( nebula_get_os('name') == 'Android' ): //Android/Chrome ?>
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo $image_meta_directory; ?>/android-chrome-192x192.png<?php echo $cache_query; ?>" />
<?php endif; ?>

<?php //Facebook Metadata ?>
<?php if ( nebula_option('facebook_app_id') ): ?>
	<meta property="fb:app_id" content="<?php echo nebula_option('facebook_app_id'); ?>" />
<?php endif; ?>
<?php if ( get_option('facebook_page_id') ): ?>
	<meta property="fb:pages" content="<?php echo nebula_option('facebook_page_id'); ?>" />
<?php endif; ?>
<?php if ( get_option('facebook_admin_ids') ): ?>
	<meta property="fb:admins" content="<?php echo get_option('facebook_admin_ids'); ?>" />
<?php endif; ?>

<?php //Twitter Metadata ?>
<?php if ( !empty($post) && has_post_thumbnail($post->ID) ): ?>
	<?php if ( get_the_post_thumbnail($post->ID, 'twitter_large') ): ?>
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'twitter_large'); ?>?<?php echo uniqid(); ?>" />
	<?php else: ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:image" content="<?php echo nebula_get_thumbnail_src($post->ID, 'twitter_small'); ?>?<?php echo uniqid(); ?>" />
	<?php endif; ?>
<?php else: ?>
	<?php if ( file_exists(get_theme_file_path('/images/meta') . '/twitter-card_large.png') ): ?>
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:image" content="<?php echo $image_meta_directory; ?>/twitter-card_large.png?<?php echo uniqid(); ?>" />
	<?php else: ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:image" content="<?php echo $image_meta_directory; ?>/twitter-card.png?<?php echo uniqid(); ?>" />
	<?php endif; ?>
<?php endif; ?>
<meta name="twitter:title" content="<?php the_title(); ?>" />
<meta name="twitter:description" content="<?php echo nebula_excerpt(array('length' => 30, 'more' => '', 'ellipsis' => false, 'strip_tags' => true)); ?>" />
<?php if ( nebula_option('twitter_user') ): ?>
	<meta name="twitter:site" content="<?php echo nebula_option('twitter_user'); ?>" />
<?php endif; ?>
<?php if ( nebula_option('author_bios', 'enabled') && !empty($post) && get_the_author_meta('twitter', $post->post_author) ): ?>
	<meta name="twitter:creator" content="@<?php echo get_the_author_meta('twitter', $post->post_author); ?>" />
<?php endif; ?>

<?php if ( nebula_get_os('name') == 'Windows' ): //Windows Tiles ?>
	<meta name="application-name" content="<?php echo get_bloginfo('name') ?>" />
	<meta name="msapplication-TileColor" content="#0098d7" />
	<meta name="msapplication-square70x70logo" content="<?php echo $image_meta_directory; ?>/mstile-70x70.png<?php echo $cache_query; ?>" />
	<meta name="msapplication-square150x150logo" content="<?php echo $image_meta_directory; ?>/mstile-150x150.png<?php echo $cache_query; ?>" />
	<meta name="msapplication-wide310x150logo" content="<?php echo $image_meta_directory; ?>/mstile-310x150.png<?php echo $cache_query; ?>" />
	<meta name="msapplication-square310x310logo" content="<?php echo $image_meta_directory; ?>/mstile-310x310.png<?php echo $cache_query; ?>" />
	<meta name="msapplication-notification" content="frequency=30;polling-uri=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=1;polling-uri2=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=2;polling-uri3=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=3;polling-uri4=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=4;polling-uri5=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=5; cycle=1" />
<?php endif; ?>

<?php //Local/Geolocation Metadata ?>
<meta name="geo.placename" content="<?php echo nebula_option('locality'); ?>, <?php echo nebula_option('region'); ?>" />
<meta name="geo.position" content="<?php echo nebula_option('latitude'); ?>;<?php echo nebula_option('longitude'); ?>" />
<meta name="geo.region" content="<?php echo get_bloginfo('language'); ?>" />
<meta name="ICBM" content="<?php echo nebula_option('latitude'); ?>, <?php echo nebula_option('longitude'); ?>" />
<meta property="place:location:latitude" content="<?php echo nebula_option('latitude'); ?>" />
<meta property="place:location:longitude" content="<?php echo nebula_option('longitude'); ?>" />

<?php
	//JSON-LD Structured Data
	//Google Structured Data Documentation: https://developers.google.com/search/docs/data-types/data-type-selector
	//JSON-LD Examples: http://jsonld.com/
	//Google Structured Data Testing Tool: https://search.google.com/structured-data/testing-tool

	$company_type = 'LocalBusiness'; //@TODO "Nebula" 0: Consider a Nebula Option for this type (LocalBusiness (default), Organization, etc)
?>
<script type="application/ld+json">
	{
		"@context": "http://schema.org/",
		"@type": "<?php echo $company_type; ?>",
		"name": "<?php echo ( nebula_option('site_owner') )? nebula_option('site_owner') : get_bloginfo('name'); ?>",
		"url": "<?php echo home_url('/'); ?>",
		"address": {
			"@type": "PostalAddress",
			"streetAddress": "<?php echo nebula_option('street_address'); ?>",
			"addressLocality": "<?php echo nebula_option('locality'); ?>",
			"addressRegion": "<?php echo nebula_option('region'); ?>",
			"postalCode": "<?php echo nebula_option('postal_code'); ?>",
			"addressCountry": "<?php echo nebula_option('country_name'); ?>"
		},

		<?php if ( $company_type == 'LocalBusiness' ): ?>
			"geo": {
				"@type": "GeoCoordinates",
				"latitude": <?php echo nebula_option('latitude'); ?>,
				"longitude": <?php echo nebula_option('longitude'); ?>
			},
			<?php
				$opening_hours_specification = '';
				foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
					if ( nebula_option('business_hours_' . $weekday . '_enabled') && nebula_option('business_hours_' . $weekday . '_open') != '' && nebula_option('business_hours_' . $weekday . '_close') != '' ){
						$opening_hours_specification .= '{
							"@type": "OpeningHoursSpecification",
							"dayOfWeek": "' . $weekday . '",
							"opens": "' . date('H:i', strtotime(nebula_option('business_hours_' . $weekday . '_open'))) . '",
							"closes": "' . date('H:i', strtotime(nebula_option('business_hours_' . $weekday . '_close'))) . '"
						},';
					}
				}
			?>
			<?php if ( !empty($opening_hours_specification) ): ?>
				"openingHoursSpecification": [
					<?php echo rtrim($opening_hours_specification, ','); ?>
				],
			<?php endif; ?>
		<?php endif; ?>

		"contactPoint": {
			"@type": "ContactPoint",

			<?php if ( nebula_option('phone_number') ): ?>
				"telephone": "+<?php echo nebula_option('phone_number'); ?>",
			<?php else: ?>
				"url": "<?php echo home_url(); ?>/contact",
			<?php endif; ?>

			"email": "<?php echo nebula_option('contact_email'); ?>",
			"contactType": "customer service"
		},

		<?php
			$company_same_as = '';
			if ( nebula_option('facebook_url') ){
				$company_same_as .= '"' . nebula_option('facebook_url') . '",';
			}

			if ( nebula_option('twitter_username') ){
				$company_same_as .= '"' . nebula_twitter_url() . '",';
			}

			if ( nebula_option('google_plus_url') ){
				$company_same_as .= '"' . nebula_option('google_plus_url') . '",';
			}

			if ( nebula_option('linkedin_url') ){
				$company_same_as .= '"' . nebula_option('linkedin_url') . '",';
			}

			if ( nebula_option('youtube_url') ){
				$company_same_as .= '"' . nebula_option('youtube_url') . '",';
			}

			if ( nebula_option('instagram_url') ){
				$company_same_as .= '"' . nebula_option('instagram_url') . '",';
			}
		?>
		<?php if ( !empty($company_same_as) ): ?>
			"sameAs": [
				<?php echo rtrim($company_same_as, ','); ?>
			],
		<?php endif; ?>

		"logo": "<?php echo get_theme_file_uri('/images/logo.png'); ?>"
	}
</script>

<?php if ( is_author() && nebula_option('author_bios', 'enabled') ): ?>
	<script type="application/ld+json">
		{
			"@context": "http://schema.org/",
			"@type": "Person",
			"name": "<?php echo get_the_author(); ?>",
			"email": "<?php echo get_the_author_meta('user_email'); ?>",

			<?php if ( get_the_author_meta('jobtitle') ): ?>
				"jobTitle": "<?php echo get_the_author_meta('jobtitle'); ?>",
			<?php endif; ?>

			<?php if ( get_the_author_meta('phonenumber') ): ?>
				"telephone": "+<?php echo get_the_author_meta('phonenumber'); ?>",
			<?php endif; ?>

			<?php
				if ( get_the_author_meta('facebook', $user->ID) ){
					$person_same_as .= '"http://www.facebook.com/' . get_the_author_meta('facebook', $user->ID) . '",';
				}

				if ( get_the_author_meta('twitter', $user->ID) ){
					$person_same_as .= '"' . nebula_twitter_url(get_the_author_meta('twitter', $user->ID)) . '",';
				}

				if ( get_the_author_meta('googleplus', $user->ID) ){
					$person_same_as .= '"https://plus.google.com/+' . get_the_author_meta('googleplus', $user->ID) . '",';
				}

				if ( get_the_author_meta('linkedin', $user->ID) ){
					$person_same_as .= '"https://www.linkedin.com/profile/view?id=' . get_the_author_meta('linkedin', $user->ID) . '",';
				}

				if ( get_the_author_meta('youtube', $user->ID) ){
					$person_same_as .= '"https://www.youtube.com/channel/' . get_the_author_meta('youtube', $user->ID) . '",';
				}

				if ( get_the_author_meta('instagram', $user->ID) ){
					$person_same_as .= '"http://instagram.com/' . get_the_author_meta('instagram', $user->ID) . '",';
				}
			?>
			<?php if ( !empty($person_same_as) ): ?>
				"sameAs": [
					<?php echo rtrim($person_same_as, ','); ?>
				],
			<?php endif; ?>

			"image": "<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>"
		}
	</script>
<?php endif; ?>

<?php if ( is_singular('post') ): //@todo: but not products ?>
	<script type="application/ld+json">
		{
			"@context": "http://schema.org/",
			"@type": "Article",
			"mainEntityofPage": {
				"@type": "WebPage",
				"@id": "<?php echo get_permalink(); ?>"
			},
			"headline": "<?php echo get_the_title(); ?>",

			<?php $post_thumbnail_meta = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
			<?php if ( !empty($post_thumbnail_meta) ): ?>
				"image": {
					"@type": "ImageObject",
					"url": "<?php echo $post_thumbnail_meta[0]; ?>",
					"width": "<?php echo $post_thumbnail_meta[1]; ?>",
					"height": "<?php echo $post_thumbnail_meta[2]; ?>"
				},
			<?php else: ?>
				"image": {
					"@type": "ImageObject",
					"url": "<?php echo get_theme_file_uri('/images/meta/og-thumb.png'); ?>",
					"width": "1200",
					"height": "600"
				},
			<?php endif; ?>

			"datePublished": "<?php echo get_the_date('c'); ?>",
			"dateModified": "<?php echo get_the_modified_date('c'); ?>",
			"author": {
				<?php if ( nebula_option('author_bios', 'enabled') ): ?>
					"@type": "Person",
					"name": "<?php echo the_author_meta('display_name', $post->post_author); ?>"
				<?php else: ?>
					"@type": "Organization",
					"name": "<?php echo nebula_option('site_owner'); ?>"
				<?php endif; ?>
			},
			"publisher": {
				"@type": "Organization",
				"name": "<?php echo ( nebula_option('site_owner') )? nebula_option('site_owner') : get_bloginfo('name'); ?>",
				"logo": {
					"@type": "ImageObject",
					"url": "<?php echo get_theme_file_uri('/images/logo.png'); ?>"
				}
			},
			"description": "<?php echo nebula_excerpt(array('length' => 100, 'more' => '', 'ellipsis' => false, 'strip_tags' => true)); ?>"
		}
	</script>
<?php endif; ?>

<?php do_action('nebula_metadata_end'); ?>