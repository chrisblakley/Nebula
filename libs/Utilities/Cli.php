<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Cli') ){
	trait Cli {
		//Register WP-CLI commands
		public function register_cli_commands(){
			if ( defined('WP_CLI') && WP_CLI ){ //Don't use $this->is_cli() here just for safety
				WP_CLI::add_command('nebula init', array($this, 'cli_init'), array(
					'aliases' => array('nebula initialize', 'nebula initialization'),
				));
				WP_CLI::add_command('nebula info', array($this, 'cli_nebula_info'), array(
					'aliases' => array('nebula information', 'nebula dev-info', 'nebula dev-info', 'nebula status'),
				));
				WP_CLI::add_command('nebula warnings', array($this, 'cli_nebula_warnings'), array(
					'aliases' => array('nebula warning'),
				));
				WP_CLI::add_command('nebula option', array($this, 'cli_option'), array(
					'aliases' => array('nebula options'),
				));
				WP_CLI::add_command('nebula data', array($this, 'cli_data'));
				WP_CLI::add_command('nebula sass', array($this, 'cli_sass'), array(
					'aliases' => array('nebula scss', 'nebula css', 'nebula style', 'nebula styles'),
				));
				WP_CLI::add_command('nebula transient', array($this, 'cli_transients'), array(
					'aliases' => array('nebula transients'),
				));
				WP_CLI::add_command('nebula todo', array($this, 'cli_todo'), array(
					'aliases' => array('nebula todos', 'nebula to-do', 'nebula to-dos'),
				));
			}
		}

		//Output a message in CLI if it is being used
		public function cli_output($message, $level='log', $exit_on_error=true){
			if ( $this->is_cli() ){
				$level = ( in_array($level, array('log', 'warning', 'error', 'success')) )? $level : 'log'; //Ensure a valid level is used

				switch ( $level ){
					case 'success':
						WP_CLI::success($message);
						break;
					case 'warning':
						WP_CLI::warning($message);
						break;
					case 'error':
						WP_CLI::error($message, $exit_on_error);
						break;
					default:
						WP_CLI::log($message);
						break;
				}
			}
		}

		//wp nebula init
		public function cli_init($args, $assoc_args){
			if ( $this->is_initialized_before() || $this->get_data('initialized') ){
				WP_CLI::confirm('This website has already been initialized before. Are you sure you want to proceed?');
			}

			$this->initialization();
		}

		//wp nebula info verbose
		public function cli_nebula_info($args, $assoc_args){
			$subcommand = ( isset($args[0]) )? $args[0] : '';

			global $wp_version;

			WP_CLI::log('=== Nebula Info ===');
			WP_CLI::log('Website: ' . home_url('/'));
			WP_CLI::log('Nebula Version: ' . $this->version('realtime'));
			WP_CLI::log('Committed: ' . $this->version('date'));
			WP_CLI::log('WordPress Version: ' . $wp_version);
			WP_CLI::log('PHP Version: ' . PHP_VERSION);

			if ( is_child_theme() ){
				WP_CLI::log('Using Child Theme: ' . get_option('stylesheet'));
				WP_CLI::log('Child Theme Version: ' . $this->child_version());
			}

			if ( is_multisite() ){
				WP_CLI::log('Is Multisite');
			}

			//Also include technical information when "dev" is requested
			//Note: This is not all of the information available compared to the Dev Info Dashboard metabox
			if ( $subcommand == 'verbose' || $subcommand == 'dev' || $subcommand == 'all' ){
				WP_CLI::log('Environment Type: ' . wp_get_environment_type());
				WP_CLI::log('Server OS: ' . PHP_OS);
				WP_CLI::log('MySQL Version: ' . mysqli_get_client_version());
				WP_CLI::log('Sass Last Processed: ' . $this->get_data('scss_last_processed'));
				WP_CLI::log('GA Measurement ID: ' . $this->get_option('ga_measurement_id', ''));
				WP_CLI::log('GTM Container ID: ' . $this->get_option('gtm_id', ''));
				WP_CLI::log('Revisions: ' . (( WP_POST_REVISIONS == -1 )? 'all' : WP_POST_REVISIONS));
			}
		}

		//Output any warnings that Nebula has detected
		//wp nebula warnings
		public function cli_nebula_warnings($args, $assoc_args){
			$nebula_warnings = $this->check_warnings(true);

			WP_CLI::log('=== Nebula Warnings (' . count($nebula_warnings) . ') ===');
			foreach ( $nebula_warnings as $warning ){
				$this->cli_output(strip_tags($warning['description']), $warning['level'], false);
			}
		}

		//Get the value of a Nebula option or set the value of one or multiple options
		//wp nebula option set --scss=1 --jpeg_quality=75 --dev_email_domain="example.com"
		public function cli_option($args, $assoc_args){
			$subcommand = ( isset($args[0]) )? $args[0] : 'set';

			$aliases = array(
				'sass' => 'scss',
				'jpg_quality' => 'jpeg_quality',
				'ga_tracking_id' => 'ga_measurement_id',
				'ga4_measurement_id' => 'ga_measurement_id',
				'sw' => 'service_worker',
				'x_username' => 'twitter_username',
			);

			//Helper to normalize keys and optionally show warning
			$normalize_key = function($key) use ($aliases){
				$key = str_replace('-', '_', $key);

				if ( isset($aliases[$key]) ){
					WP_CLI::warning('Note: You used "' . $key . '", but the actual Nebula option name is "' . $aliases[$key] . '"');
					return $aliases[$key];
				}

				return $key;
			};

			//Get a Nebula Option
			if ( $subcommand == 'get' ){
				//Support shorthand like --scss instead of --key=scss
				if ( empty($assoc_args['key']) ){
					$first_key = array_key_first($assoc_args);
					if ( $first_key ){
						$assoc_args['key'] = $first_key;
					}
				}

				if ( empty($assoc_args['key']) ){
					WP_CLI::error('You must provide a --key to get.');
					return;
				}

				$key = $normalize_key($assoc_args['key']);
				$value = $this->get_option($key);

				if ( $value === null ){
					WP_CLI::warning('No value found for "' . $key . '"');
				} else {
					WP_CLI::log('"' . $key . '" = "' . (( is_scalar($value))? $value : json_encode($value)) . '"');
				}

				return;

			//List the values of all Nebula Options
			} elseif ( $subcommand == 'list' ){
				$nebula_options = get_option('nebula_options');

				foreach ( $nebula_options as $key => $value ){
					WP_CLI::log('"' . $key . '" = "' . (( is_scalar($value) )? $value : json_encode($value)) . '"');
				}

				return;
			}

			//Set one or more Nebula Options
			if ( empty($assoc_args) ){
				WP_CLI::error('No options provided. Use --key=value format.');
				return;
			}

			//Normalize aliases on all keys in $assoc_args before update
			$normalized_assoc_args = array();
			foreach ( $assoc_args as $key => $value ){
				$normalized_key = $normalize_key($key);
				$normalized_assoc_args[$normalized_key] = $value;
			}

			//Now loop through the normalized keys to actually set the values
			foreach ( $normalized_assoc_args as $key => $value ){
				$this->update_option($key, $value);
				WP_CLI::success('Updated Nebula option "' . $key . '" to "' . $value . '"');
			}
		}

		//Update one or multiple datapoints
		//wp nebula data set --check_new_options=1 --need_sass_compile=1
		public function cli_data($args, $assoc_args){
			$subcommand = ( isset($args[0]) )? $args[0] : 'set';

			//Get a Nebula datapoint
			if ( $subcommand == 'get' ){
				if ( empty($assoc_args['key']) ){
					$first_key = array_key_first($assoc_args);

					if ( $first_key ){
						$assoc_args['key'] = $first_key;
					}
				}

				if ( empty($assoc_args['key']) ){
					WP_CLI::error('You must provide a --key to get.');
					return;
				}

				$value = $this->get_data($assoc_args['key']);

				if ( $value === null ){
					WP_CLI::warning('No value found for "' . $assoc_args['key'] . '"');
				} else {
					WP_CLI::log('"' . $assoc_args['key'] . '" = "' . ( is_scalar($value)? $value : json_encode($value) ) . '"');
				}

				return;
			}

			//Set one or more Nebula datapoints
			if ( empty($assoc_args) ){
				WP_CLI::error('No datapoints provided. Use --key=value format.');
				return;
			}

			foreach ( $assoc_args as $key => $value ){
				$this->update_data($key, $value);
				WP_CLI::success('Updated Nebula data "' . $key . '" to "' . $value . '"');
			}
		}

		//Force process all Sass
		//wp nebula sass process
		public function cli_sass($args, $assoc_args){
			$subcommand = ( isset($args[0]) )? $args[0] : 'process';

			if ( $subcommand == 'process' ){
				$this->scss_controller(true);
				return;
			} elseif ( $subcommand == 'last' ){
				WP_CLI::log('Nebula Sass last processed: ' . $this->get_data('scss_last_processed'));
				return;
			}

			WP_CLI::warning('Unknown subcommand "' . $subcommand . '"');
		}

		//Clear specified transients
		//wp nebula transient delete --transient=nebula_todo_items
		public function cli_transients($args, $assoc_args){
			$subcommand = ( isset($args[0]) )? $args[0] : 'process';

			if ( $subcommand == 'delete' || $subcommand == 'clear' ){
				if ( !empty($assoc_args['transient']) && $assoc_args['transient'] != 'all' ){
					$transient_name = $assoc_args['transient'];

					if ( delete_transient($transient_name) ){
						WP_CLI::success('Transient "' . $transient_name . '" deleted.');
					} else {
						WP_CLI::warning('Transient "' . $transient_name . '" not found or could not be deleted.');
					}
				} else {
					$this->delete_transients();
					WP_CLI::success('Nebula transients deleted.');
				}
				return;
			}

			WP_CLI::warning('Unknown subcommand "' . $subcommand . '"');
		}

		//Output Files/Lines with To-Do Comments
		//wp nebula todo
		public function cli_todo($args, $assoc_args){
			$subcommand = ( isset($args[0]) )? $args[0] : '';

			$todo_items = get_transient('nebula_todo_items');

			if ( empty($todo_items) ){
				WP_CLI::error('The "nebula_todo_items" transient is empty. Load the WP Dashboard to process it.');
			}

			if ( is_child_theme() ){
				unset($todo_items['parent']);
			}

			//Otherwise we are outputting the to-do items themselves
			foreach ( $todo_items as $location => $todos ){
				if ( $subcommand == 'count' ){
					$theme_type = ( is_child_theme() )? 'child' : 'parent';
					WP_CLI::log('To-Do Count: ' . count($todo_items[$theme_type]));
					return;
				}

				if ( count($todos) == 0 ){
					WP_CLI::success('No to-do comments found!');
					return;
				}

				//Loop through the to-do items in this theme location
				foreach ( $todos as $todo ){
					WP_CLI::log(str_replace($todo['directory'], '', dirname($todo['filepath'])) . '/' . basename($todo['filepath']) . ' (Line: ' . ($todo['line_number']+1) . '): ' . $todo['description']);
				}
			}
		}
	}
}