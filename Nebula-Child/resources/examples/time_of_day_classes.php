<style>
	#currenttimeclasses {font-size: 24px;}
</style>

<script>
	jQuery(document).on('ready', function(){
		timeClasses = [];
		jQuery.each(jQuery('body').attr('class').split(' '), function(index, value){
			if ( value.indexOf('time-') === 0 ){
				timeClasses.push(value);
			}
		});

		jQuery('#currenttimeclasses').html(timeClasses.join('<br/>'));
	});
</script>

<div class="row">
	<div class="col-md-12">
		<br/>

		<h2>Current time-of-day classes: <strong><?php echo date('g:ia'); ?></strong></h2>

		<br/>

		<div id="currenttimeclasses"></div>

		<br/><br/><br/>
	</div><!--/cols-->
</div><!--/row-->