<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}
?>

<?php if ( nebula()->is_analytics_allowed() ): ?>
	<?php nebula()->timer('Analytics (Include)'); ?>
	<?php if ( nebula()->get_option('ga_tracking_id') ): //Universal Google Analytics //@todo "Nebula" 0: Remove after July 2023 ?>
		<!-- Nebula GA (UA) -->
		<script src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html(nebula()->get_option('ga_tracking_id')); ?>" async></script>
		<script async>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', "<?php echo esc_html(nebula()->get_option('ga_tracking_id')); ?>");
		</script>
	<?php endif; ?>

	<?php if ( nebula()->get_option('ga_measurement_id') ): //Google Analytics ?>
		<!-- Nebula GA4 <?php echo nebula()->get_option('ga_property_id'); ?> -->
		<script src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>" async></script>
		<script async>
			window.performance.mark('(Nebula) Analytics [Start]');

			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}

			<?php if ( nebula()->option('ga_require_consent') ):  ?>
				gtag('consent', 'default', {ad_storage: 'denied'});
			<?php endif; ?>

			<?php
				$pageview_properties = array(
					'send_page_view' => true,
				);

				if ( nebula()->is_dev() || nebula()->is_debug() ){
					$pageview_properties['debug_mode'] = true;
				}

				//WordPress User ID
				if ( nebula()->get_option('ga_wpuserid') && is_user_logged_in() ){
					$pageview_properties['user_id'] = get_current_user_id(); //This property must be less than 256 characters (and cannot match the CID). GA4 does not make this easily available in reporting.
					$pageview_properties['wp_id'] = get_current_user_id(); //This is to make it more easily available in GA4 reports/explorations
				}

				if ( is_singular() || is_page() ){
					global $post;

					if ( is_singular() ){
						//Designate single posts because they aren't always easily distinguishable from the URL alone
						$pageview_properties['single_post'] = ( is_front_page() )? 'Front Page' : 'Single Post';
						$pageview_properties['post_type'] = get_post_type(get_the_ID());

						//Article author
						// if ( nebula()->get_option('author_bios') ){
						// 	$pageview_properties['post_author'] = get_the_author();
						// }

						//Article's published year
						$pageview_properties['publish_date'] = get_the_date('Y-m-d');
					}

					//Word Count
					// $word_count = nebula()->word_count();
					// if ( $word_count ){
					// 	echo 'nebula.post.wordcount = ' . $word_count . ';';
					// 	$pageview_properties['word_count'] = nebula()->word_count(array('range' => true));
					// }
				}

				//Query Strings
				if ( !empty(nebula()->url_components('query')) ){
					$pageview_properties['query_string'] = nebula()->url_components('query');
				}

				//Business Open/Closed
