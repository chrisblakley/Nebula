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
                Nebula Visitor Admin Page
             ===========================*/

            if ( nebula_option('visitors_db') ) {
                //Add Visitors menu in admin
                add_action('admin_menu', array($this, 'admin_menu'));
            }

            //Get details for user
            add_action('wp_ajax_nebula_visitor_admin_detail', array( $this, 'admin_detail' ) );
            add_action('wp_ajax_nopriv_nebula_visitor_admin_detail', array( $this, 'admin_detail' ) );

            //Manually update data from the admin interface
            add_action('wp_ajax_nebula_ajax_manual_update_visitor', array( $this, 'ajax_manual_update_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_manual_update_visitor', array( $this, 'ajax_manual_update_visitor' ) );

            //Find similar visitors by IP or IP and User Agent
            add_action('wp_ajax_nebula_ajax_similar_visitors', array( $this, 'ajax_similar_visitors' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_similar_visitors', array( $this, 'ajax_similar_visitors' ) );

            //Manually delete user from the admin interface
            add_action('wp_ajax_nebula_ajax_manual_delete_visitor', array( $this, 'ajax_manual_delete_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_manual_delete_visitor', array( $this, 'ajax_manual_delete_visitor' ) );

            //Manually remove expired visitors from the admin interface
            add_action('wp_ajax_nebula_ajax_manual_remove_expired', array( $this, 'ajax_manual_remove_expired' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_manual_remove_expired', array( $this, 'ajax_manual_remove_expired' ) );

            //Manually remove visitors with a Lead Score of 0 (or less)
            add_action('wp_ajax_nebula_ajax_manual_remove_noscore', array( $this, 'ajax_manual_remove_noscore' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_manual_remove_noscore', array( $this, 'ajax_manual_remove_noscore' ) );

            //Manually delete the entire Nebula Visitor tables
            add_action('wp_ajax_nebula_ajax_drop_nv_table', array( $this, 'ajax_drop_nv_table' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_drop_nv_table', array( $this, 'ajax_drop_nv_table' ) );

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
            Nebula Visitor Admin Page
         ===========================*/

        //Nebula admin subpages
        public function admin_menu(){
            add_theme_page('Nebula Visitors Data', 'Nebula Visitors Data', 'manage_options', 'nebula_visitors_data', array( $this, 'admin_page' ) ); //Nebula Visitors Data page
        }

        //The Nebula Visitors Data page output
        public function admin_page(){
            global $wpdb;
            $all_visitors_data_head = $wpdb->get_results("SHOW columns FROM nebula_visitors"); //Get the column names from the primary table
            $all_visitors_data_head = (array) $all_visitors_data_head;
            $all_visitors_data = $wpdb->get_results("SELECT * FROM nebula_visitors"); //Get all data from the data table
            $all_visitors_data = (array) $all_visitors_data;

            if ( !empty($all_visitors_data) ): ?>
                <script>
                    jQuery(window).on('load', function(){
                        jQuery('#visitors_data').DataTable({
                            "aaSorting": [[12, "desc"]], //Default sort (column number)
                            "aLengthMenu": [[20, 50, 100, -1], [20, 50, 100, "All"]], //"Show X entries" dropdown. Values, Text
                            "iDisplayLength": 20, //Default entries shown (Does NOT need to match aLengthMenu).
                            "scrollX": true,
                            "scrollY": '65vh',
                            "scrollCollapse": true,
                            //"paging": false
                            "oLanguage": {"Filter": ""}, //"Search:" label text.
                            "aoColumns": [ //Column Options. Change column comments to headers for easy reference.
                                /* Seen UTC */          {"bVisible": false},
                                /* Modified UTC */      {"bVisible": false},
                                /* ID */                {"bVisible": false},
                                /* Nebula ID */         {className: "hide_column"},
                                /* GA CID */            {"bVisible": false},
                                /* IP Address */        {},
                                /* User Agent */        {},
                                /* Fingerprint */       {"bVisible": false},
                                /* Nebula Session ID */ {"bVisible": false},
                                /* Notable POI */       {},
                                /* Email Address */     {"bVisible": false},
                                /* Is Known */          {"bVisible": false},
                                /* Last Seen On */      {"iDataSort": 0},
                                /* Last Modified On */  {"iDataSort": 1, "bVisible": false},
                                /* Most Identifiable */ {},
                                /* Lead Score */        {},
                                /* Notes */             {},
                            ]
                        });

                        jQuery(document).on('click tap touch', '.dataTables_wrapper tbody td', function(){
                            if ( jQuery(this).closest('tr').hasClass('selected') ){
                                jQuery('.dataTables_wrapper tbody tr.selected').removeClass('selected');

                                jQuery('#user-details').html('<p class="select-a-visitor">Select a visitor on the left to view details.</p>');
                                jQuery('#user-details-filter-con').css('opacity', '0');
                            } else {
                                jQuery('.dataTables_wrapper tbody tr').removeClass('selected');
                                jQuery(this).closest('tr').addClass('selected');

                                jQuery('#user-details').html('<p><i class="fa fa-spinner fa-spin"></i> Loading details for that user...</p>');

                                jQuery.ajax({
                                    type: "POST",
                                    url: nebula.site.ajax.url,
                                    data: {
                                        nonce: nebula.site.ajax.nonce,
                                        action: 'nebula_visitor_admin_detail',
                                        data: jQuery.trim(jQuery(this).closest('tr').find('.nebula_id').text()),
                                    },
                                    success: function(response){
                                        jQuery('#user-details').html(response);
                                        jQuery('#user-details-filter-con').css('opacity', '1');
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown){
                                        //Error
                                    },
                                    timeout: 60000
                                });
                            }
                        });
                    });

                    //Filter details
                    jQuery(document).on('keyup', '#user-details-filter', function(){
                        keywordSearch('.detail-loop-container', '.detail-con', jQuery.trim(jQuery(this).val()));
                    });

                    //Change value to input field for manual updating
                    jQuery(document).on('click tap touch', 'a.edit-this-user-data', function(){
                        var currentActualValue = jQuery.trim(jQuery(this).closest('.user-data-value-con').find('.user-data-value').attr('data-actual'));
                        var currentVisualValue = jQuery.trim(jQuery(this).closest('.user-data-value-con').find('.user-data-value').text());

                        jQuery(this).closest('.user-data-value-con').html('<p class="user-data-value form-inline"><input id="user-data-old-value" type="hidden" value="' + currentVisualValue + '" data-actual="' + currentActualValue + '" /><input id="user-data-editing" class="form-control form-control-sm" type="text" value="' + currentActualValue + '" />&nbsp;<a class="btn btn-sm btn-brand update-user-data-btn" href="#">Update</a>&nbsp;<a class="btn btn-sm btn-danger update-user-data-cancel" href="#">Cancel</a>&nbsp;<i class="ajax-update-spinner fa fa-spinner fa-spin"></i><br/><small class="user-date-editing-note" style="display: block; width: 100%;">Edited values may get overwritten.</small></p>');

                        return false;
                    });

                    //Cancel editing
                    jQuery(document).on('click tap touch', '.update-user-data-cancel', function(){
                        var oldVisualValue = jQuery.trim(jQuery(this).closest('.user-data-value-con').find('#user-data-old-value').val());
                        var oldActualValue = jQuery.trim(jQuery(this).closest('.user-data-value-con').find('#user-data-old-value').attr('data-actual'));

                        jQuery(this).closest('.user-data-value-con').html('<span class="user-data-value" data-actual="' + oldActualValue + '">' + oldVisualValue + '</span>&nbsp;<a class="edit-this-user-data" href="#"><i class="fa fa-pencil"></i></a>');
                        return false;
                    });

                    //Add new data
                    jQuery(document).on('click tap touch', '#user-detail-add-new-data', function(){
                        oThis = jQuery(this);
                        var newLabel = jQuery('#new-user-data-label').val();
                        var newValue = jQuery('#new-user-data-value').val();
                        var safeLabel = newLabel.toLowerCase().replace(/([^a-z\s])/g, '').replace(/(\s)/g, '_');
                        var humanLabel = safeLabel.replace(/(_)/g, ' ');

                        oThis.closest('.new-data-con').find('.ajax-update-spinner').fadeIn();
                        jQuery.ajax({
                            type: "POST",
                            url: nebula.site.ajax.url,
                            data: {
                                nonce: nebula.site.ajax.nonce,
                                action: 'nebula_ajax_manual_update_visitor',
                                nid: jQuery('#detail-results').attr('data-nid'),
                                label: safeLabel,
                                value: newValue,
                            },
                            success: function(response){
                                //Success

                                oThis.closest('.new-data-con').find('.ajax-update-spinner').hide();
                                if ( response == 'success' ){
                                    //Append to list
                                    jQuery('.detail-loop-container').append('<div class="row detail-con"><div class="col-4"><p class="detail-label" data-label="' + safeLabel + '"><strong class="human-label">' + humanLabel + '</strong></p></div><div class="col-8"><p class="user-data-value-con"><span class="user-data-value" data-actual="' + newValue + '">' + newValue + '</span>&nbsp;<a class="edit-this-user-data" href="#"><i class="fa fa-pencil"></i></a></p></div></div>');

                                    jQuery('#new-user-data-label').val('');
                                    jQuery('#new-user-data-value').val('');
                                } else {
                                    //There was a database error.
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown){
                                //Error
                                oThis.closest('.new-data-con').find('.ajax-update-spinner').hide();
                            },
                            timeout: 60000
                        });

                        return false;
                    });

                    //Send updated detail value to DB
                    jQuery(document).on('click tap touch', '.update-user-data-btn', function(){
                        oThis = jQuery(this);
                        var newValue = jQuery.trim(oThis.closest('.user-data-value-con').find('#user-data-editing').val());

                        oThis.closest('.user-data-value-con').find('.ajax-update-spinner').fadeIn();
                        jQuery.ajax({
                            type: "POST",
                            url: nebula.site.ajax.url,
                            data: {
                                nonce: nebula.site.ajax.nonce,
                                action: 'nebula_ajax_manual_update_visitor',
                                nid: jQuery('#detail-results').attr('data-nid'),
                                label: oThis.closest('.detail-con').find('.detail-label').attr('data-label'),
                                value: newValue,
                            },
                            success: function(response){
                                //Success
                                oThis.closest('.user-data-value-con').find('.ajax-update-spinner').hide();
                                if ( response == 'success' ){
                                    oThis.closest('.user-data-value-con').html('<span class="user-data-value">' + newValue + '</span>&nbsp;<a class="edit-this-user-data" href="#"><i class="fa fa-pencil"></i></a>');
                                } else {
                                    oThis.closest('.user-data-value-con').find('.user-date-editing-note').text('There was a database error.');
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown){
                                //Error
                                oThis.closest('.user-data-value-con').find('.ajax-update-spinner').hide();
                                oThis.closest('.user-data-value-con').find('.user-date-editing-note').html('An AJAX error occurred:<br>' + textStatus + ' - ' + errorThrown);
                            },
                            timeout: 60000
                        });

                        return false;
                    });

                    //Find similar users
                    jQuery(document).on('click tap touch', '.similar-visitors-con .btn', function(){
                        jQuery.ajax({
                            type: "POST",
                            url: nebula.site.ajax.url,
                            data: {
                                nonce: nebula.site.ajax.nonce,
                                action: 'nebula_ajax_similar_visitors',
                                similar: jQuery(this).attr('data-similar'),
                                nid: jQuery(this).attr('data-nid'),
                            },
                            success: function(response){
                                //Success
                                console.debug(response);
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown){
                                //Error
                            },
                            timeout: 60000
                        });

                        return false;
                    });

                    //Delete individual visitor from the DB
                    jQuery(document).on('click tap touch', '#delete-this-user-data', function(){
                        if ( confirm("Are you sure you want to delete this user data? This can not be undone.") ){
                            jQuery('.delete-this-visitor-icon').removeClass('fa-trash').addClass('fa-spin fa-spinner');
                            jQuery.ajax({
                                type: "POST",
                                url: nebula.site.ajax.url,
                                data: {
                                    nonce: nebula.site.ajax.nonce,
                                    action: 'nebula_ajax_manual_delete_visitor',
                                    nid: jQuery('#detail-results').attr('data-nid'),
                                },
                                success: function(response){
                                    //Success
                                    console.debug(response);

                                    if ( response == 'success' ){
                                        window.location.reload();
                                    }
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown){
                                    //Error
                                },
                                timeout: 60000
                            });
                        }

                        return false;
                    });

                    //Remove expired visitors
                    jQuery(document).on('click tap touch', '#delete-expired-visitors', function(){
                        jQuery('.remove-expired-visitors-icon').removeClass('fa-trash').addClass('fa-spin fa-spinner');
                        jQuery.ajax({
                            type: "POST",
                            url: nebula.site.ajax.url,
                            data: {
                                nonce: nebula.site.ajax.nonce,
                                action: 'nebula_ajax_manual_remove_expired',
                            },
                            success: function(response){
                                //Success
                                if ( response == 'success' ){
                                    window.location.reload();
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown){
                                //Error
                            },
                            timeout: 60000
                        });

                        return false;
                    });

                    //Remove 0 score visitors
                    jQuery(document).on('click tap touch', '#delete-noscore-visitors', function(){
                        jQuery('.remove-noscore-visitors-icon').removeClass('fa-trash').addClass('fa-spin fa-spinner');
                        jQuery.ajax({
                            type: "POST",
                            url: nebula.site.ajax.url,
                            data: {
                                nonce: nebula.site.ajax.nonce,
                                action: 'nebula_ajax_manual_remove_noscore',
                            },
                            success: function(response){
                                //Success
                                if ( response == 'success' ){
                                    window.location.reload();
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown){
                                //Error
                            },
                            timeout: 60000
                        });

                        return false;
                    });

                    //Delete and disable Nebula Visitors DB
                    jQuery(document).on('click tap touch', '#delete-everything-and-disable', function(){
                        if ( confirm("Are you sure you want to delete all visitor data and disable this feature? This can not be undone.") ){
                            jQuery('.delete-all-data-icon').removeClass('fa-bomb').addClass('fa-spin fa-spinner');
                            jQuery.ajax({
                                type: "POST",
                                url: nebula.site.ajax.url,
                                data: {
                                    nonce: nebula.site.ajax.nonce,
                                    action: 'nebula_ajax_drop_nv_table',
                                },
                                success: function(response){
                                    //Success
                                    window.location = response;
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown){
                                    //Error
                                },
                                timeout: 60000
                            });
                        }

                        return false;
                    });
                </script>

                <div id="nebula-visitor-data" class="wrap">
                    <div class="row">
                        <div class="col-12">
                            <h2>Nebula Visitors Data</h2>
                            <?php
                                if ( !current_user_can('manage_options') && !is_dev() ){
                                    wp_die('You do not have sufficient permissions to access this page.');
                                }
                            ?>

                            <p>Lines in <em><strong>bold italic</strong></em> are your data. <span style="color: #5cb85c;">Green</span> lines are "known" visitors who have been identified.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="dataTables_wrapper">
                                <table id="visitors_data" class="display compact" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <td>Seen (UTC)</td>
                                            <td>Modified (UTC)</td>
                                            <?php foreach ( $all_visitors_data_head as $column_name ): ?>
                                                <td>
                                                    <?php
                                                        $column_name = (array) $column_name;
                                                        echo ucwords(str_replace('_', ' ', $column_name['Field'])); //what is this?
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $your_nebula_id = nebula_vdb_get_appropriate_nebula_id(); ?>
                                        <?php foreach ( $all_visitors_data as $visitor_data ): ?>
                                            <?php
                                                $visitor_data = (array) $visitor_data;
                                                $row_class = '';
                                                if ( $visitor_data['nebula_id'] === $your_nebula_id ){
                                                    $row_class .= 'you ';
                                                }

                                                if ( $visitor_data['is_known'] == '1' ){
                                                    $row_class .= 'known ';
                                                }
                                            ?>
                                            <tr class="ajaxrow <?php echo $row_class; ?>">
                                                <td><?php echo $visitor_data['last_seen_on']; ?></td>
                                                <td><?php echo $visitor_data['last_modified_on']; ?></td>
                                                <?php foreach ( $visitor_data as $column => $value ): ?>
                                                    <td class="<?php echo $column; ?>">
                                                        <?php if ( $column == 'notes' && !empty($value) ): ?>
                                                            <div><i class="fa fa-sticky-note" title="<?php echo sanitize_text_field(trim($value)); ?>" style="color: #f7dd00;"></i></div>
                                                        <?php else: ?>
                                                            <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;">
                                                                <?php
                                                                    if ( is_utc_timestamp($value) ){
                                                                        echo date('F j, Y @ g:ia', intval($value)); //For Last Modified timestamp
                                                                    } else {
                                                                        echo sanitize_text_field(mb_strimwidth($value, 0, 153, '...'));
                                                                    }
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="dangerous-stuff">
                                <div class="row">
                                    <div class="col-7">
                                        <p><i class="delete-all-data-icon fa fa-bomb"></i> <a id="delete-everything-and-disable" class="danger-link" href="#">Delete all data and disable Nebula Visitor DB</a></p>
                                    </div>
                                    <div class="col-5">
                                        <p class="text-right"><i class="remove-expired-visitors-icon fa fa-trash"></i> <a id="delete-expired-visitors" class="danger-link" href="#">Remove expired visitors</a></p>
                                        <p class="text-right"><i class="remove-noscore-visitors-icon fa fa-trash"></i> <a id="delete-noscore-visitors" class="danger-link" href="#">Remove visitors with a score of 0 (or less)</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="user-details-filter-con" class="text-right">Filter: <input id="user-details-filter" class="form-control" type="text" /></div>

                            <div id="user-details-con">
                                <div class="user-title-bar">
                                    <p><i class="fa fa-id-card"></i> <strong>Visitor Details</strong></p>
                                </div>

                                <div id="user-details"><p class="select-a-visitor">Select a visitor on the left to view details.</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="wrap">
                    <h2>Nebula Visitors Data</h2>
                    <p><strong>Nebula Visitors table is empty!</strong><br/>Wait for new visitors, or even <a href="<?php echo home_url('/'); ?>" target="_blank">visit your website yourself</a> then refresh this page for data to appear.</p>

        <!--
                    <div id="dangerous-stuff">
                        <p><i class="delete-all-data-icon fa fa-bomb"></i> <a id="delete-everything-and-disable" class="danger-link" href="#">Delete all data and disable Nebula Visitor DB</a></p>
                    </div>
        -->
                </div>
            <?php endif;
        }

        //Get details for user
        public function admin_detail(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            nebula_vdb_update_visitor_data(false, true, sanitize_text_field($_POST['data'])); //Update the score withou otherwise affecting data.

            global $wpdb;
            $this_user_data = $wpdb->get_results("SELECT id, label, value FROM nebula_visitors_data WHERE nebula_id = '" . sanitize_text_field($_POST['data']) . "' ORDER BY id");
            if ( empty($this_user_data) ){
                echo '<p>Data was not found for this user...</p>';
            }

            $organized_data = array();
            foreach ( $this_user_data as $index => $value ){
                $label = $this_user_data[$index]->label;

                $unserialized_value = $this_user_data[$index]->value;
                if ( is_serialized($unserialized_value) ){
                    $unserialized_value = unserialize($unserialized_value);
                }
                $organized_data[$label] = $unserialized_value;
            }
            ?>
                <div id="detail-results" data-nid="<?php echo sanitize_text_field($_POST['data']); ?>">
                    <div>
                        <?php if ( $organized_data['nebula_id'] == nebula_vdb_get_appropriate_nebula_id() ): ?>
                            <div class="visitor-notice-item">
                                <p><i class="fa fa-fw fa-smile-o" style="color: blue;"></i> This visitor is you!</p>
                            </div>
                        <?php endif; ?>

                        <?php if ( !empty($organized_data['notices']) ): ?>
                            <div class="visitor-notice-item">
                                <p><i class="fa fa-fw fa-exclamation-triangle" style="color: orange;"></i> Display notices here</p>
                            </div>
                        <?php endif; ?>

                        <?php if ( !empty($organized_data['notes']) ): ?>
                            <div class="visitor-notice-item">
                                <p><i class="fa fa-fw fa-sticky-note" style="color: #f7dd00;"></i> <?php echo $organized_data['notes']; ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ( !empty($organized_data['photo']) ): ?>
                            <p><img src="<?php echo ( is_array($organized_data['photo']) )? end($organized_data['photo']) : $organized_data['photo']; ?>" style="width: 100%;" /></p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <h3 class="text-center">Lead Score: <strong><?php echo $organized_data['lead_score']; ?></strong></h3>
                        <table id="score-legend">
                            <tr>
                                <td style="color: #5cb85c;">Demographic</td>
                                <td style="color: #0275d8;">Behavior</td>
                                <td style="color: #f0ad4e;">Modifier</td>
                            </tr>
                        </table>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($organized_data['demographic_score']/$organized_data['lead_score'])*100; ?>%;" aria-valuenow="<?php echo $organized_data['demographic_score']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $organized_data['lead_score']; ?>"><?php echo $organized_data['demographic_score']; ?></div>
                            <div class="progress-bar bg-brand" role="progressbar" style="width: <?php echo ($organized_data['behavior_score']/$organized_data['lead_score'])*100; ?>%;" aria-valuenow="<?php echo $organized_data['behavior_score']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $organized_data['lead_score']; ?>"><?php echo $organized_data['behavior_score']; ?></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($organized_data['score_mod']/$organized_data['lead_score'])*100; ?>%;" aria-valuenow="<?php echo $organized_data['score_mod']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $organized_data['lead_score']; ?>"><?php echo $organized_data['score_mod']; ?></div>
                        </div>
                    </div>

                    <div class="detail-loop-container">
                        <?
                            foreach ( $this_user_data as $index => $value ):
                                $unserialized_value = $this_user_data[$index]->value;
                                if ( is_serialized($unserialized_value) ){
                                    $unserialized_value = unserialize($unserialized_value);
                                }
                        ?>
                            <div class="row detail-con">
                                <div class="col-4">
                                    <p class="detail-label" data-label="<?php echo $this_user_data[$index]->label; ?>">
                                        <strong class="human-label"><?php echo str_replace('_', ' ', $this_user_data[$index]->label); ?></strong>
                                        <div class="hidden"><?php echo $this_user_data[$index]->label; ?></div>
                                    </p>
                                </div>
                                <div class="col-8">
                                    <div class="user-data-value-con">
                                        <?php if ( is_array($unserialized_value) && count($unserialized_value) == 1 ){ //Convert single array datapoints to strings
                                            $unserialized_value = implode('', $unserialized_value);
                                        }

                                        if ( is_array($unserialized_value) ): ?>
                                            <ol start="0">
                                                <?php foreach ( $unserialized_value as $value ): ?>
                                                    <li class="user-data-value" data-actual="<?php echo htmlspecialchars($value); ?>">
                                                        <span>
                                                            <?php
                                                                if ( is_utc_timestamp($value) ){
                                                                    echo date('l, F j, Y @ g:ia', intval($value));
                                                                } else {
                                                                    echo htmlspecialchars($value);
                                                                }
                                                            ?>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ol>
                                        <?php else: ?>
                                            <span class="user-data-value" data-actual="<?php echo htmlspecialchars($unserialized_value); ?>">
                                                <?php
                                                    if ( is_utc_timestamp($unserialized_value) ){
                                                        echo date('l, F j, Y @ g:ia', $unserialized_value);
                                                    } else {
                                                        echo htmlspecialchars($unserialized_value);
                                                    }
                                                ?>
                                            </span>
                                            <?php if ( !nebula_vdb_is_protected_label($this_user_data[$index]->label) ): ?>
                                                &nbsp;<a class="edit-this-user-data" href="#"><i class="fa fa-pencil"></i></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="detail-new-row">
                        <p>
                            <h4>Add New</h4>
                            <p><small>Avoid using special characters in the label.</small></p>
                        </p>
                        <div class="row new-data-con">
                            <div class="col-4">
                                <div class="form-inline">
                                    <input id="new-user-data-label" class="form-control" type="text" placeholder="Label" />
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-inline">
                                    <input id="new-user-data-value" class="form-control" type="text" placeholder="Value" />&nbsp;<a id="user-detail-add-new-data" class="btn btn-brand" href="#">Add</a>&nbsp;<i class="ajax-update-spinner fa fa-spinner fa-spin"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="similar-visitors-con" style="margin-top: 30px;">
                        <h4>Find similar visitors</h4>
                        <p><small>Note: These run queries, but don't update anything on this page yet...</small></p>
                        <a class="btn btn-brand" data-similar="ip" data-nid="<?php echo $organized_data['nebula_id']; ?>" href="#">IP Address</a> <a class="btn btn-brand" data-similar="ip_useragent" data-nid="<?php echo $organized_data['nebula_id']; ?>" href="#">IP and User Agent</a></p>
                    </div>

                    <div class="useful-queries" style="margin-top: 30px;">
                        <h4>Useful Queries</h4>
                        <p><small>These queries can be pasted into phpMyAdmin to facilitate more advanced customization.</small></p>
                        <div>
                            <strong>Select this user</strong><br/>
                            <div class="nebula-code-con clearfix mysql">
                                <pre class="nebula-code mysql">SELECT * FROM nebula_visitors_data
        WHERE nebula_id = '<?php echo $organized_data['nebula_id']; ?>'</pre>
                            </div>
                        </div>
                        <div>
                            <strong>Select similar users by IP</strong><br/><?php //@TODO "Nebula" 0: Can't edit user_agent with this query... ?>
                            <div class="nebula-code-con clearfix mysql">
                                <pre class="nebula-code mysql">SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.*
        FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id
        WHERE (nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value = '<?php echo $organized_data['ip_address']; ?>') OR (nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value = '<?php echo $organized_data['ip_address']; ?>')</pre>
                            </div>
                        </div>
                        <div>
                            <strong>Select similar users by IP + User Agent</strong><br/><?php //@TODO "Nebula" 0: Can't edit user_agent with this query... ?>
                            <div class="nebula-code-con clearfix mysql">
                                <pre class="nebula-code mysql">SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.*
        FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id
        WHERE ((nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value = '<?php echo $organized_data['ip_address']; ?>') OR (nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value = '<?php echo $organized_data['ip_address']; ?>'))
        AND nebula_visitors.user_agent = '<?php echo $organized_data['user_agent']; ?>'</pre>
                            </div>
                        </div>
                        <div>
                            <strong>Select similar users by Fingerprint</strong><br/><?php //@TODO "Nebula" 0: Can't edit user_agent with this query... ?>
                            <div class="nebula-code-con clearfix mysql">
                                <pre class="nebula-code mysql">SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.*
        FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id
        WHERE nebula_visitors.fingerprint = '<?php echo $organized_data['fingerprint']; ?>'</pre>
                            </div>
                        </div>
                    </div>

                    <div id="dangerous-stuff">
                        <p class="text-right"><i class="delete-this-visitor-icon fa fa-trash"></i> <a id="delete-this-user-data" class="danger-link" href="#">Delete this visitor</a></p>
                    </div>
                </div>
            <?php
            wp_die();
        }

        //Manually update data from the admin interface
        public function ajax_manual_update_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            $nebula_id = sanitize_text_field($_POST['nid']);
            $label = sanitize_text_field(strtolower(preg_replace(array('/([^a-zA-Z_])/', '/(\s)/'), array('', '_'), $_POST['label'])));
            $value = sanitize_text_field($_POST['value']);

            if ( nebula_vdb_is_protected_label($label) ){
                wp_die('That is a protected label.');
            }

            $manual_update = nebula_vdb_update_visitor_data(array($label => $value), true, $nebula_id);
            if ( $manual_update === false ){
                echo 'error';
            } else {
                echo 'success';
            }

            wp_die();
        }

        //Find similar visitors by IP or IP and User Agent
        public function ajax_similar_visitors(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            $all_visitor_data = nebula_vdb_get_all_visitor_data('any', sanitize_text_field($_POST['nid']));
            $ip_address = $all_visitor_data['ip_address'];
            $user_agent = $all_visitor_data['user_agent'];

            $query = "SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.* FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id WHERE (nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value LIKE '%" . $ip_address . "%') OR (nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value LIKE '%" . $ip_address . "%')";
            if ( $_POST['similar'] == 'ip_useragent' ){
                $query = "SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.* FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id WHERE ((nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value LIKE '%" . $ip_address . "%') OR (nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value LIKE '%" . $ip_address . "%')) AND nebula_visitors.user_agent = '" . $user_agent . "'";
            }

            global $wpdb;
            $similar_visitors = $wpdb->get_results($query);

            if ( $similar_visitors === false ){
                echo 'error';
            } else {
                echo "success:\r\n";
                var_export( $similar_visitors );
                //@todo "Nebula" 0: loop through result object and put it into the main datatable... somehow...
            }

            wp_die();
        }

        //Manually delete user from the admin interface
        public function ajax_manual_delete_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            if ( !current_user_can('manage_options') ){
                wp_die('You do not have permissions to remove visitor data.');
            }

            $nebula_id = sanitize_text_field($_POST['nid']);

            global $wpdb;
            $manual_delete = $wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE nebula_id = %s", $nebula_id)); //Associated data in nebula_visitors_data will cascade delete

            if ( $manual_delete === false ){
                echo 'error';
            } else {
                echo 'success';
            }

            wp_die();
        }


        //Manually remove expired visitors from the admin interface
        public function ajax_manual_remove_expired(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            $manual_remove_expired = nebula_vdb_remove_expired(true);

            if ( $manual_remove_expired === false ){
                echo 'error';
            } else {
                echo 'success';
            }

            wp_die();
        }

        //Manually remove visitors with a Lead Score of 0 (or less)
        public function ajax_manual_remove_noscore(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            global $wpdb;
            $manual_delete = $wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE lead_score <= %d", 0)); //Associated data in nebula_visitors_data will cascade delete

            if ( $manual_delete === false ){
                echo 'error';
            } else {
                echo 'success';
            }

            wp_die();
        }


        //Manually delete the entire Nebula Visitor tables
        public function ajax_drop_nv_table(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
            if ( !current_user_can('manage_options') ){
                wp_die('You do not have permissions to do that.');
            }

            global $wpdb;
            $remove_nv_table = $wpdb->query("DROP TABLE nebula_visitors_data");
            $remove_nv_table = $wpdb->query("DROP TABLE nebula_visitors");
            nebula_update_option('visitors_db', 'disabled');

            echo admin_url();

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
