<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Logs') ){
	trait Logs {
		public function hooks(){
			if ( $this->get_option('administrative_log') ){ //This log option is to log administrative changes as a table in Nebula Options
				add_action('init', array($this, 'register_table_names')); //This must happen on all pages so logs can be added or retrieved

				if ( $this->is_staff() ){
					add_action('admin_init', array($this, 'create_tables') );
				}

				if ( is_user_logged_in() ){
					add_action('wp_ajax_add_log', array($this, 'add_log_via_ajax'));
					add_action('wp_ajax_remove_log', array($this, 'remove_log_via_ajax'));
					add_action('wp_ajax_clean_logs', array($this, 'clean_logs_via_ajax'));
				}

				//Additional events to log
				if ( $this->get_option('administrative_log') ){ //Ignore these hooks if logging is not enabled
					add_filter('auto_update_core', array($this, 'log_auto_core_update'), 10, 2);
				}
			}

			if ( $this->get_option('js_error_log') ){ //This log option is to capture JavaScript errors in a log file on the server
				add_action('wp_ajax_nebula_js_error_log', array($this, 'js_error_log'));
				add_action('wp_ajax_nopriv_nebula_js_error_log', array($this, 'js_error_log'));
			}
		}

		//Log JavaScript errors to a file
		public function js_error_log(){
			$post = $this->super->post; //Get the $_POST data

			if ( !empty($post['message']) ){ //If we have data
				if ( !wp_verify_nonce($post['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

				$js_log_file = get_stylesheet_directory() . '/js_error.log';

				//Check the file size to be safe
				if ( filesize($js_log_file) >= MB_IN_BYTES*100 ){ //100mb limit
					unlink($js_log_file); //Delete the file to start a new one. This is to be overly cautious.
				}

				$error_message = ( isset($post['message']) )? sanitize_text_field($post['message']) : false; //Sanitize the error message text

				if ( !empty($error_message) ){
					$this->debug_log($error_message, $js_log_file);
				}
			}

			exit;
		}

		//Log a message to a file
		//Note: This will create a new file if it does not exist, but does not create new directories!
		public function debug_log($message='', $filepath=false){
			if ( empty($filepath) ){
				$filepath = get_template_directory() . '/nebula.log';
				if ( is_child_theme() ){
					$filepath = get_stylesheet_directory() . '/nebula.log'; //Use the child theme directory if using a child theme
				}
			}

			//If the message is not a string, encode it as JSON
			if ( !is_string($message) ){
				$message = wp_json_encode($message);
			}

			$message = '[' . date('l, F j, Y - g:i:sa') . '] ' . $message . ' (on ' . $this->requested_url() . ')' . PHP_EOL; //Add timestamp, URL, and newline

			file_put_contents($filepath, $message, FILE_APPEND); //Create the log file if needed and append to it
			do_action('qm/debug', $message); //Log it in Query Monitor as well
		}

		//Register table name in $wpdb global
		public function register_table_names(){
			if ( $this->get_option('administrative_log') ){
				global $wpdb;

				if ( !isset($wpdb->nebula_logs) ){
					$wpdb->nebula_logs = $wpdb->prefix . 'nebula_logs';
				}
			}
		}

		//Create Nebula logs table
		public function create_tables(){
			if ( !$this->is_admin_page() && !isset($this->super->get['settings-updated']) && !$this->is_staff() ){ //Only trigger this in admin when Nebula Options are saved (by a staff member)
				return;
			}

			$logs_table_transient = nebula()->transient('nebula_logs_table_exists', function(){
				global $wpdb;

				$logs_table = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->nebula_logs . "'"); //DB Query here

				if ( empty($logs_table) ){
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //Why is this needed?

					$create_logs_table_sql = "CREATE TABLE " . $wpdb->nebula_logs . " (
						id INT(11) NOT NULL AUTO_INCREMENT,
						timestamp TINYTEXT NOT NULL,
						message LONGTEXT,
						user_id INT(11) NOT NULL,
						importance INT(6),
						PRIMARY KEY (id)
						) ENGINE = InnoDB;"; //DB Query here
					dbDelta($create_logs_table_sql);
				}

				return true;
			}); //No expiration for this transient
		}

		//Insert log into DB
		public function add_log($message='', $importance=0, $optimize=true){
			if ( $this->get_option('administrative_log') && is_user_logged_in() && !empty($message) ){
				global $wpdb;

				try {
					$log_insertion = $wpdb->insert($wpdb->nebula_logs, array(
						'timestamp' => sanitize_text_field(date('U')),
						'message' => sanitize_text_field($message),
						'user_id' => intval(get_current_user_id()), //Note: returns 0 in cron jobs
						'importance' => intval($importance)
					)); //DB Query
				} catch(Exception $error){
					//This could happen if the option was enabled (somehow) by non-staff, and a log was attempted to be added
					$this->update_option('administrative_log', 0); //Disable the option just to be safe. Unfortunately this cannot be logged somewhere...
					do_action('qm/error', $error);
					return false;
				}

				delete_transient('nebula_logs');
				do_action('qm/info', 'Added Nebula Log: ' . sanitize_text_field($message));

/*
				if ( !empty($optimize) ){
					//$this->optimize_logs(); //@todo "nebula" 0: Need to test this before enabling!
				}
*/

				if ( $this->is_debug(false) || WP_DEBUG || WP_DEBUG_LOG ){
					$this->debug_log($message . ' [User: ' . get_userdata(intval(get_current_user_id()))->display_name . ']'); //Log the message to a file too when debug mode is active
				}

				return is_int($log_insertion); //Boolean return
			}

			return false;
		}

		//Insert log via admin interface (AJAX)
		public function add_log_via_ajax(){
			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('{response:"Permission Denied. Refresh and try again."}'); }

			$message = sanitize_text_field($this->super->post['message']); //Sanitize message string
			$importance = intval($this->super->post['importance']); //Sanitize importance integer @todo "Nebula" 0: nullish coalescing operator here (set to 4)

			$this->add_log($message, $importance);
			exit('{response:success}');
		}

		//Remove log from DB
		public function remove_log($id){
			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				global $wpdb;

				$wpdb->delete($wpdb->nebula_logs, array('id' => intval($id))); //DB Query

				delete_transient('nebula_logs');
			}

			return false;
		}

		//Remove log via admin interface (AJAX)
		public function remove_log_via_ajax(){
			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('{response:"Permission Denied. Refresh and try again."}'); }

			$log_id = intval($this->super->post['id']); //Sanitize ID
			$this->remove_log($log_id);
			exit('{response:success}');
		}

		//Remove all low importance logs from DB (by default this removes any log messages with importance of 4 or below)
		public function clean_logs($importance=4){
			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				global $wpdb;

				$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE importance <= %d", $importance)); //DB Query

				delete_transient('nebula_logs');
			}

			return false;
		}

		//Remove all low importance logs from DB via admin interface (AJAX)
		public function clean_logs_via_ajax(){
			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('{response:"Permission Denied. Refresh and try again."}'); }

			$importance = intval($this->super->post['importance']); //Sanitize importance
			$this->clean_logs($importance);
			exit('{response:success}');
		}

		//Remove low importance logs before a date
		public function optimize_logs(){
			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				$row_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->nebula_logs); //DB Query - Count the rows in the table

				if ( $row_count >= 800 ){
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE id IN (SELECT id FROM " . $wpdb->nebula_logs . " ORDER BY timestamp ASC LIMIT 800) AND importance <= %d"), 4); //DB Query - Delete the oldest 800 logs with importance of 4 or lower

					$row_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->nebula_logs); //DB Query - Count the rows in the table
					if ( $row_count >= 500 ){
						$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE id IN (SELECT id FROM " . $wpdb->nebula_logs . " ORDER BY timestamp ASC LIMIT 400) AND importance <= %d"), 5); //DB Query - Delete the oldest 400 logs with importance of 5 or lower

						$row_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->nebula_logs); //DB Query - Count the rows in the table
						if ( $row_count >= 400 ){
							$this->add_log('Nebula Logs were automatically disabled due to an unexpectedly large database table.', 10, false); //Add a log without recursively attempting to optimize
							$this->update_option('administrative_log', 0); //Disable Nebula Logs automatically to prevent an unexpectedly large database table
						}
					}
				}

				delete_transient('nebula_logs');
			}
		}

		//Get all logs data (or just the column names)
		public function get_logs($rows=true){
			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				//Only return column names if requested
				if ( empty($rows) ){
					global $wpdb;
					$nebula_logs_data_head = $wpdb->get_results("SHOW columns FROM $wpdb->nebula_logs"); //Get the column names from the primary table
					return (array) $nebula_logs_data_head; //Convert to an array and return
				}

				//Otherwise get the actual logs data (rows)
				$nebula_logs_data = nebula()->transient('nebula_logs', function(){
					global $wpdb; //Need to re-declare so it is available within this function
					return $wpdb->get_results("SELECT * FROM $wpdb->nebula_logs ORDER BY timestamp DESC LIMIT 100"); //Get all data (last 100 logs) from the DB table in descending order (latest first)
				}, HOUR_IN_SECONDS);

				return (array) $nebula_logs_data; //Convert to an array and return
			}

			return false;
		}

		//Attempt to log automatic WordPress core updates
		public function log_auto_core_update($update, $item){
			nebula()->add_log('Automatic WP Core update', 4); //I don't know if this will properly log because logs require a logged-in user, but this may happen at any time...
			return $update;
		}
	}
}