// 				if ( nebula()->has_business_hours() ){
// 					if ( nebula()->business_open() ){
// 						$business_open = 'During Business Hours';
// 						echo 'nebula.user.client.businessopen = true;';
// 					} else {
// 						$business_open = 'Non-Business Hours';
// 						echo 'nebula.user.client.businessopen = false;';
// 					}
//
// 					$pageview_properties['business_hours'] = $business_open;
// 				}

				//Relative time ("Late Morning", "Early Evening")
				// $relative_time = nebula()->relative_time();
				// $time_description = implode(' ', $relative_time['description']);
				// $time_range = $relative_time['standard'][0] . ':00' . $relative_time['ampm'] . ' - ' . $relative_time['standard'][2] . ':59' . $relative_time['ampm'];
				// $pageview_properties['relative_time'] = ucwords($time_description) . ' (' . $time_range . ')';

				//WP Role
				$pageview_properties['user_role'] = nebula()->user_role();

				//WPML Language
				if ( defined('ICL_LANGUAGE_NAME') ){
					$pageview_properties['wpml_language'] = ICL_LANGUAGE_NAME . ' (' . ICL_LANGUAGE_CODE . ')';
				}
			?>

			//Set the property JS object here
			nebula.pageviewProperties = <?php echo wp_json_encode(apply_filters('nebula_ga_pageview_properties', $pageview_properties)); //Allow other functions to modify the PHP pageview properties ?>;

			//Post Categories and Tags
			nebula.pageviewProperties.post_categories = nebula?.post?.categories;
			nebula.pageviewProperties.post_tags = nebula?.post?.tags;

			//Post Ancestors
			if ( nebula?.post?.ancestors ){
				nebula.pageviewProperties.ancestors = Object.values(nebula.post.ancestors).join(); //Convert the list of ancestor slugs into a comma-separated string
			}

			if ( window.performance ){
				//Redirects
				nebula.pageviewProperties.redirect_count = performance.navigation.redirectCount;

				//Navigation Type
				nebula.pageviewProperties.navigation_type = 'Unknown';
				switch ( performance.navigation.type ){
					case 0: //Normal navigation
						nebula.pageviewProperties.navigation_type = 'Navigation';
						break;
					case 1: //Reload
						nebula.pageviewProperties.navigation_type = 'Reload';
						break;
					case 2: //Forward or Back button
						nebula.pageviewProperties.navigation_type = 'Back/Forward';
						break;
					default:
						nebula.pageviewProperties.navigation_type = 'Other (' + performance.navigation.type + ')';
						break;
				}

				//Text Fragment Ex: #:~:text=This%20is%20an%20example.
				// if ( window.performance ){
				// 	var firstNavigationEntry = window.performance.getEntriesByType('navigation')[0];
				// 	if ( typeof firstNavigationEntry === 'object' ){ //This object sometimes does not exist in Safari
				// 		var textFragment = firstNavigationEntry.name.match('#:~:text=(.*)');
				// 		if ( textFragment ){ //If the text fragment exists, set the GA dimension
				// 			nebula.pageviewProperties.text_fragment = decodeURIComponent(textFragment[1]);
				// 		}
				// 	}
				// }
			}

			if ( window !== window.top ){
				var htmlClasses = document.getElementsByTagName('html')[0].getAttribute("class") || '';
				document.getElementsByTagName('html')[0].setAttribute('class', headCSS + 'in-iframe'); //Use vanilla JS in case jQuery is not yet available
				nebula.pageviewProperties.window_type = 'Iframe: ' + window.top.location.href;
			}

			if ( navigator.standalone || window.matchMedia('(display-mode: standalone)').matches ){
				var htmlClasses = document.getElementsByTagName('html')[0].getAttribute("class") || '';
				document.getElementsByTagName('html')[0].setAttribute('class', headCSS + 'in-standalone-app'); //Use vanilla JS in case jQuery is not yet available
				nebula.pageviewProperties.window_type = 'Standalone App';
			}

			nebula.user.saveData = <?php echo wp_json_encode(nebula()->is_save_data()); //JSON Encode forces boolean return to print ?>;
			//nebula.pageviewProperties.save_data = nebula.user.saveData;

			//Prefers reduced motion
			// nebula.user.prefersReducedMotion = false;
			// if ( window.matchMedia('(prefers-reduced-motion: reduce)').matches ){
			// 	nebula.user.prefersReducedMotion = true;
			// }
			// nebula.pageviewProperties.prefers_reduced_motion = nebula.user.prefersReducedMotion;

			//Prefers color scheme
			// nebula.user.prefersColorScheme = 'light';
			// if ( window.matchMedia('(prefers-color-scheme: dark)').matches ){
			// 	nebula.user.prefersColorScheme = 'dark';
			// }
			// nebula.pageviewProperties.prefers_color_scheme = nebula.user.prefersColorScheme;

			<?php do_action('nebula_ga_before_pageview'); //Simple action for adding/modifying all custom definitions (including JS) before the pageview hit is sent. ?>

			gtag('js', new Date());
			gtag('config', '<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>', nebula.pageviewProperties); //This sends the page_view

			window.performance.mark('(Nebula) Analytics Pageview'); //Inexact
			window.performance.measure('(Nebula) Time to Analytics Pageview', 'navigationStart', '(Nebula) Analytics Pageview');

			gtag('get', '<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>', 'client_id', function(gaClientId){
				nebula.user.cid = gaClientId; //Update the CID in Nebula ASAP to reflect the actual GA CID
				nebula.session.id = nebula.session.id.replace(/cid:(.*?);/i, 'cid:' + gaClientId + ';'); //Replace the CID in the Nebula Session ID as well

				gtag('set', 'user_properties', {
					client_id: gaClientId
				});
			});

			<?php if ( nebula()->get_option('ga_wpuserid') && is_user_logged_in() ): //Need to do this twice because user_id cannot be accessed in GA4 reports, so need to send it again as a custom dimension. ?>
				gtag('set', 'user_properties', {
					user_id: '<?php echo get_current_user_id(); ?>'
				});
			<?php endif; ?>

			gtag('get', '<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>', 'session_id', function(gaSessionId){
				let nebulaSessionId = nebula?.session?.id || '';
				gtag('set', 'user_properties', {
					ga_session_id: gaSessionId,
					nebula_session_id: nebulaSessionId + 'ga:' + gaSessionId
				});
			});

			<?php if ( nebula()->is_staff() ): ?>
				gtag('set', 'user_properties', {
					traffic_type: 'internal' //This is a default GA4 property name/value for internal traffic filtering
				});
			<?php endif; ?>

			//User role
			// gtag('set', 'user_properties', {
			// 	role: '<?php echo nebula()->user_role(); ?>'
			// });

			<?php if ( is_404() ): //Track 404 Errors ?>
				var lastReferrer = nebula.session?.referrer || document.referrer || '(Unknown Referrer)';
				gtag('event', '404_not_found', {
					event_category: '404 Not Found',
					event_action: '<?php echo esc_url(nebula()->requested_url()); ?>',
					event_label: 'Referrer: ' + lastReferrer,
					requested_url: '<?php echo esc_url(nebula()->requested_url()); ?>',
					referrer: lastReferrer,
					non_interaction: true
				});
			<?php endif; ?>

			<?php do_action('nebula_ga_after_pageview'); ?>
		</script>
	<?php else: //If Measurement ID is empty: ?>
		<?php if ( !nebula()->get_option('ga_tracking_id') && !nebula()->get_option('gtm_id') ): //If GTM ID is also empty, set an empty gtag() function to prevent JS errors ?>
			<script>
				if ( typeof gtag == 'undefined' ){
					function gtag(){}; //No GA in Nebula
				}
			</script>
		<?php endif; ?>
	<?php endif; ?>

	<?php nebula()->timer('Analytics (Include)', 'end'); ?>
