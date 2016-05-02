<script>
	jQuery(document).ready(function() {
		jQuery.getScript('https://www.google.com/jsapi', function(){
		    google.load('maps', '3', {
		        other_params: 'libraries=geometry',
		        callback: function(){
		        	requestPosition();
		        }
		    });
		}).fail(function(){
		    ga('send', 'event', 'Error', 'JS Error', 'Google Maps Geometry script could not be loaded.', {'nonInteraction': 1});
		});

		jQuery(document).on('geolocationSuccess', function(){
			nebula_checkSpecificLocation();
			nebula_containsLocation_info();
			renderMap();
		});

		jQuery(document).on('geolocationError', function(){
			console.log('geolocation error in example');
			nebula_containsLocation_info();
		});
	});

	function nebula_checkSpecificLocation(){
	    var latlng = new google.maps.LatLng(nebula.session.geolocation.coordinates.latitude, nebula.session.geolocation.coordinates.longitude);





				/*
					//How to pull locations from a custom field to create a polygon
					if ( google.maps.geometry.poly.containsLocation(latlng, nysfPolygon) ){
						if ( nysfLocation.accuracy.meters <= 200 ){
							jQuery.getJSON("http://nysfair.org/venues.json", function(data){
								jQuery(data).each(function(i){
									if ( data[i].location.polygon && data[i].location.polygon != '' ){
										var polygonArray = null;
										var polygonArray = JSON.parse(data[i].location.polygon);
										var venuePath = [];
										jQuery(polygonArray).each(function(i){
											venuePath.push(new google.maps.LatLng(polygonArray[i][0], polygonArray[i][1]));
										});
										var thisPolygon = new google.maps.Polygon({
											paths: venuePath
										});

										if ( google.maps.geometry.poly.containsLocation(latlng, thisPolygon) ){
											ga('set', 'dimension1', data[i].name);
											ga('send', 'event', 'Fairgrounds Location', lat + ', ' + lng, data[i].name);
											return true;
									    }
									}
								});
							});
						}
					}
				*/










		//Admin/Upstairs
		var adminUpstairsPolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.05365905348903, -76.16576135158539),
				new google.maps.LatLng(43.05364925384085, -76.16546899080276),
				new google.maps.LatLng(43.05376488958951, -76.16546362638474),
				new google.maps.LatLng(43.05377076936752, -76.16575330495834)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, adminUpstairsPolygon) ){
	        if ( nebula.session.geolocation.altitude ){
		        if ( nebula.session.geolocation.altitude > 121 ){
			        nebula.session.geolocation.phg = 'Upstairs';
		        } else {
			        nebula.session.geolocation.phg = 'Administrative';
		        }
	        } else {
		        nebula.session.geolocation.phg = 'Administrative or Upstairs';
	        }

	        return true;
	    }

		//Interactive
		var interactivePolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.05387660527521, -76.16560578346252),
				new google.maps.LatLng(43.05387170546869, -76.16548106074333),
				new google.maps.LatLng(43.053760969737205, -76.16548374295235),
				new google.maps.LatLng(43.05375900981094, -76.16560846567154)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, interactivePolygon) ){
	        nebula.session.geolocation.phg = 'Interactive';
	        return true;
	    }

		//Courtyard
		var courtyardPolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.054024579247326, -76.16565674543381),
				new google.maps.LatLng(43.05402653916509, -76.16571441292763),
				new google.maps.LatLng(43.05385210623873, -76.16572514176369),
				new google.maps.LatLng(43.05385112627708, -76.16566479206085)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, courtyardPolygon) ){
	        nebula.session.geolocation.phg = 'Courtyard';
	        return true;
	    }

	    //Main Offices
		var mainOfficesPolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.054115715357135, -76.16565003991127),
				new google.maps.LatLng(43.05392070351615, -76.16566881537437),
				new google.maps.LatLng(43.053883465003636, -76.16548910737038),
				new google.maps.LatLng(43.054108855654626, -76.16547033190727)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, mainOfficesPolygon) ){
	        nebula.session.geolocation.phg = 'Main Office Area';
	        return true;
	    }

	    //Conference Room
		var conferenceRoomPolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.054108855654626, -76.16547167301178),
				new google.maps.LatLng(43.054276428166986, -76.16546228528023),
				new google.maps.LatLng(43.054279368031544, -76.16559237241745),
				new google.maps.LatLng(43.05411179552722, -76.16560444235802)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, conferenceRoomPolygon) ){
	        nebula.session.geolocation.phg = 'Conference Room';
	        return true;
	    }

		//PR
		var prPolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.054113755442216, -76.16564601659775),
				new google.maps.LatLng(43.05412257505882, -76.16590216755867),
				new google.maps.LatLng(43.05428720766966, -76.16589814424515),
				new google.maps.LatLng(43.05427838807672, -76.16563931107521)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, prPolygon) ){
	        nebula.session.geolocation.phg = 'PR';
	        return true;
	    }

		//Back Parking Lot
		var backParkingPolygon = new google.maps.Polygon({
	        paths: [
				new google.maps.LatLng(43.05481540097902, -76.16596519947052),
				new google.maps.LatLng(43.05429014753368, -76.16597324609756),
				new google.maps.LatLng(43.054276428166986, -76.16542339324951),
				new google.maps.LatLng(43.05438814292081, -76.1652597784996),
				new google.maps.LatLng(43.05479384215749, -76.16525173187256)
			]
	    });
	    if ( google.maps.geometry.poly.containsLocation(latlng, backParkingPolygon) ){
	        nebula.session.geolocation.phg = 'Back Parking Area';
	        return true;
	    }

		//PHG Overall
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
	        nebula.session.geolocation.phg = true;
	    } else {
		    nebula.session.geolocation.phg = false;
	    }
	}

	function nebula_containsLocation_info(){
		if ( !nebula.session.geolocation.error ){
			jQuery('#location-results .latlng').html(nebula.session.geolocation.coordinates.latitude + ', ' + nebula.session.geolocation.coordinates.longitude);

			if ( nebula.session.geolocation.accuracy.meters < 50 ){
		        var accText = 'Excellent';
		        ga('set', gaCustomDimensions['geoAccuracy'], 'Excellent (<50m)');
		    } else if ( nebula.session.geolocation.accuracy.meters > 50 && nebula.session.geolocation.accuracy.meters < 300 ){
		        var accText = 'Good';
		        ga('set', gaCustomDimensions['geoAccuracy'], 'Good (50m - 300m)');
		    } else if ( nebula.session.geolocation.accuracy.meters > 300 && nebula.session.geolocation.accuracy.meters < 1500 ){
		        var accText = 'Poor';
		        ga('set', gaCustomDimensions['geoAccuracy'], 'Poor (300m - 1500m)');
		    } else {
		        var accText = 'Very Poor';
		        ga('set', gaCustomDimensions['geoAccuracy'], 'Very Poor (>1500m)');
		    }

			if ( nebula.session.geolocation.phg ){
				if ( typeof nebula.session.geolocation.phg === 'string' ){
					ga('set', gaCustomDimensions['geoName'], 'At PHG in ' + nebula.session.geolocation.phg);
					jQuery('#location-results .specific-location').html('You <strong style="color: green;">are</strong> at Pinckney Hugo Group in <strong>' + nebula.session.geolocation.phg + '</strong>.');
				} else {
					ga('set', gaCustomDimensions['geoName'], 'At Pinckney Hugo Group');
					jQuery('#location-results .specific-location').html('You <strong style="color: green;">are</strong> at Pinckney Hugo Group.');
				}
				ga('send', 'event', 'PHG Location', nebula.session.geolocation.coordinates.latitude + ', ' + nebula.session.geolocation.coordinates.longitude);
			} else {
				ga('set', gaCustomDimensions['geoName'], 'Not at Pinckney Hugo Group');
				jQuery('#location-results .specific-location').html('You are <strong style="color: maroon;">not</strong> at Pinckney Hugo Group.');
			}

			nebulaConversion('example_map', 'Example Contains Location');

		    jQuery('#location-results .accuracy').html('<strong style="color: ' + nebula.session.geolocation.accuracy.color + ';">' + accText + '</strong> location accuracy: ' + nebula.session.geolocation.accuracy.meters.toFixed(2) + ' meters (' + nebula.session.geolocation.accuracy.miles + ' miles). Altitude: ' + nebula.session.geolocation.altitude.meters);

		} else {
			jQuery('#location-results .latlng').html('<strong>Error:</strong> ' + nebula.session.geolocation.error.description);
		}
	}



	//Render Google Map
	function renderMap(){
	    if ( typeof google === 'undefined' ){
	    	return false;
	    } else {
	    	var myOptions = {
				zoom: 11,
				//scrollwheel: false,
				zoomControl: true,
				scaleControl: true,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
		    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		    var bounds = new google.maps.LatLngBounds();

			//PHG Polygon
			var phgLoc = new google.maps.LatLng(43.05353287760792, -76.1650257388153);
			var path = [
				new google.maps.LatLng(43.054872237835816, -76.16603493690491),
				new google.maps.LatLng(43.053515978470244, -76.1660885810852),
				new google.maps.LatLng(43.05312399040743, -76.16611003875732),
				new google.maps.LatLng(43.053100471044004, -76.16554141044617),
				new google.maps.LatLng(43.05345326054855, -76.16556286811829),
				new google.maps.LatLng(43.05344542080385, -76.16541266441345),
				new google.maps.LatLng(43.05435874432114, -76.16526246070862),
				new google.maps.LatLng(43.05484871914285, -76.16522490978241)
			];
			var polyline = new google.maps.Polygon({path:path, strokeColor: "#0098d7", strokeOpacity: 1.0, strokeWeight: 2});
			polyline.setMap(map);
			bounds.extend(phgLoc);

			//Detected Location Marker
			if ( nebula.session.geolocation.coordinates.latitude != 0 ) { //Detected location is set
				var detectLoc = new google.maps.LatLng(nebula.session.geolocation.coordinates.latitude, nebula.session.geolocation.coordinates.longitude);
				marker = new google.maps.Marker({
			        position: detectLoc,
			        icon: 'https://mt.google.com/vt/icon?psize=10&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=%E2%80%A2&scale=1',
			        //animation: google.maps.Animation.DROP,
			        clickable: false,
			        map: map
			    });
			    var circle = new google.maps.Circle({
					strokeColor: nebula.session.geolocation.accuracy.color,
					strokeOpacity: 0.7,
					strokeWeight: 1,
					fillColor: nebula.session.geolocation.accuracy.color,
					fillOpacity: 0.15,
					map: map,
					radius: nebula.session.geolocation.accuracy.meters
				});
				circle.bindTo('center', marker, 'position');

				//var detectbounds = new google.maps.LatLngBounds();
				bounds.extend(detectLoc);
				//map.fitBounds(detectbounds); //Use this instead of the one below to center on detected location only (ignoring other markers)
			}
			map.fitBounds(bounds);
			google.maps.event.trigger(map, "resize");

			jQuery(document).trigger('mapRendered');
		}
	}
</script>

<style>
	#location-results {border: 1px solid #999; padding: 10px 15px;}
		#location-results p {margin: 0; padding: 0;}
</style>

<div class="row">
	<div class="col-md-12">
		<h3>Location Information</h3>
		<div id="location-results">
			<p class="latlng"></p>
			<p class="accuracy"></p>
			<br />
			<p class="specific-location"></p>
		</div>
	</div><!--/col-->
</div><!--/row-->
<div class="row">
	<div class="col-md-12">
		<div class="googlemapcon">
			<div id="map_canvas"></div>
		</div>
	</div><!--/col-->
</div><!--/row-->