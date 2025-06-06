window.performance.mark('(Nebula) Inside utilities.js (module)');

//Safely wait for the window load event, or if it has already occurred run the callback immediately.
//Use this function inside of dynamically imported files in case the window load event happens before the imported file has finished loading
nebula.bufferedWindowLoad = function(callback){
	//If the window load event has already happened, run the callback immediately
	if ( document.readyState === 'complete' ){ //Note: "interactive" = DOM Ready, "complete" = Window Load
		return callback();
	}

	//If the window has not yet loaded, add an event listener to wait for it
	window.addEventListener('load', () => callback());
};

//Check if the user has enabled DNT (if supported in their browser)
//This is in the utilities module so this function can be used without (and to prevent) the need to load the analytics module at all when not necessary
nebula.isDoNotTrack = function(){
	//Use server-side header detection first
	if ( typeof nebula.user?.dnt === 'boolean' ){ //Check if it is defined
		return nebula.user.dnt;
	}

	//Check for Global Privacy Control (GPC) support
	if ( navigator?.globalPrivacyControl === true ){
		return true; //The user does not provide consent to sell or share their data
	}

	//If the noga query string exists (to prevent self-reporting)
	if ( nebula.get('noga') ){
		return true; //Do not track internal visits
	}

	//Check for browser support and user preference of DNT
	if ( navigator?.doNotTrack == '1' || window?.doNotTrack == '1' ){ //Safari still does not have full support and relies on window (not navigator)
		return true; //This user prefers not to be tracked
	}

	return false; //The user is allowing tracking -or- the browser does not support DNT features
};

//Check if this page view is the first in a session
//Note: Always use this function in JS and never read it from the "session" cookie in JS directly because it (inherently) won't get updated for JS
nebula.isLandingPage = function(){
	if ( nebula.isDoNotTrack() ){
		return false; //Not tracking this user
	}

	if ( nebula?.session?.is_landing_page ){
		jQuery('body').addClass('is-landing-page');
		return true;
	}

	if ( jQuery('body').hasClass('is-landing-page') ){ //If this function is called again on this page, detect it this way since the storage method will now think it is false
		return true;
	}

	//This method may not be necessary anymore as the PHP detection exists now
	if ( typeof localStorage !== 'undefined' && localStorage !== null ){ //In some instances localStorage was null for some reason
		let lpTimestamp = localStorage.getItem('landing_page');

		if ( !lpTimestamp || Date.now() >= parseInt(lpTimestamp)+60*60*1000 ){ //If the storage item does not exist, or if the timestamp is over an hour ago
			localStorage.setItem('landing_page', Date.now().toString()); //Set the (new) timestamp
			jQuery('body').addClass('is-landing-page');
			return true;
		}
	}

	return false; //This page view is not the first of the session
};

nebula.timings = [];
nebula.scroll = {
	offset: 0, //Used for global scroll offsets (when not able to modify certain links or to save redundant parameters)
	speed: 500
};

