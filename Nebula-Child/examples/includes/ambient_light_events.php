<style>
	body {-webkit-transition: background-color 1s; -moz-transition: background-color 1s; -o-transition: background-color 1s; transition: background-color 1s;}

	body.luminosity-normal {background-color: #ccc;}

	body.luminosity-dim {background-color: #000;}
		body.luminosity-dim h2,
		body.luminosity-dim h3,
		body.luminosity-dim h4,
		body.luminosity-dim h5,
		body.luminosity-dim h6,
		body.luminosity-dim p,
		body.luminosity-dim span,
		body.luminosity-dim div {color: #fff !important;}

	body.luminosity-washed {background-color: #fff;}
		body.luminosity-washed h2,
		body.luminosity-washed h3,
		body.luminosity-washed h4,
		body.luminosity-washed h5,
		body.luminosity-washed h6,
		body.luminosity-washed p,
		body.luminosity-washed span,
		body.luminosity-washed div {color: #000 !important;}

	/*	Eventually this JS API could be replaced with luminosity media queries. This example would be as follows:

		@media screen and (luminosity: normal) {
			body {background-color: #ccc;}
		}

		@media screen and (luminosity: dim) {
			body {background-color: #000;}
		}

		@media screen and (luminosity: washed) {
			body {background-color: #fff;}
		}

	*/
</style>


<script>
	jQuery(document).ready(function() {

		window.addEventListener('devicelight', function(e) {
			var lux = e.value;
			jQuery('.devicelightsupport').html(lux + ' lux');

			if ( lux < 50 ) {
				jQuery('body').removeClass('luminosity-normal luminosity-washed').addClass('luminosity-dim');
				jQuery('.luminosityprofile').html('Dim');
			}

			if ( lux >= 50 && lux <= 1000 ) {
				jQuery('body').removeClass('luminosity-washed luminosity-dim').addClass('luminosity-normal');
				jQuery('.luminosityprofile').html('Normal');
			}

			if ( lux > 1000 )  {
				jQuery('body').removeClass('luminosity-normal luminosity-dim').addClass('luminosity-washed');
				jQuery('.luminosityprofile').html('Washed');
			}
		});

		window.addEventListener('lightlevel', function(e) {
			var level = e.value;
			jQuery('.lightlevelsupport').html(level);

			if ( level === 'dim' ) {
				jQuery('body').removeClass('luminosity-normal luminosity-washed').addClass('luminosity-dim');
				jQuery('.luminosityprofile').html('Dim');
			}

			if ( level === 'normal' )  {
				jQuery('body').removeClass('luminosity-washed luminosity-dim').addClass('luminosity-normal');
				jQuery('.luminosityprofile').html('Normal');
			}

			if ( level === 'bright' )  {
				jQuery('body').removeClass('luminosity-normal luminosity-dim').addClass('luminosity-washed');
				jQuery('.luminosityprofile').html('Washed');
			}
		});

	});
</script>

<div class="row">
	<div class="col-md-12">

		<h2>Ambient Light</h2>
		<p>
			<strong>Luminosity Profile:</strong> <span class="luminosityprofile">Unsupported</span><br />
			<strong>Device Light: </strong> <span class="devicelightsupport">devicelight is not supported.</span><br />
			<strong>Light Level: </strong> <span class="lightlevelsupport">lightlevel is not supported.</span><br />
		</p>

	</div><!--/col-->
</div><!--/row-->