<div class="row">
	<div class="sixteen columns">
		<div id="facebook-connect" style="margin-top: 15px;">
			<p><strong>The Facebook SDK has not loaded yet.</strong></p>
			<img class="fbpicture" style="margin: 0; max-width: 360px;" /><br /><br />
			<div class="fb-login-button" data-max-rows="1" data-size="large" data-show-faces="false" data-auto-logout-link="true" scope="public_profile,email" onlogin="checkFacebookStatus();"></div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<script>
	jQuery(document).on('fbConnected', function(){
		jQuery('.facebook-connect-con a').text('Logout').removeClass('disconnected').addClass('connected');
		jQuery('#facebook-connect p strong').text('You have been connected to Facebook, ' + nebula.user.facebook.name.first + '.');
		jQuery('.fbpicture').attr('src', nebula.user.facebook.image.large).show();
		nebulaConversion('facebook', 'connect');
	});

	jQuery(document).on('fbNotAuthorized', function(){
		jQuery('.facebook-connect-con a').text('Connect with Facebook').removeClass('connected').addClass('disconnected');
		jQuery('#facebook-connect p strong').text('Please connect to this site by logging in below:');
		jQuery('.fbpicture').attr('src', '').hide();
	});

	jQuery(document).on('fbDisconnected', function(){
		jQuery('.facebook-connect-con a').text('Connect with Facebook').removeClass('connected').addClass('disconnected');
		jQuery('#facebook-connect p strong').text('You are not logged into Facebook. Log in below:');
		jQuery('.fbpicture').attr('src', '').hide();
	});

	jQuery(window).on('load', function(){
		if ( !window.fbAsyncInit ) {
			jQuery('#facebook-connect p strong').text('The Facebook SDK has not loaded yet. Main.js may have triggered before the async FB SDK loaded- Need to fix this bug! (Try refreshing)').css('color', 'red');
		} else {
			jQuery('#facebook-connect p strong').text('Connect with Facebook for this example:');
		}
	});
</script>