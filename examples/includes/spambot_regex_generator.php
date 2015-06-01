<style>
	.spambot-regex-con {width: 100%; min-height: 300px; font-size: 12px; font-family: monospace; padding: 5px 10px;}
</style>

<div class="row">
	<div class="sixteen columns">

		<br/><hr/><br/>

		<?php if ( function_exists('nebula_spambot_regex') ) : ?>
			<h2>Generated RegEx Pattern - For Excluding Spambots</h2>
			<p>The spambot regex function ran successfully! Here is a simple RegEx pattern of common referral spambots (Generated using <a href="https://gist.github.com/chrisblakley/e31a07380131e726d4b5" target="_blank">this Github Gist</a>).</p>
			<p>This regex pattern requires routine maintenance by modifying (or getting a new) pattern whenever new spambots are discovered. This pattern also fails the 255 character limit in Google Analytics.</p>
			<textarea class="spambot-regex-con"><?php echo nebula_spambot_regex(); ?></textarea>
		<?php else : ?>
			<h2>Warning!</h2>
			<p>Nebula Spambot Regex function does not exist. This implies that there is an error trying to retrieve spambot list from <a href="https://gist.github.com/chrisblakley/e31a07380131e726d4b5" target="_blank">this GitHub Gist</a>!</p>
		<?php endif; ?>

		<br/><br/><hr/><br/>

		<h2>Generated RegEx Pattern - For Including Valid Hostnames</h2>
		<p>The valid hostname regex function ran successfully! Here is a simple RegEx pattern of valid hostnames. Add any additional valid hostnames that are not on this domain too!</p>
		<p>This pattern does not require routine maintenance unless additional valid hostnames are added. <strong>Important note: make sure you have included all possible hostnames/domains that are used for your site!</strong></p>
		<p>Enter your valid hostnames in Nebula Settings then run the function <?php echo do_shortcode('[code]<?php echo nebula_valid_hostname_regex(); ?>[/code]'); ?> on your own server (or expand the help icon in Nebula Settings)!</p>
		<textarea class="spambot-regex-con"><?php echo nebula_valid_hostname_regex(); ?></textarea>





		<p><em>Note: Google Analytics filters limit patterns to 255 characters.</em></p>

	</div><!--/columns-->
</div><!--/row-->