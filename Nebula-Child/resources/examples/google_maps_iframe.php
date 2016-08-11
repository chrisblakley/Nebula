<?php //https://developers.google.com/maps/documentation/embed/start ?>

<div class="row">
	<div class="col-md-12">
		<iframe class="googlemap nebulaframe nebulashadow floating"
			width="100%"
			height="250"
			frameborder="0"
			src="https://www.google.com/maps/embed/v1/place
			?key=<?php echo nebula_option('google_browser_api_key'); ?>
			&q=Pinckney+Hugo+Group
			&zoom=14
			&maptype=roadmap">
		</iframe>
	</div><!--/col-->
</div><!--/row-->