<?php
	//This code does not work in this template- it would need to be moved to functions.php
	/*
	add_action('wp_ajax_nebula_example_ajax', 'nebula_example_ajax_function');
	add_action('wp_ajax_nopriv_nebula_example_ajax', 'nebula_example_ajax_function');
	function nebula_example_ajax_function() {
		if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
		echo 'Success! Your message was: "' . $_POST['data'][0]['message'] . '"';
		exit();
	}
	*/
?>

<script>
	jQuery(document).on('submit', '.ajax-example-form', function(e){
		var messageData = [{
			'message': jQuery(".ajax-example-form input.message").val()
		}];
		jQuery.ajax({
			type: "POST",
			url: bloginfo["ajax_url"],
			data: {
				nonce: bloginfo["ajax_nonce"],
				action: 'nebula_example_ajax',
				data: messageData,
			},
			success: function(response){
				jQuery('.example-response').css('border', '1px solid green').text(response);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				jQuery('.example-response').css('border', '1px solid red').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
				ga('send', 'event', 'Error', 'AJAX Error', 'Example AJAX');
			},
			timeout: 60000
		});
		e.preventDefault();
		return false;
	});
</script>

<style>
	.example-response {padding: 15px;}
	.no-js .ajax-example-form,
	.no-js .example-response {display: none;}
</style>

<form class="ajax-example-form">
	<input type="text" class="message" />
	<input type="submit" />
</form>

<div class="example-response"></div>