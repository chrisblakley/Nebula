<script>
	jQuery(window).on('load', function() {
		//@TODO "Nebula" 0: This can be called on document ready normally, but must reside within main.js or else it becomes a race condition. - I think this is fixed now, try it in a doc ready now.
		if ( GET('hello') ) {
			console.log(GET('hello'));
			nebula_event('GET Example', 'Test query string: ' + GET('hello'));
		}
	});
</script>