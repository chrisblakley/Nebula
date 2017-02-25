<div class="row">
	<div class="col-md-12">
		<p>
			<span>This number will be linked on mobile devices:</span><br />
			<code>nebula_tel_link('(315) 478-6700');</code><br />
			<?php echo nebula_tel_link('(315) 478-6700'); ?>
		</p>

		<br /><br /><hr />

		<p><strong>Here be in-progress testing! Post-dial numbers are not working on Android (and not tested on iOS or Windows Phone)</strong></p>

		<p>
			<span>This number will be linked on mobile devices and uses DMTF tones:</span><br />
			<code>nebula_tel_link('3154786700', 'p239');</code><br />
			<?php echo nebula_tel_link('3154786700', 'p239'); ?>
		</p>

		<p>
			<span>Hard-coded for testing DMTF tones:</span><br />
			<span>Note which devices each format works on!</span><br />

			<a href="tel:+13154786700,239">+13154786700,239</a><br /><br />
			<a href="tel:+13154786700;239">+13154786700;239</a><br /><br />
		</p>

		<p>
			<span>Hard-coded for testing SMS:</span><br />
			<span>Note which devices each format works on!</span><br />

			<a href="sms:+13154805016">SMS no body</a><br /><br />
			<a href="sms:+13154805016;body=This is a message!">SMS ;body (should work on iOS)</a><br /><br />
			<a href="sms:+13154805016?body=This is a message!">SMS ?body</a><br /><br />
			<a href="sms:+13154805016;body=This%20is%20a%20message!">SMS ?body encoded spaces</a><br /><br />
			<a href="sms:+13154805016?body=This%20is%20a%20message!">SMS ?body encoded spaces</a><br /><br />
		</p>
		<hr /><br /><br />

		<p>
			<span>This SMS number will be linked on mobile devices:</span><br />
			<code>nebula_sms_link('3154786700');</code><br />
			<?php echo nebula_tel_link('3154786700'); ?>
		</p>

		<p>
			<span>This SMS number will be linked on mobile devices and has a preset message:</span><br />
			<code>nebula_sms_link('3154786700', 'This is a message!');</code><br />
			<?php echo nebula_tel_link('3154786700', 'This is a message!'); ?>
		</p>

		<p>
			<code>nebula_phone_format('(315) 478-6700');</code><br />
			<?php echo nebula_phone_format('(315) 478-6700'); ?>
		</p>

		<p>
			<code>nebula_phone_format('315.478.6700');</code><br />
			<?php echo nebula_phone_format('(315) 478-6700'); ?>
		</p>

		<p>
			<code>nebula_phone_format('+13154786700');</code><br />
			<?php echo nebula_phone_format('+13154786700'); ?>
		</p>

		<p>
			<code>nebula_phone_format('3154786700');</code><br />
			<?php echo nebula_phone_format('3154786700'); ?>
		</p>

		<p>
			<code>nebula_phone_format('4786700');</code><br />
			<?php echo nebula_phone_format('4786700'); ?>
		</p>

		<p>
			<code>nebula_phone_format('123');</code><br />
			<?php echo nebula_phone_format('123'); ?>
		</p>

		<p>
			<code>nebula_phone_format('3154786700', 'tel');</code><br />
			<?php echo nebula_phone_format('3154786700', 'tel'); ?>
		</p>

		<p>
			<code>nebula_phone_format('(315) 478-6700 x123');</code><br />
			<?php echo nebula_phone_format('(315) 478-6700 x123'); ?>
		</p>

		<p>
			<code>nebula_phone_format('(315) 478-6700', 'human');</code><br />
			<?php echo nebula_phone_format('(315) 478-6700', 'human'); ?>
		</p>
	</div><!--/col-->
</div><!--/row-->