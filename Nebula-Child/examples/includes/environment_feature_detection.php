<style>
	.featureitem {white-space: nowrap;}
	.featureitem-no {color: red;}

	.googlemaptester {width:100%; height: 350px !important; border: 1px solid grey; margin-bottom: 15px;}
</style>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/swfobject/2.2/swfobject.min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=true"></script>

<script>
	jQuery(document).ready(function() {
		jsDetection();

		requestTestPosition(); //NOTE: These geolocation functions are customized for this example page! Use the built-in location functions in main.js for standard detection!

		jQuery('.javascript-enabled').text('Enabled');

		jQuery('.browservardumptrigger').on('click', function(){
			jQuery('.browservardump').toggleClass('hidden');
			return false;
		});
	});

	jQuery(window).on('load', function(){
		detectData();

		if (navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate) {
		    navigator.vibrate([100,200,100,100,75,25,100,200,100,500,100,200,100,500]); //Shave and a haircut
		}

		jQuery.ajax({
			type: "POST",
			url: nebula.site.ajax.url,
			//@TODO "Nebula" 0: Add nebula.site.ajax.nonce here!
			data: {
				action: 'nebula_environment_data',
				data: {
					'environment': jQuery('#fulldata').text(),
					'trigger': 'window',
				},
			},
			success: function(data){
				ga('send', 'event', 'Environment Data', 'AJAX Success');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				ga('send', 'event', 'Environment Data', 'AJAX Error');
			},
			timeout: 60000
		});

	});

	function jsDetection(){
		jQuery('.jsdetection').each(function(){
			jQuery(this).html('<i class="fa fa-spin fa-spinner"></i> Detecting...');
		});
	}

	function detectData(){

		var deviceVibration = ( navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate ? 'Vibration Supported' : 'Vibration Unsupported' );
		jQuery('.devicevibration').html(deviceVibration);

		//Feature Detection
		features = jQuery('html').attr('class').split(' ');
		//Alphabetize features
		uniqueFeatures = features;
		if ( !jQuery('html').hasClass('ie') ) {
			features = features.sort();
			uniqueFeatures = features.filter(function(elem, pos) {
			    return features.indexOf(elem) == pos;
			});
			jQuery('.features-info').html('');
			jQuery.each(uniqueFeatures, function(index, value) {
				if ( value.match('no-') ) {
					jQuery(".features-info").append(" <span class='featureitem featureitem-no'>" + value + '</span>,');
				} else {
					jQuery(".features-info").append(" <span class='featureitem featureitem-yes'>" + value + '</span>,');
				}
			 });
		} else {
			uniqueFeatures = features.sort();
			jQuery('.features-info').html('');
			jQuery.each(uniqueFeatures, function(index, value){
				jQuery('.features-info').append(value + ' ');
			})
			//jQuery('.features-info').html(uniqueFeatures);
		}

		jQuery('.screen-info .resolution').html(window.screen.width + 'px x ' + window.screen.height + 'px'); //Screen Resolution Detection
		jQuery('.screen-info .viewport-size').html(jQuery(window).width() + 'px x ' + jQuery(window).height() + 'px'); //Viewport Detection
		jQuery('.screen-info .pixel-density').html(window.devicePixelRatio); //Pixel Density Detection
		jQuery('.screen-info .color-depth').html(screen.colorDepth + 'bit'); //Color Depth detection

		var cookiesEnabled = ( navigator.cookieEnabled === true ? 'Enabled' : 'Disabled' );
		jQuery('.cookies-enabled').html(cookiesEnabled);

		if ( typeof swfobject !== 'undefined' ) {
			var playerVersion = swfobject.getFlashPlayerVersion();
			if ( playerVersion.major != '0' ) { //Working?
				var majorVersion = playerVersion.major;
				var flashEnabled = playerVersion['major'] + '.' + playerVersion['minor'] + '.' + playerVersion['release'];
				jQuery('.flashversion').html('v' + flashEnabled);
			} else {
				jQuery('.flashversion').html('Not Supported');
			}
		}

	} //End detectData()

	//Get lat/lon
	//NOTE: These geolocation functions are customized for this example page! Use the built-in location functions in main.js for standard detection!
	function requestTestPosition() {
	    var nav = null;
	    if (nav == null) {
	        nav = window.navigator;
	    }
	    var geoloc = nav.geolocation;
	    if (geoloc != null) {
	        geoloc.getCurrentPosition(successTestCallback, errorTestCallback, {enableHighAccuracy: true});
	    }
	}

	function successTestCallback(position) {
		window.lat = position.coords.latitude;
		window.lng = position.coords.longitude;
		window.accuracy = position.coords.accuracy;
		window.altitude = position.coords.altitude;
		window.speed = position.coords.speed;

		jQuery('.coord').html(lat.toFixed(4) + ', ' + lng.toFixed(4));
		if ( ( accuracy <= 25 ) ) {
			zoomLevel = 17;
			accColor = '#00bb00';
		} else if ( accuracy > 25 && accuracy <= 50 ) {
			zoomLevel = 17;
			accColor = '#46d100';
		} else if ( accuracy > 51 && accuracy <= 150 ) {
			zoomLevel = 16;
			accColor = '#a4ed00';
		} else if ( accuracy > 151 && accuracy <= 400 ) {
			zoomLevel = 15;
			accColor = '#f2ee00';
		} else if ( accuracy > 401 && accuracy <= 800 ) {
			zoomLevel = 14;
			accColor = '#ffc600';
		} else if ( accuracy > 801 && accuracy <= 1500 ) {
			zoomLevel = 13;
			accColor = '#ff6f00';
		} else if ( accuracy > 1501 && accuracy <= 3000 ) {
			zoomLevel = 12;
			accColor = '#ff1900';
		} else if ( accuracy > 3001 && accuracy <= 8000 ) {
			zoomLevel = 11;
			accColor = '#ff0000';
		} else if ( accuracy > 8001 && accuracy <= 15000 ) {
			zoomLevel = 10;
			accColor = '#ff0000';
		} else if ( accuracy > 15001 && accuracy <= 30000 ) {
			zoomLevel = 9;
			accColor = '#ff0000';
		} else if ( accuracy > 30001 && accuracy <= 60000 ) {
			zoomLevel = 8;
			accColor = '#ff0000';
		} else if ( accuracy > 60001 && accuracy <= 130000 ) {
			zoomLevel = 7;
			accColor = '#ff0000';
		} else if ( accuracy > 130001 && accuracy <= 250000 ) {
			zoomLevel = 6;
			accColor = '#ff0000';
		} else {
			zoomLevel = 5;
			accColor = '#ff0000';
		}
		jQuery('.locacc').html(accuracy.toFixed(0) + ' meters');
		jQuery('.accind').css('color', accColor);
		codeTestLatLng(lat, lng);
		//initialize_map();
	}

	function errorTestCallback(error) {
	    var strMessage = "";
	    // Check for known errors
	    switch (error.code) {
	        case error.PERMISSION_DENIED:
	            strMessage = 'Access to your location is turned off. Change your settings to report location data.';
	            break;
	        case error.POSITION_UNAVAILABLE:
	            strMessage = "Data from location services is currently unavailable.";
	            break;
	        case error.TIMEOUT:
	            strMessage = "Location could not be determined within a specified timeout period.";
	            break;
	        default:
	            break;
	    }
	    jQuery('.coord').html(strMessage);
	}

	function codeTestLatLng(lat, lng) {
		geocoder = new google.maps.Geocoder();
		latlng = new google.maps.LatLng(lat, lng);
		geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[1]) { //formatted address
					jQuery('.address').html(results[1].formatted_address);
					window.loadedAddress = results[1].formatted_address;
					//find country name
					for (var i=0; i<results[0].address_components.length; i++) {
						for (var b=0;b<results[0].address_components[i].types.length;b++) {
							//there are different types that might hold a city admin_area_lvl_1 usually does in come cases looking for sublocality type will be more appropriate
							if (results[0].address_components[i].types[b] == "administrative_area_level_1") {
								city = results[0].address_components[i]; //this is the object you are looking for
								break;
							}
						}
					}
				}
			}
		});
		var myOptions = {
			zoom: zoomLevel
		}
		map = new google.maps.Map(document.getElementById("test_map_canvas"), myOptions);
		var marker = new google.maps.Marker({
		    //icon:'img/map-icon-marker.png',
		    position: latlng,
		    map: map,
		    animation:google.maps.Animation.DROP
		});
		var circle = new google.maps.Circle({
			strokeColor: accColor,
			strokeOpacity: 0.7,
			strokeWeight: 1,
			fillColor: accColor,
			fillOpacity: 0.15,
			map: map,
			radius: accuracy
		});
		circle.bindTo('center', marker, 'position');


		if ( accuracy < 35 ) {
			smRadius = 35
		} else {
			smRadius = accuracy
		}
		var actualPlace = {
			location: latlng,
			radius: smRadius,
			map: map,
			keyword: "*"
			//rankBy: google.maps.places.RankBy.DISTANCE //Does not allow for radius and therefore returns first result regardless of distance.
			};
		var service = new google.maps.places.PlacesService(map);
		service.nearbySearch(actualPlace, actualTestPlaceList);

		google.maps.event.trigger(map, "resize");
		map.setCenter(latlng);
		map.panTo(latlng);
	}

	function actualTestPlaceList(results, status) { //@TODO "Nebula" 0: Work this function into main.js! Clean it up first!
		cityState = '';
		geocoder = new google.maps.Geocoder();
		geocoder.geocode({'latLng': latlng}, function(results, status) {
			if ( status == google.maps.GeocoderStatus.OK ) {
				if ( results[1] ) { //formatted address
					jQuery('.address').html(results[1].formatted_address);
					cityState = ', ' + results[1].address_components[1].long_name + ', ' + results[1].address_components[3].short_name;
					loadedAddress = results[1].formatted_address;
					//find country name
					for ( var i=0; i<results[0].address_components.length; i++ ) {
						for ( var b=0; b<results[0].address_components[i].types.length; b++ ) {
							//there are different types that might hold a city admin_area_lvl_1 usually does in come cases looking for sublocality type will be more appropriate
							if (results[0].address_components[i].types[b] == "administrative_area_level_1") {
								city = results[0].address_components[i]; //this is the object you are looking for
								break;
							}
						}
					}
				}
			}
		});

		//A value in decimal degrees to an precision of 4 decimal places is precise to 11.132 meters at the equator. A value in decimal degrees to 5 decimal places is precise to 1.1132 meter at the equator.
		setTimeout(function(){ //@TODO "Nebula" 0: Maybe instead of a setTimeout here, this could be a callback function on the geocode?
			if (status == google.maps.places.PlacesServiceStatus.OK) {
				for (var i = 0; i < results.length; i++) {
					//var place = results[i];
				}
			}
			if ( !results[0] ) {
				var actualPlaceName = 'Location is likely a residential neighborhood.';
				var actualCity = '';
			} else {
				var actualPlaceName = results[0].name;
				var actualCity = results[0]; //Find another way to pull city information from the closest result.
			}
			if ( accuracy < 300 ) {
				if ( actualPlaceName.indexOf('Location is') >= 0 ) {
					jQuery('.actualplace').html(actualPlaceName);
					ga('send', 'event', 'Geolocation', window.lat.toFixed(4) + ', ' + window.lng.toFixed(4) + ' (Residential/Non-Commercial' + cityState + ')', 'Accuracy: ' + window.accuracy + ' meters');
				} else {
					jQuery('.actualplace').html('<a href="https://maps.google.com?q=' + encodeURI(actualPlaceName) + '" target="_blank">' + actualPlaceName + '</a>'); //@TODO "Nebula" 0: encodeURI isn't working here.
					ga('send', 'event', 'Geolocation', window.lat.toFixed(4) + ', ' + window.lng.toFixed(4) + ' (' + actualPlaceName + cityState + ')', 'Accuracy: ' + window.accuracy + ' meters'); //@TODO "Nebula" 0: Maybe consider the Actions to be something like: "LAT, LNG (Business Name, City, State)"

				}
			} else {
				jQuery('.actualplace').html('Location accuracy is too poor to determine actual place.').css('font-size', '12px').css('color', '#aaa');
				ga('send', 'event', 'Geolocation', window.lat.toFixed(4) + ', ' + window.lng.toFixed(4) + ' (Poor Accuracy' + cityState + ')', 'Accuracy: ' + window.accuracy + ' meters');
			}
			//console.debug(results[0]);

			jQuery.ajax({
				type: "POST",
				url: nebula.site.ajax.url,
				//@TODO "Nebula" 0: Add nebula.site.ajax.nonce here!
				data: {
					action: 'nebula_environment_data',
					data: {
						'environment': jQuery('#fulldata').text(),
						'trigger': 'geo',
					},
				},
				success: function(data){
					ga('send', 'event', 'Environment Data', 'AJAX Success');
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					ga('send', 'event', 'Environment Data', 'AJAX Error');
				},
				timeout: 60000
			});

		}, 250);

	}
