<script>
	jQuery(document).ready(function() {
		if ( jQuery('.cform7-message').length ){
			jQuery('.cform7-message').on('keyup', function(){
		    	localStorage.setItem('global_message', jQuery('.cform7-message').val());
				jQuery('.cform7-message').val(localStorage.getItem('global_message'));
		    });

		    jQuery(window).bind('storage',function(e){
		    	jQuery('.cform7-message').val(localStorage.getItem('global_message'));
		    });

			jQuery('#localstorage-form').submit(function(){
				jQuery('.cform7-message').val('');
				localStorage.removeItem('global_message');
				return false;
			});
		}
	});

	jQuery(window).on('load', function(){
		if ( jQuery('.cform7-message').val() != '' ) {
			localStorage.setItem('global_message', jQuery('.cform7-message').val());
			jQuery('.cform7-message').val(localStorage.getItem('global_message'));
		} else {
			jQuery('.cform7-message').val(localStorage.getItem('global_message'));
		}
	});
</script>

<div class="row">
	<div class="col-md-12">
		<p>Open <strong><a href="<?php the_permalink(); ?>" target="_blank">this page</a></strong> in another tab and change the text below.</p>
		<p>The <code>storage</code> event triggers on the other windows, giving us a way to communicate between windows using localStorage. This will be used for things like contact form messages so tabs/windows can be switched by the user without losing message data.</p>

		<form id="localstorage-form">
			<ul>
				<li class="form-group">
					<span class="contact-form-heading">Message</span>
					<span class="wpcf7-form-control-wrap message">
						<textarea name="message" cols="40" rows="4" class="form-control cform7-message" placeholder="Type here (or waiting for data from other windows...)"></textarea>
					</span>
				</li>
				<li>
					<input id="contact-submit" type="submit" value="Fake Send (for testing)" class="btn btn-primary wpcf7-form-control wpcf7-submit">
				</li>
			</ul>
		</form>
	</div><!--/col-->
</div><!--/row-->