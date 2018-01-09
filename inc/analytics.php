<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		http_response_code(403);
		die();
	}
?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('ga_tracking_id') && !is_customize_preview() ): //Universal Google Analytics ?>
	<script>
		<?php //@todo "Nebula" 0: Consider using the Data Layer for some of the below parameters (declare it here, then push to it below) ?>

		window.GAready = false;

		//Load the alternative async tracking snippet: https://developers.google.com/analytics/devguides/collection/analyticsjs/#alternative_async_tracking_snippet
		window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
		ga('create', '<?php echo nebula()->get_option('ga_tracking_id'); ?>', 'auto'<?php echo ( nebula()->get_option('ga_wpuserid') && is_user_logged_in() )? ', {"userId": "' . get_current_user_id() . '"}': ''; ?>);

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

		<?php
			//Create various custom dimensions and custom metrics in Google Analytics, then store the index ("dimension3", "metric5", etc.) in Nebula Options.
			//Note: Ecommerce dimensions are added in nebula_ecommerce.php
		?>
		gaCustomDimensions = {
			gaCID: '<?php echo nebula()->get_option('cd_gacid'); ?>',
			hitID: '<?php echo nebula()->get_option('cd_hitid'); ?>',
			hitTime: '<?php echo nebula()->get_option('cd_hittime'); ?>',
			hitType: '<?php echo nebula()->get_option('cd_hittype'); ?>',
			hitInteractivity: '<?php echo nebula()->get_option('cd_hitinteractivity'); ?>',
			hitMethod: '<?php echo nebula()->get_option('cd_hitmethod'); ?>',
			deviceMemory: '<?php echo nebula()->get_option('cd_devicememory'); ?>',
			batteryMode: '<?php echo nebula()->get_option('cd_batterymode'); ?>',
			batteryPercent: '<?php echo nebula()->get_option('cd_batterypercent'); ?>',
			network: '<?php echo nebula()->get_option('cd_network'); ?>',
			referrer: '<?php echo nebula()->get_option('cd_referrer'); ?>',
			author: '<?php echo nebula()->get_option('cd_author'); ?>',
			businessHours: '<?php echo nebula()->get_option('cd_businesshours'); ?>',
			categories: '<?php echo nebula()->get_option('cd_categories'); ?>',
			tags: '<?php echo nebula()->get_option('cd_tags'); ?>',
			contactMethod: '<?php echo nebula()->get_option('cd_contactmethod'); ?>',
			formTiming: '<?php echo nebula()->get_option('cd_formtiming'); ?>',
			formFlow: '<?php echo nebula()->get_option('cd_formflow'); ?>',
			windowType: '<?php echo nebula()->get_option('cd_windowtype'); ?>',
			geolocation: '<?php echo nebula()->get_option('cd_geolocation'); ?>',
			geoAccuracy: '<?php echo nebula()->get_option('cd_geoaccuracy'); ?>',
			geoName: '<?php echo nebula()->get_option('cd_geoname'); ?>',
			relativeTime: '<?php echo nebula()->get_option('cd_relativetime'); ?>',
			sessionID: '<?php echo nebula()->get_option('cd_sessionid'); ?>',
			poi: '<?php echo nebula()->get_option('cd_notablepoi'); ?>',
			role: '<?php echo nebula()->get_option('cd_role'); ?>',
			timestamp: '<?php echo nebula()->get_option('cd_timestamp'); ?>',
			userID: '<?php echo nebula()->get_option('cd_userid'); ?>',
			fbID: '<?php echo nebula()->get_option('cd_fbid'); ?>',
			videoWatcher: '<?php echo nebula()->get_option('cd_videowatcher'); ?>',
			eventIntent: '<?php echo nebula()->get_option('cd_eventintent'); ?>',
			wordCount: '<?php echo nebula()->get_option('cd_wordcount'); ?>',
			weather: '<?php echo nebula()->get_option('cd_weather'); ?>',
			temperature: '<?php echo nebula()->get_option('cd_temperature'); ?>',
			publishDate: '<?php echo nebula()->get_option('cd_publishdate'); ?>',
			blocker: '<?php echo nebula()->get_option('cd_blocker'); ?>',
			queryString: '<?php echo nebula()->get_option('cd_querystring'); ?>',
			mqBreakpoint: '<?php echo nebula()->get_option('cd_mqbreakpoint'); ?>',
			mqResolution: '<?php echo nebula()->get_option('cd_mqresolution'); ?>',
			mqOrientation: '<?php echo nebula()->get_option('cd_mqorientation'); ?>',
			visibilityState: '<?php echo nebula()->get_option('cd_visibilitystate'); ?>',
		}

		gaCustomMetrics = {
			serverResponseTime: '<?php echo nebula()->get_option('cm_serverresponsetime'); ?>',
			domReadyTime: '<?php echo nebula()->get_option('cm_domreadytime'); ?>',
			windowLoadedTime: '<?php echo nebula()->get_option('cm_windowloadedtime'); ?>',
			batteryLevel: '<?php echo nebula()->get_option('cm_batterylevel'); ?>',
			formImpressions: '<?php echo nebula()->get_option('cm_formimpressions'); ?>',
			formStarts: '<?php echo nebula()->get_option('cm_formstarts'); ?>',
			formSubmissions: '<?php echo nebula()->get_option('cm_formsubmissions'); ?>',
			notableDownloads: '<?php echo nebula()->get_option('cm_notabledownloads'); ?>',
			engagedReaders: '<?php echo nebula()->get_option('cm_engagedreaders'); ?>',
			pageVisible: '<?php echo nebula()->get_option('cm_pagevisible'); ?>',
			pageHidden: '<?php echo nebula()->get_option('cm_pagehidden'); ?>',
			videoStarts: '<?php echo nebula()->get_option('cm_videostarts'); ?>',
			videoPlaytime: '<?php echo nebula()->get_option('cm_videoplaytime'); ?>',
			videoCompletions: '<?php echo nebula()->get_option('cm_videocompletions'); ?>',
			autocompleteSearches: '<?php echo nebula()->get_option('cm_autocompletesearches'); ?>',
			autocompleteSearchClicks: '<?php echo nebula()->get_option('cm_autocompletesearchclicks'); ?>',
			wordCount: '<?php echo nebula()->get_option('cm_wordcount'); ?>',
			maxScroll: '<?php echo nebula()->get_option('cm_maxscroll'); ?>',
		}

		<?php
			//Original Referrer
			if ( empty($_SESSION['original_referrer']) ){ //Only capture the referrer on the first page of the session (so it doesn't get replaced with an on-site referrer)
				$original_referrer = ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '(none)';
				echo 'ga("set", gaCustomDimensions["referrer"], "' . $original_referrer . '");';

				$_SESSION['original_referrer'] = $original_referrer;
			}

			if ( is_singular() || is_page() ){
				global $post;

				if ( is_singular() ){
					//Article author
					if ( nebula()->get_option('author_bios') && nebula()->get_option('cd_author') ){
						echo 'ga("set", gaCustomDimensions["author"], "' . get_the_author() . '");';
					}

					//Article's published year
					if ( nebula()->get_option('cd_publishdate') ){
						echo 'ga("set", gaCustomDimensions["publishDate"], "' . get_the_date('Y-m-d') . '");';
					}
				}

				if ( nebula()->get_option('cd_categories') ){
					echo 'ga("set", gaCustomDimensions["categories"], nebula.post.categories);';
				}

				if ( nebula()->get_option('cd_tags') ){
					echo 'ga("set", gaCustomDimensions["tags"], nebula.post.tags);';
				}

				//Word Count
				$word_count = nebula()->word_count();
				if ( $word_count ){
					echo 'nebula.post.wordcount = ' . $word_count . ';';

					if ( nebula()->get_option('cm_wordcount') ){
						echo 'ga("set", gaCustomMetrics["wordCount"], nebula.post.wordcount);';
					}

					if ( nebula()->get_option('cd_wordcount') ){
						echo 'ga("set", gaCustomDimensions["wordCount"], "' . nebula()->word_count(array('range' => true)) . '");';
					}
				}
			}

			//Business Open/Closed
			if ( nebula()->business_open() ){
				$business_open = 'During Business Hours';
				echo 'nebula.user.client.businessopen = true;';
			} else {
				$business_open = 'Non-Business Hours';
				echo 'nebula.user.client.businessopen = false;';
			}
			if ( nebula()->get_option('cd_businesshours') ){
				echo 'ga("set", gaCustomDimensions["businessHours"], "' . $business_open . '");';
			}

			//Relative time ("Late Morning", "Early Evening")
			if ( nebula()->get_option('cd_relativetime') ){
				$relative_time = nebula()->relative_time();
				$time_description = implode(' ', $relative_time['description']);
				$time_range = $relative_time['standard'][0] . ':00' . $relative_time['ampm'] . ' - ' . $relative_time['standard'][2] . ':59' . $relative_time['ampm'];
				echo 'ga("set", gaCustomDimensions["relativeTime"], "' . ucwords($time_description) . ' (' . $time_range . ')");';
			}

			//Role
			if ( nebula()->get_option('cd_role') ){
				echo 'ga("set", gaCustomDimensions["role"], "' . nebula()->user_role() . '");';
			}

			//Session ID
			if ( nebula()->get_option('cd_sessionid') ){
				echo 'nebula.session.id = "' . nebula()->nebula_session_id() . '";';
				echo 'ga("set", gaCustomDimensions["sessionID"], nebula.session.id);';
			}

			//WordPress User ID
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( $current_user && nebula()->get_option('cd_userid') ){
					echo 'ga("set", gaCustomDimensions["userID"], "' . $current_user->ID . '");';
					echo 'ga("set", "userId", ' . $current_user->ID . ');';
				}
			}

			//Weather Conditions
			if ( nebula()->get_option('cd_weather') ){
				echo 'ga("set", gaCustomDimensions["weather"], "' . nebula()->weather('conditions') . '");';
			}
			//Temperature Range
			if ( nebula()->get_option('cd_temperature') ){
				$temp_round = floor(nebula()->weather('temperature')/5)*5;
				$temp_round_celcius = round(($temp_round-32)/1.8);
				$temp_range = strval($temp_round) . '°F - ' . strval($temp_round+4) . '°F (' . strval($temp_round_celcius) . '°C - ' . strval($temp_round_celcius+2) . '°C)';
				echo 'ga("set", gaCustomDimensions["temperature"], "' . $temp_range . '");';
			}

			//Notable POI (IP Addresses)
			if ( nebula()->get_option('cd_notablepoi') ){
				echo 'ga("set", gaCustomDimensions["poi"], "' . nebula()->poi() . '");';
			}
		?>

		//Window Type
		if ( window !== window.top ){
			jQuery('html').addClass('in-iframe');
			ga('set', gaCustomDimensions['windowType'], 'Iframe: ' + window.top.location.href);
		}
		if ( navigator.standalone || window.matchMedia('(display-mode: standalone)').matches ){
			jQuery('html').addClass('in-standalone-app');
			ga('set', gaCustomDimensions['windowType'], 'Standalone App');
		}

		//Autotrack Page Visibility
		if ( gaCustomMetrics['pageHidden'] && gaCustomMetrics['pageVisible'] ){
			ga('require', 'pageVisibilityTracker', {
				hiddenMetricIndex: parseInt(gaCustomMetrics['pageHidden'].replace('metric', '')),
				visibleMetricIndex: parseInt(gaCustomMetrics['pageVisible'].replace('metric', '')),
				fieldsObj: {nonInteraction: true}
			});
		}

		//Autotrack Clean URL
		var queryStringDimension = parseInt(gaCustomDimensions['queryString'].replace('dimension', ''));
		ga('require', 'cleanUrlTracker', {
			stripQuery: ( queryStringDimension )? true : false,
			queryDimensionIndex: queryStringDimension,
			queryParamsWhitelist: ['s', 'rs'],
			indexFilename: 'index.php',
			trailingSlash: 'add'
		});

		//Autotrack Social Widgets
		ga('require', 'socialWidgetTracker', {
			hitFilter: function(model){
				model.set('hitType', 'event'); //Change the hit type from `social` to `event`.

				//Map the social values to event values.
				model.set('eventCategory', model.get('socialNetwork'));
				model.set('eventAction', model.get('socialAction'));
				model.set('eventLabel', model.get('socialTarget'));

				//Unset the social values.
				model.set('socialNetwork', null);
				model.set('socialAction', null);
				model.set('socialTarget', null);
			}
		});

		//Autotrack Media Queries
		if ( gaCustomDimensions['mqBreakpoint'] || gaCustomDimensions['mqResolution'] || gaCustomDimensions['mqOrientation'] ){
			ga('require', 'mediaQueryTracker', {
				definitions: [{
					name: 'Breakpoint',
					dimensionIndex: parseInt(gaCustomDimensions['mqBreakpoint'].replace('dimension', '')),
					items: [
						{name: 'xs', media: 'all'},
						{name: 'sm', media: '(min-width: 544px)'},
						{name: 'md', media: '(min-width: 768px)'},
						{name: 'lg', media: '(min-width: 992px)'},
						{name: 'xl', media: '(min-width: 1200px)'}
					]
				}, {
					name: 'Resolution',
					dimensionIndex: parseInt(gaCustomDimensions['mqResolution'].replace('dimension', '')),
					items: [
						{name: '1x', media: 'all'},
						{name: '1.5x', media: '(min-resolution: 144dpi)'},
						{name: '2x', media: '(min-resolution: 192dpi)'}
					]
				}, {
					name: 'Orientation',
					dimensionIndex: parseInt(gaCustomDimensions['mqOrientation'].replace('dimension', '')),
					items: [
						{name: 'landscape', media: '(orientation: landscape)'},
						{name: 'portrait', media: '(orientation: portrait)'}
					]
				}],
				fieldsObj: {nonInteraction: true}
			});
		}

		//Autotrack Impressions (Scroll into view)
		//Elements themselves are detected in main.js (or child.js)
		ga('require', 'impressionTracker', {
			hitFilter: function(model, element){
				if ( jQuery(element).is('form') && !jQuery(element).find('input[name=s]').length ){
					if ( !jQuery(element).hasClass('.ignore-form') && !jQuery(element).find('.ignore-form').length && !jQuery(element).parents('.ignore-form').length ){
						ga('set', gaCustomMetrics['formImpressions'], 1);
					}
				}
			}
		});

		//Autotrack Max Scroll
		ga('require', 'maxScrollTracker', {
			maxScrollMetricIndex: parseInt(gaCustomMetrics['maxScroll'].replace('metric', '')),
			hitFilter: function(model){
				model.set('nonInteraction', true, true); //Set non-interaction to true (prevent scrolling affecting bounce rate)
			},
		});

		//Autotrack Outbound Links
		ga('require', 'outboundLinkTracker', {
			events: ['click', 'auxclick', 'contextmenu']
		});

		<?php if ( nebula()->get_option('google_optimize_id') ): //Google Optimize ?>
			ga('require', '<?php echo nebula()->get_option('google_optimize_id'); ?>');
		<?php endif; ?>

		<?php do_action('nebula_ga_before_send_pageview'); //Hook into for adding more custom definitions before the pageview hit is sent. Can override any above definitions too. ?>

		//Modify the payload before sending data to Google Analytics
		ga(function(tracker){
			tracker.set(gaCustomDimensions['gaCID'], tracker.get('clientId'));

			if ( nebula && nebula.session && nebula.session.id ){
				nebula.session.id = nebula.session.id.replace(/;cid:(.+);/i, ';cid:' + tracker.get('clientId') + ';'); //Update the CID once assigned
			}

			var originalBuildHitTask = tracker.get('buildHitTask'); //Grab a reference to the default buildHitTask function.
			tracker.set('buildHitTask', function(model){ //This runs on every hit send
				var qt = model.get('queueTime') || 0;

				<?php if ( nebula()->get_option('ga_session_timeout_minutes') && intval(nebula()->get_option('ga_session_timeout_minutes')) >= 5 ): //Send new pageview after session timeout expires ?>
					if ( model.get('hitType') !== 'pageview' && typeof lastHit === 'object' ){
						var currentHit = new Date();
						if ( (currentHit-lastHit) > (<?php echo nebula()->get_option('ga_session_timeout_minutes'); ?>*60000) ){ //If after GA session timeout
							model.set('campaignSource', '(session timeout)');
							ga('send', 'pageview');
						}
					}
					lastHit = new Date(); //Update the last GA hit time
				<?php endif; ?>

				//Move impression tracking for CF7 forms to the "CF7 Form" event category //@todo "Nebula" 0: If the fieldsObj is ever updated in Autotrack, do this programmatically in main.js
				if ( model.get('hitType') === 'event' && model.get('eventAction') === 'impression' && model.get('eventLabel').indexOf('wpcf7') > -1 ){
					model.set('eventCategory', 'CF7 Form', true);
				}

				//Always send hit dimensions with all payloads
				//model.set(gaCustomDimensions['gaCID'], tracker.get('clientId'), true);
				model.set(gaCustomDimensions['hitID'], uuid(), true);
				model.set(gaCustomDimensions['hitTime'], String(new Date-qt), true);
				model.set(gaCustomDimensions['hitType'], model.get('hitType'), true);

				var interactivity = 'Interaction';
				if ( model.get('nonInteraction') ){
					interactivity = 'Non-Interaction';
				}
				model.set(gaCustomDimensions['interactivity'], interactivity, true);

				var transportMethod = model.get('transport') || 'JavaScript';
				model.set(gaCustomDimensions['hitMethod'], model.get('transport'), true);

				model.set(gaCustomDimensions['timestamp'], localTimestamp(), true);
				model.set(gaCustomDimensions['visibilityState'], document.visibilityState, true);

				var connection = ( navigator.onLine )? 'Online' : 'Offline';
				model.set(gaCustomDimensions['network'], connection, true);

				if ( 'deviceMemory' in navigator ){ //Chrome 64+
					var deviceMemoryLevel = navigator.deviceMemory < 1 ? 'lite' : 'full';
					model.set(gaCustomDimensions['deviceMemory'], navigator.deviceMemory + '(' + deviceMemoryLevel + ')', true);
				} else {
					model.set(gaCustomDimensions['deviceMemory'], '(not set)', true);
				}

				originalBuildHitTask(model); //Send the payload to Google Analytics
			});
		});

		<?php if ( (isset($_SERVER['HTTP_X_PURPOSE']) && $_SERVER['HTTP_X_PURPOSE'] === 'preview') && (isset($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'snapchat') > 0) ): //Check if viewing in Snapchat ?>
			window.snapchatPageShown = false;
			function onSnapchatPageShow(){ //Listen for swipe-up for Snapchat users due to preloading. This function is called from Snapchat itself!
				window.snapchatPageShown = true;
				nebulaSendGAPageview();
			}
		<?php else: ?>
			nebulaSendGAPageview();
		<?php endif; ?>

		function nebulaSendGAPageview(){
			ga('send', 'pageview', {
				'hitCallback': function(){
					window.GAready = true; //Set a global boolean variable
					document.dispatchEvent(new Event('gaready')); //Trigger an event when GA is ready (without jQuery)

					if ( typeof initEventTracking === 'function' ){
						initEventTracking();
					}

					<?php if ( is_child_theme() ): ?>
						if ( typeof supplementalEventTracking === 'function' ){
							supplementalEventTracking();
						}
					<?php endif; ?>
				}
			});
		}

		<?php do_action('nebula_ga_after_send_pageview'); ?>

		<?php if ( !nebula()->is_bot() && ( nebula()->get_option('adblock_detect') ) ): //Detect Ad Blockers (After pageview because asynchronous- uses GA event). ?>
			jQuery.ajaxSetup({cache: true});
			jQuery.getScript(nebula.site.directory.template.uri + '/assets/js/vendor/show_ads.js').done(function(){
				if ( nebula.session.flags ){
					nebula.session.flags.adblock = 'false';
				}
			}).fail(function(){ <?php //Ad Blocker Detected ?>
				jQuery('html').addClass('ad-blocker');
				<?php if ( nebula()->get_option('cd_blocker') ): //Scope: Session. Note: this is set AFTER the pageview is already sent (due to async), so it needs the event below. ?>
					ga('set', gaCustomDimensions['blocker'], 'Ad Blocker');
				<?php endif; ?>

				if ( nebula.session.flags && nebula.session.flags.adblock !== 'true' ){
					ga('send', 'event', 'Ad Blocker', 'Blocked', 'This user is using ad blocking software.', {'nonInteraction': true}); //Uses an event because it is asynchronous!
					nebula.session.flags.adblock = 'true';
				}
			});
		<?php endif; ?>

		//Generate a unique ID for hits and windows
		function uuid(a){
			return a ? (a^Math.random()*16 >> a/4).toString(16) : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, uuid);
		}

		//Get local time string with timezone offset
		function localTimestamp(){
			var now = new Date();
			var tzo = -now.getTimezoneOffset();
			var dif = ( tzo >= 0 )? '+' : '-';
			var pad = function(num){
				var norm = Math.abs(Math.floor(num));
				return (( norm < 10 )? '0' : '') + norm;
			};
			return Math.round(now/1000) + ' (' + now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()) + '.' + pad(now.getMilliseconds()) + ' UTC' + dif + pad(tzo/60) + ':' + pad(tzo%60) + ')';
		}
	</script>

	<script src='https://www.google-analytics.com/analytics.js' async></script>
