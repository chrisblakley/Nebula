//Note: Do not use jQuery in this file!

window.performance.mark('(Nebula) Inside usage.js (module)');

//Detect Window Errors
window.addEventListener('error', function(error){
	let errorMessage = error.message + ' at ' + error.lineno + ' of ' + error.filename + ' on ' + window.location.href;

	//Ignore browser extension errors and JS console eager evaluation errors
	if ( errorMessage.toLowerCase().includes('-extension://') || errorMessage.toLowerCase().includes('side-effect in debug-evaluate') || errorMessage.toLowerCase().includes('unexpected end of input') ){ //Ex: chrome-extension:// or safari-extension:// -or- errors originating from the JS console itself
		return false;
	}

	if ( error.message.toLowerCase().includes('script error') ){ //If it is a script error
		errorMessage = 'Script error (An error occurred in a script hosted on a different domain)'; //No additional information is available because of the browser's same-origin policy. Use CORS when possible to get additional information.
	} else if ( nebula.site?.options?.js_error_log ){ //If the option is enabled to log JS errors
		let device = nebula.user.client.browser.full + ' (' + nebula.user.client.device.formfactor + ' - ' + nebula.user.client.os + ')';

		let user = '';
		if ( nebula?.user?.id ){
			user = ' [' + nebula.user.id + ']';
		}
		if ( nebula.user.staff ){
			user += ' (' + nebula.user.staff + ')';
		}

		errorMessage += ' using ' + device + user; //Append device and WP data

		fetch(nebula.site.ajax.url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'Cache-Control': 'no-cache',
			},
			body: new URLSearchParams({
				nonce: nebula.site.ajax.nonce,
				action: 'nebula_js_error_log',
				message: errorMessage,
				line: error.lineno,
				filename: error.filename,
				page: window.location.href,
				device: device,
				user: user
			}),
			priority: 'high'
		});
	}

	gtag('event', 'exception', {
		message: '(JS) ' + errorMessage,
		fatal: false //Is there a better way to detect fatal vs non-fatal errors?
	});

	window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array
	window.dataLayer.push({'event': 'nebula-window-error', 'error': errorMessage});

	if ( typeof nebula.crm === 'function' ){
		nebula.crm('event', 'JavaScript Error');
	}

	nebula.usage(error);
}, {passive: true});

//Track Nebula framework errors for quality assurance.
nebula.usage = async function(error = false){
	if ( error ){
		let message = '';
		let lineNumber = '';
		let fileName = '';

		if ( typeof error === 'string' ){ //If a string was sent from another function like nebula.help()
			message = error;
		} else if ( error?.filename.match(/themes\/Nebula-?(main|parent|\d+\.\d+)?\//i) ){
			message = error.message;
			lineNumber = error.lineno;
			fileName = error.filename;
		}

		if ( message ){
			let description = '(JS) ' + message;
			if ( lineNumber || fileName ){
				description += ' at ' + lineNumber + ' of ' + fileName + ' (v' + nebula.version.number + ')';
			}

			//GA4
			fetch('https://www.google-analytics.com/mp/collect?measurement_id=G-79YGGYLVJK&api_secret=rRFT9IynSg-DEo5t7j1mqw', {
				method: 'POST',
				body: JSON.stringify({
					client_id: nebula.user.cid,
					events: [{
						name: 'exception',
						params: {
							user_agent: nebula.user.client.user_agent,
							hostname: window.location.hostname,
							page_location: window.location.href,
							page_title: document.title,
							website_name: nebula.site.name,
							home_url: nebula.site.home_url,
							site_url: nebula.site.directory.root,
							unix_timestamp: Date.now(),
							nebula_version: nebula.version.number,
							nebula_version_date: nebula.version.date,
							client_id: nebula.user.cid,
							theme_type: ( nebula.site.is_child )? 'Child' : 'Parent',
							transport_method: 'JavaScript',
							description: description,
							line_number: lineNumber,
							file_name: fileName,
							fatal: true
						}
					}]
				}),
				priority: 'low'
			});
		}
	}
};