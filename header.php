<?php $debug_class = ( is_debug() ) ? 'debug' : ''; ?>
<!doctype html><?php /* manifest="<?php echo get_template_directory_uri(); ?>/includes/manifest.appcache" */ //To begin setting up ApplicationCache, move this attribute inside the <html> tag. ?>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie6 lt-ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IEMobile]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie iem7" dir="ltr"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class=" <?php echo $debug_class; ?> no-js"><!--<![endif]-->
	<head>
		<?php do_action('nebula_head_open'); ?>

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="referrer" content="always">
		<meta charset="<?php bloginfo('charset'); ?>" />

		<title><?php wp_title('-', true, 'right'); ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="HandheldFriendly" content="True" />
		<meta name="MobileOptimized" content="320" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/includes/manifest.json" /> <!-- Web App Manifest Icons/Settings -->
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

		<?php include_once('includes/metadata.php'); //All text components of metadata are declared in this file. ?>
		<?php include_once('includes/metagraphics.php'); //All graphic components of metadata are declared in this file. ?>

		<?php //Stylesheets are loaded at the top of functions.php (so they can be registerred and enqueued). ?>

		<script> //Universal Analytics
			<?php //@TODO "Analytics" 5: Admin > View Settings - Turn on Site Search Tracking and enter "s,rs" in the Query Parameter input field! ?>
			var analyticsScript = ( <?php echo ( is_debug() ) ? 1 : 0; ?> ? 'analytics_debug.js' : 'analytics.js' );

			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/' + analyticsScript,'ga');

			ga('create', '<?php echo $GLOBALS['ga']; ?>', 'auto'); <?php //Change Tracking ID in Nebula Settings or functions.php! ?>
			<?php if ( nebula_adwords_enabled() ): //Enable AdWords integration in Nebula Settings, or delete this conditional. ?>
				ga('require', 'displayfeatures');
			<?php endif; ?>
			ga('send', 'pageview');
		</script>

		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="fullbodywrapper">
			<div id="fb-root"></div>

			<noscript>
				<?php //Certain security plugins and htaccess settings can prevent the query strings in this iframe src from working. If page info for "JavaScript Disabled" in GA is not right, that is a likely reason. ?>
				<iframe class="hidden" src="<?php echo get_template_directory_uri(); ?>/includes/no-js.php?h=<?php echo home_url('/'); ?>&amp;p=<?php echo nebula_url_components('all'); ?>&amp;t=<?php echo urlencode(get_the_title($post->ID)); ?>" width="0" height="0" style="display:none;position:absolute;"></iframe>
			</noscript>

			<?php do_action('nebula_body_open'); ?>

			<div id="mobilebarcon">
				<div class="row mobilenavcon">
					<div class="sixteen columns clearfix">
						<a class="mobilenavtrigger alignleft" href="#mobilenav" title="Navigation"><i class="fa fa-bars"></i></a>
						<nav id="mobilenav">
							<?php
								if ( has_nav_menu('mobile') ){
									wp_nav_menu(array('theme_location' => 'mobile', 'depth' => '9999'));
								} elseif ( has_nav_menu('primary') ){
									wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999'));
								}
							?>
						</nav><!--/mobilenav-->

						<form id="mobileheadersearch" class="nebula-search-iconable search" method="get" action="<?php echo home_url('/'); ?>">
							<?php
								if ( !empty($_GET['s']) ) {
									$current_search = $_GET['s'];
								} elseif ( !empty($_GET['rs']) ) {
									$current_search = $_GET['rs'];
								}
								$header_search_placeholder = ( isset($current_search) ) ? $current_search : 'What are you looking for?' ;
							?>
							<input class="nebula-search open input search" type="search" name="s" placeholder="<?php echo $header_search_placeholder; ?>" autocomplete="off" x-webkit-speech />
						</form>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/topbarcon-->

			<?php if ( has_nav_menu('secondary') ) : ?>
				<div id="secondarynavcon" class="container">
					<div class="row">
						<div class="sixteen columns">
							<nav id="secondarynav">
			        			<?php wp_nav_menu(array('theme_location' => 'secondary', 'depth' => '2')); ?>
			        		</nav>
						</div><!--/columns-->
					</div><!--/row-->
				</div><!--/container-->
			<?php endif; ?>

			<div id="logonavcon" class="container">
				<div class="row">
					<div class="six columns">
						<?php
							//@TODO "Graphics" 4: Logo should have at least two versions: logo.svg and logo.png - Save them out in the images directory then update the paths below.
							//Important: Do not delete the /phg/ directory from the server; we use our logo in the WP Admin (among other places)!
						?>
						<a class="logocon" href="<?php echo home_url(); ?>">
							<img src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/images/logo.png'" alt="<?php bloginfo('name'); ?>"/>
						</a>
					</div><!--/columns-->
					<div class="ten columns">
						<?php if ( has_nav_menu('primary') ) : ?>
							<nav id="primarynav" class="clearfix">
								<?php wp_nav_menu(array('theme_location' => 'primary', 'depth' => '2')); ?>
			        		</nav>
		        		<?php endif; ?>
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
					<hr class="zero" />
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
					<hr class="zero" />
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
					<hr class="zero" />
				</div><!--/container-->
			<?php endif; ?>