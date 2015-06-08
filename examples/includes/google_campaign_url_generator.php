<style>
	.builder-form-heading {display: inline-block; margin: 0; font-size: 12px; font-weight: bold;}
	.inputhelp-description {display: none; margin: 0; padding: 0; font-size: 10px;}

	#generatedoutput {color: #444; font-family: monospace; font-size: 12px; line-height: 14px; padding: 10px; resize: vertical; height: 150px;}
		#generatedoutput.danger {color: maroon;}
	a.selectall {display: block; text-align: right; font-size: 10px;}
	.required {color: red;}

	.generatingspinner {display: none; color: #0098d7; font-size: 12px; text-transform: uppercase; font-weight: bold;}

	.example-report {font-size: 12px;}

	#lastcampaignurl {display: none;}
	#lastcampaignurl .lastcampaignurlhere {color: #444; font-family: monospace; font-size: 12px; line-height: 14px; padding: 10px; resize: vertical; height: 100px;}

	p.faq {font-size: 12px; margin-bottom: 30px;}
	span.question {display: block; font-weight: bold;}
	span.answer {display: block;}
	span.source {display: block; text-align: right; font-size: 10px; font-style: italic; color: #999;}
	span.source a {color: #999;}
		span.source a:hover,
		span.source a.hover {color: #0098d7;}
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

			debounce(function(){
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

			jQuery('.ex-source').html(utm_source);
			jQuery('.ex-medium').text(utm_medium);
			jQuery('.ex-name').text(utm_campaign);
			jQuery('.example-report').removeClass('hidden');
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
					<p class="inputhelp-description"><strong>(Required)</strong> The source of the campaign such as a search engine, newsletter name, or referrer.<br/>Examples: google, facebook, newsletter 4, coupon</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Medium<span class="required">*</span></span>
					<span>
						<input type="text" id="campaign-medium" class="builderinput text input builderrequired">
					</span>
					<p class="inputhelp-description"><strong>(Required)</strong> The medium of the campaign such as email, or cost-per-click.<br/>Examples: cpc, banner, email, retargeting, display</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Term</span>
					<span>
						<input type="text" id="campaign-term" class="builderinput text input">
					</span>
					<p class="inputhelp-description"><em>(Optional)</em> Used for paid search. Enter the associated paid keyword(s) with this ad. This can be used for the text that was specifically linked in an email.</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Content</span>
					<span>
						<input type="text" id="campaign-content" class="builderinput text input">
					</span>
					<p class="inputhelp-description"><em>(Optional)</em> Used for differentiating ads/links that point to the same URL.<br/>Examples: buffalo, ottawa, syracuse, logo link, text link</p>
				</li>

				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Campaign Name<span class="required">*</span></span>
					<span>
						<input type="text" id="campaign-name" class="builderinput text input builderrequired">
					</span>
					<p class="inputhelp-description"><strong>(Required)</strong> Used for identifying a specific promotion or campaign.<br/>Examples: clearance sale, promo code, slogan</p>
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

		<p class="example-report hidden">How traffic from this link would appear in Google Analytics:<br/>
			<strong>Acquisition > All Traffic: </strong> "<span class="ex-source"></span> / <span class="ex-medium"></span>"<br/>
			<strong>Acquisition > Campaigns:</strong> "<span class="ex-name"></span>"
		</p>

		<br/>
		<br/>

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

<div class="row">
	<div class="sixteen columns">
		<br/>
		<hr/>

		<h2>Google Analytics Campaign Tracking FAQ</h2>

		<br/>

		<p class="faq">
			<span class="question">What does "UTM" stand for?</span>
			<span class="answer">UTM stands for "Urchin Tracking Module". Urchin was purchased by Google in 2005 and re-branded to "Google Analytics".</span>
			<span class="source">Source: <a href="http://help.campaignmonitor.com/topic.aspx?t=111" target="_blank">Campaign Monitor &raquo;</a></span>
		</p>

		<p class="faq">
			<span class="question">Where can I learn more about Campaigns and Google Analytics in general?</span>
			<span class="answer">The <a href="https://analyticsacademy.withgoogle.com/course01" target="_blank">Digital Analytics Fundamentals</a> course in <a href="https://analyticsacademy.withgoogle.com/" target="_blank">Google's Analytics Academy</a> is a great resource to learn and test your knowledge using their <a href="https://analyticsacademy.withgoogle.com/course01/assessment?name=Fin" target="_blank">assessment test</a>.</span>
			<span class="source">Source: <a href="https://analyticsacademy.withgoogle.com/" target="_blank">Google Analytics Academy &raquo;</a></span>
		</p>

		<p class="faq">
			<span class="question">Where can I take a proof-of-proficiency test and become certified in Google Analytics?</span>
			<span class="answer">The <a href="https://google.starttest.com/" target="_blank">Google Testing Center</a> is what you're looking for. Once you pass the Analytics Individual Qualification, you will become a <a href="https://www.google.com/partners/" target="_blank">Google Partner</a>! For more information, check out the <a href="https://support.google.com/analytics/answer/3424287" target="_blank">Google Analytics IQ FAQ &raquo;</a></span>
			<span class="source">Source: <a href="https://support.google.com/analytics/answer/3424288" target="_blank">Google Analytics IQ &raquo;</a></span>
		</p>

		<p class="faq">
			<span class="question">For Google/Facebook conversion tracking and remarketing, where in the HTML do the tag snippets go?</span>
			<span class="answer"><a href="https://support.google.com/adwords/answer/2476688" target="_blank">Google Remarketing</a> and <a href="https://support.google.com/adwords/answer/1722054?hl=en" target="_blank">Google Conversion</a> tags go at the end of <code>&lt;body&gt;</code>. <a href="https://developers.facebook.com/docs/ads-for-websites/website-custom-audiences/getting-started#install-the-pixel" target="_blank">Facebook Remarketing</a> and <a href="https://www.facebook.com/help/435189689870514/" target="_blank">Facebook Conversion</a> tags need to go at the end of <code>&lt;head&gt;</code>.</span>
			<span class="source">Sources: <a href="https://support.google.com/adwords/answer/2476688" target="_blank">Google Remarketing &raquo;</a>, <a href="https://support.google.com/adwords/answer/1722054?hl=en" target="_blank">Google Conversion &raquo;</a>, <a href="https://developers.facebook.com/docs/ads-for-websites/website-custom-audiences/getting-started#install-the-pixel" target="_blank">Facebook Remarketing &raquo;</a>, <a href="https://www.facebook.com/help/435189689870514/" target="_blank">Facebook Conversion &raquo;</a></span>
		</p>
	</div><!--/coluns-->
</div><!--/row-->