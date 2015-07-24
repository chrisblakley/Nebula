<script src="https://maps.googleapis.com/maps/api/js?libraries=geometry"></script><!-- @TODO "Nebula" 0: Load with Google API loader -->
<script>
	jQuery(document).ready(function() {
		requestPosition();

		jQuery(document).on('geolocationSuccess', function(){
			nebula_checkSpecificLocation();
			nebula_containsLocation_info();
		});

		jQuery(document).on('geolocationError', function(){
			console.log('geolocation error in example');
			nebula_containsLocation_info();
		});
	});

	function nebula_checkSpecificLocation(){
	    var latlng = new google.maps.LatLng(nebulaLocation.coordinates.latitude, nebulaLocation.coordinates.longitude);

	    var phgPolygon = new google.maps.Polygon({
	        paths: [
	            new google.maps.LatLng(43.05484871914285, -76.16599742788821),
	            new google.maps.LatLng(43.054119635186765, -76.16602156776935),
	            new google.maps.LatLng(43.05410787569708, -76.16572920698673),
	            new google.maps.LatLng(43.05361201516351, -76.165777486749),
	            new google.maps.LatLng(43.053604175439084, -76.16544489283115),
	            new google.maps.LatLng(43.054370503762705, -76.16540197748691),
	            new google.maps.LatLng(43.054378343389146, -76.1652732314542),
	            new google.maps.LatLng(43.05481148119386, -76.16526250261813)
	        ]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, phgPolygon) ){
	        ga('send', 'event', 'PHG Location', lat + ', ' + lng);
	        ga('set', 'dimension1', 'At Pinckney Hugo Group');
	        nebulaLocation.phg = true;
	    } else {
		    ga('set', 'dimension1', 'Not at Pinckney Hugo Group');
		    nebulaLocation.phg = false;
	    }
	}

	function nebula_containsLocation_info(){
		if ( !nebulaLocation.error ){
			jQuery('#location-results .latlng').html(nebulaLocation.coordinates.latitude + ', ' + nebulaLocation.coordinates.longitude);
			if ( nebulaLocation.phg ){
				jQuery('#location-results .specific-location').html('You <strong style="color: green;">are</strong> at Pinckney Hugo Group.');
			} else {
				jQuery('#location-results .specific-location').html('You are <strong style="color: maroon;">not</strong> at Pinckney Hugo Group.');
			}

			if ( nebulaLocation.accuracy.meters < 50 ){
		        var accText = 'Excellent';
		        ga('set', 'dimension2', 'Excellent (<50m)');
		    } else if ( nebulaLocation.accuracy.meters > 50 && nebulaLocation.accuracy.meters < 300 ){
		        var accText = 'Good';
		        ga('set', 'dimension2', 'Good (50m - 300m)');
		    } else if ( nebulaLocation.accuracy.meters > 300 && nebulaLocation.accuracy.meters < 1500 ){
		        var accText = 'Poor';
		        ga('set', 'dimension2', 'Okay (300m - 1500m)');
		    } else {
		        var accText = 'Terrible';
		        ga('set', 'dimension2', 'Poor (>1500m)');
		    }

		    jQuery('#location-results .accuracy').html('<strong style="color: ' + nebulaLocation.accuracy.color + ';">' + accText + '</strong> location accuracy: ' + nebulaLocation.accuracy.meters + ' meters (' + nebulaLocation.accuracy.miles + ' miles).');

		} else {
			jQuery('#location-results .latlng').html('<strong>Error:</strong> ' + nebulaLocation.error.description);
		}
	}
</script>

<style>
	#location-results {border: 1px solid #999; padding: 10px 15px;}
		#location-results p {margin: 0; padding: 0;}
</style>

<div class="row">
	<div class="sixteen columns">
		<h3>Location Information</h3>
		<div id="location-results">
			<p class="latlng"></p>
			<p class="accuracy"></p>
			<br/>
			<p class="specific-location"></p>
		</div>
	</div><!--/columns-->
</div><!--/row-->