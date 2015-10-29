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

		<?php if ( nebula_ga_remarketing_enabled() ): ?>
			ga('require', 'displayfeatures');
		<?php endif; ?>

		//Create various custom dimensions and custom metrics in Google Analytics, then store the strings ("dimension3", "metric5", etc.) in Nebula Options.
		gaCustomDimensions = {
			'author': '<?php echo nebula_get_custom_definition('nebula_cd_author'); //Hit ?>',
			'businessHours': '<?php echo nebula_get_custom_definition('nebula_cd_businesshours'); //Hit ?>',
			'categories': '<?php echo nebula_get_custom_definition('nebula_cd_categories'); //Hit ?>',
			'contactMethod': '<?php echo nebula_get_custom_definition('nebula_cd_contactmethod'); //Session ?>',
			'geolocation': '<?php echo nebula_get_custom_definition('nebula_cd_geolocation'); //Session ?>',
			'geoAccuracy': '<?php echo nebula_get_custom_definition('nebula_cd_geoaccuracy'); //Session ?>',
			'geoName': '<?php echo nebula_get_custom_definition('nebula_cd_geoname'); //Session ?>',
			'notablebrowser': '<?php echo nebula_get_custom_definition('nebula_cd_notablebrowser'); //Session ?>',
			'scrollDepth': '<?php echo nebula_get_custom_definition('nebula_cd_scrolldepth'); //Hit ?>',
			'sessionID': '<?php echo nebula_get_custom_definition('nebula_cd_sessionid'); //Session ?>',
			'staff': '<?php echo nebula_get_custom_definition('nebula_cd_staff'); //User ?>',
			'timestamp': '<?php echo nebula_get_custom_definition('nebula_cd_timestamp'); //Hit ?>',
			'userID': '<?php echo nebula_get_custom_definition('nebula_cd_userid'); //User ?>',
			'videoWatcher': '<?php echo nebula_get_custom_definition('nebula_cd_videowatcher'); //Session ?>',
			'wordCount': '<?php echo nebula_get_custom_definition('nebula_cd_wordcount'); //Hit ?>',
			'weather': '<?php echo nebula_get_custom_definition('nebula_cd_weather'); //Hit ?>',
			'temperature': '<?php echo nebula_get_custom_definition('nebula_cd_temperature'); //Hit ?>',
		}

		<?php if ( is_single() ): ?>
			<?php if ( nebula_author_bios_enabled() && nebula_get_custom_definition('nebula_cd_author') ): ?>
				ga('set', gaCustomDimensions['author'], '<?php echo get_the_author(); ?>');
			<?php endif; ?>

			<?php if ( nebula_get_custom_definition('nebula_cd_categories') ): ?>
				<?php
					foreach(get_the_category() as $category){
						$cats[] = $category->name;
					}
					$post_cats = ( !empty($cats) )? implode(sort($cats), ', ') : 'No Categories';
				?>
				ga('set', gaCustomDimensions['categories'], '<?php echo $post_cats; ?>');
			<?php endif; ?>

			<?php if ( nebula_get_custom_definition('nebula_cd_wordcount') ): ?>
				<?php
					global $post;
					$word_count = str_word_count(strip_tags($post->post_content));
					if ( is_int($word_count) ){
						if ( $word_count < 500 ){
							$word_count_range = '<500';
						} elseif ( $word_count < 1000 ){
							$word_count_range = '500 - 999';
						} elseif ( $word_count < 1500 ){
							$word_count_range = '1,000 - 1,499';
						} elseif ( $word_count < 2000 ){
							$word_count_range = '1,500 - 1,999';
						} else {
							$word_count_range = '2,000+';
						}
					}
				?>
				ga('set', gaCustomDimensions['wordCount'], '<?php echo $word_count_range; ?>');
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( nebula_get_custom_definition('nebula_cd_businesshours') ): ?>
			ga('set', gaCustomDimensions['businessHours'], '<?php echo ( business_open() )? 'During Business Hours' : 'Non-Business Hours'; ?>');
		<?php endif; ?>

		<?php if ( nebula_get_custom_definition('nebula_cd_sessionid') ): ?>
			<?php
				$session_info = ( is_debug() )? 'Dbg.' : '';
				$session_info .= ( nebula_wireframing_enabled() )? 'Wr.' : '';
				if ( is_client() ){
					$session_info .= 'Cl.';
				} elseif ( is_dev() ){
					$session_info .= 'Dv.';
				}
				$session_info .= ( is_user_logged_in() )? 'Li.' : '';
				$session_info .= ( nebula_is_bot() )? 'Bt.' : '';
			?>
			var sessionID = new Date().getTime() + '.<?php echo $session_info; ?>' + Math.random().toString(36).substring(5);
			ga('set', gaCustomDimensions['sessionID'], sessionID);
		<?php endif; ?>

		<?php $current_user = wp_get_current_user(); ?>
		<?php if ( $current_user && nebula_get_custom_definition('nebula_cd_userid') ): ?>
			ga('set', gaCustomDimensions['userID'], '<?php echo $current_user->ID; ?>');
		<?php endif; ?>

		<?php if ( nebula_get_custom_definition('nebula_cd_staff') && (is_dev() || is_client()) ): ?>
			<?php $usertype = ( is_client() )? 'Client' : 'Developer'; ?>
			ga('set', gaCustomDimensions['staff'], '<?php echo $usertype; ?>');
		<?php endif; ?>

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
		<?php if ( nebula_get_custom_definition('nebula_cd_timestamp') ): ?>
			ga('set', gaCustomDimensions['timestamp'], isoTimestamp());
		<?php endif; ?>

		<?php if ( nebula_get_custom_definition('nebula_cd_weather') ): ?>
			ga('set', gaCustomDimensions['weather'], '<?php echo nebula_weather('conditions'); ?>');
		<?php endif; ?>
		<?php if ( nebula_get_custom_definition('nebula_cd_temperature') ): ?>
			<?php
				$temp_round = floor(nebula_weather('temperature')/5)*5;
				$temp_range = strval($temp_round) . '°F - ' . strval($temp_round+4) . '°F';
			?>
			ga('set', gaCustomDimensions['temperature'], '<?php echo $temp_range; ?>');
		<?php endif; ?>

		ga('send', 'pageview'); //Sends pageview along with set dimensions.
	</script>
<?php else: ?>
	<?php if ( is_dev() ): ?>
		<script>console.error('WARNING: No Google Analytics tracking ID!');</script>
	<?php endif; ?>
<?php endif; ?>


<?php if ( !nebula_option('nebula_facebook_custom_audience_pixel', 'disabled') ): //Facebook Custom Audience Pixel ?>
	<?php if ( get_option('nebula_facebook_custom_audience_pixel_id') != '' ): ?>
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
	<?php else: ?>
		<?php if ( is_dev() ): ?>
			<script>console.warn('Facebook Custom Audience Pixel is enabled, but the pixel ID is empty!');</script>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>