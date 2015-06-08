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

		if ( document.body.createTextRange || window.getSelection ) {
			jQuery('.example-select-supported').addClass('yep').html('Selecting text is supported in your browser');
		} else {
			jQuery('.example-select-supported').addClass('nope').html('Selecting text is NOT supported in your browser');
		}
		jQuery(document).on('click touch tap', '.example-select-trigger', function(){
		    jQuery('.example-select').selectText();
			return false;
		});

		try {
			if ( document.queryCommandEnabled("copy") ) {
				jQuery('.example-copy-supported').addClass('yep').html('Copying text is supported in your browser');
			} else {
				jQuery('.example-copy-supported').addClass('maybe').html('Copying text might not be supported in your browser... <strong>But try it anyway</strong>- sometimes it still works!');
			}
		} catch(err){
			jQuery('.example-copy-supported').addClass('nope').html('Copying text is NOT supported in your browser.');
		}

		jQuery(document).on('click touch tap', '.example-copy-trigger', function(){
		    jQuery('.example-copy').selectText('copy');
		    jQuery('.example-copy-trigger').text('Attempted to copy!');
		    setTimeout(function(){
			    jQuery('.example-copy-trigger').text('Click here to copy the above text.');
		    }, 2000);
		    jQuery('.selectcopyexamplecon textarea').val('').attr('placeholder', 'Ok, now try pasting it here!');
			return false;
		});


		//@TODO "Nebula" 0: See if you can paste on doc ready the contents of the clipboard. This should be an example of what *not* to do.
/*
		try {
			var successfulPaste = document.execCommand('paste');
			var msg = successfulPaste ? 'successful' : 'unsuccessful';
			console.log('Paste command was ' + msg);
		} catch(err){
			console.log('Unable to paste');
		}
*/

	});
</script>


<div class="row selectcopyexamplecon">
	<div class="sixteen columns">

		<br/><br/><br/>

		<div class="example-select-supported"><i class="fa fa-spin fa-spinner"></i> Testing browser support...</div>
		<div class="example-text-here example-select">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam faucibus ligula elit, non feugiat lectus ultrices eu. Sed eget interdum dui. Fusce tempus varius turpis, a viverra mi iaculis euismod.</div>
		<br/>
		<div class="medium primary btn">
			<a class="example-select-trigger" href="#">Click here to select the above text.</a>
		</div>
		<br/><br/>

		<hr/>

		<br/>

		<div class="example-copy-supported"><i class="fa fa-spin fa-spinner"></i> Testing browser support...</div>
		<div class="example-text-here example-copy">Morbi ac lorem dictum, placerat eros id, tempor ligula. Mauris vitae consequat lacus, eget suscipit nisi. Donec auctor magna augue, non fringilla quam vulputate sit amet. Etiam laoreet nibh vitae mi porta fringilla.</div>
		<br/>
		<div class="medium primary btn">
			<a class="example-copy-trigger" href="#">Click here to copy the above text.</a>
		</div>
		<br/><br/><br/>

		<textarea placeholder="Click the copy link and then try pasting here to see if it worked..." style="width: 100%; min-height: 150px;"></textarea>

	</div><!--/columns-->
</div><!--/row-->