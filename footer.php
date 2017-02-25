<?php
/**
 * Theme Footer
 */
?>
			<div id="footer-section">
				<?php get_template_part('includes/footer_widgets'); //Footer widget logic. ?>

				<?php if ( has_nav_menu('footer') ): ?>
					<div class="footerlinks">
						<div class="container">
							<div class="row powerfootercon">
								<div class="col-md-12">
									<nav id="powerfooter">
										<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2')); ?>
									</nav>
								</div><!--/col-->
							</div><!--/row-->
						</div><!--/container-->
					</div>
				<?php endif; ?>

				<div class="copyright">
					<div class="container">
						<div class="row">
							<div class="col-md-8">
								<p>
									<?php if ( !empty( get_theme_mod('nebula_footer_logo', '') ) ): ?>
										<a class="footerlogo" href="<?php echo home_url('/'); ?>"><img src="<?php echo get_theme_mod('nebula_footer_logo', ''); ?>" /></a>
									<?php endif; ?>
									<?php echo date('Y'); ?> &copy; <a href="<?php echo home_url('/'); ?>" title="Last commit: <?php echo nebula_version('date'); ?>"><strong><?php bloginfo('name'); ?></strong> <?php echo nebula_version('version'); ?></a>, all rights reserved.<br />
									<a href="https://www.google.com/maps/place/<?php echo nebula_full_address(true); ?>" target="_blank"><?php echo nebula_full_address(); ?></a>
								</p>
							</div><!--/col-->
							<?php if ( !get_theme_mod('nebula_hide_footer_search', false) ): ?>
								<div class="col-md-4">
									<form class="nebula-search-iconable search footer-search" method="get" action="<?php echo home_url('/'); ?>">
										<input class="nebula-search open input search" type="search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech />
									</form>
								</div><!--/col-->
							<?php endif; ?>
						</div><!--/row-->
					</div><!--/container-->
				</div>

			</div><!--/footer-section-->

			<?php //Scripts are loaded in functions.php (so they can be registerred and enqueued). ?>
			<?php wp_footer(); ?>
			<?php do_action('nebula_footer'); ?>

		</div><!--/body-wrapper-->
	</body>
</html>