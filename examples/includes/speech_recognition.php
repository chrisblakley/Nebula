<style>
	#start_button {background: #0098d7; font-size: 16px; color: #fff; padding: 3px 10px; -webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
		#start_button:hover {background: #95D600;}
		#start_button.active {-webkit-animation: recording 3s infinite; -moz-animation: recording 3s infinite; -o-animation: recording 3s infinite; animation: recording 3s infinite;}
			@-webkit-keyframes recording {
				0%, 100% {background: red;}
				50% {background: maroon;}
			}
			@-moz-keyframes recording {
				0%, 100% {background: red;}
				50% {background: maroon;}
			}
			@-o-keyframes recording {
				0%, 100% {background: red;}
				50% {background: maroon;}
			}
			@keyframes recording {
				0%, 100% {background: red;}
				50% {background: maroon;}
			}
		#start_button.pending {background: lightgrey;}
			#start_button.active:hover,
			#start_button.active.hover,
			#start_button.pending:hover,
			#start_button.pending.hover {background: maroon;}

	#functionlist ul li {margin-bottom: 10px;}
</style>

<h4 id="speech-help" style="text-align: center;"></h4>

<div id="startbuttoncon" style="text-align: center; margin: 15px 0;">
	<a id="start_button" href="#">
		<i id="start_button_icon" class="fa fa-microphone"></i> <span id="start_button_text"> Start</span>
	</a>
</div>

<p class="speechconfidence" style="margin: 0; font-size: 12px;"></p>

<div id="results" style="border: 1px solid #ccc; background: #fafafa; margin-bottom: 15px; padding: 15px; text-align: left; min-height: 150px; width: 100%;">
	<span id="final_span" style="font-weight: bold; color: black;"></span>
	<span id="interim_span" style="color: #777;"></span>
</div>

<p id="ajaxnavtext" style="font-size: 12px; margin: 0; display: none;">Navigation is still being developed. Your request would have sent you here:</p>
<input id="ajaxarea" type="text" disabled style="display: none; width: 100%; font-size: 12px; margin-bottom: 15px; padding: 3px 15px; border: 1px solid red;" />

<p style="margin: 0; margin-top: 5px; font-size: 12px;">Say <strong>"I love Nebula"</strong> or better yet, introduce yourself and then say it!</p>
<input id="ilovenebula" type="text" disabled style="width: 100%; font-size: 12px; padding: 3px 15px;" /><br /><br />

<div id="functionlist">
	<h4>Functions</h4>

	<ul style="font-size: 12px;">
		<li>
			<strong>"My name is ________"</strong><br />
			<span>Introduce yourself.</span>
		</li>
		<li>
			<strong>"Search for _________"</strong><br />
			<span>Trigger a Wordpress search.</span>
		</li>
		<li>
			<strong>"Navigate to _________"</strong><br />
			<span>Query through page titles, post titles, menu items, categories, and tags (in that order) to find the request. If not found, trigger search results.</span>
		</li>
		<li>
			<strong>"Driving Directions"</strong><br />
			<span>Receive directions to PHG from your current location.</span>
		</li>
		<li>
			<strong>"Stop Listening"</strong><br />
			<span>Stop all speech recognition.</span>
		</li>
	</ul>
</div>

