window.performance.mark('(Nebula) Inside /modules/location.js');

//Places - Address Autocomplete
//This uses the Google Maps Geocoding API
//The passed selector must be an input element
nebula.addressAutocomplete = function(autocompleteInput, uniqueID = 'unnamed'){
	if ( jQuery(autocompleteInput).length && jQuery(autocompleteInput).is('input') ){ //If the addressAutocomplete ID exists
		if ( typeof google === 'object' && google?.maps ){
			nebula.googleAddressAutocompleteCallback(autocompleteInput, uniqueID);
		} else {
			//Log all instances to be called after the maps JS library is loaded. This prevents the library from being loaded multiple times.
			if ( typeof autocompleteInputs !== 'object' ){
				var autocompleteInputs = {}; //Cannot use let here (scope?)
			}

			autocompleteInputs[uniqueID] = autocompleteInput;

			nebula.debounce(function(){
				nebula.loadJS('https://www.google.com/jsapi?key=' + nebula.site.options.nebula_google_browser_api_key, 'google-maps').then(function(){ //May not need key here, but just to be safe.
					google.load('maps', '3', {
						other_params: 'libraries=places&key=' + nebula.site.options.nebula_google_browser_api_key,
						callback: function(){
							jQuery.each(autocompleteInputs, function(uniqueID, input){
								nebula.googleAddressAutocompleteCallback(input, uniqueID);
							});
						}
					});
				});
			}, 100, 'google maps script load');
		}
	}
};

nebula.googleAddressAutocompleteCallback = function(autocompleteInput, uniqueID = 'unnamed'){
	window[uniqueID] = new google.maps.places.Autocomplete(
		jQuery(autocompleteInput)[0],
		{types: ['geocode']} //Restrict the search to geographical location types
	);

	google.maps.event.addListener(window[uniqueID], 'place_changed', function(){ //When the user selects an address from the dropdown, populate the address fields in the form.
		let place = window[uniqueID].getPlace(); //Get the place details from the window[uniqueID] object.
		let simplePlace = nebula.sanitizeGooglePlaceData(place, uniqueID);

		let thisEvent = {
			event_name: 'autocomplete_address',
			event_category: 'Contact',
			event_action: 'Autocomplete Address',
			event_label: simplePlace.city + ', ' + simplePlace.state.abbr + ' ' + simplePlace.zip.code,
			place: place,
			simple_place: simplePlace
		};

		nebula.dom.document.trigger('nebula_address_selected', [place, simplePlace, jQuery(autocompleteInput)]);
		nebula.dom.document.trigger('nebula_event', thisEvent);
		gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));

		nebula.crm('identify', {
			street_number: simplePlace.street.number,
			street_name: simplePlace.street.name,
			street_full: simplePlace.street.full,
			city: simplePlace.city,
			county: simplePlace.county,
			state: simplePlace.state.name,
			country: simplePlace.country.name,
			zip: simplePlace.zip.code,
			address: simplePlace.street.full + ', ' + simplePlace.city + ', ' + simplePlace.state.abbr + ' ' + simplePlace.zip.code
		});
	});

	jQuery(autocompleteInput).on('focus', function(){
		if ( nebula.site.protocol === 'https' && navigator.geolocation ){
			navigator.geolocation.getCurrentPosition(function(position){ //Bias to the user's geographical location.
				let geolocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				let circle = new google.maps.Circle({
					center: geolocation,
					radius: position.coords.accuracy
				});
				window[uniqueID].setBounds(circle.getBounds());
			});
		}
	}).on('keydown', function(e){
		if ( e.key === 'Enter' && jQuery('.pac-container:visible').length ){ //Prevent form submission when enter key is pressed while the "Places Autocomplete" container is visbile
			return false;
		}
	});

	if ( autocompleteInput === '#address-autocomplete' ){
		nebula.dom.document.on('nebula_address_selected', function(){
			//do any default stuff here.
		});
	}
};

