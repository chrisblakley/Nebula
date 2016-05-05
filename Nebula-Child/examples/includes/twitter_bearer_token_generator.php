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
			<div class="form-group">
				<span>Consumer Key</span>
				<input id="consumerkey" class="form-control" type="text" required />
			</div>
			<div class="form-group">
				<span>Consumer Secret</span>
				<input id="consumersecret" class="form-control" type="text" required />
			</div>
			<input class="btn btn-primary" type="submit" value="Generate" /><i class="fa fa-spinner fa-spin hidden" style="font-size: 18px; margin-left: 10px; display: inline-block;"></i>
		</form>

		<div id="generatorresults" class="hidden">
			<div class="form-group">
				<span>Generated Access Token</span>
				<textarea readonly class="form-control" style="padding: 10px 15px; font-family: monospace; font-size: 12px;"></textarea>
			</div>
		</div>
	</div><!--/col-->
</div><!--/row-->