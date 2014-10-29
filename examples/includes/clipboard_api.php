<style>
	.clipboardapicon {display: block;}
		.clipboardapicon.notsupported {display: none;}
	.clipboardapinope {display: none;}
		.clipboardapinope.notsupported {display: block;}
</style>

<script>
	jQuery(document).ready(function() {
		if ( typeof ClipboardEvent === 'undefined' ) {
			jQuery('.clipboardapicon, .clipboardapinope').addClass('notsupported');
		}

		//jQuery('.clipboardapicon textarea').val()

		jQuery('#copy').on('click', function(){
			var clipboardEvent = new ClipboardEvent('copy', {
				dataType: 'text/plain',
				data: document.querySelector('textarea').value
			});
			document.dispatchEvent(clipboardEvent);
			//console.debug(clipboardEvent);
			//console.debug(clipboardEvent.clipboardData);
		});

		jQuery('#cut').on('click', function(){
			var clipboardEvent = new ClipboardEvent('cut', {
				dataType: 'text/plain',
				data: document.querySelector('textarea').value
			});
			document.dispatchEvent(clipboardEvent);
			//console.debug(clipboardEvent);
			//console.debug(clipboardEvent.clipboardData);
		});

		jQuery('#paste').on('click', function(){
			var clipboardEvent = new ClipboardEvent('paste', {
				dataType: 'text/plain',
				data: 'My string'
			});
			document.querySelector('textarea').dispatchEvent(clipboardEvent);
			//jQuery('.clipboardapicon textarea').val(clipboardEvent.clipboardData);
			//console.debug(clipboardEvent);
			//console.debug(clipboardEvent.clipboardData);
		});
	});
</script>

<div class="row">
	<div class="sixteen columns">

		<div class="clipboardapicon">
			<div class="field">
				<textarea>Lorem ipsum dolor sit amet.</textarea>
			</div>

			<button id="copy">copy</button>
			<button id="cut">cut</button>
			<button id="paste">paste</button>
		</div>

		<div class="clipboardapinope">
			<p style="color: red;">The Clipboard API is not supported in your browser.</p>
		</div>

	</div><!--/columns-->
</div><!--/row-->