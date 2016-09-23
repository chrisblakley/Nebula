<?php $debug_class = ( is_debug() )? 'debug' : ''; ?>
<!doctype html>
<!--[if lt IE 7]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie6 lt-ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 7]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 8]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 9]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IEMobile]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie iem7" dir="ltr"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class=" <?php echo $debug_class; ?> no-js"><!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo('charset'); ?>" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<?php do_action('nebula_head_open'); ?>

		<meta name="referrer" content="always">
		<meta name="HandheldFriendly" content="True" />
		<meta name="MobileOptimized" content="320" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta class="theme-color" name="theme-color" content="<?php echo nebula_sass_color('primary'); ?>">
		<meta class="theme-color" name="msapplication-navbutton-color" content="<?php echo nebula_sass_color('primary'); ?>">
		<meta class="theme-color" name="apple-mobile-web-app-status-bar-style" content="<?php echo nebula_sass_color('primary'); ?>">
		<?php get_template_part('includes/metadata'); ?>

		<link rel="manifest" href="<?php echo nebula_manifest_json_location(); ?>" />
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<?php nebula_prerender(); ?>
		<?php wp_head(); ?>
		<?php get_template_part('includes/analytics'); //Google Analytics and other analytics trackers. ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="body-wrapper">
			<div id="header-section">
				<div id="fb-root"></div>
				<?php do_action('nebula_body_open'); ?>

				<div id="mobilebarcon">
					<div class="row mobilenavcon">
						<div class="col-md-12">
							<a class="mobilenavtrigger alignleft" href="#mobilenav" title="Navigation"><i class="fa fa-bars"></i></a>
							<nav id="mobilenav">
								<?php
									if ( has_nav_menu('mobile') ){
										wp_nav_menu(array('theme_location' => 'mobile', 'depth' => '9999'));
									} elseif ( has_nav_menu('primary') ){
										wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999'));
									}
								?>
							</nav>

							<form id="mobileheadersearch" class="nebula-search-iconable search" method="get" action="<?php echo home_url('/'); ?>">
								<?php
									if ( !empty($_GET['s']) || !empty($_GET['rs']) ) {
										$current_search = ( !empty($_GET['s']) )? $_GET['s'] : $_GET['rs'];
									}
									$header_search_placeholder = ( isset($current_search) )? $current_search : 'What are you looking for?' ;
								?>
								<input class="nebula-search open input search" type="search" name="s" placeholder="<?php echo $header_search_placeholder; ?>" autocomplete="off" x-webkit-speech />
							</form>
						</div><!--/col-->
					</div><!--/row-->
				</div><!--/topbarcon-->

				<?php if ( has_nav_menu('secondary') ): ?>
					<div id="secondarynavcon">
						<div class="container">
							<div class="row">
								<div class="col-md-12">
									<nav id="secondarynav">
					        			<?php wp_nav_menu(array('theme_location' => 'secondary', 'depth' => '2')); ?>
					        		</nav>
								</div><!--/col-->
							</div><!--/row-->
						</div><!--/container-->
					</div>
				<?php endif; ?>

				<div id="logonavcon">
					<div class="container">
						<div class="row">
							<div class="col-lg-4">
								<a class="logocon" href="<?php echo home_url(); ?>">
									<img class="svg" src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" alt="<?php bloginfo('name'); ?>"/>
								</a>
							</div><!--/col-->
							<div class="col-lg-8">
								<?php if ( has_nav_menu('primary') ): ?>
									<nav id="primarynav" class="clearfix">
										<?php wp_nav_menu(array('theme_location' => 'primary', 'depth' => '2')); ?>
					        		</nav>
				        		<?php endif; ?>
				        	</div><!--/col-->
						</div><!--/row-->
					</div><!--/container-->
				</div>
			</div><!--/header-section-->

			<?php get_template_part('includes/header_drawer'); //Header drawer logic. ?>