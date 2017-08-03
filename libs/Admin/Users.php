<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Users') ){
	trait Users {
		public function hooks(){
			add_action('init', array($this, 'users_status_init'));
			add_action('admin_init', array($this, 'users_status_init'));
			add_filter('manage_users_columns', array($this, 'user_columns_head'));
			add_action('manage_users_custom_column', array($this, 'user_columns_content' ), 15, 3);
			add_filter('user_contactmethods', array($this, 'user_contact_methods'));
			add_action('admin_init', array($this, 'easy_author_image_init'));

			if ( !current_user_can( 'subscriber' ) && !current_user_can( 'contributor' ) ){
				add_action('show_user_profile', array($this, 'extra_profile_fields'));
				add_action('edit_user_profile', array($this, 'extra_profile_fields'));
			}

			add_action('personal_options_update', array($this, 'save_extra_profile_fields'));
			add_action('edit_user_profile_update', array($this, 'save_extra_profile_fields'));
		}

		//Update user online status
		public function users_status_init(){
			if ( is_user_logged_in() ){
				$logged_in_users = $this->get_data('users_status');

				$unique_id = $_SERVER['REMOTE_ADDR'] . '.' . preg_replace("/[^a-zA-Z0-9\.]+/", "", $_SERVER['HTTP_USER_AGENT']);
				$current_user = wp_get_current_user();

				//@TODO "Nebula" 0: Technically, this should be sorted by user ID -then- unique id -then- the rest of the info. Currently, concurrent logins won't reset until they have ALL expired. This could be good enough, though.

				if ( !isset($logged_in_users[$current_user->ID]['last']) || $logged_in_users[$current_user->ID]['last'] < time()-600 ){ //If a last login time does not exist for this user -or- if the time exists but is greater than 10 minutes, update.
					$logged_in_users[$current_user->ID] = array(
						'id' => $current_user->ID,
						'username' => $current_user->user_login,
						'last' => time(),
						'ip' => $_SERVER['REMOTE_ADDR'],
						'unique' => array($unique_id),
					);
					$this->update_data('users_status', $logged_in_users);
				} else {
					if ( !in_array($unique_id, $logged_in_users[$current_user->ID]['unique']) ){
						array_push($logged_in_users[$current_user->ID]['unique'], $unique_id);
						$this->update_data('users_status', $logged_in_users);
					}
				}
			}
		}

		//Add columns to user listings
		public function user_columns_head($defaults){
			$defaults['company'] = 'Company';
			$defaults['registered'] = 'Registered';
			$defaults['status'] = 'Status';
			$defaults['ip'] = 'Last IP';
			$defaults['id'] = 'ID';
			return $defaults;
		}

		//Custom columns content to user listings
		public function user_columns_content($value='', $column_name, $id){
			if ( $column_name === 'company' ){
				return get_the_author_meta('jobcompany', $id);
			}

			if ( $column_name === 'registered' ){
				$user_data = get_userdata($id);
				return date('F j, Y', strtotime($user_data->user_registered));
			}

			if ( $column_name === 'status' ){
				if ( $this->is_user_online($id) ){
					$online_now = '<i class="fa fa-caret-right" style="color: green;"></i> <strong>Online Now</strong>';
					if ( $this->user_single_concurrent($id) > 1 ){
						$online_now .= '<br/><small>(<strong>' . $this->user_single_concurrent($id) . '</strong> locations)</small>';
					}
					return $online_now;
				} else {
					return ( $this->user_last_online($id) )? '<small>Last Seen: <br /><em>' . date('M j, Y @ g:ia', $this->user_last_online($id)) . '</em></small>' : '';
				}
			}

			if ( $column_name === 'ip' ){
				$logged_in_users = $this->get_data('users_status');
				$last_ip = $logged_in_users[$id]['ip'];

				$notable_poi = $this->poi($last_ip);
				if ( !empty($notable_poi) ){
					$last_ip .= '<br><small>(' . $notable_poi . ')</small>';
				}

				return $last_ip;
			}

			if ( $column_name === 'id' ){
				return $id;
			}
		}

		//Check if a user has been online in the last 10 minutes
		public function is_user_online($id){
			$override = apply_filters('pre_nebula_is_user_online', null, $id);
			if ( isset($override) ){return;}

			$logged_in_users = $this->get_data('users_status');
			return isset($logged_in_users[$id]['last']) && $logged_in_users[$id]['last'] > time()-600; //10 Minutes
		}

		//Check when a user was last online.
		public function user_last_online($id){
			$override = apply_filters('pre_nebula_user_last_online', null, $id);
			if ( isset($override) ){return;}

			$logged_in_users = $this->get_data('users_status');
			if ( isset($logged_in_users[$id]['last']) ){
				return $logged_in_users[$id]['last'];
			}
			return false;
		}

		//Get a count of online users, or an array of online user IDs.
		public function online_users($return='count'){
			$override = apply_filters('pre_nebula_online_users', null, $return);
			if ( isset($override) ){return;}

			$logged_in_users = $this->get_data('users_status');
			if ( empty($logged_in_users) || !is_array($logged_in_users) ){
				return ( strtolower($return) === 'count' )? 0 : false; //If this happens it indicates an error.
			}

			$user_online_count = 0;
			$online_users = array();
			foreach ( $logged_in_users as $user ){
				if ( !empty($user['username']) && isset($user['last']) && $user['last'] > time()-600 ){
					$online_users[] = $user;
					$user_online_count++;
				}
			}

			return ( strtolower($return) === 'count' )? $user_online_count : $online_users;
		}

		//Check how many locations a single user is logged in from.
		public function user_single_concurrent($id){
			$override = apply_filters('pre_nebula_user_single_concurrent', null, $id);
			if ( isset($override) ){return;}

			$logged_in_users = $this->get_data('users_status');
			if ( isset($logged_in_users[$id]['unique']) ){
				return count($logged_in_users[$id]['unique']);
			}
			return 0;
		}

		//Additional Contact Info fields
		public function user_contact_methods( $contact_methods ){
			$override = apply_filters('pre_nebula_user_contact_methods', null, $user); //@TODO "Nebula" 0: Revise this $user
			if ( isset($override) ){return;}

			unset($contact_methods['yim']);
			unset($contact_methods['aim']);
			unset($contact_methods['jabber']);
			$contact_methods['facebook'] = 'Facebook Username';
			$contact_methods['twitter'] = 'Twitter Username <small>(Without @)</small>';
			$contact_methods['linkedin'] = 'LinkedIn ID';
			$contact_methods['youtube'] = 'YouTube Channel ID';
			$contact_methods['instagram'] = 'Instagram Username';
			$contact_methods['pinterest'] = 'Pinterest Username';
			$contact_methods['googleplus'] = 'Google+ Username <small>(Without +)</small>';
			return $contact_methods;
		}

		//Custom User headshot
		public function easy_author_image_init(){
			global $pagenow;
			if ( $pagenow === 'media-upload.php' || $pagenow === 'async-upload.php' ){
				add_filter('gettext', array($this, 'q_replace_thickbox_button_text' ), 1, 3); //Replace the button text for the uploader
			}
		}

		public function q_replace_thickbox_button_text($translated_text, $text, $domain){
			if ( $text === 'Insert into Post' ){
				if ( strpos(wp_get_referer(), 'profile') != '' ){
					return 'Choose this photo.';
				}
			}
			return $translated_text;
		}

		//Show the fields in the user admin page
		public function extra_profile_fields($user){
			do_action('nebula_extra_profile_fields', $user);
			if ( isset($override) ){return;}
			?>
			<h3>Additional Information</h3>
			<table class="form-table">
				<tr class="headshot_button_con">
					<th>
						<label for="headshot_button"><span class="description">Headshot</span></label>
					</th>
					<td>
						<input id="headshot_button" type="button" class="button" value="<?php echo ( get_user_meta($user->ID, 'headshot_url', true) )? 'Change headshot' : 'Upload new headshot';; ?>" />
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
								<img style="max-width:100%; border-radius: 100px; border: 5px solid #fff; box-shadow: 0 0 8px 0 rgba(0,0,0,0.2);" src="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" />
							</div>
						<?php else: ?>
							<div id="headshot_preview" style="height: 100px; width: 100px; line-height: 100px; border: 2px solid #ccc; text-align: center; font-size: 5em;">?</div>
						<?php endif; ?>
						<span id="upload_success" style="display:block;"></span>

						<input id="headshot_url" class="regular-text" type="hidden" name="headshot_url" value="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" />
					</td>
				</tr>
				<tr>
					<th><label for="jobtitle">Job Title</label></th>
					<td>
						<input id="jobtitle" class="regular-text" type="text" name="jobtitle" value="<?php echo esc_attr(get_the_author_meta('jobtitle', $user->ID)); ?>" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="jobcompany">Company</label></th>
					<td>
						<input id="jobcompany" class="regular-text" type="text" name="jobcompany" value="<?php echo esc_attr(get_the_author_meta('jobcompany', $user->ID)); ?>" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="jobcompanywebsite">Company Website</label></th>
					<td>
						<input id="jobcompanywebsite" class="regular-text" type="url" name="jobcompanywebsite" value="<?php echo esc_attr(get_the_author_meta('jobcompanywebsite', $user->ID)); ?>" placeholder="http://" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="usercity">City</label></th>
					<td>
						<input id="usercity" class="regular-text" type="text" name="usercity" value="<?php echo esc_attr(get_the_author_meta('usercity', $user->ID)); ?>" placeholder="City" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="userstate">State</label></th>
					<td>
						<input id="userstate" class="regular-text" type="text" name="userstate" value="<?php echo esc_attr(get_the_author_meta('userstate', $user->ID)); ?>" placeholder="State" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="phoneextension">Phone Number</label></th>
					<td>
						<input id="phonenumber" class="regular-text" type="text" name="phonenumber" value="<?php echo esc_attr(get_the_author_meta('phonenumber', $user->ID)); ?>" /><br />
					</td>
				</tr>
			</table>
			<?php
		}

		//Save the field values to the DB
		public function save_extra_profile_fields($user_id){
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

			//If editing own user, update NVDB
			if ( $this->get_option('visitors_db') && $user_id === get_current_user_id() ){
				$this->update_visitor(array(
					'job_title' => sanitize_text_field($_POST['jobtitle']),
					'company' => sanitize_text_field($_POST['jobcompany']),
					'company_website' => sanitize_text_field($_POST['jobcompanywebsite']),
					'city' => sanitize_text_field($_POST['usercity']),
					'state_name' => sanitize_text_field($_POST['userstate']),
					'phone_number' => sanitize_text_field($_POST['phonenumber']),
				));
			}
		}
	}
}