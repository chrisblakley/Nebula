<style>
	.level-four-media-query {border: 1px solid red; padding: 5px;}
		.level-four-media-query:before {content: 'Script not supported'; display: block; text-align: center; color: red;}
		.level-four-media-query:after {content: 'Luminosity not supported'; display: block; text-align: center; color: red;}
		.level-four-media-query div:before {content: 'Pointer not supported'; display: block; text-align: center; color: red;}
		.level-four-media-query div:after {content: 'Hover not supported'; display: block; text-align: center; color: red;}

	@media (script) {
		.level-four-media-query { border: 1px solid green; }
			.level-four-media-query:before {content: 'Scripts Enabled'; color: green;}
	}

	@media (luminosity: dim) {
		.level-four-media-query { background: white; }
			.level-four-media-query:after {content: 'Dim Luminosity'; color: green;}
	}

	@media (luminosity: washed) {
		.level-four-media-query { background: black; }
			.level-four-media-query:after {content: 'Washed Luminosity'; color: green;}
	}

	@media (luminosity: normal) {
		.level-four-media-query { background: grey; }
			.level-four-media-query:after {content: 'Normal Luminosity'; color: green;}
	}

	@media (pointer: coarse) {
		.level-four-media-query { height: 100px; }
			.level-four-media-query div:before {content: 'Coarse Pointer'; color: green;}
	}

	@media (pointer: fine) {
		.level-four-media-query { height: auto; }
			.level-four-media-query div:before {content: 'Fine Pointer'; color: green;}
	}

	@media (pointer: fine) {
		.level-four-media-query { height: auto; }
			.level-four-media-query div:before {content: 'No Pointer...?'; color: green;}
	}

	@media (hover) { /* ...or is it (hover: 1) */
		.level-four-media-query:hover { background: green; border: 2px solid forestgreen; font-weight: bold; text-decoration: underline; }
			.level-four-media-query div:after {content: 'Hover Available'; color: green;}
	}



	/* Level 4 Selectors */
	.level-four-selector {margin-top: 30px; border: 1px solid blue; padding: 5px;}

	.level-four-selector:after {content: 'Parent Selector not supported'; color: red; display: block; text-align: center;}
		div! .level-four-selector:after {content: 'Parent Selector supported'; color: green;}





</style>


<div class="row">
	<div class="sixteen columns">
		<div class="level-four-media-query">
			<div></div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	<div class="sixteen columns">
		<div class="level-four-selector">
			<div></div>
		</div>
	</div><!--/columns-->
</div><!--/row-->