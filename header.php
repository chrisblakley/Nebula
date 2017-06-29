<?php $debug_class = ( nebula()->is_debug() )? 'debug' : ''; ?>
<!doctype html>
<!--[if lt IE 7]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie6 lt-ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 7]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie7 lte-ie7 lt-ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 8]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie8 lte-ie8 lt-ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IE 9]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie ie9 lte-ie9 lt-ie10"><![endif]-->
<!--[if IEMobile]><html <?php language_attributes(); ?> class="<?php echo $debug_class; ?> no-js ie iem7" dir="ltr"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class=" <?php echo $debug_class; ?> no-js"><!--<![endif]-->
	<head>
		<?php get_template_part('inc/metadata'); //Do not place tags above this. ?>
		<?php wp_head(); ?>
		<?php get_template_part('inc/analytics'); //Google Analytics and other analytics trackers. ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="body-wrapper">
			<div id="header-section" role="banner">
				<div id="fb-root"></div>
				<?php do_action('nebula_body_open'); ?>

				<div id="mobilebarcon">
					<div class="row mobilenavcon">
						<div class="col">
							<a class="mobilenavtrigger alignleft" href="#mobilenav" title="Navigation"><i class="fa fa-bars"></i></a>
							<nav id="mobilenav" role="navigation">
								<?php
									if ( has_nav_menu('mobile') ){
										wp_nav_menu(array('theme_location' => 'mobile', 'depth' => '9999'));
									} elseif ( has_nav_menu('primary') ){
										wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999'));
									}
								?>
							</nav>

							<form id="mobileheadersearch" class="nebula-search search" method="get" action="<?php echo home_url('/'); ?>">
								<?php
									if ( !empty($_GET['s']) || !empty($_GET['rs']) ) {
										$current_search = ( !empty($_GET['s']) )? $_GET['s'] : $_GET['rs'];
									}
									$header_search_placeholder = ( isset($current_search) )? $current_search : 'What are you looking for?' ;
								?>
								<label class="sr-only" for="nebula-mobile-search">Search</label>
								<input id="nebula-mobile-search" class="open input search" type="search" name="s" placeholder="<?php echo $header_search_placeholder; ?>" autocomplete="off" role="search" x-webkit-speech />
							</form>
						</div><!--/col-->
					</div><!--/row-->
				</div><!--/topbarcon-->

				<div id="navigation-section">
					<?php if ( has_nav_menu('secondary') ): ?>
						<div id="secondarynavcon">
							<div class="container">
								<div class="row">
									<div class="col">
										<nav id="secondarynav" role="navigation">
						        			<?php wp_nav_menu(array('theme_location' => 'secondary', 'depth' => '2')); ?>
						        		</nav>
									</div><!--/col-->
								</div><!--/row-->
							</div><!--/container-->
						</div>
					<?php endif; ?>

					<div id="logonavcon" class="<?php echo ( get_bloginfo('description') != '' && !get_theme_mod('nebula_hide_blogdescription', false) )? 'has-description' : ''; ?>">
						<div class="container">
							<div class="row">
								<div class="col-md-4">
									<a class="logocon" href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name'); ?>">
										<img class="svg" src="<?php echo get_template_directory_uri(); ?>/assets/img/logo.svg" alt="<?php bloginfo('name'); ?>"/>
									</a>
								</div><!--/col-->
								<div class="col-md-8">
									<?php if ( has_nav_menu('primary') ): ?>
										<nav id="primarynav" class="clearfix">
											<?php wp_nav_menu(array('theme_location' => 'primary', 'depth' => '2')); ?>
						        		</nav>
					        		<?php endif; ?>
					        	</div><!--/col-->
							</div><!--/row-->
						</div><!--/container-->
					</div>
				</div>
			</div><!--/header-section-->