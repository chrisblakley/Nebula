<?php
/**
 * Visitors
 *
 * @package     Nebula\Visitors
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Visitors' ) ) {

    class Nebula_Visitors {

        public function __construct() {

            /*==========================
                Admin
             ===========================*/

            if ( nebula_option('visitors_db') ) {
                //Add Visitors menu in admin
                add_action('admin_menu', array($this, 'admin_menu'));
            }

            //Manually update visitor data
            add_action('wp_ajax_nebula_ajax_manual_update_visitor', array( $this, 'ajax_manual_update_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_manual_update_visitor', array( $this, 'ajax_manual_update_visitor' ) );

            //Manually delete null and 0 score rows
            add_action('wp_ajax_nebula_ajax_remove_zero_scores', array( $this, 'ajax_remove_zero_scores' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_remove_zero_scores', array( $this, 'ajax_remove_zero_scores' ) );

            //Manually delete the entire Nebula Visitor table
            add_action('wp_ajax_nebula_ajax_drop_nv_table', array( $this, 'ajax_drop_table' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_drop_nv_table', array( $this, 'ajax_drop_table' ) );

            /*==========================
                Frontend
             ===========================*/

            //Create Users Table with minimal default columns.
            add_action('init', array( $this, 'create_visitors_table' ), 2); //Using init instead of admin_init so this triggers before check_nebula_id (below)

            //Check if the Nebula ID exists on load and generate/store a new one if it does not.
            add_action('init', array( $this, 'check_nebula_id' ), 11);

            //Retrieve User Data
            add_action('wp_ajax_nebula_ajax_get_visitor_data', array( $this, 'ajax_get_visitor_data' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_get_visitor_data', array( $this, 'ajax_get_visitor_data' ) );

            //Vague Data - Only update if it doesn't already exist in the DB
            add_action('wp_ajax_nebula_ajax_vague_visitor', array( $this, 'ajax_vague_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_vague_visitor', array( $this, 'ajax_vague_visitor' ) );

            //Update Visitor Data
            add_action('wp_ajax_nebula_ajax_update_visitor', array( $this, 'ajax_update_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_update_visitor', array( $this, 'ajax_update_visitor' ) );

            //Append to Visitor Data
            add_action('wp_ajax_nebula_ajax_append_visitor', array( $this, 'ajax_append_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_append_visitor', array( $this, 'ajax_append_visitor' ) );

            //Increment Visitor Data
            add_action('wp_ajax_nebula_ajax_increment_visitor', array( $this, 'ajax_increment_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_increment_visitor', array( $this, 'ajax_increment_visitor' ) );
        }

        /*==========================
            Admin
         ===========================*/

        //Nebula admin subpages
        public function admin_menu(){
            add_theme_page('Nebula Visitors Data', 'Nebula Visitors Data', 'manage_options', 'nebula_visitors_data', array( $this, 'admin_page' ) ); //Nebula Visitors Data page
        }

        //The Nebula Visitors Data page output
        public function admin_page(){
            global $wpdb;
            $all_visitors_data_head = $wpdb->get_results("SHOW columns FROM nebula_visitors");
            $all_visitors_data_head = (array) $all_visitors_data_head;
            $all_visitors_data = $wpdb->get_results("SELECT * FROM nebula_visitors");
            $all_visitors_data = (array) $all_visitors_data;

            if ( !empty($all_visitors_data) ): ?>
                <script>
                    jQuery(window).on('load', function(){
                        jQuery('#visitors_data').DataTable({
                            "aaSorting": [[0, "desc"]], //Default sort (column number)
                            "aLengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]], //"Show X entries" dropdown. Values, Text
                            "iDisplayLength": 25, //Default entries shown (Does NOT need to match aLengthMenu).
                            "scrollX": true,
                            "scrollY": '65vh',
                            "scrollCollapse": true,
                            //"paging": false
                        });

                        jQuery('.dataTables_filter input').attr('placeholder', 'Filter');

                        jQuery(document).on('click tap touch', '.dataTables_wrapper tbody td', function(){
                            jQuery(this).parents('tr').toggleClass('selected');

                            if ( jQuery(this).parents('tr').hasClass('selected') ){
                                if ( jQuery(this).attr('data-column') === 'id' || jQuery(this).attr('data-column') === 'nebula_id' || jQuery(this).attr('data-column') === 'ga_cid' || jQuery(this).attr('data-column') === 'score' ){
                                    jQuery('#querystatus').html('This column is protected.');
                                } else {
                                    jQuery('.activecell').removeClass('activecell');
                                    jQuery(this).addClass('activecell');
                                    jQuery('#queryid').val(jQuery(this).parents('tr').find('td[data-column="id"]').text());
                                    jQuery('#querycol').val(jQuery(this).attr('data-column'));
                                    jQuery('#queryval').val(jQuery(this).text());
                                    jQuery('#querystatus').html('');
                                }
                            } else {
                                jQuery(this).removeClass('activecell');
                                jQuery('#queryid').val('');
                                jQuery('#querycol').val('');
                                jQuery('#queryval').val('');
                            }
                            jQuery('#queryprog').removeClass();
                        });

                        jQuery(document).on('click tap touch', '.refreshpage', function(){
                            window.location.reload();
                            return false;
                        });

                        jQuery('#runquery').on('click tap touch', function(){
                            if ( jQuery('#queryid').val() !== '' && jQuery('#querycol').val() !== '' ){
                                if ( jQuery('#querycol').val() === 'id' || jQuery('#querycol').val() === 'nebula_id' || jQuery('#querycol').val() === 'ga_cid' ){
                                    jQuery('#querystatus').html('This column is protected.');
                                    return false;
                                }

                                jQuery('#querystatus').html('');
                                jQuery('#queryprog').removeClass().addClass('fa fa-fw fa-spinner fa-spin');

                                jQuery.ajax({
                                    type: "POST",
                                    url: nebula.site.ajax.url,
                                    data: {
                                        nonce: nebula.site.ajax.nonce,
                                        action: 'nebula_ajax_manual_update_visitor',
                                        id: jQuery('#queryid').val(),
                                        col: jQuery('#querycol').val(),
                                        val: jQuery('#queryval').val(),
                                    },
                                    success: function(response){
                                        jQuery('#querystatus').html('Success! Updated table value visualized- <a class="refreshpage" href="#">refresh this page</a> to see actual updated data (and updated score).');
                                        jQuery('#queryprog').removeClass().addClass('fa fa-fw fa-check');
                                        setTimeout(function(){
                                            jQuery('#queryprog').removeClass();
                                        }, 1500);

                                        jQuery('.activecell').text(jQuery('#queryval').val());

                                        jQuery('#queryid').val('');
                                        jQuery('#querycol').val('');
                                        jQuery('#queryval').val('');
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown){
                                        jQuery('#querystatus').text('An AJAX error occured.');
                                        jQuery('#queryprog').removeClass().addClass('fa fa-fw fa-times');
                                    },
                                    timeout: 60000
                                });
                            } else {
                                jQuery('#querystatus').html('ID and Column are required.');
                            }

                            return false;
                        });

                        <?php if ( current_user_can('manage_options') ): ?>
                        jQuery('#deletezeroscores a').on('click tap touch', function(){
                            if ( confirm("Are you sure you want to remove all scores of 0 (or less)? This can not be undone.") ){
                                jQuery('#deletezeroscores').html('<i class="fa fa-fw fa-spin fa-spinner"></i> Removing scores of 0 (or less)...');

                                jQuery.ajax({
                                    type: "POST",
                                    url: nebula.site.ajax.url,
                                    data: {
                                        nonce: nebula.site.ajax.nonce,
                                        action: 'nebula_ajax_remove_zero_scores',
                                    },
                                    success: function(response){
                                        jQuery('#deletezeroscores').html('Success! Visitor data with score of 0 (or less) have been removed. Refreshing page... <a class="refreshpage" href="#">Manual Refresh</a>');
                                        window.location.reload();
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown){
                                        jQuery('#deletezeroscores').html('Error. An AJAX error occured. <a class="refreshpage" href="#">Please refresh and try again.</a>');
                                    },
                                    timeout: 60000
                                });
                            }

                            return false;
                        });

                        jQuery('#dropnvtable a').on('click tap touch', function(){
                            if ( confirm("Are you sure you want to delete the entire Nebula Visitors table? This can not be undone.") ){
                                jQuery('#dropnvtable').html('<i class="fa fa-fw fa-spin fa-spinner"></i> Deleting Nebula Visitors Table...');

                                jQuery.ajax({
                                    type: "POST",
                                    url: nebula.site.ajax.url,
                                    data: {
                                        nonce: nebula.site.ajax.nonce,
                                        action: 'nebula_ajax_drop_nv_table',
                                    },
                                    success: function(response){
                                        jQuery('#dropnvtable').html('Success! Nebula Visitors table has been dropped from the database. The option has also been disabled. Re-enable it in <a href="themes.php?page=nebula_options">Nebula Options</a>.');
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown){
                                        jQuery('#dropnvtable').html('Error. An AJAX error occured. <a class="refreshpage" href="#">Please refresh and try again.</a>');
                                    },
                                    timeout: 60000
                                });
                            }

                            return false;
                        });
                        <?php endif; ?>
                    });
                </script>

                <div id="nebula-visitor-data" class="wrap">
                    <h2>Nebula Visitors Data</h2>
                    <?php
                    if ( !current_user_can('manage_options') && !is_dev() ){
                        wp_die('You do not have sufficient permissions to access this page.');
                    }
                    ?>

                    <p>Visitor data can be sorted and filtered here. Lines in <em>italics</em> are your data. Green lines are "known" visitors who have identified themselves. If your Hubspot CRM API key is added to <a href="themes.php?page=nebula_options" target="_blank">Nebula Options</a>, known visitors' data is automatically updated there. To modify data, click the cell to be updated and complete the form below the table. Use the Notes column to make notes about users (this column can not be accessed for retargeting!)</p>
                    <p>Data will expire 30 days after the visitors' "Last Modified Date" unless the score is 100 or greater. Scores of 0 (or less) can be deleted manually by clicking the corresponding link at the bottom of this page.</p>

                    <div class="dataTables_wrapper">
                        <table id="visitors_data" class="display compact" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <?php foreach ( $all_visitors_data_head as $column_name ): ?>
                                    <td>
                                        <?php
                                        $column_name = (array) $column_name;
                                        echo ucwords(str_replace('_', ' ', $column_name['Field']));
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $all_visitors_data as $visitor_data ): ?>
                                <?php
                                $visitor_data = (array) $visitor_data;
                                $row_class = '';
                                if ( $visitor_data['nebula_id'] === get_nebula_id() ){
                                    $row_class .= 'you ';
                                }

                                if ( $visitor_data['known'] == '1' ){
                                    $row_class .= 'known ';
                                }
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <?php foreach ( $visitor_data as $column => $value ): ?>
                                        <?php
                                        $cell_title = '';
                                        $cell_class = '';
                                        $date_columns = array('create_date', 'last_modified_date', 'current_session');
                                        if ( in_array($column, $date_columns) ){
                                            $cell_title = date('l, F j, Y - g:i:sa', $value);
                                            $cell_class = 'moreinfo';
                                            $value = $value . ' (' . date('F j, Y - g:i:sa', $value) . ')';
                                        }

                                        if ( $value == '0' ){
                                            $cell_class = 'zerovalue';
                                        }
                                        ?>
                                        <td class="<?php echo $cell_class; ?>" title="<?php echo $cell_title; ?>" data-column="<?php echo $column; ?>"><?php echo sanitize_text_field(mb_strimwidth($value, 0, 153, '...')); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="modify-visitor-form">
                        <h2>Modify Visitor Data</h2>
                        <p>Click a cell in the table to modify that visitor data. Some columns are protected, and others may revert when that visitor returns to the website (for example: Nebula Session ID, User Agent, and others will be re-stored each new visit).</p>

                        <table>
                            <tr class="label-cell">
                                <td class="id-col">ID</td>
                                <td class="col-col">Column</td>
                                <td class="val-col">Value</td>
                                <td class="run-col"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="id-col"><input id="queryid" type="text" /></td>
                                <td class="col-col"><input id="querycol" type="text" /></td>
                                <td class="val-col"><input id="queryval" type="text" /></td>
                                <td class="run-col"><input id="runquery" class="button button-primary" type="submit" name="submit" value="Update Data"></td>
                                <td><i id="queryprog" class="fa fa-fw"></i></td>
                            </tr>
                        </table>

                        <p id="querystatus"></p>

                        <?php if ( current_user_can('manage_options') ): ?>
                            <div id="deletezeroscores" class="action-warning"><a class="danger" href="#"><i class="fa fa-fw fa-warning"></i> Delete Scores of 0 (or less).</a></div>
                        <?php endif; ?>

                        <?php if ( current_user_can('manage_options') ): ?>
                            <div id="dropnvtable" class="action-warning"><a class="danger" href="#"><i class="fa fa-fw fa-warning"></i> Delete entire Nebula Visitors table and disable Visitors Database option.</a></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="wrap">
                    <h2>Nebula Visitors Data</h2>
                    <p>
                        <strong>Nebula Visitors table is empty or does not exist!</strong><br/>
                        To create the table, simply save the <a href="themes.php?page=nebula_options">Nebula Options</a> (and be sure that "Visitor Database" is enabled under the Functions tab).
                    </p>
                </div>
            <?php endif;
        }

        //Manually update visitor data
        public function ajax_manual_update_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            $id = absint(intval($_POST['id']));
            $col = sanitize_key($_POST['col']);
            $val = sanitize_text_field($_POST['val']);

            $protected_columns = array('id', 'nebula_id', 'ga_cid', 'score');
            if ( in_array($col, $protected_columns) ){
                return false;
                exit;
            }

            global $wpdb;
            $manual_update = $wpdb->update(
                'nebula_visitors',
                array($col => $val),
                array('id' => $id),
                array('%s'),
                array('%d')
            );

            //recalculate the score after the update
            $update_score = $wpdb->update(
                'nebula_visitors',
                array('score' => nebula_calculate_visitor_score($id)),
                array('id' => $id),
                array('%d'),
                array('%d')
            );

            wp_die();
        }

        //Manually delete null and 0 score rows
        public function ajax_remove_zero_scores(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            if ( current_user_can('manage_options') ){
                global $wpdb;
                $zero_scores = $wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE score <= %d", 0));
            }

            wp_die();
        }

        //Manually delete the entire Nebula Visitor table
        public function ajax_drop_table(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            if ( current_user_can('manage_options') ){
                global $wpdb;
                $remove_nv_table = $wpdb->query("DROP TABLE nebula_visitors");
                nebula_update_option('visitors_db', 'disabled');
            }

            wp_die();
        }

        /*==========================
            Frontend
         ===========================*/

        //Create Users Table with minimal default columns.
        public function create_visitors_table(){
            if ( is_admin() && nebula_option('visitors_db') && isset($_GET['settings-updated']) && is_staff() ){ //Only trigger this in admin when Nebula Options are saved.
                global $wpdb;

                $visitors_table = $wpdb->query("SHOW TABLES LIKE 'nebula_visitors'");
                if ( empty($visitors_table) ){
                    $created = $wpdb->query("CREATE TABLE nebula_visitors (
				id INT(11) NOT NULL AUTO_INCREMENT,
				nebula_id TEXT NOT NULL,
				known BOOLEAN NOT NULL DEFAULT FALSE,
				email_address TEXT NOT NULL,
				hubspot_vid INT(11) NOT NULL,
				last_modified_date INT(12) NOT NULL DEFAULT 0,
				last_session_id TEXT NOT NULL,
				notable_poi TEXT NOT NULL,
				score INT(5) NOT NULL DEFAULT 0,
				score_mod INT(5) NOT NULL DEFAULT 0,
				PRIMARY KEY (id)
			) ENGINE = MyISAM;"); //Try InnoDB as engine? and add ROW_FORMAT=COMPRESSED ?
                } else {
                    nebula_remove_expired_visitors();
                }
            }
        }

        //Check if the Nebula ID exists on load and generate/store a new one if it does not.
        public function check_nebula_id(){
            if ( nebula()->utilities->device_detection->is_bot() || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'wordpress') !== false ){
                return false; //Don't add bots to the DB
            }

            $nebula_id = $this->get_nebula_id();

            if ( empty($nebula_id) ){ //If new user
                $this->generate_nebula_id();
            }

            $this->new_or_returning_visitor();
        }

        //Check if this visitor is new or returning using several factors
        public function new_or_returning_visitor(){
            if ( nebula_option('visitors_db') ){
                $last_session_id = nebula_get_visitor_data('last_session_id'); //Check if this returning visitor exists in the DB (in case they were removed)
                if ( empty($last_session_id) ){ //If the nebula_id is not in the DB already, treat it as a new user
                    //Prevent duplicates for users blocking cookies or Google Analytics
                    if ( strpos(ga_parse_cookie(), '-') !== false ){ //If GA CID was generated by Nebula
                        global $wpdb;
                        $unique_new_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE ip_address = '" . sanitize_text_field($_SERVER['REMOTE_ADDR']) . "' AND user_agent = '" . sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . "'");
                        if ( !empty($unique_new_visitor) ){
                            $unique_new_visitor = (array) $unique_new_visitor[0];
                            if ( strpos($unique_new_visitor['ga_cid'], '-') !== false ){ //If that GA CID was also generated by Nebula
                                $this->generate_nebula_id($unique_new_visitor['nebula_id']); //Give this user the same Nebula ID
                                //Update that row instead of inserting a new visitor.
                                $this->update_visitor(array(
                                    'first_session' => '0',
                                    'notes' => 'This user tracked by IP and User Agent.',
                                ));
                            } else {
                                $this->insert_visitor(array('first_session' => '0')); //The matching visitor row had a GA CID assigned (no dashes)
                            }
                        } else {
                            $this->insert_visitor(array('first_session' => '0')); //No matching IP Address with same User Agent
                        }
                    } else {
                        $this->insert_visitor(array('first_session' => '0'));
                    }
                } else {
                    //Check if new session or another pageview in same session
                    if ( session_id() != $last_session_id ){ //New session
                        $update_data = array(
                            'first_session' => '0',
                            'current_session' => time(),
                            'current_session_pageviews' => '1',
                            'last_modified_date' => time(),
                        );
                        $this->increment_visitor(array('session_count'));
                    } else { //Same session
                        $update_data = array(
                            'current_session' => time(),
                            'last_modified_date' => time(),
                        );
                        $this->increment_visitor(array('current_session_pageviews'));
                    }

                    $this->update_visitor($update_data);
                }
            }
        }

        //Return the Nebula ID (or false)
        public function get_nebula_id(){
            if ( isset($_COOKIE['nid']) ){
                return htmlentities(preg_replace('/[^a-zA-Z0-9\.]+/', '', $_COOKIE['nid']));
            }

            return false;
        }

        //Generate a unique Nebula ID and store it in a cookie and insert into DB
        //Or force a specific Nebula ID
        public function generate_nebula_id($force=null){
            if ( !empty($force) ){
                $_COOKIE['nid'] = $force;
            } else {
                $_COOKIE['nid'] = nebula_version('full') . '.' . bin2hex(openssl_random_pseudo_bytes(5)) . '.' . uniqid(); //Update to random_bytes() when moving to PHP7
            }

            $nid_expiration = strtotime('January 1, 2035'); //Note: Do not let this cookie expire past 2038 or it instantly expires.
            setcookie('nid', $_COOKIE['nid'], $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Store the Nebula ID as a cookie called "nid".

            if ( nebula_option('visitors_db') ){
                if ( empty($force) ){
                    global $wpdb;
                    $nebula_id_from_matching_ga_cid = $wpdb->get_results($wpdb->prepare("SELECT nebula_id FROM nebula_visitors WHERE ga_cid = '%s'", ga_parse_cookie())); //Check if the ga_cid exists, and if so use THAT nebula_id again
                    if ( !empty($nebula_id_from_matching_ga_cid) ){
                        $_COOKIE['nid'] = reset($nebula_id_from_matching_ga_cid[0]);
                        setcookie('nid', $_COOKIE['nid'], $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Update the Nebula ID cookie
                    }
                }
            }

            return $_COOKIE['nid'];
        }

        //Create necessary columns by comparing passed data to existing columns
        public function nebula_visitors_create_missing_columns($all_data){
            if ( nebula_option('visitors_db') ){
                $existing_columns = nebula_visitors_existing_columns(); //Returns an array of current table column names

                $needed_columns = array();
                foreach ( $all_data as $column => $value ){
                    if ( empty($value) ){
                        $all_data[$column] = ''; //Convert null values to empty strings.
                    }

                    if ( !in_array($column, $existing_columns) ){ //If the column does not exist, add it to an array
                        $needed_columns[] = $column;
                    }
                }

                if ( !empty($needed_columns) ){
                    global $wpdb;

                    $alter_query = "ALTER TABLE nebula_visitors ";
                    foreach ( $needed_columns as $column_name ){
                        $column_name = sanitize_key($column_name);

                        $sample_value = $all_data[$column_name];
                        $data_type = 'TEXT'; //Default data type
                        if ( is_int($sample_value) ){
                            $data_type = 'INT(12)';
                        } elseif ( strlen($sample_value) == 1 && ($sample_value === '1' || $sample_value === '0') ){
                            $data_type = 'INT(7)';
                        }

                        $alter_query .= "ADD " . $column_name . " " . $data_type . " NOT NULL, "; //Prep each needed column into a query
                    }

                    $create_columns = $wpdb->query(rtrim($alter_query, ', ')); //Create the needed columns
                }
            }
        }

        //Retrieve User Data
        public function ajax_get_visitor_data(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            $column = sanitize_text_field($_POST['data']);
            echo $this->get_visitor_data($column);
            wp_die();
        }

        public function get_visitor_data($column){ //@TODO "Nebula" 0: Update to allow multiple datapoints to be accessed in one query.
            if ( nebula_option('visitors_db') ){
                $column = sanitize_key($column);

                if ( $column == 'notes' ){
                    return false;
                }

                $nebula_id = get_nebula_id();
                if ( !empty($nebula_id) && !empty($column) ){
                    global $wpdb;
                    $requested_data = $wpdb->get_results($wpdb->prepare("SELECT " . $column . " FROM nebula_visitors WHERE nebula_id = '%s'", $nebula_id));

                    if ( !empty($requested_data) && !empty($requested_data[0]) && strtolower(reset($requested_data[0])) != 'null' ){
                        return reset($requested_data[0]); //@TODO "Nebula" 0: update so this could return multiple values
                    }
                }
            }

            return false;
        }

        //Vague Data - Only update if it doesn't already exist in the DB
        public function ajax_low_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            $data = $_POST['data'];
            echo $this->vague_visitor($data);
            wp_die();
        }

        public function vague_visitor($data=array()){
            if ( nebula_option('visitors_db') ){
                $data_to_send = array();
                foreach ( $data as $column => $value ){
                    $existing_value = $this->get_visitor_data($column);
                    if ( empty($existing_value) ){ //If the requested data is empty/null, then update.
                        $data_to_send[$column] = $value;
                    }
                }

                if ( !empty($data_to_send) ){
                    nebula_update_visitor($data_to_send);
                }
            }
            return false;
        }

        //Update Visitor Data
        public function ajax_update_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            $data = $_POST['data'];
            echo $this->update_visitor($data);
            wp_die();
        }

        public function update_visitor($data=array(), $send_to_hubspot=true){ //$data is going to be array(column => value) or an array of arrays
            if ( nebula_option('visitors_db') ){
                global $wpdb;
                $nebula_id = $this->get_nebula_id();

                if ( !empty($nebula_id) ){
                    $update_every_time = $this->visitor_data_update_everytime();
                    $all_data = array_merge($update_every_time, $data); //Add any passed data

                    //Check if the data should be sent to Hubspot
                    if ( !empty($send_to_hubspot) ){
                        $need_to_resend = false;
                        $non_priority_columns = array('id', 'known', 'create_date', 'last_modified_date', 'initial_session', 'first_session', 'last_session_id', 'previous_session', 'current_session', 'current_session_pageviews', 'session_count', 'hubspot_vid', 'bot', 'expiration', 'score', 'ga_block', 'js_block', 'ad_block', 'nebula_session_id'); //These columns should not facilitate a Hubspot send by themselves.
                        foreach ( $all_data as $column => $value ){
                            if ( !in_array($column, $non_priority_columns) ){
                                $need_to_resend = true;
                            }
                        }

                        if ( empty($need_to_resend) ){
                            $send_to_hubspot = false;
                        }
                    }

                    nebula_visitors_create_missing_columns($all_data);
                    $all_strings = array_fill(0, count($all_data), '%s'); //Create sanitization array

                    //Update the visitor row
                    $updated_visitor = $wpdb->update(
                        'nebula_visitors',
                        $all_data,
                        array('nebula_id' => $nebula_id),
                        $all_strings,
                        array('%s')
                    );

                    if ( $updated_visitor === false ){ //If visitor does not exist in the table, create it with defaults and current data... might need to be true if its int(0) too, so maybe go back to empty($updated_visitor)
                        $this->insert_visitor($all_data, $send_to_hubspot);
                    } else {
                        check_if_known($send_to_hubspot);
                    }

                    return true;
                }
            }

            return false;
        }

        //Append to Visitor Data
        public function ajax_append_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            $data = $_POST['data']; //json_decode(stripslashes()); but its already an array... why?
            echo $this->append_visitor($data);
            wp_die();
        }

        public function append_visitor($data=array(), $send_to_hubspot=true){ //$data is going to be array(column => value) or an array of arrays
            if ( nebula_option('visitors_db') ){
                global $wpdb;
                $nebula_id = get_nebula_id();

                if ( !empty($nebula_id) && !empty($data) ){
                    $this->update_visitor(array('last_modified_date' => time()));
                    $this->visitors_create_missing_columns($data);

                    $append_query = "UPDATE nebula_visitors ";
                    foreach ( $data as $column => $value ){
                        $column = sanitize_key($column);

                        $value = sanitize_text_field($value);
                        $append_query .= "SET " . $column . " = CONCAT_WS(',', NULLIF(" . $column . ", ''), '" . $value . "'),"; //how to further prepare/sanitize this value? not sure how many %s are needed...
                    }
                    $append_query = rtrim($append_query, ', ');
                    $append_query .= "WHERE nebula_id = '" . $nebula_id . "'";

                    if ( strpos(str_replace(' ', '', $append_query), 'nebula_visitorsWHERE') === false ){
                        $appended_visitor = $wpdb->query($append_query); //currently working on this

                        if ( $appended_visitor === false ){ //If visitor does not exist in the table, create it with defaults and current data. might need to be true if its int(0) too, so maybe go back to empty($updated_visitor)
                            $this->insert_visitor($data, $send_to_hubspot);
                        } else {
                            check_if_known($send_to_hubspot);
                        }

                        return true;
                    }
                }
            }

            return false;
        }

        //Increment Visitor Data
        public function ajax_increment_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            $data = $_POST['data'];
            echo nebula_increment_visitor($data);
            wp_die();
        }

        public function increment_visitor($data){ //Data should be an array of columns to increment
            if ( nebula_option('visitors_db') ){
                global $wpdb;
                $nebula_id = get_nebula_id();

                if ( is_string($data) ){
                    $data = array($data);
                }

                //@TODO "Nebula" 0: echo here to see if this gets triggered multiple times (when it should only get triggered once)

                if ( !empty($nebula_id) && !empty($data) ){
                    $increment_query = "UPDATE nebula_visitors ";
                    foreach ( $data as $column ){
                        $column = sanitize_key($column);
                        $increment_query .= "SET " . $column . " = " . $column . "+1, ";
                    }
                    $increment_query = rtrim($increment_query, ', ') . ' ';
                    $increment_query .= "WHERE nebula_id = '" . $nebula_id . "'";

                    if ( strpos(str_replace(' ', '', $increment_query), 'nebula_visitorsWHERE') === false ){
                        $incremented_visitor = $wpdb->query($increment_query);
                        if ( $incremented_visitor === false ){ //If visitor does not exist in the table, create it with defaults and current data... might need to be true if its int(0) too, so maybe go back to empty($updated_visitor)
                            return false;
                        }

                        return true;
                    }
                }
            }

            return false;
        }

        //Insert visitor into table with all default detections
        public function insert_visitor($data=array(), $send_to_hubspot=true){
            if ( nebula_option('visitors_db') ){
                $nebula_id = get_nebula_id();
                if ( !empty($nebula_id) ){
                    global $wpdb;

                    $defaults = array(
                        'nebula_id' => $nebula_id,
                        'ga_cid' => ga_parse_cookie(), //Will be UUID on first visit then followed up with actual GA CID via AJAX (if available)
                        'known' => '0',
                        'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                        'create_date' => time(),
                        'hubspot_vid' => false,
                        'score' => '0',
                        'notes' => '',
                        'referer' => ( isset($_SERVER['HTTP_REFERER']) )? sanitize_text_field($_SERVER['HTTP_REFERER']) : '',
                        'first_session' => '1',
                        'session_count' => '1',
                        'prev_session' => '0',
                        'last_session_id' => session_id(),
                        'nebula_session_id' => nebula_session_id(),
                        'current_session' => time(),
                        'current_session_pageviews' => '1',
                        'prerendered' => '0',
                        'page_visibility_hidden' => '0',
                        'page_visibility_visible' => '0',
                        'ga_block' => '0',
                        'page_not_found' => '0',
                        'no_search_results' => '0',
                        'external_links' => '0',
                        'non_linked_click' => '0',
                        'copied_text' => '0',
                        'html_errors' => '0',
                        'js_errors' => '0',
                        'css_errors' => '0',
                        'ajax_errors' => '0',
                        'page_suggestion_clicks' => '0',
                        'infinite_query_loads' => '0',
                        'score_mod' => '0',
                    );

                    //Attempt to detect IP Geolocation data using https://freegeoip.net/
                    if ( nebula_option('ip_geolocation') && nebula_is_available('http://freegeoip.net') ){
                        $response = wp_remote_get('http://freegeoip.net/json/' . $_SERVER['REMOTE_ADDR']);
                        if ( !is_wp_error($response) ){
                            $ip_geo_data = json_decode($response['body']);
                            if ( !empty($ip_geo_data) ){
                                $defaults['ip_country'] = sanitize_text_field($ip_geo_data->country_name);
                                $defaults['ip_region'] = sanitize_text_field($ip_geo_data->region_name);
                                $defaults['ip_city'] = sanitize_text_field($ip_geo_data->city);
                                $defaults['ip_zip'] = sanitize_text_field($ip_geo_data->zip_code);
                            }
                        } else {
                            set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'http://freegeoip.net/json/')), 'Unavailable', 60*5); //5 minute expiration
                        }
                    }

                    $defaults = nebula_visitor_data_update_everytime($defaults);

                    $all_data = array_merge($defaults, $data); //Add any passed data
                    nebula_visitors_create_missing_columns($all_data);
                    $all_strings = array_fill(0, count($all_data), '%s'); //Create sanitization array

                    $wpdb->insert('nebula_visitors', $all_data, $all_strings); //Insert a row with all the default (and passed) sanitized values.

                    check_if_known($send_to_hubspot);

                    nebula_remove_expired_visitors();
                    return true;
                }
            }

            return false;
        }

        //Update certain visitor data everytime.
        //Pass the existing data array to this to append to it (otherwise a new array is created)!
        public function visitor_data_update_everytime($defaults=array()){
            $defaults['ga_cid'] = ga_parse_cookie();
            $defaults['notable_poi'] = nebula_poi();
            $defaults['last_modified_date'] = time();
            $defaults['nebula_session_id'] = nebula_session_id();
            $defaults['score'] = nebula_calculate_visitor_score(); //Try to limit this (without doing a DB query to check)

            //Avoid ternary operators to prevent overwriting existing data (like manual DB entries)

            //Check for nv_ query parameters
            $query_strings = parse_str($_SERVER['QUERY_STRING']);
            if ( !empty($query_strings) ){
                foreach ( $query_strings as $key => $value ){
                    if ( strpos($key, 'nv_') === 0 ){
                        if ( empty($value) ){
                            $value = 'true';
                        }
                        $defaults[sanitize_key(substr($key, 3))] = sanitize_text_field(str_replace('+', ' ', urldecode($value)));
                    }
                }
            }

            //Logged-in User Data
            if ( is_user_logged_in() ){
                $defaults['wp_user_id'] = get_current_user_id();

                $user = get_userdata(get_current_user_id());
                if ( !empty($user) ){
                    //Default WordPress user info
                    if ( !empty($user->roles) ){
                        $defaults['wp_role'] = sanitize_text_field($user->roles[0]);
                    }
                    if ( !empty($user->user_firstname) ){
                        $defaults['first_name'] = sanitize_text_field($user->user_firstname);
                    }
                    if ( !empty($user->user_lastname) ){
                        $defaults['last_name'] = sanitize_text_field($user->user_lastname);
                    }
                    if ( !empty($user->user_firstname) && !empty($user->user_lastname) ){
                        $defaults['full_name'] = sanitize_text_field($user->user_firstname . ' ' . $user->user_lastname);
                    }
                    if ( !empty($user->user_email) ){
                        $defaults['email_address'] = sanitize_text_field($user->user_email);
                    }
                    if ( !empty($user->user_login) ){
                        $defaults['username'] = sanitize_text_field($user->user_login);
                    }

                    //Custom user fields
                    if ( get_user_meta($user->ID, 'headshot_url', true) ){
                        $defaults['photo'] = sanitize_text_field(get_user_meta($user->ID, 'headshot_url', true));
                    }
                    if ( get_user_meta($user->ID, 'jobtitle', true) ){
                        $defaults['job_title'] = sanitize_text_field(get_user_meta($user->ID, 'jobtitle', true));
                    }
                    if ( get_user_meta($user->ID, 'jobcompany', true) ){
                        $defaults['company'] = sanitize_text_field(get_user_meta($user->ID, 'jobcompany', true));
                    }
                    if ( get_user_meta($user->ID, 'jobcompanywebsite', true) ){
                        $defaults['company_website'] = sanitize_text_field(get_user_meta($user->ID, 'jobcompanywebsite', true));
                    }
                    if ( get_user_meta($user->ID, 'phonenumber', true) ){
                        $defaults['phone_number'] = sanitize_text_field(get_user_meta($user->ID, 'phonenumber', true));
                    }
                    if ( get_user_meta($user->ID, 'usercity', true) ){
                        $defaults['city'] = sanitize_text_field(get_user_meta($user->ID, 'usercity', true));
                    }
                    if ( get_user_meta($user->ID, 'userstate', true) ){
                        $defaults['state_name'] = sanitize_text_field(get_user_meta($user->ID, 'userstate', true));
                    }
                }
            }

            //Campaign Data
            if ( isset($_GET['utm_campaign']) ){
                $defaults['utm_campaign'] = sanitize_text_field($_GET['utm_campaign']);
            }
            if ( isset($_GET['utm_medium']) ){
                $defaults['utm_medium'] = sanitize_text_field($_GET['utm_medium']);
            }
            if ( isset($_GET['utm_source']) ){
                $defaults['utm_source'] = sanitize_text_field($_GET['utm_source']);
            }
            if ( isset($_GET['utm_content']) ){
                $defaults['utm_content'] = sanitize_text_field($_GET['utm_content']);
            }
            if ( isset($_GET['utm_term']) ){
                $defaults['utm_term'] = sanitize_text_field($_GET['utm_term']);
            }

            //Device information
            $defaults['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
            $defaults['device_form_factor'] = nebula_get_device('formfactor');
            $defaults['device_full'] = nebula_get_device('full');
            $defaults['device_brand'] = nebula_get_device('brand');
            $defaults['device_model'] = nebula_get_device('model');
            $defaults['device_type'] = nebula_get_device('type');
            $defaults['os_full'] = nebula_get_os('full');
            $defaults['os_name'] = nebula_get_os('name');
            $defaults['os_version'] = nebula_get_os('version');
            $defaults['browser_full'] = nebula_get_browser('full');
            $defaults['browser_name'] = nebula_get_browser('name');
            $defaults['browser_version'] = nebula_get_browser('version');
            $defaults['browser_engine'] = nebula_get_browser('engine');
            $defaults['browser_type'] = nebula_get_browser('type');

            return $defaults;
        }

        //Calculate and update the visitor with a score
        public function calculate_visitor_score($id=null){
            if ( nebula_option('visitors_db') ){
                global $wpdb;

                //Which visitor data to calculate for?
                if ( !empty($id) && current_user_can('manage_options') ){ //If an ID is passed and the user is an admin, use the passed visitor ID (Note: ID is from the visitor DB- not WordPress ID).
                    $this_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE id = '" . $id . "'");
                } else { //Else, use the current visitor
                    $nebula_id = get_nebula_id();
                    if ( !empty($nebula_id) ){
                        $this_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE nebula_id = '" . $nebula_id . "'");
                    }
                }

                if ( $this_visitor ){
                    $this_visitor = (array) $this_visitor[0];

                    //A score of 100+ will prevent deletion
                    $point_values = array(
                        'notable_poi' => 100,
                        'known' => 100,
                        'hubspot_vid' => 100,
                        'notes' => 25,
                        'notable_download' => 10,
                        'pdf_view' => 10,
                        'internal_search' => 20,
                        'contact_method' => 75,
                        'ecommerce_addtocart' => 75,
                        'ecommerce_checkout' => 75,
                        'engaged_reader' => 10,
                        'contact_funnel' => 25,
                        'street_full' => 75,
                        'geo_latitude' => 75,
                        'video_play' => 10,
                        'video_engaged' => 25,
                        'video_finished' => 25,
                        'fb_like' => 20,
                        'fb_share' => 20,
                        'score_mod' => $this_visitor['score_mod'],
                    );

                    $score = ( $this_visitor['session_count'] > 1 )? $this_visitor['session_count'] : 0; //Start the score at the session count if higher than 1
                    foreach ( $this_visitor as $column => $value ){
                        if ( array_key_exists($column, $point_values) && !empty($value) ){
                            if ( $column == 'notes' && $value == 'This user tracked by IP and User Agent.' ){
                                continue;
                            }
                            $score += $point_values[$column];
                        }
                    }

                    return $score;
                }
            }

            return false;
        }

        //Remove expired visitors from the DB
        //This is only ran when Nebula Options are saved, and when *new* visitors are inserted.
        public function remove_expired_visitors(){
            if ( nebula_option('visitors_db') ){
                global $wpdb;
                $expiration_length = time()-2592000; //30 days
                $wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE last_modified_date < %d AND known = %d AND score < %d", $expiration_length, 0, 100));
            }
        }

        //Look up what columns currently exist in the nebula_visitors table.
        public function visitors_existing_columns(){
            global $wpdb;
            return $wpdb->get_col("SHOW COLUMNS FROM nebula_visitors", 0); //Returns an array of current table column names
        }

        //Query email address or Hubspot VID to see if the user is known
        public function check_if_known($send_to_hubspot=true){
            if ( nebula_option('visitors_db') ){
                global $wpdb;
                $nebula_id = get_nebula_id();

                $known_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE nebula_id LIKE '" . $nebula_id . "' AND (email_address REGEXP '.+@.+\..+' OR hubspot_vid REGEXP '^\d+$')");
                if ( !empty($known_visitor) ){
                    if ( !is_known() ){
                        nebula_update_visitor(array('known' => '1')); //Update to known visitor (if previously unknown)
                    }

                    $known_visitor_data = (array) $known_visitor[0];
                    if ( !empty($send_to_hubspot) ){
                        nebula_prep_data_for_hubspot_crm_delivery($known_visitor_data);
                    }
                    return true;
                }
            }

            return false;
        }

        //Lookup if this visitor is known
        public function is_known(){
            $known = nebula_get_visitor_data('known');
            if ( !empty($known) && ($known === 1 || $known === '1') ){ //@TODO "Nebula" 0: Figure out which of these is best
                return true;
            }

            return false;
        }

        //Prepare Nebula Visitor data to be sent to Hubspot CRM
        //This includes skipping empty fields, ignoring certain fields, and renaming others.
        public function prep_data_for_hubspot_crm_delivery($data){
            if ( nebula_option('hubspot_api') ){
                $data_for_hubspot = array();
                if ( !empty($data['hubspot_vid']) ){
                    $data_for_hubspot['hubspot_vid'] = $data['hubspot_vid'];
                }
                if ( !empty($data['email_address']) ){
                    $data_for_hubspot['email_address'] = $data['email_address'];
                }
                $data_for_hubspot['properties'] = array();

                $ignore_columns = array('id', 'known', 'last_modified_date', 'first_session', 'prev_session', 'last_session_id', 'current_session', 'hubspot_vid', 'bot', 'current_session_pageviews', 'score', 'expiration', 'street_number', 'street_name', 'zip_suffix', 'zip_full');
                $rename_columns = array(
                    'ip_address' => 'nebula_ip',
                    'ip_city' => 'nebula_ip_city',
                    'ip_region' => 'nebula_ip_region',
                    'ip_country' => 'nebula_ip_country',
                    'street_full' => 'address',
                    'state_abbr' => 'state',
                    'country_name' => 'country',
                    'zip_code' => 'zip',
                    'first_name' => 'firstname',
                    'last_name' => 'lastname',
                    'email_address' => 'email',
                    'phone_number' => 'phone',
                );

                foreach ( $data as $column => $value ){
                    //Skip empty column values
                    if ( empty($value) ){
                        continue;
                    }

                    //Ignore unnecessary columns
                    if ( in_array($column, $ignore_columns) ){
                        continue;
                    }

                    //Rename certain columns to Hubspot CRM notation
                    if ( array_key_exists($column, $rename_columns) ){
                        $column = $rename_columns[$column];
                    }

                    //Add the column/value to the Hubspot data array
                    $data_for_hubspot['properties'][] = array(
                        'property' => $column,
                        'value' => $value
                    );
                }

                nebula_send_to_hubspot($data_for_hubspot);
            }
        }

    }

}// End if class_exists check
