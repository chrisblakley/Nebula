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
		//Tooltips
		if ( jQuery('[data-toggle="tooltip"]').length ){
			jQuery('[data-toggle="tooltip"]').tooltip();
		}

		//Popovers
		if ( jQuery('[data-toggle="popover"]').length ){
			jQuery('[data-toggle="popover"]').popover({'trigger': 'hover'});
		}

		nebula.checkBootstrapToggleButtons();
		jQuery('[data-toggle=buttons] input').on('change', function(){
			nebula.checkBootstrapToggleButtons();
		});

		//Carousels - Override this to customize options
		if ( jQuery('.carousel').length ){
			jQuery('.carousel').each(function(){
				if ( jQuery(this).hasClass('auto-indicators') ){
					let carouselID = jQuery(this).attr('id');
					let slideCount = jQuery(this).find('.carousel-item').length;

					let i = 0;
					let markup = '<ol class="carousel-indicators">'; //@TODO "Nebula" 0: Why is there no space between indicators when using this auto-indicators?
					while ( i < slideCount ){
						let active = ( i === 0 )? 'class="active"' : '';
						markup += '<li data-target="#' + carouselID + '" data-slide-to="' + i + '" ' + active + '></li>';
						i++;
					}
					markup += '</ol>';
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
	jQuery('[data-toggle=buttons]').each(function(){
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
	jQuery('img').on('error', function(){
		let thisImage = jQuery(this);
		let imagePath = thisImage.attr('src');
		if ( imagePath.split('.').pop() === 'svg' ){
			let fallbackPNG = imagePath.replace('.svg', '.png');
			jQuery.get(fallbackPNG).done(function(){
				thisImage.prop('src', fallbackPNG);
				thisImage.removeClass('svg');
			}).fail(function(){
				ga('send', 'exception', {'exDescription': '(JS) Broken Image: ' + imagePath, 'exFatal': false});
				nebula.crm('event', 'Broken Image');
			});
		} else {
			ga('send', 'exception', {'exDescription': '(JS) Broken Image: ' + imagePath, 'exFatal': false});
			nebula.crm('event', 'Broken Image');
		}
	});
};

//Send data to other tabs/windows using the Service Worker
nebula.postMessage = function(data = {}){
	if ( navigator?.serviceWorker?.controller && data ){
		navigator.serviceWorker.controller.postMessage(data);
	}
};

//Focus on an element
nebula.focusOnElement = function(element = false){
	if ( !element ){
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

	let queries = [];
	queryParameters.forEach(function(value, key){
		queries[key] = value;
	});

	if ( !parameter ){
		return queries;
	}

	return queries[parameter] || false;
};

//Remove an array of parameters from the query string.
nebula.removeQueryParameter = function(keys, url = location.search){
	//Convert single key to an array if it is provided as a string
	if ( typeof keys === 'string' ){
		keys = [keys];
	}

	let queryParameters = new URLSearchParams(url);

	jQuery.each(keys, function(index, item){
		queryParameters.delete(item);
	});

	return queryParameters.toString();
};

//Trigger a reflow on an element.
//This is useful for repeating animations.
nebula.reflow = function(selector){
	let element;
	if ( typeof selector === 'string' ){
		element = jQuery(selector);
	} else if ( typeof selector === 'object' ){
		element = selector;
	} else {
		return false;
	}

	element.width();
};

//Handle repeated animations in a single function.
nebula.animate = function(selector, newAnimationClasses, oldAnimationClasses){
	let element;
	if ( typeof selector === 'string' ){
		element = jQuery(selector);
	} else if ( typeof selector === 'object' ) {
		element = selector;
	} else {
		return false;
	}

	newAnimationClasses += ' animate';
	element.removeClass(newAnimationClasses); //Remove classes first so they can be re-added.

	if ( oldAnimationClasses ){
		element.removeClass(oldAnimationClasses); //Remove conflicting animation classes.
	}

	nebula.reflow(element); //Refresh the element so it can be animated again.
	element.addClass(newAnimationClasses); //Animate the element.
};

//Helpful animation event listeners
nebula.animationTriggers = function(){
	//On document ready
	jQuery('.ready').each(function(){
		nebula.loadAnimate(jQuery(this));
	});

	//On window load
	nebula.dom.window.on('load', function(){
		jQuery('.load').each(function(){
			nebula.loadAnimate(jQuery(this));
		});
	});

	//On click
	nebula.dom.document.on('click', '.click, [nebula-click]', function(){
		let animationClass = jQuery(this).attr('nebula-click') || '';
		nebula.animate(jQuery(this), animationClass);
	});
};

nebula.loadAnimate = function(oThis){
	let animationDelay = oThis.attr('nebula-delay');
	if ( typeof animationDelay === 'undefined' || animationDelay === 0 ){
		nebula.animate(oThis, 'load-animate');
	} else {
		setTimeout(function(){
			nebula.animate(oThis, 'load-animate');
		}, animationDelay);
	}
};

//Allows something to be called once per pageload.
//Call without self-executing parenthesis in the parameter! Ex: nebula.once(customFunction, 'test example');
//To add parameters, use an array as the 2nd parameter. Ex: nebula.once(customFunction, ['parameter1', 'parameter2'], 'test example');
//Can be used for boolean. Ex: nebula.once('boolean test');
nebula.once = function(fn, args, unique){
	if ( typeof nebula.onces === 'undefined' ){
		nebula.onces = {};
	}

	if ( typeof args === 'string' ){ //If no parameters
		unique = args;
		args = [];
	}

	//Reset all
	if ( fn === 'clear' || fn === 'reset' ){
		nebula.onces = {};
	}

	//Remove a single entry
	if ( fn === 'remove' ){
		delete nebula.onces[unique];
	}

	if ( typeof fn === 'function' ){ //If the first parameter is a function
		if ( typeof nebula.onces[unique] === 'undefined' || !nebula.onces[unique] ){ //@todo "Nebula" 0: Use optional chaining?
			nebula.onces[unique] = true;
			return fn.apply(this, args);
		}
	} else { //Else return boolean
		unique = fn; //If only one parameter is passed
		if ( typeof nebula.onces[unique] === 'undefined' || !nebula.onces[unique] ){ //@todo "Nebula" 0: Use optional chaining?
			nebula.onces[unique] = true;
			return true;
		} else {
			return false;
		}
	}
};

//Waits for events to finish before triggering
//Passing immediate triggers the function on the leading edge (instead of the trailing edge).
nebula.debounce = function(callback = false, wait = 1000, uniqueID = 'No Unique ID', immediate = false){
	if ( !callback ){
		console.error('nebula.debounce() requires a callback function.');
		return false;
	}

	if ( typeof nebula.debounceTimers === 'undefined' ){
		nebula.debounceTimers = {};
	}

	let context = this;
	let args = arguments;
	let later = function(){
		nebula.debounceTimers[uniqueID] = null;
		if ( !immediate ){
			callback.apply(context, args);
		}
	};
	let callNow = immediate && !nebula.debounceTimers[uniqueID];

	clearTimeout(nebula.debounceTimers[uniqueID]); //Clear the timeout on every event. Once events stop the timeout is allowed to complete.
	nebula.debounceTimers[uniqueID] = setTimeout(later, wait);
	if ( callNow ){
		callback.apply(context, args);
	}
};

//Limit functionality to only run once per specified time period
nebula.throttle = function(callback, cooldown = 1000, uniqueID = 'No Unique ID'){
	if ( typeof nebula.throttleTimers === 'undefined' ){
		nebula.throttleTimers = {};
	}

	let context = this;
	let args = arguments;
	let later = function(){
		if ( typeof nebula.throttleTimers[uniqueID] === 'undefined' ){ //If we're not waiting
			window.requestAnimationFrame(function(){
				callback.apply(context, args); //Execute callback function

				nebula.throttleTimers[uniqueID] = 'waiting'; //Prevent future invocations

				//After the cooldown period, allow future invocations
				setTimeout(function(){
					nebula.throttleTimers[uniqueID] = undefined; //Allow future invocations (undefined means it is not waiting)
				}, cooldown);
			});
		}
	};

	return later();
};

//Cookie Management
nebula.createCookie = function(name, value, days = 3650){ //Reduce the default days in 2027 to lower than 10 years (and each year thereafter)
	let expires = ''; //Must remain var
	let date = new Date();
	date.setTime(date.getTime()+(days*24*60*60*1000));
	expires = '; expires=' + date.toGMTString(); //Note: Do not let this cookie expire past 2038 or it instantly expires. http://en.wikipedia.org/wiki/Year_2038_problem
	document.cookie = name + '=' + value + expires + '; path=/;SameSite=Lax';
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
		return false;
	}

	//Can not modify a timer once it has ended.
	if ( typeof nebula.timings[uniqueID] !== 'undefined' && nebula.timings[uniqueID].total > 0 ){ //@todo "Nebula" 0: Use optional chaining?
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
		} else {
			if ( !nebula.timings[uniqueID].lap[lapNumber-1].out ){
				return nebula.timings[uniqueID].lap[lapNumber-1].duration;
			}
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
	if ( typeof timestamp === 'object' ){
		console.warn('Pass date as string in the format: Fri Mar 27 21:40:02 +0000 2016');
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
	if ( diff <= 90 ){ return 'one minute ago'; }
	if ( diff <= 3540 ){ return Math.round(diff/60) + ' minutes ago'; }
	if ( diff <= 5400 ){ return '1 hour ago'; }
	if ( diff <= 86_400 ){ return Math.round(diff/3600) + ' hours ago'; }
	if ( diff <= 129_600 ){ return '1 day ago'; }
	if ( diff < 604_800 ){ return Math.round(diff/86_400) + ' days ago'; }
	if ( diff <= 777_600 ){ return '1 week ago'; }

	return 'on ' + timestamp;
};

//Convert DOM elements into a tree string
nebula.domTreeToString = function(element){
	//If the element is a selector, convert to a jQuery object
	if ( typeof element === 'string' ){
		element = jQuery(element);
	}

	//If the element is a native JS object, convert to jQuery
	if ( element.nodeType ){
		element = jQuery(element);
	}

	//Map the parent elements into an array and concatenate together
	let selector = element.parents().map(function(){
		let parentTag = this.tagName.toLowerCase();

		//Append the ID if a parent element has one
		let parentID = jQuery(this).attr('id');
		if ( parentID ){
			parentTag += '#' + parentID;
		}

		return parentTag;
	}).get().reverse().concat([this.nodeName]).join(' > ');

	selector += element.get(0).tagName.toLowerCase();

	//Append the ID to the last element
	let id = element.attr('id');
	if ( id ){
		selector += '#' + id;
	}

	//Add the classnames to the last element
	let classNames = element.attr('class');
	if ( classNames ){
		selector += '.' + classNames.trim().replaceAll(/\s/gi, '.');
	}

	return selector;
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
nebula.sanitize = function(text){
	return document.createElement('div').appendChild(document.createTextNode(text)).parentNode.innerHTML;
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
				ga('send', 'exception', {'exDescription': '(JS) Desktop Notification error', 'exFatal': false});
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
			if( !('permission' in Notification) ){ //Firefox and Chrome only
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
	if ( navigator.onLine ){
		nebula.dom.body.removeClass('offline');

		if ( 'localStorage' in window ){
			localStorage.setItem('network_connection', 'online');
		}
	} else {
		nebula.dom.body.addClass('offline');

		if ( 'localStorage' in window ){
			localStorage.setItem('network_connection', 'offline');
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
		ga('send', 'event', 'Page Visibility', 'Prerendered', 'Page loaded before tab/window was visible', {'nonInteraction': true});
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
nebula.isInViewport = function(element, offset){return nebula.isInView(element, offset);};
nebula.isInView = function(element, offset = 1){
	if ( element ){
		if ( typeof element === 'string' ){
			element = jQuery(element);
		}

		let elementTop = element.offset().top;
		let elementBottom = element.offset().top+element.innerHeight();

		let windowTop = nebula.dom.document.scrollTop();
		let windowBottom = nebula.dom.document.scrollTop()+nebula.dom.window.height()*offset;

		if ( !nebula.dom.body.hasClass('page-visibility-hidden') && ((elementTop >= windowTop && elementTop < windowBottom) || (elementBottom >= windowTop && elementBottom < windowBottom) || (elementTop < windowTop && elementBottom > windowBottom)) ){
			return true;
		}
	}

	return false;
};