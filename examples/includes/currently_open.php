<div class="row">
	<div class="sixteen columns">
		<?php if ( currently_open() ) : ?>
			<p style="font-size: 32px;"><i class="fa fa-building-o"></i> We are currently <strong style="color: green;">open</strong>!</p>
		<?php else : ?>
			<p style="font-size: 32px;"><i class="fa fa-building"></i> We are currently <strong style="color: maroon;">closed</strong>.</p>
		<?php endif; ?>
	</div><!--/columns-->
</div><!--/row-->