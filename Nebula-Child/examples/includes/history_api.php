<script>
	jQuery(document).ready(function() {
		history.replaceState(null, document.title, location);
		history.pushState(null, document.title, location);
		console.log('History state modified.');

		if (window.history && window.history.pushState) {
			window.addEventListener("popstate", function(e) {
				if ( !window.dontnavigate ) {
					window.location = "https://gearside.com/";
				}
				e.stopPropagation();
			}, false);
		}
	});

	function modifyURL() {
		window.dontnavigate = 1;
		history.replaceState(null, "Changing the Title Too", "https://gearside.com/new-url");
		console.log('URL modified.');

		nebulaConversion('history_api', true);

		jQuery('div.btn a').fadeOut();

		setTimeout(function(){
			window.dontnavigate = 0;
		}, 1000);
		return false;
	}
</script>

<div class="medium primary btn">
	<a href="#" onclick="modifyURL()">Modify the URL</a>
</div>