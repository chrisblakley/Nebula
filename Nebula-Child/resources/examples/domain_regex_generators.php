<style>
	.spambot-regex-con {width: 100%; min-height: 300px; font-size: 12px; font-family: monospace; padding: 5px 10px;}
</style>

<script>
	jQuery(document).on('submit', '#customhostnameregex', function(e){
		jQuery('i.fa-spinner').removeClass('hidden');
		//nebulaConversion('form', 'Example Regex Generator'); //@TODO "Nebula" 0: Change to nv()

		var hostnameData = [{
			'hostnames': jQuery('#validhostnames').val().trim(),
		}];
		jQuery.ajax({
			type: "POST",
			url: nebula.site.ajax.url,
			//@TODO "Nebula" 0: Add nebula.site.ajax.nonce here!
			data: {
				action: 'nebula_domain_regex_generator',
				data: hostnameData,
			},
			success: function(response){
				jQuery('#customregex').text(response);

				jQuery('#customregex').removeClass('hidden');
				jQuery('i.fa-spinner').addClass('hidden');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				jQuery('#generatorresults textarea').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
				ga('send', 'event', 'Error', 'Domain Regex Generator', 'AJAX Error');
				jQuery('i.fa-spinner').addClass('hidden');
			},
			timeout: 60000
		});

		nv('send', {'domain_regex_generator': jQuery('#validhostnames').val().trim()});
		e.preventDefault();
		return false;
	});
</script>

<div class="row">
	<div class="col-md-12">
		<br /><hr /><br />

		<?php if ( function_exists('nebula_spambot_regex') ) : ?>
			<h2>Generated RegEx Pattern - For Excluding Spambots</h2>
			<p>The spambot regex function ran successfully! Here is a simple RegEx pattern of common referral spambots (Generated using <a href="https://gist.github.com/chrisblakley/e31a07380131e726d4b5" target="_blank">this Github Gist</a>).</p>
			<p>This regex pattern requires routine maintenance by modifying (or getting a new) pattern whenever new spambots are discovered. This pattern also fails the 255 character limit in Google Analytics.</p>
			<textarea class="spambot-regex-con"><?php echo nebula_spambot_regex(); ?></textarea>
		<?php else : ?>
			<h2>Warning!</h2>
			<p>Nebula Spambot Regex function does not exist. This implies that there is an error trying to retrieve spambot list from <a href="https://gist.github.com/chrisblakley/e31a07380131e726d4b5" target="_blank">this GitHub Gist</a>!</p>
		<?php endif; ?>

		<br /><br /><hr /><br />

		<h2>Generated RegEx Pattern - For Including Valid Hostnames</h2>
		<p>The valid hostname regex function ran successfully! Here is a simple RegEx pattern of valid hostnames. Add any additional valid hostnames that are not on this domain too!</p>
		<p>This pattern does not require routine maintenance unless additional valid hostnames are added. <strong>Important note: make sure you have included all possible hostnames/domains that are used for your site!</strong></p>
		<p>Enter your valid hostnames in Nebula Options then run the function <?php echo do_shortcode('[code]<?php echo nebula_valid_hostname_regex(); ?>[/code]'); ?> on your own server (or expand the help icon in Nebula Options)!</p>
		<textarea class="spambot-regex-con"><?php echo nebula_valid_hostname_regex(); ?></textarea>

		<br /><br /><hr /><br />

		<h2 id="customhostnames">Custom Valid Hostnames Include Regex Pattern</h2>
		<p>Enter a comma-separated list of valid hostnames here (including domains, sub-domains, vanity domains, etc).</p>
		<form id="customhostnameregex">
			<div class="form-group">
				<span>Valid Hostnames (Comma Separated)</span>
				<input id="validhostnames" class="form-control" type="text" placeholder="gearside.com, gearsidecreative.com">
			</div>
			<input class="btn btn-primary" type="submit" value="Generate"><i class="fa fa-spinner fa-spin hidden" style="font-size: 18px; margin-left: 10px; display: inline-block;"></i>
		</form>
		<textarea id="customregex" class="form-control spambot-regex-con hidden"></textarea>

		<p><em>Note: Google Analytics filters limit patterns to 255 characters.</em></p>
	</div><!--/col-->
</div><!--/row-->