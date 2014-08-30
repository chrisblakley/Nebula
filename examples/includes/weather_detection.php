<div class="row">
	<div class="sixteen columns">
		<?php nebula_weather(); ?>					
		<p>It is currently <strong><?php echo $current_weather['temp']; ?>&deg;F</strong> and <strong><?php echo $current_weather['conditions']; ?></strong> in <strong><?php echo $current_weather['city']; ?></strong>, <strong><?php echo $current_weather['state']; ?></strong>.</p>
		<p>Sunrise: <strong><?php echo $current_weather['sunrise']; ?></strong>, Sunset: <strong><?php echo $current_weather['sunset']; ?></strong>.</p>							
	</div><!--/columns-->
</div><!--/row-->