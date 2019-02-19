<section id="navigation-section" class="mp-<?php echo get_theme_mod('menu_position', 'over'); ?>">
	<?php if ( has_nav_menu('utility') ): ?>
		<div id="utilitynavcon">
			<div class="container">
				<div class="row">
					<div class="col">
						<nav id="utility-nav" role="navigation">
		        			<?php wp_nav_menu(array('theme_location' => 'utility')); ?>
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
						<?php $logo = nebula()->logo('header'); ?>
						<?php if ( !empty($logo) ): ?>
							<img class="svg" src="<?php echo $logo; ?>" alt="<?php bloginfo('name'); ?>" importance="high" />
						<?php else: //Otherwise fallback to the Site Title text ?>
							<?php bloginfo('name'); ?>
						<?php endif; ?>
					</a>
				</div><!--/col-->
				<div class="col-lg-8">
					<?php if ( has_nav_menu('primary') ): ?>
						<nav id="primary-nav">
							<?php wp_nav_menu(array('theme_location' => 'primary')); ?>
		        		</nav>
	        		<?php endif; ?>
	        	</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</div>
</section>