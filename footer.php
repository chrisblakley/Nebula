<?php
/**
 * Theme Footer
 */
?>
			<div id="footer-section" role="contentinfo">
				<div class="nebula-color-overlay"></div>

				<?php get_template_part('inc/footer_widgets'); //Footer widget logic. ?>

				<div class="container">
					<div class="row powerfootercon">
						<div class="col">
							<?php if ( get_theme_mod('nebula_footer_logo', false) ): ?>
								<a class="footerlogo" href="<?php echo home_url('/'); ?>"><img src="<?php echo get_theme_mod('nebula_footer_logo', ''); ?>" /></a>
							<?php endif; ?>

							<?php if ( has_nav_menu('footer') ): ?>
								<nav id="powerfooter" role="navigation">
									<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2')); ?>
								</nav>
							<?php endif; ?>
						</div><!--/col-->
					</div><!--/row-->
					<div class="row copyright-con">
						<div class="col">
							<p class="copyright">&copy; <?php echo date('Y'); ?> <a href="<?php echo home_url(); ?>"><strong>Nebula</strong></a> <?php echo nebula()->version('full'); ?>, <em>all rights reserved</em>.</p>

							<form class="nebula-search search footer-search" method="get" action="<?php echo home_url('/'); ?>">
								<label class="sr-only" for="nebula-footer-search">Search</label>
								<input id="nebula-footer-search" class="open input search" type="search" name="s" placeholder="Search" autocomplete="off" role="search" x-webkit-speech />
							</form>
						</div><!--/col-->
					</div><!--/row-->
				</div><!--/container-->

				<?php do_action('nebula_footer'); ?>
			</div>

			<?php wp_footer(); ?>
		</div><!--/body-wrapper-->
	</body>
</html>