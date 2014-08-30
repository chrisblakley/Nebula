<?php if ( !array_key_exists('debug', $_GET) ) : ?>
	<h2>Refreshing to enable debug mode!</h2>
	<script>
		document.location = "<?php the_permalink(); ?>?debug";
	</script>
<?php else : ?>
	<?php trigger_error('This is an example of a warning', E_USER_WARNING); ?>	
	<?php trigger_error('This is an example of a notice', E_USER_NOTICE); ?>
	<?php trigger_error('This is an example of a deprecated function', E_USER_DEPRECATED); ?>
<?php endif; ?>