<?php else: //If Tracking ID is empty: ?>
	<script>
		window.GAready = true; <?php //Set to true to prevent AJAX Google Analytics data ?>
		function ga(){return false;}
		function gaCustomDimensions(){return false;}
		function gaCustomMetrics(){return false;}
		function uuid(){return false;}
		function localTimestamp(){return false;}
	</script>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('gtm_id') && !is_customize_preview() ): //Google Tag Manager ?>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo nebula()->get_option('gtm_id'); ?>');</script>
	<!-- End Google Tag Manager -->

	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo nebula()->get_option('gtm_id'); ?>"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('adwords_remarketing_conversion_id') && !is_customize_preview() ): //Google AdWords Remarketing Tag ?>
	<link rel="prefetch" href="//www.googleadservices.com/pagead/conversion.js" />

	<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = <?php echo nebula()->get_option('adwords_remarketing_conversion_id'); ?>;
		var google_custom_params = window.google_tag_params;
		var google_remarketing_only = true;
		/* ]]> */
	</script>
	<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
		<div style="display:inline;">
			<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/<?php echo nebula()->get_option('adwords_remarketing_conversion_id'); ?>/?value=0&amp;guid=ON&amp;script=0"/>
		</div>
	</noscript>
<?php endif; ?>

<?php if ( nebula()->is_analytics_allowed() && nebula()->get_option('facebook_custom_audience_pixel_id') && !is_customize_preview() ): //Facebook Custom Audience ?>
	<link rel="prefetch" href="//connect.facebook.net/en_US/fbevents.js" />

	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

		fbq('init', '<?php echo nebula()->get_option('facebook_custom_audience_pixel_id'); ?>'); //@todo "Nebula" 0: Can we *get* data from Hubspot to send email and other info here?
		fbq('track', 'PageView');

		<?php do_action('nebula_fbq_after_track_pageview'); //Hook into for adding more Facebook custom audience tracking. ?>
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo nebula()->get_option('facebook_custom_audience_pixel_id'); ?>&ev=PageView&noscript=1"/></noscript>
<?php endif; ?>

