<script>
	jQuery(document).ready(function() {

		var voiceSelect = document.getElementById('voice');
		// Check for browser support
		var supportMsg = document.getElementById('msg');

		if ('speechSynthesis' in window) {
			supportMsg.innerHTML = 'Your browser <strong>supports</strong> speech synthesis.';
		} else {
			supportMsg.innerHTML = 'Sorry your browser <strong>does not support</strong> speech synthesis.';
		}

		jQuery('#speakit').on('click tap touch', function(){
			var textToSay = jQuery('#speaktext').val();
			speak(textToSay);
			//console.log('sending to speak');
			nebulaConversion('speech_synthesis', textToSay);
			return false;
		});

		loadVoices();
		// Fetch the list of voices and populate the voice options.
		function loadVoices() {
		  // Fetch the available voices.
			var voices = speechSynthesis.getVoices();

		  // Loop through each of the voices.
			voices.forEach(function(voice, i) {
		    // Create a new option element.
				var option = document.createElement('option');

		    // Set the options value and text.
				option.value = voice.name;
				option.innerHTML = voice.name;

		    // Add the option to the voice selector.
				voiceSelect.appendChild(option);
			});
		}

		// Chrome loads voices asynchronously.
		window.speechSynthesis.onvoiceschanged = function(e) {
			loadVoices();
		};


		// Create a new utterance for the specified text and add it to the queue.
		function speak(text) {
			// Create a new instance of SpeechSynthesisUtterance.
			var msg = new SpeechSynthesisUtterance();

			//Remove URLs
			var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i; //Strip URLs
		    text = text.replace(exp, "");
			// Set the text.
			msg.text = text;

			var voiceName = jQuery('#voice').val();

			msg.voice = speechSynthesis.getVoices().filter(function(voice){ return voice.name == voiceName; })[0];

			window.speechSynthesis.speak(msg);
		}
	});
</script>

<div class="row">
	<div class="col-md-12">
		<div id="msg">Enable JavaScript to use speech synthesis...</div>

		<select name="voice" id="voice"></select><br /><br />
		<input id="speaktext" type="text" placeholder="Text to speak..."/>
		<a id="speakit" href="#">Speak It!</a>
	</div><!--/col-->
</div><!--/row-->