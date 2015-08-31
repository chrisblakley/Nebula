<style>
	fieldset {border: 1px solid #ccc; padding-bottom: 5px;}
		input#csv-format {margin-left: 25px;}
	textarea {display: block; width: 100%; height: 350px;}
</style>

<script>
	jQuery(document).on('ready', function(){

		jQuery('#convertbtn').on('click touch tap', function(){

			if ( jQuery('#generated-polygon').val().indexOf('Latitude,Longitude') > -1 ){
				var converted = '[' + jQuery('#generated-polygon').val().replace(/(Latitude,Longitude\n)/g, '[').replace(/(\n)/g, '],[') + ']';
				converted = converted.replace(/(\],\[\])/g, ']]');
			} else {
				var converted = jQuery('#generated-polygon').val().replace(/(new google\.maps\.LatLng\()/g, '[').replace(/(\),)/g, '],').replace(/(\)])[;]?/g, ']]').replace(/(\n)/, '');
			}

			jQuery('#nested-array').val(converted);
			return false;
		});

	});
</script>

<div class="row">
	<div class="eight columns">
		<span class="contact-form-heading">CSV or Path Array to convert*</span>
		<textarea id="generated-polygon" placeholder="CSV or path array to convert..."></textarea>
	</div><!--/columns-->
	<div class="eight columns">
		<span class="contact-form-heading">Converted nested array</span>
		<textarea id="nested-array" placeholder="Converted array will go here..."></textarea>
	</div><!--/columns-->
</div><!--/row-->

<br />

<div class="row">
	<div class="sixteen columns" style="text-align: center;">
		<div class="btn primary medium">
			<a id="convertbtn" href="#">Convert to nested array</a>
		</div>
	</div><!--/columns-->
</div><!--/row-->