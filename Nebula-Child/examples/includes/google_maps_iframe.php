<?php //https://developers.google.com/maps/documentation/embed/start ?>

<div class="row">
	<div class="sixteen columns">
		<iframe class="googlemap nebulaborder"
			width="100%"
			height="250"
			frameborder="0"
			src="https://www.google.com/maps/embed/v1/place
			?key=<?php echo nebula_option('google_browser_api_key'); ?>
			&q=Pinckney+Hugo+Group
			&zoom=14
			&maptype=roadmap">
		</iframe>
		<div class="nebulashadow floating" offset="-6"></div>

		<small>Seen above with a nebulaborder and floating nebulashadow (to examine when browsers begin supporting better data-attributes in CSS)</small>
	</div><!--/columns-->
</div><!--/row-->