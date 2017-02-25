<script>
	jQuery(document).on('batteryChange', function(){
		jQuery('.batterypercentage').html(nebula.user.client.device.battery.percentage);
		jQuery('.batterystatus').html(nebula.user.client.device.battery.mode);
		jQuery('.batteryfully').html(toTime(nebula.user.client.device.battery.chargingTime));
		jQuery('.batteryremaining').html(toTime(nebula.user.client.device.battery.dischargingTime));
	});

	function isInt(value) {
		var x;
		return isNaN(value) ? !1 : (x = parseFloat(value), (0 | x) === x);
	}

	function toTime(seconds) {
		if ( !isInt(seconds) ){
			return seconds;
		}

		sec = parseInt(seconds, 10);

		var hours = Math.floor(sec/3600),
		minutes = Math.floor((sec-(hours*3600))/60),
		seconds = sec-(hours*3600)-(minutes*60);
		if ( hours < 10 ){ hours = '0' + hours; }
		if ( minutes < 10 ){ minutes = '0' + minutes; }
		if ( seconds < 10 ){ seconds = '0' + seconds; }

		return hours + ':' + minutes;
	}
</script>

<div class="row">
	<div class="col-md-12">
		<h2>Battery Status</h2>
		<p>
			<strong>Percentage:</strong> <span class="batterypercentage">Unsupported</span><br />
			<strong>Status:</strong> <span class="batterystatus">Unsupported</span><br />
			<strong>Fully Charged:</strong> <span class="batteryfully">Unsupported</span><br />
			<strong>Remaining Charge:</strong> <span class="batteryremaining">Unsupported</span><br />
		</p>
	</div><!--/col-->
</div><!--/row-->