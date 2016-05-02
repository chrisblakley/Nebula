<style>
	object {width: 300px; height: 600px;}
</style>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/swfobject/2.2/swfobject.min.js"></script>

<script>
	jQuery(document).ready(function() {

		swfobject.embedSWF("<?php echo get_template_directory_uri(); ?>/examples/images/flash_banner_analytics_embed.swf?r<?php echo rand(5, 50000); ?>", "embeddedVersion", "300", "600", "9.0.0");
		swfobject.embedSWF("<?php echo get_template_directory_uri(); ?>/examples/images/flash_banner_analytics_external.swf?r<?php echo rand(5, 50000); ?>", "externalVersion", "300", "600", "9.0.0");
		swfobject.embedSWF("<?php echo get_template_directory_uri(); ?>/examples/images/flash_banner_analytics_hybrid.swf?r<?php echo rand(5, 50000); ?>", "hybridVersion", "300", "600", "9.0.0");

		//console.debug(navigator.userAgent);

		//Use this script inside Flash.
		//ga('create', 'UA-36461517-1', 'auto', {'name': 'nebulaTracker'});
		//ga('nebulaTracker.send', 'event', 'Switched UA');

		//In ActionScript, the snippet would look something like this:
		/*
			var parentURL:String = ExternalInterface.call("window.location.href.toString");
			if ( parentURL == null ) {
				parentURL = 'No URL Detected';
			}

			var parentTitle:String = ExternalInterface.call("window.document.title.toString");
			if ( parentTitle == null ) {
				parentTitle = 'No Page Title Detected';
			}

			ExternalInterface.call("ga('create', 'UA-00000000-1', 'auto', {'name': 'nebulaTracker'})"); //@TODO "Nebula" 0: Replace the UA string with the desired tracking ID.
			ExternalInterface.call("ga('nebulaTracker.send', 'event', 'Flash Banner View', '" + parentURL + "', '" + parentTitle + "')");
		*/

	});
</script>

<div class="row">
	<div class="col-md-12">
		<hr />
		<div id="facebook-connect" style="margin-top: 15px;">
			<p><strong>Connect with Facebook to see how the banner can obtain user information too:</strong></p>
			<?php nebula_facebook_link(); ?>
		</div>
		<hr />
	</div><!--/col-->
</div><!--/row-->

<div class="row">
	<div class="col-md-6">

		<h2>Embedded GA Code</h2>
		<p>This flash banner embeds the entire Google Analytics code within it. This increases the filesize to well over 40kb before artwork/animation even begins.</p>

		<div id="embeddedVersion"></div>

	</div><!--/col-->

	<div class="col-md-6">

		<h2>External GA Code</h2>
		<p>This flash banner attempts to pull a GA code that is already used on the page, and duplicates the tracker (with an alternate ID) to send the event to a different account.</p>

		<div id="externalVersion"></div>

	</div><!--/col-->
</div><!--/row-->

<hr style="margin: 25px 0;"/>

<div class="row">
	<div class="col-md-6">

		<h2>Hybrid GA Code</h2>
		<p>This flash banner detects if the GATracker is imported or not. If so, it uses it, otherwise it attempts to use the external GA code. <em>Note: This is in progress.</em></p>

	</div><!--/col-->

	<div class="col-md-6">

		<div id="hybridVersion"></div>

	</div><!--/col-->
</div><!--/row-->