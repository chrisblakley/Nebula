<?php
	//In progress
	//@todo "nebula" 0: rename the page to "Feature Detection and Available User Data"
?>

<style>
	.heading {background: #0098d7; color: #fff;}

	.bool {}
	.bool-null {background: red; color: #fff;}
	.bool-true {color: green;}
		.bool-true.bool-reverse {color: red;}
	.bool-false {color: #aaa;}
		.bool-false.bool-reverse {color: green;}

	.htmlclass-no,
	.bodyclass-no {color: red;}

	.googlemapcon {height: 400px;}
</style>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/swfobject/2.2/swfobject.min.js"></script>

<script>
	jQuery(document).on('ready', function(){

		requestPosition();

		onlyJSdetection();
		pullHTMLClasses();
		pullBodyClasses();

		jQuery('#jsenabled').html('Enabled');

	});

	jQuery(window).on('load', function(){
		detectData();
		setTimeout(function(){
			detectData();
		}, 1000);

		setTimeout(function(){
			jQuery.ajax({
				type: "POST",
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'nebula_environment_data',
					data: {
						'environment': jQuery('#detections').text(),
						'trigger': 'window',
						'debug': nebula,
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

			nebulaVibrate([100,200,100,100,75,25,100,200,100,500,100,200,100,500]);
		}, 1250);
	});

	//Retrigger detections at events
	jQuery(document).on('fbConnected geolocationSuccess addressSuccess placeSuccess', function(){
		detectData();
	});

	jQuery(document).on('geolocationSuccess', function(){
		renderMap();
	});

	function onlyJSdetection(){
		jQuery('.onlyjsdetection').each(function(){
			jQuery(this).html('<i class="fa fa-spin fa-spinner"></i> Detecting...');
		});
	}

	function detectData(triggered){
		//HTML Classes
		setTimeout(function(){
			pullHTMLClasses();
		}, 250);

		//Vibration
		js_print_bool(checkVibration(), '#vibration');

		//Load Times
		if ( typeof performance !== 'undefined' ){
			setTimeout(function(){
				var perceivedLoad = performance.timing.loadEventEnd-performance.timing.navigationStart;
				jQuery('#perceivedload').html('<span class="datapoint data-available">' + perceivedLoad + 'ms</span>');

				var actualLoad = performance.timing.loadEventEnd-performance.timing.responseEnd;
				jQuery('#actualload').html('<span class="datapoint data-available">' + actualLoad + 'ms</span>');
			}, 0);
		} else {
			jQuery('#perceivedload').html('<span class="datapoint data-unavailable">Unavailable</span>');
			jQuery('#actualload').html('<span class="datapoint data-unavailable">Unavailable</span>');
		}

		jQuery('#resolution').html(window.screen.width + 'px x ' + window.screen.height + 'px'); //Screen Resolution Detection
		jQuery('#viewportsize').html(jQuery(window).width() + 'px x ' + jQuery(window).height() + 'px'); //Viewport Detection
		jQuery('#pixeldensity').html(window.devicePixelRatio); //Pixel Density Detection
		jQuery('#colordepth').html(screen.colorDepth + 'bit'); //Color Depth detection

		var cookiesEnabled = ( navigator.cookieEnabled === true )? 'Enabled' : 'Disabled';
		jQuery('#cookies').html(cookiesEnabled);


		//Network Connection
		var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || false;
		if ( connection ){
			jQuery('.networkdata').removeClass('hidden');
			jQuery('#connectiontype').html(connection.type); //Get the connection type (unknown, ethernet, 2G, 3G, 4G, wifi, none)
			jQuery('#connectionmetered').html(connection.metered);
			jQuery('#connectionbandwidth').html(connection.bandwidth);
		} else {
			jQuery('#connectiontype, #connectionmetered, #connectionbandwidth').html('Not Supported');
		}


		//Facebook
		if ( has(nebula, 'user.facebook.id') ){
			jQuery('.fbdata').removeClass('hidden');
			jQuery('#fbid').html(nebula.user.facebook.id);
			jQuery('#fburl').html(nebula.user.facebook.url);
			jQuery('#fbname').html(nebula.user.facebook.name.full);
			jQuery('#fbgender').html(nebula.user.facebook.gender);
			jQuery('#fbemail').html(nebula.user.facebook.email);
			jQuery('#fbimage').html(nebula.user.facebook.image.large);
		}

		//Geolocation
		if ( has(nebula, 'session.geolocation.coordinates.latitude') ){
			jQuery('.geodata').removeClass('hidden');
			jQuery('#geocoords').html(nebula.session.geolocation.coordinates.latitude + ', ' + nebula.session.geolocation.coordinates.longitude);
			jQuery('#geoacc').html(nebula.session.geolocation.accuracy.meters + 'meters (' + nebula.session.geolocation.accuracy.miles + ' miles)');

			if ( has(nebula, 'session.geolocation.address.number') ){
				jQuery('.geoaddressdata').removeClass('hidden');
				jQuery('#geostreet').html(nebula.session.geolocation.address.number + ' ' + nebula.session.geolocation.address.street);
				jQuery('#geocity').html(nebula.session.geolocation.address.city);
				jQuery('#geostate').html(nebula.session.geolocation.address.state);
				jQuery('#geozip').html(nebula.session.geolocation.address.zip);
				jQuery('#geotown').html(nebula.session.geolocation.address.town);
				jQuery('#geocounty').html(nebula.session.geolocation.address.county);
				jQuery('#geocountry').html(nebula.session.geolocation.address.country);

				if ( has(nebula, 'session.geolocation.address.place.name') ){
					jQuery('.geoplacedata').removeClass('hidden');
					jQuery('#geoplaceid').html(nebula.session.geolocation.address.place.place_id);
					jQuery('#geoplacename').html(nebula.session.geolocation.address.place);
					jQuery('#geoplacephone').html(nebula.session.geolocation.address.place);
					jQuery('#geoplacewebsite').html(nebula.session.geolocation.address.place);
					jQuery('#geoplaceurl').html(nebula.session.geolocation.address.place);
				} else if ( nebula.session.geolocation.accuracy.meters >= 100 ){
					jQuery('.geoplace').html('<span class="bool-false">Location not accurate enough for place detection.</span>');
				}
			}
		}

	} //END detectData()

	//Render Google Map
	function renderMap(){
    	var myOptions = {
			zoom: 11,
			scrollwheel: false,
			zoomControl: true,
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
	    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	    var bounds = new google.maps.LatLngBounds();

		//Detected Location Marker
		if ( has(nebula, 'session.geolocation.coordinates.latitude') ){
			var detectLoc = new google.maps.LatLng(nebula.session.geolocation.coordinates.latitude, nebula.session.geolocation.coordinates.longitude);
			marker = new google.maps.Marker({
		        position: detectLoc,
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
			bounds.extend(detectLoc);
		}

		if ( nebula.session.geolocation.accuracy.meters > 100 ){
			map.fitBounds(circle.getBounds());
		} else if ( bounds.getNorthEast().equals(bounds.getSouthWest()) ){ //Don't zoom in too far on only one marker
			var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat()+0.0005, bounds.getNorthEast().lng()+0.0005);
			var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat()-0.0005, bounds.getNorthEast().lng()-0.0005);
			bounds.extend(extendPoint1);
			bounds.extend(extendPoint2);

			map.fitBounds(bounds);
		}

		google.maps.event.trigger(map, "resize");
		jQuery(document).trigger('mapRendered');
	}


	function pullHTMLClasses(){
		features = jQuery('html').attr('class').split(' ');
		uniqueFeatures = features;
		if ( !jQuery('html').hasClass('ie') ) {
			features = features.sort();
			uniqueFeatures = features.filter(function(elem, pos) {
			    return features.indexOf(elem) == pos;
			});
			jQuery('#htmlclasses').html('');
			jQuery.each(uniqueFeatures, function(index, value) {
				if ( value.match('no-') ) {
					jQuery('#htmlclasses').append(' <span class="htmlclass htmlclass-no">' + value + '</span>,');
				} else {
					jQuery('#htmlclasses').append(' <span class="htmlclass htmlclass-yes">' + value + '</span>,');
				}
			 });
		} else {
			uniqueFeatures = features.sort();
			jQuery('#htmlclasses').html('');
			jQuery.each(uniqueFeatures, function(index, value){
				jQuery('#htmlclasses').append(value + ' ');
			})
		}
	}

	function pullBodyClasses(){
		features = jQuery('body').attr('class').split(' ');
		uniqueFeatures = features;
		if ( !jQuery('html').hasClass('ie') ) {
			features = features.sort();
			uniqueFeatures = features.filter(function(elem, pos) {
			    return features.indexOf(elem) == pos;
			});
			jQuery('#bodyclasses').html('');
			jQuery.each(uniqueFeatures, function(index, value) {
				if ( value.match('no-') ) {
					jQuery('#bodyclasses').append(' <span class="bodyclass bodyclass-no">' + value + '</span>,');
				} else {
					jQuery('#bodyclasses').append(' <span class="bodyclass bodyclass-yes">' + value + '</span>,');
				}
			 });
		} else {
			uniqueFeatures = features.sort();
			jQuery('#htmlclasses').html('');
			jQuery.each(uniqueFeatures, function(index, value){
				jQuery('#htmlclasses').append(value + ' ');
			})
		}
	}

	function js_print_bool(bool, id, reverse){
		if ( typeof bool !== 'boolean' ){
			jQuery(id).html('<span class="bool bool-null">null</span>');
		}

		var reverseClass = ( reverse )? 'bool-reverse' : '';

		if ( bool ){
			jQuery(id).html('<span class="bool bool-true ' + reverseClass + '">True</span>');
		} else {
			jQuery(id).html('<span class="bool bool-false ' + reverseClass + '">False</span>');
		}
	}

	function js_print_data(data, id){
		if ( data ){
			jQuery(id).html('<span class="datapoint data-available">' + data + '</span>');
		} else {
			jQuery(id).html('<span class="datapoint data-unavailable">Unavailable</span>');
		}
	}
</script>

<?php
	function nebula_print_bool($bool=null, $reverse=false){ //@todo: instead of reverse, maybe pass strings for true and false as parameters for class? or color?
		if ( !is_bool($bool) ){
			echo '<em class="bool bool-null">null</em>';
			return;
		}

		$reverse_class = ( $reverse )? 'bool-reverse' : '';

		if ( $bool ){
			echo '<span class="bool bool-true ' . $reverse_class . '">True</span>';
		} else {
			echo '<span class="bool bool-false ' . $reverse_class . '">False</span>';
		}

		return;
	}

	function nebula_print_data($data=null, $unavailable='Unavailable', $dependent=true){
		if ( $dependent && $data ){
			echo '<span class="datapoint data-available">' . $data . '</span>';
		} else {
			echo '<span class="datapoint data-unavailable">' . $unavailable . '</span>';
		}
	}

	function print_array_r($array, $sep=' '){
		if ( !is_array($array) ){
	        echo $array . $sep;
	        return;
	    }

	    foreach ( $array as $key => $value ){
            if ( !is_int($key) ){
            	print_array_r($key, ' => ');
            }
            print_array_r($value, ', ');
	    }
	}
?>

<div class="row">
	<div class="col-md-12">

		<table id="detections" class="table">
			<tr>
				<td colspan="2" class="heading">Hardware</td>
			</tr>
			<tr>
				<td class="datalabel">IP Address</td>
				<td><?php nebula_print_data($_SERVER['REMOTE_ADDR']); ?></td>
			</tr>
			<tr>
				<td>Device</td>
				<td><?php nebula_print_data(nebula_get_device('full')); ?></td>
			</tr>
			<tr>
				<td>Form Factor</td>
				<td><?php nebula_print_data(nebula_get_device('formfactor')); ?></td>
			</tr>
			<tr>
				<td>Type</td>
				<td><?php nebula_print_data(nebula_get_device('type')); ?></td>
			</tr>
			<tr>
				<td>Screen Resolution</td>
				<td id="resolution" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Viewport Size</td>
				<td id="viewportsize" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Pixel Density</td>
				<td id="pixeldensity" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Color Depth</td>
				<td id="colordepth" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Battery</td>
				<td class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Vibration</td>
				<td id="vibration" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr class="networkdata">
				<td>Connection Type</td>
				<td id="connectiontype" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr class="networkdata">
				<td>Metered Connection</td>
				<td id="connectionmetered" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr class="networkdata">
				<td>Connection Bandwidth</td>
				<td id="connectionbandwidth" class="onlyjsdetection">Unknown</td>
			</tr>



			<tr>
				<td colspan="2" class="heading">Software</td>
			</tr>
			<tr>
				<td>User Agent</td>
				<td><?php nebula_print_data($_SERVER['HTTP_USER_AGENT']); ?></td>
			</tr>
			<tr>
				<td>Operating System</td>
				<td><?php nebula_print_data(nebula_get_os('full')); ?></td>
			</tr>
			<tr>
				<td>Browser</td>
				<td><?php nebula_print_data(nebula_get_browser('full')); ?></td>
			</tr>
			<tr>
				<td>Rendering Engine</td>
				<td><?php nebula_print_data(nebula_get_browser('engine')); ?></td>
			</tr>
			<tr>
				<td>Ad Blocker</td>
				<td id="adblock"><?php nebula_print_bool($nebula['session']['flags']['adblock'], true); ?></td>
			</tr>
			<tr>
				<td>GA Blocker</td>
				<td id="gablock"><?php nebula_print_bool($nebula['session']['flags']['gablock'], true); ?></td>
			</tr>
			<tr>
				<td>HTML Classes</td>
				<td id="htmlclasses" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Body Classes</td>
				<td id="bodyclasses" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Flash</td>
				<td id="flash" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Cookies</td>
				<td id="cookies" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>JavaScript</td>
				<td id="jsenabled" class="onlyjsdetection">Unknown</td>
			</tr>



			<tr>
				<td colspan="2" class="heading">User</td>
			</tr>
			<tr class="wpuserdata">
				<td>Name (WP)</td>
				<td>
					<?php
						if ( is_user_logged_in() ){
							$user_info = get_userdata(get_current_user_id());
							echo $user_info->display_name;
						} else {
							echo 'Not Logged In';
						}
					?>
				</td>
			</tr>
			<tr class="wpuserdata">
				<td>User ID</td>
				<td><?php nebula_print_data($nebula['user']['id'], 'Not Logged In'); ?></td>
			</tr>
			<tr class="wpuserdata">
				<td>Role</td>
				<td><?php nebula_print_data($nebula['user']['role'], 'Not Logged In'); ?></td>
			</tr>
			<tr>
				<td>Bot</td>
				<td><?php nebula_print_bool(nebula_is_bot()); ?></td>
			</tr>
			<tr>
				<td>CID</td>
				<td><?php echo nebula_print_data(ga_parse_cookie()); ?></td>
			</tr>
			<tr>
				<td>Developer</td>
				<td><?php nebula_print_bool(is_dev()); ?></td>
			</tr>
			<tr>
				<td>Client</td>
				<td><?php nebula_print_bool(is_client()); ?></td>
			</tr>
			<tr>
				<td>Debug</td>
				<td><?php nebula_print_bool(is_debug()); ?></td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook ID</td>
				<td id="fbid"><?php nebula_print_data($nebula['user']['facebook']['id']); ?></td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook URL</td>
				<td id="fburl"><?php nebula_print_data($nebula['user']['facebook']['url']); ?></td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook Name</td>
				<td id="fbname"><?php nebula_print_data($nebula['user']['facebook']['name']['full']); ?></td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook Gender</td>
				<td id="fbgender"><?php nebula_print_data($nebula['user']['facebook']['gender']); ?></td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook Email</td>
				<td id="fbemail"><?php nebula_print_data($nebula['user']['facebook']['email']); ?></td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook Location</td>
				<td id="fblocation"><?php nebula_print_data($nebula['user']['facebook']['location']['locale']); ?> (Time Zone: <?php nebula_print_data($nebula['user']['facebook']['location']['timezone']); ?>)</td>
			</tr>
			<tr class="fbdata <?php echo ( empty($nebula['user']['facebook']['id']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Facebook Image</td>
				<td id="fbimage"><?php nebula_print_data($nebula['user']['facebook']['image']['base']); ?></td>
			</tr>
			<tr>
				<td>Conversions</td>
				<td><?php print_array_r($nebula['user']['conversions']); ?></td>
			</tr>



			<tr>
				<td colspan="2" class="heading">Session</td>
			</tr>
			<tr>
				<td>Session ID</td>
				<td><?php nebula_print_data($nebula['session']['id']); ?></td>
			</tr>
			<tr>
				<td>Referrer</td>
				<td><?php nebula_print_data($nebula['session']['referrer']); ?></td>
			</tr>
			<tr>
				<td>History Depth</td>
				<td>
					<?php
						if ( !empty($nebula['session']['history']) ){
							nebula_print_data($nebula['session']['history']);
						} else {
							echo 'Unavailable';
						}
					?>
				</td>
			</tr>
			<tr>
				<td>Session Notes</td>
				<td><?php nebula_print_data($nebula['session']['notes'], '-'); //supplement with js ?></td>
			</tr>
			<tr class="geodata <?php echo ( empty($nebula['session']['geolocation']['coordinates']) && !is_dev() )? 'hidden' : ''; ?>">
				<td class="googlemapcon" colspan="2"><div id="map_canvas" class="googlemap"></div></td>
			</tr>
			<tr class="geodata <?php echo ( empty($nebula['session']['geolocation']['coordinates']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Geolocation</td>
				<td id="geocoords"><?php nebula_print_data($nebula['session']['geolocation']['coordinates']['latitude']); ?>, <?php nebula_print_data($nebula['session']['geolocation']['coordinates']['longitude']); ?></td>
			</tr>
			<tr class="geodata <?php echo ( empty($nebula['session']['geolocation']['coordinates']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Accuracy</td>
				<td id="geoacc"><span style="color: <?php echo $nebula['session']['geolocation']['accuracy']['color']; ?>;"><?php nebula_print_data($nebula['session']['geolocation']['accuracy']['meters']) . ' meters'; ?> (<?php nebula_print_data($nebula['session']['geolocation']['accuracy']['miles']); ?> miles)</span></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Address</td>
				<td id="geostreet"><?php nebula_print_data($nebula['session']['geolocation']['address']['number']); ?> <?php nebula_print_data($nebula['session']['geolocation']['address']['street']); ?></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>City</td>
				<td id="geocity"><?php nebula_print_data($nebula['session']['geolocation']['address']['city']); ?></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Zip Code</td>
				<td id="geozip"><?php nebula_print_data($nebula['session']['geolocation']['address']['zip']); ?></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Town</td>
				<td id="geotown"><?php nebula_print_data($nebula['session']['geolocation']['address']['town']); ?></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>County</td>
				<td id="geocounty"><?php nebula_print_data($nebula['session']['geolocation']['address']['county']); ?></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>State</td>
				<td id="geostate"><?php nebula_print_data($nebula['session']['geolocation']['address']['state']); ?></td>
			</tr>
			<tr class="geoaddressdata <?php echo ( empty($nebula['session']['geolocation']['address']['street']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Country</td>
				<td id="geocountry"><?php nebula_print_data($nebula['session']['geolocation']['address']['country']); ?></td>
			</tr>
			<tr class="geoplacedata <?php echo ( empty($nebula['session']['geolocation']['address']['place']['name']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Place ID</td>
				<td id="geoplaceid" class="geoplace"><?php nebula_print_data($nebula['session']['geolocation']['address']['place']['id']); ?></td>
			</tr>
			<tr class="geoplacedata <?php echo ( empty($nebula['session']['geolocation']['address']['place']['name']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Place Name</td>
				<td id="geoplacename" class="geoplace"><?php nebula_print_data($nebula['session']['geolocation']['address']['place']['name']); ?></td>
			</tr>
			<tr class="geoplacedata <?php echo ( empty($nebula['session']['geolocation']['address']['place']['name']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Place Phone Number</td>
				<td id="geoplacephone" class="geoplace"><?php nebula_print_data($nebula['session']['geolocation']['address']['place']['phone']); ?></td>
			</tr>
			<tr class="geoplacedata <?php echo ( empty($nebula['session']['geolocation']['address']['place']['name']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Place Website</td>
				<td id="geoplacewebsite" class="geoplace"><?php nebula_print_data($nebula['session']['geolocation']['address']['place']['website']); ?></td>
			</tr>
			<tr class="geoplacedata <?php echo ( empty($nebula['session']['geolocation']['address']['place']['name']) && !is_dev() )? 'hidden' : ''; ?>">
				<td>Place Google URL</td>
				<td id="geoplaceurl" class="geoplace"><?php nebula_print_data($nebula['session']['geolocation']['address']['place']['url']); ?></td>
			</tr>
			<tr>
				<td>First Visit</td>
				<td><?php nebula_print_data(date('l, F j, Y @ g:ia', $nebula['user']['sessions']['first']), 'False', $nebula['user']['sessions']['first']); ?></td>
			</tr>
			<tr>
				<td>Previous Visit</td>
				<td><?php nebula_print_data(date('l, F j, Y @ g:ia', $nebula['user']['sessions']['last']), 'False', $nebula['user']['sessions']['last']); ?></td>
			</tr>
			<tr>
				<td>Unique Visit Count</td>
				<td><?php nebula_print_data($nebula['user']['sessions']['count']); ?></td>
			</tr>
			<tr>
				<td>Local Date/Time</td>
				<td class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Body Classes</td>
				<td><?php print_array_r(get_body_class()); ?></td>
			</tr>
			<tr>
				<td>Perceived Load Time</td>
				<td id="perceivedload" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Actual Load Time</td>
				<td id="actualload" class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>Redirects</td>
				<td class="onlyjsdetection">Unknown</td>
			</tr>
			<tr>
				<td>DB Queries</td>
				<td><?php nebula_print_data(get_num_queries()); ?></td>
			</tr>


			<tr>
				<td colspan="2" class="heading">Site</td>
			</tr>
			<tr>
				<td>WordPress Version</td>
				<td>
					<?php
						global $wp_version;
						nebula_print_data($wp_version);
					?>
				</td>
			</tr>
			<tr>
				<td>Nebula Version</td>
				<td><?php nebula_print_data(nebula_version('version')); ?></td>
			</tr>
			<tr>
				<td>Nebula Commit Date</td>
				<td><?php nebula_print_data(date('l, F j, Y', nebula_version('utc'))); ?></td>
			</tr>
			<?php if ( is_dev(1) ): ?>
				<tr>
					<td>Hostname</td>
					<td><?php echo gethostname(); ?></td>
				</tr>
				<?php if ( !empty($_SERVER['REMOTE_HOST']) ): ?>
					<tr>
						<td>Remote Host</td>
						<td><?php echo $_SERVER['REMOTE_HOST']; ?></td>
					</tr>
				<?php endif; ?>
				<tr>
					<td>HTTP Host</td>
					<td><?php echo $_SERVER['HTTP_HOST']; ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<td>Business Open</td>
				<td><?php nebula_print_bool(is_business_open()); ?></td>
			</tr>
			<tr>
				<td>Relative Time</td>
				<td><?php echo implode(', ', nebula_relative_time('description')); ?></td>
			</tr>
			<tr>
				<td>Site Date/Time</td>
				<td><?php echo date('l, F j, Y @ g:ia'); ?></td>
			</tr>
			<tr>
				<td>Weather</td>
				<td><?php echo nebula_weather('temperature'); ?>&deg;F <?php echo nebula_weather('conditions'); ?></td>
			</tr>
			<tr>
				<td>Sunrise</td>
				<td><?php nebula_print_data(date('g:ia', $sunrise)); ?></td>
			</tr>
			<tr>
				<td>Sunset</td>
				<td><?php nebula_print_data(date('g:ia', $sunset)); ?></td>
			</tr>

			<tr>
				<td colspan="2" class="heading">Debug</td>
			</tr>
		</table>

	</div><!--/col-->
</div><!--/row-->