<?php endif; ?>

<?php if ( nebula()->get_option('gtm_id') ): //Google Tag Manager (can be used for more than just tracking) ?>
	<!-- Nebula GTM <?php echo nebula()->get_option('ga_property_id'); ?> -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo nebula()->get_option('gtm_id'); ?>');</script>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('google_ads_id') && !is_customize_preview() ): ?>
	<!-- Nebula Google Ads -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo nebula()->get_option('google_ads_id'); ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo nebula()->get_option('google_ads_id'); ?>', {allow_enhanced_conversions: true});
	</script>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('facebook_custom_audience_pixel_id') && !is_customize_preview() ): //Facebook Custom Audience ?>
	<!-- Nebula FB -->
	<link rel="prefetch" href="//connect.facebook.net/en_US/fbevents.js" />
	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

		fbq('init', '<?php echo esc_html(nebula()->get_option('facebook_custom_audience_pixel_id')); ?>'); //@todo "Nebula" 0: Can we *get* data from Hubspot to send email and other info here?
		fbq('track', 'PageView');

		<?php do_action('nebula_fbq_after_track_pageview'); //Hook into for adding more Facebook custom audience tracking. ?>
	</script>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('hubspot_portal') ): //Hubspot CRM ?>
	<!-- Nebula Hubspot -->
	<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/<?php echo esc_html(nebula()->get_option('hubspot_portal')); ?>.js"></script>
	<script>
		var _hsq = window._hsq = window._hsq || [];
		_hsq.push(['setPath', '<?php echo str_replace(get_site_url(), '', get_permalink()); ?>']); //Is this even needed?

		<?php
			$hubspot_identify = array(
				'ipaddress' => nebula()->get_ip_address(),
				'user_agent' => nebula()->super->server['HTTP_USER_AGENT'],
				'session_id' => nebula()->nebula_session_id(), //If this hits rate limits, consider removing it
			);

			if ( is_user_logged_in() ){
				$user_info = get_userdata(get_current_user_id());

				$hubspot_identify['email'] = $user_info->user_email;
				$hubspot_identify['firstname'] = $user_info->first_name;
				$hubspot_identify['lastname'] = $user_info->last_name;
				$hubspot_identify['wordpress_id'] = get_current_user_id();
				$hubspot_identify['username'] = $user_info->user_login;
				$hubspot_identify['role'] = nebula()->user_role();
				$hubspot_identify['jobtitle'] = get_user_meta(get_current_user_id(), 'jobtitle', true);
				$hubspot_identify['company'] = get_user_meta(get_current_user_id(), 'jobcompany', true);
				$hubspot_identify['website'] = get_user_meta(get_current_user_id(), 'jobcompanywebsite', true);
				$hubspot_identify['city'] = get_user_meta(get_current_user_id(), 'usercity', true);
				$hubspot_identify['state'] = get_user_meta(get_current_user_id(), 'userstate', true);
				$hubspot_identify['phone'] = get_user_meta(get_current_user_id(), 'phonenumber', true);
			}

			$hubspot_identify['device'] = nebula()->get_device();
			$hubspot_identify['os'] = nebula()->get_os();
			$hubspot_identify['browser'] = nebula()->get_browser();
			$hubspot_identify['bot'] = ( nebula()->is_bot() )? 1 : 0;
		?>

		var hubspotIdentify = <?php echo wp_json_encode(apply_filters('nebula_hubspot_identify', $hubspot_identify)); //Allow other functions to hook into Hubspot identifications ?>;
		hubspotIdentify.cookies = ( window.navigator.cookieEnabled )? '1' : '0';
		hubspotIdentify.screen = window.screen.width + 'x' + window.screen.height + ' (' + window.screen.colorDepth + ' bits)';

		_hsq.push(["identify", hubspotIdentify]);

		<?php do_action('nebula_hubspot_before_send_pageview'); //Hook into for adding more parameters before the pageview is sent. Can override any above identifications too. ?>

		<?php if ( nebula()->get_option('ga_measurement_id') ): //If Google Analytics is used, grab the Client ID before sending the Hubspot pageview ?>
			gtag('get', '<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>', 'client_id', function(clientId){
				_hsq.push(["identify", {
					client_id: clientId,
				}]);

				_hsq.push(['trackPageView']);
			});
		<?php else: ?>
			_hsq.push(['trackPageView']);
		<?php endif; ?>
	</script>
<?php endif; ?>

<?php do_action('nebula_analytics_end'); //Hook into for adding more tracking scripts/services (or copy this entire file to the child theme and modify it directly). ?>