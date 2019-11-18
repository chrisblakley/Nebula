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

			<header id="header-section">
				<?php if ( (get_theme_mod('nebula_offcanvas_menu', true) && (has_nav_menu('mobile') || has_nav_menu('primary'))) || get_theme_mod('nebula_mobile_search', true) ): ?>
					<div id="mobilebarcon">
						<div class="row mobilenavcon">
							<div class="col">
								<?php if ( get_theme_mod('nebula_offcanvas_menu', true) && (has_nav_menu('mobile') || has_nav_menu('primary')) ): ?>
									<a class="mobilenavtrigger alignleft" href="#mobilenav" title="Navigation"><i class="fas fa-bars"></i></a>
									<nav id="mobilenav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Mobile navigation">
										<meta itemprop="name" content="Mobile Menu">
										<?php
											if ( has_nav_menu('mobile') ){
												wp_nav_menu(array('theme_location' => 'mobile', 'menu_id' => 'main-panel')); //Do not change menu_id
											} elseif ( has_nav_menu('primary') ){
												wp_nav_menu(array('theme_location' => 'header', 'menu_id' => 'main-panel')); //Do not change menu_id
											}
										?>

										<?php if ( has_nav_menu('utility') ): ?>
											<?php wp_nav_menu(array('theme_location' => 'utility', 'menu_id' => 'utility-panel')); //Do not change menu_id ?>
										<?php endif; ?>
									</nav>
								<?php endif; ?>

								<?php if ( get_theme_mod('nebula_mobile_search', true) ): ?>
									<form id="mobileheadersearch" class="nebula-search search" method="get" action="<?php echo home_url('/'); ?>" role="search">
										<?php
											if ( !empty($_GET['s']) || !empty($_GET['rs']) ) {
												$current_search = ( !empty($_GET['s']) )? $_GET['s'] : $_GET['rs'];
											}
											$header_search_placeholder = ( isset($current_search) )? $current_search : __('What are you looking for?', 'nebula');
										?>
										<label class="sr-only" for="nebula-mobile-search"><?php _e('Search', 'nebula'); ?></label>
										<input id="nebula-mobile-search" class="open input search" type="search" name="s" placeholder="<?php echo $header_search_placeholder; ?>" autocomplete="off" x-webkit-speech />
									</form>
								<?php endif; ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/topbarcon-->
				<?php endif; ?>

				<?php if ( get_theme_mod('menu_position') === 'above' ): ?>
					<?php get_template_part('inc/navigation'); ?>
				<?php endif; ?>

				<?php if ( is_active_sidebar('header-widget-area') ): ?>
					<div id="header-widget-area">
						<div class="container">
							<?php dynamic_sidebar('header-widget-area'); ?>
						</div><!--/container-->
					</div>
				<?php endif; ?>
			</header>