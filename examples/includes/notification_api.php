<script>
	jQuery(document).ready(function() {
		if ( !checkNotificationPermission() ) {
			jQuery('.notsupported').removeClass('hidden');
			jQuery('.basicnotify, .fullnotify').parents('div').removeClass('primary').addClass('danger');
		}

		jQuery('.basicnotify').on('click', function(){
			desktopNotification("Basic Notification", "This is the message");
			return false;
		});

		jQuery('.fullnotify').on('click', function(){
			var message = {
				dir: "ltr",
				lang: "en-US",
				body: "This is a fully customized notification with callback functions!",
				icon: bloginfo['template_directory'] + "/images/og-thumb2.png"
			}
			desktopNotification("Fully Customized Notification", message, clickNotify, closeNotify, showNotify, errorNotify);

			function clickNotify() {
				jQuery('.fullnotify').parents('div').removeClass('primary danger info warning').addClass('success');
			}

			function closeNotify() {
				jQuery('.fullnotify').parents('div').removeClass('warning primary danger success').addClass('info');
			}

			function showNotify() {
				jQuery('.fullnotify').parents('div').removeClass('primary success info danger').addClass('warning');
			}

			function errorNotify() {
				jQuery('.fullnotify').parents('div').removeClass('primary warning success info').addClass('danger');
			}

			return false;
		});
	});
</script>


<p class="notsupported hidden" style="font-weight: bold; color: red;">Desktop Notifications are not supported in your browser!</p>


<p>The following button passes only a title and body and uses Nebula defaults for everything else:</p>
<div class="medium primary btn">
	<a class="basicnotify" href="#">Basic Notification</a>
</div>

<br/><br/><br/><p>The following button passes everything and uses the callbacks too:</p>
<div class="medium primary btn">
	<a class="fullnotify" href="#">Fully Customized</a>
</div>

<!-- @TODO "Nebula" 0: Make an example of how to close a notification with instance.close(); -->