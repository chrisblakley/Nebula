<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		exit;
	}

	global $post;
	$image_meta_directory = get_theme_file_uri('/assets/img/meta'); //Use this and concatenate the filenames so that it will never revert back to the parent theme if individual meta images are missing.
	$cache_query = ( nebula()->is_debug() )? '?nocache' . mt_rand(1000, mt_getrandmax()) . '=debug' . mt_rand(1000, mt_getrandmax()) : ''; //Add a random query string when debugging to force-clear the cache.

	/*
		Use http://realfavicongenerator.net to generate metagraphics or upload a 512x512 image to set as the Site Icon in the Customizer.

		Notes:
			- Safari Pinned Tab and msapplication-TileColor color must be set individually.
			- OG Thumbnails and Twitter Card must be manually created.

		Twitter Card Validator: https://cards-dev.twitter.com/validator
		Facebook Linter: https://developers.facebook.com/tools/debug/
	*/
?>

<?php //These must be the first three tags! ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

<?php do_action('nebula_head_open'); ?>

<meta name="referrer" content="always">
<meta name="HandheldFriendly" content="True" />
<meta name="MobileOptimized" content="320" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta class="theme-color" name="theme-color" content="<?php echo get_theme_mod('nebula_primary_color', nebula()->get_color('primary')); ?>">
<meta class="theme-color" name="msapplication-navbutton-color" content="<?php echo get_theme_mod('nebula_primary_color', nebula()->get_color('primary')); ?>">
<meta class="theme-color" name="apple-mobile-web-app-status-bar-style" content="<?php echo get_theme_mod('nebula_primary_color', nebula()->get_color('primary')); ?>">
<meta http-equiv="Accept-CH" content="Device-Memory">

<?php if ( is_ssl() ): //Upgrade http requests to https (cascades into iframes) ?>
	<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
<?php endif; ?>

<?php if ( nebula()->get_option('google_search_console_verification') ): ?>
	<meta name="google-site-verification" content="<?php echo esc_attr(nebula()->get_option('google_search_console_verification')); ?>" />
<?php endif; ?>

<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') && !is_plugin_active('autodescription/autodescription.php') ): //If Yoast SEO and SEO Framework is not active ?>
	<meta name="description" content="<?php echo esc_attr(nebula()->meta_description()); ?>" />
	<link rel="canonical" href="<?php the_permalink(); ?>" />
<?php endif; ?>

<?php $wpseo_social = get_option('wpseo_social'); ?>
<?php if ( (!is_plugin_active('wordpress-seo/wp-seo.php') && !is_plugin_active('autodescription/autodescription.php')) || (!empty($wpseo_social) && !$wpseo_social['opengraph']) ): //If Yoast SEO is not active, or if it is and the Open Graph settings are disabled ?>
	<meta property="og:type" content="business.business" />
	<meta property="og:locale" content="<?php echo str_replace('-', '_', get_bloginfo('language')); ?>" />
	<meta property="og:title" content="<?php echo get_the_title(); ?>" />
	<meta property="og:description" content="<?php echo esc_attr(nebula()->meta_description()); ?>" />

	<meta property="og:url" content="<?php the_permalink(); ?>" />
	<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>" />

	<meta property="business:contact_data:website" content="<?php echo home_url('/'); ?>" />
	<meta property="business:contact_data:phone_number" content="+<?php echo esc_attr(nebula()->get_option('phone_number')); ?>" />
	<meta property="business:contact_data:fax_number" content="+<?php echo esc_attr(nebula()->get_option('fax_number')); ?>" />
	<meta property="business:contact_data:street_address" content="<?php echo esc_attr(nebula()->get_option('street_address')); ?>" />
	<meta property="business:contact_data:locality" content="<?php echo esc_attr(nebula()->get_option('locality')); ?>" />
	<meta property="business:contact_data:region" content="<?php echo esc_attr(nebula()->get_option('region')); ?>" />
	<meta property="business:contact_data:postal_code" content="<?php echo esc_attr(nebula()->get_option('postal_code')); ?>" />
	<meta property="business:contact_data:country_name" content="<?php echo esc_attr(nebula()->get_option('country_name')); ?>" />

	<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ): //Business hours of operation. ?>
		<?php if ( nebula()->get_option('business_hours_' . $weekday . '_enabled') && nebula()->get_option('business_hours_' . $weekday . '_open') != '' && nebula()->get_option('business_hours_' . $weekday . '_close') != '' ) : ?>
			<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
			<meta property="business:hours:start" content="<?php echo esc_attr(nebula()->get_option('business_hours_' . $weekday . '_open')); ?>" />
			<meta property="business:hours:end" content="<?php echo esc_attr(nebula()->get_option('business_hours_' . $weekday . '_close')); ?>" />
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

