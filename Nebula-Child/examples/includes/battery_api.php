<script>
	jQuery(document).ready(function() {
		var battery;

		// Check for browser support first
		if (!!navigator.getBattery) { //The latest API is supported
			// Use the battery promise to asynchronously call showStatus()
			navigator.getBattery().then(function(battery) {
				showStatus(battery);
			});
		} else if (!!navigator.battery) { //The old API is supported
			var battery = navigator.battery;
			showStatus(battery);
		}

		if (navigator.battery) {
			readBattery(navigator.battery);
			jQuery('.batteryinfo').removeClass('hidden');
			jQuery('.batterynotsupported').addClass('hidden');
		} else if (navigator.getBattery) {
			navigator.getBattery().then(readBattery);
			jQuery('.batteryinfo').removeClass('hidden');
			jQuery('.batterynotsupported').addClass('hidden');
		} else {
			jQuery('.batteryinfo').addClass('hidden');
			jQuery('.batterynotsupported').removeClass('hidden');
		}
	});

	jQuery(window).on('load', function() {
		battery.addEventListener('chargingchange', function() {
			readBattery();
		});

		battery.addEventListener("levelchange", function() {
			readBattery();
		});
	});


	function toTime(sec) {
		sec = parseInt(sec, 10);

		var hours = Math.floor(sec / 3600),
		minutes = Math.floor((sec - (hours * 3600)) / 60),
		seconds = sec - (hours * 3600) - (minutes * 60);

		if (hours < 10) { hours   = '0' + hours; }
		if (minutes < 10) { minutes = '0' + minutes; }
		if (seconds < 10) { seconds = '0' + seconds; }

		return hours + ':' + minutes;
	}

	function readBattery(b) {
		battery = b || battery;

		console.debug(battery);

		var percentage = parseFloat((battery.level * 100).toFixed(2)) + '%',
		fully,
		remmaining;

		if (battery.charging && battery.chargingTime === Infinity) {
			jQuery('.batteryfullyitem').removeClass('hidden');
			fully = 'Calculating...';
		} else if (battery.chargingTime !== Infinity) {
			jQuery('.batteryfullyitem').removeClass('hidden');
			fully = toTime(battery.chargingTime);
		} else {
			jQuery('.batteryfullyitem').addClass('hidden');
		}

		if (!battery.charging && battery.dischargingTime === Infinity) {
			jQuery('.batteryremainingitem').removeClass('hidden');
			remmaining = 'Calculating...';
		} else if (battery.dischargingTime !== Infinity) {
			jQuery('.batteryremainingitem').removeClass('hidden');
			remmaining = toTime(battery.dischargingTime);
		} else {
			jQuery('.batteryremainingitem').addClass('hidden');
		}

		adaptorbattery = battery.charging ? 'Adapter' : 'Battery';

		jQuery('.batterypercentage').html(percentage);
		jQuery('.batterystatus').html(adaptorbattery);
		jQuery('.batteryfully').html(fully);
		jQuery('.batteryremaining').html(remmaining);

	}
</script>

<div class="row">
	<div class="col-md-12">

		<h2>Battery Status</h2>
		<p class="batteryinfo batterysupported">
			<strong>Percentage:</strong> <span class="batterypercentage">Unsupported</span><br />
			<strong>Status:</strong> <span class="batterystatus">Unsupported</span><br />
			<strong class="batteryfullyitem">Fully Charged:</strong> <span class="batteryfullyitem batteryfully">Unsupported</span><br />
			<strong class="batteryremainingitem">Remaining Charge:</strong> <span class="batteryremainingitem batteryremaining">Unsupported</span><br />
		</p>
		<p class="batterynotsupported hidden">Unsupported</p>

	</div><!--/col-->
</div><!--/row-->