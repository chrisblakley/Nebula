<?php
/**
 * Theme Footer
 */
?>
			<?php if ( is_active_sidebar('footer-widget-area') ): ?>
				<div id="footer-widget-section">
					<?php if ( get_theme_mod('nebula_fwa_overlay_color') || get_theme_mod('nebula_fwa_overlay_opacity') ): ?>
						<div class="custom-color-overlay"></div>
					<?php endif; ?>

					<div class="container">
						<div class="row">
							<?php dynamic_sidebar('footer-widget-area'); ?>
						</div><!--/row-->
					</div><!--/container-->
				</div>
			<?php endif; ?>

			<div id="footer-section" role="contentinfo">
				<?php if ( get_theme_mod('nebula_footer_overlay_color') || get_theme_mod('nebula_footer_overlay_opacity') ): ?>
					<div class="custom-color-overlay"></div>
				<?php else: ?>
					<div class="nebula-color-overlay"></div>
				<?php endif; ?>

				<div class="container">
					<?php if ( get_theme_mod('nebula_footer_logo') ): ?>
						<div class="row footerlogocon">
							<div class="col">
								<a class="footerlogo" href="<?php echo home_url('/'); ?>">
									<?php
										$logo = get_theme_file_uri('/assets/img/logo.svg');
										if ( get_theme_mod('custom_logo') ){ //If the Customizer logo exists
											$logo = nebula()->get_thumbnail_src(get_theme_mod('custom_logo'));
											if ( get_theme_mod('one_color_logo') && get_theme_mod('nebula_footer_single_color_logo') ){ //If the one-color logo exists and is requested
												$logo = get_theme_mod('one_color_logo');
											}
										}
									?>
									<img class="svg" src="<?php echo $logo; ?>" alt="<?php bloginfo('name'); ?>"/>
								</a>
							</div><!--/col-->
						</div><!--/row-->
					<?php endif; ?>
					<div class="row powerfootercon">
						<div class="col">
							<?php if ( has_nav_menu('footer') ): ?>
								<nav id="powerfooter" role="navigation">
									<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2')); ?>
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
									&copy; <?php echo date('Y'); ?> <a href="<?php echo home_url('/'); ?>"><strong><?php echo ( nebula()->get_option('site_owner') )? nebula()->get_option('site_owner') : get_bloginfo('name'); ?></strong></a>, <em>all rights reserved</em>.
								<?php endif; ?>
							</p>

							<?php if ( get_theme_mod('nebula_footer_search', true) ): ?>
								<form class="nebula-search search footer-search" method="get" action="<?php echo home_url('/'); ?>">
									<label class="sr-only" for="nebula-footer-search">Search</label>
									<input id="nebula-footer-search" class="open input search" type="search" name="s" placeholder="Search" autocomplete="off" role="search" x-webkit-speech />
								</form>
							<?php endif; ?>
						</div><!--/col-->
					</div><!--/row-->
				</div><!--/container-->

				<?php do_action('nebula_footer'); ?>
			</div>

			<?php wp_footer(); ?>
		</div><!--/body-wrapper-->
	</body>
</html>