//Organize the Google Place data into an organized (and named) object
//Use uniqueID to name places like "home", "mailing", "billing", etc.
nebula.sanitizeGooglePlaceData = function(place = false, uniqueID = 'unnamed'){
	if ( !place ){
		nebula.help('Place data is required for sanitization.', '/functions/sanitizegoogleplacedata/');
		return false;
	}

	if ( typeof nebula.user.address === 'undefined' ){
		nebula.user.address = {};
	}

	if ( !Array.isArray(nebula.user.address) ){
		nebula.user.address = [];
	}

	nebula.user.address[uniqueID] = {
		street: {
			number: null,
			name: null
		},
		city: null,
		county: null,
		state: {
			name: null,
			abbr: null
		},
		country: {
			name: null,
			abbr: null
		},
		zip: {
			code: null,
			suffix: null
		}
	};

	for ( let component of place.address_components ){
		//Lots of different address types. This function uses only the common ones: https://developers.google.com/maps/documentation/geocoding/#Types
		switch ( component.types[0] ){
			case "street_number":
				nebula.user.address[uniqueID].street.number = component.short_name; //123
				break;
			case "route":
				nebula.user.address[uniqueID].street.name = component.long_name; //Street Name Rd.
				break;
			case "locality":
				nebula.user.address[uniqueID].city = component.long_name; //Liverpool
				break;
			case "administrative_area_level_2":
				nebula.user.address[uniqueID].county = component.long_name; //Onondaga County
				break;
			case "administrative_area_level_1":
				nebula.user.address[uniqueID].state.name = component.long_name; //New York
				nebula.user.address[uniqueID].state.abbr = component.short_name; //NY
				break;
			case "country":
				nebula.user.address[uniqueID].country.name = component.long_name; //United States
				nebula.user.address[uniqueID].country.abbr = component.short_name; //US
				break;
			case "postal_code":
				nebula.user.address[uniqueID].zip.code = component.short_name; //13088
				break;
			case "postal_code_suffix":
				nebula.user.address[uniqueID].zip.suffix = component.short_name; //4725
				break;
			default:
				//console.log('Address component ' + component.types[0] + ' not used.');
		}
	}

	if ( nebula.user.address[uniqueID].street.number && nebula.user.address[uniqueID].street.name ){
		nebula.user.address[uniqueID].street.full = nebula.user.address[uniqueID].street.number + ' ' + nebula.user.address[uniqueID].street.name;
	}

	if ( nebula.user.address[uniqueID].zip.code && nebula.user.address[uniqueID].zip.suffix ){
		nebula.user.address[uniqueID].zip.full = nebula.user.address[uniqueID].zip.code + '-' + nebula.user.address[uniqueID].zip.suffix;
	}

	return nebula.user.address[uniqueID];
};

//Request Geolocation
function requestPosition(){
	if ( google?.maps ){
		nebula.loadJS('https://www.google.com/jsapi?key=' + nebula.site.options.nebula_google_browser_api_key, 'google-maps').then(function(){ //May not need key here, but just to be safe.
			google.load('maps', '3', {
				other_params: 'libraries=placeskey=' + nebula.site.options.nebula_google_browser_api_key,
				callback: function(){
					nebula.getCurrentPosition();
				}
			});
		});
	} else {
		getCurrentPosition();
	}
}

nebula.getCurrentPosition = function(){
	let geolocation = window.navigator?.geolocation;
	if ( geolocation !== null ){
		geolocation.getCurrentPosition(geoSuccessCallback, geoErrorCallback, {enableHighAccuracy: true}); //One-time location poll
		//geoloc.watchPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //Continuous location poll (This will update the nebula.session.geolocation object regularly, but be careful sending events to GA- may result in TONS of events)
	}
};

//Geolocation Success
function geoSuccessCallback(position){
	nebula.session.geolocation = {
		error: false,
		coordinates: { //A value in decimal degrees to an precision of 4 decimal places is precise to 11.132 meters at the equator. A value in decimal degrees to 5 decimal places is precise to 1.1132 meter at the equator.
			latitude: position.coords.latitude,
			longitude: position.coords.longitude
		},
		accuracy: {
			meters: position.coords.accuracy,
			miles: (position.coords.accuracy*0.000621371).toFixed(2),
		},
		altitude: { //Above the mean sea level
			meters: position.coords.altitude,
			miles: (position.coords.altitude*0.000621371).toFixed(2),
			accuracy: position.coords.altitudeAccuracy,
		},
		speed: {
			mps: position.coords.speed,
			kph: (position.coords.speed*3.6).toFixed(2),
			mph: (position.coords.speed*2.23694).toFixed(2),
		},
		heading: position.coords.heading, //Degrees clockwise from North
		address: false
	};

	nebula.session.geolocation.accuracy.color = '#ff1900';
	nebula.session.geolocation.accuracy.description = 'Very Poor (>1500m)';
	if ( nebula.session.geolocation.accuracy.meters < 50 ){
		nebula.session.geolocation.accuracy.color = '#00bb00';
		nebula.session.geolocation.accuracy.description = 'Excellent (<50m)';
	} else if ( nebula.session.geolocation.accuracy.meters > 50 && nebula.session.geolocation.accuracy.meters < 300 ){
		nebula.session.geolocation.accuracy.color = '#a4ed00';
		nebula.session.geolocation.accuracy.description = 'Good (50m - 300m)';
	} else if ( nebula.session.geolocation.accuracy.meters > 300 && nebula.session.geolocation.accuracy.meters < 1500 ){
		nebula.session.geolocation.accuracy.color = '#ffc600';
		nebula.session.geolocation.accuracy.description = 'Poor (300m - 1500m)';
	}

	gtag('set', 'user_properties', {
		geolocation_accuracy: nebula.session.geolocation.accuracy.description
	});

	nebula.addressLookup(position.coords.latitude, position.coords.longitude);

	sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
	nebula.dom.document.trigger('geolocationSuccess', nebula.session.geolocation);
	nebula.dom.body.addClass('geo-latlng-' + nebula.session.geolocation.coordinates.latitude.toFixed(4).replace('.', '_') + '_' + nebula.session.geolocation.coordinates.longitude.toFixed(4).replace('.', '_') + ' geo-acc-' + nebula.session.geolocation.accuracy.meters.toFixed(0).replace('.', ''));
	nebula.session.geolocation.coordinates.anonymized = nebula.session.geolocation.coordinates.latitude.toFixed(2) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(2);

	gtag('set', 'user_properties', {
		geolocation: nebula.session.geolocation.coordinates.anonymized
	});

	gtag('event', 'geolocation', {
		event_category: 'Geolocation',
		event_action: 'Success',
		coordinates: nebula.session.geolocation.coordinates.anonymized,
		accuracy: nebula.session.geolocation.accuracy.meters.toFixed(2) + ' meters'
	});

	nebula.crm('identify', {'geolocation': nebula.session.geolocation.coordinates.latitude.toFixed(4) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(4) + ' (Accuracy: ' + nebula.session.geolocation.accuracy.meters.toFixed(2) + ' meters'});
}

