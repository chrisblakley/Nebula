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
			if ( $this->is_minimal_mode() ){return null;}

			$post = $this->super->post; //Get the $_POST data

			if ( !empty($post['message']) ){ //If we have data
				if ( !wp_verify_nonce($post['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

				$js_log_file = get_stylesheet_directory() . '/js_error.log';

				//Check the file size to be safe
				if ( file_exists($js_log_file) ){
					if ( filesize($js_log_file) >= MB_IN_BYTES*100 ){ //100mb limit
						unlink($js_log_file); //Delete the file to start a new one. This is to be overly cautious.
					}
				}

				$error_message = ( isset($post['message']) )? sanitize_text_field($post['message']) : false; //Sanitize the error message text

				if ( !empty($error_message) ){
					$this->debug_log($error_message, $js_log_file);
				}
			}

			exit;
		}

		//Log a message to a file
		//This shorter function accepts a file *name* instead of a full path and stores it in a /logs/ directory in the child or parent theme
		//Useful for logging certain occurrences by website visitors themselves
		public function log($message='', $filename=false, $verbose=false, $limited=true){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Log', 'start', '[Nebula] Logs');

			if ( empty($filename) || str_contains($filename, '/') ){ //If a filename is not provided or contains a full path
				$this->debug_log($message, $filename, $verbose, $limited); //Treat this as an alias of the debug_log() function
				$this->timer('Log', 'end');
				return null;
			}

			$filepath = get_template_directory() . '/data/logs/';
			if ( is_child_theme() ){
				$filepath = get_stylesheet_directory() . '/data/logs/'; //Use the child theme directory if using a child theme
			}

			//Ensure the /logs/ directory exists in the appropriate theme location
			if ( !file_exists($filepath) ){
				wp_mkdir_p($filepath);
			}

			if ( !file_exists($filepath . 'index.html') ){
				touch($filepath . 'index.html'); //Create an empty index.html file in the /data/logs/ directory to ensure no file listing
			}

			if ( !str_contains($filename, '.') ){ //If a file extension was not provided, add one
				$filename .= '.log';
			}

			$filepath .= $filename;

			$this->debug_log($message, $filepath, $verbose, $limited); //Now call the function that actually writes the log to the file
			$this->timer('Log', 'end');
		}

		//Log a message to either a nebula.log file, or a full file *path*
		//Useful for logging debug messages by developers
		//If you want a shorter method by only providing a message and a file *name*, use nebula()->log()
		//Note: This will create a new file and parent directories if they do not exist
		public function debug_log($message='', $filepath=false, $verbose=false, $limited=true){
			if ( $this->is_minimal_mode() ){return null;}

			if ( empty($filepath) ){
				$filepath = get_template_directory() . '/nebula.log';
				if ( is_child_theme() ){
					$filepath = get_stylesheet_directory() . '/nebula.log'; //Use the child theme directory if using a child theme
				}
			}

			//Ensure the directory for the file exists
			if ( !file_exists(dirname($filepath)) ){
				wp_mkdir_p(dirname($filepath)); //Create the directory and any necessary parents
			}

			//If the message is not a string, encode it as JSON
			if ( !is_string($message) ){
				$message = wp_json_encode($message);
			}

			//If verbose data is requested, add it to the message
			if ( !empty($verbose) ){
				$message .= ' | IP: ' . $this->get_ip_address(false);
				$message .= ' | Role: ' . $this->user_role(true);
				$message .= ' | SID: ' . $this->nebula_session_id();

				if ( !empty($this->super->server['HTTP_USER_AGENT']) ){
					$message .= ' | UA: ' . $this->super->server['HTTP_USER_AGENT'];
				}
			}

			$message = '[' . date('l, F j, Y - g:i:sa') . '] ' . $message . ' | URL: ' . $this->requested_url() . PHP_EOL; //Add timestamp, URL, and newline

			//Limit the file size of Nebula-based log files
			if ( !empty($limited) ){
				if ( file_exists($filepath) && filesize($filepath) > (MB_IN_BYTES*100) ){
					$lines = explode('\n', file_get_contents($filepath));
					$half = array_slice($lines,ceil(count($lines)/2)); //Keep last 50%
					file_put_contents($filepath, implode('\n', $half));
				}
			}

			file_put_contents($filepath, $message, FILE_APPEND); //Create the log file if needed and append to it
			do_action('qm/debug', $message); //Log it in Query Monitor as well
		}

		//Register table name in $wpdb global
		public function register_table_names(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Register Logs Tables', 'start', '[Nebula] Logs');

			if ( $this->get_option('administrative_log') ){
				global $wpdb;

				if ( !isset($wpdb->nebula_logs) ){
					$wpdb->nebula_logs = $wpdb->prefix . 'nebula_logs';
				}
			}

			$this->timer('Register Logs Tables', 'end');
		}

		//Create Nebula logs table
		public function create_tables(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !$this->is_admin_page() && !isset($this->super->get['settings-updated']) && !$this->is_staff() ){ //Only trigger this in admin when Nebula Options are saved (by a staff member)
				return;
			}

			$this->timer('Create Logs Tables', 'start', '[Nebula] Logs');

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

			$this->timer('Create Logs Tables', 'end', '[Nebula] Logs');
		}

		//Insert log into DB
		//Reminder: Importance of 4 or less will get removed when logs are cleaned. Importance of 6 or more will appear bold in list.
		public function add_log($message='', $importance=0, $optimize=true){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !is_user_logged_in() || empty($message) ){
				return null;
			}

			$this->timer('Add Log to Table', 'start', '[Nebula] Logs');

			//Add the log to the Sucuri audit log if the plugin is being used
			if ( class_exists('SucuriScanEvent') && method_exists('SucuriScanEvent', 'reportInfoEvent') ){ //Check if the Sucuri class and one of the log functions exists
				SucuriScanEvent::reportInfoEvent($message);
			}

			if ( $this->get_option('administrative_log') ){ //If the Nebula Option is enabled
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
					$this->timer('Add Log to Table', 'end');
					return null;
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

				$this->timer('Add Log to Table', 'end');
				return is_int($log_insertion); //Boolean return
			}

			$this->timer('Add Log to Table', 'end');
			return null;
		}

		//Insert log via admin interface (AJAX)
		public function add_log_via_ajax(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('{response:"Permission Denied. Refresh and try again."}'); }

			$message = sanitize_text_field($this->super->post['message']); //Sanitize message string
			$importance = intval($this->super->post['importance']); //Sanitize importance integer @todo "Nebula" 0: nullish coalescing operator here (set to 4)

			$this->add_log($message, $importance);
			exit('{response:success}');
		}

		//Remove log from DB
		public function remove_log($id){
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				global $wpdb;

				$wpdb->delete($wpdb->nebula_logs, array('id' => intval($id))); //DB Query

				delete_transient('nebula_logs');
			}

			return null;
		}

		//Remove log via admin interface (AJAX)
		public function remove_log_via_ajax(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('{response:"Permission Denied. Refresh and try again."}'); }

			$log_id = intval($this->super->post['id']); //Sanitize ID
			$this->remove_log($log_id);
			exit('{response:success}');
		}

		//Remove all low importance logs from DB (by default this removes any log messages with importance of 4 or below)
		public function clean_logs($importance=4){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Clean Logs Table', 'start', '[Nebula] Logs');

			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				global $wpdb;

				$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->nebula_logs . " WHERE importance <= %d", $importance)); //DB Query

				delete_transient('nebula_logs');
			}

			$this->timer('Clean Logs Table', 'end');
			return null;
		}

		//Remove all low importance logs from DB via admin interface (AJAX)
		public function clean_logs_via_ajax(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('{response:"Permission Denied. Refresh and try again."}'); }

			$importance = intval($this->super->post['importance']); //Sanitize importance
			$this->clean_logs($importance);
			exit('{response:success}');
		}

		//Remove low importance logs before a date
		public function optimize_logs(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Optimize Logs Table', 'start', '[Nebula] Logs');

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

			$this->timer('Optimize Logs Table', 'end');
		}

		//Get all logs data (or just the column names)
		public function get_logs($rows=true){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Get Logs From Table', 'start', '[Nebula] Logs');

			if ( $this->get_option('administrative_log') && $this->is_staff() ){
				//Only return column names if requested
				if ( empty($rows) ){
					global $wpdb;
					$nebula_logs_data_head = $wpdb->get_results("SHOW columns FROM $wpdb->nebula_logs"); //Get the column names from the primary table
					$this->timer('Get Logs From Table', 'end');
					return (array) $nebula_logs_data_head; //Convert to an array and return
				}

				//Otherwise get the actual logs data (rows)
				$nebula_logs_data = nebula()->transient('nebula_logs', function(){
					global $wpdb; //Need to re-declare so it is available within this function
					$this->timer('Get Logs From Table', 'end');
					return $wpdb->get_results("SELECT * FROM $wpdb->nebula_logs ORDER BY timestamp DESC LIMIT 100"); //Get all data (last 100 logs) from the DB table in descending order (latest first)
				}, HOUR_IN_SECONDS);

				$this->timer('Get Logs From Table', 'end');
				return (array) $nebula_logs_data; //Convert to an array and return
			}

			$this->timer('Get Logs From Table', 'end');
			return null;
		}

		//Attempt to log automatic WordPress core updates
		public function log_auto_core_update($update, $item){
			nebula()->add_log('Automatic WP Core update', 4); //I don't know if this will properly log because logs require a logged-in user, but this may happen at any time...
			return $update;
		}

		//Get all PHP log files
		public function get_log_files($requested_type='all', $fresh=false){
			if ( $this->is_minimal_mode() ){return null;}
			$timer_name = $this->timer('Get Log Files', 'start', '[Nebula] Logs');

			//Use the transient so we avoid scanning multiple times in short periods of time
			$all_log_files = $this->transient('nebula_all_log_files', function(){
				//These file names are what we will be looking for when globbing the directories
				$file_names = array(
					'php' => 'error_log',
					'wordpress' => 'debug.log',
					'nebula' => 'nebula.log',
					'javascript' => 'js_error.log',
				);

				//Prepare the list
				$all_log_files = array(
					'php' => array(),
					'wordpress' => array(),
					'nebula' => array()
				);

				//Add the PHP ini log file (if it exists). It may just be the same as the Wordpress debug.log file.
				$ini_error_log_file = ini_get('error_log');
				if ( file_exists($ini_error_log_file) ){
					$all_log_files['php'][] = array(
						'type' => 'php',
						'path' => ini_get('error_log'), //Full file path
						'name' => basename(ini_get('error_log')),
						'shortpath' => ( str_contains(ini_get('error_log'), WP_CONTENT_DIR) )? '/' . str_replace(ABSPATH, '', ini_get('error_log')) : ini_get('error_log'), //Shorten the path if it is within the WordPress directory
						'bytes' => filesize(ini_get('error_log')),
					);
				}

				//Check all theme files
				foreach ( $this->glob_r(WP_CONTENT_DIR . '/themes/*') as $file ){
					if ( $this->contains($file, array('/twenty')) ){ //Ignore certain strings (this can be anything in the filepath)
						continue; //Move on to the next file
					}

					foreach ( $file_names as $type => $name ){
						if ( str_contains($file, '/' . $name) ){ //If this file contains one of the target log file names (from the array above)
							$all_log_files[$type][] = array(
								'type' => $type,
								'path' => $file, //Full file path
								'shortpath' => '/' . str_replace(ABSPATH, '', $file), //Relative to the root WP directory
								'name' => $name,
								'bytes' => filesize($file),
							);

							break 2; //Move on to the next file
						}
					}
				}

				//Now check the theme /data/logs/ directory for any files at all
				$theme_directory = ( is_child_theme() )? get_stylesheet_directory() : get_template_directory();
				if ( file_exists($theme_directory . '/data/logs/') ){
					foreach ( $this->glob_r($theme_directory . '/data/logs/*') as $file ){
						$all_log_files['theme'][] = array(
							'type' => 'theme',
							'path' => $file, //Full file path
							'shortpath' => '/' . str_replace(ABSPATH, '', $file), //Relative to the root WP directory
							'name' => basename($file),
							'bytes' => filesize($file),
						);
					}
				}

				//Check for the content directory debug.log file (this is separate to reduce the glob process because it is a single file in a set location)
				$wp_content_debug_log = WP_CONTENT_DIR . '/debug.log';
				if ( file_exists($wp_content_debug_log) ){
					$all_log_files['wordpress'][] = array(
						'type' => 'wordpress',
						'path' => $wp_content_debug_log,
						'shortpath' => '/' . str_replace(ABSPATH, '', $wp_content_debug_log), //Relative to the root WP directory
						'name' => 'debug.log',
						'bytes' => filesize($wp_content_debug_log),
					);
				}

				//De-duplicate the arrays (so that no endpoint URL exists more than once at all regardless of nested "type" array)
				$seen = array();
				foreach ( $all_log_files as $key => &$logs ){
					$logs = array_filter($logs, function($log) use (&$seen){
						$url = $log['path'];

						if ( in_array($url, $seen) ){
							return false; //Exclude/Remove this as it is a duplicate
						}

						$seen[] = $url; //Mark the path as seen
						return true; //Keep unique logs
					});
				}
				unset($logs); //Delete the reference array that was only needed for de-duping

				return $all_log_files;
			}, MINUTE_IN_SECONDS*15);

			$this->timer($timer_name, 'end');

			if ( $requested_type === 'all' ){
				return $all_log_files;
			}

			switch ( str_replace(array('_', ',', '-'), '', $requested_type) ){
				case 'php':
				case 'errorlog':
				case 'errors':
					return $all_log_files['php'];
				case 'wordpress':
				case 'wp':
				case 'debuglog':
				case 'debug':
					return $all_log_files['wordpress'];
				case 'nebula':
				case 'nebulalog':
					return $all_log_files['nebula'];
				default:
					return $all_log_files;
			}
		}

		//Count the number of fatal errors in the error_log file if it exists
		public function count_fatal_errors(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !ini_get('log_errors') ){ //Check if error logging is enabled
				return null;
			}

			$timer_name = $this->timer('Count Fatal Errors', 'start', '[Nebula] Logs');

			$log_file = ini_get('error_log'); //Path to the error log file

			//Ensure the log file exists
			if ( !$log_file || !file_exists($log_file) ){
				return 0;
			}

			//Ensure the log file is readable
			if ( !is_readable($log_file) ){
				return 'Error log file not readable';
			}

			//Ignore log files that would be too slow to process
			$log_file_size = filesize($log_file);
			if ( $log_file_size > MB_IN_BYTES*20 ){ //Only process files within this size threshold
				return 'Error log file too large (' . $this->format_bytes($log_file_size) . ')';
			}

			$fatal_error_count = 0; //Initialize counter
			$one_week_ago = strtotime('-7 days'); //Get the timestamp for one week ago

			//Open the log file for reading
			$file_handle = fopen($log_file, 'r');
			if ( $file_handle ){
				while ( ($line = fgets($file_handle)) !== false ){
					//Extract the timestamp from the log line (assuming standard log format)
					if ( preg_match('/\[(.*?)\]/', $line, $matches) ){
						$log_timestamp = strtotime($matches[1]);

						//Skip dates that are outside the desired threshold (remember that the most recent log entries are at the end)
						if ( $log_timestamp < $one_week_ago ){
							continue;
						}

						//Check if the line contains a fatal error within the desired range
						if ( str_contains(strtolower($line), 'php fatal') || str_contains(strtolower($line), 'fatal error:') ){ //Note: WP automatic plugin updates often have text that says "will not be checked for fatal errors" so we need to avoid that false positive
							$fatal_error_count++;
						}
					} else {
						break; //Stop reading if the timestamp cannot be matched
					}
				}
				fclose($file_handle);
			}

			$timer_name = $this->timer('Count Fatal Errors', 'end');
			return $fatal_error_count;
		}
	}
}