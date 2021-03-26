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

			<?php nebula()->timer('Header Template'); ?>
			<header id="header-section">
				<a class="skip-to-content-link sr-only" href="#content-section">Skip to Content</a>

				<?php if ( (get_theme_mod('nebula_offcanvas_menu', true) && (has_nav_menu('offcanvas') || has_nav_menu('primary'))) || get_theme_mod('nebula_mobile_search', true) ): ?>
					<div id="mobilebarcon">
						<div class="row mobilerow">
							<div class="col">
								<?php if ( get_theme_mod('nebula_offcanvas_menu', true) && (has_nav_menu('offcanvas') || has_nav_menu('primary')) ): ?>
									<?php nebula()->timer('Offcanvas Menu'); ?>
									<a class="offcanvasnavtrigger alignleft" href="#offcanvasnav" title="Navigation"><i class="fas fa-bars"></i></a>
									<nav id="offcanvasnav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Offcanvas navigation">
										<meta itemprop="name" content="Offcanvas Menu">
										<?php
											$mmenu_menu = false;
											if ( has_nav_menu('offcanvas') ){
												$mmenu_menu = 'offcanvas';
											} elseif ( has_nav_menu('primary') ){
												$mmenu_menu = 'primary';
											}

											if ( !empty($mmenu_menu) ){
												wp_nav_menu(array('theme_location' => $mmenu_menu, 'menu_id' => 'main-panel')); //Do not change menu_id (for Mmenu tabs)
											}
										?>

										<?php if ( has_nav_menu('utility') ): ?>
											<?php wp_nav_menu(array('theme_location' => 'utility', 'menu_id' => 'utility-panel')); //Do not change menu_id (for Mmenu tabs) ?>
										<?php endif; ?>
									</nav>
									<?php nebula()->timer('Offcanvas Menu', 'end'); ?>
								<?php endif; ?>

								<?php if ( get_theme_mod('nebula_mobile_search', true) ): ?>
									<form id="mobileheadersearch" class="nebula-search search" method="get" action="<?php echo home_url('/'); ?>" role="search">
										<?php
											if ( !empty($_GET['s']) || !empty($_GET['rs']) ) {
												$current_search = ( !empty($_GET['s']) )? $_GET['s'] : $_GET['rs'];
											}
											$header_search_placeholder = ( isset($current_search) )? $current_search : __('Search', 'nebula');
										?>
										<label class="sr-only" for="nebula-mobile-search"><?php _e('Search', 'nebula'); ?></label>
										<input id="nebula-mobile-search" class="open input search" type="search" name="s" placeholder="<?php echo $header_search_placeholder; ?>" autocomplete="off" x-webkit-speech />
									</form>
								<?php endif; ?>
							</div><!--/col-->
						</div><!--/row-->
					</div>
				<?php endif; ?>

				<?php if ( get_theme_mod('menu_position') === 'above' ): ?>
					<?php get_template_part('inc/navigation'); ?>
				<?php endif; ?>

				<?php if ( is_active_sidebar('header-widget-area') ): ?>
					<?php nebula()->timer('Header Widgets'); ?>
					<div id="header-widget-area">
						<div class="container">
							<?php dynamic_sidebar('header-widget-area'); ?>
						</div><!--/container-->
					</div>
					<?php nebula()->timer('Header Widgets', 'end'); ?>
				<?php endif; ?>
			</header>
			<?php nebula()->timer('Header Template', 'end'); ?>