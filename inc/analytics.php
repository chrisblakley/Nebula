<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		exit;
	}
?>

<?php if ( nebula()->is_analytics_allowed() ): ?>
	<?php nebula()->timer('Analytics (Include)'); ?>
	<?php if ( nebula()->get_option('ga_tracking_id') ): //Universal Google Analytics ?>
		<script type="module" async>
			import {setDimension, uuid, localTimestamp} from '<?php echo get_template_directory_uri(); ?>/assets/js/modules/analytics.js';

			window.performance.mark('(Nebula) Analytics [Start]');

			//Load the alternative async tracking snippet: https://developers.google.com/analytics/devguides/collection/analyticsjs/#alternative_async_tracking_snippet
			//Allow Linker for cross-domain tracking. Linker plugin and configuration must be done in the child theme.
			window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			ga('create', '<?php echo esc_html(nebula()->get_option('ga_tracking_id')); ?>', 'auto', {
				<?php echo ( nebula()->get_option('ga_wpuserid') && is_user_logged_in() )? '"userId": "' . get_current_user_id() . '",' : ''; ?>
				"allowLinker": true
			});

			ga('set', 'anonymizeIp', true); //Anonymize the IP address //This happens by default in GA4 so can be removed here.

			//Use Beacon if supported. Eventually we can completely remove this when GA uses Beacon by default.
			if ( 'sendBeacon' in navigator ){
				ga('set', 'transport', 'beacon');
			}

			<?php if ( nebula()->get_option('ga_displayfeatures') ): ?>
				ga('require', 'displayfeatures');
			<?php endif; ?>

			<?php if ( nebula()->get_option('ga_linkid') ): ?>
				ga('require', 'linkid');
			<?php endif; ?>

			<?php if ( nebula()->get_option('google_optimize_id') ): //Google Optimize ?>
				ga('require', '<?php echo nebula()->get_option('google_optimize_id'); ?>');
			<?php endif; ?>

			<?php do_action('nebula_ga_before_dimensions'); //Hook into for adding more custom definitions before the pageview hit is sent. Can override any above definitions too. ?>

			if ( window.performance ){
				setDimension('Redirect Count', performance.navigation.redirectCount, nebula.analytics.dimensions.redirectcount);

				//Navigation Type
				var navigationTypeLabel = 'Unknown';
				switch ( performance.navigation.type ){
					case 0: //Normal navigation
						navigationTypeLabel = 'Navigation';
						break;
					case 1: //Reload
						navigationTypeLabel = 'Reload';
						break;
					case 2: //Forward or Back button
						navigationTypeLabel = 'Back/Forward';
						break;
					default:
						navigationTypeLabel = 'Other (' + performance.navigation.type + ')';
						break;
				}
				setDimension('Navigation Type', navigationTypeLabel, nebula.analytics.dimensions.navigationtype);

				//Text Fragment (Ex: #:~:text=This%20is%20an%20example.
				if ( window.performance ){
					var firstNavigationEntry = window.performance.getEntriesByType('navigation')[0];
					if ( typeof firstNavigationEntry === 'object' ){ //This object sometimes does not exist in Safari
						var textFragment = firstNavigationEntry.name.match('#:~:text=(.*)');
						if ( textFragment ){ //If the text fragment exists, set the GA dimension
							setDimension('Text Fragment', decodeURIComponent(textFragment[1]), nebula.analytics.dimensions.textFragment);
						}
					}
				}
			}

			<?php
				if ( is_singular() || is_page() ){
					global $post;

					if ( is_singular() ){
						//Article author
						if ( nebula()->get_option('author_bios') && nebula()->get_option('cd_author') ){
							echo 'setDimension("Author", "' . get_the_author() . '", nebula.analytics.dimensions.author);';
						}

						//Article's published year
						if ( nebula()->get_option('cd_publishdate') ){
							echo 'setDimension("Publish Date", "' . get_the_date('Y-m-d') . '", nebula.analytics.dimensions.publishDate);';
						}
					}

					if ( nebula()->get_option('cd_categories') ){
						echo 'setDimension("Post Categories", nebula.post.categories, nebula.analytics.dimensions.categories);';
					}

					if ( nebula()->get_option('cd_tags') ){
						echo 'setDimension("Post Tags", nebula.post.tags, nebula.analytics.dimensions.tags);';
					}

					//Word Count
					$word_count = nebula()->word_count();
					if ( $word_count ){
						echo 'nebula.post.wordcount = ' . $word_count . ';';

						if ( nebula()->get_option('cm_wordcount') ){
							echo 'ga("set", nebula.analytics.metrics.wordCount, nebula.post.wordcount);';
						}

						if ( nebula()->get_option('cd_wordcount') ){
							echo 'setDimension("Word Count", "' . nebula()->word_count(array('range' => true)) . '", nebula.analytics.dimensions.wordCount);';
						}
					}
				}

				//Business Open/Closed
				if ( nebula()->get_option('cd_businesshours') ){
					if ( nebula()->business_open() ){
						$business_open = 'During Business Hours';
						echo 'nebula.user.client.businessopen = true;';
					} else {
						$business_open = 'Non-Business Hours';
						echo 'nebula.user.client.businessopen = false;';
					}

					echo 'setDimension("Business Hours", "' . $business_open . '", nebula.analytics.dimensions.businessHours);';
				}

				//Relative time ("Late Morning", "Early Evening")
				if ( nebula()->get_option('cd_relativetime') ){
					$relative_time = nebula()->relative_time();
					$time_description = implode(' ', $relative_time['description']);
					$time_range = $relative_time['standard'][0] . ':00' . $relative_time['ampm'] . ' - ' . $relative_time['standard'][2] . ':59' . $relative_time['ampm'];
					echo 'setDimension("Relative Time", "' . ucwords($time_description) . ' (' . $time_range . ')", nebula.analytics.dimensions.relativeTime);';
				}

				//Role
				if ( nebula()->get_option('cd_role') ){
					echo 'setDimension("Role", "' . nebula()->user_role() . '", nebula.analytics.dimensions.role);';
				}

				//Session ID
				if ( nebula()->get_option('cd_sessionid') ){
					echo 'nebula.session.id = "' . nebula()->nebula_session_id() . '";';
					echo 'setDimension("Session ID", nebula.session.id, nebula.analytics.dimensions.sessionID);';
				}

				//WordPress User ID
				if ( is_user_logged_in() ){
					$current_user = wp_get_current_user();
					if ( $current_user && nebula()->get_option('cd_userid') ){
						echo 'ga("set", "userId", ' . $current_user->ID . ');';
						echo 'setDimension("User ID", "' . $current_user->ID . '", nebula.analytics.dimensions.userID);';
					}
				}

				//Weather Conditions
				if ( nebula()->get_option('cd_weather') ){
					echo 'setDimension("Weather", "' . nebula()->weather('conditions') . '", nebula.analytics.dimensions.weather);';
				}
				//Temperature Range
				if ( nebula()->get_option('cd_temperature') ){
					$temp_round = floor(nebula()->weather('temperature')/5)*5;
					$temp_round_celcius = round(($temp_round-32)/1.8);
					$temp_range = strval($temp_round) . '°F - ' . strval($temp_round+4) . '°F (' . strval($temp_round_celcius) . '°C - ' . strval($temp_round_celcius+2) . '°C)';
					echo 'setDimension("Temperature", "' . $temp_range . '", nebula.analytics.dimensions.temperature);';
				}

				//WPML Language
				if ( defined('ICL_LANGUAGE_NAME') ){
					echo 'setDimension("WPML Language", "' . ICL_LANGUAGE_NAME . ' (' . ICL_LANGUAGE_CODE . ')", nebula.analytics.dimensions.wpmlLang);';
				}
			?>

			<?php if ( nebula()->get_option('cd_windowtype') ): //Window Type ?>
				if ( window !== window.top ){
					var htmlClasses = document.getElementsByTagName('html')[0].getAttribute("class") || '';
					document.getElementsByTagName('html')[0].setAttribute('class', headCSS + 'in-iframe'); //Use vanilla JS in case jQuery is not yet available
					setDimension('Window Type', 'Iframe: ' + window.top.location.href, nebula.analytics.dimensions.windowType);
				}

				if ( navigator.standalone || window.matchMedia('(display-mode: standalone)').matches ){
					var htmlClasses = document.getElementsByTagName('html')[0].getAttribute("class") || '';
					document.getElementsByTagName('html')[0].setAttribute('class', headCSS + 'in-standalone-app'); //Use vanilla JS in case jQuery is not yet available
					setDimension('Window Type', 'Standalone App', nebula.analytics.dimensions.windowType);
				}
			<?php endif; ?>

			nebula.user.saveData = <?php echo json_encode(nebula()->is_save_data()); //JSON Encode forces boolean return to print ?>;
			setDimension('Save Data', nebula.user.saveData, nebula.analytics.dimensions.saveData);

			//Prefers reduced motion
			nebula.user.prefersReducedMotion = false;
			if ( window.matchMedia('(prefers-reduced-motion: reduce)').matches ){
				nebula.user.prefersReducedMotion = true;
			}
			setDimension('Prefers Reduced Motion', nebula.user.prefersReducedMotion, nebula.analytics.dimensions.reducedMotion);

			//Prefers color scheme
			nebula.user.prefersColorScheme = 'light';
			if ( window.matchMedia('(prefers-color-scheme: dark)').matches ){
				nebula.user.prefersColorScheme = 'dark';
			}
			setDimension('Prefers Color Scheme', nebula.user.prefersColorScheme, nebula.analytics.dimensions.colorScheme);

			<?php if ( nebula()->get_option('cd_offline') ): ?>
				setDimension('Offline', 'online', nebula.analytics.dimensions.offline);
			<?php endif; ?>

			<?php do_action('nebula_ga_before_send_pageview'); //Hook into for adding more custom definitions before the pageview hit is sent. Can override any above definitions too. ?>

			//Modify the payload before sending data to Google Analytics
			ga(function(tracker){
				tracker.set(nebula.analytics.dimensions.gaCID, tracker.get('clientId'));

				if ( nebula && nebula.session && nebula.session.id ){
					nebula.session.id = nebula.session.id.replace(/;cid:(.+);/i, ';cid:' + tracker.get('clientId') + ';'); //Update the CID once assigned
				}

				var originalBuildHitTask = tracker.get('buildHitTask'); //Grab a reference to the default buildHitTask function.
				tracker.set('buildHitTask', function(model){ //This runs on every hit send
					var qt = model.get('queueTime') || 0;

					//Remove PII if present
					if ( model.get('location').includes('crm-') ){
						model.set('location', model.get('location').replace(/(crm-.*?)&|(crm-.*?)$/gi, ''), true);
					}

					//Remove index.php filenames for consistency
					if ( model.get('location').includes('index.php') ){
						model.set('location', model.get('location').replace(/(index\.php)/i, ''), true);
					}

					<?php if ( nebula()->get_option('cd_querystring') ): ?>
						//Store the query string in a custom dimension if desired
						if ( model.get('location').includes('?') ){ //If a query string exists
							model.set(nebula.analytics.dimensions.queryString, '?' + model.get('location').split('?').pop(), true); //Store just the query string in the custom dimension

							if ( !model.get('location').includes('gclid') && !model.get('location').includes('utm_') && !model.get('location').includes('?s') && !model.get('location').includes('?rs') ){ //Ignore search queries and UTMs are fine too because GA removes them from page reports
								//model.set('page', model.get('location').split('?').shift(), true); //Only keep the URL before the "?" as the location... Never modify the location data as it is used for session-level dimensions such as UTM and GCLID
							}
						}
					<?php endif; ?>

					//Add a trailing slash if needed (do this after the query string modification)
					if ( !model.get('location').includes('?') && !model.get('location').endsWith('/') && !/\.\w+$/.test(model.get('location')) ){ //Ignore query strings and filenames (and if there is already a trailing slash)
						model.set('location', model.get('location') + '/', true);
					}

					<?php if ( nebula()->get_option('cd_mqbreakpoint') ): ?>
						//Bootstrap Breakpoint
						if ( window.matchMedia("(min-width: 2048px)").matches ){
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'uw', true);
						} else if ( window.matchMedia("(min-width: 1400px)").matches ){
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'xxl', true);
						} else if ( window.matchMedia("(min-width: 1200px)").matches ){
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'xl', true);
						} else if ( window.matchMedia("(min-width: 992px)").matches ){
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'lg', true);
						} else if ( window.matchMedia("(min-width: 768px)").matches ){
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'md', true);
						} else if ( window.matchMedia("(min-width: 544px)").matches ){
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'sm', true);
						} else {
							model.set(nebula.analytics.dimensions.mqBreakpoint, 'xs', true);
						}
					<?php endif; ?>

					<?php if ( nebula()->get_option('cd_mqresolution') ): ?>
						//Screen Resolution
						if ( window.matchMedia("(min-resolution: 192dpi)").matches ){
							model.set(nebula.analytics.dimensions.mqResolution, '2x', true);
						} else if ( window.matchMedia("(min-resolution: 144dpi)").matches ){
							model.set(nebula.analytics.dimensions.mqResolution, '1.5x', true);
						} else {
							model.set(nebula.analytics.dimensions.mqResolution, '1x', true);
						}
					<?php endif; ?>

					<?php if ( nebula()->get_option('cd_mqorientation') ): ?>
						//Screen Orientation
						if ( window.matchMedia("(orientation: portrait)").matches ){
							model.set(nebula.analytics.dimensions.mqOrientation, 'Portrait', true);
						} else if ( window.matchMedia("(orientation: landscape)").matches ){
							model.set(nebula.analytics.dimensions.mqOrientation, 'Landscape', true);
						}
					<?php endif; ?>

					//Always send hit dimensions with all payloads
					model.set(nebula.analytics.dimensions.hitID, uuid(), true);
					model.set(nebula.analytics.dimensions.hitTime, String(new Date-qt), true);
					model.set(nebula.analytics.dimensions.hitType, model.get('hitType'), true);

					var interactivity = 'Interaction';
					if ( model.get('nonInteraction') ){
						interactivity = 'Non-Interaction';
					}
					model.set(nebula.analytics.dimensions.hitInteractivity, interactivity, true);

					var transportMethod = model.get('transport') || 'JavaScript';
					model.set(nebula.analytics.dimensions.hitMethod, model.get('transport'), true);

					model.set(nebula.analytics.dimensions.timestamp, localTimestamp(), true);
					model.set(nebula.analytics.dimensions.visibilityState, document.visibilityState, true);

					var connection = ( navigator.onLine )? 'Online' : 'Offline';
					model.set(nebula.analytics.dimensions.network, connection, true);

					if ( 'deviceMemory' in navigator ){ //Chrome 64+
						var deviceMemoryLevel = navigator.deviceMemory < 1 ? 'lite' : 'full';
						model.set(nebula.analytics.dimensions.deviceMemory, navigator.deviceMemory + '(' + deviceMemoryLevel + ')', true);
					} else {
						model.set(nebula.analytics.dimensions.deviceMemory, '(not set)', true);
					}

					<?php do_action('nebula_ga_additional_tasks'); //Hook into for adding more task operations ?>

					originalBuildHitTask(model); //Send the payload to Google Analytics
				});

				<?php do_action('nebula_ga_after_send_pageview'); ?>
			});

			<?php if ( (isset(nebula()->super->server['HTTP_X_PURPOSE']) && nebula()->super->server['HTTP_X_PURPOSE'] === 'preview') && (isset(nebula()->super->server['HTTP_USER_AGENT']) && strpos(strtolower(nebula()->super->server['HTTP_USER_AGENT']), 'snapchat') > 0) ): //Check if viewing in Snapchat ?>
				nebula.snapchatPageShown = false;
				function onSnapchatPageShow(){ //Listen for swipe-up for Snapchat users due to preloading. This function is called from Snapchat itself!
					nebula.snapchatPageShown = true;
					nebulaSendGAPageview();
				}
			<?php else: ?>
				nebulaSendGAPageview();
			<?php endif; ?>

			function nebulaSendGAPageview(){
				ga('send', 'pageview', {
					'hitCallback': function(){
						window.performance.mark('(Nebula) Analytics Pageview');
						window.performance.measure('(Nebula) Time to Analytics Pageview', 'navigationStart', '(Nebula) Analytics Pageview');
					}
				});
			}

			<?php do_action('nebula_ga_after_send_pageview'); ?>

			<?php if ( is_404() ): //Track 404 Errors ?>
				var lastReferrer = nebula.session?.referrer || document.referrer || '(Unknown Referrer)';
				ga('send', 'event', '404 Not Found', '<?php echo esc_url(nebula()->requested_url()); ?>', 'Referrer: ' + lastReferrer, {'nonInteraction': true});
			<?php endif; ?>
		</script>

		<script src='https://www.google-analytics.com/analytics.js' async></script>
	<?php else: //If Tracking ID is empty: ?>
		<?php if ( !nebula()->get_option('gtm_id') ): //If GTM ID is also empty, set an empty ga() function to prevent JS errors ?>
			<script>
				function ga(){}
			</script>
		<?php endif; ?>
	<?php endif; ?>

	<?php nebula()->timer('Analytics (Include)', 'end'); ?>
