window.performance.mark('(Nebula) Inside /admin-modules/users.js');

//Add user fields for headshot image
nebula.userHeadshotFields = function(){
	if ( jQuery('body').hasClass('profile-php') ){
		jQuery('#headshot_button').on('click', function(){
			tb_show('Uploading a new headshot!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
			return false;
		});

		window.send_to_editor = function(html){
			var imageURL = jQuery(html).attr('src');
			jQuery('#headshot_url').val(imageURL); //updates our hidden field that will update our author's meta when the form is saved
			tb_remove();
			jQuery('#headshot_preview').html('<img src="' + imageURL + '" style="max-width: 100%; max-height: 100%;" />');

			jQuery('#submit_options_form').trigger('click');
			jQuery('#upload_success').text('Here is a preview of the profile picture you chose.');
		};

		jQuery('#headshot_remove').on('click', function(){
			jQuery('#headshot_url').val('');
			jQuery('#headshot_preview').remove();
			jQuery('#upload_success').text('Picture removed.');
		});

		jQuery('#avatar_button').on('click', function(){
			tb_show('Uploading a new avatar!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
			return false;
		});

		jQuery('#avatar_remove').on('click', function(){
			jQuery('#avatar_url').val('');
			jQuery('#avatar_preview').remove();
			jQuery('#upload_success').text('Picture removed.');
		});
	}
};