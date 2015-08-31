<script>
	jQuery(document).on('ready', function(){
		mapInfo = [];
		mapActions();

		jQuery.getScript('https://www.google.com/jsapi', function(){
		    google.load('maps', '3', {
		        callback: function(){
		        	getAllLocations();
		        }
		    });
		}).fail(function(){
		    ga('send', 'event', 'Error', 'JS Error', 'Google Maps script could not be loaded.', {'nonInteraction': 1});
		});
	});

	//Retreive Lat/Lng locations
	function getAllLocations(){
		mapInfo['markers'] = [];
		jQuery('.latlngcon').each(function(i){
			var alat = jQuery(this).find('.lat').text();
			var alng = jQuery(this).find('.lng').text();
			mapInfo['markers'][i] = [alat, alng];
		});
		renderMap(mapInfo);
	}

	//Interactive Functions of the Google Map
	function mapActions(){
		originalTrafficText = jQuery('.maptraffic').text();
		jQuery(document).on('click touch tap', '.maptraffic', function(){
			if ( mapInfo['traffic'] == 1 ){
				mapInfo['traffic'] = 0;
				jQuery('.maptraffic').removeClass('active').addClass('inactive').text(originalTrafficText);
				jQuery('.maptraffic-icon').removeClass('active').addClass('inactive');
			} else {
				mapInfo['traffic'] = 1;
				jQuery('.maptraffic').addClass('active').removeClass('inactive').text('Disable Traffic');
				jQuery('.maptraffic-icon').addClass('active').removeClass('inactive');
			}
			renderMap(mapInfo);
			return false;
		});

		jQuery(document).on('click touch tap', '.mapgeolocation', function(){
			if ( jQuery('.mapgeolocation-icon').hasClass('inactive') ){
				mapInfo['userLocation'] = true;
			} else {
				mapInfo['userLocation'] = false;
			}

			if ( typeof nebulaLocation === 'undefined' || nebulaLocation.coordinates.latitude == 0 || mapInfo['userLocation'] ){
				jQuery('.mapgeolocation-icon').removeClass('inactive fa-location-arrow').addClass('fa-spinner fa-spin');
				jQuery('.mapgeolocation').removeClass('inactive').attr('title', 'Requesting location...').text('Detecting Location...');
				requestPosition();
			} else {
				jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-ban success error').addClass('inactive fa-location-arrow');
				jQuery(this).removeClass('active success failure').text('Detect Location').addClass('inactive').attr('title', 'Detect current location').css('color', '');
				renderMap(mapInfo);
			}
			return false;
		});

		jQuery('.mapgeolocation').hover(function(){
			if ( jQuery(this).hasClass('active') ){
				jQuery('.mapgeolocation-icon').removeClass('fa-location-arrow').addClass('fa-ban');
			}
		}, function(){
			if ( jQuery(this).hasClass('active') ){
				jQuery('.mapgeolocation-icon').removeClass('fa-ban').addClass('fa-location-arrow');
			}
		});

		originalRefreshText = jQuery('.maprefresh').text();
		pleaseWait = 0;
		jQuery(document).on('click touch tap', '.maprefresh', function(){
			if ( !jQuery(this).hasClass('timeout') ){
				pleaseWait = 0;
				renderMap(mapInfo);
				jQuery('.maprefresh').addClass('timeout', function(){
					jQuery('.maprefresh').text('Refreshing...');
					jQuery('.maprefresh-icon').removeClass('inactive').addClass('fa-spin');
				});
			} else {
				pleaseWait++;
				if ( pleaseWait < 10 ){
					jQuery('.maprefresh').text('Please wait...');
				} else {
					jQuery('.maprefresh').text('Hold your horses!');
				}
			}
			return false;
		});

		//Event Listeners

		//Refresh listener
		jQuery(document).on('mapRendered', function(){
			setTimeout(function(){
				jQuery('.maprefresh').addClass('timeout').text('Refreshed!');
				jQuery('.maprefresh-icon').removeClass('fa-refresh fa-spin inactive').addClass('fa-check-circle success');
			}, 500);

			setTimeout(function(){ //Hide the refresh button to prevent spamming it
				jQuery('.maprefresh').removeClass('timeout').text(originalRefreshText);
				jQuery('.maprefresh-icon').removeClass('fa-check-circle success').addClass('fa-refresh inactive');
			}, 10000);
		});

		//Geolocation Success listener
		jQuery(document).on('geolocationSuccess', function(){
			jQuery('.mapgeolocation').text('Location Accuracy: ').append('<span>' + nebulaLocation.accuracy.miles + ' miles <small>(' + nebulaLocation.accuracy.meters.toFixed(2) + ' meters)</small></span>').find('span').css('color', nebulaLocation.accuracy.color);
			setTimeout(function(){
				jQuery('.mapgeolocation').addClass('active').attr('title', '');
				jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-spin inactive').addClass('fa-location-arrow');
			}, 500);
			renderMap(mapInfo);
		});

		//Geolocation Error listener
		jQuery(document).on('geolocationError', function(){
			jQuery('.mapgeolocation').removeClass('success').text(geolocationErrorMessage);
			setTimeout(function(){
				jQuery('.mapgeolocation').attr('title', '');
				jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-spin').addClass('fa-location-arrow error');
			}, 500);
		});
	} //End mapActions()


	//Render Google Map
	function renderMap(mapInfo){
	    if ( typeof google === 'undefined' ){
	    	return false;
	    } else {
	    	var myOptions = {
				zoom: 11,
				scrollwheel: false,
				zoomControl: true,
				scaleControl: true,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
		    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		    var bounds = new google.maps.LatLngBounds();

			if ( typeof mapInfo['traffic'] !== 'undefined' ){
				if ( mapInfo['traffic'] == 1 ) {
					var trafficLayer = new google.maps.TrafficLayer();
					trafficLayer.setMap(map);
				}
			}

			//Hard-Coded Custom Marker
			//List of Google icons: https://sites.google.com/site/gmapsdevelopment/
			//https://mt.google.com/vt/icon?psize=27&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=%E2%80%A2&scale=1
			var phg = new google.maps.LatLng('43.0536608', '-76.1656');
			bounds.extend(phg);
			marker = new google.maps.Marker({
				position: phg,
				icon: 'https://mt.google.com/vt/icon?psize=10&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=PHG&scale=1',
				clickable: false,
				map: map
			});

			//Dynamic Markers (passed from getAllLocations()
			if ( typeof mapInfo['markers'] !== 'undefined' ){
				var marker, i;
			    for (i = 0; i < mapInfo['markers'].length; i++){
			        var pos = new google.maps.LatLng(mapInfo['markers'][i][0], mapInfo['markers'][i][1]);
			        bounds.extend(pos);
			        marker = new google.maps.Marker({
			            position: pos,
			            //icon:'../../wp-content/themes/gearside2014/images/map-icon-marker.png', //@TODO "Nebula" 0: It would be cool if these were specific icons for each location. Pull from frontend w/ var?
			            clickable: false,
			            map: map
			        });
			        if ( typeof Gumby != 'undefined' ){ Gumby.log('Marker created for: ' + mapInfo['markers'][i][0] + ', ' + mapInfo['markers'][i][1]); }
			    }(marker, i);
		    }

			//Detected Location Marker
			//List of Google icons: https://sites.google.com/site/gmapsdevelopment/
			if ( typeof nebulaLocation !== 'undefined' && mapInfo['userLocation'] ){
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
			}

			//Don't zoom in too far on only one marker
			if ( bounds.getNorthEast().equals(bounds.getSouthWest()) ){
				var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat()+0.0005, bounds.getNorthEast().lng()+0.0005);
				var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat()-0.0005, bounds.getNorthEast().lng()-0.0005);
				bounds.extend(extendPoint1);
				bounds.extend(extendPoint2);
			}
			map.fitBounds(bounds);
			google.maps.event.trigger(map, "resize");

			jQuery(document).trigger('mapRendered');
		}
	}
