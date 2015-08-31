<div class="row">
	<div class="sixteen columns">
		<?php if ( nebula_weather() ): ?>
			<p>It is currently <strong><?php echo nebula_weather('temp'); ?>&deg;F</strong> and <strong><?php echo nebula_weather('conditions'); ?></strong> in <strong><?php echo nebula_weather('city'); ?></strong>, <strong><?php echo nebula_weather('state'); ?></strong>.</p>
			<p>Sunrise: <strong><?php echo nebula_weather('sunrise'); ?></strong>, Sunset: <strong><?php echo nebula_weather('sunset'); ?></strong>.</p>
		<?php else: ?>
			<p><strong>Error:</strong> Weather forecast does not exist.</p>
		<?php endif; ?>
	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	<div class="sixteen columns">
		<br />
		<h4>Available Data</h4>
		<p>Using the 'json' parameter: <?php echo do_shortcode("[code]nebula_weather('13204', 'json')[/code]"); ?></p>
		<?php echo do_shortcode('[pre lang=js]' . json_encode(nebula_weather('13204', 'json'), JSON_PRETTY_PRINT) . '[/pre]'); ?>
	</div><!--/columns-->
</div><!--/row-->