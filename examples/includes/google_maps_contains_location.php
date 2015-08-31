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
	    var latlng = new google.maps.LatLng(nebulaLocation.coordinates.latitude, nebulaLocation.coordinates.longitude);





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
											ga('send', 'event', 'Fairgrounds Location', lat + ', ' + lng, data[i].name);
											ga('set', 'dimension1', data[i].name);
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
	        if ( nebulaLocation.altitude ){
		        if ( nebulaLocation.altitude > 121 ){
			        nebulaLocation.phg = 'Upstairs';
		        } else {
			        nebulaLocation.phg = 'Administrative';
		        }
	        } else {
		        nebulaLocation.phg = 'Administrative or Upstairs';
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
	        nebulaLocation.phg = 'Interactive';
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
	        nebulaLocation.phg = 'Courtyard';
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
	        nebulaLocation.phg = 'Main Office Area';
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
	        nebulaLocation.phg = 'Conference Room';
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
	        nebulaLocation.phg = 'PR';
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
	        nebulaLocation.phg = 'Back Parking Area';
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
	        nebulaLocation.phg = true;
	    } else {
		    nebulaLocation.phg = false;
	    }
	}

	function nebula_containsLocation_info(){
		if ( !nebulaLocation.error ){
			jQuery('#location-results .latlng').html(nebulaLocation.coordinates.latitude + ', ' + nebulaLocation.coordinates.longitude);
			if ( nebulaLocation.phg ){
				ga('send', 'event', 'PHG Location', nebulaLocation.coordinates.latitude + ', ' + nebulaLocation.coordinates.longitude);
				if ( typeof nebulaLocation.phg === 'string' ){
					ga('set', 'dimension1', 'At PHG in ' + nebulaLocation.phg);
					jQuery('#location-results .specific-location').html('You <strong style="color: green;">are</strong> at Pinckney Hugo Group in <strong>' + nebulaLocation.phg + '</strong>.');
				} else {
					ga('set', 'dimension1', 'At Pinckney Hugo Group');
					jQuery('#location-results .specific-location').html('You <strong style="color: green;">are</strong> at Pinckney Hugo Group.');
				}
			} else {
				ga('set', 'dimension1', 'Not at Pinckney Hugo Group');
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
		        ga('set', 'dimension2', 'Poor (300m - 1500m)');
		    } else {
		        var accText = 'Very Poor';
		        ga('set', 'dimension2', 'Very Poor (>1500m)');
		    }

		    jQuery('#location-results .accuracy').html('<strong style="color: ' + nebulaLocation.accuracy.color + ';">' + accText + '</strong> location accuracy: ' + nebulaLocation.accuracy.meters.toFixed(2) + ' meters (' + nebulaLocation.accuracy.miles + ' miles). Altitude: ' + nebulaLocation.altitude.meters);

		} else {
			jQuery('#location-results .latlng').html('<strong>Error:</strong> ' + nebulaLocation.error.description);
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
			if ( nebulaLocation.coordinates.latitude != 0 ) { //Detected location is set
				var detectLoc = new google.maps.LatLng(nebulaLocation.coordinates.latitude, nebulaLocation.coordinates.longitude);
				marker = new google.maps.Marker({
			        position: detectLoc,
			        icon: 'https://mt.google.com/vt/icon?psize=10&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=%E2%80%A2&scale=1',
			        //animation: google.maps.Animation.DROP,
			        clickable: false,
			        map: map
			    });
			    var circle = new google.maps.Circle({
					strokeColor: nebulaLocation.accuracy.color,
					strokeOpacity: 0.7,
					strokeWeight: 1,
					fillColor: nebulaLocation.accuracy.color,
					fillOpacity: 0.15,
					map: map,
					radius: nebulaLocation.accuracy.meters
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
	<div class="sixteen columns">
		<h3>Location Information</h3>
		<div id="location-results">
			<p class="latlng"></p>
			<p class="accuracy"></p>
			<br />
			<p class="specific-location"></p>
		</div>
	</div><!--/columns-->
</div><!--/row-->
<div class="row">
	<div class="sixteen columns">
		<div class="googlemapcon">
			<div id="map_canvas"></div>
		</div>
	</div><!--/columns-->
</div><!--/row-->