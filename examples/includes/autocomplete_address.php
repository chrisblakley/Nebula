<script>
	jQuery(document).ready(function(){

		nebulaAddressAutocomplete('#address-street');

		jQuery(document).on('nebula_address_selected', function(){
			jQuery('#address-street').val(nebula.user.address.street.full).trigger('keyup').on('blur change', function(){
				jQuery(this).val(nebula.user.address.street.full).off('keyup blur change').trigger('keyup'); //Add this to the autocomplete field so it doesn't get overriden on blur.
			});
			jQuery('#address-city').val(nebula.user.address.city).focus().blur();
			jQuery('#address-state').val(nebula.user.address.state.abbreviation).focus().blur();
			jQuery('#address-zip').val(nebula.user.address.zip.code).focus().blur();
			jQuery('#address-country').val(nebula.user.address.country.name).focus().blur();


			//Showing available address components for this example.
			console.log('Available Nebula Address Components:');
			console.debug(nebula.user.address);

			//console.log('Raw Google Address Components:');
			//console.debug(place.address_components); //@TODO "Nebula" 0: place is undefined (probably a scope issue). window.place isn't working.

			nebulaConversion('contact', 'Example Autocomplete Address');
		});

	});
</script>

<div class="row">
	<div class="sixteen columns">
		<?php echo do_shortcode('[contact-form-7 id="1796" title="Address"]'); ?>
	</div><!--/columns-->
</div><!--/row-->