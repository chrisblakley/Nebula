<div class="row">
	<div class="col-md-12">
		<h3>Right now?</h3>
		<?php if ( business_open() ): ?>
			<p style="font-size: 24px;"><i class="fa fa-building-o"></i> We are currently <strong style="color: green;">open</strong>!</p>
		<?php else : ?>
			<p style="font-size: 24px;"><i class="fa fa-building"></i> We are currently <strong style="color: maroon;">closed</strong>.</p>
		<?php endif; ?>
	</div><!--/col-->
</div><!--/row-->

<br />

<div class="row">
	<div class="col-md-12">
		<h3>Tomorrow at any time?</h3>
		<?php if ( business_open('tomorrow', 1) ): //2nd parameter in for any time on that day. ?>
			<p style="font-size: 24px;"><i class="fa fa-building-o"></i> We will be <strong style="color: green;">open</strong>!</p>
		<?php else : ?>
			<p style="font-size: 24px;"><i class="fa fa-building"></i> We will be <strong style="color: maroon;">closed</strong>.</p>
		<?php endif; ?>
	</div><!--/col-->
</div><!--/row-->

<br />

<div class="row">
	<div class="col-md-12">
		<h3>How about November 24th of this year?</h3>
		<?php
			if ( date('Ymd') == date('Ymd', time()) ){
				$tense = ( time() < strtotime('November 24') )? 'will be' : 'were';
			} else {
				$tense = 'are';
			}
		?>
		<?php if ( business_open('november 24', 1) ): ?>
			<p style="font-size: 24px;"><i class="fa fa-building-o"></i> We <?php echo $tense; ?> <strong style="color: green;">open</strong>!</p>
		<?php else : ?>
			<p style="font-size: 24px;"><i class="fa fa-building"></i> We <?php echo $tense; ?> <strong style="color: maroon;">closed</strong>.</p>
		<?php endif; ?>
	</div><!--/col-->
</div><!--/row-->