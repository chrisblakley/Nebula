<h2>Example</h2>
<div class="row">
	<div class="sixteen columns">
		<?php if ( nebula_is_bot() ): ?>
			<p style="font-size: 42px;"><strong>You are a robot!</strong></p>
		<?php endif; ?>

		<p>You are on a <?php echo nebula_get_device('type'); ?> which is a <?php echo nebula_get_device('formfactor'); ?> device. That means it <strong><?php echo ( nebula_is_desktop() )? 'is' : 'is not'; ?></strong> a desktop device, it <strong><?php echo ( nebula_is_tablet() )? 'is' : 'is not'; ?></strong> a tablet device, and it <strong><?php echo ( nebula_is_mobile() )? 'is' : 'is not'; ?></strong> a mobile device.</p>

		<?php if ( nebula_get_device('model') != '' ): ?>
			<p>The device itself is a <strong><?php echo nebula_get_device('full'); ?></strong>.</p>
		<?php endif; ?>

		<p>The operating system on this device is <strong><?php echo nebula_get_os('full'); ?></strong>. That means the OS <strong><?php echo ( nebula_is_os('windows') )? 'is' : 'is not'; ?></strong> Windows, it <strong><?php echo ( nebula_is_os('mac') )? 'is' : 'is not'; ?></strong> Mac, it <strong><?php echo ( nebula_is_os('ios') )? 'is' : 'is not'; ?></strong> iOS, and it <strong><?php echo ( nebula_is_os('android') )? 'is' : 'is not'; ?></strong> Android. For example, this <strong><?php echo ( nebula_is_os('mac', '10.10') )? 'is' : 'is not'; ?></strong> Mac 10.10 (Yosemite), and it <strong><?php echo ( nebula_is_os('mac') )? 'is' : 'is not'; ?></strong> Macintosh.</p>

		<p>You are using <strong><?php echo nebula_get_browser('full'); ?></strong> which uses the <strong><?php echo nebula_get_browser('engine'); ?></strong> rendering engine. For example, this <strong><?php echo ( nebula_is_browser('ie', 10) )? 'is' : 'is not'; ?></strong> Internet Explorer 10, and it <strong><?php echo ( nebula_is_browser('ie') )? 'is' : 'is not'; ?></strong> Internet Explorer.</p>

		<p>
			<?php if ( nebula_is_browser('ie') ): ?>
				Is it less than IE10? <strong><?php echo ( nebula_is_browser('ie', 10, '<') )? 'Yes' : 'No'; ?></strong>
			<?php elseif ( nebula_is_browser('chrome') ): ?>
				Is it less than Chrome 44? <strong><?php echo ( nebula_is_browser('chrome', 43, 'lt') )? 'Yes' : 'No'; ?></strong><br/>
			<?php elseif ( nebula_is_browser('firefox') ): ?>
				Is it less than Firefox 35? <strong><?php echo ( nebula_is_browser('firefox', 38, '<') )? 'Yes' : 'No'; ?></strong>
			<?php elseif ( nebula_is_browser('safari') ): ?>
				Is it less than Safari 6? <strong><?php echo ( nebula_is_browser('safari', 5, 'lt') )? 'Yes' : 'No'; ?></strong>
			<?php endif; ?>
		</p>
	</div><!--/columns-->
</div><!--/row-->

<br/>
<div class="row">
	<div class="sixteen columns">
		<hr/>
		<p>See all data Nebula can see - <a href="https://gearside.com/nebula/documentation/utilities/environment-feature-detection/">Full environment detection &raquo;</a></p>
	</div><!--/columns-->
</div><!--/row-->