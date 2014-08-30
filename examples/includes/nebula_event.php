<?php if ( !array_key_exists('debug', $_GET) ) : ?>
	<h2>Refreshing to enable debug mode!</h2>
	<script>
		document.location = "<?php the_permalink(); ?>?debug";
	</script>
<?php else : ?>
	<script>
		jQuery(document).on('click', 'a.nebula_event', function(){
			nebula_event('Example Nebula Event', 'User Triggered', 'This is the label');
			return false;
		});
	</script>
	
	<div class="medium primary btn">
		<a class="nebula_event" href="#">Trigger sample event</a>
	</div>
<?php endif; ?>