<?php //Open Graph Thumbnails ?>
<?php if ( !empty($post) && has_post_thumbnail($post->ID) ): ?>
	<?php if ( get_the_post_thumbnail($post->ID, 'open_graph_large') ): ?>
		<meta property="og:image" content="<?php echo nebula()->get_thumbnail_src($post->ID, 'open_graph_large'); ?>" />
	<?php else: ?>
		<meta property="og:image" content="<?php echo nebula()->get_thumbnail_src($post->ID, 'open_graph_small'); ?>" />
	<?php endif; ?>
<?php endif; ?>

<?php if ( file_exists(get_theme_file_path('/assets/img/meta') . '/og-thumb.png') ): ?>
	<meta property="og:image" content="<?php echo $image_meta_directory . '/og-thumb.png' . $cache_query; ?>" />
<?php endif; ?>
<?php for ( $i = 2; file_exists(get_theme_file_path('/assets/img/meta') . '/og-thumb-' . $i . '.png'); $i++ ): //Check for additional Open Graph thumbnail images named "og-thumb-#.png" ?>
	<meta property="og:image" content="<?php echo $image_meta_directory . '/og-thumb-' . $i . '.png' . $cache_query; ?>" />
<?php endfor; ?>

<?php if ( !has_site_icon() ): ?>
	<link rel="shortcut icon" type="image/png" href="<?php echo $image_meta_directory . '/favicon.ico' . $cache_query; ?>" />
<?php endif; ?>
<link rel="shortcut icon" type="image/png" sizes="16x16" href="<?php echo get_site_icon_url(16, $image_meta_directory . '/favicon-16x16.png') . $cache_query; ?>" />
<link rel="shortcut icon" type="image/png" sizes="32x32" href="<?php echo get_site_icon_url(32, $image_meta_directory . '/favicon-16x16.png') . $cache_query; ?>" />

<?php if ( !has_site_icon() ): ?>
	<link rel="mask-icon" href="<?php echo $image_meta_directory . '/safari-pinned-tab.svg' . $cache_query; ?>" color="<?php echo nebula()->get_color('primary'); ?>" />
<?php endif; ?>

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_site_icon_url(180, $image_meta_directory . '/apple-touch-icon.png') . $cache_query; ?>" />
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo get_site_icon_url(192, $image_meta_directory . '/android-chrome-192x192.png') . $cache_query; ?>" />

<?php //Facebook Metadata ?>
<?php if ( nebula()->get_option('facebook_app_id') ): ?>
	<meta property="fb:app_id" content="<?php echo esc_attr(nebula()->get_option('facebook_app_id')); ?>" />
<?php endif; ?>
<?php if ( get_option('facebook_page_id') ): ?>
	<meta property="fb:pages" content="<?php echo esc_attr(nebula()->get_option('facebook_page_id')); ?>" />
<?php endif; ?>
<?php if ( get_option('facebook_admin_ids') ): ?>
	<meta property="fb:admins" content="<?php echo esc_attr(nebula()->get_option('facebook_admin_ids')); ?>" />
