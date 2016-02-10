<?php
/**
 * Theme Footer
 */
?>
			<hr class="zero" style="margin-top: 30px;"/>

			<div id="footer">
				<?php get_template_part('includes/footer_widgets'); //Footer widget logic. ?>

				<?php if ( has_nav_menu('footer') ): ?>
					<div class="container footerlinks">
						<div class="row powerfootercon">
							<div class="sixteen columns">
								<nav id="powerfooter">
									<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2')); ?>
								</nav>
							</div><!--/columns-->
						</div><!--/row-->
					</div><!--/container-->
				<?php endif; ?>

				<div class="container copyright">
					<div class="row">
						<div class="eleven columns ">
							<p>
								<a class="footerlogo" href="<?php echo home_url(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-32x32.png" /></a>
								<?php echo date('Y'); ?> &copy; <a href="<?php echo home_url(); ?>" title="Last commit: <?php echo nebula_version('date'); ?>"><strong><?php bloginfo('name'); ?></strong> <?php echo nebula_version('version'); ?></a>, all rights reserved.<br />
								<a href="https://www.google.com/maps/place/<?php echo nebula_full_address(1); ?>" target="_blank"><?php echo nebula_full_address(); ?></a>
							</p>
						</div><!--/columns-->
						<div class="four columns push_one">
							<form class="nebula-search-iconable search footer-search" method="get" action="<?php echo home_url('/'); ?>">
								<input class="nebula-search open input search" type="search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech />
							</form>
						</div><!--/columns-->
					</div><!--/row-->
				</div><!--/container-->

			</div><!--/footer-->

			<?php //Scripts are loaded in functions.php (so they can be registerred and enqueued). ?>
			<?php wp_footer(); ?>
			<?php do_action('nebula_footer'); ?>

		</div><!--/fullbodywrapper-->
	</body>
</html>