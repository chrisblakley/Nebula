<script>
	jQuery(document).ready(function() {

		if ( window.addEventListener ) { //IE9+
			window.addEventListener('error', function(e) {
				if ( typeof e !== 'undefined' && typeof e.message !== 'undefined' && e.lineno != 0 ) {
					ga('send', 'event', 'Error', 'JavaScript Error', e.message + ' in: ' + e.filename + ' on line ' + e.lineno);
					ga('send', 'exception', e.message, false);
				}
			});
		} else { //Older Browsers
			window.attachEvent('onerror', function(e) {
				if ( typeof e !== 'undefined' && typeof e.message !== 'undefined' && e.lineno != 0 ) {
					ga('send', 'event', 'Error', 'JavaScript Error', '(Older Browser): ' . e.message + ' in: ' + e.filename + ' on line ' + e.lineno);
					ga('send', 'exception', e.message, false);
				}
			});
		}

	});
</script>