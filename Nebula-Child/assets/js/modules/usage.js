//Child theme event tracking
export function supplementalEventTracking(){
	nebula.cacheSelectors();

	if ( nebula.isDoNotTrack() ){
		return false;
	}

	if ( typeof window.gtm !== 'function' ){
		window.gtm = function(){}; //Prevent gtm() calls from erroring if GA is off or blocked.
	}

	//Simple example:
	//nebula.dom.document.on('click', '.selector', function(){
	//	gtag('event', 'Action', {
	//		event_category: 'Category',
	//		event_label: 'Label',
	//		something_extra: 'Extra', //Feel free to send additional data in custom parameters
	//		non_interaction: true //If this action is not significant (or if not initiated by the user explicitly)
	//	});
	//});

	//Add your custom event tracking here!
}