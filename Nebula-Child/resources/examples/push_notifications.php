<script>
	jQuery(document).ready(function() {
		//This is the same as checkNotificationPermission(), but tailored for this example.
		//Generally, you could just call it with: if ( !checkNotificationPermission() ) { //Supported and Permission Granted }
		Notification = window.Notification || window.mozNotification || window.webkitNotification;
		if ( !(Notification) ) {
			jQuery('.notsupported').css('color', 'red').text('Desktop Notifications are not supported in your browser.');
			jQuery('.basicnotify, .fullnotify, .customnotify').removeClass('btn-primary').addClass('btn-danger');
		} else if ( Notification.permission === "granted" ) {
			jQuery('.notsupported').css('color', 'green').addClass('hidden');
		} else if ( Notification.permission !== 'denied' ) {
			jQuery('.notsupported').css('color', 'orange').removeClass('hidden').text('Permissions must be granted to enable Desktop Notifications.');
			Notification.requestPermission(function (permission) {
				if( !('permission' in Notification) ) {
					Notification.permission = permission;
				}
				if (permission === "granted") {
					jQuery('.notsupported').css('color', 'green').addClass('hidden');
				}
			});
		}

		jQuery('.basicnotify').on('click', function(){
			desktopNotification("Basic Notification", "This is the message");
			ga('send', 'event', 'Notification Activated', 'Basic Notification');
			nv('send', {'desktop_notification_example': 'Basic Notification'});
			return false;
		});

		jQuery('.fullnotify').on('click', function(){
			var message = {
				dir: "ltr",
				lang: "en-US",
				body: "This is a fully customized notification with callback functions!",
				icon: nebula.site.template_directory + "/images/meta/favicon-192x192.png"
			}
			desktopNotification("Fully Customized Notification", message, clickNotify, closeNotify, showNotify, errorNotify);
			ga('send', 'event', 'Notification Activated', 'Fully Customized');
			nv('send', {'desktop_notification_example': 'Fully Customized'});

			function clickNotify() {
				jQuery('.fullnotify').removeClass('btn-primary btn-danger btn-success btn-info btn-warning').addClass('btn-success');
				//console.log('You clicked the notification!');
				ga('send', 'event', 'Notification Clicked');
			}

			function closeNotify() {
				jQuery('.fullnotify').removeClass('btn-warning btn-primary btn-info btn-danger btn-success').addClass('btn-info');
				//console.log('You closed the notification.');
				ga('send', 'event', 'Notification Closed');
			}

			function showNotify() {
				jQuery('.fullnotify').removeClass('btn-primary btn-success btn-warning btn-info btn-danger').addClass('btn-warning');
				//console.log('The notification has been shown.');
			}

			function errorNotify() {
				jQuery('.fullnotify').removeClass('btn-primary btn-warning btn-danger btn-success btn-info').addClass('btn-danger');
				//console.log('There was an error with the notification.');
				ga('send', 'event', 'Notification Error', 'An error happened when the fully customized notification was clicked.');
			}

			return false;
		});

		jQuery('.custommessageform').on('submit', function(){
			var customtitle = 'Default Custom Title';
			var custommessage = 'Default custom message.';

			if ( jQuery('.customtitle').val().trim() != '' ) {
				customtitle = jQuery('.customtitle').val().trim();
			}

			if ( jQuery('.custommessage').val().trim() != '' ) {
				custommessage = jQuery('.custommessage').val().trim();
			}

			var message = {
				body: custommessage,
			}
			desktopNotification(customtitle, message);
			ga('send', 'event', 'Notification Activated', 'Custom Message', customtitle + ': ' +  message);
			jQuery('.customtitle').val('').focus();
			jQuery('.custommessage').val('');
			nv('send', {'desktop_notification_example': customtitle + ': ' +  custommessage});
			return false;
		});

		jQuery('.resetcustomfields').on('click', function(){
			jQuery('.customtitle').val('').focus();
			jQuery('.custommessage').val('');
			return false;
		});
	});
</script>

<p class="notsupported" style="font-weight: bold;">Checking notification permissions.</p>

<p>The following button passes only a title and body and uses Nebula defaults for everything else:</p>
<a class="btn btn-primary basicnotify" href="#">Basic Notification</a>

<br /><br /><br /><p>The following button passes everything and uses the callbacks too:</p>
<a class="btn btn-primary fullnotify" href="#">Fully Customized</a>

<form class="custommessageform">
	<fieldset>
		<legend><strong>Custom Message Notification</strong></legend>
		<ul>
			<li class="form-group">
				<input id="text1" class="form-control customtitle" type="text" placeholder="Title"/>
			</li>
			<li class="form-group">
				<input id="text2" class="form-control custommessage" type="text" placeholder="Custom message"/>
			</li>
			<li style="text-align: right; margin-top: -15px;">
				<a class="resetcustomfields" href="#" style="color: red; font-size: 10px;">Reset</a>
			</li>
			<li>
				<input class="btn btn-primary customnotify" type="submit" value="Custom Notification" />
			</li>
		</ul>
	</fieldset>
</form>

<!-- @TODO "Nebula" 0: Make an example of how to close a notification with instance.close(); -->