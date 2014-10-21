<!DOCTYPE html>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie6 lt-ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="no-js ie ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="no-js ie ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IEMobile]><html <?php language_attributes(); ?> class="no-js ie iem7" dir="ltr"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class=" <?php echo (array_key_exists('debug', $_GET)) ? 'debug' : ' '; ?> no-js"><!--<![endif]-->
	<head>
		<meta http-equiv='X-UA-Compatible' content='IE=edge' />
		<meta charset="<?php bloginfo('charset'); ?>" />

		<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') || is_front_page() ) : //@TODO "Nebula" 0: Prevent Wordpress SEO (Yoast) from altering the title on the homepage. ?>
			<title><?php wp_title('-', true, 'right'); ?></title>
		<?php else : ?>
			<title><?php wp_title('-', true, 'right'); ?></title>
		<?php endif; ?>

		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>

		<link rel="profile" href="http://gmpg.org/xfn/11" />

		<?php //Stylesheets are loaded at the top of functions.php (so they can be registerred and enqueued). ?>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

		<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/images/apple-startup.png"> <!-- @TODO "Graphics" 1: Create an apple startup screen 320x480px. -->
		<link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon.png"> <!-- @TODO "Graphics" 1: Create an apple touch icon 129x129px. -->

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
			<meta name="keywords" content="<?php echo nebula_settings_conditional_text('nebula_keywords', ''); ?>" /><!-- @TODO "Metadata" 1: Replace '' with comma-separated keywords. -->
			<meta name="news_keywords" content="<?php echo nebula_settings_conditional_text('nebula_news_keywords', ''); ?>" /><!-- @TODO "Metadata" 1: Replace '' with comma-separated news event keywords. --> <!-- @TODO "Nebula" 0: W3 Validator Invalid: "Keyword news_keywords is not registered." -->
			<meta name="author" content="<?php echo get_template_directory_uri(); ?>/humans.txt" />

			<meta property="business:contact_data:website" content="<?php echo home_url('/'); ?>" />
			<meta property="business:contact_data:email" content="<?php echo nebula_settings_conditional_text('nebula_contact_email', get_option('admin_email', $GLOBALS['admin_user']->user_email)); //@TODO "Metadata" 2: Verify admin email address. ?>" />
			<meta property="business:contact_data:phone_number" content="+<?php echo nebula_settings_conditional_text('nebula_phone_number', ''); ?>" /> <!-- Ex: "1-315-478-6700" -->
			<meta property="business:contact_data:fax_number" content="+<?php echo nebula_settings_conditional_text('nebula_fax_number', ''); ?>" /> <!-- Ex: "1-315-478-6700" -->
			<meta property="business:contact_data:street_address" content="<?php echo nebula_settings_conditional_text('nebula_street_address', ''); ?>" />
			<meta property="business:contact_data:locality" content="<?php echo nebula_settings_conditional_text('nebula_locality', ''); ?>" /> <!-- City -->
			<meta property="business:contact_data:region" content="<?php echo nebula_settings_conditional_text('nebula_region', ''); ?>" /> <!-- State -->
			<meta property="business:contact_data:postal_code" content="<?php echo nebula_settings_conditional_text('nebula_postal_code', ''); ?>" />
			<meta property="business:contact_data:country_name" content="<?php echo nebula_settings_conditional_text('nebula_country_name', 'USA'); ?>" /> <!-- USA -->
		<?php endif; ?>

		<!-- @TODO "Graphics" 4: Create at least one OG Thumbnail. Minimum Size: 560x560px with a 246px tall safezone in the center. Use og-temp.png as a template (Use PNG to avoid compression artifacts!). -->
		<meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/images/og-thumb.png" />
    	<meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/images/og-thumb2.png" />

		<?php //Business hours of operation. Times should be in the format "5:30 pm" or "17:30". Remove from Foreach loop to override Nebula Settings. ?>
		<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ) : ?>
			<?php if ( get_option('nebula_business_hours_' . $weekday . '_enabled') && get_option('nebula_business_hours_' . $weekday . '_open') != '' && get_option('nebula_business_hours_' . $weekday . '_close') != '' ) : ?>
				<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
				<meta property="business:hours:start" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_open'); ?>" />
				<meta property="business:hours:end" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_close'); ?>" />
			<?php endif; ?>
		<?php endforeach; ?>

		<!-- Facebook Metadata -->
		<?php $GLOBALS['social']['facebook_url'] = nebula_settings_conditional_text('nebula_facebook_url', 'https://www.facebook.com/PinckneyHugo'); //@TODO "Social" 1: Enter the URL of the Facebook page here. ?>
		<?php $GLOBALS['social']['facebook_access_token'] = nebula_settings_conditional_text('nebula_facebook_access_token', ''); //@TODO "Social" 1: Enter Facebook Access Token. This only stored in PHP for reference. Do NOT share or store in browser-facing code. ?>
		<meta property="fb:app_id" content="<?php echo $GLOBALS['social']['facebook_app_id'] = nebula_settings_conditional_text('nebula_facebook_app_id', ''); //@TODO "Social" 1: Enter Facebook App ID. Instructions: http://smashballoon.com/custom-facebook-feed/access-token/ ?>" />
		<meta property="fb:page_id" content="<?php echo $GLOBALS['social']['facebook_page_id'] = nebula_settings_conditional_text('nebula_facebook_page_id', ''); //@TODO "Social" 1: Enter Facebook Page ID. ?>" />
		<meta property="fb:admins" content="<?php echo $GLOBALS['social']['facebook_admin_ids'] = nebula_settings_conditional_text('facebook_admin_ids', ''); //@TODO "Social" 1: Comma separated IDs of FB admins. Ex: "1234,2345,3456" ?>" />

		<!-- Twitter Metadata -->
		<?php $GLOBALS['social']['twitter_url'] = nebula_settings_conditional_text('nebula_twitter_url', 'https://twitter.com/pinckneyhugo'); //@TODO "Social" 1: Enter the URL of the Twitter page here. ?>
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:title" content="<?php the_title(); ?>" />
		<meta name="twitter:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
		<meta name="twitter:image" content="<?php echo get_template_directory_uri(); ?>/images/og-thumb.png" />
		<meta name="twitter:site" content="" /> <!-- "@username" of website -->
		<meta name="twitter:creator" content="" /> <!-- "@username" of content creator -->

		<!-- Other Social Metadata -->
		<?php $GLOBALS['social']['google_plus_url'] = nebula_settings_conditional_text('nebula_google_plus_url', ''); //@TODO "Social" 1: Enter the URL of the Google+ page here. ?>
		<?php $GLOBALS['social']['linkedin_url'] = nebula_settings_conditional_text('nebula_linkedin_url', ''); //@TODO "Social" 1: Enter the URL of the LinkedIn page here. ?>
		<?php $GLOBALS['social']['youtube_url'] = nebula_settings_conditional_text('nebula_youtube_url', ''); //@TODO "Social" 1: Enter the URL of the Youtube page here. ?>
		<?php $GLOBALS['social']['instagram_url'] = nebula_settings_conditional_text('nebula_instagram_url', ''); //@TODO "Social" 1: Enter the URL of the Instagram page here. ?>

		<!-- Local/Geolocation Metadata -->
		<meta name="geo.placename" content="<?php echo nebula_settings_conditional_text('nebula_locality', ''); ?>, <?php echo nebula_settings_conditional_text('nebula_region', ''); ?>" /> <!-- The city (and state if needed). Replace each respective '' with the appropriate value. -->
		<meta name="geo.position" content="<?php echo nebula_settings_conditional_text('nebula_latitude', ''); ?>;<?php echo nebula_settings_conditional_text('nebula_longitude', ''); ?>" /> <!-- Semi-colon separated latitude;longitude. Replace each respsective '' with the appropriate value. -->
		<meta name="geo.region" content="<?php echo bloginfo('language'); ?>" />
		<meta name="ICBM" content="<?php echo nebula_settings_conditional_text('nebula_latitude', ''); ?>, <?php echo nebula_settings_conditional_text('nebula_longitude', ''); ?>" /> <!-- Comma and space separated latitude;longitude. Replace each respsective '' with the appropriate value. -->
		<meta property="place:location:latitude" content="<?php echo nebula_settings_conditional_text('nebula_latitude', ''); ?>" />
		<meta property="place:location:longitude" content="<?php echo nebula_settings_conditional_text('nebula_longitude', ''); ?>" />

		<!--Microsoft Windows 8 Tiles /-->
		<meta name="application-name" content="<?php bloginfo('name'); ?>" />
		<meta name="msapplication-notification" content="frequency=720;polling-uri=<?php bloginfo('rss_url'); ?>"> <!-- @TODO "Nebula" 0: W3 Validator Invalid: "Keyword msapplication-notification is not registered." -->
		<meta name="msapplication-TileColor" content="#ffffff" />
		<meta name="msapplication-square70x70logo" content="<?php echo get_template_directory_uri(); ?>/images/tiny.png" /><!-- 70x70px -->
		<meta name="msapplication-square150x150logo" content="<?php echo get_template_directory_uri(); ?>/images/square.png" /><!-- 150x150px -->
		<meta name="msapplication-wide310x150logo" content="<?php echo get_template_directory_uri(); ?>/images/wide.png" /><!-- 310x150px -->
		<meta name="msapplication-square310x310logo" content="<?php echo get_template_directory_uri(); ?>/images/large.png" /><!-- 310x310px -->

		<script>
			social = []; //Not localized with WP because needs to be able to be modified in header.php if desired.
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

			ga('create', '<?php echo $GLOBALS['ga']; ?>', 'auto'); <?php //Change Tracking ID in Nebula Settings or functions.php ?>
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

		<script>
			<?php //Using this for GA event tracking will note when events are being sent during debug mode (or for admins) without needing to additionally log the event. ?>
			function nebula_event(category, action, label, value, error1, error2) {
				category = typeof category !== 'undefined' ? category : null;
				action = typeof action !== 'undefined' ? action : null;
				label = typeof label !== 'undefined' ? label : null;
				value = typeof value !== 'undefined' ? value : null;
				error1 = typeof error1 !== 'undefined' ? error1 : null;
				error2 = typeof error2 !== 'undefined' ? error2 : null;

				if ( category == 'send' && action == 'event' ) {
					console.warn('Warning: Remove "send" and "event" from nebula_event parameters!');
					category = label;
					action = value;
					label = error1;
					value = error2;
				}

				<?php global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone; ?>
				<?php if ( nebula_settings_conditional('nebula_console_css') ) : //Disable console styles by making this condition false. ?>
					var css = '%c';
					if ( <?php echo ($is_gecko || $is_chrome) ? '1' : '0'; ?> ) {
						var styling = 'padding: 0 0 0 13px; background-image: url(<?php echo get_template_directory_uri(); ?>/images/phg/ga.png); background-repeat: no-repeat; background-size: 10px 10px; background-position-y: 1px; color: #f5981d;';
					} else if ( <?php echo ($is_safari) ? '1' : '0'; ?> ) {
						var styling = 'color: #f5981d;';
					} else {
						var styling = '';
					}
				<?php else : ?>
					var css = '';
					var styling = '';
				<?php endif; ?>

				if ( typeof ga == 'function' ) {
					ga('send', 'event', category, action, label, value); //Important! If modifying this function, DO NOT DELETE THIS LINE!
					var consolePrepend = 'Sending GA event: ';
				} else {
					var consolePrepend = 'ga() is not defined. Attempted event: ';
				}

				if ( document.getElementsByTagName("html")[0].className.indexOf('lte-ie8') < 0 ) { //If not IE8 or less
					if ( <?php echo (is_dev()) ? '1' : '0'; ?> || debug == 1 ) {
						console.log(css + consolePrepend + category + ', ' + action + ', ' + label + ', ' + value, styling);
					}
				}
			}
		</script>
	</head>
	<body <?php body_class(); ?>>
		<div id="fullbodywrapper">
			<div id="fb-root"></div>

			<noscript>
				<iframe class="hidden" src="<?php echo get_template_directory_uri(); ?>/includes/no-js.php?h=<?php echo home_url('/'); ?>&amp;p=<?php echo get_page_uri(); ?>&amp;t=<?php urlencode(get_the_title()); ?>" width="0" height="0" style="display:none;position:absolute;"></iframe>
			</noscript>

			<div id="topbarcon">
				<div class="row mobilenavcon">
					<div class="sixteen columns clearfix">

						<a class="alignleft" href="#mobilenav"><i class="fa fa-bars"></i></a>
						<nav id="mobilenav">
							<?php
								if ( has_nav_menu('mobile') ) {
									wp_nav_menu(array('theme_location' => 'mobile', 'depth' => '9999'));
								} elseif ( has_nav_menu('header') ) {
									wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999'));
								}
							?>
						</nav><!--/mobilenav-->

						<a class="alignright" href="#mobilecontact"><i class="fa fa-users"></i></a>
						<nav id="mobilecontact" class="unhideonload hidden">
							<ul>

					    		<?php $nebula_phone_number = nebula_settings_conditional_text('nebula_phone_number', ''); //@TODO "Metadata" 1: Add phone number here. ?>
					    		<?php if ( $nebula_phone_number ) : ?>
						    		<li>
						    			<a href="tel:<?php echo nebula_phone_format($nebula_phone_number, 'tel'); ?>"><i class="fa fa-phone"></i> <?php echo $nebula_phone_number; ?></a>
						    		</li>
					    		<?php endif; ?>


								<?php $nebula_admin_email = nebula_settings_conditional_text('nebula_contact_email', get_option('admin_email', $admin_user->user_email)); //@TODO "Metadata" 1: Verify this email is the one that should appear. ?>
					    		<li>
					    			<a href="mailto:<?php echo $nebula_admin_email; ?>" target="_blank"><i class="fa fa-envelope"></i> <?php echo $nebula_admin_email; ?></a>
					    		</li>


								<?php $nebula_full_address = nebula_settings_conditional_text_bool('nebula_street_address', $GLOBALS['enc_address'], '760+West+Genesee+Street+Syracuse+NY+13204'); //@TODO "Metadata" 1: Add address here. ?>
					    		<?php if ( $nebula_full_address ) : ?>
						    		<li>
						    			<a class="directions" href="https://www.google.com/maps/dir/Current+Location/<?php echo $nebula_full_address; ?>" target="_blank"><i class="fa fa-compass"></i> Directions<br/><div><small><?php echo $nebula_full_address; ?></small></div></a>
						    		</li>
								<?php endif; ?>

					    	</ul>
						</nav><!--/mobilecontact-->

					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/topbarcon-->

			<?php if ( has_nav_menu('topnav') ) : ?>
				<div class="row topnavcon">
					<div class="sixteen columns">
						<nav id="topnav">
		        			<?php wp_nav_menu(array('theme_location' => 'topnav', 'depth' => '2')); ?>
		        		</nav>
					</div><!--/columns-->
				</div><!--/row-->
			<?php endif; ?>

			<div id="logonavcon" class="row">
				<div class="six columns">
					<?php
						//@TODO "Graphics" 4: Logo should have at least two versions: logo.svg and logo.png - Save them out in the images directory then update the paths below.
						//Important: Do not delete the /phg/ directory from the server; we use our logo in the WP Admin (among other places)!
					?>
					<a class="logocon" href="<?php echo home_url(); ?>">
						<img src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/images/logo.png'" alt="<?php bloginfo('name'); ?>"/>
					</a>
				</div><!--/columns-->
				<?php if ( has_nav_menu('header') ) : ?>
					<div class="ten columns">
						<nav id="primarynav" class="clearfix">
							<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '2')); ?>
		        		</nav>
		        	</div><!--/columns-->
	        	<?php endif; ?>
			</div><!--/row-->

			<div class="container fixedbar" style="position: fixed; top: 0; left: 0; z-index: 9999;">
				<div class="row">
					<div class="four columns">
						<a href="<?php echo home_url(); ?>"><i class="fa fa-home"></i> <?php echo bloginfo('name'); ?></a>
					</div><!--/columns-->
					<div class="twelve columns">
						<nav id="fixednav">
							<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '2')); ?>
		        		</nav>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->

			<?php if ( !is_search() && (array_key_exists('s', $_GET) || array_key_exists('rs', $_GET)) ) : ?>
				<div class="container headerdrawercon">
					<hr/>
					<div class="row">
						<div class="sixteen columns headerdrawer">
							<span>Your search returned only one result. You have been automatically redirected.</span>
							<a class="close" href="<?php the_permalink(); ?>"><i class="fa fa-times"></i></a>
							<?php echo get_search_form(); echo '<script>document.getElementById("s") && document.getElementById("s").focus();</script>' . PHP_EOL; ?>
						</div><!--/columns-->
					</div><!--/row-->
					<hr/>
				</div><!--/container-->
			<?php elseif ( (is_page('search') || is_page_template('tpl-search.php')) && array_key_exists('invalid', $_GET) ) : ?>
				<div class="container headerdrawercon">
					<hr/>
					<div class="row">
						<div class="sixteen columns headerdrawer invalid">
							<span>Your search was invalid. Please try again.</span>
							<a class="close" href="<?php the_permalink(); ?>"><i class="fa fa-times"></i></a>
							<?php echo get_search_form(); echo '<script>document.getElementById("s") && document.getElementById("s").focus();</script>' . PHP_EOL; ?>
						</div><!--/columns-->
					</div><!--/row-->
					<hr/>
				</div><!--/container-->
			<?php elseif ( is_404() || array_key_exists('s', $_GET) ) : ?>
				<div id="suggestedpage" class="container headerdrawercon">
					<hr/>
					<div class="row">
						<div class="sixteen columns headerdrawer">
							<h3>Did you mean?</h3>
							<p><a class="suggestion" href="#"></a></p>

							<a class="close" href="<?php the_permalink(); ?>"><i class="fa fa-times"></i></a>
						</div><!--/columns-->
					</div><!--/row-->
					<hr/>
				</div><!--/container-->
			<?php endif; ?>