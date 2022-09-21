		<?php if ( is_active_sidebar('footer-widget-area') ): ?>
			<?php nebula()->timer('Footer Widgets'); ?>
			<section id="footer-widget-section">
				<?php if ( get_theme_mod('nebula_fwa_overlay_color') || get_theme_mod('nebula_fwa_overlay_opacity') ): ?>
					<div class="custom-color-overlay"></div>
				<?php endif; ?>

				<div class="container">
					<div class="row">
						<?php dynamic_sidebar('footer-widget-area'); ?>
					</div><!--/row-->
				</div><!--/container-->
			</section>
			<?php nebula()->timer('Footer Widgets', 'end'); ?>
		<?php endif; ?>

		<?php nebula()->timer('Footer Section'); ?>
		<footer id="footer-section" class="lazy-load">
			<?php if ( get_theme_mod('nebula_footer_overlay_color') || get_theme_mod('nebula_footer_overlay_opacity') ): ?>
				<div class="custom-color-overlay"></div>
			<?php else: ?>
				<div class="nebula-color-overlay"></div>
			<?php endif; ?>

			<div class="container">
				<?php if ( get_theme_mod('nebula_footer_logo') ): ?>
					<div class="row justify-content-center footerlogocon">
						<div class="col-3">
							<a class="footerlogo" href="<?php echo home_url('/'); ?>" aria-label="<?php bloginfo('name'); ?>">
								<?php $logo = nebula()->logo('footer'); ?>
								<?php if ( !empty($logo) ): ?>
									<img class="svg" src="<?php echo $logo; ?>" alt="<?php bloginfo('name'); ?>" importance="low" />
								<?php else: //Otherwise fallback to the Site Title text ?>
									<?php bloginfo('name'); ?>
								<?php endif; ?>
							</a>
						</div><!--/col-->
					</div><!--/row-->
				<?php endif; ?>
				<div class="row powerfootercon">
					<div class="col">
						<?php if ( has_nav_menu('footer') ): ?>
							<nav id="powerfooter" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Footer navigation">
								<meta itemprop="name" content="Footer Menu">
								<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => 2)); ?>
							</nav>
						<?php endif; ?>
					</div><!--/col-->
				</div><!--/row-->
				<div class="row copyright-con">
					<div class="col">
						<p class="copyright">
							<?php if ( get_theme_mod('nebula_footer_text') ): ?>
								<?php echo get_theme_mod('nebula_footer_text'); ?>
							<?php else:?>
								&copy; <?php echo date('Y'); ?> <a href="<?php echo home_url('/'); ?>"><strong><?php echo ( nebula()->get_option('site_owner') )? esc_html(nebula()->get_option('site_owner')) : get_bloginfo('name'); ?></strong></a>, <em><?php _e('all rights reserved', 'nebula'); ?></em>.
							<?php endif; ?>
						</p>

						<?php if ( get_theme_mod('nebula_footer_search', true) ): ?>
							<form class="nebula-search search footer-search" method="get" action="<?php echo home_url('/'); ?>" role="search">
								<div class="nebula-input-group">
									<i class="fa-solid fa-magnifying-glass"></i>
									<label class="visually-hidden" for="nebula-footer-search"><?php _e('Search', 'nebula'); ?></label>
									<input id="nebula-footer-search" class="open input search" type="search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech />
								</div>
							</form>
						<?php endif; ?>
					</div><!--/col-->
				</div><!--/row-->
			</div><!--/container-->

			<?php do_action('nebula_footer'); ?>
		</footer>
		<?php nebula()->timer('Footer Section', 'end'); ?>

		<?php wp_footer(); ?>
	</body>
</html>