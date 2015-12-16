<script>
	jQuery(document).on('ready', function(){

		hoverFlag = false;
		jQuery('#testbutton').hover(function(){
			if ( !hoverFlag ){
				nebulaConversion('nebula_retargeting', 'hovered');
				hoverFlag = true;
			}
		});

		jQuery('#testbutton').on('click tap touch', function(){
			nebulaConversion('nebula_retargeting', 'hovered', 'remove');
			nebulaConversion('nebula_retargeting', 'clicked');
			document.location.reload(true);
			return false;
		});

		jQuery('#testreset').on('click tap touch', function(){
			nebulaConversion('nebula_retargeting', 'hovered', 'remove');
			nebulaConversion('nebula_retargeting', 'clicked', 'remove');
			document.location.reload(true);
			return false;
		});

	});
</script>


<div class="row">
	<div class="sixteen columns">

		<div style="padding: 30px; padding-bottom: 60px; text-align: center;">
			<?php if ( nebula_retarget('nebula_retargeting', 'clicked') ): ?>
				<h3><strong>Thanks</strong> for testing this out!</h3>
				<div id="testreset" class="medium secondary btn">
					<a href="#">Click here to reset (or clear cookies).</a>
				</div>
			<?php elseif ( nebula_retarget('nebula_retargeting', 'hovered') ): ?>
				<h3>Ooh, you hovered over it, but <strong>didn't click</strong>.</h3>
				<div id="testbutton" class="medium warning btn">
					<a href="#">What, are you scared? Click here!</a>
				</div>
			<?php else: ?>
				<h3>You <strong>have not</strong> tested this example yet.</h3>
				<div id="testbutton" class="medium primary btn">
					<a href="#">Click here to give it a try!</a>
				</div>
			<?php endif; ?>
		</div>


		<h2>Some other things you have done</h2>
		<ul>
			<?php echo ( nebula_retarget('abandoned_form') )? '<li>You abandoned a form (started without successful submit)</li>' : ''; ?>
			<?php echo ( nebula_retarget('facebook', 'like') )? '<li>Liked something on Facebook</li>' : ''; ?>
			<?php echo ( nebula_retarget('facebook', 'share') )? '<li>Shared something on Facebook</li>' : ''; ?>
			<?php echo ( nebula_retarget('facebook', 'connect') )? '<li>Connected using Facebook</li>' : ''; ?>
			<?php echo ( nebula_retarget('google_plus', 'like') )? '<li>Liked something on Google+</li>' : ''; ?>
			<?php echo ( nebula_retarget('pdf') )? '<li>Viewed a PDF</li>' : ''; ?>
			<?php echo ( nebula_retarget('download') )? '<li>Downloaded something</li>' : ''; ?>
			<?php echo ( nebula_retarget('keywords') )? '<li>Searched the site</li>' : ''; ?>
			<?php echo ( nebula_retarget('keywords', 'chris', false) )? '<li>Searched specifically for Chris</li>' : ''; ?>
			<?php echo ( nebula_retarget('contact', 'email', false) )? '<li>Contacted by email</li>' : ''; ?>
			<?php echo ( nebula_retarget('contact', 'phone', false) )? '<li>Contacted by phone</li>' : ''; ?>
			<?php echo ( nebula_retarget('contact', 'sms', false) )? '<li>Contacted by text message</li>' : ''; ?>
			<?php echo ( nebula_retarget('print') )? '<li>Printed</li>' : ''; ?>
			<?php echo ( nebula_retarget('engaged', 'like') )? '<li>Fully read a page</li>' : ''; ?>
			<?php echo ( nebula_retarget('contact', 'form', false) )? '<li>Submitted a contact form</li>' : ''; ?>
			<?php echo ( nebula_retarget('videos', 'played', false) )? '<li>Played a video</li>' : ''; ?>
			<?php echo ( nebula_retarget('videos', 'engaged', false) )? '<li>Watched part a video</li>' : ''; ?>
			<?php echo ( nebula_retarget('videos', 'finished', false) )? '<li>Finished a video</li>' : ''; ?>
			<?php echo ( nebula_retarget('videos', 'paused', false) )? '<li>Paused a video</li>' : ''; ?>
			<?php echo ( nebula_retarget('videos', 'seeked', false) )? '<li>Seeked within a video</li>' : ''; ?>
			<?php echo ( nebula_retarget('404') )? '<li>Reached a 404 page (sorry about that).</li>' : ''; ?>
			<?php echo ( nebula_retarget('getusermedia') )? '<li>Tested the Get User Media API</li>' : ''; ?>
			<?php echo ( nebula_retarget('campaign_url') )? '<li>Generated a Campaign URL</li>' : ''; ?>
			<?php echo ( nebula_retarget('hero_video', 'played') )? '<li>Played the example hero video</li>' : ''; ?>
			<?php echo ( nebula_retarget('history_api') )? '<li>Tested the History API</li>' : ''; ?>
			<?php echo ( nebula_retarget('nebula_upload') )? '<li>Tested the Nebula JS uploader</li>' : ''; ?>
			<?php echo ( nebula_retarget('contact', 'Example Autocomplete Address') )? '<li>Tested the Autocomplete Address form</li>' : ''; ?>
			<?php echo ( nebula_retarget('nebula_url_components') )? '<li>Tested the URL Components example</li>' : ''; ?>
			<?php echo ( nebula_retarget('notification_api') )? '<li>Tested the Notification API</li>' : ''; ?>
			<?php echo ( nebula_retarget('speech_recognition') )? '<li>Tested the Speech Recognition example</li>' : ''; ?>
			<?php echo ( nebula_retarget('speech_synthesis') )? '<li>Tested the Speech Synthesis example</li>' : ''; ?>
			<?php echo ( nebula_retarget('twitter', 'bearer token') )? '<li>Generated a Twitter Bearer Token</li>' : ''; ?>
			<?php echo ( nebula_retarget('vibration') )? '<li>Tested a vibration pattern</li>' : ''; ?>
			<?php echo ( nebula_retarget('whois') )? '<li>Tested a WHOIS lookup</li>' : ''; ?>
			<?php echo ( nebula_retarget('nebula', 'download') )? '<li>You downloaded Nebula! Thanks!</li>' : ''; ?>
			<?php echo ( nebula_retarget('nebula', 'spotlight', false) )? '<li>You used the Spotlight feature to find something cool!</li>' : ''; ?>
		</ul>

		<br/><br/><br/>
		<h3>In fact, here's all the data we have for you</h3>
		<pre class="nebula-code">
			<?php var_dump($nebula['user']['conversions']); ?>
		</pre>


	</div><!--/columns-->
</div><!--/row-->