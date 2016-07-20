<script>
	jQuery(document).ready(function(){
		prefillFacebookFields();
	});

	//Connect to Facebook without using Facebook Login button
	function facebookLoginLogout(){
		if ( !nebula.user.facebook.status ){
			FB.login(function(response){
				checkFacebookStatus();
			}, {scope:'public_profile,email'});
		} else {
			FB.logout(function(response){
				checkFacebookStatus();
			});
		}
		return false;
	}

	//Fetch Facebook user information
	jQuery(document).on('fbinit', function(){
		checkFacebookStatus();
	});
	function checkFacebookStatus(){
		FB.getLoginStatus(function(response){
			nebula.user.facebook = {'status': response.status}
			if ( has(nebula, 'user.facebook') && nebula.user.facebook.status === 'connected' ){ //User is logged into Facebook and is connected to this app.
				FB.api('/me', function(response){
					//Update the Nebula User Facebook Object
					nebula.user.facebook = {
						id: response.id,
						name: {
							first: response.first_name,
							last: response.last_name,
							full: response.name,
						},
						gender: response.gender,
						email: response.email,
						image: {
							base: 'https://graph.facebook.com/' + response.id + '/picture',
							thumbnail: 'https://graph.facebook.com/' + response.id + '/picture?width=100&height=100',
							large: 'https://graph.facebook.com/' + response.id + '/picture?width=1000&height=1000',
						},
						url: response.link,
						location: {
							locale: response.locale,
							timezone: response.timezone,
						},
						verified: response.verified,
					}

					//Update Nebula User Object
					nebula.user.name = {
						first: response.first_name,
						last: response.last_name,
						full: response.name,
					};
					nebula.user.gender = response.gender;
					nebula.user.email = response.email;
					nebula.user.location = {
						locale: response.locale,
						timezone: response.timezone,
					}

					nv('send', {
						'first_name': response.first_name,
						'last_name': response.last_name,
						'full_name': response.name,
						'gender': response.gender,
						'email_address': response.email,
						'locale': response.locale,
						'timezone': response.timezone,
						'facebook_id': response.id,
						'facebook_connect': '1',
					});

					ga('set', gaCustomDimensions['timestamp'], localTimestamp());
					ga('set', gaCustomDimensions['sessionNotes'], sessionNote('FB Connect'));
					ga('set', gaCustomDimensions['fbID'], nebula.user.facebook.id);

					if ( has(nebula, 'user.flags') && nebula.user.flags.fbconnect !== 'true' ){
						ga('send', 'event', 'Social', 'Facebook Connect', nebula.user.facebook.id);
						nebula.user.flags.fbconnect = 'true';
					}

					nebula.dom.body.removeClass('fb-disconnected').addClass('fb-connected fb-' + nebula.user.facebook.id);
					createCookie('nebulaUser', JSON.stringify(nebula.user));
					jQuery(document).trigger('fbConnected');
				});
			} else if ( has(nebula, 'user.facebook') && nebula.user.facebook.status === 'not_authorized' ){ //User is logged into Facebook, but has not connected to this app.
				nebulaConversion('facebook', 'connect', 'remove');
				nebula.dom.body.removeClass('fb-connected').addClass('fb-not_authorized');
				jQuery(document).trigger('fbNotAuthorized');
				if ( nebula.user.flags ){
					nebula.user.flags.fbconnect = 'false';
				}
			} else { //User is not logged into Facebook.
				nebulaConversion('facebook', 'connect', 'remove');
				nebula.dom.body.removeClass('fb-connected').addClass('fb-disconnected');
				jQuery(document).trigger('fbDisconnected');
				if ( has(nebula, 'user.flags') ){
					nebula.user.flags.fbconnect = 'false';
				}
			}
		});
	}

	//Fill or clear form inputs with Facebook data
	function prefillFacebookFields(){
		jQuery(document).on('fbConnected', function(){
			fbConnectFlag = true;

			jQuery('.fb-name, .comment-form-author input, input.name').each(function(){
				jQuery(this).val(nebula.user.facebook.name.full).trigger('keyup');
			});
			jQuery('.fb-first-name, input.first-name').each(function(){
				jQuery(this).val(nebula.user.facebook.name.first).trigger('keyup');
			});
			jQuery('.fb-last-name, input.last-name').each(function(){
				jQuery(this).val(nebula.user.facebook.name.last).trigger('keyup');
			});
			jQuery('.fb-email, .comment-form-email input, .wpcf7-email, input.email').each(function(){
				jQuery(this).val(nebula.user.facebook.email).trigger('keyup');
			});
			debugInfo();
		});

		jQuery(document).on('fbNotAuthorized fbDisconnected', function(){
			if ( fbConnectFlag ){ //If FB was actually logged in at some point.
				jQuery('.fb-form-name, .comment-form-author input, .cform7-name, .fb-form-email, .comment-form-email input, input[type="email"]').each(function(){
					jQuery(this).val('').trigger('keyup');
				});
			}
		});
	}

	//EXAMPLE SCRIPTS BELOW
	//Connect to Facebook without using the Facebook Login button
	jQuery(document).on('click touch tap', '.facebook-connect', function(){
		facebookLoginLogout();
		return false;
	});

	jQuery(document).on('fbConnected', function(){
		jQuery('.facebook-connect-con a').text('Logout').removeClass('disconnected').addClass('connected');
		jQuery('#facebook-connect p strong').text('You have been connected to Facebook, ' + nebula.user.facebook.name.first + '.');
		jQuery('.fbpicture').attr('src', nebula.user.facebook.image.large).show();
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

<div class="row">
	<div class="col-md-12">
		<div id="facebook-connect" style="margin-top: 15px;">
			<p><strong>The Facebook SDK has not loaded yet.</strong></p>
			<img class="fbpicture" style="margin: 0; max-width: 360px;" /><br /><br />
			<div class="fb-login-button" data-max-rows="1" data-size="large" data-show-faces="false" data-auto-logout-link="true" scope="public_profile,email" onlogin="checkFacebookStatus();"></div>
		</div>
	</div><!--/col-->
</div><!--/row-->