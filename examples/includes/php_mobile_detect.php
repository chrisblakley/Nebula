<div class="row">
	<div class="sixteen columns">
		<?php if ( $GLOBALS["mobile_detect"]->isMobile() ) : ?>
			<?php if ( $GLOBALS["mobile_detect"]->isTablet() ) : ?>
				<?php if ( $GLOBALS["mobile_detect"]->isIOS() ) : ?>
					<p>You are using an <strong>iPad</strong>.</p>
				<?php elseif ( $GLOBALS["mobile_detect"]->is('AndroidOS') ) : ?>
					<?php if ( $GLOBALS["mobile_detect"]->isSamsung() ) : ?>
						<p>You are using a Samsung tablet.</p>
					<?php else : ?>
						<p>You are using an Android tablet or other tablet device.</p>
					<?php endif; ?>
				<?php else : ?>
					<p>You are using a tablet.</p>
				<?php endif; ?>
			<?php else : ?>
				<?php if ( $GLOBALS["mobile_detect"]->isIOS() ) : ?>
					<p>You are using an <strong>iPhone</strong>.</p>
				<?php elseif ( $GLOBALS["mobile_detect"]->is('AndroidOS') ) : ?>
					<?php if ( $GLOBALS["mobile_detect"]->isSamsung() ) : ?>
						<p>You are using a Samsung phone or other Samsung mobile device.</p>
					<?php else : ?>
						<p>You are using an Android phone or other mobile device.</p>
					<?php endif; ?>
				<?php else : ?>
					<p>You are using a phone or other mobile device.</p>
				<?php endif; ?>
			<?php endif; ?>
		<?php else : ?>
			<p>You are <strong>not</strong> using a mobile device or tablet.</p>
		<?php endif; ?>
	</div><!--/columns-->
</div><!--/row-->