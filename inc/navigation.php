<div id="navigation-section" class="mp-<?php echo get_theme_mod('menu_position', 'over'); ?>">
	<?php if ( has_nav_menu('utility') ): ?>
		<div id="utilitynavcon">
			<div class="container">
				<div class="row">
					<div class="col">
						<nav id="utility-nav" role="navigation">
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
				<div class="col-lg-4 logocon">
					<a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name'); ?>">
						<?php if ( get_theme_mod('custom_logo') || get_theme_mod('nebula_hero_single_color_logo') ): //If the Customizer logo exists ?>
							<?php
								$logo = nebula()->get_thumbnail_src(get_theme_mod('custom_logo'));
								if ( get_theme_mod('one_color_logo') ){ //If the one-color logo exists
									if ( (is_front_page() && get_theme_mod('nebula_hero_single_color_logo')) || (!is_front_page() && get_theme_mod('nebula_header_single_color_logo')) ){ //If it is the frontpage and the home one-color logo is requested -OR- if it is a subpage and the header one-color logo is requested
										$logo = get_theme_mod('one_color_logo');
									}
								}
							?>
							<img class="svg" src="<?php echo $logo; ?>" alt="<?php bloginfo('name'); ?>"/>
						<?php elseif ( file_exists(get_stylesheet_directory() . '/assets/img/logo.svg') ): //Use the child theme logo.svg image if it exists ?>
							<img class="svg" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/logo.svg'; ?>" alt="<?php bloginfo('name'); ?>"/>
						<?php else: //Otherwise fallback to the Site Title text ?>
							<?php bloginfo('name'); ?>
						<?php endif; ?>
					</a>
				</div><!--/col-->
				<div class="col-lg-8">
					<?php if ( has_nav_menu('primary') ): ?>
						<nav id="primary-nav">
							<?php wp_nav_menu(array('theme_location' => 'primary', 'depth' => '2')); ?>
		        		</nav>
	        		<?php endif; ?>
	        	</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
</div>
