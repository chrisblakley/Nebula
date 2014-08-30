<div class="row">
	<div class="sixteen columns">
		<div id="facebook-connect" style="margin-top: 15px;">
			<p><strong>The Facebook SDK has not loaded yet.</strong></p>									
			<img class="fbpicture" /><br/><br/>
			<div class="fb-login-button" data-max-rows="1" data-size="large" data-show-faces="false" data-auto-logout-link="true" scope="public_profile,email" onlogin="checkFacebookLogin();"></div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<script>
	jQuery(window).on('load', function(){
		if ( !window.fbAsyncInit ) {
			jQuery('#facebook-connect p strong').text('The Facebook SDK has not loaded yet. Main.js may have triggered before the async FB SDK loaded- Need to fix this bug! (Try refreshing)').css('color', 'red');
		} else {
			jQuery('#facebook-connect p strong').text('Connect with Facebook for this example:');
		}
	});
</script>