<?php if ( nebula()->get_option('hubspot_portal') ): //Hubspot CRM ?>
	<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/<?php echo nebula()->get_option('hubspot_portal'); ?>.js"></script>
	<script>
		var _hsq = window._hsq = window._hsq || [];
		_hsq.push(['setPath', '<?php echo str_replace(get_site_url(), '', get_permalink()); ?>']); //Is this even needed?

		_hsq.push(["identify", {
			ipaddress: '<?php echo $_SERVER['REMOTE_ADDR']; ?>',
			user_agent: '<?php echo $_SERVER['HTTP_USER_AGENT']; ?>',
			session_id: '<?php echo nebula()->nebula_session_id(); //If this hits rate limits, consider removing it ?>',
		}]);

		<?php if ( is_user_logged_in() ): //if logged into wordpress ?>
			<?php $user_info = get_userdata(get_current_user_id()); ?>

			_hsq.push(["identify", {
				email: '<?php echo $user_info->user_email; ?>',
				firstname: '<?php echo $user_info->first_name; ?>',
				lastname: '<?php echo $user_info->last_name; ?>',
				id: '<?php echo get_current_user_id(); ?>',
				username: '<?php echo $user_info->user_login; ?>',
				role: '<?php echo nebula()->user_role(); ?>',
				jobtitle: '<?php echo get_user_meta(get_current_user_id(), 'jobtitle', true); ?>',
				company: '<?php echo get_user_meta(get_current_user_id(), 'jobcompany', true); ?>',
				website: '<?php echo get_user_meta(get_current_user_id(), 'jobcompanywebsite', true); ?>',
				city: '<?php echo get_user_meta(get_current_user_id(), 'usercity', true); ?>',
				state: '<?php echo get_user_meta(get_current_user_id(), 'userstate', true); ?>',
				phone: '<?php echo get_user_meta(get_current_user_id(), 'phonenumber', true); ?>',
				notable_poi: '<?php echo nebula()->poi(); ?>',
				cookies: ( window.navigator.cookieEnabled )? '1' : '0',
				screen: window.screen.width + 'x' + window.screen.height + ' (' + window.screen.colorDepth + ' bits)',
			}]);
		<?php endif; ?>

		<?php if ( nebula()->get_option('device_detection') ): ?>
			_hsq.push(["identify", {
				device: '<?php echo nebula()->get_device(); ?>',
				os: '<?php echo nebula()->get_os(); ?>',
				browser: '<?php echo nebula()->get_browser(); ?>',
				bot: '<?php echo ( nebula()->is_bot() )? 1 : 0; ?>',
			}]);
		<?php endif; ?>

		<?php do_action('nebula_hubspot_before_send_pageview'); //Hook into for adding more parameters before the pageview is sent. Can override any above identifications too. ?>

		<?php if ( nebula()->get_option('ga_tracking_id') ): //If Google Analytics is used, grab the Client ID before sending the Hubspot pageview ?>
			if ( typeof window.ga === 'function' ){ //If ga() exists get the CID, otherwise don't wait for it and just send the Hubspot pageview
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