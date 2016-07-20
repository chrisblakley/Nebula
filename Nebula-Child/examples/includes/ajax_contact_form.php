<script>
	jQuery(document).on('submit', '#ajax-contact', function(e){
		var contactData = [{
			'name': jQuery("#ajax-contact input.name").val(),
			'email': jQuery("#ajax-contact input.email").val(),
			'message': jQuery("#ajax-contact textarea.message").val(),
		}];

		nv('send', {
			'full_name': jQuery("#ajax-contact input.name").val(),
			'email_address': jQuery("#ajax-contact input.email").val()
		});

		jQuery('#form-messages').html('<i class="fa fa-spinner fa-spin sending"></i> Sending...');

		jQuery.ajax({
			type: "POST",
			url: jQuery('#ajax-contact').attr('action'),
			data: {
				data: contactData,
			},
			success: function(response){
				if ( response.indexOf('Thank you') > -1 ) {
					jQuery('#ajax-contact input:not(#contact-submit), #ajax-contact textarea').val('').trigger('keyup');
					jQuery('#ajax-contact').slideUp();

					//conversionTracker();
					ga('send', 'event', 'Contact', 'Submit', 'AJAX Example Form Submission from ' + contactData[0]['name'] + ': "' + contactData[0]['message'] + '"');
					nebulaConversion('contact', 'Example AJAX Form');
				}
				jQuery('#form-messages').html(response);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				jQuery('#form-messages').text(errorThrown);
				ga('send', 'event', 'Contact', 'Error', 'Contact Form AJAX Error');
			},
			timeout: 60000
		});

		e.preventDefault();
		return false;
	});
</script>

<div class="row">
	<div class="col-md-12">
		<form id="ajax-contact" method="post" action="<?php echo get_template_directory_uri(); ?>/includes/mailer.php">
			<ul>
				<li class="form-group">
					<span class="contact-form-heading">Name*</span>
					<input class="form-control" type="text" placeholder="Name" required/>
				</li>
				<li class="form-group">
					<span class="contact-form-heading">Email*</span>
					<input class="form-control" type="email" placeholder="Email" required/>
				</li>
				<li class="form-group">
					<span class="contact-form-heading">Message*</span>
					<textarea class="form-control" placeholder="Message" required></textarea>
				</li>

				<input class="btn btn-primary" type="submit" value="Send">
			</ul>
		</form>
		<div id="form-messages"></div>
	</div><!--/col-->
</div><!--/row-->