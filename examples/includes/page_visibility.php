<script>
	function getPageVisibility(){
		if ( typeof document.hidden != "undefined" ) {
			return document.hidden;
		} else {
			console.log('page visibility api is not supported');
			return false;
		}
	}
	
	if ( document.visibilityState == 'prerender' ) {
		console.log('This page was prerendered!');
	}
	
	jQuery(document).on('visibilitychange', function(){								
		var pagevislog = jQuery('.pagevislog').text();
		
		if ( getPageVisibility() ) {
			console.log('tab hidden');
			jQuery('.pagevislog').text(pagevislog + '-Hidden-');
		} else {
			console.log('tab visible');
			jQuery('.pagevislog').text(pagevislog + '-Visible-');
		}
	});
	
	jQuery(window).on('load', function(){
		
	});					
</script>

<p><strong>Change tabs to see the log:</strong></p>
<div class="pagevislog" style="min-height: 50px; border: 1px solid blue; padding: 15px;"></div>