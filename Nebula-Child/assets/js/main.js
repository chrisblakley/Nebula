window.performance.mark('(Child) Inside main.js');
jQuery.noConflict();

/*==========================
 DOM Ready (After nebula.js is loaded)
 ===========================*/

jQuery(function(){
	window.performance.mark('(Child) DOM Ready [Start]');

	nebula.cacheSelectors();

	//Analytics
	if ( !nebula.isDoNotTrack() ){
		import('./modules/usage.js').then(function(module){
			module.supplementalEventTracking();
		});
	}

	window.performance.mark('(Child) DOM Ready [End]');
	window.performance.measure('(Child) DOM Ready Functions', '(Child) DOM Ready [Start]', '(Child) DOM Ready [End]');
}); //End Document Ready

/*==========================
 Window Load
 ===========================*/

window.addEventListener('load', function(){
	window.performance.mark('(Child) Window Load [Start]');

	//Window load functions here

	window.performance.mark('(Child) Window Load [End]');
	window.performance.measure('(Child) Window Load Functions', '(Child) Window Load [Start]', '(Child) Window Load [End]');
}); //End Window Load

/*==========================
 Window Resize
 ===========================*/

// window.addEventListener('resize', function(){
// 	nebula.debounce(function(){
//
// 	}, 250, 'window resize');
// }, {passive: true}); //End Window Resize