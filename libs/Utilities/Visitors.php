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

if( !trait_exists( 'Visitors' ) ) {

    trait Visitors {

		//Commented out temporarily:
        public function hooks() {

			//Nebula Visitor Admin Page

            if ( nebula()->option('visitors_db') ) {
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


			//Nebula Visitor Data

             //The controller for Nebula Visitors DB process.
            //Triggering at get_header allows for template_redirects to happen before fingerprinting (prevents false multipageviews)
            add_action('get_header', array( $this, 'controller' ), 11);

            //Check if the Nebula ID exists on load and generate/store a new one if it does not.
            add_action('init', array( $this, 'check_nebula_id' ), 11);

            //Retrieve User Data
            add_action('wp_ajax_nebula_vdb_ajax_get_visitor_data', array( $this, 'ajax_get_visitor_data' ) );
            add_action('wp_ajax_nopriv_nebula_vdb_ajax_get_visitor_data', array( $this, 'ajax_get_visitor_data' ) );

            //Vague Data - Only update if it doesn't already exist in the DB
            add_action('wp_ajax_nebula_ajax_vague_visitor', array( $this, 'ajax_vague_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_ajax_vague_visitor', array( $this, 'ajax_vague_visitor' ) );

            //Update Visitor Data
            add_action('wp_ajax_nebula_vdb_ajax_update_visitor', array( $this, 'ajax_update_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_vdb_ajax_update_visitor', array( $this, 'ajax_update_visitor' ) );

            //Append to Visitor Data
            add_action('wp_ajax_nebula_vdb_ajax_append_visitor', array( $this, 'ajax_append_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_vdb_ajax_append_visitor', array( $this, 'ajax_append_visitor' ) );

            //Remove data from the Nebula Visitor DB
            add_action('wp_ajax_nebula_vdb_ajax_remove_datapoint', array( $this, 'ajax_remove_datapoint' ) );
            add_action('wp_ajax_nopriv_nebula_vdb_ajax_remove_datapoint', array( $this, 'ajax_remove_datapoint' ) );

            //Increment Visitor Data
            add_action('wp_ajax_nebula_vdb_ajax_increment_visitor', array( $this, 'ajax_increment_visitor' ) );
            add_action('wp_ajax_nopriv_nebula_vdb_ajax_increment_visitor', array( $this, 'ajax_increment_visitor' ) );
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
                                        <?php $your_nebula_id = $this->get_appropriate_nebula_id(); ?>
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
                                                                    if ( nebula()->is_utc_timestamp($value) ){
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

            $this->update_visitor_data(false, true, sanitize_text_field($_POST['data'])); //Update the score withou otherwise affecting data.

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
                        <?php if ( $organized_data['nebula_id'] == $this->get_appropriate_nebula_id() ): ?>
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
                        <?php foreach ( $this_user_data as $index => $value ): ?>
                        <?
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
                                            <?php if ( !$this->is_protected_label($this_user_data[$index]->label) ): ?>
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

            if ( $this->is_protected_label($label) ){
                wp_die('That is a protected label.');
            }

            $manual_update = $this->update_visitor_data(array($label => $value), true, $nebula_id);
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

            $all_visitor_data = $this->get_all_visitor_data('any', sanitize_text_field($_POST['nid']));
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

            $manual_remove_expired = $this->remove_expired(true);

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
            nebula()->update_option('visitors_db', 'disabled');

            echo admin_url();

            wp_die();
        }

        /*==========================
            Nebula Visitor Data
         ===========================*/

        //The controller for Nebula Visitors DB process.
        //Triggering at get_header allows for template_redirects to happen before fingerprinting (prevents false multipageviews)
         public function controller(){
            $override = apply_filters('pre_nebula_vdb_controller', false);
            if ( $override !== false ){return $override;}

            if ( !nebula()->option('visitors_db') || nebula()->is_ajax_request() || nebula()->is_bot() || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'wordpress') !== false ){
                return false; //Don't add bots to the DB
            }

            //Create the NVDB Tables
            if ( nebula()->is_admin_page() && isset($_GET['settings-updated']) && is_staff() ){ //Only trigger this in admin when Nebula Options are saved (by a staff member)
                $this->create_tables();
            }

            //Only run on front-end
            if ( !nebula()->is_admin_page() && !nebula()->is_login_page() ){
                $returning_visitor = $this->is_returning_visitor();
                $nebula_id = $this->get_appropriate_nebula_id($returning_visitor);

                $treat_as_new_visitor = true; //Prep this until detected as returning
                if ( $returning_visitor ){ //Returning Visitor
                    $treat_as_new_visitor = false;

                    $all_visitor_data = $this->get_all_visitor_data('fresh');
                    if ( !empty($all_visitor_data) ){
                        $this->returning_visitor();
                    } else {
                        $treat_as_new_visitor = true; //Run failsafe because that visitor doesn't have a DB row (even though they are returning)
                    }
                }

                if ( $treat_as_new_visitor ){ //New User (or returning with no DB row)
                    $built_visitor_data = $this->build_new_visitor_data_object(); //Run procedure for new user.

                    if ( $returning_visitor ){ //This is true if visitor is returning, but did not have a DB row (so must be inserted as new).
                        $built_visitor_data['is_first_session'] = 0;
                        $built_visitor_data['is_new_user'] = 0;
                        $built_visitor_data['is_returning_user'] = 1;
                    }

                    $this->insert_visitor($built_visitor_data);
                }

                //Check if this visitor is similar to any known visitors (if not already)
                if ( !$this->get_visitor_datapoint('similar_to_known_ip_ua') ){
                    $similar_ip_known_visitor = $this->similar_to_known(); //If current visitor IP is similar to a known visitor
                    if ( !empty($similar_ip_known_visitor) ){
                        $similar_ip_ua_known_visitor = $this->similar_to_known(true); //If current visitor IP + User Agent is similar to a known visitor
                        if ( !empty($similar_ip_ua_known_visitor) ){
                            $this->update_visitor_data(array('similar_to_known_ip_ua' => $similar_ip_ua_known_visitor), false);
                        }

                        $this->update_visitor_data(array('similar_to_known_ip' => $similar_ip_known_visitor));
                    }
                }

                $this->remove_expired();
            }
        }

        //Create Users Table with minimal default columns.
        public function create_tables(){
            global $wpdb;

            $visitors_table = $wpdb->query("SHOW TABLES LIKE 'nebula_visitors'"); //DB Query here
            if ( empty($visitors_table) ){
                $create_primary_table = $wpdb->query("CREATE TABLE nebula_visitors (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nebula_id VARCHAR(255),
                    ga_cid TINYTEXT NOT NULL,
                    ip_address TINYTEXT NOT NULL,
                    user_agent TEXT NOT NULL,
                    fingerprint LONGTEXT,
                    nebula_session_id TEXT NOT NULL,
                    notable_poi TINYTEXT,
                    email_address TINYTEXT,
                    is_known BOOLEAN NOT NULL,
                    last_seen_on INT(11) NOT NULL,
                    last_modified_on INT(11) NOT NULL,
                    most_identifiable TINYTEXT,
                    lead_score INT(6),
                    notes LONGTEXT,
                    PRIMARY KEY (id),
                    UNIQUE (nebula_id)
                ) ENGINE = InnoDB;"); //DB Query here

                //Create the data table
                    //Must have unique combination of nebula_id and label
                    //References the nebula_id from the primary table, so if a row is deleted from there it deletes all rows with the same nebula_id here.
                $create_data_table = $wpdb->query("CREATE TABLE nebula_visitors_data (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nebula_id VARCHAR(255),
                    label VARCHAR(255),
                    value LONGTEXT,
                    PRIMARY KEY (id),
                    UNIQUE (nebula_id, label),
                    CONSTRAINT fk_nebula_visitors_nebula_id FOREIGN KEY (nebula_id) REFERENCES nebula_visitors (nebula_id) ON DELETE CASCADE
                )
                ENGINE = InnoDB;"); //DB Query here
            }
        }

        //Check if the Nebula ID exists on load and generate/store a new one if it does not.
        public function check_nebula_id(){
            if ( nebula()->is_bot() || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'wordpress') !== false ){
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
            if ( nebula()->option('visitors_db') ){
                $last_session_id = nebula()->get_visitor_data('last_session_id'); //Check if this returning visitor exists in the DB (in case they were removed)
                if ( empty($last_session_id) ){ //If the nebula_id is not in the DB already, treat it as a new user
                    //Prevent duplicates for users blocking cookies or Google Analytics
                    if ( strpos(nebula()->ga_parse_cookie(), '-') !== false ){ //If GA CID was generated by Nebula
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
                    if ( nebula()->nebula_session_id() != $last_session_id ){ //New session
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
                $_COOKIE['nid'] = nebula()->version('full') . '.' . bin2hex(openssl_random_pseudo_bytes(5)) . '.' . uniqid(); //Update to random_bytes() when moving to PHP7
            }

            $nid_expiration = strtotime('January 1, 2035'); //Note: Do not let this cookie expire past 2038 or it instantly expires.
            setcookie('nid', $_COOKIE['nid'], $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Store the Nebula ID as a cookie called "nid".

            if ( nebula()->option('visitors_db') ){
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
        public function visitors_create_missing_columns($all_data){
            if ( nebula()->option('visitors_db') ){
                $existing_columns = nebula()->visitors_existing_columns(); //Returns an array of current table column names

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

        //Check if a certain label is protected from manual changes
        //Add more labels using this example hook:
        /*
            add_filter('nebula_vdb_protected_labels', 'project_protected_labels');
            function project_protected_labels($array){
                array_push($array, 'my_label_1', 'another_example_label', 'and_so_on'));
                return $array;
            }
        */
        public function is_protected_label($label){
            $default_protected_labels = array('id', 'nebula_id', 'ga_cid', 'lead_score', 'demographic_score', 'behavior_score');

            $additional_protected_labels = apply_filters('nebula_vdb_protected_labels', array());
            $all_protected_labels = array_merge($default_protected_labels, $additional_protected_labels);

            if ( in_array($label, $all_protected_labels) ){
                return true;
            }

            return false;
        }

        //Procedure for new or returning visitors
        public function returning_visitor(){
            if ( $this->is_same_session() ){ //Same Session
                $session_start_time = $this->get_visitor_datapoint('current_session_on');

                $batch_update_data = $this->update_visitor_data(array(
                    'current_session_duration' => ( !empty($session_start_time) )? round(time()/60)*60-$session_start_time : 0,
                    'last_seen_on' => round(time()/60)*60,
                    'is_non_bounce' => '1',
                ), false);

                if ( $this->is_page_refresh() ){
                    $batch_update_data = $this->increment_visitor_data('total_refreshes', false);
                } else {
                    $batch_update_data = $this->increment_visitor_data(array('current_session_pageviews', 'total_pageviews'), false);

                    $batch_update_data = $this->append_visitor_data(array(
                        'last_page_viewed' => nebula()->url_components('all'),
                        'all_ip_addresses' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                        'all_notable_pois' => nebula()->poi(),
                        'acquisition_channel' => $this->detect_acquisition_channel(),
                        'referrer' => ( $this->is_external_referrer() )? $_SERVER['HTTP_REFERER'] : '',
                    ), false);
                }

                $this->send_all_to_cache_and_db($batch_update_data); //Update everything as batch here
            } else { //New Session
                //Calculate time since last session
                $last_session_date = $this->get_visitor_datapoint('prev_sessions');
                $time_since_last_session = time()-$last_session_date;
                $batch_update_data = $this->update_visitor_data(array(
                    'current_session_pageviews' => 0,
                    'is_first_session' => 0,
                    'is_new_user' => 0,
                    'is_returning_user' => 1,
                    'time_since_last_session' => $time_since_last_session,
                    'last_seen_on' => round(time()/60)*60,
                    'acquisition_keywords' => $this->get_referrer_search_terms(),
                    'is_homescreen_app' => ( isset($_GET['hs']) )? '1' : false, //Don't set false condition to prevent overwriting.
                ), false);
                $batch_update_data = $this->append_visitor_data(array(
                    'prev_session_on' => round(time()/60)*60, //Rounded to the nearest minute
                    'acquisition_channel' => $this->detect_acquisition_channel(),
                    'referrer' => ( $this->is_external_referrer() )? $_SERVER['HTTP_REFERER'] : '',
                ), false);
                $batch_update_data = $this->increment_visitor_data('session_count', false);

                $this->send_all_to_cache_and_db($batch_update_data); //Update everything as batch here
            }
        }

        //Detect acquisition method
        public function detect_acquisition_channel(){
            //Check for campaign URL
            if ( isset($_GET['utm_campaign']) ){ //utm_campaign
                return 'Campaign (' . $_GET['utm_campaign'] . ')';
            }

            if ( $this->is_external_referrer() ){
                $hostname = nebula()->url_components('host', $_SERVER['HTTP_REFERER']);

                //Check Email
                if ( $this->is_email_referrer($_SERVER['HTTP_REFERER']) ){
                    return 'Email';
                }

                //Check social
                if ( $this->is_social_network($_SERVER['HTTP_REFERER']) ){
                    return 'Social (' . $hostname . ')';
                }

                //Check search
                if ( $this->is_search_engine($_SERVER['HTTP_REFERER']) ){
                    return 'Search (' . $hostname . ')';
                }

                return 'Referral (' . $hostname . ')';
            }

            return 'Direct (or Unknown)';
        }

        //Check if referrer is an email app
        public function is_email_referrer($referrer=false){
            if ( nebula()->url_components('protocol', $referrer) == 'android-app' ){ //Gmail App on Android
                return true;
            }

            if ( isset($_GET['utm_medium']) && strtolower($_GET['utm_medium']) == 'email' ){ //Email campaigns
                return true;
            }

            return false;
        }

        //Check if hostname is a social network
        public function is_social_network($url){
            $sld = nebula()->url_components('sld', $url);

            $social_hostnames = array('facebook.com', 'twitter.com', 't.co', 'linkedin.com', 'reddit.com', 'plus.google.com', 'pinterest.com', 'tumblr.com', 'digg.com', 'stumbleupon.com', 'yelp.com');
            foreach ( $social_hostnames as $social_hostname ){
                if ( preg_match('/^' . $sld . '\./', $social_hostname) ){
                    return true;
                }
            }

            return false;
        }

        //Check if hostname is a seach engine
        public function is_search_engine($url){
            $sld = nebula()->url_components('sld', $url);

            $search_engine_hostnames = array('google.com', 'bing.com', 'yahoo.com', 'baidu.com', 'aol.com', 'ask.com', 'excite.com', 'duckduckgo.com', 'yandex.com', 'lycos.com', 'chacha.com');
            foreach ( $search_engine_hostnames as $search_engine_hostname ){
                if ( preg_match('/^' . $sld . '\./', $search_engine_hostname) ){
                    return true;
                }
            }

            return false;
        }

        //Check for external referrer
        public function is_external_referrer(){
            if ( !empty($_SERVER['HTTP_REFERER']) && nebula()->url_components('domain', $_SERVER['HTTP_REFERER']) != nebula()->url_components('domain') ){
                return true;
            }

            return false;
        }

        //Figure out which nebula ID to use in priority order
        public function get_appropriate_nebula_id($check_db=true){
            //From global variable
            if ( !empty($GLOBALS['nebula_id']) ){
                return $GLOBALS['nebula_id'];
            }

            //From Session
            if ( isset($_SESSION) && !empty($_SESSION['nebula_id']) ){
                $this->force_nebula_id_to_visitor($_SESSION['nebula_id']);
                return $_SESSION['nebula_id'];
            }

            //From Cookie
            $nebula_id_from_cookie = $this->get_nebula_id_from_cookie();
            if ( !empty($nebula_id_from_cookie) ){
                $this->force_nebula_id_to_visitor($nebula_id_from_cookie);
                return $nebula_id_from_cookie;
            }

            //From DB (by matching GA CID)
            if ( $check_db && nebula()->option('visitors_db') ){ //Only run if confirmed returning visitor
                $nebula_id_from_ga_cid = $this->get_previous_nebula_id_by_ga_cid();
                if ( !empty($nebula_id_from_ga_cid) ){
                    $this->force_nebula_id_to_visitor($nebula_id_from_ga_cid);
                    return $nebula_id_from_ga_cid;
                }
            }

            //Generate a new Nebula ID
            $generated_nebula_id = $this->generate_nebula_id();
            $this->force_nebula_id_to_visitor($generated_nebula_id);
            return $generated_nebula_id;
        }

        //Check if the visitor is returning
        public function is_returning_visitor(){
            //From global variable
            if ( !empty($GLOBALS['nebula_id']) ){
                return true;
            }

            //From Session
            if ( isset($_SESSION) && !empty($_SESSION['nebula_id']) ){
                return true;
            }

            //From Cookie
            $nebula_id_from_cookie = $this->get_nebula_id_from_cookie();
            if ( !empty($nebula_id_from_cookie) ){
                return true;
            }

            //From DB
            if ( nebula()->option('visitors_db') ){
                global $wpdb;

                //Check for an old visitor with the same fingerprint
                //@TODO "Nebula" 0: I don't think the server-side fingerprint alone is unique enough to push this live... Really needs the JS detections to work, but I can't think of a way that isn't AJAX JS or a second pageview to match against it...
                $unique_new_visitor = $wpdb->get_results("SELECT ga_cid FROM nebula_visitors WHERE (ip_address = '" . sanitize_text_field($_SERVER['REMOTE_ADDR']) . "' AND user_agent = '" . sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . "') OR fingerprint LIKE '%" . sanitize_text_field($this->fingerprint()) . "%'"); //DB Query here
                if ( !empty($unique_new_visitor) ){

                    //@TODO "Nebula" 0: This uses the first result... We want to find a user that is known, or that has a GA CID, else highest score.
                    $unique_new_visitor = (array) $unique_new_visitor[0];

                    $this->force_nebula_id_to_visitor($unique_new_visitor['nebula_id']); //Give this user the same Nebula ID
                    $this->append_visitor_data(array('notices' => 'This user was tracked by fingerprint.'), false);
                    return true;
                }
            }

            return false;
        }

        //Check if the GA CID was generated by Nebula (instead of Google Analytics)
        public function is_nebula_generated_cid(){
            if ( strpos(nebula()->ga_parse_cookie(), '-') !== false ){
                return true;
            }

            return false;
        }

        //Store the Nebula ID in a cookie
        public function force_nebula_id_to_visitor($forced_nebula_id){
            //Global
            $GLOBALS['nebula_id'] = $forced_nebula_id;

            //Session
            $_SESSION['nebula_id'] = $forced_nebula_id;

            //Cookie
            $_COOKIE['nid'] = $forced_nebula_id;
            $nid_expiration = strtotime('January 1, 2035'); //Note: Do not let this cookie expire past 2038 or it instantly expires.
            if ( !headers_sent() ){
                setcookie('nid', $forced_nebula_id, $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Store the Nebula ID as a cookie called "nid".
            }

            return true;
        }

        //Check if the ga_cid exists, and if so use THAT nebula_id again
        public function get_previous_nebula_id_by_ga_cid(){
            if ( nebula()->option('visitors_db') ){
                global $wpdb;
                $nebula_id_from_matching_ga_cid = $wpdb->get_results($wpdb->prepare("SELECT nebula_id FROM nebula_visitors WHERE ga_cid = '%s'", ga_parse_cookie())); //DB Query here.
                if ( !empty($nebula_id_from_matching_ga_cid) ){
                    return reset($nebula_id_from_matching_ga_cid[0]);
                }
            }

            return false;
        }

        //Return the Nebula ID (or false)
        public function get_nebula_id_from_cookie(){
            if ( isset($_COOKIE['nid']) ){
                return htmlentities(preg_replace('/[^a-zA-Z0-9\.]+/', '', $_COOKIE['nid']));
            }

            return false;
        }

        //Get all the visitor data at once as an object from the DB
        public function get_all_visitor_data($storage_type=false, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                //Get data from cache
                if ( empty($alt_nebula_id) ){
                    $cached_visitor_data = wp_cache_get('nebula_visitor');
                    if ( $cached_visitor_data && $storage_type != 'fresh' ){ //If the data cache exists and not forcing fresh data
                        return $cached_visitor_data;
                    }
                }

                //Get data from Database
                if ( $storage_type != 'cache' ){
                    $nebula_id = ( !empty($alt_nebula_id) )? $alt_nebula_id : nebula()->get_appropriate_nebula_id();

                    global $wpdb;
                    $all_visitor_db_data = $wpdb->get_results("SELECT id, label, value FROM nebula_visitors_data WHERE nebula_id = '" . sanitize_text_field($nebula_id) . "' ORDER BY id"); //DB Query here

                    if ( $all_visitor_db_data ){
                        //Re-organize the data
                        $organized_data = array();
                        foreach ( $all_visitor_db_data as $index => $value ){
                            $label = $all_visitor_db_data[$index]->label;

                            $unserialized_value = $all_visitor_db_data[$index]->value;
                            if ( is_serialized($unserialized_value) ){
                                $unserialized_value = unserialize($unserialized_value);
                            }
                            $organized_data[$label] = $unserialized_value;
                        }

                        if ( empty($alt_nebula_id) ){
                            wp_cache_set('nebula_visitor', $organized_data); //Cache the result (but not for other alternate Nebula IDs)
                            wp_cache_set('nebula_visitor_old', $organized_data);
                        }

                        return $organized_data;
                    }
                }
            }

            return false;
        }

        //Check if continuing the same session
        public function is_same_session(){
            //Check for external referrer
            if ( $this->is_external_referrer() && !$this->is_page_refresh() ){
                return false;
            }

            //Check if last session ID matches current session ID
            $last_session_id = $this->get_visitor_datapoint('last_session_id');
            if ( $last_session_id == nebula()->nebula_session_id() ){
                return true;
            }

            return false;
        }

        //Check if the page was refreshed
        public function is_page_refresh(){
            $last_page_viewed = $this->get_visitor_datapoint('last_page_viewed');
            if ( $last_page_viewed == nebula()->url_components('all') ){
                return true;
            }

            return false;
        }

        //Retrieve User Data
        public function ajax_get_visitor_data(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula()->option('visitors_db') ){ die('Permission Denied.'); }
            $key = sanitize_text_field($_POST['data']);
            echo json_encode($this->get_visitor_datapoint($key));
            wp_die();
        }

        //Get a single datapoint from the Nebula Visitors DB
        public function get_visitor_datapoint($key, $return_all=false, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                if ( $key == 'notes' ){
                    return false;
                }

                $all_visitor_data = $this->get_all_visitor_data('any', $alt_nebula_id);
                if ( isset($all_visitor_data) && !empty($all_visitor_data[$key]) ){
                    if ( is_array($all_visitor_data[$key]) ){ //If this datapoint is an array (for appended data)
                        if ( $return_all ){ //If requesting all datapoints
                            return $all_visitor_data[$key];
                        }

                        return end($all_visitor_data[$key]); //Otherwise, return only the last datapoint
                    }

                    return $all_visitor_data[$key];
                }
            }

            return false;
        }

        public function get_visitor_data($column){ //@TODO "Nebula" 0: Update to allow multiple datapoints to be accessed in one query.
            if ( nebula()->option('visitors_db') ){
                $column = sanitize_key($column);

                if ( $column == 'notes' ){
                    return false;
                }

                $nebula_id = nebula()->get_nebula_id();
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
            if ( nebula()->option('visitors_db') ){
                $data_to_send = array();
                foreach ( $data as $column => $value ){
                    $existing_value = $this->get_visitor_data($column);
                    if ( empty($existing_value) ){ //If the requested data is empty/null, then update.
                        $data_to_send[$column] = $value;
                    }
                }

                if ( !empty($data_to_send) ){
                    nebula()->update_visitor($data_to_send);
                }
            }
            return false;
        }

        //Update Visitor Data
        public function ajax_update_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula()->option('visitors_db') ){ die('Permission Denied.'); }
            $data = $_POST['data'];
            echo json_encode($this->update_visitor_data($data));
            wp_die();
        }


        //Update the Nebula visitor data
        public function update_visitor_data($data=array(), $update_db=true, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                if ( is_string($data) ){
                    $data = array($data);
                }

                if ( empty($alt_nebula_id) ){
                    $all_data = $this->update_data_everytime($this->get_all_visitor_data());
                } else {
                    $all_data = $this->get_all_visitor_data('any', $alt_nebula_id);
                    $all_data['most_identifiable'] = $this->most_identifiable_label($all_data, false, $alt_nebula_id);
                }

                if ( is_array($data) ){
                    foreach ( $data as $key => $value ){
                        $value = str_replace(array("\r", "\n"), ' ', $value);
                        $all_data[$key] = $value;
                    }

                    $all_data['last_modified_on'] = time();
                }

                if ( empty($alt_nebula_id) ){
                    wp_cache_set('nebula_visitor', $all_data); //Cache the result
                } else {
                    $scores = $this->calculate_scores($all_data);
                    $all_data['behavior_score'] = $scores['behavior'];
                    $all_data['demographic_score'] = $scores['demographic'];
                    $all_data['lead_score'] = $scores['lead'];
                }

                //Update the database (else presume it will update in batch later)
                if ( $update_db ){
                    $rows_updated = $this->send_all_to_cache_and_db($all_data, $alt_nebula_id);
                }

                if ( !empty($alt_nebula_id) ){
                    return $rows_updated;
                }

                return $all_data;
            }

            return false;
        }

        //Actually send the updated data to the database
        public function send_all_to_cache_and_db($all_data, $alt_nebula_id=false){ //This does like 4 db queries...
            if ( nebula()->option('visitors_db') ){
                if ( empty($all_data) ){
                    trigger_error('nebula_vdb_send_all_to_cache_and_db() requires all data to be passed as a parameter!', E_USER_ERROR);
                    return false;
                }

                if ( empty($alt_nebula_id) ){
                    wp_cache_set('nebula_visitor', $all_data); //Cache the result
                }

                $nebula_id = ( !empty($alt_nebula_id) )? $alt_nebula_id : $this->get_appropriate_nebula_id();

                global $wpdb;

                //Update Primary table first
                $updated_primary_table = $wpdb->update('nebula_visitors', $this->prep_primary_table_for_db($all_data), array('nebula_id' => $nebula_id)); //DB Query here

                //Insert new rows first
                $old_visitor_data = wp_cache_get('nebula_visitor_old');
                if ( empty($alt_nebula_id) && !empty($old_visitor_data) ){
                    //Diff against old data
                    $old_labels = array_keys($old_visitor_data);
                    $new_labels = array_keys($all_data);
                    $non_existing_labels = array_diff($new_labels, $old_labels);

                    if ( !empty($non_existing_labels) ){
                        $this->insert_visitor($all_data, $alt_nebula_id);
                        wp_cache_set('nebula_visitor_old', $all_data);
                    }
                } else {
                    //Just insert everything
                    $this->insert_visitor($all_data, $alt_nebula_id);
                }

                $updated_data = array();
                foreach ( $all_data as $label => $value ){
                    $value = str_replace(array("\r", "\n"), ' ', $value);

                    if ( is_null($value) || (is_string($value) && trim($value) == '') ){ //Don't use empty() here because we want to include data set to false
                        continue;
                    }

                    if ( is_array($value) ){
                        $value = serialize($value);
                    }

                    if ( !empty($old_visitor_data) ){
                        if ( !empty($old_visitor_data[$label]) && $old_visitor_data[$label] == $value ){
                            continue;
                        }
                    }

                    $updated_data[$label] = $value;
                }

                //Update existing rows
                $update_query = "UPDATE nebula_visitors_data SET value = CASE";
                foreach ( $updated_data as $label => $value ){
                    $update_query .= " WHEN label = '" . $label . "' THEN '" . $value . "'";
                }
                $update_query .= " END WHERE label IN (";

                foreach ( $updated_data as $label => $value ){
                    $update_query .= "'" . $label . "',";
                }
                $update_query = rtrim($update_query, ',');
                $update_query .= ") AND nebula_id = '" . $nebula_id . "';";

                $updated_visitor = $wpdb->query($update_query); //DB Query here
                if ( $updated_visitor !== false ){
                    if ( $updated_visitor > 0 ){ //If 1 or more rows were updated
                        if ( $this->is_known($all_data, $alt_nebula_id) ){
                            //@TODO "Nebula" 0: Run known visitor procedure... What is it going to be? Maybe a do_action (with parameter of all data)?
                        }
                    }
                }

                return $updated_visitor; //Return how many rows updated or false if error
            }

            return false;
        }

        //Append to Visitor Data
        public function ajax_append_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula()->option('visitors_db') ){ die('Permission Denied.'); }
            $data = $_POST['data']; //json_decode(stripslashes()); but its already an array... why?

            echo json_encode($this->append_visitor_data($data));
            wp_die();
        }

        public function append_visitor_data($data=array(), $update_db=true){
            if ( nebula()->option('visitors_db') ){
                if ( !is_array($data) ){
                    trigger_error("nebula_vdb_append_visitor_data() expects data to be passed as an array of key => value pairs.", E_USER_ERROR);
                    return false;
                }

                $all_data = $this->update_data_everytime($this->get_all_visitor_data());

                //Add data here to append everytime (if different than prior)
                //IP Geolocation
                $ip_geolocation = nebula()->ip_location('all');
                if ( !empty($ip_geolocation) ){
                    $data['ip_geo'] = sanitize_text_field($ip_geolocation->city) . ', ' . sanitize_text_field($ip_geolocation->region_name) . ' ' . sanitize_text_field($ip_geolocation->zip_code) . ', ' . sanitize_text_field($ip_geolocation->country_name) . ' (' . sanitize_text_field($_SERVER['REMOTE_ADDR']) . ')';
                }

                foreach ( $data as $key => $value ){
                    $value = str_replace(array("\r", "\n"), ' ', $value);

                    if ( !empty($value) ){ //Skip empty values
                        if ( !empty($all_data[$key]) ){ //The key exists
                            if ( !is_array($all_data[$key]) ){
                                $all_data[$key] = array($all_data[$key]); //Existing value is a string. Convert it to an array.
                            }

                            //If next value is one letter more than the previous value, replace the previous with the new.
                            if ( end($all_data[$key]) == substr($value, 0, -1) ){
                                array_pop($all_data[$key]);
                                array_push($all_data[$key], $value);
                            }

                            if ( in_array($value, $all_data[$key]) ){ //Value already exists in the array
                                $existing_index = array_search($value, $all_data[$key]);
                                unset($all_data[$key][$existing_index]); //Remove the old data from the array.
                                array_push($all_data[$key], $value); //Append the data to the end of the array.
                                $all_data[$key] = array_values($all_data[$key]); //Rebase the indexes of the array after unsetting (so it doesn't skip numbers)
                            } else {
                                array_push($all_data[$key], $value); //Append the data to the end of the array since it wasn't in there yet.
                            }

                            //If this datapoint has an array longer than 20, remove oldest entries
                            $datapoint_size = count($all_data[$key]);
                            if ( $datapoint_size > 20 ){
                                $all_data[$key] = array_slice($all_data[$key], ($datapoint_size-20)); //Keep last 20 indexes
                            }
                        } else { //Key does not exist
                            $all_data[$key] = $value; //Add value as a string
                        }
                    }
                }

                wp_cache_set('nebula_visitor', $all_data); //Cache the result

                if ( $update_db ){
                    $this->send_all_to_cache_and_db($all_data);
                }

                return $all_data;
            }

            return false;
        }

        //Remove data from the Nebula Visitor DB
        public function ajax_remove_datapoint(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula()->option('visitors_db') ){ die('Permission Denied.'); }

            $this->remove_visitor_data($_POST['data']);
            wp_die();
        }

        //Remove data from the Nebula Visitor DB
        public function remove_visitor_data($data, $update_db=true){
            if ( nebula()->option('visitors_db') ){
                if ( is_string($data) ){
                    $this->update_visitor_data(array($data => false), false); //Remove the entire label
                } else {
                    foreach ( $data as $label => $value ){
                        if ( !is_string($label) ){
                            $batch_update_data = $this->update_visitor_data(array($value => false), false); //Remove the entire label
                        } else {
                            $db_datapoint = $this->get_visitor_datapoint($label, true);

                            if ( is_string($db_datapoint) ){
                                if ( $db_datapoint == $value ){
                                    $batch_update_data = $this->update_visitor_data(array($value => false), false); //Remove the entire label
                                }
                            } else {
                                $matched_index = array_search($value, $db_datapoint);

                                if ( $matched_index !== false ){
                                    unset($db_datapoint[$matched_index]); //Remove just this index
                                    $batch_update_data = $this->update_visitor_data(array($label => $db_datapoint), false);
                                }
                            }
                        }
                    }
                }

                return $this->update_visitor_data($batch_update_data, $update_db);
            }

            return false;
        }

        //Increment Visitor Data
        public function ajax_increment_visitor(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula()->option('visitors_db') ){ die('Permission Denied.'); }
            $data = $_POST['data'];
            echo json_encode($this->increment_visitor_data($data));
            wp_die();
        }

        //Increment a datapoint in the Nebula Visitor DB
        public function increment_visitor_data($datapoints=array(), $update_db=true){
            if ( nebula()->option('visitors_db') ){
                if ( is_string($datapoints) ){
                    $datapoints = array($datapoints);
                }

                $all_data = $this->update_data_everytime($this->get_all_visitor_data());

                foreach ( $datapoints as $key ){
                    if ( array_key_exists($key, $all_data) && !empty($all_data[$key]) ){
                        if ( !is_int($all_data[$key]) ){ //Data is not an integer. Try to parse it...
                            $all_data[$key] = intval($all_data[$key]);
                        }

                        if ( is_int($all_data[$key]) ){ //If it is an integer, increment it.
                            $all_data[$key]++;
                        }
                    } else { //Current number either doesn't exist or is not an integer
                        $all_data = $this->update_visitor_data(array($key => 1), $update_db);
                    }
                }

                wp_cache_set('nebula_visitor', $all_data); //Cache the result
                $updated_visitor_data = $this->update_visitor_data($all_data, $update_db);

                return $updated_visitor_data;
            }

            return false;
        }

        //Build a set of data for a brand new visitor
        public function build_new_visitor_data_object($new_data=array()){
            if ( nebula()->option('visitors_db') ){
                $defaults = array(
                    'nebula_id' => $this->get_appropriate_nebula_id(),
                    'ga_cid' => ga_parse_cookie(), //Will be UUID on first visit then followed up with actual GA CID via AJAX (if available)
                    'is_known' => '0',
                    'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                    'all_ip_addresses' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                    'notable_poi' => nebula()->poi(),
                    'all_notable_pois' => nebula()->poi(),
                    'created_on' => time(),
                    'lead_score' => 0,
                    'demographic_score' => 0,
                    'behavior_score' => 0,
                    'score_mod' => 0,
                    'referrer' => ( $this->is_external_referrer() )? $_SERVER['HTTP_REFERER'] : '',
                    'acquisition_channel' => $this->detect_acquisition_channel(),
                    'acquisition_keywords' => $this->get_referrer_search_terms(),
                    'is_homescreen_app' => ( isset($_GET['hs']) )? '1' : false,
                    'is_first_session' => '1',
                    'is_new_user' => '1',
                    'is_returning_user' => '0',
                    'session_count' => 1,
                    'prev_session_on' => round(time()/60)*60, //Rounded to the nearest minute
                    'last_session_id' => nebula()->nebula_session_id(),
                    'last_seen_on' => round(time()/60)*60, //Rounded to the nearest minute
                    'nebula_session_id' => nebula()->nebula_session_id(),
                    'current_session_on' => round(time()/60)*60, //Rounded to the nearest minute
                    'current_session_duration' => 0,
                    'current_session_pageviews' => 1,
                    'total_pageviews' => 1,
                    'landing_page' => nebula()->url_components('all'),
                    'last_page_viewed' => nebula()->url_components('all'),
                    'notes' => '',
                    'notices' => '',
                );

                //Attempt to detect IP Geolocation data using https://freegeoip.net/
                $ip_geolocation = nebula()->ip_location('all');
                if ( !empty($ip_geolocation) ){
                    $defaults['ip_country'] = sanitize_text_field($ip_geolocation->country_name);
                    $defaults['ip_region'] = sanitize_text_field($ip_geolocation->region_name);
                    $defaults['ip_city'] = sanitize_text_field($ip_geolocation->city);
                    $defaults['ip_zip'] = sanitize_text_field($ip_geolocation->zip_code);
                    $defaults['ip_time_zone'] = sanitize_text_field($ip_geolocation->time_zone);
                    $defaults['ip_geo'] = sanitize_text_field($ip_geolocation->city) . ', ' . sanitize_text_field($ip_geolocation->region_name) . ' ' . sanitize_text_field($ip_geolocation->zip_code) . ', ' . sanitize_text_field($ip_geolocation->country_name) . ' (' . sanitize_text_field($_SERVER['REMOTE_ADDR']) . ')';

                    if ( !empty($ip_geolocation->time_zone) ){
				$local_time = new DateTime('now', new DateTimeZone($ip_geolocation->time_zone));
				if ( !empty($local_time) ){
					$defaults['ip_time_zone_offset'] = $local_time->format('P');
					$defaults['ip_local_time'] = $local_time->format('l, F j, Y @ g:ia');
				}
			}
                }

                $all_data = $this->update_data_everytime($defaults); //Add any passed data

                wp_cache_set('nebula_visitor', $all_data); //Cache the result
                return $all_data;
            }

            return false;
        }

        //Pull search queries from referrer
        public function get_referrer_search_terms(){
            if ( $this->is_external_referrer() && $this->is_search_engine($_SERVER['HTTP_REFERER']) ){
                //Google
                if ( strpos($_SERVER['HTTP_REFERER'], 'google') && strpos($_SERVER['HTTP_REFERER'], 'q=') !== false ){
                    $search_term = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], 'q=')); //Remove everything before q=
                    $search_term = substr($search_term, 2); //Remove q=

                    //Remove everything after next &
                    if ( strpos($search_term, '&') ){
                        $search_term = substr($search_term, 0, strpos($search_term, '&'));
                    }

                    return urldecode($search_term);
                }
            }

            return false;
        }

        //Prep data for the primary table
        public function prep_primary_table_for_db($all_data){
            if ( empty($all_data) ){
                trigger_error('nebula_vdb_prep_primary_table_for_db() requires all data to be passed as a parameter!', E_USER_ERROR);
                return false;
            }

            return array(
                'nebula_id' => $all_data['nebula_id'],
                'ga_cid' => $all_data['ga_cid'],
                'ip_address' => $all_data['ip_address'],
                'user_agent' => $all_data['user_agent'],
                'fingerprint' => $all_data['fingerprint'],
                'nebula_session_id' => $all_data['nebula_session_id'],
                'notable_poi' => $all_data['notable_poi'],
                'email_address' => $all_data['email_address'],
                'is_known' => $all_data['is_known'],
                'last_seen_on' => $all_data['last_seen_on'],
                'last_modified_on' => $all_data['last_modified_on'],
                'most_identifiable' => $all_data['most_identifiable'],
                'lead_score' => $all_data['lead_score'],
                'notes' => $all_data['notes'],
            );
        }

        //Insert visitor into table with all default detections
        public function insert_visitor($data=array(), $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') && !empty($data) ){
                //Primary table prep
                $prepped_primary_data = $this->prep_primary_table_for_db($data);

                global $wpdb;
                $inserted_primary_table = $wpdb->replace('nebula_visitors', $prepped_primary_data); //Insert a row with all the default (and passed) sanitized values. DB Query here

                //Data table prep
                $nebula_id = ( !empty($alt_nebula_id) )? $alt_nebula_id : $this->get_appropriate_nebula_id();

                //Build columns
                foreach ( $data as $label => $value ){
                    if ( is_array($value) ){
                        $value = serialize($value);
                    }

                    $built_values[] = array(
                        "'" . $nebula_id . "'",
                        "'" . $label . "'",
                        "'" . $value . "'"
                    );
                }

                //Build rows
                $built_insert_query = "INSERT IGNORE INTO nebula_visitors_data (nebula_id, label, value) VALUES";
                foreach ( $built_values as $row ){
                    $built_insert_query .= " (" . implode(',', $row) . "),";
                }
                $built_insert_query = rtrim($built_insert_query, ',') . ';';

                $inserted_visitor_data = $wpdb->query($built_insert_query); //DB Query here

                return true;
            }

            return false;
        }

        //Update certain visitor data everytime.
        //Pass the existing data array to this to append to it (otherwise a new array is created)... So probably don't want to call this without passing the parameter.
        public function update_data_everytime($defaults=array()){
            if ( nebula()->option('visitors_db') ){
                //Avoid ternary operators to prevent overwriting existing data (like manual DB entries)

                $cached_visitor_data = $this->get_all_visitor_data('cache'); //@todo "Nebula" 0: could we just use $defaults instead of the cache?
                $latest_data = ( !empty($cached_visitor_data) )? $cached_visitor_data : $defaults;
                if ( !empty($latest_data) ){ //Only update these in the cache to prevent unnecessary DB queries
                    //Detect Google Analytics blocking
                    if ( !$this->is_nebula_generated_cid() ){ //GA is not blocked if GA generated the CID
                        $defaults['is_ga_blocked'] = '0';
                    } else {
                        $total_pageviews = ( array_key_exists('total_pageviews', $latest_data) )? $latest_data['total_pageviews'] : 0;
                        if ( $total_pageviews > 1 ){
                            $defaults['is_multipage_visitor'] = '1';
                        }
                        $total_refreshes = ( array_key_exists('total_refreshes', $latest_data) )? $latest_data['total_refreshes'] : 0;
                        if ( nebula()->option('ga_tracking_id') && ($total_pageviews > 1 || $total_refreshes > 1) ){ //If GA is enabled and if more than 1 pageview then it's blocked
                            $defaults['is_ga_blocked'] = '1';
                        }
                    }

                    //Update session duration
                    $session_start_time = $latest_data['current_session_on'];
                    $defaults['current_session_duration'] = round(time()/60)*60-$session_start_time; //Rounded to the nearest minute
                    $defaults['is_known'] = ( $this->is_known($defaults) )? '1' : '0';
                }

                $defaults['ga_cid'] = nebula()->ga_parse_cookie();
                $defaults['notable_poi'] = nebula()->poi();
                $defaults['last_modified_on'] = time();
                $defaults['nebula_session_id'] = nebula()->nebula_session_id();

                if ( nebula()->is_staff() ){
                    $defaults['is_staff'] = '1';
                }

                //Check for nv_ query parameters like ?nv_first_name=john
                $query_strings = parse_str($_SERVER['QUERY_STRING']);
                if ( !empty($query_strings) ){
                    foreach ( $query_strings as $key => $value ){
                        if ( strpos($key, 'nv_') === 0 ){
                            if ( empty($value) ){
                                $value = '1';
                            }
                            $defaults[sanitize_key(substr($key, 3))] = sanitize_text_field(str_replace('+', ' ', urldecode($value)));
                        }
                    }
                }

                //Logged-in User Data
                if ( is_user_logged_in() ){
                    $defaults['wp_user_id'] = get_current_user_id();

                    $user = get_userdata(get_current_user_id());
                    if ( !empty($user) ){ //WordPress user data exists
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

                //Request information
                if ( !nebula()->is_ajax_request() ){
                    $defaults['http_accept'] = $_SERVER['HTTP_ACCEPT'];
                    $defaults['http_encoding'] = $_SERVER['HTTP_ACCEPT_ENCODING'];
                    $defaults['http_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                }

                //Device information
                $defaults['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
                $defaults['device_form_factor'] = ( nebula()->get_device('formfactor') )? nebula()->get_device('formfactor') : 'Unknown';
                $defaults['device_full'] = ( nebula()->get_device('full') )? nebula()->get_device('full') : 'Unknown';
                $defaults['device_brand'] = ( nebula()->get_device('brand') )? nebula()->get_device('brand') : 'Unknown';
                $defaults['device_model'] = ( nebula()->get_device('model') )? nebula()->get_device('model') : 'Unknown';
                $defaults['device_type'] = ( nebula()->get_device('type') )? nebula()->get_device('type') : 'Unknown';
                $defaults['os_full'] = ( nebula()->get_os('full') )? nebula()->get_os('full') : 'Unknown';
                $defaults['os_name'] = ( nebula()->get_os('name') )? nebula()->get_os('name') : 'Unknown';
                $defaults['os_version'] = ( nebula()->get_os('version') )? nebula()->get_os('version') : 'Unknown';
                $defaults['browser_full'] = ( nebula()->get_browser('full') )? nebula()->get_browser('full') : 'Unknown';
                $defaults['browser_name'] = ( nebula()->get_browser('name') )? nebula()->get_browser('name') : 'Unknown';
                $defaults['browser_version'] = ( nebula()->get_browser('version') )? nebula()->get_browser('version') : 'Unknown';
                $defaults['browser_engine'] = ( nebula()->get_browser('engine') )? nebula()->get_browser('engine') : 'Unknown';
                $defaults['browser_type'] = ( nebula()->get_browser('type') )? nebula()->get_browser('type') : 'Unknown';

                //Information based on other visitor data
                $defaults['most_identifiable'] = $this->most_identifiable_label($defaults, false);
                $defaults['fingerprint'] = $this->fingerprint($defaults);

                $scores = $this->calculate_scores($defaults);
                $defaults['behavior_score'] = $scores['behavior'];
                $defaults['demographic_score'] = $scores['demographic'];
                $defaults['lead_score'] = $scores['lead'];

                return $defaults;
            }

            return false;
        }

        //Generate a fingerprint for thie visitor
        public function fingerprint($data=null){
            //Server-side detections
            $fingerprint = 's:';
            if ( empty($data) ){ //If additional data is unavailable, return only server-side fingerprint
                $fingerprint .= ( !empty($_SERVER['HTTP_USER_AGENT']) )? $this->smash_text($_SERVER['HTTP_USER_AGENT']) : '';
                $fingerprint .= ( !empty($_SERVER['HTTP_ACCEPT']) )? $this->smash_text($_SERVER['HTTP_ACCEPT']) : '';
                $fingerprint .= ( !empty($_SERVER['HTTP_ACCEPT_ENCODING']) )? $this->smash_text($_SERVER['HTTP_ACCEPT_ENCODING']) : '';
                $fingerprint .= ( !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) )? $this->smash_text($_SERVER['HTTP_ACCEPT_LANGUAGE']) : '';
                $fingerprint .= nebula()->ip_location('timezone');

                return $fingerprint;
            } else { //Use provided $data (so AJAX doesn't alter server-side detections)
                $fingerprint .= ( !empty($data['user_agent']) )? $this->smash_text($data['user_agent']) : '';
                $fingerprint .= ( !empty($data['http_accept']) )? $this->smash_text($data['http_accept']) : '';
                $fingerprint .= ( !empty($data['http_encoding']) )? $this->smash_text($data['http_encoding']) : '';
                $fingerprint .= ( !empty($data['http_language']) )? $this->smash_text($data['http_language']) : '';
                $fingerprint .= ( !empty($data['ip_time_zone']) )? $this->smash_text($data['ip_time_zone']) : '';
            }

            //Client-side detections
            $fingerprint .= '.c:';
            $fingerprint .= ( !empty($data['screen']) )? $this->smash_text($data['screen']) : '';
            $fingerprint .= ( !empty($data['ip_time_zone']) )? $this->smash_text($data['ip_time_zone']) : '';
            $fingerprint .= ( !empty($data['plugins']) )? $this->smash_text($data['plugins']) : '';
            $fingerprint .= ( !empty($data['cookies']) )? $this->smash_text($data['cookies']) : '';

            return $fingerprint;
        }

        //Nebula smash encode text
        public function smash_text($string){
            return str_rot13(trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9]/', '', urldecode(html_entity_decode(strip_tags(strtolower($string))))))));
        }

        //Calculate lead score
        public function calculate_scores($data=null, $storage_type=false, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                if ( !empty($data) ){
                    $all_visitor_data = $data;
                } else {
                    $all_visitor_data = $this->get_all_visitor_data($storage_type, $alt_nebula_id);
                }

                $demographic_score = $this->calculate_demographic_score($all_visitor_data);
                $behavior_score = $this->calculate_behavior_score($all_visitor_data);
                $score_modifier = intval($all_visitor_data['score_mod']);
                $lead_score = $demographic_score+$behavior_score+$score_modifier;

                return array(
                    'demographic' => $demographic_score,
                    'behavior' => $behavior_score,
                    'modifier' => $score_modifier,
                    'lead' => $lead_score,
                );
            }
        }

        //Calculate demographic score (Name, job title, etc.)
        //Hook into this with add_filter to use your own values.
        /*
            add_filter('nebula_vdb_demographic_points', 'project_demo_points');
            function project_demo_points($array){
                $array['notable_poi'] = 500;
                $array['email_address'] = 500;
                return $array;
            }
        */
        public function calculate_demographic_score($data){
            if ( !empty($data) ){
                //label => points (If label exists in DB its value is not empty)
                //label => array(match => points) (Match can be string or RegEx)
                $default_demographic_points = array(
                    'notable_poi' => 100,
                    'full_name' => 75,
                    'email_address' => 100,
                    'is_known' => 100,
                    'street_full' => 50,
                    'geo_latitude' => 75,
                    'geo_accuracy' => array('/^(\d{5})/', -35),
                    'photo' => 100,
                    'ip_city' => 5,
                    'company' => 25,
                    'job_title' => 5,
                    'job_title' => array('/(senior|^C[A-Z]{2}$|President|Chief)/i', 25),
                    'phone_number' => 75,
                    'notes' => 5,
                    'referrer' => 10,
                );

                $additional_demographic_points = apply_filters('nebula_vdb_demographic_points', array());
                $demographic_point_values = array_merge($default_demographic_points, $additional_demographic_points);

                return $this->points_adder($data, $demographic_point_values);
            }
        }

        //Calculate behavior score (Events, actions, etc.)
        //Hook into this with add_filter to use your own values or add new ones.
        /*
            add_filter('nebula_vdb_behavior_points', 'project_behavior_points');
            function project_behavior_points($array){
                $array['ordered_accessory'] = 100;
                $array['ordered_warranty'] = 500;
                return $array;
            }
        */
        public function calculate_behavior_score($data){
            if ( !empty($data) ){
                //label => points (if label exists and value is not empty)
                //label => array(match => points)
                $default_behavior_points = array(
                    'acquisition_keywords' => 5,
                    'utm_campaign' => 5,
                    'notable_download' => 5,
                    'pdf_view' => 5,
                    'outbound_links' => 1,
                    'internal_search' => 15,
                    'contact_method' => 75,
                    'ecommerce_addtocart' => 35,
                    'ecommerce_checkout' => 50,
                    'is_ecommerce_customer' => 100,
                    'engaged_reader' => 5,
                    'contact_funnel' => 10,
                    'copied_text' => 3,
                    'print' => 25,
                    'video_play' => 10,
                    'video_engaged' => 15,
                    'video_finished' => 25,
                    'fb_like' => 10,
                    'fb_share' => 15,
                    'twitter_share' => 15,
                    'gplus_share' => 15,
                    'li_share' => 15,
                    'pin_share' => 15,
                    'email_share' => 15,
                    'is_returning_user' => 1,
                    'is_multipage_visitor' => 3,
                    'is_ga_blocked' => -10,
                    'current_session_duration' => array('0', -10), //Bounced visitors
                );

                $additional_behavior_points = apply_filters('nebula_vdb_behavior_points', array());
                $behavior_point_values = array_merge($default_behavior_points, $additional_behavior_points);

                $behavior_score = $this->points_adder($data, $behavior_point_values);
                $behavior_score = floor($behavior_score-(floor((time()-$data['last_seen_on'])/DAY_IN_SECONDS)*0.33)); //Remove .33 points per day since last seen (rounded down)

                return $behavior_score;
            }
        }

        //Adds up point totals from provided data and points
        public function points_adder($data, $points){
            if ( empty($data) || empty($points) ){
                return false; //This function requires data and points.
            }

            $score = 0;
            foreach ( $data as $label => $value ){
                if ( array_key_exists($label, $points) ){ //If this visitor has this data
                    if ( is_array($points[$label]) ){ //If the score has an condition array to match
                        if ( is_array($value) ){ //If the visitor data is also an array
                            $value = implode(',', $value); //Convert it to a single comma-separated string
                        }

                        if ( trim($value) == $points[$label][0] || (strpos($points[$label][0], '/') === 0 && preg_match($points[$label][0], trim($value))) ){
                            $score += $points[$label][1];
                        }
                    } elseif ( !empty($value) ){ //Just checking if the data exists
                        $score += $points[$label];
                    }
                }
            }

            return $score;
        }

        //Determine the most identifiable characteristic of this visitor
        public function most_identifiable_label($data=false, $storage_type=false, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                if ( !empty($data) ){
                    $all_visitor_data = $data;
                } else {
                    $all_visitor_data = $this->get_all_visitor_data($storage_type, $alt_nebula_id);
                }

                //These are in specific order!

                //Facebook Connect
                if ( !empty($all_visitor_data['facebook_connect']) ){
                    return 'Facebook Connect';
                }

                //Customer
                if ( !empty($all_visitor_data['ecommerce_customer']) ){
                    return 'Ecommerce Customer';
                }

                //Name
                if ( !empty($all_visitor_data['full_name']) || !empty($all_visitor_data['first_name']) || !empty($all_visitor_data['name']) ){
                    return 'Name';
                }

                //Email Address
                if ( !empty($all_visitor_data['email_address']) ){
                    return 'Email Address';
                }

                //Username
                if ( !empty($all_visitor_data['username']) ){
                    return 'Username';
                }

                //Geolocation
                if ( !empty($all_visitor_data['geo_latitude']) ){
                    return 'Geolocation';
                }

                //Similar to Known Visitor
                if ( !empty($all_visitor_data['similar_to_known']) ){
                    return 'Similar to Known';
                }

                //Notable POI
                if ( !empty($all_visitor_data['notable_poi']) ){
                    return 'Notable POI';
                }

                //Staff
                if ( !empty($all_visitor_data['is_staff']) ){
                    return 'Staff';
                }

                //Photo
                if ( !empty($all_visitor_data['photo']) ){
                    return 'Photo';
                }

                //Address
                if ( !empty($all_visitor_data['street_full']) ){
                    return 'Address';
                }

                //Autocomplete Address
                if ( !empty($all_visitor_data['autocomplete_street_full']) ){
                    return 'Autocomplete Address';
                }

                //Address Lookup
                if ( !empty($all_visitor_data['address_lookup']) ){
                    return 'Address Lookup';
                }

                //Contact Form Submission
                if ( !empty($all_visitor_data['contact_funnel_submit_success']) ){
                    return 'Contact Form Submission';
                }

                //Contact Method
                if ( !empty($all_visitor_data['contact_method']) ){
                    return 'Contact Method';
                }

                //Contact Form Submission Attempt
                if ( !empty($all_visitor_data['form_submission_error']) ){
                    return 'Contact Form Submission Attempt';
                }

                //Contact Funnel Started
                if ( !empty($all_visitor_data['contact_funnel_started']) ){
                    return 'Contact Funnel Started';
                }

                //City
                if ( !empty($all_visitor_data['ip_city']) ){
                    return 'IP Geo';
                }

                //Referrer
                if ( !empty($all_visitor_data['referrer']) ){
                    return 'Referrer';
                }

                //Country
                if ( !empty($all_visitor_data['ip_country']) ){
                    return 'IP Country';
                }

                //Watched Video
                if ( !empty($all_visitor_data['video_play']) ){
                    return 'Watched Video';
                }

                //Engaged Reader
                if ( !empty($all_visitor_data['engaged_reader']) ){
                    return 'Engaged Reader';
                }

                //IP Address
                if ( !empty($all_visitor_data['ip_address']) ){
                    return 'IP Address';
                }

                //User Agent
                if ( !empty($all_visitor_data['user_agent']) ){
                    return 'User Agent';
                }
            }

            return false;
        }

        //Check if this visitor is similar to a known visitor
        public function similar_to_known($specific=false, $storage_type=false, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                $all_visitor_data = $this->get_all_visitor_data($storage_type, $alt_nebula_id);

                $query = "SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.* FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id WHERE nebula_visitors.is_known = '1' AND ((nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "') OR (nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "'))";
                if ( !empty($specific) ){
                    $query = "SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.* FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id WHERE nebula_visitors.is_known = '1' AND ((nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "') OR (nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "')) AND nebula_visitors.user_agent = '" . sanitize_text_field($all_visitor_data['user_agent']) . "'";
                }

                global $wpdb;
                $similar_known_visitors = $wpdb->get_results($query); //DB Query here

                if ( empty($similar_known_visitors) ){
                    return false;
                } else { //This visitor is similar to a known visitor
                    if ( $similar_known_visitors[0]->nebula_id == $all_visitor_data['nebula_id'] ){
                        return false;
                    }

                    return $similar_known_visitors[0]->nebula_id;
                }
            }
        }

        //Remove expired visitors from the DB
        //This is only ran when Nebula Options are saved, and when *new* visitors are inserted.
        public function remove_expired($force=false){
            if ( nebula()->option('visitors_db') ){

                $nebula_visitor_remove_expired = get_transient('nebula_visitor_db_remove_expired');
                if ( empty($nebula_visitor_remove_expired) || !empty($force) || nebula()->is_debug() ){
                    global $wpdb;

                    //Remove visitors who haven't been modified in the last 90 days and are not known
                    $expiration_time = time()-(MONTH_IN_SECONDS*6); //6 months ago
                    $removed_visitors = $wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE last_modified_on < %d AND is_known = %s AND lead_score < %d", $expiration_length, '0', 100)); //DB Query here

                    //How to recalculate scores for all users without looping through every single one?

                    set_transient('nebula_visitor_db_remove_expired', time(), MONTH_IN_SECONDS); //@TODO "Nebula" 0: change this to run daily?
                    return $removed_visitors;
                }
            }

            return false;
        }

        //Lookup if this visitor is known
        public function is_known($data=false, $alt_nebula_id=false){
            if ( nebula()->option('visitors_db') ){
                //Allow cached results by double-checking Nebula ID
                if ( $alt_nebula_id == $this->get_appropriate_nebula_id() ){
                    $alt_nebula_id = false;
                }

                if ( !empty($data) ){ //If data is passed to this function
                    if ( !empty($data['known']) || !empty($data['email_address']) || !empty($data['hubspot_vid']) ){
                        return true;
                    }
                } else { //Otherwise, go get the data
                    $known = $this->get_visitor_datapoint('is_known', false, $alt_nebula_id);
                    if ( !empty($known) ){
                        return true;
                    }

                    $email = $this->get_visitor_datapoint('email_address', false, $alt_nebula_id);
                    if ( !empty($email) ){
                        return true;
                    }

                    $hubspot_vid = $this->get_visitor_datapoint('hubspot_vid', false, $alt_nebula_id);
                    if ( !empty($hubspot_vid) ){
                        return true;
                    }
                }
            }

            return false;
        }

        //Look up what columns currently exist in the nebula_visitors table.
        public function visitors_existing_columns(){
            global $wpdb;
            return $wpdb->get_col("SHOW COLUMNS FROM nebula_visitors", 0); //Returns an array of current table column names
        }

        //Query email address or Hubspot VID to see if the user is known
        public function check_if_known($send_to_hubspot=true){
            if ( nebula()->option('visitors_db') ){
                global $wpdb;
                $nebula_id = get_nebula_id();

                $known_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE nebula_id LIKE '" . $nebula_id . "' AND (email_address REGEXP '.+@.+\..+' OR hubspot_vid REGEXP '^\d+$')");
                if ( !empty($known_visitor) ){
                    if ( !is_known() ){
                        nebula()->update_visitor(array('known' => '1')); //Update to known visitor (if previously unknown)
                    }

                    $known_visitor_data = (array) $known_visitor[0];
                    if ( !empty($send_to_hubspot) ){
                        nebula()->prep_data_for_hubspot_crm_delivery($known_visitor_data);
                    }
                    return true;
                }
            }

            return false;
        }

        //Prepare Nebula Visitor data to be sent to Hubspot CRM
        //This includes skipping empty fields, ignoring certain fields, and renaming others.
        public function prep_data_for_hubspot_crm_delivery($data){
            if ( nebula()->option('hubspot_api') ){
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

                nebula()->send_to_hubspot($data_for_hubspot);
            }
        }

    }

}
