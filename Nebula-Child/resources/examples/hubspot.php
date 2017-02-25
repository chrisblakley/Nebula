<style>
	#hubspot-example ul {padding-left: 0; list-style: none;}
		#hubspot-example ul li {}

	#result .success {color: green;}
</style>

<script>
	jQuery(document).on('ready', function(){
		jQuery('#hubspot-example').submit(function(){
			nvForm();
			hubspot('send', 'contact', jQuery('#emailaddress').val(), {
				firstname: jQuery('#firstname').val(),
				lastname: jQuery('#lastname').val(),
			});

			jQuery('#hubspot-example').slideUp();
			jQuery('#result').html('<p class="success">Form submitted successfully. Refresh the page to see the remote data...</p>');

			return false;
		});
	});

	jQuery(document).on('nebula_hubspot_sent', function(){
		location.reload();
	});
</script>

<div class="row">
	<div class="col-md-12">

		<?php $hubspot_data = nebula_get_hubspot_contact(); ?>
		<?php if ( !empty($hubspot_data) ): ?>
			<p>Hi <strong><?php echo $hubspot_data['properties']['firstname']['value']; ?> <?php echo $hubspot_data['properties']['lastname']['value']; ?></strong>! This data is being remotely pulled from Hubspot. Your email address is <strong><?php echo $hubspot_data['properties']['email']['value']; ?></strong> and your Hubspot Visitor ID is <strong><?php echo $hubspot_data['vid']; ?></strong>. Use the form below to update your information.</p>
		<?php else: ?>
			<p>There is no Hubspot data detected for you yet. Fill out the form below to update your information and see how it can be pulled from Hubspot.</p>
		<?php endif; ?>

		<form id="hubspot-example" method="post">
			<ul>
				<li class="form-group">
					<span class="contact-form-heading">First Name</span>
					<input id="firstname" class="form-control nv-first_name" type="text" placeholder="First Name" required/>
				</li>
				<li class="form-group">
					<span class="contact-form-heading">Last Name</span>
					<input id="lastname" class="form-control nv-last_name" type="text" placeholder="Last Name" required/>
				</li>
				<li class="form-group">
					<span class="contact-form-heading">Email*</span>
					<input id="emailaddress" class="form-control nv-email_address" type="email" placeholder="Email" required/>
				</li>
				<input class="btn btn-primary" type="submit" value="Send">
			</ul>
		</form>

		<div id="result"></div>
	</div><!--/cols-->
</div><!--/row-->