<script>
	jQuery(document).ready(function() {
		jQuery('#speech-help').text('Click on the microphone icon and begin speaking.');

		window.speakerName = '';
		var final_transcript = '';
		var recognizing = false;
		var ignore_onend;
		var start_timestamp;

		window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;

		if ( window.SpeechRecognition === null ) {
			noSpeechRecognition();
		} else {
			var recognition = new window.SpeechRecognition();
			recognition.continuous = true;
			recognition.interimResults = true;
			recognition.lang = 'en-US';
			recognition.maxAlternatives = 1;

			recognition.onstart = function() {
				recognizing = true;
				//Animated start button icon to recording here
				jQuery('#speech-help').text('Speak now.');
				jQuery('#start_button').removeClass().addClass('active');
				jQuery('#start_button_text').text(' Listening...');
				jQuery('#start_button_icon').removeClass().addClass('fa fa-comment');
			};

			recognition.onaudiostart = function(event) { console.log('onaudiostart'); } //When audio is being listened for...?
			recognition.onsoundstart = function(event) { console.log('onsoundstart'); } //When a sound is detected
			recognition.onspeechstart = function(event) { console.log('onspeechstart'); } //When human speech is detected

			recognition.onerror = function(event) {
				if ( event.error == 'no-speech' ) {
					//start_img.src = 'mic.gif';
					jQuery('#speech-help').text('No speech was detected. You may need to adjust your microphone settings.');
					jQuery('#start_button').removeClass();
					jQuery('#start_button_text').text(' No Speech');
					jQuery('#start_button_icon').removeClass().addClass('fa fa-volume-off');
					ignore_onend = true;
					ga('send', 'event', 'Speech Recognition', 'Error', 'No speech was detected.');
				}

				if ( event.error == 'audio-capture' ) {
					//start_img.src = 'mic.gif';
					jQuery('#speech-help').text('No microphone was found. Ensure that a microphone is installed and that microphone settings are configured correctly.');
					jQuery('#start_button').removeClass();
					jQuery('#start_button_text').text(' No Microphone');
					jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone-slash');
					ignore_onend = true;
					ga('send', 'event', 'Speech Recognition', 'Error', 'No microphone was found.');
				}

				if ( event.error == 'not-allowed' ) {
					if (event.timeStamp - start_timestamp < 100) {
						jQuery('#speech-help').text('Permission to use microphone is blocked. To change, go to chrome://settings/contentExceptions#media-stream');
						jQuery('#start_button').removeClass();
						jQuery('#start_button_text').text(' Blocked');
						jQuery('#start_button_icon').removeClass().addClass('fa fa-times-circle');
						ga('send', 'event', 'Speech Recognition', 'Error', 'Permission to use microphone is blocked.');
					} else {
						jQuery('#speech-help').text('Permission to use microphone was denied.');
						jQuery('#start_button').removeClass();
						jQuery('#start_button_text').text(' Denied');
						jQuery('#start_button_icon').removeClass().addClass('fa fa-times-circle-o');
						ga('send', 'event', 'Speech Recognition', 'Error', 'Permission to use microphone was denied.');
					}
					ignore_onend = true;
				}
			};

			recognition.onspeechend = function(event) { console.log('onspeechend'); } //When human speech has stopped
			recognition.onsoundend = function(event) { console.log('onsoundend'); } //When sound has stopped
			recognition.onaudioend = function(event) { console.log('onaudioend'); } //When audio is no longer being listened for...?
			recognition.onnomatch = function(event) { console.log('onnomatch'); } // No idea...

			recognition.onresult = function(event) {
				var interim_transcript = '';
				for ( var i = event.resultIndex; i < event.results.length; ++i ) {
					if ( event.results[i].isFinal ) {
						final_transcript += event.results[i][0].transcript;
						jQuery('.speechconfidence').html('I am <strong>' + (event.results[i][0].confidence*100).toFixed(2) + '%</strong> sure you said:');
						keyPhrases(final_transcript);
					} else {
						interim_transcript += event.results[i][0].transcript;
						jQuery('.speechconfidence').html('I am <strong>' + (event.results[i][0].confidence*100).toFixed(2) + '%</strong> sure you said:');
						keyPhrases(interim_transcript);
					}
				}
				final_transcript = capitalize(final_transcript);
				jQuery('#final_span').text(linebreak(final_transcript));
				jQuery('#interim_span').text(linebreak(interim_transcript));
			};

			recognition.onend = function() {
				recognizing = false;

				if ( final_transcript ) {
					if ( final_transcript.indexOf('*') > -1 ) {
						ga('send', 'event', 'Speech Recognition', 'Transcript (Swearing)', '"' + final_transcript + '"');
					} else {
						ga('send', 'event', 'Speech Recognition', 'Transcript', '"' + final_transcript + '"');
					}
				}

				if ( ignore_onend ) {
					return;
				}

				resetStartButton();
				jQuery('#speech-help').text('Click on the microphone icon and begin speaking.');
			};
		}

		function noSpeechRecognition() {
			jQuery('#results, #startbuttoncon').hide();
			jQuery('#speech-help').text('Speech detection is not supported in your browser.').css('color', 'red');
			ga('send', 'event', 'Speech Recognition', 'Not Supported');
		}

		var two_line = /\n\n/g;
		var one_line = /\n/g;
		function linebreak(s) {
			return s.replace(two_line, '<p></p>').replace(one_line, '<br>');
		}

		var first_char = /\S/;
		function capitalize(s) {
			return s.replace(first_char, function(m) { return m.toUpperCase(); });
		}

		function startListening(event) {
			if ( recognizing ) {
				recognition.stop();
				return;
			}

			final_transcript = '';
			recognition.start();
			ignore_onend = false;
			final_span.innerHTML = '';
			interim_span.innerHTML = '';
			//start_img.src = 'mic-slash.gif';
			jQuery('#speech-help').text('Click the "Allow" button above to enable your microphone.');
			jQuery('#start_button').removeClass().addClass('pending');
			jQuery('#start_button_text').text(' Requesting Permission...');
			jQuery('#start_button_icon').removeClass().addClass('fa fa-external-link-square');
			start_timestamp = event.timeStamp;
		}

		function keyPhrases(transcript) {
			transcript = transcript.toLowerCase();

			//"My Name is _______"
			phraseMyNameIs = ['my name is'];
			if ( checkAlternates(transcript, phraseMyNameIs) ) {
				speakerName = transcript.substr( transcript.indexOf('my name is')+11, 25);
				speakerName = speakerName.substr( 0, speakerName.indexOf(' ') );
				speakerName = speakerName.charAt(0).toUpperCase() + speakerName.slice(1);

				if ( (speakerName == 'Jeff' || speakerName == 'Geoff') && clientinfo["remote_addr"] == '72.43.235.106' ) {
					speakerName = 'Jef';
				}
			}

			//"I Love Nebula"
			phraseILoveNebula = ['i love nebula', 'isle of nebula', 'i love allah', 'i love nutella', 'isle of nutella', 'isle of allah'];
			if ( checkAlternates(transcript, phraseILoveNebula) ) {
				if ( window.speakerName != '' ) {
					jQuery('#ilovenebula').val('I love you too, ' + window.speakerName + '.' );
				} else {
					jQuery('#ilovenebula').val('I love you too.');
				}
			}

			//"Search for ________"
			phraseSearchFor = ['search for'];
			if ( checkAlternates(transcript, phraseSearchFor) ) {
				searchQuery = transcript.substr( transcript.indexOf('search for ')+11, 99);

				jQuery('#speech-help').text('About to search. Say "Stop Listening" or click the button to cancel.');
				setTimeout(function(){
					if ( recognizing ) { //This allows the user to cancel navigation by stopping.
						jQuery('#speech-help').text('Searching now...');
						resetStartButton();
						ignore_onend = true;
						recognition.stop();
						ga('send', 'event', 'Speech Recognition', 'Search for: ' + searchQuery);
						searchQuery = searchQuery.replace(' ', '+');
						window.location.href = bloginfo['home_url'] + '?s=' + searchQuery;
					}
				}, 3000);
			}

			//"Driving Directions"
			phraseDrivingDirections = ['driving directions'];
			if ( checkAlternates(transcript, phraseDrivingDirections) ) {
				jQuery('#speech-help').text('Let\'s get you here...');
				jQuery('#start_button').removeClass();
				jQuery('#start_button_text').text(' Start');
				jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
				ignore_onend = true;
				recognition.stop();
				ga('send', 'event', 'Speech Recognition', 'Driving Directions');
				window.location.href = 'https://www.google.com/maps/dir/Current+Location/<?php echo ( nebula_full_address() )? nebula_full_address(1) : '760+West+Genesee+Street+Syracuse+NY+13204'; ?>';
			}

			//"Navigate to ________"
			phraseNavigateTo = ['navigate to', 'browse to', 'go to'];
			if ( checkAlternates(transcript, phraseNavigateTo) ) {
				navigationRequest = transcript.substr( transcript.indexOf('navigate to ')+12, 99);
				//@TODO "Nebula" 0: Need to set navigationRequest to alt phrases if user said "browser to" or "go to"
				jQuery('#speech-help').text('About to navigate. Say "Stop Listening" or click the button to cancel.');
				jQuery('#ajaxarea').fadeIn();
				setTimeout(function(){
					if ( recognizing ) { //This allows the user to cancel navigation by stopping.
						jQuery('#speech-help').text('Navigating...');
						jQuery('#start_button').removeClass();
						jQuery('#start_button_text').text(' Start');
						jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
						ignore_onend = true;
						recognition.stop();

						jQuery('#ajaxarea').css('border', '1px solid grey');

						jQuery.ajax({
							type: "POST",
							url: '<?php echo admin_url('admin-ajax.php'); ?>',
							data: {
								action: 'navigator',
								data: navigationRequest,
								nonce: '<?php echo wp_create_nonce('nebula_ajax_navigator_nonce'); ?>'
							},
							//dataType: 'html',
							success: function(response){
								jQuery('#ajaxnavtext').fadeIn();
								jQuery('#ajaxarea').val(response).css('border', '1px solid green');
								//@TODO "Nebula" 0: window location href here
								console.log(response);
								ga('send', 'event', 'Speech Recognition', 'Navigate to: ' + navigationRequest, 'Response: ' + response);
							},
							error: function(MLHttpRequest, textStatus, errorThrown){
								console.log('There was an AJAX error: ' + errorThrown);
								ga('send', 'event', 'Speech Recognition', 'Error', 'Navigation error: ' + errorThrown);
							},
							timeout: 60000
						});

					}
				}, 3000);
			}

			//"Stop Listening" (should always be the last check)
			phraseStopListening = ['stop listening', 'topless']; //Phrases that sound like "stop listening".
			if ( checkAlternates(transcript, phraseStopListening) ) {
				jQuery('#speech-help').text('Stopped because you said so.');
				console.log('you requested stop listening');
				jQuery('#start_button').removeClass();
				jQuery('#start_button_text').text(' Start');
				jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
				ignore_onend = true;
				recognition.stop();
			}
		}


		function checkAlternates(transcript, altPhrases) {
			var length = altPhrases.length;
			while ( length-- ) {
				if ( transcript.indexOf(altPhrases[length]) != -1 ) {
					return true;
				}
			}
			return false;
		}

		function resetStartButton() {
			jQuery('#start_button').removeClass();
			jQuery('#start_button_text').text(' Start');
			jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
		}

		jQuery('#start_button').on('click', function(event){
			startListening(event);
			return false;
		});
	});

	/*
		@TODO "Nebula" 0:
			- Recording button should pulsate color when recording.
	*/

</script>