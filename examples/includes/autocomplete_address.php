<script>
	jQuery(document).ready(function(){

		nebulaAddressAutocomplete('#address-street');

		jQuery(document).on('nebula_address_selected', function(){
			jQuery('#address-street').val(addressComponents.street.full).on('blur change', function(){
				jQuery(this).val(addressComponents.street.full).off('blur change'); //Add this to the autocomplete field so it doesn't get overriden on blur.
			});
			jQuery('#address-city').val(addressComponents.city);
			jQuery('#address-state').val(addressComponents.state.abbreviation);
			jQuery('#address-zip').val(addressComponents.zip.code);
			jQuery('#address-country').val(addressComponents.country.name);


			//Showing available address components for this example.
			console.log('Available Nebula Address Components:');
			console.debug(addressComponents);

			//console.log('Raw Google Address Components:');
			//console.debug(place.address_components); //@TODO "Nebula" 0: place is undefined (probably a scope issue). window.place isn't working.
		});

	});
</script>

<div class="row">
	<div class="sixteen columns">
		<?php echo do_shortcode('[contact-form-7 id="1796" title="Address"]'); ?>
	</div><!--/columns-->
</div><!--/row-->