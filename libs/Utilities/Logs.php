<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Logs') ){
	trait Logs {
		public function hooks(){
			if ( $this->is_staff() && $this->get_option('logs') ){
				add_action('init', array($this, 'register_table_names')); //This must happen on all pages so logs can be added or retreived
				add_action('admin_init', array($this, 'create_tables') );
				add_action('wp_ajax_add_log', array($this, 'add_log_via_ajax'));
				add_action('wp_ajax_remove_log', array($this, 'remove_log_via_ajax'));
				add_action('wp_ajax_clean_logs', array($this, 'clean_logs_via_ajax'));
			}
		}

		//Register table name in $wpdb global
		public function register_table_names(){
			if ( $this->get_option('logs') && $this->is_staff() ){
				global $wpdb;

				if ( !isset($wpdb->nebula_logs) ){
					$wpdb->nebula_logs = $wpdb->prefix . 'nebula_logs';
				}
			}
		}

		//Create Nebula logs table
		public function create_tables(){
			if ( !$this->is_admin_page() && !isset($_GET['settings-updated']) && !$this->is_staff() ){ //Only trigger this in admin when Nebula Options are saved (by a staff member)
				return;
			}

			global $wpdb;

			$logs_table = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->nebula_logs . "'"); //DB Query here

			//@todo "Nebula" 0: could store this in a "permanent" transient. this only ever needs to happen once

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
		}

		//Insert log into DB
		public function add_log($message='', $importance=0, $optimize=true){
			if ( $this->get_option('logs') && $this->is_staff() && !empty($message) ){
				global $wpdb;

				$wpdb->insert($wpdb->nebula_logs, array(
					'timestamp' => sanitize_text_field(date('U')),
					'message' => sanitize_text_field($message),
					'user_id' => intval(get_current_user_id()), //Note: returns 0 in cron jobs
					'importance' => intval($importance)
				)); //DB Query

				delete_transient('nebula_logs');

/*
				if ( !empty($optimize) ){
					//$this->optimize_logs(); //@todo "nebula" 0: Need to test this before enabling!
				}
*/
			}

			return false;
		}

		//Insert log via admin interface (AJAX)
		public function add_log_via_ajax(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ wp_die('Permission Denied. Refresh and try again.'); }

			$message = sanitize_text_field($_POST['data'][0]['message']); //Sanitize message string
			$importance = intval($_POST['data'][0]['importance']); //Sanitize importance integer @todo "Nebula" 0: nullish coalescing operator here (set to 4)

			$this->add_log($message, $importance);
			exit;
		}

		//Remove log from DB
		public function remove_log($id){
			if ( $this->get_option('logs') && $this->is_staff() ){
				global $wpdb;

				$wpdb->delete($wpdb->nebula_logs, array('id' => intval($id))); //DB Query

				delete_transient('nebula_logs');
			}

			return false;
		}

		//Remove log via admin interface (AJAX)
		public function remove_log_via_ajax(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ wp_die('Permission Denied. Refresh and try again.'); }

			$log_id = intval($_POST['data'][0]['id']); //Sanitize ID
			$this->remove_log($log_id);
			exit;
		}

		//Remove all low importance logs from DB (by default this removes any log messages with importance of 4 or below)
		public function clean_logs($importance=4){
			if ( $this->get_option('logs') && $this->is_staff() ){
				global $wpdb;

				$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE importance <= %d", $importance)); //DB Query

				delete_transient('nebula_logs');
			}

			return false;
		}

		//Remove all low importance logs from DB via admin interface (AJAX)
		public function clean_logs_via_ajax(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ wp_die('Permission Denied. Refresh and try again.'); }

			$importance = intval($_POST['data'][0]['importance']); //Sanitize importance
			$this->clean_logs($importance);
			exit;
		}

		//Remove low importance logs before a date
		public function optimize_logs(){
			if ( $this->get_option('logs') && $this->is_staff() ){
				$row_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->nebula_logs); //DB Query - Count the rows in the table

				if ( $row_count >= 800 ){
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE id IN (SELECT id FROM " . $wpdb->nebula_logs . " ORDER BY timestamp ASC LIMIT 800) AND importance <= %d"), 4); //DB Query - Delete the oldest 800 logs with importance of 4 or lower

					$row_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->nebula_logs); //DB Query - Count the rows in the table
					if ( $row_count >= 500 ){
						$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE id IN (SELECT id FROM " . $wpdb->nebula_logs . " ORDER BY timestamp ASC LIMIT 400) AND importance <= %d"), 5); //DB Query - Delete the oldest 400 logs with importance of 5 or lower

						$row_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->nebula_logs); //DB Query - Count the rows in the table
						if ( $row_count >= 400 ){
							$this->add_log('Nebula Logs were automatically disabled due to an unexpectedly large database table.', 10, false); //Add a log without recursively attempting to optimize
							$this->update_option('logs', 0); //Disable Nebula Logs automatically to prevent an unexpectedly large database table
						}
					}
				}

				delete_transient('nebula_logs');
			}
		}

		//Get all logs data (or just the column names)
		public function get_logs($rows=true){
			if ( $this->get_option('logs') && $this->is_staff() ){
				global $wpdb;

				//Only return column names if requested
				if ( empty($rows) ){
					$nebula_logs_data_head = $wpdb->get_results("SHOW columns FROM $wpdb->nebula_logs"); //Get the column names from the primary table
					return (array) $nebula_logs_data_head; //Convert to an array and return
				}

				//Otherwise get the actual logs data (rows)
				$nebula_logs_data = get_transient('nebula_logs');
				if ( empty($nebula_logs_data) || $this->is_debug() ){
					$nebula_logs_data = $wpdb->get_results("SELECT * FROM $wpdb->nebula_logs ORDER BY timestamp DESC LIMIT 100"); //Get all data (last 100 logs) from the DB table in descending order (latest first)
					set_transient('nebula_logs', $nebula_logs_data, HOUR_IN_SECONDS);
				}
				return (array) $nebula_logs_data; //Convert to an array and return
			}

			return false;
		}
	}
}