<?php endif; ?>

<?php if ( nebula()->get_option('gtm_id') ): //Google Tag Manager (can be used for more than just tracking) ?>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo nebula()->get_option('gtm_id'); ?>');</script>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('adwords_remarketing_conversion_id') && !is_customize_preview() ): //Google AdWords Remarketing Tag ?>
	<link rel="prefetch" href="//www.googleadservices.com/pagead/conversion.js" />

	<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = <?php echo esc_html(nebula()->get_option('adwords_remarketing_conversion_id')); ?>;
		var google_custom_params = window.google_tag_params;
		var google_remarketing_only = true;
		/* ]]> */
	</script>
	<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('facebook_custom_audience_pixel_id') && !is_customize_preview() ): //Facebook Custom Audience ?>
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

		var hubspotIdentify = <?php echo json_encode(apply_filters('nebula_hubspot_identify', $hubspot_identify)); //Allow other functions to hook into Hubspot identifications ?>;
		hubspotIdentify.cookies = ( window.navigator.cookieEnabled )? '1' : '0';
		hubspotIdentify.screen = window.screen.width + 'x' + window.screen.height + ' (' + window.screen.colorDepth + ' bits)';

		_hsq.push(["identify", hubspotIdentify]);

		<?php do_action('nebula_hubspot_before_send_pageview'); //Hook into for adding more parameters before the pageview is sent. Can override any above identifications too. ?>

		<?php if ( nebula()->get_option('ga_tracking_id') ): //If Google Analytics is used, grab the Client ID before sending the Hubspot pageview ?>
			if ( typeof window.ga === 'function' ){ <?php //If ga() exists get the CID, otherwise don't wait for it and just send the Hubspot pageview ?>
				window.ga(function(tracker){
					_hsq.push(["identify", {
						ga_cid: tracker.get('clientId'),
					}]);

					_hsq.push(['trackPageView']);
				});
			} else {
				_hsq.push(['trackPageView']);
			}
		<?php else: ?>
			_hsq.push(['trackPageView']);
		<?php endif; ?>
	</script>
<?php endif; ?>

<?php do_action('nebula_analytics_end'); //Hook into for adding more tracking scripts/services (or copy this entire file to the child theme and modify it directly). ?>