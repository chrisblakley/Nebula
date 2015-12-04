<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		die('Error 403: Forbidden.');
	}
?>

<?php if ( !empty($GLOBALS['ga']) ): //Universal Google Analytics ?>
	<script>
		<?php //@TODO "Analytics" 5: Admin > View Settings - Turn on Site Search Tracking and enter "s,rs" in the Query Parameter input field! ?>

		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/<?php echo ( is_debug(1) )? 'analytics_debug.js' : 'analytics.js'; ?>','ga');

		ga('create', '<?php echo $GLOBALS['ga']; ?>', 'auto'); <?php //Change Tracking ID in Nebula Options or functions.php! ?>

		<?php if ( nebula_is_option_enabled('nebula_ga_displayfeatures') ): ?>
			ga('require', 'displayfeatures');
		<?php endif; ?>

		<?php if ( nebula_is_option_enabled('nebula_ga_linkid') ): ?>
			ga('require', 'linkid');
		<?php endif; ?>

		<?php //Create various custom dimensions and custom metrics in Google Analytics, then store the index ("dimension3", "metric5", etc.) in Nebula Options. ?>
		gaCustomDimensions = {
			author: '<?php echo nebula_option('nebula_cd_author'); //Hit ?>',
			businessHours: '<?php echo nebula_option('nebula_cd_businesshours'); //Hit ?>',
			categories: '<?php echo nebula_option('nebula_cd_categories'); //Hit ?>',
			tags: '<?php echo nebula_option('nebula_cd_tags'); //Hit ?>',
			contactMethod: '<?php echo nebula_option('nebula_cd_contactmethod'); //Session ?>',
			geolocation: '<?php echo nebula_option('nebula_cd_geolocation'); //Session ?>',
			geoAccuracy: '<?php echo nebula_option('nebula_cd_geoaccuracy'); //Session ?>',
			geoName: '<?php echo nebula_option('nebula_cd_geoname'); //Session ?>',
			notablebrowser: '<?php echo nebula_option('nebula_cd_notablebrowser'); //Session ?>',
			relativeTime: '<?php echo nebula_option('nebula_cd_relativetime'); //Hit ?>',
			scrollDepth: '<?php echo nebula_option('nebula_cd_scrolldepth'); //Hit ?>',
			maxScroll: '<?php echo nebula_option('nebula_cd_maxscroll'); //Hit ?>',
			sessionID: '<?php echo nebula_option('nebula_cd_sessionid'); //Session ?>',
			staff: '<?php echo nebula_option('nebula_cd_staff'); //User ?>',
			timestamp: '<?php echo nebula_option('nebula_cd_timestamp'); //Hit ?>',
			userID: '<?php echo nebula_option('nebula_cd_userid'); //User ?>',
			videoWatcher: '<?php echo nebula_option('nebula_cd_videowatcher'); //Session ?>',
			eventIntent: '<?php echo nebula_option('nebula_cd_eventintent'); //Hit ?>',
			wordCount: '<?php echo nebula_option('nebula_cd_wordcount'); //Hit ?>',
			weather: '<?php echo nebula_option('nebula_cd_weather'); //Hit ?>',
			temperature: '<?php echo nebula_option('nebula_cd_temperature'); //Hit ?>',
			publishYear: '<?php echo nebula_option('nebula_cd_publishyear'); //Hit ?>',
			adBlocker: '<?php echo nebula_option('nebula_adblocker'); //User ?>',
		}

		gaCustomMetrics = {
			notableFormViews: '<?php echo nebula_option('nebula_cm_notableformviews'); //Hit, Integer ?>',
			notableFormStarts: '<?php echo nebula_option('nebula_cm_notableformstarts'); //Hit, Integer ?>',
			notableFormSubmissions: '<?php echo nebula_option('nebula_cm_notableformsubmissions'); //Hit, Integer ?>',
			notableDownloads: '<?php echo nebula_option('nebula_cm_notabledownloads'); //Hit, Integer ?>',
			engagedReaders: '<?php echo nebula_option('nebula_cm_engagedReaders'); //Hit, Integer ?>',
			videoStarts: '<?php echo nebula_option('nebula_cm_videostarts'); //Hit, Integer ?>',
			videoPlaytime: '<?php echo nebula_option('nebula_cm_videoplaytime'); //Hit, Time ?>',
			videoCompletions: '<?php echo nebula_option('nebula_cm_videocompletions'); //Hit, Integer ?>',
			autocompleteSearches: '<?php echo nebula_option('nebula_cm_autocompletesearches'); //Hit, Integer ?>',
			autocompleteSearchClicks: '<?php echo nebula_option('nebula_cm_autocompletesearchclicks'); //Hit, Integer ?>',
			pageSuggestions: '<?php echo nebula_option('nebula_cm_pagesuggestions'); //Hit, Integer ?>',
			pageSuggestionsAccepted: '<?php echo nebula_option('nebula_cm_pagesuggestionsaccepted'); //Hit, Integer ?>',
			wordCount: '<?php echo nebula_option('nebula_cm_wordcount'); //Hit, Integer ?>',
		}

		<?php do_action('nebula_ga_before_send_pageview'); ?>

		<?php
			if ( is_single() || is_page() ){
				global $post;

				if ( is_single() ){
					//Article author
					if ( nebula_is_option_enabled('authorbios') && nebula_option('nebula_cd_author') ){
						echo 'ga("set", gaCustomDimensions["author"], "' . get_the_author() . '");';
					}

					//Article's published year
					if ( nebula_option('nebula_cd_publishyear') ){
						echo 'ga("set", gaCustomDimensions["publishYear"], "' . get_the_date('Y') . '");';
					}
				}

				//Categories
				$post_cats = get_the_category();
				if ( !empty($post_cats) ){
					foreach($post_cats as $category){
						$cats[] = $category->name;
					}
					sort($cats);
					$cat_list = implode(', ', $cats);
				} else {
					$cat_list = 'No Categories';
				}
				echo 'clientinfo.categories = "' . $cat_list . '";';
				if ( nebula_option('nebula_cd_categories') ){
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
					$tag_list = 'No Tags';
				}
				echo 'clientinfo.tags = "' . $tag_list . '";';
				if ( nebula_option('nebula_cd_tags') ){
					echo 'ga("set", gaCustomDimensions["tags"], "' . $tag_list . '");';
				}

				//Word Count
				$word_count = str_word_count(strip_tags($post->post_content));
				if ( is_int($word_count) ){
					if ( nebula_option('nebula_cm_wordcount') ){
						echo 'ga("set", gaCustomMetrics["wordCount"], ' . $word_count . ');';
					}
					echo 'postinfo.wordcount = ' . $word_count . ';';
					if ( $word_count < 500 ){
						$word_count_range = '<500 words';
					} elseif ( $word_count < 1000 ){
						$word_count_range = '500 - 999 words';
					} elseif ( $word_count < 1500 ){
						$word_count_range = '1,000 - 1,499 words';
					} elseif ( $word_count < 2000 ){
						$word_count_range = '1,500 - 1,999 words';
					} else {
						$word_count_range = '2,000+ words';
					}
					if ( nebula_option('nebula_cd_wordcount') ){
						echo 'ga("set", gaCustomDimensions["wordCount"], "' . $word_count_range . '");';
					}
				}
			}

			//Business Open/Closed
			if ( business_open() ){
				$business_open = 'During Business Hours';
				echo 'clientinfo.businessopen = true;';
			} else {
				$business_open = 'Non-Business Hours';
				echo 'clientinfo.businessopen = false;';
			}
			if ( nebula_option('nebula_cd_businesshours') ){
				echo 'ga("set", gaCustomDimensions["businessHours"], "' . $business_open . '");';
			}

			//Relative time ("Late Morning", "Early Evening")
			if ( nebula_option('nebula_cd_relativetime') ){
				$relative_time = nebula_relative_time();
				$time_description = implode(' ', $relative_time['description']);
				$time_range = $relative_time['standard'][0] . ':00' . $relative_time['ampm'] . ' - ' . $relative_time['standard'][2] . ':59' . $relative_time['ampm'];
				echo 'ga("set", gaCustomDimensions["relativeTime"], "' . ucwords($time_description) . ' (' . $time_range . ')");';
			}

			//Session ID
			if ( nebula_option('nebula_cd_sessionid') ){
				$session_info = ( is_debug() )? 'Dbg.' : '';
				$session_info .= ( nebula_is_option_enabled('wireframing') )? 'Wr.' : '';
				if ( is_client() ){
					$session_info .= 'Cl.';
				} elseif ( is_dev() ){
					$session_info .= 'Dv.';
				}
				$session_info .= ( is_user_logged_in() )? 'Li.' : '';
				$session_info .= ( nebula_is_bot() )? 'Bt.' : '';

				echo 'clientinfo.sessionid = new Date().getTime() + ".' . $session_info . '" + Math.random().toString(36).substring(5);';
				echo 'ga("set", gaCustomDimensions["sessionID"], clientinfo.sessionid);';
			}

			//WordPress User ID
			$current_user = wp_get_current_user();
			if ( $current_user && nebula_option('nebula_cd_userid') ){
				echo 'ga("set", gaCustomDimensions["userID"], "' . $current_user->ID . '");';
			}

			//Staff
			if ( nebula_option('nebula_cd_staff') ){
				$skip = false;
				if ( is_dev() ){
					$usertype = 'Developer';
				} elseif ( is_client() ){
					$usertype = 'Client';
				} elseif ( is_user_logged_in() ){
					$user_info = get_userdata(get_current_user_id());
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
					    	$skip = true;
					    	break;
					    default:
					    	$usertype = 'Logged-In';
					    	break;
					}
				}
				if ( !$skip ){
					echo 'ga("set", gaCustomDimensions["staff"], "' . $usertype . '");';
				}
			}

			//ISO Timestamp
			if ( nebula_option('nebula_cd_timestamp') ){
				echo 'ga("set", gaCustomDimensions["timestamp"], isoTimestamp());';
			}

			//Weather Conditions
			if ( nebula_option('nebula_cd_weather') ){
				echo 'ga("set", gaCustomDimensions["weather"], "' . nebula_weather('conditions') . '");';
			}
			//Temperature Range
			if ( nebula_option('nebula_cd_temperature') ){
				$temp_round = floor(nebula_weather('temperature')/5)*5;
				$temp_round_celcius = round(($temp_round-32)/1.8);
				$temp_range = strval($temp_round) . '°F - ' . strval($temp_round+4) . '°F (' . strval($temp_round_celcius) . '°C - ' . strval($temp_round_celcius+2) . '°C)';
				echo 'ga("set", gaCustomDimensions["temperature"], "' . $temp_range . '");';
			}
		?>

		<?php //Detect Ad Blockers. Our local show_ads.js only assigns adsEnabled variable to true. Best synchronous method of ad block detection. ?>
		adBlockUser = 'Non-Blocker';
		clientinfo.adblock = false;
		jQuery('html').removeClass('no-ads').addClass('ads');
		if ( window.adsEnabled === undefined ){
			clientinfo.adblock = true;
			adBlockUser = 'Ad Blocker';
			jQuery('html').removeClass('ads').addClass('no-ads');
		}
		<?php if ( nebula_option('nebula_cd_adblocker') ): ?>
			ga('set', gaCustomDimensions['adblocker'], adBlockUser);
		<?php endif; ?>

		<?php if ( nebula_option('nebula_cm_notableformviews') ): //Notable Form Views (to calculate against submissions) ?>
			if ( jQuery('.notable-form').is('*') ){
				ga('set', gaCustomMetrics['notableFormViews'], 1);
			}
		<?php endif; ?>

		ga('send', 'pageview'); //Sends pageview along with set dimensions.

		//Get time as ISO string with timezone offset
		function isoTimestamp(){
			var now = new Date();
			var tzo = -now.getTimezoneOffset();
			var dif = ( tzo >= 0 )? '+' : '-';
			var pad = function(num){
				var norm = Math.abs(Math.floor(num));
				return (( norm < 10 )? '0' : '') + norm;
			};
			return now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()) + '.' + pad(now.getMilliseconds()) + dif + pad(tzo/60) + ':' + pad(tzo%60);
		}
	</script>
	<noscript><img src="<?php echo ga_UTM_gif(); //Track users who disable JavaScript. ?>" width="1" height="1" style="display: none;" /></noscript>
<?php endif; ?>


<?php if ( nebula_option('nebula_facebook_custom_audience_pixel_id') ): ?>
	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

		fbq('init', '<?php echo get_option('nebula_facebook_custom_audience_pixel_id'); ?>');
		fbq('track', 'PageView');
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo get_option('nebula_facebook_custom_audience_pixel_id'); ?>&ev=PageView&noscript=1"/></noscript>
<?php endif; ?>