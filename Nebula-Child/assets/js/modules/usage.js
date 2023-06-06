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
	// nebula.dom.document.on('click', '.selector', function(){
	// 	gtag('event', 'event_name_here', {
	// 		event_category: 'Category',
	// 		event_action: 'Action',
	// 		event_label: 'Label',
	// 		text: jQuery(this).text(), //For example: the text of the clicked link
	// 		link: jQuery(this).attr('href'). //For example: the href of the clicked link
	// 		something_extra: 'Extra', //Feel free to send additional custom parameters to help identify this event
	// 		non_interaction: true //If this action is not significant (or if not initiated by the user explicitly)
	// 	});
	// });

	//Add your custom event tracking here!
}