</script>

<div class="row">
	<div id="fulldata" class="col-md-12">

		<h3>User</h3>
		<p>
			<strong>IP Address:</strong> <a href="http://whatismyipaddress.com/ip/<?php echo $_SERVER["REMOTE_ADDR"]; ?>" target="_blank"><?php echo $_SERVER["REMOTE_ADDR"]; ?></a><br />
			<?php if ( is_user_logged_in() ) : ?>
				<?php $current_user = wp_get_current_user(); ?>
				Logged in as <?php echo $current_user->display_name; ?> <em>(<?php echo trim(ucwords($current_user->roles[0])); ?> <?php echo ( is_dev() )? ', Developer' : ''; ?>)</em><br />
			<?php else : ?>
				Not logged in<br />
			<?php endif; ?>
			<span class="facebook-connected-as hidden"></span>
			<!-- @TODO "Nebula" 0: If connected to Facebook -->
		</p>

		<h3>User Agent</h3>
		<p style="font-family: monospace;">
			<?php echo $_SERVER["HTTP_USER_AGENT"]; ?><br />
			<a href="http://udger.com/resources/online-parser" target="_blank" style="font-family: 'Open Sans', sans-serif;">User Agent Parser &raquo;</a>
		</p>

		<h3 class="hidden">Debug</h3>
		<p class="hidden" style="font-family: monospace;">
			Referrer: <?php echo $_SERVER["HTTP_REFERER"]; ?><br />
			Remote Host: <?php echo $_SERVER["REMOTE_HOST"]; ?><br />
			Hostname: <?php echo gethostname(); ?><br />
			HTTP Host: <?php echo $_SERVER["HTTP_HOST"]; ?><br />
		</p>

		<h3>Device</h3>
		<p>
			<?php if ( !nebula_is_desktop() ): ?>
				<span><?php echo ucwords(nebula_get_device('formfactor')); ?>: <?php echo ucwords(nebula_get_device('type')); ?></span><br />
				<span><?php echo nebula_get_device('full'); ?></span>
				<span class="mobilebatt"><br />Battery: </span><span class="thebattery">(Info unavailable)</span>
				<br /><span class="devicevibration"></span>
			<?php else : ?>
				Desktop
			<?php endif; ?>
		</p>

		<h3>Operating System</h3>
		<p>
			<?php echo nebula_get_os('full'); ?><br />
		</p>

		<h3 class="browservardumptrigger">Browser</h3>
		<p>
			<?php echo nebula_get_browser('full'); ?><br />
			<strong>Rendering Engine:</strong> <?php echo nebula_get_browser('engine'); ?><br />
		</p>

		<h3>Features</h3>
		<p class="jsdetection features-info">Enable JavaScript to detect features.</p>


		<h3>Screen</h3>
		<p class="screen-info">
			<strong>Resolution:</strong> <span class="jsdetection resolution">Enable JavaScript to detect screen resolution.</span><br />
			<strong>Viewport Size:</strong> <span class="jsdetection viewport-size">Enable JavaScript to detect viewport dimensions.</span><br />
			<strong>Pixel Density:</strong> <span class="jsdetection pixel-density">Enable JavaScript to detect pixel density.</span><br />
			<strong>Color Depth:</strong> <span class="jsdetection color-depth">Enable JavaScript to detect color depth.</span><br />
		</p>

		<h3>Miscellaneous</h3>
		<p class="miscellaneous-info">
			<strong>JavaScript:</strong> <span class="javascript-enabled">Disabled</span><br />
			<strong>Cookies:</strong> <span class="jsdetection cookies-enabled">Enable JavaScript to detect cookies.</span><br />
			<strong>Flash:</strong> <span class="jsdetection flashversion">Enable JavaScript to detect flash version.</span><br />
		</p>

		<h3>Location</h3>
		<p>
			<strong>Coordinates:</strong> <span class="jsdetection coord">Enable JavaScript to detect coordinates.</span><br />
			<strong>City:</strong> <span class="jsdetection address">Enable JavaScript to detect city.</span><br />
			<strong>Closest Business:</strong> <span class="jsdetection actualplace">Enable JavaScript to detect registerred location.</span><br />
			<strong>Location Accuracy:</strong> <span class="jsdetection locacc">Enable JavaScript to detect accuracy.</span><br />
		</p>
		<div id="test_map_canvas" class="googlemaptester"></div>
	</div><!--/col-->
</div><!--/row-->

<div class="row">
	<div class="col-md-12">
		<?php
			//@TODO "Nebula" 0: Create a form so people can email us their environment settings.
			//However, if anything on this page breaks, then the email form will likely not work.
		?>
	</div><!--/col-->
</div><!--/row-->