<?php endif; ?>

<?php //Twitter Metadata ?>
<?php if ( !empty($post) && has_post_thumbnail($post->ID) ): ?>
	<?php if ( get_the_post_thumbnail($post->ID, 'twitter_large') ): ?>
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:image" content="<?php echo nebula()->get_thumbnail_src($post->ID, 'twitter_large') . '?' . uniqid(); ?>" />
	<?php else: ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:image" content="<?php echo nebula()->get_thumbnail_src($post->ID, 'twitter_small') . '?' . uniqid(); ?>" />
	<?php endif; ?>
<?php else: ?>
	<?php if ( file_exists(get_theme_file_path('/assets/img/meta') . '/twitter-card_large.png') ): ?>
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:image" content="<?php echo $image_meta_directory . '/twitter-card_large.png?' . uniqid(); ?>" />
	<?php else: ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:image" content="<?php echo $image_meta_directory . '/twitter-card.png?' . uniqid(); ?>" />
	<?php endif; ?>
<?php endif; ?>
<?php if ( !is_plugin_active('wordpress-seo/wp-seo.php') ): ?>
	<meta name="twitter:title" content="<?php the_title(); ?>" />
	<meta name="twitter:description" content="<?php echo esc_attr(nebula()->meta_description(false, 200)); ?>" />
<?php endif; ?>

<?php if ( nebula()->get_option('twitter_user') ): ?>
	<meta name="twitter:site" content="<?php echo esc_attr(nebula()->get_option('twitter_user')); ?>" />
<?php endif; ?>
<?php if ( nebula()->get_option('author_bios') && !empty($post) && get_the_author_meta('twitter', $post->post_author) ): ?>
	<meta name="twitter:creator" content="@<?php echo get_the_author_meta('twitter', $post->post_author); ?>" />
<?php endif; ?>

<?php if ( nebula()->get_os('name') == 'Windows' ): //Windows Tiles ?>
	<meta name="application-name" content="<?php echo get_bloginfo('name') ?>" />
	<meta name="msapplication-TileColor" content="#0098d7" />
	<meta name="msapplication-square70x70logo" content="<?php echo get_site_icon_url(70, $image_meta_directory . '/mstile-70x70.png') . $cache_query; ?>" />
	<meta name="msapplication-square150x150logo" content="<?php echo get_site_icon_url(150, $image_meta_directory . '/mstile-150x150.png') . $cache_query; ?>" />
	<?php if ( !has_site_icon() ): ?>
		<meta name="msapplication-wide310x150logo" content="<?php echo get_theme_file_uri('/assets/img/meta/mstile-310x150.png') . $cache_query; ?>" />
	<?php endif; ?>
	<meta name="msapplication-square310x310logo" content="<?php echo get_site_icon_url(310, $image_meta_directory . '/mstile-310x310.png') . $cache_query; ?>" />
	<meta name="msapplication-notification" content="frequency=30;polling-uri=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=1;polling-uri2=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=2;polling-uri3=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=3;polling-uri4=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=4;polling-uri5=http://notifications.buildmypinnedsite.com/?feed=<?php echo get_bloginfo('rss_url'); ?>&amp;id=5; cycle=1" />
<?php endif; ?>

<?php //Local/Geolocation Metadata ?>
<meta name="geo.placename" content="<?php echo esc_attr(nebula()->get_option('locality')); ?>, <?php echo esc_attr(nebula()->get_option('region')); ?>" />
<meta name="geo.position" content="<?php echo esc_attr(nebula()->get_option('latitude')); ?>;<?php echo esc_attr(nebula()->get_option('longitude')); ?>" />
<meta name="geo.region" content="<?php echo get_bloginfo('language'); ?>" />
<meta name="ICBM" content="<?php echo esc_attr(nebula()->get_option('latitude')); ?>, <?php echo esc_attr(nebula()->get_option('longitude')); ?>" />
<meta property="place:location:latitude" content="<?php echo esc_attr(nebula()->get_option('latitude')); ?>" />
<meta property="place:location:longitude" content="<?php echo esc_attr(nebula()->get_option('longitude')); ?>" />

