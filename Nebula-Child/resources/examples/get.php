<script>
	jQuery(window).on('load', function() {
		//@TODO "Nebula" 0: This can be called on document ready normally, but must reside within main.js or else it becomes a race condition. - I think this is fixed now, try it in a doc ready now.
		if ( get('hello') ) {
			console.log(get('hello'));
			ga('send', 'event', 'Get Example', 'Test query string: ' + get('hello'));
		}
	});
</script>