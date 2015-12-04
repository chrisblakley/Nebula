<style>

</style>


<script>
	jQuery(document).on('ready', function(){

		jQuery('.start-timer a').on('click touch tap', function(){
			console.log('Nebula Timer starting...');
			nebulaTimer('example', 'start');
			console.debug(nebulaTimings);
			return false;
		});


		jQuery('.lap-timer a').on('click touch tap', function(){
			console.log('************************');
			console.log('New Nebula Timer lap...');
			console.debug( nebulaTimer('example', 'lap') );
			console.debug(nebulaTimings);
			return false;
		});


		jQuery('.end-timer a').on('click touch tap', function(){
			console.log('Stopping Nebula Timer...');
			console.debug( nebulaTimer('example', 'end') );
			console.debug(nebulaTimings);
			return false;
		});

	});
</script>


<div class="row">
	<div class="sixteen columns">

	<h2>Example</h2>
	<p>Open the JavaScript console to see the data for this example.</p>

	<p class="start-timer"><a href="#">Start Timer</a></p>
	<p class="lap-timer"><a href="#">New Lap</a></p>
	<p class="end-timer"><a href="#">End</a></p>

	</div><!--/columns-->
</div><!--/row-->