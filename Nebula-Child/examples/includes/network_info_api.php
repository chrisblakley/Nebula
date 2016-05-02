<script>
	jQuery(document).ready(function() {

		var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || false;

		console.debug(connection);
		jQuery('.networkdebug').html(JSON.stringify(connection));

		if ( connection ) {
			jQuery('.connectiontype').html(connection.type); //Get the connection type (unknown, ethernet, 2G, 3G, 4G, wifi, none)
			jQuery('.connectionmetered').html(connection.metered);
			jQuery('.connectionbandwidth').html(connection.bandwidth);
		} else {
			jQuery('.networkinfo').html('Your browser does not support the Network Information API.');
		}

	});
</script>


<div class="row">
	<div class="col-md-12">

		<h2>Network Information</h2>
		<p class="networkinfo">
			<strong>Connection Type:</strong> <span class="connectiontype"></span><br />
			<strong>Metered?</strong> <span class="connectionmetered"></span><br />
			<strong>Bandwidth:</strong> <span class="connectionbandwidth"></span><br />
		</p>

		<p><strong>Debug:</strong> <span class="networkdebug"></span></p>
	</div><!--/col-->
</div><!--/row-->