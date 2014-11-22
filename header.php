<!DOCTYPE html>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie6 lt-ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="no-js ie ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="no-js ie ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IEMobile]><html <?php language_attributes(); ?> class="no-js ie iem7" dir="ltr"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class=" <?php echo (array_key_exists('debug', $_GET)) ? 'debug' : ' '; ?> no-js"><!--<![endif]-->
	<?php /* manifest="<?php echo get_template_directory_uri(); ?>/includes/manifest.appcache" */ //To begin setting up ApplicationCache, move this attribute to the <html> tag. ?>
	<head>
		<meta http-equiv='X-UA-Compatible' content='IE=edge' />
		<meta charset="<?php bloginfo('charset'); ?>" />

		<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') || is_front_page() ) : ?>
			<title><?php wp_title('-', true, 'right'); ?></title>
		<?php else : ?>
			<title><?php wp_title('-', true, 'right'); ?></title>
		<?php endif; ?>

		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/includes/manifest.json">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
		<link rel="profile" href="http://gmpg.org/xfn/11" />

		<?php //Stylesheets are loaded at the top of functions.php (so they can be registerred and enqueued). ?>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

		<?php include_once('includes/metagraphics.php'); //All graphic components of metadata are declared in this file. ?>

		<!-- Open Graph Metadata -->
		<?php //Check that all Open Graph data is working: https://developers.facebook.com/tools/debug ?>
		<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') || is_front_page() ) : ?>
			<meta property="og:type" content="business.business" />
			<meta property="og:locale" content="<?php echo bloginfo('language'); ?>" />
			<meta property="og:title" content="<?php the_title(); ?>" />
			<meta property="og:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
			<meta property="og:url" content="<?php the_permalink(); ?>" />
			<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />

			<link rel="canonical" href="<?php the_permalink(); ?>" />

			<meta name="description" content="<?php echo nebula_the_excerpt('', 100, 0); ?>" />
			<meta name="keywords" content="<?php echo nebula_settings_conditional_text('nebula_keywords', ''); ?>" />
			<meta name="news_keywords" content="<?php echo nebula_settings_conditional_text('nebula_news_keywords', ''); ?>" />
			<meta name="author" content="<?php echo get_template_directory_uri(); ?>/humans.txt" />

			<meta property="business:contact_data:website" content="<?php echo home_url('/'); ?>" />
			<meta property="business:contact_data:email" content="<?php echo nebula_settings_conditional_text('nebula_contact_email', get_option('admin_email', $GLOBALS['admin_user']->user_email)); ?>" />
			<meta property="business:contact_data:phone_number" content="+<?php echo nebula_settings_conditional_text('nebula_phone_number', ''); ?>" />
			<meta property="business:contact_data:fax_number" content="+<?php echo nebula_settings_conditional_text('nebula_fax_number', ''); ?>" />
			<meta property="business:contact_data:street_address" content="<?php echo nebula_settings_conditional_text('nebula_street_address', ''); ?>" />
			<meta property="business:contact_data:locality" content="<?php echo nebula_settings_conditional_text('nebula_locality', ''); ?>" />
			<meta property="business:contact_data:region" content="<?php echo nebula_settings_conditional_text('nebula_region', ''); ?>" />
			<meta property="business:contact_data:postal_code" content="<?php echo nebula_settings_conditional_text('nebula_postal_code', ''); ?>" />
			<meta property="business:contact_data:country_name" content="<?php echo nebula_settings_conditional_text('nebula_country_name', 'USA'); ?>" />
		<?php endif; ?>

		<?php //Business hours of operation. Times should be in the format "5:30 pm" or "17:30". Remove from Foreach loop to override Nebula Settings. ?>
		<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ) : ?>
			<?php if ( get_option('nebula_business_hours_' . $weekday . '_enabled') && get_option('nebula_business_hours_' . $weekday . '_open') != '' && get_option('nebula_business_hours_' . $weekday . '_close') != '' ) : ?>
				<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
				<meta property="business:hours:start" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_open'); ?>" />
				<meta property="business:hours:end" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_close'); ?>" />
			<?php endif; ?>
		<?php endforeach; ?>

		<!-- Facebook Metadata -->
		<?php $GLOBALS['social']['facebook_url'] = nebula_settings_conditional_text('nebula_facebook_url', 'https://www.facebook.com/GearsideCreative'); ?>
		<?php $GLOBALS['social']['facebook_access_token'] = nebula_settings_conditional_text('nebula_facebook_access_token', ''); ?>
		<meta property="fb:app_id" content="<?php echo $GLOBALS['social']['facebook_app_id'] = nebula_settings_conditional_text('nebula_facebook_app_id', ''); ?>" />
		<meta property="fb:page_id" content="<?php echo $GLOBALS['social']['facebook_page_id'] = nebula_settings_conditional_text('nebula_facebook_page_id', ''); ?>" />
		<meta property="fb:admins" content="<?php echo $GLOBALS['social']['facebook_admin_ids'] = nebula_settings_conditional_text('facebook_admin_ids', ''); ?>" />

		<!-- Twitter Metadata -->
		<?php //twitter:image is located in includes/metagraphics.php ?>
		<?php $GLOBALS['social']['twitter_url'] = nebula_settings_conditional_text('nebula_twitter_url', 'https://twitter.com/great_blakes'); ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:title" content="<?php the_title(); ?>" />
		<meta name="twitter:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
		<meta name="twitter:site" content="" />
		<meta name="twitter:creator" content="" />

		<!-- Other Social Metadata -->
		<?php $GLOBALS['social']['google_plus_url'] = nebula_settings_conditional_text('nebula_google_plus_url', ''); ?>
		<?php $GLOBALS['social']['linkedin_url'] = nebula_settings_conditional_text('nebula_linkedin_url', ''); ?>
		<?php $GLOBALS['social']['youtube_url'] = nebula_settings_conditional_text('nebula_youtube_url', ''); ?>
		<?php $GLOBALS['social']['instagram_url'] = nebula_settings_conditional_text('nebula_instagram_url', ''); ?>

		<!-- Local/Geolocation Metadata -->
		<meta name="geo.placename" content="<?php echo nebula_settings_conditional_text('nebula_locality', ''); ?>, <?php echo nebula_settings_conditional_text('nebula_region', ''); ?>" />
		<meta name="geo.position" content="<?php echo nebula_settings_conditional_text('nebula_latitude', ''); ?>;<?php echo nebula_settings_conditional_text('nebula_longitude', ''); ?>" />
		<meta name="geo.region" content="<?php echo bloginfo('language'); ?>" />
		<meta name="ICBM" content="<?php echo nebula_settings_conditional_text('nebula_latitude', ''); ?>, <?php echo nebula_settings_conditional_text('nebula_longitude', ''); ?>" />
		<meta property="place:location:latitude" content="<?php echo nebula_settings_conditional_text('nebula_latitude', ''); ?>" />
		<meta property="place:location:longitude" content="<?php echo nebula_settings_conditional_text('nebula_longitude', ''); ?>" />

		<!--Microsoft Windows 8 Tiles /-->
		<meta name="application-name" content="<?php bloginfo('name'); ?>" />
		<meta name="msapplication-notification" content="frequency=720;polling-uri=<?php bloginfo('rss_url'); ?>">
		<meta name="msapplication-config" content="<?php echo get_template_directory_uri(); ?>/includes/ieconfig.xml" />

		<style>
			<?php
				//Sunrise & Sunset
				$dayTime["sunrise"] = strtotime(nebula_weather('sunrise'))-strtotime('today');
				$dayTime["sunset"] = strtotime(nebula_weather('sunset'))-strtotime('today');
				$dayTime["noon"] = (($dayTime["sunset"]-$dayTime["sunrise"])/2)+$dayTime["sunrise"];

				$dayTimeModifier = (int) (($dayTime["noon"]-$dayTime["sunrise"])/6);
				$dayTime["dawn"] = (int) $dayTime["sunrise"]-$dayTimeModifier;
				$dayTime["aftcalc"] = (int) $dayTime["sunrise"]+$dayTimeModifier;
				$dayTime["evecalc"] = (int) $dayTime["sunset"]-$dayTimeModifier;
				$dayTime["dusk"] = (int) $dayTime["sunset"]+$dayTimeModifier;

				//Determine time of day photo to display
				$currentDayTime = time()-strtotime("today");
				echo '/* ' . $currentDayTime . ' */';
				//$currentDayTime = 73000;
				switch ( true ) {

					/*==========================
					 Morning
					 ===========================*/
					case $currentDayTime >= $dayTime["dawn"] && $currentDayTime < $dayTime["aftcalc"] : ?>
						<?php
							$glowPercent = (($currentDayTime-$dayTime["dawn"])*100)/($dayTime["aftcalc"]-$dayTime["dawn"]);
							if ( $currentDayTime <= $dayTime["sunrise"] ) {
								$glow_color = 'rgba(240, 140, 140, 0.6)'; //Blue/Purple
							} else {
								$glow_color = 'rgba(240, 200, 130, 0.9)'; //Yellow
							}
						?>
						#bgsky {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-back-morning.jpg') no-repeat; background-size: cover;}
						#fullbodywrapper {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-fore-morning.png'), linear-gradient(to bottom, rgba(130, 194, 237, 0.25) <?php echo max([20, $glowPercent]); ?>%, <?php echo $glow_color; ?> 100%);}
							}
						<?php break;

					/*==========================
					 Afternoon (with Animation - non-subpixel)
					 ===========================*/
					case $currentDayTime >= $dayTime["aftcalc"] && $currentDayTime < $dayTime["evecalc"] && 1==1 : ?>
						#bgsky {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-back-afternoon.jpg') repeat-x; background-size: cover;}
							#bgsky.animate,
							.frosted.animate {
								animation: cloudAnimation 500s linear infinite;
								-moz-animation: cloudAnimation 500s linear infinite;
								-webkit-animation: cloudAnimation 500s linear infinite;
								-ms-animation: cloudAnimation 500s linear infinite;
								-o-animation: cloudAnimation 500s linear infinite;
							}

							@keyframes cloudAnimation {
								0% {background-position-x: 0;}
								100% {background-position-x: 2616px;}
							}
							@-moz-keyframes cloudAnimation {
								0% {background-position-x: 0;}
								100% {background-position-x: 2616px;}
							}
							@-webkit-keyframes cloudAnimation {
								0% {background-position-x: 0;}
								100% {background-position-x: 2616px;}
							}
							@-ms-keyframes cloudAnimation {
								0% {background-position-x: 0;}
								100% {background-position-x: 2616px;}
							}
							@-o-keyframes cloudAnimation {
								0% {background-position-x: 0;}
								100% {background-position-x: 2616px;}
							}

						#fullbodywrapper {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-fore-afternoon.png');}

						.logodivider {transform: translate3d(0, 0, 0); /* Trigger hardware acceleration. Must not be on #bgsky or #fullbodywrapper because it breaks fixed positioning on child elements. */}

						<?php break;

					/*==========================
					 Afternoon (with Transition - subpixel)  http://jsfiddle.net/5pVr4/6/
					 ===========================*/
					case $currentDayTime >= $dayTime["aftcalc"] && $currentDayTime < $dayTime["evecalc"] : ?>

						#bgsky {overflow: hidden;}

						.cloudtest {
							position: fixed; /* @TODO: Absolute scrolls clouds, fixed is in front of mmenu :( */
						    left: 0;
						    right: -2616px;
						    top: 0;
						    bottom: 0;
							background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-back-afternoon.jpg') repeat-x;

						    animation: cloudAnimation 500s linear infinite;
							-moz-animation: cloudAnimation 500s linear infinite;
							-webkit-animation: cloudAnimation 500s linear infinite;
							-ms-animation: cloudAnimation 500s linear infinite;
							-o-animation: cloudAnimation 500s linear infinite;
						}

						/* This kind of helps, but once the menu is open it shows how broken it is.
							.mm-opening .cloudtest,
							.mm-opened .cloudtest {position: absolute;}
						*/

						@keyframes cloudAnimation {
							from {transform: translateX(0);}
							to {transform: translateX(-2616px);}
						}
						@-moz-keyframes cloudAnimation {
							from {-moz-transform: translateX(0);}
							to {-moz-transform: translateX(-2616px);}
						}
						@-webkit-keyframes cloudAnimation {
							from {-webkit-transform: translateX(0);}
							to {-webkit-transform: translateX(-2616px);}
						}
						@-ms-keyframes cloudAnimation {
							from {-ms-transform: translateX(0);}
							to {-ms-transform: translateX(-2616px);}
						}
						@-o-keyframes cloudAnimation {
							from {-o-transform: translateX(0);}
							to {-o-transform: translateX(-2616px);}
						}


						#fullbodywrapper {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-fore-afternoon.png');}

						.logodivider {transform: translate3d(0, 0, 0); /* Trigger hardware acceleration. Must not be on #bgsky or #fullbodywrapper because it breaks fixed positioning on child elements. */}

						<?php break;

					/*==========================
					 Evening
					 ===========================*/
					case $currentDayTime >= $dayTime["evecalc"] && $currentDayTime < $dayTime["dusk"] : ?>
						<?php
							$glowPercent = (($currentDayTime-$dayTime["evecalc"])*100)/($dayTime["dusk"]-$dayTime["evecalc"]);
							if ( $currentDayTime <= $dayTime["sunset"] ) {
								$glow_color = 'rgba(255, 144, 0,'; //Orange
							} else {
								$glow_color = 'rgba(255, 45, 0,'; //Red
							}
						?>
						#bgsky {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-back-evening.jpg') no-repeat; background-size: cover; }
						#fullbodywrapper {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-fore-evening.png'), linear-gradient(to bottom, rgba(100, 165, 240, <?php echo min([0.5, (100-$glowPercent)/100]); ?>) <?php echo max([20, $glowPercent]) . '%,' . $glow_color . min([0.5, (100-$glowPercent)/100]); ?>) 100%);}
						<?php break;

					/*==========================
					 Night
					 ===========================*/
					case $currentDayTime >= $dayTime["dusk"] :
					case $currentDayTime < $dayTime["dawn"] : ?>
						#bgsky {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-back-night.jpg') no-repeat; background-size: cover; }
						#fullbodywrapper {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-fore-night.png');}
						<?php break;

					/*==========================
					 Default
					 ===========================*/
					default: ?>
						#bgsky {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-back-evening.jpg') no-repeat; background-size: cover; }
						#fullbodywrapper {background: url('<?php echo get_template_directory_uri(); ?>/images/bg/bg-fore-evening.png'), linear-gradient(to bottom, rgba(0, 0, 0, 0) 99%, rgba(255, 45, 0, 0) 100%);}
						<?php break;
				}
			?>
		</style>

		<script>
			social = [];
			social['facebook_url'] = "<?php echo $GLOBALS['social']['facebook_url']; ?>";
			social['facebook_app_id'] = "<?php echo $GLOBALS['social']['facebook_app_id']; ?>";
			social['twitter_url'] = "<?php echo $GLOBALS['social']['twitter_url']; ?>";
			social['google_plus_url'] = "<?php echo $GLOBALS['social']['google_plus_url']; ?>";
			social['linkedin_url'] = "<?php echo $GLOBALS['social']['linkedin_url']; ?>";
			social['youtube_url'] = "<?php echo $GLOBALS['social']['youtube_url']; ?>";
			social['instagram_url'] = "<?php echo $GLOBALS['social']['instagram_url']; ?>";
		</script>

		<script> //Universal Analytics
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $GLOBALS['ga']; ?>', 'auto');
			ga('require', 'displayfeatures');
			ga('send', 'pageview');
		</script>

		<script>
			if ( window.addEventListener ) {
				window.addEventListener('error', function(e) {
					if ( e.lineno != 0 ) {
						ga('send', 'event', 'Error', 'JavaScript Error', e.message + ' in: ' + e.filename + ' on line ' + e.lineno);
						ga('send', 'exception', e.message, false);
					}
				});
			}
		</script>

		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
			<div id="bgsky" data-0="background-position-y: 0px;" data-end="background-position-y: -10px;">
				<!-- <div class="cloudtest FixedTop"></div> -->
				<div id="fullbodywrapper" data-0="background-position-y: 0px;" data-end="background-position-y: -25px;">
					<div id="fb-root"></div>

					<noscript>
						<iframe class="hidden" src="<?php echo get_template_directory_uri(); ?>/includes/no-js.php?h=<?php echo home_url('/'); ?>&p=<?php echo get_page_uri(); ?>&t=<?php wp_title('-', true, 'right'); ?>" width="0" height="0" style="display:none;position:absolute;"></iframe>
					</noscript>

					<div id="stickyhead">
						<div class="mobilemenucon">
			        		<a class="mobilenavtrigger" href="#mobilenav"><i class="fa fa-bars"></i></a> <!-- @TODO: Clicking this re-anchors to the top when triggering menu... why? -->
							<nav id="mobilenav">
								<?php wp_nav_menu(array('theme_location' => 'mobile')); ?>
							</nav><!--/mobilenav-->
		        		</div><!--/mobilemenucon-->

		        		<div id="logocon" data-0="opacity:1; top: 0px;" data-83="opacity:0; top: -20px;">
							<a class="inithide" href="<?php echo home_url('/'); ?>"><img class="svglogo" src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/images/logo.png'" alt="Gearside Creative"/></a>
						</div>

		        		<?php if ( is_front_page() ) : ?>
			        		<div class="loadingcon">
								<div id="bgimgloadcontainer">
									<div class="bgimgload"></div>
					        		<div class="bgimgloadtext">
					        			<span>Loading...<br/></span>
					        			<span id="countHolder">0.00</span>
					        			<script type="text/javascript">
											pageLoaded = 0;
											initialTime = Date.now();
											var actualTimer = setInterval(function() {
												var currentTime = Date.now();
												var displayTime = (currentTime-initialTime)/1000;
												displayTime = displayTime.toFixed(2);

												if ( pageLoaded == 1 ) {
											        jQuery('#countHolder').css('color', '#0f0').html(displayTime);
											        jQuery('.loadingcon').fadeOut(500);
											        jQuery('#logocon a').removeClass('inithide');
											        window.clearInterval(actualTimer);
											        return false;
											    } else if(displayTime < 2 && pageLoaded == 0){
										            jQuery('#countHolder').css('color', '#fff').html(displayTime);
										        } else if (displayTime >= 2 && displayTime < 4 && pageLoaded == 0){
										            jQuery('#countHolder').css('color', '#fcc').html(displayTime);
										        } else if (displayTime >= 4 && displayTime < 6 && pageLoaded == 0){
										            jQuery('#countHolder').css('color', 'f99').html(displayTime);
										        } else if (displayTime >= 6 && displayTime < 8 && pageLoaded == 0){
										            jQuery('#countHolder').css('color', 'f66').html(displayTime);
										        } else if (displayTime >= 8 && displayTime < 10 && pageLoaded == 0){
										            jQuery('#countHolder').css('color', 'f33').html(displayTime);
										        } else {
										        	ga('send', 'event', 'Homepage Visible Load', 'Loading 10+', 'Visible load time: ' + displayTime);
										        	jQuery('#countHolder').css('color', 'f00').html('10+');
										        	jQuery('.loadingcon').fadeOut(500, function(){
														jQuery(this).remove();
														jQuery('#logocon a').removeClass('inithide');
													});
										        	window.clearInterval(actualTimer);
										            return false;
										        }
											}, 10);
					        			</script>
					        		</div>
					        	</div>
							</div><!--/container-->
		        		<?php endif; ?>

		        		<div class="likecon">
							<div class="fb-like" data-href="https://www.facebook.com/GearsideCreative" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
						</div>
					</div>

					<div class="minilogocon">
						<a href="<?php echo home_url('/'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/logo-symbol.svg" onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/images/logo-symbol.png'" alt="Gearside Creative"/></a>
					</div>

					<?php if ( !is_search() && (array_key_exists('s', $_GET) || array_key_exists('rs', $_GET)) ) : ?>
						<div class="container headerdrawercon">
							<hr/>
							<div class="row">
								<div class="sixteen columns headerdrawer">
									<span><i class="fa fa-share"></i> Your search returned only one result. You have been automatically redirected.</span>
									<a class="close" href="<?php the_permalink(); ?>"><i class="fa fa-times"></i></a>
								</div><!--/columns-->
							</div><!--/row-->
							<hr/>
						</div><!--/container-->
					<?php elseif ( (is_page('search') || is_page_template('tpl-search.php')) && array_key_exists('invalid', $_GET) ) : ?>
						<div class="container headerdrawercon">
							<hr/>
							<div class="row">
								<div class="sixteen columns headerdrawer invalid">
									<span><i class="fa fa-exclamation-triangle"></i> Your search was invalid. Please try again.</span>
									<a class="close" href="<?php the_permalink(); ?>"><i class="fa fa-times"></i></a>
								</div><!--/columns-->
							</div><!--/row-->
							<hr/>
						</div><!--/container-->
					<?php elseif ( is_404() || array_key_exists('s', $_GET) ) : ?>
						<div id="suggestedpage" class="container headerdrawercon">
							<hr/>
							<div class="row">
								<div class="sixteen columns headerdrawer">
									<h3><i class="fa fa-question-circle"></i> Did you mean?</h3>
									<p><a class="suggestion" href="#"></a></p>
									<a class="close" href="<?php the_permalink(); ?>"><i class="fa fa-times"></i></a>
								</div><!--/columns-->
							</div><!--/row-->
							<hr/>
						</div><!--/container-->
					<?php endif; ?>

					<div id="searchnavfixer" data-0="position: relative; background: !rgba(0,0,0,0); z-index: inherit;" data-top="position: fixed; top: 0; background: !rgba(0,0,0,0.9); z-index: !7000;">
						<div class="row">
							<div class="sixteen columns">

								<div id="searchnavcon" class="row" data-0="border-bottom: !1px solid rgba(255, 255, 255, 0.7);" data-0-top="border-bottom: !1px solid rgba(255, 255, 255, 0);">
									<div class="twelve columns searchcon">
										<form id="headersearch" method="get" action="<?php echo home_url('/'); ?>">
											<?php
												if ( $_GET['s'] ) {
													$current_search = $_GET['s'];
												} elseif ( $_GET['rs'] ) {
													$current_search = $_GET['rs'];
												}
												$header_search_placeholder = ( isset($current_search) ) ? $current_search : 'What are you looking for?' ;
											?>
											<i class="fa fa-search"></i> <input id="s" name="s" type="search" placeholder="<?php echo $header_search_placeholder; ?>" x-webkit-speech/> <!-- @TODO: If on a search result page, change the placeholder to the current search term! -->
										</form>
									</div><!--/columns-->
									<div class="four columns navcon">
										<nav id="primarynav" class="clearfix">
											<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '1')); ?>
						        		</nav>
									</div><!--/columns-->
								</div><!--/row-->

							</div><!--/columns-->
						</div><!--/row-->
					</div>

					<br/>