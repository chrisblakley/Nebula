<script>
	jQuery(document).ready(function() {

		var connection = window.navigator.connection || window.navigator.mozConnection || null;

		console.debug(connection);

		if ( connection !== null ) {
			jQuery('.connectiontype').html(connection.type); //Get the connection type

			var speed = connection.downlinkMax || connection.bandwidth; //Get the connection speed in megabits per second (Mbps)
			jQuery('.connectionspeed').html(type);
		} else {
			jQuery('.networkinfo').html('Your browser does not support the Network Information API.');
		}

	});
</script>


<div class="row">
	<div class="sixteen columns">

		<h2>Network Information</h2>
		<p class="networkinfo">
			<strong>Connection Type:</strong> <span class="connectiontype"></span><br/>
			<strong>Connection Speed:</strong> <span class="connectionspeed"></span><br/>
		</p>

	</div><!--/columns-->
</div><!--/row-->