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

		ga('create', '<?php echo $GLOBALS['ga']; ?>', 'auto'<?php echo ( nebula_option('ga_wpuserid', 'enabled') && is_user_logged_in() )? ', {"userId": "' . get_current_user_id() . '"}': ''; ?>); <?php //Change Tracking ID in Nebula Options or functions.php! ?>

		<?php if ( nebula_option('ga_displayfeatures', 'enabled') ): ?>
			ga('require', 'displayfeatures');
		<?php endif; ?>

		<?php if ( nebula_option('ga_linkid', 'enabled') ): ?>
			ga('require', 'linkid');
		<?php endif; ?>

		<?php
			//Create various custom dimensions and custom metrics in Google Analytics, then store the index ("dimension3", "metric5", etc.) in Nebula Options.
			//Note: Ecommerce dimensions are added in nebula_ecommerce.php
		?>
		gaCustomDimensions = {
			author: '<?php echo nebula_option('cd_author'); //Hit ?>',
			businessHours: '<?php echo nebula_option('cd_businesshours'); //Hit ?>',
			categories: '<?php echo nebula_option('cd_categories'); //Hit ?>',
			tags: '<?php echo nebula_option('cd_tags'); //Hit ?>',
			contactMethod: '<?php echo nebula_option('cd_contactmethod'); //Session ?>',
			firstInteraction: '<?php echo nebula_option('cd_firstinteraction'); //User ?>',
			geolocation: '<?php echo nebula_option('cd_geolocation'); //Session ?>',
			geoAccuracy: '<?php echo nebula_option('cd_geoaccuracy'); //Session ?>',
			geoName: '<?php echo nebula_option('cd_geoname'); //Session ?>',
			relativeTime: '<?php echo nebula_option('cd_relativetime'); //Hit ?>',
			scrollDepth: '<?php echo nebula_option('cd_scrolldepth'); //Hit ?>',
			maxScroll: '<?php echo nebula_option('cd_maxscroll'); //Hit ?>',
			sessionID: '<?php echo nebula_option('cd_sessionid'); //Session ?>',
			sessionNotes: '<?php echo nebula_option('cd_sessionnotes'); //Session ?>',
			poi: '<?php echo nebula_option('cd_notablepoi'); //User ?>',
			role: '<?php echo nebula_option('cd_role'); //User ?>',
			timestamp: '<?php echo nebula_option('cd_timestamp'); //Hit ?>',
			userID: '<?php echo nebula_option('cd_userid'); //User ?>',
			fbID: '<?php echo nebula_option('cd_fbid'); //User ?>',
			videoWatcher: '<?php echo nebula_option('cd_videowatcher'); //Session ?>',
			eventIntent: '<?php echo nebula_option('cd_eventintent'); //Hit ?>',
			wordCount: '<?php echo nebula_option('cd_wordcount'); //Hit ?>',
			weather: '<?php echo nebula_option('cd_weather'); //Hit ?>',
			temperature: '<?php echo nebula_option('cd_temperature'); //Hit ?>',
			publishYear: '<?php echo nebula_option('cd_publishyear'); //Hit ?>',
			adBlocker: '<?php echo nebula_option('cd_adblocker'); //Session ?>',
		}

		gaCustomMetrics = {
			formViews: '<?php echo nebula_option('cm_formviews'); //Hit, Integer ?>',
			formStarts: '<?php echo nebula_option('cm_formstarts'); //Hit, Integer ?>',
			formSubmissions: '<?php echo nebula_option('cm_formsubmissions'); //Hit, Integer ?>',
			notableDownloads: '<?php echo nebula_option('cm_notabledownloads'); //Hit, Integer ?>',
			engagedReaders: '<?php echo nebula_option('cm_engagedreaders'); //Hit, Integer ?>',
			videoStarts: '<?php echo nebula_option('cm_videostarts'); //Hit, Integer ?>',
			videoPlaytime: '<?php echo nebula_option('cm_videoplaytime'); //Hit, Time ?>',
			videoCompletions: '<?php echo nebula_option('cm_videocompletions'); //Hit, Integer ?>',
			autocompleteSearches: '<?php echo nebula_option('cm_autocompletesearches'); //Hit, Integer ?>',
			autocompleteSearchClicks: '<?php echo nebula_option('cm_autocompletesearchclicks'); //Hit, Integer ?>',
			wordCount: '<?php echo nebula_option('cm_wordcount'); //Hit, Integer ?>',
		}

		<?php
			if ( is_404() ){
				echo 'ga("set", gaCustomDimensions["sessionNotes"], sessionNote("HTTP 404 Page"));';
			}

			if ( is_singular() || is_page() ){
				global $post;

				if ( is_singular() ){
					//Article author
					if ( nebula_option('author_bios', 'enabled') && nebula_option('cd_author') ){
						echo 'ga("set", gaCustomDimensions["author"], "' . get_the_author() . '");';
					}

					//Article's published year
					if ( nebula_option('cd_publishyear') ){
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
					if ( nebula_option('cd_categories') ){
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
					if ( nebula_option('cd_tags') && !is_front_page() ){
						echo 'ga("set", gaCustomDimensions["tags"], "' . $tag_list . '");';
					}
				}

				//Word Count
				$word_count = str_word_count(strip_tags($post->post_content));
				if ( is_int($word_count) ){
					if ( nebula_option('cm_wordcount') ){
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
					if ( nebula_option('cd_wordcount') ){
						echo 'ga("set", gaCustomDimensions["wordCount"], "' . $word_count_range . '");';
					}
				}
			}

			//Business Open/Closed
			if ( business_open() ){
				$business_open = 'During Business Hours';
				echo 'nebula.user.client.businessopen = true;';
			} else {
				$business_open = 'Non-Business Hours';
				echo 'nebula.user.client.businessopen = false;';
			}
			if ( nebula_option('cd_businesshours') ){
				echo 'ga("set", gaCustomDimensions["businessHours"], "' . $business_open . '");';
			}

			//Relative time ("Late Morning", "Early Evening")
			if ( nebula_option('cd_relativetime') ){
				$relative_time = nebula_relative_time();
				$time_description = implode(' ', $relative_time['description']);
				$time_range = $relative_time['standard'][0] . ':00' . $relative_time['ampm'] . ' - ' . $relative_time['standard'][2] . ':59' . $relative_time['ampm'];
				echo 'ga("set", gaCustomDimensions["relativeTime"], "' . ucwords($time_description) . ' (' . $time_range . ')");';
			}

			//Role
			if ( nebula_option('cd_role') ){
				$usertype = '';
				if ( is_user_logged_in() ){
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
					    	break;
					    default:
					    	$usertype = ucwords($user_info->roles[0]);
					    	break;
					}
				}

				$staff = '';
				if ( is_dev() ){
					$staff = ' (Developer)';
				} elseif ( is_client() ){
					$staff = ' (Client)';
				}

				if ( !empty($usertype) || !empty($staff) ){
					echo 'ga("set", gaCustomDimensions["role"], "' . $usertype .  $staff . '");';
				}
			}

			//Session ID
			if ( nebula_option('cd_sessionid') ){
				echo 'nebula.session.id = "' . nebula_session_id() . '";';
				echo 'ga("set", gaCustomDimensions["sessionID"], nebula.session.id);';
			}

			//WordPress User ID
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( $current_user && nebula_option('cd_userid') ){
					echo 'ga("set", gaCustomDimensions["userID"], "' . $current_user->ID . '");';
					echo 'ga("set", "userId", ' . $current_user->ID . ');';
				}
			}

			//User's local timestamp
			if ( nebula_option('cd_timestamp') ){
				echo 'ga("set", gaCustomDimensions["timestamp"], localTimestamp());';
			}

			//First visit timestamp
			if ( nebula_option('cd_firstinteraction') && !empty($nebula['user']['sessions']['initial']) ){
				echo 'ga("set", gaCustomDimensions["firstInteraction"], "' . time() . '");';
			}

			//Weather Conditions
			if ( nebula_option('cd_weather') ){
				echo 'ga("set", gaCustomDimensions["weather"], "' . nebula_weather('conditions') . '");';
			}
			//Temperature Range
			if ( nebula_option('cd_temperature') ){
				$temp_round = floor(nebula_weather('temperature')/5)*5;
				$temp_round_celcius = round(($temp_round-32)/1.8);
				$temp_range = strval($temp_round) . '°F - ' . strval($temp_round+4) . '°F (' . strval($temp_round_celcius) . '°C - ' . strval($temp_round_celcius+2) . '°C)';
				echo 'ga("set", gaCustomDimensions["temperature"], "' . $temp_range . '");';
			}

			//Notable POI (IP Addresses)
			if ( nebula_option('cd_notablepoi') && nebula_option('notableiplist') ){
				$notable_ip_lines = explode("\n", nebula_option('notableiplist'));
				foreach ( $notable_ip_lines as $line ){
					$ip_info = explode(' ', strip_tags($line), 2); //0 = IP Address or RegEx pattern, 1 = Name
					if ( ($ip_info[0][0] === '/' && preg_match($ip_info[0], $_SERVER['REMOTE_ADDR'])) || $ip_info[0] == $_SERVER['REMOTE_ADDR'] ){ //If regex pattern and matches IP, or if direct match
						echo 'ga("set", gaCustomDimensions["poi"], "' . str_replace(array("\r\n", "\r", "\n"), '', $ip_info[1]) . '");';
						break;
					}
				}
			} elseif ( isset($_GET['poi']) ){ //If POI query string exists //@TODO "Nebula" 0: in main.js strip this query string off the URL somehow?
				echo 'ga("set", gaCustomDimensions["poi"], "' . str_replace('%20', '', $_GET['poi']) . '");';
			}
		?>

		<?php if ( nebula_option('cm_formviews') ): //Notable Form Views (to calculate against submissions) ?>
			if ( !jQuery('form').hasClass('.ignore-form') && !jQuery('form').find('.ignore-form').length ){
				ga('set', gaCustomMetrics['formViews'], 1);
			}
		<?php endif; ?>

		<?php if ( !nebula_is_bot() && ( nebula_option('adblock_detect') || nebula_option('cd_adblocker') ) ): //Detect Ad Blockers. ?>
			jQuery.getScript(nebula.site.directory.template.uri + '/js/libs/show_ads.js').done(function(){
				adBlockUser = 'No Ad Blocker';
				if ( nebula.session.flags ){
					nebula.session.flags.adblock = 'false';
				}
			}).fail(function(){ <?php //Ad Blocker Detected ?>
				jQuery('html').addClass('ad-blocker');
				adBlockUser = 'Ad Blocker Detected';
				<?php if ( nebula_option('cd_adblocker') ): //Scope: Session ?>
					ga('set', gaCustomDimensions['adBlocker'], adBlockUser); <?php //Note: this is set AFTER the pageview is already sent (due to async), so it needs the event below. ?>
				<?php endif; ?>

				if ( nebula.session.flags && nebula.session.flags.adblock !== 'true' ){
					ga('send', 'event', adBlockUser, 'This user is using ad blocking software.');
					nebula.session.flags.adblock = 'true';
				}
			});
		<?php endif; ?>


		<?php do_action('nebula_ga_before_send_pageview'); //Hook into for adding more custom definitions before the pageview hit is sent. Can override any above definitions too. ?>

		ga('send', 'pageview'); <?php //Send pageview along with set dimensions. ?>
		//console.log('pageview sent');

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

		//Add or remove session notes
		function sessionNote(action, item){
			if ( !jQuery('html').hasClass('lte-ie8') ){
				if ( typeof sessionStorage['nebulaSession'] === 'undefined' ){
					nebula.session.notes = [];
				} else {
					nebula.session = JSON.parse(sessionStorage['nebulaSession']);
					if ( !nebula.session.notes ){
						nebula.session.notes = [];
					}
				}

				if ( action === 'return' ){
					return nebula.session.notes.join(', ');
				} else if ( action != 'add' && action != 'remove' ){
					item = action;
					action = 'add';
				}

				if ( !item ){ //IE8 does not like this.
					return nebula.session.notes.join(', ');
				}

				itemIndex = nebula.session.notes.indexOf(item.replace(/"|%22/g, ''));

				if ( action === 'add' ){
					if ( itemIndex < 0 ){
						nebula.session.notes.push(item);
					} else {
						return nebula.session.notes.join(', ');
					}
				} else {
					if ( itemIndex >= 0 ){
						nebula.session.notes.splice(itemIndex, 1);
					} else {
						return nebula.session.notes.join(', ');
					}
				}

				sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
				return nebula.session.notes.join(', ');
			} else {
				return 'IE8';
			}
		}
	</script>
	<noscript>
		<img src="<?php echo ga_UTM_gif(); ?>" width="1" height="1" style="display: none;" /><?php //Track pageviews of users who disable JavaScript. ?>
		<iframe class="hidden" src="<?php echo home_url(); ?>/?nonce=<?php global $nebula; echo $nebula['site']['ajax']['nonce']; ?>&js=false&id=<?php echo $post->ID; ?>" width="0" height="0" style="display: none; position: absolute;"></iframe><?php //Send "JavaScript Disabled" event. ?>
	</noscript>
<?php else: //If Tracking ID is empty: ?>
	<script>
		function ga(){return false;}
		function gaCustomDimensions(){return false;}
		function gaCustomMetrics(){return false;}
		function localTimestamp(){return false;}
		function sessionNote(){return false;}
	</script>
<?php endif; ?>


<?php if ( nebula_option('facebook_custom_audience_pixel_id') ): ?>
	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

		fbq('init', '<?php echo nebula_option('facebook_custom_audience_pixel_id'); ?>');
		fbq('track', 'PageView');
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo nebula_option('facebook_custom_audience_pixel_id'); ?>&ev=PageView&noscript=1"/></noscript>
<?php endif; ?>