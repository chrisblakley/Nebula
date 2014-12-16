<div class="row">
	<div class="sixteen columns">

		<p>Domain registrar is <strong><a href="http://<?php echo whois_info('registrar_url'); ?>" target="_blank"><?php echo whois_info('registrar'); ?></a></strong> <?php echo ( whois_info('reseller') ) ? '(via ' . whois_info('reseller') . ')' : ''; ?>.</p>
		<p>Domain expiration date is <strong><?php echo date("F j, Y", strtotime(whois_info('expiration'))); ?></strong>.</p>

	</div><!--/columns-->
</div><!--/row-->