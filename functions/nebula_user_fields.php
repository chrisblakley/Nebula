<?php

/**********
 WARNING: Custom user meta fields can not have hyphens in them! Use underscores or all one word!
 *********/

add_action('admin_enqueue_scripts', 'enqueue_nebula_user_meta');
function enqueue_nebula_user_meta(){
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('easy-author-image-uploader');
}

//Additional Contact Info fields
add_filter('user_contactmethods', 'nebula_user_contactmethods');
function nebula_user_contactmethods($contactmethods){
    unset($contactmethods['yim']);
    unset($contactmethods['aim']);
    unset($contactmethods['jabber']);
    $contactmethods['facebook'] = 'Facebook Username';
    $contactmethods['twitter'] = 'Twitter Username <small>(Without @)</small>';
    $contactmethods['googleplus'] = 'Google+ Username <small>(Without +)</small>';
    $contactmethods['linkedin'] = 'LinkedIn ID';
    $contactmethods['youtube'] = 'YouTube Channel ID';
    $contactmethods['instagram'] = 'Instagram Username';
    return $contactmethods;
}


add_action('admin_init', 'easy_author_image_init');
function easy_author_image_init(){
	global $pagenow;
	if ( $pagenow == 'media-upload.php' || $pagenow == 'async-upload.php' ){
		add_filter('gettext', 'q_replace_thickbox_button_text', 1, 3); //Replace the button text for the uploader
	}
}
function q_replace_thickbox_button_text($translated_text, $text, $domain){
	if ( $text == 'Insert into Post' ){
		$referer = strpos(wp_get_referer(), 'profile');
		if ( $referer != '' ){
			return 'Choose this photo.';
		}
	}
	return $translated_text;
}

//Show the fields in the user admin page
if ( !user_can($current_user, 'subscriber') && !user_can($current_user, 'contributor') ){
	add_action('show_user_profile', 'extra_profile_fields');
	add_action('edit_user_profile', 'extra_profile_fields');
}
function extra_profile_fields($user){ ?>
	<h3>Additional Information</h3>
	<table class="form-table">
		<tr class="headshot_button_con">
			<th>
				<label for="headshot_button"><span class="description">Headshot</span></label>
			</th>
			<?php $buttontext = ( get_the_author_meta('headshot_url', $user->ID) )? 'Change headshot' : 'Upload new headshot'; ?>
			<td>
				<input id="headshot_button" type="button" class="button" value="<?php echo $buttontext; ?>" />
				<?php if ( get_the_author_meta('headshot_url', $user->ID) ): ?>
					<input id="headshot_remove" type="button" class="button" value="Remove headshot" />
				<?php endif; ?>
				<br /><span class="description">Please select "Full Size" when choosing the headshot.</span>
			</td>
		</tr>
		<tr>
			<th>
				<label for="headshot_preview"><span class="description">Preview</span></label>
			</th>
			<td>
				<?php if ( get_the_author_meta('headshot_url', $user->ID) ): ?>
					<div id="headshot_preview" style="min-height: 100px; max-width: 150px;">
						<img style="max-width:100%; border-radius: 100px; border: 5px solid #fff; box-shadow: 0px 0px 8px 0 rgba(0,0,0,0.2);" src="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" />
					</div>
				<?php else: ?>
					<div id="headshot_preview" style="height: 100px; width: 100px; line-height: 100px; border: 2px solid #CCC; text-align: center; font-size: 5em;">?</div>
				<?php endif; ?>
				<span id="upload_success" style="display:block;"></span>

				<input type="hidden" name="headshot_url" id="headshot_url" value="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="jobtitle">Job Title</label></th>
			<td>
				<input type="text" name="jobtitle" id="jobtitle" value="<?php echo esc_attr(get_the_author_meta('jobtitle', $user->ID)); ?>" class="regular-text" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
		<tr>
			<th><label for="jobcompany">Company</label></th>
			<td>
				<input type="text" name="jobcompany" id="jobcompany" value="<?php echo esc_attr(get_the_author_meta('jobcompany', $user->ID)); ?>" class="regular-text" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
		<tr>
			<th><label for="jobcompanywebsite">Company Website</label></th>
			<td>
				<input type="url" name="jobcompanywebsite" id="jobcompanywebsite" value="<?php echo esc_attr(get_the_author_meta('jobcompanywebsite', $user->ID)); ?>" class="regular-text" placeholder="http://" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
		<tr>
			<th><label for="userlocation">Location</label></th>
			<td>
				<input type="text" name="userlocation" id="userlocation" value="<?php echo esc_attr(get_the_author_meta('userlocation', $user->ID)); ?>" class="regular-text" placeholder="City, State" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
		<tr>
			<th><label for="phoneextension">Phone Number</label></th>
			<td>
				<input type="text" name="phonenumber" id="phonenumber" value="<?php echo esc_attr(get_the_author_meta('phonenumber', $user->ID)); ?>" class="regular-text" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
	</table>
<?php }

//Save the field values to the DB
add_action('personal_options_update', 'save_extra_profile_fields');
add_action('edit_user_profile_update', 'save_extra_profile_fields');
function save_extra_profile_fields($user_id){
	if ( !current_user_can('edit_user', $user_id) ){
		return false;
	}
	update_user_meta($user_id, 'headshot', $_POST['headshot']);
	update_user_meta($user_id, 'headshot_url', $_POST['headshot_url']);
	update_user_meta($user_id, 'jobtitle', $_POST['jobtitle']);
	update_user_meta($user_id, 'jobcompany', $_POST['jobcompany']);
	update_user_meta($user_id, 'jobcompanywebsite', $_POST['jobcompanywebsite']);
	update_user_meta($user_id, 'userlocation', $_POST['userlocation']);
	update_user_meta($user_id, 'phonenumber', $_POST['phonenumber']);
}

function nebula_facebook_link(){
	echo '<p class="facebook-connect-con"><i class="fa fa-facebook-square"></i> <a class="facebook-connect" href="#">Connect with Facebook</a></p>';
}