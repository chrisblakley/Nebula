<?php

/**********
 WARNING: Custom user meta fields can not have hyphens in them! Use underscores or all one word!
 *********/

//Additional Contact Info fields
add_filter('user_contactmethods', 'nebula_user_contactmethods');
function nebula_user_contactmethods($contactmethods){
	$override = apply_filters('pre_nebula_user_contactmethods', false, $user);
	if ( $override !== false ){echo $override; return;}

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

//Custom User headshot
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
	add_action('show_user_profile', 'nebula_extra_profile_fields');
	add_action('edit_user_profile', 'nebula_extra_profile_fields');
}
function nebula_extra_profile_fields($user){
	$override = apply_filters('nebula_extra_profile_fields', false, $user);
	if ( $override !== false ){echo $override; return;}
?>
	<h3>Additional Information</h3>
	<table class="form-table">
		<tr class="headshot_button_con">
			<th>
				<label for="headshot_button"><span class="description">Headshot</span></label>
			</th>
			<?php $buttontext = ( get_user_meta($user->ID, 'headshot_url', true) )? 'Change headshot' : 'Upload new headshot'; ?>
			<td>
				<input id="headshot_button" type="button" class="button" value="<?php echo $buttontext; ?>" />
				<?php if ( get_user_meta($user->ID, 'headshot_url', true) ): ?>
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
				<?php if ( get_user_meta($user->ID, 'headshot_url', true) ): ?>
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
			</td>
		</tr>
		<tr>
			<th><label for="jobcompany">Company</label></th>
			<td>
				<input type="text" name="jobcompany" id="jobcompany" value="<?php echo esc_attr(get_the_author_meta('jobcompany', $user->ID)); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="jobcompanywebsite">Company Website</label></th>
			<td>
				<input type="url" name="jobcompanywebsite" id="jobcompanywebsite" value="<?php echo esc_attr(get_the_author_meta('jobcompanywebsite', $user->ID)); ?>" class="regular-text" placeholder="http://" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="usercity">City</label></th>
			<td>
				<input type="text" name="usercity" id="usercity" value="<?php echo esc_attr(get_the_author_meta('usercity', $user->ID)); ?>" class="regular-text" placeholder="City" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="userstate">State</label></th>
			<td>
				<input type="text" name="userstate" id="userstate" value="<?php echo esc_attr(get_the_author_meta('userstate', $user->ID)); ?>" class="regular-text" placeholder="State" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="phoneextension">Phone Number</label></th>
			<td>
				<input type="text" name="phonenumber" id="phonenumber" value="<?php echo esc_attr(get_the_author_meta('phonenumber', $user->ID)); ?>" class="regular-text" /><br />
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
	update_user_meta($user_id, 'headshot', sanitize_text_field($_POST['headshot']));
	update_user_meta($user_id, 'headshot_url', sanitize_text_field($_POST['headshot_url']));
	update_user_meta($user_id, 'jobtitle', sanitize_text_field($_POST['jobtitle']));
	update_user_meta($user_id, 'jobcompany', sanitize_text_field($_POST['jobcompany']));
	update_user_meta($user_id, 'jobcompanywebsite', sanitize_text_field($_POST['jobcompanywebsite']));
	update_user_meta($user_id, 'usercity', sanitize_text_field($_POST['usercity']));
	update_user_meta($user_id, 'userstate', sanitize_text_field($_POST['userstate']));
	update_user_meta($user_id, 'phonenumber', sanitize_text_field($_POST['phonenumber']));

	if ( $user_id == get_current_user_id() ){
		nebula_update_visitor(array(
			'job_title' => sanitize_text_field($_POST['jobtitle']),
			'company' => sanitize_text_field($_POST['jobcompany']),
			'company_website' => sanitize_text_field($_POST['jobcompanywebsite']),
			'city' => sanitize_text_field($_POST['usercity']),
			'state_name' => sanitize_text_field($_POST['userstate']),
			'phone_number' => sanitize_text_field($_POST['phonenumber']),
		));
	}
}