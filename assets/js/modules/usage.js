//Note: Do not use jQuery in this file!

window.performance.mark('(Nebula) Inside usage.js (module)');

//Detect Window Errors
window.addEventListener('error', function(error){
	let errorMessage = error.message + ' at ' + error.lineno + ' of ' + error.filename;
	if ( error.message.toLowerCase().includes('script error') ){ //If it is a script error
		errorMessage = 'Script error (An error occurred in a script hosted on a different domain)'; //No additional information is available because of the browser's same-origin policy. Use CORS when possible to get additional information.
	}

	ga('send', 'exception', {'exDescription': '(JS) ' + errorMessage, 'exFatal': false}); //Is there a better way to detect fatal vs non-fatal errors?

	window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array
	window.dataLayer.push({'event': 'nebula-window-error', 'error': errorMessage});

	if ( typeof nebula.crm === 'function' ){
		nebula.crm('event', 'JavaScript Error');
	}

	nebula.usage(error);
}, {passive: true});

//Track Nebula framework errors for quality assurance. This will need to be updated for GA4 most likely.
nebula.usage = async function(error = {}){
	if ( error ){
		let message = '';
		let lineNumber = '';
		let fileName = '';

		if ( typeof error === 'string' ){ //If a string was sent from another function like nebula.help()
			message = error;
		} else if ( error.filename.match(/themes\/Nebula-?(main|parent|\d+\.\d+)?\//i) ){
			message = error.message;
			lineNumber = error.lineno;
			fileName = error.filename;
		}

		if ( message ){
			let description = '(JS) ' + message;
			if ( lineNumber || fileName ){
				description += ' at ' + lineNumber + ' of ' + fileName;
			}

			navigator.sendBeacon && navigator.sendBeacon('https://www.google-analytics.com/collect', [
				'v=1', //Protocol Version
				'tid=UA-36461517-5', //Tracking ID
				'cid=' + nebula.user.cid,
				'ua=' + nebula.user.client.user_agent, //User Agent
				'dl=' + window.location.href, //Page
				'dt=' + document.title, //Title
				't=exception', //Hit Type
				'exd=' + description, //Exception Detail
				'exf=1', //Fatal Exception?
				'cd1=' + nebula.site.home_url, //Homepage URL
				'cd2=' + Date.now(), //UNIX Time
				'cd6=' + nebula.version.number, //Nebula version
				'cd5=' + nebula.site.directory.root, //Site_URL
				'cd7=' + nebula.user.cid, //GA CID
				'cd9=' + nebula.site.is_child, //Is child theme?
				'cd12=' + window.location.href, //Permalink
				'cn=Nebula Usage', //Campaign
				'cs=' + nebula.site.home_url, //Source
				'cm=WordPress', //Medium
			].join('&'));
		}
	}
};