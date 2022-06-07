<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Users') ){
	trait Users {
		public function hooks(){
			add_action('init', array($this, 'users_status_init')); //This happens on all pages (front-end and admin)

			//Exclude AJAX and REST requests
			if ( !$this->is_background_request() ){
				add_action('user_register', array($this, 'log_new_user'), 10, 1);
				add_action('delete_user', array($this, 'log_delete_user'), 10, 3);
				add_action('password_reset', array($this, 'log_password_reset'), 10, 1);
				add_filter('email_change_email', array($this, 'log_admin_email_change'), 10, 3);

				add_filter('manage_users_columns', array($this, 'user_columns_head'));
				add_filter('manage_users_sortable_columns', array($this, 'user_columns_sortable'));
				add_action('manage_users_custom_column', array($this, 'user_columns_content' ), 15, 3);
				add_action('pre_get_users', array($this, 'user_columns_orderby')); //Handles the order when a user column is sorted

				add_filter('user_contactmethods', array($this, 'user_contact_methods'));

				if ( current_user_can('publish_posts') ){ //If the user is not a subscriber or contributor role (Ex: only authors and above)
					add_action('show_user_profile', array($this, 'extra_profile_fields'));
					add_action('edit_user_profile', array($this, 'extra_profile_fields'));
				}

				add_action('personal_options_update', array($this, 'save_extra_profile_fields'));
				add_action('edit_user_profile_update', array($this, 'save_extra_profile_fields'));

				if ( is_plugin_active('wordpress-seo/wp-seo.php') ){ //If Yoast is active
					add_action('wpseo_register_roles', array($this, 'remove_yoast_roles'), 50); //Yoast hook (run after Yoast does)
				}
			}
		}

		//Log when administrators are created
		public function log_new_user($user_id){
			if ( user_can($user_id, 'manage_options') ){
				$this->add_log('New admin user registered (' . get_user_by('id', $user_id)->user_login . ')', 6);
			}
		}

		//Log when administrators are deleted
		public function log_delete_user($user_id, $reassign, $user){
			if ( user_can($user_id, 'manage_options') ){
				$this->add_log('Admin user deleted (' . get_user_by('id', $user_id)->user_login . ')', 8);
			}
		}

		//Log when administrator passwords are reset
		public function log_password_reset($user){
			if ( user_can($user->ID, 'manage_options') ){
				nebula()->add_log('Admin password reset (' . $user->user_login . ')', 6);
			}
		}

		//Log when administrator email addresses are changed
		public function log_admin_email_change($email_change_email, $user, $userdata){
			if ( user_can($user['ID'], 'manage_options') ){
				nebula()->add_log('Admin email changed (' . $user['user_login'] . ')', 6);
			}

			return $email_change_email;
		}

		//Update user online status
		public function users_status_init(){
			if ( is_user_logged_in() ){
				$logged_in_users = $this->get_data('users_status');

				$unique_id = $this->get_ip_address() . '.' . preg_replace("/[^a-zA-Z0-9\.]+/", "", $this->super->server['HTTP_USER_AGENT']);
				$current_user = wp_get_current_user();

				//Technically, this should be sorted by user ID -then- unique id -then- the rest of the info. Currently, concurrent logins won't reset until they have ALL expired.
				if ( !isset($logged_in_users[$current_user->ID]['last']) || $logged_in_users[$current_user->ID]['last'] < time()-600 ){ //If a last login time does not exist for this user -or- if the time exists but is greater than 10 minutes, update.
					$logged_in_users[$current_user->ID] = array(
						'id' => $current_user->ID,
						'username' => $current_user->user_login,
						'last' => time(),
						'ip' => $this->get_ip_address(),
						'unique' => array($unique_id),
					);
					$this->update_data('users_status', $logged_in_users);
				} else {
					if ( !in_array($unique_id, $logged_in_users[$current_user->ID]['unique']) ){
						array_push($logged_in_users[$current_user->ID]['unique'], $unique_id);
						$this->update_data('users_status', $logged_in_users);
					}
				}

				update_user_meta($current_user->ID, 'gacid', sanitize_text_field($this->ga_parse_cookie())); //Add last known GA Client ID to user
			}
		}

		//Add columns to user listings
		public function user_columns_head($columns){
			$columns['company'] = 'Company';
			$columns['registered'] = 'Registered';
			$columns['status'] = 'Last Seen';
			$columns['id'] = 'ID';

			if ( $this->get_option('ga_tracking_id') || $this->get_option('gtm_id') ){
				$columns['gacid'] = 'GA Client ID'; //This is the last GA CID. It could be different if the user logs in on different computers/browsers.
			}

			return $columns;
		}

		public function user_columns_sortable($columns){
			$columns['company'] = 'company';
			$columns['registered'] = 'registered';
			//$columns['status'] = 'last seen';
			$columns['id'] = 'id';
			return $columns;
		}

		//Custom columns content to user listings
		public function user_columns_content($value, $column_name, $id){
			if ( $column_name === 'company' ){
				return get_the_author_meta('jobcompany', $id);
			}

			if ( $column_name === 'registered' ){
				$user_data = get_userdata($id);
				return date('F j, Y', strtotime($user_data->user_registered));
			}

			if ( $column_name === 'status' ){
				if ( $this->is_user_online($id) ){
					$online_now = '<i class="fa-solid fa-caret-right" style="color: #58c026;"></i> <strong>Online Now</strong>';
					if ( $this->user_single_concurrent($id) > 1 ){
						$online_now .= '<br /><small>(<strong>' . $this->user_single_concurrent($id) . '</strong> locations)</small>';
					}
					return $online_now;
				} else {
					$today_icon = ( date('Y-m-d', $this->user_last_online($id)) == date('Y-m-d') )? '<i class="fa-regular fa-clock" title="Online today"></i> ' : '<i class="fa-regular fa-calendar"></i> ';
					return ( $this->user_last_online($id) )? '<small>' . $today_icon . human_time_diff($this->user_last_online($id)) . ' ago<br /><em>' . date('M j, Y @ g:ia', $this->user_last_online($id)) . '</em></small>' : '';
				}
			}

			if ( $column_name === 'id' ){
				$you = '';
				if ( $id === get_current_user_id() ){
					$you = ' <strong>(You)</strong>';
				}

				return $id . $you;
			}

			if ( $column_name === 'gacid' ){
				return '<small>' . esc_html(get_user_meta($id, 'gacid', true)) . '</small>';
			}

			return $value; //Always return the default value to prevent conflicts with other column data!
		}

		public function user_columns_orderby($query){
			if ( $this->is_admin_page() ){
				$orderby = strtolower($query->get('orderby'));

				if ( $orderby === 'company' ){
					$query->set('orderby', 'company');
				}

				if ( $orderby === 'registered' ){
					$query->set('orderby', 'registered');
				}

				//Ordering by Last Seen would require a quite complex custom query (because this data is not stored on the user)

				if ( $orderby === 'id' ){
					$query->set('orderby', 'id');
				}
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
		public function user_contact_methods($contact_methods){
			unset($contact_methods['yim']);
			unset($contact_methods['aim']);
			unset($contact_methods['jabber']);
			$contact_methods['facebook'] = 'Facebook Username';
			$contact_methods['twitter'] = 'Twitter Username <small>(Without @)</small>';
			$contact_methods['linkedin'] = 'LinkedIn ID';
			$contact_methods['youtube'] = 'YouTube Channel ID';
			$contact_methods['instagram'] = 'Instagram Username';
			$contact_methods['pinterest'] = 'Pinterest Username';

			return $contact_methods;
		}

		//Show the fields in the user admin page
		public function extra_profile_fields($user){
			do_action('nebula_extra_profile_fields', $user);
			if ( isset($override) ){return;}
			?>
			<h3 class="nebula-additional-information">Additional Information</h3>
			<table class="form-table">
				<tr class="nebula-job-title-wrap">
					<th><label for="jobtitle">Job Title</label></th>
					<td>
						<input id="jobtitle" class="regular-text" type="text" name="jobtitle" value="<?php echo esc_attr(get_the_author_meta('jobtitle', $user->ID)); ?>" autocomplete="organization-title" /><br />
					</td>
				</tr>
				<tr class="nebula-company-wrap">
					<th><label for="jobcompany">Company</label></th>
					<td>
						<input id="jobcompany" class="regular-text" type="text" name="jobcompany" value="<?php echo esc_attr(get_the_author_meta('jobcompany', $user->ID)); ?>" autocomplete="organization" /><br />
					</td>
				</tr>
				<tr class="nebula-company-website-wrap">
					<th><label for="jobcompanywebsite">Company Website</label></th>
					<td>
						<input id="jobcompanywebsite" class="regular-text" type="url" name="jobcompanywebsite" value="<?php echo esc_attr(get_the_author_meta('jobcompanywebsite', $user->ID)); ?>" placeholder="http://" autocomplete="url" /><br />
					</td>
				</tr>
				<tr class="nebula-city-wrap">
					<th><label for="usercity">City</label></th>
					<td>
						<input id="usercity" class="regular-text" type="text" name="usercity" value="<?php echo esc_attr(get_the_author_meta('usercity', $user->ID)); ?>" placeholder="City" autocomplete="address-level2" /><br />
					</td>
				</tr>
				<tr class="nebula-state-wrap">
					<th><label for="userstate">State</label></th>
					<td>
						<input id="userstate" class="regular-text" type="text" name="userstate" value="<?php echo esc_attr(get_the_author_meta('userstate', $user->ID)); ?>" placeholder="State" autocomplete="address-level1" /><br />
					</td>
				</tr>
				<tr class="nebula-phone-wrap">
					<th><label for="phoneextension">Phone Number</label></th>
					<td>
						<input id="phonenumber" class="regular-text" type="text" name="phonenumber" value="<?php echo esc_attr(get_the_author_meta('phonenumber', $user->ID)); ?>" autocomplete="postal-code" /><br />
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

			update_user_meta($user_id, 'jobtitle', sanitize_text_field($this->super->post['jobtitle']));
			update_user_meta($user_id, 'jobcompany', sanitize_text_field($this->super->post['jobcompany']));
			update_user_meta($user_id, 'jobcompanywebsite', sanitize_text_field($this->super->post['jobcompanywebsite']));
			update_user_meta($user_id, 'usercity', sanitize_text_field($this->super->post['usercity']));
			update_user_meta($user_id, 'userstate', sanitize_text_field($this->super->post['userstate']));
			update_user_meta($user_id, 'phonenumber', sanitize_text_field($this->super->post['phonenumber']));
		}

		//Remove Yoast SEO user roles
		public function remove_yoast_roles(){
			if ( get_role('wpseo_manager') ){
				remove_role('wpseo_manager'); //Remove Yoast "SEO Manager" role
			}

			if ( get_role('wpseo_editor') ){
				remove_role('wpseo_editor'); //Remove Yoast "SEO Editor" role
			}
		}
	}
}