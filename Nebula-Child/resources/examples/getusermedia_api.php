<style>
	#videoerrors {color: red;}

	#snapcon {position: relative; margin: 10px 0;}

	.videocon {position: relative;}
		.videocon #video {position: relative; max-width: 100%;}
		.videocon #canvas {position: relative; max-width: 100%;}
</style>

<script>
	jQuery(document).ready(function() {
		//@TODO "Nebula" 0: Features to look into more: front/back facing camera toggle, audio settings, ideal dimensions, ideal framerate settings, more...?

		var context = jQuery('#canvas')[0].getContext("2d");
		var videoObj = {"video": true};
		videoError = false;
		lastCanvas = false;

		if ( navigator.getUserMedia ){ //Standard
			navigator.getUserMedia(videoObj, function(stream){
				video.src = stream;
				jQuery('#video')[0].play();
				setTimeout(function(){
					snapshotExample(context);
				}, 1500);
			}, function(error){
				jQuery('#videoerrors').text("Video capture error (Standard): " + error.name).removeClass('hidden');
				jQuery('#getusermediacon').addClass('hidden').remove();
				ga('send', 'event', 'Get User Media API Example', 'Capture Error', error.name);
				videoError = true;
			});
		} else if ( navigator.webkitGetUserMedia ){ //WebKit-prefixed
			navigator.webkitGetUserMedia(videoObj, function(stream){
				video.src = window.webkitURL.createObjectURL(stream);
				jQuery('#video')[0].play();
				setTimeout(function(){
					snapshotExample(context);
				}, 1500);
			}, function(error){
				jQuery('#videoerrors').text("Video capture error (Prefixed): " + error.name).removeClass('hidden');
				jQuery('#getusermediacon').addClass('hidden').remove();
				ga('send', 'event', 'Get User Media API Example', 'Capture Error', error.name);
				videoError = true;
			});
		} else {
			videoError = true;
			jQuery('#videoerrors').text("getUserMedia API is not supported in this environment.").removeClass('hidden');
			jQuery('#getusermediacon').addClass('hidden').remove();
		}

		if ( !videoError ){
			jQuery('#snapcon').removeClass('hidden');
			snapshotCount = 0;

			jQuery('#snap').on('click tap touch', function(){
				if ( snapshotCount <= 3 ) {
					snapshotExample(context);
					nv('send', {'get_user_media_example': '1'});
				} else {
					jQuery('#snapcon').fadeOut();
					ga('send', 'event', 'Get User Media API Example', 'Snap Limit Reached', 'Snapshot Count: ' + snapshotCount);
				}

				jQuery('#canvas').removeClass('hidden');
				jQuery('#downloadimage').removeClass('hidden');

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
		image.src = jQuery('#canvas')[0].toDataURL("image/png");
		blankCanvas = jQuery('#empty')[0].toDataURL("image/png");

		//Check similarity of new image vs. last image
		similarPercent = similarity(image.src, lastCanvas);
		if ( similarPercent < 1.4 ){
			//console.log('Similar canvas: ' + similarPercent + '%');
		} else {
			//console.log('Different canvas: ' + similarPercent + '%');
		}

		if ( image.src != blankCanvas ){
			//console.log('not blank');
		} else {
			//console.log('is blank');
		}

		jQuery('#downloadimage').find('a').attr('href', image.src);

		if ( snapshotCount <= 5 && !videoError ){ //&& image.src != blankCanvas //This conditional is not required. Just a safety precaution to prevent flooding.
			lastCanvas = jQuery('#canvas')[0].toDataURL("image/png");
			jQuery.ajax({
				type: "POST",
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'nebula_getusermedia_api',
					data: {
						'userimage': image.src,
						'videoerror': videoError,
					},
				},
				success: function(data){
					ga('send', 'event', 'Get User Media API Example', 'Snapped Photo', 'AJAX Success');
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					ga('send', 'event', 'Get User Media API Example', 'Snapped Photo', 'AJAX Error');
				},
				timeout: 60000
			});
		}
	}

	function similarity(a, b){
	    var lengthA = a.length;
	    var lengthB = b.length;
	    var equivalency = 0;
	    var minLength = ( a.length > b.length )? b.length : a.length;
	    var maxLength = ( a.length < b.length )? b.length : a.length;
	    for ( var i = 0; i < minLength; i++ ){
	        if ( a[i] == b[i] ){
	            equivalency++;
	        }
	    }
	    var weight = equivalency/maxLength;
	    return (weight*100);
	}
</script>

<?php if ( 1==2 ) {

		//****** This code would need to be moved to functions.php to work! ******

		//Store image examples on the server via AJAX.
		add_action('wp_ajax_nebula_getusermedia_api', 'nebula_getusermedia_api');
		add_action('wp_ajax_nopriv_nebula_getusermedia_api', 'nebula_getusermedia_api');
		function nebula_getusermedia_api(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }

			$userImage = $_POST['data']['userimage'];

			//Prevent saving if image is empty.
			$blankCanvas = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAACWCAYAAABkW7XSAAAEYklEQVR4Xu3UAQkAAAwCwdm/9HI83BLIOdw5AgQIRAQWySkmAQIEzmB5AgIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlAABg+UHCBDICBisTFWCEiBgsPwAAQIZAYOVqUpQAgQMlh8gQCAjYLAyVQlKgIDB8gMECGQEDFamKkEJEDBYfoAAgYyAwcpUJSgBAgbLDxAgkBEwWJmqBCVAwGD5AQIEMgIGK1OVoAQIGCw/QIBARsBgZaoSlACBB1YxAJfjJb2jAAAAAElFTkSuQmCC'; //Does this work universally?
			if ( $_POST['data']['videoerror'] == 'true' || $_POST['data']['userimage'] == $blankCanvas ){
				return false;
			}

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
	<div class="col-md-12">
		<div id="videoerrors" class="hidden"></div>

		<div id="getusermediacon">
			<div class="videocon">
				<video id="video" autoplay></video>
			</div>

			<div id="snapcon" class="btn primary medium hidden">
				<a id="snap" href="#">Snap Photo!</a>
			</div>

			<div class="videocon">
				<canvas id="canvas" class="hidden"></canvas>
			</div>

			<div id="downloadimage" class="btn primary medium hidden">
				<a href="#" target="_blank">Open this image in a new tab!</a>
			</div>

			<canvas id="empty"></canvas>
		</div>
	</div><!--/col-->
</div><!--/row-->