<link rel="manifest" href="<?php echo esc_url(nebula()->manifest_json_location()); ?>" />
<link rel="profile" href="https://gmpg.org/xfn/11" />

<?php
	//JSON-LD Structured Data
	//Google Structured Data Documentation: https://developers.google.com/search/docs/data-types/data-type-selector
	//JSON-LD Examples: https://jsonld.com/
	//Google Structured Data Testing Tool: https://search.google.com/structured-data/testing-tool
	//Rich Text Test: https://search.google.com/test/rich-results

	$company_type = ( nebula()->get_option('business_type') )? esc_html(nebula()->get_option('business_type')) : 'LocalBusiness';
?>
<script type="application/ld+json">
	{
		"@context": "https://schema.org/",
		"@type": "<?php echo $company_type; ?>",
		"name": "<?php echo ( nebula()->get_option('site_owner') )? esc_html(nebula()->get_option('site_owner')) : get_bloginfo('name'); ?>",
		"url": "<?php echo home_url('/'); ?>",
		"address": {
			"@type": "PostalAddress",
			"streetAddress": "<?php echo nebula()->get_option('street_address'); ?>",
			"addressLocality": "<?php echo nebula()->get_option('locality'); ?>",
			"addressRegion": "<?php echo nebula()->get_option('region'); ?>",
			"postalCode": "<?php echo nebula()->get_option('postal_code'); ?>",
			"addressCountry": "<?php echo nebula()->get_option('country_name'); ?>"
		},
		"telephone": "+<?php echo nebula()->get_option('phone_number'); ?>",

		<?php if ( nebula()->get_option('latitude') ): ?>
			"geo": {
				"@type": "GeoCoordinates",
				"latitude": "<?php echo nebula()->get_option('latitude'); ?>",
				"longitude": "<?php echo nebula()->get_option('longitude'); ?>"
			},
			"hasMap": "https://www.google.com/maps/place/<?php echo nebula()->get_option('latitude'); ?>,<?php echo nebula()->get_option('longitude'); ?>",
		<?php endif; ?>

		<?php if ( $company_type !== 'Organization' && $company_type !== 'Corporation' ): ?>
			<?php
				$opening_hours_specification = '';
				foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
					if ( nebula()->get_option('business_hours_' . $weekday . '_enabled') && nebula()->get_option('business_hours_' . $weekday . '_open') != '' && nebula()->get_option('business_hours_' . $weekday . '_close') != '' ){
						$opening_hours_specification .= '{
							"@type": "OpeningHoursSpecification",
							"dayOfWeek": "' . $weekday . '",
							"opens": "' . date('H:i', strtotime(nebula()->get_option('business_hours_' . $weekday . '_open'))) . '",
							"closes": "' . date('H:i', strtotime(nebula()->get_option('business_hours_' . $weekday . '_close'))) . '"
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

			<?php if ( nebula()->get_option('phone_number') ): ?>
				"telephone": "+<?php echo nebula()->get_option('phone_number'); ?>",
			<?php else: ?>
				"url": "<?php echo home_url(); ?>/contact",
			<?php endif; ?>

			"email": "<?php echo nebula()->get_option('contact_email'); ?>",
			"contactType": "customer service"
		},

		<?php
			$company_same_as = '';
			if ( nebula()->get_option('facebook_url') ){
				$company_same_as .= '"' . nebula()->social_url('facebook') . '",';
			}

			if ( nebula()->get_option('twitter_username') ){
				$company_same_as .= '"' . nebula()->twitter_url() . '",';
			}

			if ( nebula()->get_option('linkedin_url') ){
				$company_same_as .= '"' . nebula()->social_url('linkedin') . '",';
			}

			if ( nebula()->get_option('youtube_url') ){
				$company_same_as .= '"' . nebula()->social_url('youtube') . '",';
			}

			if ( nebula()->get_option('instagram_url') ){
				$company_same_as .= '"' . nebula()->social_url('instagram') . '",';
			}

			if ( nebula()->get_option('pinterest_url') ){
				$company_same_as .= '"' . nebula()->social_url('pinterest') . '",';
			}
		?>
		<?php if ( !empty($company_same_as) ): ?>
			"sameAs": [
				<?php echo rtrim($company_same_as, ','); ?>
			],
		<?php endif; ?>

		<?php if ( $company_type === 'LocalBusiness' ): ?>
			"priceRange": "",
		<?php endif; ?>

		"image": "<?php echo $image_meta_directory; ?>/og-thumb.png",
		"logo": "<?php echo nebula()->logo('meta'); ?>"
	}
