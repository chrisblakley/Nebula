<!DOCTYPE html>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie6 lt-ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="no-js ie ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="no-js ie ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IEMobile]><html <?php language_attributes(); ?> class="no-js ie iem7" dir="ltr"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class=" <?php echo ( is_debug() ) ? 'debug' : ' '; ?> no-js"><!--<![endif]-->
	<?php /* manifest="<?php echo get_template_directory_uri(); ?>/includes/manifest.appcache" */ //To begin setting up ApplicationCache, move this attribute to the <html> tag. ?>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="<?php bloginfo('charset'); ?>" />

		<title><?php wp_title('-', true, 'right'); ?></title>

		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/includes/manifest.json"> <!-- Web App Manifest Icons/Settings -->
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
		<link rel="profile" href="http://gmpg.org/xfn/11" />

		<?php //Stylesheets are loaded at the top of functions.php (so they can be registerred and enqueued). ?>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

		<!-- Facebook Metadata -->
		<?php $GLOBALS['social']['facebook_url'] = nebula_settings_conditional_text('nebula_facebook_url', ''); //@TODO "Social" 1: Enter the URL of the Facebook page here. ?>
		<?php $GLOBALS['social']['facebook_access_token'] = nebula_settings_conditional_text('nebula_facebook_access_token', ''); //@TODO "Social" 1: Enter Facebook Access Token. This only stored in PHP for reference. Do NOT share or store in browser-facing code. ?>
		<meta property="fb:app_id" content="<?php echo $GLOBALS['social']['facebook_app_id'] = nebula_settings_conditional_text('nebula_facebook_app_id', ''); //@TODO "Social" 1: Enter Facebook App ID. Instructions: http://smashballoon.com/custom-facebook-feed/access-token/ ?>" />
		<meta property="fb:page_id" content="<?php echo $GLOBALS['social']['facebook_page_id'] = nebula_settings_conditional_text('nebula_facebook_page_id', ''); //@TODO "Social" 1: Enter Facebook Page ID. ?>" />
		<meta property="fb:admins" content="<?php echo $GLOBALS['social']['facebook_admin_ids'] = nebula_settings_conditional_text('facebook_admin_ids', ''); //@TODO "Social" 1: Comma separated IDs of FB admins. Ex: "1234,2345,3456" ?>" />

		<!-- Twitter Metadata -->
		<?php //twitter:image is located in includes/metagraphics.php ?>
		<?php $GLOBALS['social']['twitter_url'] = nebula_settings_conditional_text('nebula_twitter_url', ''); //@TODO "Social" 1: Enter the URL of the Twitter page here. ?>

		<!-- Other Social Metadata -->
		<?php //@TODO "SEO" 3: Create/update information on Google Business! http://www.google.com/business/ ?>
		<?php $GLOBALS['social']['google_plus_url'] = nebula_settings_conditional_text('nebula_google_plus_url', ''); //@TODO "Social" 1: Enter the URL of the Google+ page here. ?>
		<?php $GLOBALS['social']['linkedin_url'] = nebula_settings_conditional_text('nebula_linkedin_url', ''); //@TODO "Social" 1: Enter the URL of the LinkedIn page here. ?>
		<?php $GLOBALS['social']['youtube_url'] = nebula_settings_conditional_text('nebula_youtube_url', ''); //@TODO "Social" 1: Enter the URL of the Youtube page here. ?>
		<?php $GLOBALS['social']['instagram_url'] = nebula_settings_conditional_text('nebula_instagram_url', ''); //@TODO "Social" 1: Enter the URL of the Instagram page here. ?>

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
			var analyticsScript = ( <?php echo ( is_debug() ) ? 1 : 0; ?> ? 'analytics_debug.js' : 'analytics.js' );

			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/' + analyticsScript,'ga');

			ga('create', '<?php echo $GLOBALS['ga']; ?>', 'auto'); <?php //Change Tracking ID in Nebula Settings or functions.php! ?>
			//ga('require', 'displayfeatures');
			ga('send', 'pageview');
			<?php //@TODO "Analytics" 5: Admin > View Settings - Turn on Site Search Tracking and enter "s,rs" in the Query Parameter input field! ?>
		</script>

		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="fullbodywrapper">
			<div id="fb-root"></div>

			<div id="topbarcon">
				<div class="row mobilenavcon">
					<div class="sixteen columns clearfix">

						<a class="mobilenavtrigger alignleft" href="#mobilenav"><i class="fa fa-bars"></i></a>
						<nav id="mobilenav">
							<?php
								if ( has_nav_menu('mobile') ) {
									wp_nav_menu(array('theme_location' => 'mobile', 'depth' => '9999'));
								} elseif ( has_nav_menu('header') ) {
									wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999'));
								}
							?>
						</nav><!--/mobilenav-->

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
					<?php fpo_component_start("Logo"); ?>
					<a class="logocon" href="<?php echo home_url(); ?>">
						<?php fpo_image('100%', '100px'); ?>
					</a>
					<?php fpo_component_end(); ?>
				</div><!--/columns-->
				<div class="ten columns">
					<?php if ( has_nav_menu('header') ) : ?>
						<nav id="primarynav" class="clearfix">
							<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '2')); ?>
		        		</nav>
	        		<?php endif; ?>
	        	</div><!--/columns-->
			</div><!--/row-->

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