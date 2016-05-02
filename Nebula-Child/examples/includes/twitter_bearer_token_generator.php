<script>
	jQuery(document).on('submit', '#bearergenerator', function(e){
		jQuery('i.fa-spinner').removeClass('hidden');

		var consumerData = [{
			'consumerKey': jQuery('#consumerkey').val().trim(),
			'consumerSecret': jQuery('#consumersecret').val().trim()
		}];
		jQuery.ajax({
			type: "POST",
			url: nebula.site.ajax.url,
			//@TODO "Nebula" 0: Add nebula.site.ajax.nonce here!
			data: {
				action: 'nebula_twitter_bearer_token_generator',
				data: consumerData,
			},
			success: function(response){
				bearerResponse = JSON.parse(response);
				if ( bearerResponse.access_token ) {
					jQuery('#generatorresults textarea').text(bearerResponse.access_token);
				} else {
					jQuery('#generatorresults textarea').text(response);
				}

				nebulaConversion('twitter', 'bearer token');

				jQuery('#generatorresults').removeClass('hidden');
				jQuery('i.fa-spinner').addClass('hidden');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				jQuery('#generatorresults textarea').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
				ga('send', 'event', 'Error', 'Twitter Bearer Token Generator', 'AJAX Error');
				jQuery('i.fa-spinner').addClass('hidden');
			},
			timeout: 60000
		});

		e.preventDefault();
		return false;
	});
</script>

<div class="row">
	<div class="col-md-12">

		<p>After creating a Twitter App, enter your consumer key and consumer secret in the following fields. This tool does not store any data.</p>

		<form id="bearergenerator">
			<div class="field">
				<span>Consumer Key</span>
				<input id="consumerkey" class="input" type="text" required />
			</div>
			<div class="field">
				<span>Consumer Secret</span>
				<input id="consumersecret" class="input" type="text" required />
			</div>
			<div class="field btn primary medium">
				<input class="submit" type="submit" value="Generate" style="padding-left: 15px; padding-right: 15px;"/>
			</div><i class="fa fa-spinner fa-spin hidden" style="font-size: 18px; margin-left: 10px; display: inline-block;"></i>
		</form>

		<div id="generatorresults" class="hidden">
			<div class="field">
				<span>Generated Access Token</span>
				<textarea readonly style="padding: 10px 15px; font-family: monospace; font-size: 12px;"></textarea>
			</div>
		</div>

	</div><!--/col-->
</div><!--/row-->