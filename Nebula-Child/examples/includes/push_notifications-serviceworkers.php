<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/examples/components/serviceworkermanifest.json">


<script>


	//chrome://serviceworker-internals/


	//Tutorial: https://dbwriteups.wordpress.com/2015/11/16/service-workers-part-5-push-notifications/

	//Nested: '<?php echo get_template_directory_uri(); ?>/examples/components/wp_sw.js'
	//Theme root (ideal): '<?php echo get_template_directory_uri(); ?>/wp_sw.js'


	if (navigator.serviceWorker) {
	    console.log("ServiceWorkers are supported");
		console.debug('<?php echo get_template_directory_uri(); ?>/examples/components/wp_sw.js');

	    navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/examples/components/wp_sw.js', {
	        //scope: './' //This triggers an out of scope error/warning.
	        scope: '/nebula/wp-content/themes/Nebula-master/' //This seems to be what it's looking for. Ideally leave this out if that scopes it to the theme root (or site root if that works with the sw.js living in the theme root still).
	    }).then(function(reg) {
	        console.log("ServiceWorker registered!!", reg);
	        console.debug(navigator.serviceWorker);
	    }).catch(function(error) {
	        console.log("Failed to register ServiceWorker.", error);
	    });
	}

	function requestNotificationPermission() {
	    if (Notification.requestPermission) {
	        Notification.requestPermission(function(result) {
	            console.log("Notification permission: ", result);
	        });
	    } else {
	        console.log("Notifications not supported by this browser.");
	    }
	}


	function registerForPush() {
	    if (navigator.serviceWorker.controller) { //This is always null. Could be a 'scope' issue? Where the sw.js file is located? Tried in root, still no luck. Would prefer it to stay within the theme somewhere (even the theme root)...
	        navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
	            serviceWorkerRegistration.pushManager.subscribe({
	                userVisibleOnly: true
	            }).then(function(subscription) {
	                console.log("Subscription for Push successful: ", subscription.endpoint);
	                console.log("DEVICE_REGISTRATION_ID: ", subscription.endpoint.substr(subscription.endpoint.lastIndexOf('/') + 1));
	                }).catch(function(error) {
	                console.log("Subscription for Push failed", error);
	            });
	        });
	    } else {
	        console.log("No active ServiceWorker");
	        console.debug(navigator.serviceWorker);
	    }
	}

	function doesBrowserSupportNotifications() {
	    var supported = true;
	    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
	        console.warn('Notifications aren\'t supported in Service Workers.');
	        supported = false;
	    }

	    if (!Notification.requestPermission) {
	        console.warn("Notifications are not supported by the browser");
	        supported = false;
	    }

	    if (Notification.permission !== 'granted') {
	        console.warn('The user has blocked notifications.');
	        supported = false;
	    }

	    // Check if push messaging is supported
	    if (!('PushManager' in window)) {
	        console.warn('Push messaging isn\'t supported.');
	        supported = false;
	    }

	    if (supported) {
	        console.log("Everthing is fine you can continue")
	    }
	};

</script>


<button type="button" onclick="requestNotificationPermission()">Request for Notification</button>
<button type="button" onclick="registerForPush()">Register For Push</button>




<?php if ( 1==2 ): //I don't know how to get/generate registration ids ?>
curl --header "Authorization: key=AIzaSyANqY4BRp3VC59vLsgu-2IF5jGKPJCm8Jo" --header "Content-Type: application/json" https://android.googleapis.com/gcm/send -d "{\"registration_ids\":[\"APA91bE9DAy6_p9bZ9I58rixOv-ya6PsNMi9Nh5VfV4lpXGw1wS6kxrkQbowwBu17ryjGO0ExDlp-S-mCiwKc5HmVNbyVfylhgwITXBYsmSszpK0LpCxr9Cc3RgxqZD7614SqDokwsc3vIEXkaT8OPIM-mnGMRYG1-hsarEU4coJWNjdFP16gWs\"]}"
<?php endif; ?>