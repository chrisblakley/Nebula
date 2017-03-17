<?php
/**
 * Theme Footer
 */
?>
			<div id="footer-section">
				<div class="nebula-color-overlay"></div>

				<?php get_template_part('inc/footer_widgets'); //Footer widget logic. ?>

				<div class="container">
					<div class="row powerfootercon">
						<div class="col">
							<?php if ( get_theme_mod('nebula_footer_logo', false) ): ?>
								<a class="footerlogo" href="<?php echo home_url('/'); ?>"><img src="<?php echo get_theme_mod('nebula_footer_logo', ''); ?>" /></a>
							<?php endif; ?>

							<?php if ( has_nav_menu('footer') ): ?>
								<nav id="powerfooter">
									<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2')); ?>
								</nav>
							<?php endif; ?>
						</div><!--/columns-->
					</div><!--/row-->
					<div class="row copyright">
						<div class="col">
							&copy; <?php echo date('Y'); ?> <a href="<?php echo home_url(); ?>"><strong>Nebula</strong></a> <?php echo nebula()->version('full'); ?>, <em>all rights reserved</em>.

							<form class="nebula-search-iconable search footer-search" method="get" action="<?php echo home_url('/'); ?>">
								<input class="nebula-search open input search" type="search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech />
							</form>
						</div><!--/columns-->
					</div><!--/row-->
				</div><!--/container-->
			</div>

			<?php //Scripts are loaded in functions.php (so they can be registerred and enqueued). ?>
			<?php wp_footer(); ?>
			<?php do_action('nebula_footer'); ?>
		</div><!--/body-wrapper-->
	</body>
</html>