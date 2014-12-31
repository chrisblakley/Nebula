jQuery.noConflict();

jQuery(document).ready(function() {

	liveFeedInterval = setInterval(function(){
		liveFeedChecker();
	}, 10000);

}); //End Document Ready


function liveFeedChecker(){
	jQuery('.syncing').fadeIn();

	ajaxTime = new Date().getTime()/1000;

	var liveFeedData = [{
		'id': postinfo['id'],
		'time': Math.round(ajaxTime)
	}];
	jQuery.ajax({
		type: "POST",
		url: bloginfo["admin_ajax"],
		data: {
			action: 'nebula_live_feed',
			data: liveFeedData,
		},
		success: function(response){
			if ( 1==1 ) {
				jQuery('#live-active').removeClass().addClass('connected').text('Live');
			} else {
				jQuery('#live-active').removeClass().addClass('offline').text('Offline');
				clearInterval(liveFeedInterval);
			}

			jQuery('#live-feed-responses').html(response);
			jQuery('.syncing').fadeOut();
		},
		error: function(MLHttpRequest, textStatus, errorThrown){
			jQuery('.syncing').fadeOut();
			jQuery('#live-active').removeClass().addClass('disconnected').text('Disconnected');
		},
		timeout: 60000
	});
}