<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		http_response_code(403);
		die();
	}
?>

<?php if ( nebula()->get_option('ga_tracking_id') ): //Universal Google Analytics ?>
	<script>
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
			author: '<?php echo nebula()->get_option('cd_author'); ?>',
			businessHours: '<?php echo nebula()->get_option('cd_businesshours'); ?>',
			categories: '<?php echo nebula()->get_option('cd_categories'); ?>',
			tags: '<?php echo nebula()->get_option('cd_tags'); ?>',
			contactMethod: '<?php echo nebula()->get_option('cd_contactmethod'); ?>',
			formTiming: '<?php echo nebula()->get_option('cd_formtiming'); ?>',
			firstInteraction: '<?php echo nebula()->get_option('cd_firstinteraction'); ?>',
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
			publishYear: '<?php echo nebula()->get_option('cd_publishyear'); ?>',
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
			if ( is_singular() || is_page() ){
				global $post;

				if ( is_singular() ){
					//Article author
					if ( nebula()->get_option('author_bios') && nebula()->get_option('cd_author') ){
						echo 'ga("set", gaCustomDimensions["author"], "' . get_the_author() . '");';
					}

					//Article's published year
					if ( nebula()->get_option('cd_publishyear') ){
						echo 'ga("set", gaCustomDimensions["publishYear"], "' . get_the_date('Y') . '");';
					}
				}

				if ( !is_front_page() ){ //Don't track cat/tags on front page
					//Categories
					$post_cats = get_the_category();
					if ( !empty($post_cats) ){
						foreach($post_cats as $category){
							$cats[] = $category->name;
						}
						sort($cats);
						$cat_list = implode(', ', $cats);
					} else {
						$cat_list = '(No Categories)';
					}
					echo 'nebula.post.categories = "' . $cat_list . '";';
					if ( nebula()->get_option('cd_categories') ){
						echo 'ga("set", gaCustomDimensions["categories"], "' . $cat_list . '");';
					}

					//Tags
					$post_tags = get_the_tags();
					if ( !empty($post_tags) ){
						foreach( get_the_tags() as $tag ){
							$tags[] = $tag->name;
						}
						sort($tags);
						$tag_list = implode(', ', $tags);
					} else {
						$tag_list = '(No Tags)';
					}
					echo 'nebula.post.tags = "' . $tag_list . '";';
					if ( nebula()->get_option('cd_tags') && !is_front_page() ){
						echo 'ga("set", gaCustomDimensions["tags"], "' . $tag_list . '");';
					}
				}

				//Word Count
				$word_count = str_word_count(strip_tags($post->post_content));
				if ( is_int($word_count) ){
					if ( nebula()->get_option('cm_wordcount') ){
						echo 'ga("set", gaCustomMetrics["wordCount"], ' . $word_count . ');';
					}
					echo 'nebula.post.wordcount = ' . $word_count . ';';
					if ( $word_count < 10 ){
						$word_count_range = '<10 words';
					} elseif ( $word_count < 500 ){
						$word_count_range = '10 - 499 words';
					} elseif ( $word_count < 1000 ){
						$word_count_range = '500 - 999 words';
					} elseif ( $word_count < 1500 ){
						$word_count_range = '1,000 - 1,499 words';
					} elseif ( $word_count < 2000 ){
						$word_count_range = '1,500 - 1,999 words';
					} else {
						$word_count_range = '2,000+ words';
					}
					if ( nebula()->get_option('cd_wordcount') ){
						echo 'ga("set", gaCustomDimensions["wordCount"], "' . $word_count_range . '");';
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
				$usertype = '';
				if ( is_user_logged_in() ){
					$user_info = get_userdata(get_current_user_id());
					$usertype = 'Unknown';
					if ( !empty($user_info->roles) ){
						switch ( $user_info->roles[0] ){
						    case 'administrator':
						    	$usertype = 'Administrator';
						    	break;
						    case 'editor':
						    	$usertype = 'Editor';
						    	break;
						    case 'author':
						    	$usertype = 'Author';
						    	break;
						    case 'contributor':
						    	$usertype = 'Contributor';
						    	break;
						    case 'subscriber':
						    	$usertype = 'Subscriber';
						    	break;
						    default:
						    	$usertype = ucwords($user_info->roles[0]);
						    	break;
						}
					}
				}

				$staff = '';
				if ( nebula()->is_dev() ){
					$staff = ' (Developer)';
				} elseif ( nebula()->is_client() ){
					$staff = ' (Client)';
				}

				if ( !empty($usertype) || !empty($staff) ){
					echo 'ga("set", gaCustomDimensions["role"], "' . $usertype .  $staff . '");';
				}
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

			//First visit timestamp
			if ( nebula()->get_option('cd_firstinteraction') ){
				$first_session = nebula()->get_visitor_datapoint('first_session');
				if ( !empty($first_session) ){
					echo 'ga("set", gaCustomDimensions["firstInteraction"], "' . time() . '");';
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
			queryDimensionIndex: parseInt(gaCustomDimensions['queryString'].replace('dimension', '')),
			indexFilename: 'index.php',
			trailingSlash: 'remove'
		});

		//Autotrack Social Widgets
		ga('require', 'socialWidgetTracker', {
			hitFilter: function(model) {
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
		ga('require', 'impressionTracker', {
			hitFilter: function(model, element){
				if ( jQuery(element).is('form') && !jQuery(element).find('input[name=s]').length ){
					if ( !jQuery(element).hasClass('.ignore-form') && !jQuery(element).find('.ignore-form').length && !jQuery(element).parents('.ignore-form').length ){
						ga('set', gaCustomMetrics['formImpressions'], 1);
					}
				}
			}
		}); //Elements are detected in main.js (or child.js)

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

			var originalBuildHitTask = tracker.get('buildHitTask'); //Grab a reference to the default sendHitTask function.
			tracker.set('buildHitTask', function(model){
				var qt = model.get('queueTime') || 0;

				//Always send hit dimensions with all payloads
				model.set(gaCustomDimensions['hitID'], uuid(), true);
				model.set(gaCustomDimensions['hitTime'], String(new Date-qt), true);
				model.set(gaCustomDimensions['hitType'], model.get('hitType'), true);
				model.set(gaCustomDimensions['timestamp'], localTimestamp(), true);
				model.set(gaCustomDimensions['visibilityState'], document.visibilityState, true);

				//Always make sure events have the page location and title associated with them (in case of session timout)
				if ( model.get('hitType') === 'event' ){
					if ( !model.get('location') ){
						//Send a new pageview if the event does not have contextual data.
						//This could be due to the user resuming after a session timeout without going to a different page or reloading.
						tracker.send('pageview');
					}
				}

				originalBuildHitTask(model); //Send the payload to Google Analytics
			});
		});

		ga('send', 'pageview'); //Send pageview with all custom dimensions and metrics

		//Initialize event tracking listeners
		ga(function(){
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
		});

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
	<script async src='https://www.google-analytics.com/analytics.js'></script>
<?php else: //If Tracking ID is empty: ?>
	<script>
		function ga(){return false;}
		function gaCustomDimensions(){return false;}
		function gaCustomMetrics(){return false;}
		function uuid(){return false;}
		function localTimestamp(){return false;}
	</script>
<?php endif; ?>

<?php if ( nebula()->get_option('adwords_remarketing_conversion_id') ): //Google AdWords Remarketing Tag ?>
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

<?php if ( nebula()->get_option('facebook_custom_audience_pixel_id') ): //Facebook Custom Audience ?>
	<link rel="prefetch" href="//connect.facebook.net/en_US/fbevents.js" />

	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

		<?php if ( nebula()->get_option('visitors_db') ): ?>
			fbq('init', '<?php echo nebula()->get_option('facebook_custom_audience_pixel_id'); ?>', {
				em: '<?php echo nebula()->get_visitor_datapoint('email_address'); ?>',
				fn: '<?php echo nebula()->get_visitor_datapoint('first_name'); ?>',
				ln: '<?php echo nebula()->get_visitor_datapoint('last_name'); ?>',
			});
		<?php else: ?>
			fbq('init', '<?php echo nebula()->get_option('facebook_custom_audience_pixel_id'); ?>');
		<?php endif; ?>

		fbq('track', 'PageView');

		<?php do_action('nebula_fbq_after_track_pageview'); //Hook into for adding more Facebook custom audience tracking. ?>
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo nebula()->get_option('facebook_custom_audience_pixel_id'); ?>&ev=PageView&noscript=1"/></noscript>
<?php endif; ?>