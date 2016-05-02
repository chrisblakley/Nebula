<div class="row">
	<div class="col-md-12">
		<picture alt="This is the alt tag for the picture">
			<source src="<?php echo get_template_directory_uri(); ?>/examples/images/example_320x212.jpg">
			<source media="(min-width: 640px)" src="<?php echo get_template_directory_uri(); ?>/examples/images/example_640x424.jpg">
			<source media="(min-width: 1000px)" src="<?php echo get_template_directory_uri(); ?>/examples/images/example@2x.jpg">
			<img src="<?php echo get_template_directory_uri(); ?>/examples/images/example.jpg" alt="This is the alt tag for the picture" />
		</picture>
	</div><!--/col-->
</div><!--/row-->