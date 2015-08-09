<?php if ( is_plugin_active('custom-facebook-feed/custom-facebook-feed.php') ): ?>
	<?php echo do_shortcode('[custom-facebook-feed id=PinckneyHugo num=3]'); ?>
<?php else: ?>
	<p>Custom Facebook Feed plugin is not active.</p>
<?php endif; ?>