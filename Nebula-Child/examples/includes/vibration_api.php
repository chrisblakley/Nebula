<script>
	jQuery(document).ready(function() {

		if ( !checkVibration() ) {
			jQuery('.notsupported').removeClass('hidden');
			jQuery('.basicvibrate').removeClass('btn-primary').addClass('btn-danger');
		}

		jQuery('.basicvibrate').on('click tap touch', function(){
			nebulaVibrate([150, 150, 150, 150, 75, 75, 150, 150, 150, 150, 450]);
			nebulaConversion('vibration', 'pattern tested');
			return false;
		});

		jQuery('form#patterntester').submit(function(){
			var thePattern = jQuery('input#pattern').val();
			thePattern = thePattern.replace(/\s+/g, '');
			var patternObj = thePattern.split(',');
			nebulaVibrate(patternObj);
			nebulaConversion('vibration', 'pattern tested');
			return false;
		});
	});
</script>

<p class="notsupported hidden" style="font-weight: bold; color: red;">Vibration is not supported in your browser!</p>

<a class="basicvibrate btn btn-primary" href="#">Go Go Vibration Test</a>

<br /><br /><br />
<form id="patterntester">
	<h2>Vibration Pattern Tester</h2>
	<p>Enter comma separated integers (in milliseconds) to test the vibration pattern.</p>
	<input type="text" id="pattern" />
	<input class="btn btn-primary" type="submit" value="Test Pattern" />
</form>