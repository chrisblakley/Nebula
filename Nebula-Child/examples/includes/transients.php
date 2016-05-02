<div class="row">
	<div class="col-md-12">

		<?php
			$new_date_time_cache = false; //This variable is for example purposes only and is NOT NEEDED when using transients normally!

			$the_date_time = get_transient('nebula_transient_example');
			if ( empty($the_date_time) || is_debug() ){
				$new_date_time_cache = true;  //This variable is for example purposes only and is NOT NEEDED when using transients normally!
				$the_date_time = date('l, F j, Y - g:ia', strtotime('now'));
				set_transient('nebula_transient_example', $the_date_time, 60*10); //10 minute cache
			}
		?>

		<h3>Super Duper Time Cacher</h3>
		<?php if ( $new_date_time_cache ): ?>
			<p>
				<strong>A winner is you!</strong><br />
				You are storing a <span style="color: green;">brand new</span> transient!<br />
				This transient is now set to: <strong style="color: green;"><?php echo $the_date_time; ?></strong>
			</p>
		<?php else: ?>
			<p>
				<strong>Someone beat you to it!</strong><br />
				You are viewing <span style="color: maroon;">cached data</span> from a transient.<br />
				The transient was set on: <strong style="color: maroon;"><?php echo $the_date_time; ?></strong>
			</p>
		<?php endif; ?>

	</div><!--/col-->
</div><!--/row-->