//Geolocation Error
function geoErrorCallback(error){
	let geolocationErrorMessage = '';
	let geoErrorNote = '';

	switch ( error.code ){
		case error.PERMISSION_DENIED:
			geolocationErrorMessage = 'Access to your location is turned off. Change your settings to report location data.';
			geoErrorNote = 'Denied';
			break;
		case error.POSITION_UNAVAILABLE:
			geolocationErrorMessage = "Data from location services is currently unavailable.";
			geoErrorNote = 'Unavailable';
			break;
		case error.TIMEOUT:
			geolocationErrorMessage = "Location could not be determined within a specified timeout period.";
			geoErrorNote = 'Timeout';
			break;
		default:
			geolocationErrorMessage = "An unknown error has occurred.";
			geoErrorNote = 'Error';
			break;
	}

	nebula.session.geolocation = {
		error: {
			code: error.code,
			description: geolocationErrorMessage
		}
	};

	nebula.dom.document.trigger('geolocationError');
	nebula.dom.body.addClass('geo-error');
	gtag('event', 'exception', {
		message: '(JS) Geolocation error: ' + geolocationErrorMessage,
		fatal: false
	});
	nebula.crm('event', 'Geolocation Error');
}


//Rough address Lookup
//If needing to look up an address that isn't the user's geolocation based on lat/long, consider a different function. This one stores user data.
nebula.addressLookup = function(lat, lng){
	let geocoder = new google.maps.Geocoder();
	let latlng = new google.maps.LatLng(lat, lng); //lat, lng
	geocoder.geocode({'latLng': latlng}, function(results, status){
		if ( status === google.maps.GeocoderStatus.OK ){
			if ( results ){
				nebula.session.geolocation.address = {
					number: nebula.extractFromAddress(results[0].address_components, "street_number"),
					street: nebula.extractFromAddress(results[0].address_components, "route"),
					city: nebula.extractFromAddress(results[0].address_components, "locality"),
					town: nebula.extractFromAddress(results[0].address_components, "administrative_area_level_3"),
					county: nebula.extractFromAddress(results[0].address_components, "administrative_area_level_2"),
					state: nebula.extractFromAddress(results[0].address_components, "administrative_area_level_1"),
					country: nebula.extractFromAddress(results[0].address_components, "country"),
					zip: nebula.extractFromAddress(results[0].address_components, "postal_code"),
					formatted: results[0].formatted_address,
					place: {
						id: results[0].place_id,
					},
				};
				nebula.crm('identify', {'address_lookup': results[0].formatted_address});

				sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
				nebula.dom.document.trigger('addressSuccess');
				if ( nebula.session.geolocation.accuracy.meters < 100 ){
					nebula.placeLookup(results[0].place_id);
				}
			}
		}
	});
};

//Extract address components from Google Maps Geocoder
nebula.extractFromAddress = function(components, desiredType){
	for ( let component of components ){
		for ( let thisType of components[i].types ){
			if ( thisType === desiredType ){
				return component.long_name;
			}
		}
	}

	return '';
};

//Lookup place information
nebula.placeLookup = function(placeID){
	if ( google?.maps?.places ){
		let service = new google.maps.places.PlacesService(jQuery('<div></div>').get(0));
		service.getDetails({
			placeId: placeID
		}, function(place, status){
			if ( status === google.maps.places.PlacesServiceStatus.OK ){
				if ( typeof place.name !== 'undefined' ){
					nebula.session.geolocation.address.place = {
						id: placeID,
						name: place.name,
						url: place.url,
						website: place.website,
						phone: place.formatted_phone_number,
						ratings: {
							rating: place.rating,
							total: place.user_ratings_total,
							reviews: ( typeof place.reviews !== 'undefined' )? place.reviews.length : 0,
						},
						utc_offset: place.utc_offset,
					};

					sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
					nebula.dom.document.trigger('placeSuccess');
				}
			}
		});
	}
};