<style>
	#videoerrors {color: red;}

	#snapcon {position: relative; margin: 10px 0;}

	.videocon {position: relative;}
		.videocon #video {position: relative; max-width: 100%;}
		.videocon #canvas {position: relative; max-width: 100%;}
</style>


<script>
	jQuery(document).ready(function() {

		var context = jQuery('#canvas')[0].getContext("2d");
		var videoObj = {"video": true};
		videoError = false;

		if ( navigator.getUserMedia ){ //Standard
			navigator.getUserMedia(videoObj, function(stream){
				video.src = stream;
				jQuery('#video')[0].play();
				setTimeout(function(){
					snapshotExample(context);
				}, 500);
			}, function(error){
				jQuery('#videoerrors').text("Video capture error (Standard): ", error.code).removeClass('hidden');
				ga('send', 'event', 'Get User Media API Example Snap', 'Capture Error', '(Standard) Possibly Permission Denied');
				videoError = true;
			});
		} else if ( navigator.webkitGetUserMedia ){ //WebKit-prefixed
			navigator.webkitGetUserMedia(videoObj, function(stream){
				video.src = window.webkitURL.createObjectURL(stream);
				jQuery('#video')[0].play();
				setTimeout(function(){
					snapshotExample(context);
				}, 500);
			}, function(error){
				jQuery('#videoerrors').text("Video capture error (Prefixed): ", error.code).removeClass('hidden');
				ga('send', 'event', 'Get User Media API Example Snap', 'Capture Error', ' (Prefixed) Possibly Permission Denied');
				videoError = true;
			});
		} else {
			jQuery('#videoerrors').text("getUserMedia API is not supported in this environment.").removeClass('hidden');
		}


		if ( !videoError ){
			jQuery('#snapcon').removeClass('hidden');
			snapshotCount = 0;

			jQuery('#snap').on('click tap touch', function(){
				if ( snapshotCount <= 3 ) {
					snapshotExample(context);
				} else {
					jQuery('#snapcon').fadeOut();
					ga('send', 'event', 'Get User Media API Example Snap', 'Snap Limit Reached', 'Snapshot Count: ' + snapshotCount);
				}

				snapshotCount++;
				return false;
			});
		}

	});


	function snapshotExample(context){
		var videoWidth = jQuery('#video').width();
		var videoHeight = jQuery('#video').height();
		jQuery('#canvas').attr('width', videoWidth).attr('height', videoHeight); //Canvas must have width and height ATTRIBUTES set. CSS only alters display size- not the amount of pixels the canvas has (Defaults to 300x150).

		context.drawImage(jQuery('#video')[0], 0, 0, videoWidth, videoHeight);

		//Convert canvas to an image
		var image = new Image();
		image.src = canvas.toDataURL("image/png");
		jQuery('#downloadimage').removeClass('hidden').find('a').attr('href', image.src);

		if ( snapshotCount <= 5 ){ //This conditional is not required. Just a safety precaution to prevent flooding.
			jQuery.ajax({
				type: "POST",
				url: bloginfo["admin_ajax"],
				data: {
					action: 'nebula_getusermedia_api',
					data: {
						'userimage': image.src,
					},
				},
				success: function(data){
					ga('send', 'event', 'Get User Media API Example Snap', 'Snapped Photo', 'AJAX Success');
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					ga('send', 'event', 'Get User Media API Example Snap', 'Snapped Photo', 'AJAX Error');
				},
				timeout: 60000
			});
		}
	}
</script>


<?php if ( 1==2 ) {

		//****** This code would need to be moved to functions.php to work! ******

		//Store image examples on the server via AJAX.
		add_action('wp_ajax_nebula_getusermedia_api', 'nebula_getusermedia_api');
		add_action('wp_ajax_nopriv_nebula_getusermedia_api', 'nebula_getusermedia_api');
		function nebula_getusermedia_api(){
			$userImage = $_POST['data']['userimage'];
			$userImage = str_replace('data:image/png;base64,', '', $userImage);
			$userImage = str_replace(' ', '+', $userImage);
			$data = base64_decode($userImage);

			$upload_dir = wp_upload_dir();
			$file = $upload_dir['basedir'] . '/get_user_media_api_images/' . date('Y-m-d_H-i-s', strtotime('now')) . '_' . uniqid() . '.png';
			$success = file_put_contents($file, $data);

			exit();
		}

} ?>


<div class="row">
	<div class="sixteen columns">

		<div id="videoerrors" class="hidden"></div>

		<div class="videocon">
			<video id="video" autoplay></video>
		</div>

		<div id="snapcon" class="btn primary medium hidden">
			<a id="snap" href="#">Snap Photo!</a>
		</div>

		<div class="videocon">
			<canvas id="canvas"></canvas>
		</div>

		<div id="downloadimage" class="btn primary medium hidden">
			<a href="#" target="_blank">Open this image in a new tab!</a>
		</div>
	</div><!--/columns-->
</div><!--/row-->