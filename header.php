<!doctype html>
<html <?php language_attributes(); ?> class=" <?php echo ( nebula()->is_debug() )? 'debug' : ''; ?> no-js">
	<head>
		<?php get_template_part('inc/metadata'); //Do not place tags above this. ?>
		<?php wp_head(); ?>
		<?php get_template_part('inc/analytics'); ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="body-wrapper">
			<div id="fb-root"></div>
			<?php do_action('nebula_body_open'); ?>

			<div id="header-section" role="banner">
				<?php if ( get_theme_mod('nebula_offcanvas_menu', true) || get_theme_mod('nebula_mobile_search', true) ): ?>
					<div id="mobilebarcon">
						<div class="row mobilenavcon">
							<div class="col">
								<?php if ( get_theme_mod('nebula_offcanvas_menu', true) ): ?>
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
								<?php endif; ?>

								<?php if ( get_theme_mod('nebula_mobile_search', true) ): ?>
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
								<?php endif; ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/topbarcon-->
				<?php endif; ?>

				<div id="navigation-section">
					<?php if ( has_nav_menu('utility') ): ?>
						<div id="utilitynavcon">
							<div class="container">
								<div class="row">
									<div class="col">
										<nav id="utilitynav" role="navigation">
						        			<?php wp_nav_menu(array('theme_location' => 'utility', 'depth' => '2')); ?>
						        		</nav>
									</div><!--/col-->
								</div><!--/row-->
							</div><!--/container-->
						</div>
					<?php endif; ?>

					<div id="logonavcon">
						<div class="container">
							<div class="row align-items-center">
								<div class="col-md-4">
									<a class="logocon" href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name'); ?>">
										<?php
											$logo = get_theme_file_uri('/assets/img/logo.svg');
											if ( get_theme_mod('custom_logo') ){ //If the Customizer logo exists
												$logo = nebula()->get_thumbnail_src(get_theme_mod('custom_logo'));
												if ( get_theme_mod('one_color_logo') ){ //If the one-color logo exists
													if ( (is_front_page() && get_theme_mod('nebula_hero_single_color_logo')) || (!is_front_page() && get_theme_mod('nebula_header_single_color_logo')) ){ //If it is the frontpage and the home one-color logo is requested -OR- if it is a subpage and the header one-color logo is requested
														$logo = get_theme_mod('one_color_logo');
													}
												}
											}
										?>
										<img class="svg" src="<?php echo $logo; ?>" alt="<?php bloginfo('name'); ?>"/>
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