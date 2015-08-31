<style>
	.example-select-supported.yep,
	.example-copy-supported.yep {color: green;}

	.example-copy-supported.maybe {color: orange;}

	.example-select-supported.nope,
	.example-copy-supported.nope {color: red;}

	.example-text-here {border: 1px solid #888; padding: 5px 10px;}
</style>

<script>
	jQuery(document).ready(function() {

		//console.debug( document.queryCommandEnabled('copy') );
		//console.debug( document.queryCommandSupported('SelectAll') );
		//console.debug( document.queryCommandIndeterm('copy') );
		//console.debug( document.queryCommandState('copy') );
		//console.debug( document.queryCommandValue('copy') );



		//This tests to see if selecting text is possible.
		if ( document.body.createTextRange || window.getSelection ) {
			jQuery('.example-select-supported').addClass('yep').html('Selecting text is supported in your browser');
		} else {
			jQuery('.example-select-supported').addClass('nope').html('Selecting text is NOT supported in your browser');
		}


		//Example of selecting text
		jQuery(document).on('click touch tap', '.example-select-trigger', function(){
		    selectText('.example-select');
			return false;
		});



		//This just tests to see if copying is available.
		try {
			if ( document.queryCommandEnabled("copy") ) {
				jQuery('.example-copy-supported').addClass('yep').html('Copying text is supported in your browser');
			} else {
				jQuery('.example-copy-supported').addClass('maybe').html('Copying text might not be supported in your browser... <strong>But try it anyway</strong>- sometimes it still works!');
			}
		} catch(err){
			jQuery('.example-copy-supported').addClass('nope').html('Copying text is NOT supported in your browser.');
		}


		//Example of copying text
		jQuery(document).on('click touch tap', '.example-copy-trigger', function(){
		    selectText('.example-copy', 'copy', function(success){
			    if ( success ) {
				    jQuery('.example-copy-supported').removeClass('maybe').addClass('yep').html('Copying text is supported in your browser');
				    jQuery('.example-copy-trigger').text('Copied!');
				    setTimeout(function(){
					    jQuery('.example-copy-trigger').text('Click here to copy the above text.');
				    }, 2000);
				    jQuery('.selectcopyexamplecon textarea').val('').attr('placeholder', 'Ok, now try pasting it here!');
				} else {
					jQuery('.example-copy-trigger').text('Unable to copy text!');
					jQuery('.example-copy-supported').removeClass('maybe').addClass('nope').html('Copying text is NOT supported in your browser.');
				}
		    });
			return false;
		});

		//Attempt to paste into the textarea automatically when it's clicked...
		jQuery('.selectcopyexamplecon textarea').on('click touch tap', function(){
			jQuery(this).val('');
			try {
				var successfulPaste = document.execCommand('paste');
				var msg = successfulPaste ? 'Automatic paste command was successful!' : 'Automatic paste command was unsuccessful. Please paste manually.';
				jQuery(this).attr('placeholder', msg);
			} catch(err){
				jQuery(this).attr('placeholder', 'Automatic paste command was unsuccessful. Please paste manually.');
			}
		}).on('blur', function(){
			jQuery(this).attr('placeholder', 'Click the copy link and then try pasting here to see if it worked...');
		});
	});
</script>

<div class="row selectcopyexamplecon">
	<div class="sixteen columns">

		<br /><br /><br />

		<div class="example-select-supported"><i class="fa fa-spin fa-spinner"></i> Testing browser support...</div>
		<div class="example-text-here example-select">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam faucibus ligula elit, non feugiat lectus ultrices eu. Sed eget interdum dui. Fusce tempus varius turpis, a viverra mi iaculis euismod.</div>
		<br />
		<div class="medium primary btn">
			<a class="example-select-trigger" href="#">Click here to select the above text.</a>
		</div>

		<br /><br /><hr /><br />

		<div class="example-copy-supported"><i class="fa fa-spin fa-spinner"></i> Testing browser support...</div>
		<div class="example-text-here example-copy">Morbi ac lorem dictum, placerat eros id, tempor ligula. Mauris vitae consequat lacus, eget suscipit nisi. Donec auctor magna augue, non fringilla quam vulputate sit amet. Etiam laoreet nibh vitae mi porta fringilla.</div>
		<br />
		<div class="medium primary btn">
			<a class="example-copy-trigger" href="#">Click here to copy the above text.</a>
		</div>

		<br /><br /><br />

		<textarea id="testtextarea" placeholder="Click the copy link and then try pasting here to see if it worked..." style="width: 100%; min-height: 150px;"></textarea>

	</div><!--/columns-->
</div><!--/row-->