nebula.regex = {
	email: /^(?<user>(?:[^<>()[\]\\.,;:\s@\"]+(?:\.[^<>()[\]\\.,;:\s@\"]+)*)|(?:\".+\"))@(?<hostname>(?:\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(?:(?:[a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i, //From JS Lint: Expected ']' and instead saw '['.
	phone: /^(?:(?<country>\+?1\s*(?:[.-]\s*)?)?(?:(?:\(\s*)?(?<area>[2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:\)\s*)?(?:[.-]\s*)?)?(?<exchange>[2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?(?<line>[0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(?<extension>\d+))?$/, //To allow letters, you'll need to convert them to their corresponding number before matching this RegEx.
	address: /^(?<number>\d{1,6})\s+(?<name>.{2,25})\b(?<suffix>avenue|ave|court|ct|street|st|drive|dr|lane|ln|road|rd|blvd|plaza|parkway|pkwy)[.,]?/i, //Street address
	date: {
		mdy: /^((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))[.\/-]((1|2)\d{3}|\d{2})$/,
		ymd: /^((1|2)\d{3}|\d{2})[.\/-]((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))$/,
	},
	hex: /^#?([a-f0-9]{6}|[a-f0-9]{3})$/,
	ip: /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
	url: /(\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?)/i,
};

nebula.initBootstrapFunctions = async function(){
	if ( typeof bootstrap !== 'undefined' ){
		await nebula.yield();

		//Tooltips
		if ( jQuery('[data-bs-toggle="tooltip"]').length ){
			jQuery('[data-bs-toggle="tooltip"]').tooltip();
		}

		//Popovers
		if ( jQuery('[data-bs-toggle="popover"]').length ){
			jQuery('[data-bs-toggle="popover"]').popover({'trigger': 'hover'});
		}

		nebula.checkBootstrapToggleButtons();
		jQuery('[data-bs-toggle=buttons] input').on('change', function(){
			nebula.checkBootstrapToggleButtons();
		});

		//Carousels - Override this to customize options
		if ( jQuery('.carousel').length ){
			jQuery('.carousel').each(async function(){
				await nebula.yield();

				if ( jQuery(this).hasClass('auto-indicators') ){
					let carouselID = jQuery(this).attr('id');
					let slideCount = jQuery(this).find('.carousel-item').length;

					let i = 0;
					let markup = '<div class="carousel-indicators">';
					while ( i < slideCount ){
						let active = ( i === 0 )? 'class="active"' : '';
						markup += '<button type="button" data-bs-target="#' + carouselID + '" data-bs-slide-to="' + i + '" ' + active + '></button>';
						i++;
					}
					markup += '</div>';
					jQuery(this).prepend(markup);
					jQuery(this).find('.carousel-item').first().addClass('active');

					if ( !jQuery(this).find('.carousel-inner').length ){
						jQuery(this).find('.carousel-item').wrapAll('<div class="carousel-inner">');
					}
				}

				jQuery(this).carousel();
			});
		}

		//Allow Bootstrap modals to use Nebula animation transitions
		//Place the data-animation attribue on the .modal div (which is what e.target is)
		nebula.dom.document.on('show.bs.modal', function(e){
			if ( jQuery(e.target).attr('data-animation-in') || jQuery(e.target).attr('data-animation') || jQuery(e.target).attr('data-animation-out') ){ //If there is any Nebula animation attribute
				let anim = jQuery(e.target).attr('data-animation-in') || jQuery(e.target).attr('data-animation') || '';

				if ( !jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes') ){
					jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes', jQuery('#' + e.target.id + ' .modal-dialog').attr('class')); //Store the original classes in a data-attribute to use later
				}

				if ( anim ){
					let originalClasses = jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes');
					jQuery('#' + e.target.id + ' .modal-dialog').attr('class', originalClasses + ' ' + anim + ' animate'); //Replace classes each time for re-animation.
				}
			}
		});

		nebula.dom.document.on('hide.bs.modal', function(e){
			if ( jQuery(e.target).attr('data-animation-in') || jQuery(e.target).attr('data-animation') || jQuery(e.target).attr('data-animation-out') ){ //If there is any Nebula animation attribute
				let anim = jQuery(e.target).attr('data-animation-out') || '';
				if ( anim ){
					let originalClasses = jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes');
					jQuery('#' + e.target.id + ' .modal-dialog').attr('class', originalClasses + ' ' + anim + ' animate'); //Replace classes each time for re-animation.
				}
			}
		});
	}
};

//Add an "inactive" class to toggle buttons when one is checked to allow for additional styling options
nebula.checkBootstrapToggleButtons = async function(){
	jQuery('[data-bs-toggle=buttons]').each(async function(){
		await nebula.yield();

		if ( jQuery(this).find('input:checked').length ){
			jQuery(this).find('input').each(function(){
				if ( jQuery(this).is(':checked') ){
					jQuery(this).closest('.btn').removeClass('inactive');
				} else {
					jQuery(this).closest('.btn').addClass('inactive');
				}
			});
		}
	});
};

//Try to fix some errors automatically
nebula.errorMitigation = function(){
	//Try to fall back to .png on .svg errors. Else log the broken image.
	let brokenImageCount = 0;
	jQuery('img').on('error', async function(){
		brokenImageCount++; // Increment broken image counter

		if ( brokenImageCount >= 5 ){
			return; //Stop fetching after a certain amount of broken images
		}

		await nebula.yield();

		let thisImage = jQuery(this);
		let imagePath = thisImage.attr('src');
		if ( imagePath.split('.').pop() === 'svg' ){
			let fallbackPNG = imagePath.replace('.svg', '.png');

			fetch(fallbackPNG, {
				method: 'GET',
				priority: 'low',
			}).then(function(response){
				if ( response.ok ){
					thisImage.prop('src', fallbackPNG);
					thisImage.removeClass('svg');
				}
			}).catch(function(error){
				gtag('event', 'Exception', {
					message: '(JS) Broken Image: ' + imagePath,
					fatal: false
				});
				nebula.crm?.('event', 'Broken Image'); //May not be defined if analytics is not active so using optional chaining on the execution of this function
			});
		} else {
			gtag('event', 'Exception', {
				message: '(JS) Broken Image: ' + imagePath,
				fatal: false
			});
			nebula.crm?.('event', 'Broken Image'); //May not be defined if analytics is not active so using optional chaining on the execution of this function
		}
	});
};

//Focus on an element
nebula.focusOnElement = function(element = false){
	if ( !element ){
		nebula.help('nebula.focusOnElement() requires an element as a string or jQuery object.', '/functions/focusonelement/');
		return;
	}

	//Debounce this because several things could call this simultaneously that cannot be reduced (like hashchange + scrollTo function call)
	nebula.debounce(function(){
		if ( typeof element === 'string' ){
			element = jQuery.find(element); //Use find here to prevent arbitrary JS execution
		} else if ( !element.jquery ){ //Check if it is already a jQuery object
			element = jQuery(element);
		}

		//If the element is not focusable itself, add tabindex to make focusable and remove again
		if ( !element.is(':focusable') ){ //Uses custom expression defined at the bottom of this file
			element.attr('tabindex', -1).on('blur focusout', function(){
				jQuery(this).removeAttr('tabindex');
			});
		}

		element.trigger('focus'); //Focus on the element
	}, 500, 'focusing on element', true);
};

//Get query string parameters
nebula.get = function(parameter = false, url = location.search){
	let queryParameters = new URLSearchParams(url);

	if ( parameter ){ //If a specific parameter is requested
		return queryParameters.get(parameter); //Return it (or null if it does not exist)
	}

	//Otherwise we will return all of the query parameters
	let queries = [];
	queryParameters.forEach(function(value, key){ //Do not use jQuery here!
		queries[key] = value;
	});

	return queries;
};

//This removes a preset list of "temporary" query parameters that should not exist past a single page load
nebula.removeTempQueryParameters = function(additionalParameters=[]){
	//If we explicitly want them to stay persistent, then exit the function without removing anything
	if ( nebula.get('persistent') ){
		return null;
	}

	//Merge all of the parameters from various sources
	let mergedParams = ['sass', 'debug', 'clear-transients', 'clear-ai-code-review'].concat(additionalParameters); //Combine the provided params with Nebula default
	let allParams = wp.hooks.applyFilters('nebulaTemporaryQueryParameters', mergedParams); //Allow others to add their own parameters to Nebula's default list
	let uniqueParams = Array.from(new Set(allParams.map(param => param.toLowerCase()))); //De-dupe all of the parameters

	jQuery.each(uniqueParams, function(index, queryParameter){
		//If even one of the parameters exists, remove them all
		if ( nebula.get(queryParameter) ){
			window.history.replaceState({}, document.title, nebula.removeQueryParameter(uniqueParams, window.location.href));
			return false; //Exit the loop if we find even one
		}
	});
};

//Remove an array of parameters from the query string.
nebula.removeQueryParameter = function(keys, url = location.search){
	//Convert single key to an array if it is provided as a string
	if ( typeof keys === 'string' ){
		keys = [keys];
	}

	let urlQuery = url;
	let baseURL = url.split('?')[0]; //Get the base URL (NOT including the "?" character)

	if ( url.indexOf('?') >= 1 ){ //If the location of the "?" character exists and is not the first character
		urlQuery = url.split('?').pop(); //Remove everything before the query string
	}

	let queryParameters = new URLSearchParams(urlQuery);

	jQuery.each(keys, function(index, item){
		queryParameters.delete(item);
	});

	let updatedQuery = decodeURIComponent(queryParameters.toString()); //Convert to string and decode the string

	//Return a string equivalent to the originally provided URL
	if ( url.indexOf('?') >= 1 ){ //If the location of the "?" character exists and is not the first character
		if ( updatedQuery.length > 0 ){ //If the query is not completely removed
			return baseURL + '?' + updatedQuery; //Append it to the original URL
		}

		return baseURL; //Otherwise, return the original URL
	}

	return updatedQuery; //Return just the query string alone
};

//Fetch API simplified wrapper
nebula.fetch = async function(url = false, headers = {}, type = 'json') {
	if ( !url ){
		nebula.help('nebula.fetch() requires a URL to retrieve.', '/functions/fetch/');
		return false;
	}

	if ( typeof headers !== 'object' ){ //If the type is passed as the second parameter
		type = headers;
		headers = {};
	}

	const fetchLabel = nebula.sanitize(url);
	window.performance.mark('(Nebula) Fetch Start ' + fetchLabel);

	await nebula.yield();

	//Create a new promise to handle fetch
	let fetchPromise = new Promise(async function(resolve, reject){
		try {
			let response = await fetch(url, headers);

			if ( !response.ok ){
				throw new Error('Network response was not ok: ' + response.statusText);
			}

			let data;
			if ( type === 'json' ){
				data = await response.json();
			} else {
				data = await response.text();
			}

			window.performance.mark('(Nebula) Fetch End ' + fetchLabel);
			window.performance.measure('(Nebula) Fetch ' + url, '(Nebula) Fetch Start ' + fetchLabel, '(Nebula) Fetch End ' + fetchLabel);

			resolve(data);
		} catch(error){
			reject(error);
		}
	});

	return fetchPromise;
};

//Trigger a reflow on an element.
//This is useful for repeating animations.
nebula.reflow = function(selector){
	let element; //This must be let and not const

	if ( typeof selector === 'string' ){
		element = jQuery(selector);
	} else if ( typeof selector === 'object' ){
		element = selector;
	} else {
		nebula.help('nebula.reflow() requires a selector as a string or jQuery object.', '/functions/reflow/');
		return false;
	}

	element.width(); //Could use element.offsetHeight here without jQuery
};

//Handle repeated animations in a single function.
nebula.animate = async function(selector, newAnimationClasses, oldAnimationClasses){
	await nebula.yield();

	let element;
	if ( typeof selector === 'string' ){
		element = jQuery(selector);
	} else if ( typeof selector === 'object' ){
		element = selector;
	} else {
		nebula.help('nebula.animate() requires a selector as a string or jQuery object.', '/functions/animate/');
		return false;
	}

	newAnimationClasses += ' animate';
	element.removeClass(newAnimationClasses); //Remove classes first so they can be re-added

	if ( oldAnimationClasses ){
		element.removeClass(oldAnimationClasses); //Remove conflicting animation classes
	}

	nebula.reflow(element); //Refresh the element so it can be animated again
	element.addClass(newAnimationClasses).trigger('animating'); //Animate the element (and trigger a DOM event that can be listened to. Remember: this event happens at the beginning of the animation not the end.)
};

//Helpful animation event listeners
nebula.animationTriggers = function(){
	//On document ready
	jQuery('.ready, .animate-dom-ready').each(function(){
		nebula.loadAnimate(jQuery(this));
	});

	//On window load
	nebula.dom.window.on('load', function(){
		jQuery('.load, .animate-window-load').each(function(){
			nebula.loadAnimate(jQuery(this));
		});
	});

	//On Font Ready
	jQuery('.animate-font-load').each(function(){
		let $oThis = jQuery(this);
		let fontName = $oThis.attr('data-font-name') || $oThis.attr('nebula-font-name'); //The font name attribute should be the exact name as it would appear in CSS

		if ( !fontName ){ //If we don't have a font name, just trigger the animation immediately
			nebula.help('animate-font-load requires an attribute for the font name.', '/functions/animations/');
			nebula.loadAnimate($oThis);
		}

		if ( document.fonts.check('1em "' + fontName + '"') ){
			setTimeout(function(){
				nebula.loadAnimate($oThis);
			}, 100);
		} else {
			document.fonts.load('1em "' + fontName + '"').then(function(){
				setTimeout(function(){
					nebula.loadAnimate($oThis);
				}, 100);
			});
		}
	});

	//On click
	nebula.dom.document.on('click', '.click, .animate-click, [data-click], [nebula-click]', function(){
		let animationClass = jQuery(this).attr('data-click') || jQuery(this).attr('nebula-click') || '';
		nebula.animate(jQuery(this), animationClass);
	});
};

nebula.loadAnimate = function($oThis){
	let animationDelay = $oThis.attr('data-delay') || $oThis.attr('nebula-delay');

	if ( typeof animationDelay === 'undefined' || animationDelay === 0 ){
		nebula.animate($oThis, 'load-animate');
	} else {
		setTimeout(function(){
			nebula.animate($oThis, 'load-animate');
		}, animationDelay);
	}
};

//Get local time string with timezone offset
nebula.localTimestamp = function(){ //Does not technically need to be exported anymore as it is only now used here in this file
	var now = new Date();
	var tzo = -now.getTimezoneOffset();
	var dif = ( tzo >= 0 )? '+' : '-';
	var pad = function(num){
		var norm = Math.abs(Math.floor(num));
		return (( norm < 10 )? '0' : '') + norm;
	};

	return Math.round(now/1000) + ' (' + now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()) + '.' + pad(now.getMilliseconds()) + ' UTC' + dif + pad(tzo/60) + ':' + pad(tzo%60) + ')';
};

//Generate a unique ID that always begins with a letter to be safe in CSS selectors
nebula.uniqueId = function(prefix='nuid', random=false){
	const seconds = Date.now() * 1000 + Math.random() * 1000; //Convert the current time into seconds
	const id = seconds.toString(16).replace(/\./g, '').padEnd(14, '0'); //Convert the timestamp into a base 16 string, remove decimal symbol, and set a minimum length (and always begin with a letter)

	if ( random ){
		return prefix + id + '.' + Math.trunc(Math.random()*100000000); //Append a random number to the end if randomness is requested
	}

	return prefix + id;
};

//Allows something to be called once per pageload.
//Call without self-executing parenthesis in the parameter! Ex: nebula.once(customFunction, 'test example');
//To add parameters, use an array as the 2nd parameter. Ex: nebula.once(customFunction, ['parameter1', 'parameter2'], 'test example');
//Can be used for boolean. Ex: nebula.once('boolean test');
nebula.once = function(fn, args, unique){
	nebula.onces = nebula.onces || {};

	if ( typeof args === 'string' ){ //If no parameters, treat it as the unique string
		unique = args;
		args = [];
	}

	//Reset all
	if ( fn === 'clear' || fn === 'reset' ){
		nebula.onces = {};
		return true;
	}

	//Remove a single entry
	if ( fn === 'remove' ){
		delete nebula.onces[unique];
		return true;
	}

	// If the first parameter fn is a function, execute it only once
	if ( typeof fn === 'function' ){
		if ( !nebula.onces[unique] ){
			nebula.onces[unique] = true;
			return fn.apply(this, args);
		}

		return false; //Do nothing if already executed
	}

	//If the first parameter fn is not a function, assume it's the 'unique' identifier and return boolean
	if ( !nebula.onces[fn] ){
		nebula.onces[fn] = true;
		return true;
	}

	return false;
};

//Waits for events to finish before triggering
//Passing immediate triggers the function on the leading edge (instead of the trailing edge).
nebula.debounce = async function(callback, wait = 1000, uniqueID = 'No Unique ID', immediate = false){
	await nebula.yield();

	if ( typeof callback !== 'function' ){
		nebula.help('nebula.debounce() requires a callback function.', '/functions/debounce/');
		return false;
	}

	nebula.debounceTimers = nebula.debounceTimers || {};

	const context = this;
	const args = arguments;

	const later = () => { //Arrow function allows for proper context scoping without needing additional variables
		nebula.debounceTimers[uniqueID] = null;
		if ( !immediate ){
			callback.apply(context, args);
		}
	};

	const callNow = immediate && !nebula.debounceTimers[uniqueID];

	clearTimeout(nebula.debounceTimers[uniqueID]); //Clear the timeout on every event. Once events stop the timeout is allowed to complete.
	nebula.debounceTimers[uniqueID] = setTimeout(later, wait);

	if ( callNow ){
		callback.apply(context, args);
	}
};

//Limit functionality to only run once per specified time period
nebula.throttle = function(callback, cooldown = 1000, uniqueID = 'No Unique ID'){
	if ( !callback ){
		nebula.help('nebula.throttle() requires a callback function.', '/functions/throttle/');
		return false;
	}

	nebula.throttleTimers = nebula.throttleTimers || {};

	const later = () => {
		if ( !nebula.throttleTimers[uniqueID] ){
			window.requestAnimationFrame(() => {
				callback.apply(this, arguments); //Execute the callback function

				nebula.throttleTimers[uniqueID] = true; //Prevent future invocations

				//After the cooldown period, allow future invocations
				setTimeout(() => {
					nebula.throttleTimers[uniqueID] = false;
				}, cooldown);
			});
		}
	};

	return later();
};

//Cache "expensive" functions by storing the result (similar to WordPress Transients)
//Consider enhancing in the future to allow the cache to work beyond a single page view- perhaps add another parameter for that?
nebula.memoize = function(action, handle = '', value = false){
	nebula.memoizeCache = nebula.memoizeCache || {};

	if ( action.toLowerCase() === 'set' ){
		nebula.memoizeCache[handle] = value;
		return value; //Returning the set value allows for memoize to be set inline with the calculated value if desired
	}

	if ( action.toLowerCase() === 'get' ){
		if ( handle in nebula.memoizeCache ){
			return nebula.memoizeCache[handle];
		}
	}

	return false;
};

//Cookie Management
nebula.createCookie = function(name, value, days = 3650){ //Reduce the default days in 2027 to lower than 10 years (and each year thereafter)
	let date = new Date();
	date.setTime(date.getTime()+(days*24*60*60*1000));
	let expires = '; expires=' + date.toUTCString(); //Note: Do not let this cookie expire past 2038 or it instantly expires. http://en.wikipedia.org/wiki/Year_2038_problem
	document.cookie = name + '=' + value + expires + '; path=/;SameSite=Lax;';
};

nebula.readCookie = function(name){
	let nameEQ = name + '=';
	let cookies = document.cookie.split(';');

	for ( let cookie of cookies ){
		while ( cookie.charAt(0) === ' ' ){
			cookie = cookie.substring(1, cookie.length);
		}

		if ( cookie.indexOf(nameEQ) === 0 ){
			return cookie.substring(nameEQ.length, cookie.length);
		}
	}

	return null;
};

nebula.eraseCookie = function(name){
	nebula.createCookie(name, '', -1);
};

//Convert a value into human-readable file size format
nebula.formatBytes = function(bytes){
	if ( bytes === 0 ) return 0;

	let sizes = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
	let i = Math.floor(Math.log(bytes)/Math.log(1024));
	let size = bytes/Math.pow(1024, i);

	return size.toFixed(1)+sizes[i];
};

//Time specific events. Unique ID is required. Returns time in milliseconds.
//Data can be accessed outside of this function via nebula.timings array.
nebula.timer = function(uniqueID, action, name){
	if ( !window.performance ){ //Safari 11+
		return false;
	}

	if ( typeof nebula.timings === 'undefined' ){
		nebula.timings = [];
	}

	//uniqueID is required
	if ( !uniqueID || uniqueID === 'start' || uniqueID === 'lap' || uniqueID === 'end' ){
		nebula.help('nebula.timer() requires a uniqueID.', '/functions/timer/');
		return false;
	}

	if ( !action ){
		if ( typeof nebula.timings[uniqueID] === 'undefined' ){
			action = 'start';
		} else {
			action = 'lap';
		}
	}

	//Can not lap or end a timing that has not started.
	if ( action !== 'start' && typeof nebula.timings[uniqueID] === 'undefined' ){
		nebula.help('nebula.timer() cannot lap or end a timing that has not started.', '/functions/timer/');
		return false;
	}

	//Can not modify a timer once it has ended.
	if ( typeof nebula.timings[uniqueID] !== 'undefined' && nebula.timings[uniqueID].total > 0 ){
		return nebula.timings[uniqueID].total;
	}

	//Update the timing data!
	let currentTime = performance.now();

	if ( action === 'start' && typeof nebula.timings[uniqueID] === 'undefined' ){
		nebula.timings[uniqueID] = {
			started: currentTime,
			cumulative: 0,
			total: 0,
			lap: [],
			laps: 0
		};

		let thisLap = {
			name: false,
			started: currentTime,
			stopped: 0,
			duration: 0,
			progress: 0,
		};
		nebula.timings[uniqueID].lap.push(thisLap);

		if ( typeof name !== 'undefined' ){
			nebula.timings[uniqueID].lap[0].name = name;
		}

		//Add the time to User Timing API (if supported)
		if ( typeof performance.measure !== 'undefined' ){
			performance.mark(uniqueID + ' [Start]');
		}
	} else {
		let lapNumber = nebula.timings[uniqueID].lap.length;

		//Finalize the times for the previous lap
		nebula.timings[uniqueID].lap[lapNumber-1].stopped = currentTime;
		nebula.timings[uniqueID].lap[lapNumber-1].duration = currentTime-nebula.timings[uniqueID].lap[lapNumber-1].started;
		nebula.timings[uniqueID].lap[lapNumber-1].progress = currentTime-nebula.timings[uniqueID].started;
		nebula.timings[uniqueID].cumulative = currentTime-nebula.timings[uniqueID].started;

		//An "out" lap means the timing for this lap may not be associated directly with the action (Usually resetting for the next actual timed lap).
		if ( action === 'start' ){
			nebula.timings[uniqueID].lap[lapNumber-1].out = true; //If another 'start' was sent, then the previous lap was an out lap
		} else {
			nebula.timings[uniqueID].lap[lapNumber-1].out = false;
		}

		//Prepare the current lap
		if ( action !== 'end' ){
			nebula.timings[uniqueID].laps++;
			if ( lapNumber > 0 ){
				nebula.timings[uniqueID].lap[lapNumber] = {};
				nebula.timings[uniqueID].lap[lapNumber].started = nebula.timings[uniqueID].lap[lapNumber-1].stopped;
			}

			if ( typeof name !== 'undefined' ){
				nebula.timings[uniqueID].lap[lapNumber].name = name;
			}

			//Add the time to User Timing API (if supported)
			if ( typeof performance.measure !== 'undefined' ){
				let lapID = name || lapNumber;
				performance.mark(uniqueID + ' [Lap ' + lapID + ']');
			}
		}

		//Return individual lap times unless 'end' is passed- then return total duration. Note: 'end' can not be updated more than once per uniqueID! Subsequent calls will return the total duration from first call.
		if ( action === 'end' ){
			//Add the time to User Timing API (if supported)
			if ( typeof performance.measure !== 'undefined' ){
				performance.mark(uniqueID + ' [End]');

				if ( performance.getEntriesByName(uniqueID + ' [Start]', 'mark') ){ //Make sure the start mark exists
					performance.measure(uniqueID, uniqueID + ' [Start]', uniqueID + ' [End]');
				}
			}

			nebula.timings[uniqueID].stopped = currentTime;
			nebula.timings[uniqueID].total = currentTime-nebula.timings[uniqueID].started;
			//@todo "Nebula" 0: Add all hot laps together (any non-"out" laps)
			return nebula.timings[uniqueID].total;
		} else if ( !nebula.timings[uniqueID].lap[lapNumber-1].out ){
			return nebula.timings[uniqueID].lap[lapNumber-1].duration;
		}
	}
};

//Convert milliseconds into separate hours, minutes, and seconds string (Ex: "3h 14m 35.2s").
nebula.millisecondsToString = function(ms){
	let milliseconds = parseInt((ms%1000)/100);
	let seconds = parseInt((ms/1000)%60);
	let minutes = parseInt((ms/(1000*60))%60);
	let hours = parseInt((ms/(1000*60*60))%24);
	let timeString = '';

	if ( hours > 0 ){
		timeString += hours + 'h ';
	}

	if ( minutes > 0 ){
		timeString += minutes + 'm ';
	}

	if ( seconds > 0 || milliseconds > 0 ){
		timeString += seconds;

		if ( milliseconds > 0 ){
			timeString += '.' + milliseconds;
		}

		timeString += 's';
	}

	return timeString;
};

//Convert time to relative
//For cross-browser support, timestamp must be passed as a string (not a Date object) in the format: Fri Mar 27 21:40:02 +0000 2016
//Consider using RelativeTimeFormat native JavaScript functionality
nebula.timeAgo = function(timestamp, raw){ //http://af-design.com/blog/2009/02/10/twitter-like-timestamps/
	if ( !timestamp instanceof Date ){
		nebula.help('Pass date as string in the format: Fri Mar 27 21:40:02 +0000 2016 (Your format: ' + timestamp + ')', '/functions/timeAgo/');
	}

	let postDate = new Date(timestamp);
	let currentTime = new Date();
	let diff = Math.floor((currentTime-postDate)/1000);

	if ( raw ){
		return diff;
	}

	if ( diff <= 1 ){ return 'just now'; }
	if ( diff < 20 ){ return diff + ' seconds ago'; }
	if ( diff < 60 ){ return 'less than a minute ago'; }
	if ( diff <= 90 ){ return '1 minute ago'; }
	if ( diff <= 3540 ){ return Math.round(diff/60) + ' minutes ago'; }
	if ( diff <= 5400 ){ return '1 hour ago'; }
	if ( diff <= 86_400 ){ return Math.round(diff/3600) + ' hours ago'; }
	if ( diff <= 129_600 ){ return '1 day ago'; }
	if ( diff < 604_800 ){ return Math.round(diff/86_400) + ' days ago'; }
	if ( diff <= 777_600 ){ return '1 week ago'; }

	return 'on ' + timestamp;
};

//Convert DOM elements into a tree string
nebula.domTreeToString = function($element){
	try {
		//If the element is a selector, convert to a jQuery object
		if ( typeof $element === 'string' ){
			$element = jQuery($element);
		}

		//If the element is a native JS object, convert to jQuery
		if ( $element.nodeType ){
			$element = jQuery($element);
		} else if ( $element[0]?.nodeType ){
			$element = jQuery($element[0]);
		}

		//Map the parent elements into an array and concatenate together
		let selector = $element.parents().map(function(){
			let parentTag = this.tagName.toLowerCase();

			//Append the ID if a parent element has one
			let parentID = jQuery(this).attr('id');
			if ( parentID ){
				parentTag += '#' + parentID;
			}

			return parentTag;
		}).get().reverse().concat([this.nodeName]).join(' ');

		selector += $element[0]?.tagName.toLowerCase(); //changed from .get(0)

		//Append the ID to the last element
		let id = $element.attr('id');
		if ( id ){
			selector += '#' + id;
		}

		//Add the classnames to the last element
		let classNames = $element.attr('class');
		if ( classNames ){
			selector += '.' + classNames.trim().replaceAll(/\s/gi, '.');
		}

		return selector;
	} catch {
		return '(Unknown)';
	}
};

nebula.vibrate = function(pattern){
	if ( typeof pattern !== 'object' ){
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	}

	if ( navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate ){
		navigator.vibrate(pattern);
	}

	return false;
};

//Sanitize text
//This function simply ensures that text only contains actual text characters
nebula.sanitize = function(text){
	return document.createElement('div').appendChild(document.createTextNode(text)).parentNode.innerHTML; //Raw JS is more efficient than jQuery for this
};

//Strip out special characters to make a safe name that can be used with HTML classes
nebula.sanitizeClassName = function(text){
	let cleanText = nebula.sanitize(text).trim(); //Sanitize the text first

	if ( /['"`]/.test(cleanText) ){ //This is an attempt to speed this up to avoid the RegEx happening when it does not need to
		cleanText = cleanText.replace(/\"|\'|\`/ig, ""); //Remove quotes and apostrophes
	}

	cleanText = cleanText.toLowerCase().replaceAll(/(\(.*\))|\W/ig, ''); //Any non-word characters \W or anything inside of parenthesis (including the parenthesis) removed

	return cleanText;
};

//Mask the email with asterisks
nebula.anonymizeEmail = function(emailAddress){
	let anonymizedEmail = '';

	if ( emailAddress.includes('@') && emailAddress.includes('.') ){ //Very simple validation. If a valid email address is not provided, no anonymization will happen!
		let emailDomain = emailAddress.split('@')[1]; //Get everything after the @

		anonymizedEmail = emailAddress.charAt(0); //Start by preserving the first character
		let emailCharacterArray = Array.from(emailAddress.split('@')[0]).slice(1); //Get an array of chars before @ and remove the first index
		jQuery.each(emailCharacterArray, function(character, index){
			if ( index === emailCharacterArray.length-1 ){ //If the current index is the last item (character)
				anonymizedEmail += character; //Use the last letter as-is
			} else {
				anonymizedEmail += '*'; //Add an asterisk for each character in the array
			}
		});
		anonymizedEmail += '@' + emailDomain; //Add the domain
	}

	return anonymizedEmail;
};

//Check if a string is alphanumeric
nebula.isAlphanumeric = function(string = '', allowWords = true){
	if ( !allowWords && string.length > 1 ){ //Ignore meta keys whose "character" is a word (not a letter)
		return false;
	}

	const alphanumericRegex = new RegExp('^[a-zA-Z0-9]+$');
	if ( string && alphanumericRegex.test(string) ){
		return true;
	}

	return false;
};

//Return a singular or plural label string based on the value
nebula.singularPlural = function(value, singular, plural=''){
	if ( value == 1 ){
		return singular;
	}

	if ( !plural ){
		plural = singular + 's'; //Append an "s" to the singular label to simplify calling the function most of the time
	}

	return plural;
};

//Calculate the contrast ratio between two hex colors
nebula.getContrast = function(hex1, hex2){
	let [r1, g1, b1] = nebula.hexToLrgb(hex1);
	let [r2, g2, b2] = nebula.hexToLrgb(hex2);
	let L1 = 0.2126*r1 + 0.7152*g1 + 0.0722*b1;
	let L2 = 0.2126*r2 + 0.7152*g2 + 0.0722*b2;
	let ratio = (L1 > L2)? (L1 + 0.05) / (L2 + 0.05) : (L2 + 0.05) / (L1 + 0.05);
	return ratio;
};

nebula.hexToLrgb = function(hex){
	hex = hex.replace('#', '');
	let bigint = parseInt(hex, 16);
	let r = ((bigint >> 16) & 255) / 255;
	let g = ((bigint >> 8) & 255) / 255;
	let b = (bigint & 255) / 255;

	let channel = c => (c <= 0.03928)? c/12.92 : Math.pow((c+0.055)/1.055, 2.4);
	return [channel(r), channel(g), channel(b)];
};

//Adjust hex color brightness by percent (-100 to +100)
nebula.adjustBrightness = function(hex, percent){
	hex = hex.replace('#', '');
	let r = parseInt(hex.substring(0, 2), 16);
	let g = parseInt(hex.substring(2, 4), 16);
	let b = parseInt(hex.substring(4, 6), 16);

	let adjust = (c) => {
		let adjusted = c + (255 - c)*(percent/100);
		if ( percent < 0 ){
			adjusted = c * (1 + percent/100);
		}
		return Math.min(255, Math.max(0, Math.round(adjusted)));
	};

	r = adjust(r);
	g = adjust(g);
	b = adjust(b);

	return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
};

//Create desktop notifications
nebula.desktopNotification = function(title, message = false, clickCallback, showCallback, closeCallback, errorCallback){
	if ( nebula.checkNotificationPermission() ){
		//Set defaults
		let defaults = {
			dir: "auto", //Direction ["auto", "ltr", "rtl"] (optional)
			lang: "en-US", //Language (optional)
			body: "", //Body message (optional)
			tag: Math.floor(Math.random()*10_000)+1, //Unique tag for notification. Prevents repeat notifications of the same tag. (optional)
			icon: nebula.site.directory.template.uri + "/assets/img/meta/android-chrome-192x192.png" //Thumbnail Icon (optional)
		};

		if ( !message ){
			message = defaults;
		} else if ( typeof message === 'string' ){
			var body = message;
			message = defaults;
			message.body = body;
		} else {
			if ( typeof message.dir === 'undefined' ){
				message.dir = defaults.dir;
			}
			if ( typeof message.lang === 'undefined' ){
				message.lang = defaults.lang;
			}
			if ( typeof message.body === 'undefined' ){
				message.body = defaults.lang;
			}
			if ( typeof message.tag === 'undefined' ){
				message.tag = defaults.tag;
			}
			if ( typeof message.icon === 'undefined' ){
				message.icon = defaults.icon;
			}
		}

		let instance = new Notification(title, message); //Trigger the notification

		if ( clickCallback ){
			instance.onclick = function(){
				clickCallback();
			};
		}
		if ( showCallback ){
			instance.onshow = function(e){
				showCallback();
			};
		} else {
			instance.onshow = function(e){
				setTimeout(function(){
					instance.close();
				}, 20_000);
			};
		}
		if ( closeCallback ){
			instance.onclose = function(){
				closeCallback();
			};
		}
		if ( errorCallback ){
			instance.onerror = function(){
				gtag('event', 'Exception', {
					message: '(JS) Desktop Notification error',
					fatal: false
				});
				errorCallback();
			};
		}
	}

	return false;
};

nebula.checkNotificationPermission = function(){
	Notification = window.Notification || window.mozNotification || window.webkitNotification;
	if ( !(Notification) ){
		return false;
	} else if ( Notification.permission === "granted" ){
		return true;
	} else if ( Notification.permission !== 'denied' ){
		Notification.requestPermission(function(permission){
			if ( !('permission' in Notification) ){ //Firefox and Chrome only
				Notification.permission = permission;
			}
			if ( permission === 'granted' ){
				return true;
			}
		});
	}

	return false;
};

//Check (or set) network availability (online/offline)
nebula.networkAvailable = function(){
	let onlineStatus = ( navigator.onLine )? 'online' : 'offline';

	nebula.dom.body.removeClass('offline');
	if ( onlineStatus === 'offline' ){
		nebula.dom.body.addClass('offline');
	}

	if ( 'localStorage' in window ){
		try {
			localStorage.setItem('network_connection', onlineStatus);
		} catch {
			//Ignore errors
		}
	}

	nebula.dom.document.trigger('nebula_network_change');
};

//Detect Network Connection
nebula.networkConnection = function(){
	let connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || false;
	if ( connection ){
		nebula.user.client.device.connection = {
			type: connection.type,
			metered: connection.metered,
			bandwidth: connection.bandwidth,
		};
	}
};

//Page Visibility
nebula.visibilityChangeActions = function(){
	if ( document.visibilityState === 'prerender' ){ //Page was prerendered
		gtag('event', 'page_visibility', {
			event_category: 'Page Visibility',
			event_action: 'Prerendered',
			event_label: 'Page loaded before tab/window was visible',
			non_interaction: true
		});

		nebula.pauseAllVideos(false);
	}

	if ( document.visibilitystate === 'hidden' ){ //Page is hidden
		nebula.dom.document.trigger('nebula_page_hidden');
		nebula.dom.body.addClass('page-visibility-hidden');
		nebula.pauseAllVideos(false);
	} else { //Page is visible
		nebula.networkAvailable();
		nebula.dom.document.trigger('nebula_page_visible');
		nebula.dom.body.removeClass('page-visibility-hidden');
	}
};

//Check if an element is within the viewport
//This has been working really well, but could be replaced with IntersectionObserver...
nebula.isInViewport = function($element, offset){return nebula.isInView($element, offset);};
nebula.isInView = function($element, offset = 1){
	if ( $element ){
		if ( typeof $element === 'string' ){
			$element = jQuery($element);
		}

		if ( $element.length ){
			let elementTop = $element.offset().top;
			let elementBottom = $element.offset().top+$element.innerHeight();

			let windowTop = nebula.dom.document.scrollTop();
			let windowBottom = nebula.dom.document.scrollTop()+nebula.dom.window.height()*offset;

			if ( !nebula.dom.body.hasClass('page-visibility-hidden') && ((elementTop >= windowTop && elementTop < windowBottom) || (elementBottom >= windowTop && elementBottom < windowBottom) || (elementTop < windowTop && elementBottom > windowBottom)) ){
				return true;
			}
		}
	}

	return false;
};

//A looping function nearly identical to jQuery.each() but supports an async callback function (and can be awaited)
//For example, since this loop function can awaited, the callback function can be yielded inside without causing a race condition
nebula.each = async function(object, callback){
	if ( !object || !callback ){
		return false;
	}

	// Check if object is array-like
	if ( Array.isArray(object) || typeof object.length === 'number' ){
		for ( let index = 0; index < object.length; index++ ){
			let result = await callback.call(object[index], index, object[index]);

			if ( result === false ){ // If callback returns false, break the loop
				break;
			}
		}
	} else {
		// For objects with key-value pairs
		for ( let key in object ){
			if ( object.hasOwnProperty(key) ){
				let result = await callback.call(object[key], key, object[key]);

				if ( result === false ){ // If callback returns false, break the loop
					break;
				}
			}
		}
	}

	return object; //Return the original object (to enable chaining)
};

//Yield back to the main thread when supported by the browser. Otherwise, do nothing.
nebula.yield = function(){
	//Use scheduler.yield if it exists
	if ( 'scheduler' in window && 'yield' in scheduler ){
		return scheduler.yield();
	}

	return; //Fall back to nothing (no yielding)
};

//Check if the Chrome Gemini AI API is available in this browser
nebula.isBrowserAiAvailable = function(){
	//Old way (probably delete this)
	if ( window?.ai?.languageModel && typeof window.ai.languageModel.create === 'function' ){
		return true;
	}

	//New way as of May 2025 (Google I/O)
	if ( LanguageModel && typeof LanguageModel.create == 'function' ){
		return true;
	}

	//await LanguageModel.availability()

	return false;
};