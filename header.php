<!doctype html>
<html <?php language_attributes(); ?> class=" <?php echo ( nebula()->is_debug() )? 'debug' : ''; ?> no-js">
	<head>
		<?php get_template_part('inc/metadata'); //Do not place tags above this. ?>
		<?php wp_head(); ?>
		<?php get_template_part('inc/analytics'); ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="fb-root"></div>
		<?php do_action('nebula_body_open'); ?>

		<?php nebula()->timer('Header Template'); ?>
		<header id="header-section">
			<a id="skip-to-content-link" class="visually-hidden-focusable" href="#content-section">Skip to Content</a>

			<?php if ( (get_theme_mod('nebula_offcanvas_menu', true) && (has_nav_menu('offcanvas') || has_nav_menu('primary'))) || get_theme_mod('nebula_mobile_search', true) ): ?>
				<div id="mobilebarcon">
					<div class="row mobilerow">
						<div class="col">
							<?php if ( get_theme_mod('nebula_offcanvas_menu', true) && (has_nav_menu('offcanvas') || has_nav_menu('primary')) ): ?>
								<a class="offcanvasnavtrigger alignleft" data-bs-toggle="offcanvas" href="#offcanvas-menu" role="button" aria-controls="offcanvas-menu" title="Navigation"><i class="fa-solid fa-bars"></i></a>
							<?php endif; ?>

							<?php if ( get_theme_mod('nebula_mobile_search', true) ): ?>
								<form id="mobileheadersearch" class="nebula-search search" method="get" action="<?php echo home_url('/'); ?>" role="search">
									<?php
										if ( !empty(nebula()->super->get['s']) || !empty(nebula()->super->get['rs']) ) {
											$current_search = ( !empty(nebula()->super->get['s']) )? nebula()->super->get['s'] : nebula()->super->get['rs'];
										}
										$header_search_placeholder = ( isset($current_search) )? $current_search : __('Search', 'nebula');
									?>
									<label class="visually-hidden" for="nebula-mobile-search"><?php _e('Search', 'nebula'); ?></label>
									<input id="nebula-mobile-search" class="open input search" type="search" name="s" placeholder="<?php echo $header_search_placeholder; ?>" autocomplete="off" x-webkit-speech />
								</form>
							<?php endif; ?>
						</div><!--/col-->
					</div><!--/row-->
				</div>

				<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas-menu" aria-labelledby="offcanvas-menu-label">
					<?php nebula()->timer('Offcanvas Menu'); ?>
					<div class="offcanvas-header">
						<h5 class="offcanvas-title" id="offcanvas-menu-label"><?php echo apply_filters('nebula_offcanvas_menu_title', __('Menu', 'nebula')); ?></h5>
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<div class="offcanvas-body">
						<?php do_action('nebula_offcanvas_top'); ?>

						<nav id="offcanvas-nav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Offcanvas navigation">
							<meta itemprop="name" content="Offcanvas Menu">

							<ul class="menu">
								<li class="menu-item <?php echo ( is_front_page() )? 'current-menu-item' : ''; ?>">
									<?php $offcanvas_menu_home_text = apply_filters('nebula_offcanvas_menu_home_text', get_bloginfo('name')); //Allow others to modify the home link text in the offcanvas menu ?>
									<?php if ( is_front_page() ): ?>
										<a href="<?php echo home_url('/'); ?>" aria-label="<?php bloginfo('name'); ?>"><?php echo $offcanvas_menu_home_text; ?></a>
									<?php else: ?>
										<a href="<?php echo home_url('/'); ?>" aria-label="<?php bloginfo('name'); ?>"><i class="fa fa-fw fa-chevron-left"></i> <?php echo $offcanvas_menu_home_text; ?> Home</a>
									<?php endif; ?>

								</li>
							</ul>

							<?php
								//Determine which menu to use (offcanvas or primary)
								$offcanvas_menu = false;
								if ( has_nav_menu('offcanvas') ){
									$offcanvas_menu = 'offcanvas';
								} elseif ( has_nav_menu('primary') ){
									$offcanvas_menu = 'primary';
								}

								if ( !empty($offcanvas_menu) ){
									wp_nav_menu(array('theme_location' => $offcanvas_menu, 'menu_id' => 'main-panel'));
								}
							?>

							<?php if ( has_nav_menu('utility') ): ?>
								<?php wp_nav_menu(array('theme_location' => 'utility', 'menu_id' => 'utility-panel')); ?>
							<?php endif; ?>
						</nav>

						<?php do_action('nebula_offcanvas_bottom'); ?>
					</div>
					<?php nebula()->timer('Offcanvas Menu', 'end'); ?>
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