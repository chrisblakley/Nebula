//Child theme event tracking
export function supplementalEventTracking(){
	nebula.cacheSelectors();

	if ( nebula.isDoNotTrack() ){
		return false;
	}

	if ( typeof window.ga !== 'function' ){
		window.ga = function(){}; //Prevent ga() calls from erroring if GA is off or blocked.
	}

	//Simple example:
	//nebula.dom.document.on('click', '.selector', function(){
	//	ga('send', 'event', 'Category', 'Action', 'Label');
	//});

	//Add your custom event tracking here!
}