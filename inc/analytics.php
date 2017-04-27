<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		http_response_code(403);
		die();
	}
?>

<?php if ( nebula()->option('ga_tracking_id') ): //Universal Google Analytics ?>
	<script>
		window.GAready = false;

		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/<?php echo ( nebula()->is_debug(1) )? 'analytics_debug.js' : 'analytics.js'; ?>','ga');

		ga('create', '<?php echo nebula()->option('ga_tracking_id'); ?>', 'auto'<?php echo ( nebula()->option('ga_wpuserid', 'enabled') && is_user_logged_in() )? ', {"userId": "' . get_current_user_id() . '"}': ''; ?>);

		ga(function(){
			window.GAready = true;
			if ( typeof initEventTracking === 'function' ){
				initEventTracking();
			}
		});

		<?php if ( nebula()->option('ga_displayfeatures', 'enabled') ): ?>
			ga('require', 'displayfeatures');
		<?php endif; ?>

		<?php if ( nebula()->option('ga_linkid', 'enabled') ): ?>
			ga('require', 'linkid');
		<?php endif; ?>

		<?php
			//Create various custom dimensions and custom metrics in Google Analytics, then store the index ("dimension3", "metric5", etc.) in Nebula Options.
			//Note: Ecommerce dimensions are added in nebula_ecommerce.php
		?>
		gaCustomDimensions = {
			author: '<?php echo nebula()->option('cd_author'); ?>',
			businessHours: '<?php echo nebula()->option('cd_businesshours'); ?>',
			categories: '<?php echo nebula()->option('cd_categories'); ?>',
			tags: '<?php echo nebula()->option('cd_tags'); ?>',
			contactMethod: '<?php echo nebula()->option('cd_contactmethod'); ?>',
			formTiming: '<?php echo nebula()->option('cd_formtiming'); ?>',
			firstInteraction: '<?php echo nebula()->option('cd_firstinteraction'); ?>',
			windowType: '<?php echo nebula()->option('cd_windowtype'); ?>',
			geolocation: '<?php echo nebula()->option('cd_geolocation'); ?>',
			geoAccuracy: '<?php echo nebula()->option('cd_geoaccuracy'); ?>',
			geoName: '<?php echo nebula()->option('cd_geoname'); ?>',
			relativeTime: '<?php echo nebula()->option('cd_relativetime'); ?>',
			scrollDepth: '<?php echo nebula()->option('cd_scrolldepth'); ?>',
			sessionID: '<?php echo nebula()->option('cd_sessionid'); ?>',
			poi: '<?php echo nebula()->option('cd_notablepoi'); ?>',
			role: '<?php echo nebula()->option('cd_role'); ?>',
			timestamp: '<?php echo nebula()->option('cd_timestamp'); ?>',
			userID: '<?php echo nebula()->option('cd_userid'); ?>',
			fbID: '<?php echo nebula()->option('cd_fbid'); ?>',
			videoWatcher: '<?php echo nebula()->option('cd_videowatcher'); ?>',
			eventIntent: '<?php echo nebula()->option('cd_eventintent'); ?>',
			wordCount: '<?php echo nebula()->option('cd_wordcount'); ?>',
			weather: '<?php echo nebula()->option('cd_weather'); ?>',
			temperature: '<?php echo nebula()->option('cd_temperature'); ?>',
			publishYear: '<?php echo nebula()->option('cd_publishyear'); ?>',
			adBlocker: '<?php echo nebula()->option('cd_adblocker'); ?>',
			mqBreakpoint: '<?php echo nebula()->option('cd_mqbreakpoint'); ?>',
			mqResolution: '<?php echo nebula()->option('cd_mqresolution'); ?>',
			mqOrientation: '<?php echo nebula()->option('cd_mqorientation'); ?>',
		}

		gaCustomMetrics = {
			formPageviews: '<?php echo nebula()->option('cm_formpageviews'); ?>',
			formImpressions: '<?php echo nebula()->option('cm_formimpressions'); ?>',
			formStarts: '<?php echo nebula()->option('cm_formstarts'); ?>',
			formSubmissions: '<?php echo nebula()->option('cm_formsubmissions'); ?>',
			notableDownloads: '<?php echo nebula()->option('cm_notabledownloads'); ?>',
			engagedReaders: '<?php echo nebula()->option('cm_engagedreaders'); ?>',
			pageVisible: '<?php echo nebula()->option('cm_pagevisible'); ?>',
			pageHidden: '<?php echo nebula()->option('cm_pagehidden'); ?>',
			videoStarts: '<?php echo nebula()->option('cm_videostarts'); ?>',
			videoPlaytime: '<?php echo nebula()->option('cm_videoplaytime'); ?>',
			videoCompletions: '<?php echo nebula()->option('cm_videocompletions'); ?>',
			autocompleteSearches: '<?php echo nebula()->option('cm_autocompletesearches'); ?>',
			autocompleteSearchClicks: '<?php echo nebula()->option('cm_autocompletesearchclicks'); ?>',
			wordCount: '<?php echo nebula()->option('cm_wordcount'); ?>',
			maxScroll: '<?php echo nebula()->option('cm_maxscroll'); ?>',
		}

		<?php
			if ( is_singular() || is_page() ){
				global $post;

				if ( is_singular() ){
					//Article author
					if ( nebula()->option('author_bios', 'enabled') && nebula()->option('cd_author') ){
						echo 'ga("set", gaCustomDimensions["author"], "' . get_the_author() . '");';
					}

					//Article's published year
					if ( nebula()->option('cd_publishyear') ){
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
					if ( nebula()->option('cd_categories') ){
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
					if ( nebula()->option('cd_tags') && !is_front_page() ){
						echo 'ga("set", gaCustomDimensions["tags"], "' . $tag_list . '");';
					}
				}

				//Word Count
				$word_count = str_word_count(strip_tags($post->post_content));
				if ( is_int($word_count) ){
					if ( nebula()->option('cm_wordcount') ){
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
					if ( nebula()->option('cd_wordcount') ){
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
			if ( nebula()->option('cd_businesshours') ){
				echo 'ga("set", gaCustomDimensions["businessHours"], "' . $business_open . '");';
			}

			//Relative time ("Late Morning", "Early Evening")
			if ( nebula()->option('cd_relativetime') ){
				$relative_time = nebula()->relative_time();
				$time_description = implode(' ', $relative_time['description']);
				$time_range = $relative_time['standard'][0] . ':00' . $relative_time['ampm'] . ' - ' . $relative_time['standard'][2] . ':59' . $relative_time['ampm'];
				echo 'ga("set", gaCustomDimensions["relativeTime"], "' . ucwords($time_description) . ' (' . $time_range . ')");';
			}

			//Role
			if ( nebula()->option('cd_role') ){
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
			if ( nebula()->option('cd_sessionid') ){
				echo 'nebula.session.id = "' . nebula()->nebula_session_id() . '";';
				echo 'ga("set", gaCustomDimensions["sessionID"], nebula.session.id);';
			}

			//WordPress User ID
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( $current_user && nebula()->option('cd_userid') ){
					echo 'ga("set", gaCustomDimensions["userID"], "' . $current_user->ID . '");';
					echo 'ga("set", "userId", ' . $current_user->ID . ');';
				}
			}

			//User's local timestamp
			if ( nebula()->option('cd_timestamp') ){
				echo 'ga("set", gaCustomDimensions["timestamp"], localTimestamp());';
			}

			//First visit timestamp
			if ( nebula()->option('cd_firstinteraction') ){
				$first_session = nebula()->get_visitor_datapoint('first_session');
				if ( !empty($first_session) ){
					echo 'ga("set", gaCustomDimensions["firstInteraction"], "' . time() . '");';
				}
			}

			//Weather Conditions
			if ( nebula()->option('cd_weather') ){
				echo 'ga("set", gaCustomDimensions["weather"], "' . nebula()->weather('conditions') . '");';
			}
			//Temperature Range
			if ( nebula()->option('cd_temperature') ){
				$temp_round = floor(nebula()->weather('temperature')/5)*5;
				$temp_round_celcius = round(($temp_round-32)/1.8);
				$temp_range = strval($temp_round) . '°F - ' . strval($temp_round+4) . '°F (' . strval($temp_round_celcius) . '°C - ' . strval($temp_round_celcius+2) . '°C)';
				echo 'ga("set", gaCustomDimensions["temperature"], "' . $temp_range . '");';
			}

			//Notable POI (IP Addresses)
			if ( nebula()->option('cd_notablepoi') ){
				echo 'ga("set", gaCustomDimensions["poi"], "' . nebula()->poi() . '");';
			}
		?>

		<?php if ( nebula()->option('cm_formpageviews') ): //Notable Form Views (to calculate against submissions) ?>
			if ( !jQuery('form').find('input[name=s]').length && !jQuery('form').hasClass('.ignore-form') && !jQuery('form').find('.ignore-form').length && !jQuery('form').parents('.ignore-form').length ){
				ga('set', gaCustomMetrics['formPageviews'], 1);
			}
		<?php endif; ?>

		<?php if ( !nebula()->is_bot() && ( nebula()->option('adblock_detect') || nebula()->option('cd_adblocker') ) ): //Detect Ad Blockers. ?>
			jQuery.getScript(nebula.site.directory.template.uri + '/assets/js/vendor/show_ads.js').done(function(){
				adBlockUser = 'No Ad Blocker';
				if ( nebula.session.flags ){
					nebula.session.flags.adblock = 'false';
				}
			}).fail(function(){ <?php //Ad Blocker Detected ?>
				jQuery('html').addClass('ad-blocker');
				adBlockUser = 'Ad Blocker Detected';
				<?php if ( nebula()->option('cd_adblocker') ): //Scope: Session ?>
					ga('set', gaCustomDimensions['adBlocker'], adBlockUser); <?php //Note: this is set AFTER the pageview is already sent (due to async), so it needs the event below. ?>
				<?php endif; ?>

				if ( nebula.session.flags && nebula.session.flags.adblock !== 'true' ){
					ga('send', 'event', adBlockUser, 'This user is using ad blocking software.'); //Might need to move this into main.js and check against the below flag
					nebula.session.flags.adblock = 'true';
				}
			});
		<?php endif; ?>

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
		} else {
			ga('require', 'pageVisibilityTracker', {
				fieldsObj: {nonInteraction: true}
			});
		}

		//Autotrack Clean URL
		ga('require', 'cleanUrlTracker');

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
				if ( model.get('eventLabel') > 65 ){
					model.set('nonInteraction', true, true); //Set non-interaction to true (prevent scrolling affecting bounce rate)
				}

			},
		});

		//Autotrack Outbound Links
		ga('require', 'outboundLinkTracker', {
			events: ['click', 'auxclick', 'contextmenu']
		});

		<?php if ( nebula()->option('google_optimize_id') ): //Google Optimize ?>
			ga('require', '<?php echo nebula()->option('google_optimize_id'); ?>');
		<?php endif; ?>

		<?php do_action('nebula_ga_before_send_pageview'); //Hook into for adding more custom definitions before the pageview hit is sent. Can override any above definitions too. ?>

		ga('send', 'pageview'); //Send pageview with all custom dimensions and metrics

		<?php do_action('nebula_ga_after_send_pageview'); ?>

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
<?php else: //If Tracking ID is empty: ?>
	<script>
		function ga(){return false;}
		function gaCustomDimensions(){return false;}
		function gaCustomMetrics(){return false;}
		function localTimestamp(){return false;}
	</script>
<?php endif; ?>

<?php if ( nebula()->option('adwords_remarketing_conversion_id') ): //Google AdWords Remarketing Tag ?>
	<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = <?php echo nebula()->option('adwords_remarketing_conversion_id'); ?>;
		var google_custom_params = window.google_tag_params;
		var google_remarketing_only = true;
		/* ]]> */
	</script>
	<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
		<div style="display:inline;">
			<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/<?php echo nebula()->option('adwords_remarketing_conversion_id'); ?>/?value=0&amp;guid=ON&amp;script=0"/>
		</div>
	</noscript>
<?php endif; ?>

<?php if ( nebula()->option('facebook_custom_audience_pixel_id') ): //Facebook Custom Audience ?>
	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

		<?php if ( nebula()->option('visitors_db') ): ?>
			fbq('init', '<?php echo nebula()->option('facebook_custom_audience_pixel_id'); ?>', {
				em: '<?php echo nebula()->get_visitor_datapoint('email_address'); ?>',
				fn: '<?php echo nebula()->get_visitor_datapoint('first_name'); ?>',
				ln: '<?php echo nebula()->get_visitor_datapoint('last_name'); ?>',
			});
		<?php else: ?>
			fbq('init', '<?php echo nebula()->option('facebook_custom_audience_pixel_id'); ?>');
		<?php endif; ?>

		fbq('track', 'PageView');

		<?php do_action('nebula_fbq_after_track_pageview'); //Hook into for adding more Facebook custom audience tracking. ?>
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo nebula()->option('facebook_custom_audience_pixel_id'); ?>&ev=PageView&noscript=1"/></noscript>
<?php endif; ?>