</script>

<div class="row">
	<div class="sixteen columns">
		<div class="container">
			<div class="row">
				<div class="eight columns">
					<ul>
						<li><strong>Example Locations</strong></li>
						<li class="latlngcon"><i class="fa fa-location-arrow" style="color: #fe7569;"></i> <span class="lat">43.109205</span>, <span class="lng">-76.095831</span></li>
						<li class="latlngcon"><i class="fa fa-location-arrow" style="color: #fe7569;"></i> <span class="lat">43.093068</span>, <span class="lng">-76.163809</span></li>
						<li class="latlngcon"><i class="fa fa-location-arrow" style="color: #fe7569;"></i> <span class="lat">43.100150</span>, <span class="lng">-76.207207</span></li>
					</ul>
				</div><!--/columns-->
				<div class="eight columns">
					<ul>
						<li><i class="maptraffic-icon fa fa-car fa-fw inactive"></i> <a class="maptraffic" href="#">Enable Traffic</a></li>
						<li><i class="mapgeolocation-icon fa fa-location-arrow fa-fw inactive"></i> <a class="mapgeolocation" href="#">Detect Location</a></li>
						<li><i class="maprefresh-icon fa fa-refresh fa-fw inactive"></i> <a class="maprefresh" href="#">Refresh Map</a></li>
					</ul>
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->

		<div class="googlemapcon nebulaframe">
			<div id="map_canvas" class="googlemap"></div>
		</div>
		<br />
	</div><!--/columns-->
</div><!--/row-->