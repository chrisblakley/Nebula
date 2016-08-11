<style>
	#svgexample svg path,
	#svgexample svg polygon {fill: red; transition: all 0.5s;}
		#svgexample svg:hover path,
		#svgexample svg:hover polygon {fill: green;}
</style>

<div class="row">
	<div id="svgexample" class="col-md-12" style="text-align: center;">
		<img class="svg" src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" />
	</div><!--/col-->
</div><!--/row-->