</script>

<?php if ( is_author() && nebula()->get_option('author_bios') ): ?>
	<script type="application/ld+json">
		{
			"@context": "https://schema.org/",
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
					$person_same_as .= '"https://www.facebook.com/' . get_the_author_meta('facebook', $user->ID) . '",';
				}

				if ( get_the_author_meta('twitter', $user->ID) ){
					$person_same_as .= '"' . nebula()->twitter_url(get_the_author_meta('twitter', $user->ID)) . '",';
				}



				if ( get_the_author_meta('linkedin', $user->ID) ){
					$person_same_as .= '"https://www.linkedin.com/profile/view?id=' . get_the_author_meta('linkedin', $user->ID) . '",';
				}

				if ( get_the_author_meta('youtube', $user->ID) ){
					$person_same_as .= '"https://www.youtube.com/channel/' . get_the_author_meta('youtube', $user->ID) . '",';
				}

				if ( get_the_author_meta('instagram', $user->ID) ){
					$person_same_as .= '"https://instagram.com/' . get_the_author_meta('instagram', $user->ID) . '",';
				}

				if ( get_the_author_meta('pinterest', $user->ID) ){
					$person_same_as .= '"https://pinterest.com/' . get_the_author_meta('pinterest', $user->ID) . '",';
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

<?php if ( is_singular('post') ): //@todo "Nebula" 0: but not products ?>
	<script type="application/ld+json">
		{
			"@context": "https://schema.org/",
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
					"url": "<?php echo $image_meta_directory; ?>/og-thumb.png",
					"width": "1200",
					"height": "600"
				},
			<?php endif; ?>

			"datePublished": "<?php echo get_the_date('c'); ?>",
			"dateModified": "<?php echo get_the_modified_date('c'); ?>",
			"author": {
				<?php if ( nebula()->get_option('author_bios') ): ?>
					"@type": "Person",
					"name": "<?php echo the_author_meta('display_name', $post->post_author); ?>"
				<?php else: ?>
					"@type": "Organization",
					"name": "<?php echo nebula()->get_option('site_owner'); ?>"
				<?php endif; ?>
			},
			"publisher": {
				"@type": "Organization",
				"name": "<?php echo ( nebula()->get_option('site_owner') )? nebula()->get_option('site_owner') : get_bloginfo('name'); ?>",
				"logo": {
					"@type": "ImageObject",
					"url": "<?php echo nebula()->logo('meta'); ?>"
				}
			},
			"description": "<?php echo nebula()->meta_description(); ?>"
		}
	</script>
<?php endif; ?>

<?php if ( is_front_page() && get_search_form(array('echo' => false)) ): //On home page if search is not disabled ?>
	<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "WebSite",
			"url": "<?php echo home_url('/'); ?>",
			"potentialAction": {
				"@type": "SearchAction",
				"target": "<?php echo home_url('/'); ?>?q={search_term_string}",
				"query-input": "required name=search_term_string"
			}
		}
	</script>
<?php endif; ?>

<?php do_action('nebula_metadata_end'); ?>