<style>
	.builder-form-heading {display: inline-block; margin: 0; font-size: 12px; font-weight: bold;}
	.inputhelp-description {display: none; margin: 0; padding: 0; font-size: 10px;}

	#generatedoutput {color: #444; font-family: monospace; font-size: 12px; line-height: 14px; padding: 10px; resize: vertical; height: 150px;}
		#generatedoutput.danger {color: maroon;}
	a.selectall {display: block; text-align: right; font-size: 10px;}
	.required {color: red;}

	.generatingspinner {display: none; color: #0098d7; font-size: 12px; text-transform: uppercase; font-weight: bold;}

	#lastcampaignurl {display: none;}
	#lastcampaignurl .lastcampaignurlhere {color: #444; font-family: monospace; font-size: 12px; line-height: 14px; padding: 10px; resize: vertical; height: 100px;}
</style>


<script>
	jQuery(document).ready(function() {

		jQuery('a.inputhelp').on('click', function(){
			jQuery(this).toggleClass('hover');
			jQuery(this).parents('li').find('.inputhelp-description').slideToggle();
			return false;
		});

		if ( readCookie('CampaignURL') ) {
			jQuery('#lastcampaignurl').fadeIn().find('.lastcampaignurlhere').val(readCookie('CampaignURL'));
		}

		jQuery(document).on('keyup blur', 'input.builderinput', function(){
			jQuery('.generatingspinner').fadeIn();

			//Check required fields
			jQuery('.builderrequired').each(function(){
				if ( jQuery(this).val().trim() == '' ) {
					jQuery(this).parents('li').addClass('warning');
				} else {
					jQuery(this).parents('li').removeClass('warning');
				}
			});

			waitForFinalEvent(function(){
		    	generateCampaignURL();
			}, 1000, "campaignurlgenerator");
		});

		jQuery('#destination-url').on('blur', function(){
			if ( validateURL(jQuery('#destination-url').val().trim()) ) {
				jQuery('#destination-url').parents('li').removeClass('danger warning');
			} else {
				jQuery('#destination-url').parents('li').addClass('danger');
			}
		});

		jQuery('a.selectall').on('click', function(){
			jQuery('#generatedoutput').focus().select();
			return false;
		});

	});

	function generateCampaignURL(){
		jQuery('.generatingspinner').fadeOut();
		var generatedResult = '';
		var destinationURL = jQuery('#destination-url').val().trim(); //Validate that it is actually a URL!
		var utm_source = jQuery('#campaign-source').val().trim();
		var utm_medium = jQuery('#campaign-medium').val().trim();
		var utm_term = jQuery('#campaign-term').val().trim();
		var utm_content = jQuery('#campaign-content').val().trim();
		var utm_campaign = jQuery('#campaign-name').val().trim();
		var requiredPassed = 0;

		//Check required fields
		jQuery('.builderrequired').each(function(){
			if ( jQuery(this).val().trim() == '' ) {
				jQuery('#generatedoutput').val('One or more required fields is empty.').addClass('danger');
			} else {
				requiredPassed++;
			}
		});

		if ( requiredPassed >= 4 ) {
			if ( validateURL(destinationURL) ) {
				jQuery('#destination-url').parents('li').removeClass('danger');
				generatedResult = destinationURL;
			} else {
				jQuery('#destination-url').parents('li').addClass('danger');
				jQuery('#generatedoutput').addClass('danger').val('Invalid Destination URL.');
				return false;
			}

			generatedResult += '?utm_source=' + encodeURIComponent(utm_source);
			generatedResult += '&utm_medium=' + encodeURIComponent(utm_medium);

			if ( utm_term != '' ) {
				generatedResult += '&utm_term=' + encodeURIComponent(utm_term);
			}

			if ( utm_content != '' ) {
				generatedResult += '&utm_content=' + encodeURIComponent(utm_content);
			}

			generatedResult += '&utm_campaign=' + encodeURIComponent(utm_campaign);

			jQuery('#generatedoutput').removeClass('danger').val(generatedResult);
			createCookie('CampaignURL', generatedResult);
			ga('send', 'event', 'Campaign URL Generated', generatedResult);
		}
	}

	function validateURL(url) {
		if ( url.indexOf("http") != 0 ) {
			return false;
		}

		var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
			'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
			'((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
			'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
			'(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
			'(\\#[-a-z\\d_]*)?$','i'); // fragment locator

		if( !pattern.test(url) ) {
			return false;
		} else {
			return true;
		}
	}
</script>


<div class="row">
	<div class="sixteen columns">
		<p>Enter values below and the campaign URL will generate automatically. Click the <i class="fa fa-question-circle"></i> links for a description of the field and examples.</p>
		<br/>
	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	<div class="eight columns">

		<form>
			<ul>
				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Destination URL<span class="required">*</span></span>
					<span>
						<input type="url" id="destination-url" class="builderinput text input builderrequired" placeholder="http://">
					</span>
					<p class="inputhelp-description"><strong>(Required)</strong> The URL of the destination page. Don't forget to include the "http://" or "https://" protocol!</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Source<span class="required">*</span></span>
					<span>
						<input type="text" id="campaign-source" class="builderinput text input builderrequired">
					</span>
					<p class="inputhelp-description"><strong>(Required)</strong> The source of the campaign such as a search engine, newsletter name, or referrer.<br/>Examples: Google, Facebook, Newsletter 4</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Medium<span class="required">*</span></span>
					<span>
						<input type="text" id="campaign-medium" class="builderinput text input builderrequired">
					</span>
					<p class="inputhelp-description"><strong>(Required)</strong> The medium of the campaign such as email, or cost-per-click.<br/>Examples: CPC, Banner, Email</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Term</span>
					<span>
						<input type="text" id="campaign-term" class="builderinput text input">
					</span>
					<p class="inputhelp-description"><em>(Optional)</em> Used for paid search. Enter the associated paid keyword(s) with this ad.</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Content</span>
					<span>
						<input type="text" id="campaign-content" class="builderinput text input">
					</span>
					<p class="inputhelp-description"><em>(Optional)</em> Used for differentiating ads/links that point to the same URL.<br/>Examples: Logo Link, Text Link</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Name<span class="required">*</span></span>
					<span>
						<input type="text" id="campaign-name" class="builderinput text input builderrequired">
					</span>
					<p class="inputhelp-description"><strong>(Required)</strong> Used for identifying a specific promotion or campaign.<br/>Examples: Product, Promo Code, Slogan</p>
				</li>
			</ul>
		</form>

	</div><!--/columns-->

	<div class="eight columns">

		<ul>
			<li class="field">
				<span class="builder-form-heading">Generated Campaign URL</span> <span class="generatingspinner"><i class="fa fa-spin fa-spinner" title="Generating..."></i> Generating...</span>
				<span class="message">
					<textarea id="generatedoutput" name="generatedurl" class="textarea input" placeholder="Your generated campaign URL will appear here." readonly></textarea>
				</span>
				<a class="selectall" href="#">Select All</a>
			</li>
		</ul>

		<br/><br/>

		<ul id="lastcampaignurl">
			<li class="field">
				<span class="builder-form-heading">Your last generated campaign URL</span>
				<span class="message">
					<textarea name="lastcampaignurlhere" class="lastcampaignurlhere textarea input" placeholder="None" readonly></textarea>
				</span>
			</li>
		</ul>

	</div><!--/columns-->
</div><!--/row-->