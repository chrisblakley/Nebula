<?php if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly ?>

<?php
	$active_tab = 'metadata';
	if ( !empty($this->super->get['tab']) ){
		$active_tab = strtolower($this->super->get['tab']);
	}

	$direct_option = false;
	if ( !empty($this->super->get['option']) ){
		$direct_option = $this->super->get['option'];
	}

	$pre_filter = false;
	if ( !empty($this->super->get['filter']) ){
		$pre_filter = $this->super->get['filter'];
	}
?>

<div class="wrap">
	<h1><i class="fa-solid fa-gear"></i> Nebula Options</h1>

	<?php
		if ( !current_user_can('manage_options') && !$this->is_dev() ){
			wp_die('You do not have sufficient permissions to access this page.');
		}

		if ( isset($this->super->get['settings-updated']) && $this->super->get['settings-updated'] == 'true' ){
			$this->usage('nebula_options_saved');
			$this->add_log('Nebula Options saved');
			do_action('nebula_options_saved');
			?>
			<div class="updated notice is-dismissible">
				<p><strong>Nebula Options</strong> have been updated.</p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
			<?php
		}

		settings_errors();
	?>

	<form method="post" action="options.php">
		<?php
			settings_fields('nebula_options'); //This must be inside the <form> tag!
			do_settings_sections('nebula_options');
			$nebula_data = get_option('nebula_data');
			$nebula_options = get_option('nebula_options');
		?>

		<div id="all-nebula-options" class="container-fluid">
			<div class="row">
				<div class="col-md-3 col-lg-3 col-xl-2">
					<div id="stickynav">
						<ul id="options-navigation" class="nav nav-pills flex-column" role="tablist" aria-orientation="vertical">
							<?php foreach ( $this->get_option_categories() as $category ): ?>
								<li class="nav-item">
									<a class="nav-link <?php echo ( $active_tab === strtolower($category['name']) )? 'active' : ''; ?>" href="#<?php echo trim(preg_replace('/\s&.*$/', '', strtolower($category['name']))); //Regex strips everything after and including  the "&" in the name ?>" data-bs-toggle="pill"><i class="fa-solid fa-fw <?php echo $category['icon']; ?>"></i> <?php echo $category['name']; ?></a>
								</li>
							<?php endforeach; ?>

							<?php if ( current_user_can('manage_options') ): ?>
								<li class="nav-item">
									<a class="nav-link <?php echo ( $active_tab === 'diagnostic' )? 'active' : ''; ?>" href="#diagnostic" data-bs-toggle="pill"><i class="fa-solid fa-fw fa-life-ring"></i> Diagnostic</a>
								</li>
							<?php endif; ?>
						</ul>

						<div class="input-group">
							<div class="input-group-text"><i class="fa-solid fa-fw fa-magnifying-glass"></i></div>
							<input type="text" list="preset-filters" id="nebula-option-filter" class="form-control" value="<?php echo $pre_filter; ?>" placeholder="Find an option..." />
						</div>
						<p id="reset-filter" class="hidden"><a class="btn btn-danger" href="#"><i class="fa-solid fa-fw fa-times"></i> Reset Filter</a></p>

						<?php
							$preset_filters = array(
								'Recommended' => 'recommended',
								'Social' => 'social',
								'Page Speed Impact' => 'page speed impact',
								'Minor Page Speed Impact' => 'minor page speed impact',
								'Moderate Page Speed Impact' => 'moderate page speed impact',
								'Major Page Speed Impact' => 'major page speed impact',
								'Privacy' => 'privacy', //GDPR, CCPA
							);
						?>
						<datalist id="preset-filters">
							<?php foreach ( apply_filters('nebula_options_interface_preset_filters', $preset_filters) as $name => $find ): ?>
								<option val="<?php echo $find; ?>"><?php echo $name; ?></option>
							<?php endforeach; ?>
						</datalist>

						<br/>

						<?php submit_button(); ?>
					</div>
				</div><!--/col-->
				<div id="nebula-options-section" class="col tab-content">
					<div id="metadata" class="tab-pane <?php echo ( $active_tab === 'metadata' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col">
								<h2 class="pane-title">Metadata</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'metadata', $nebula_options); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'metadata_side', $nebula_options); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/tab-pane-->

					<div id="functions" class="tab-pane <?php echo ( $active_tab === 'functions' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2 class="pane-title">Functions</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'functions', $nebula_options); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'functions_side', $nebula_options); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/tab-pane-->

					<div id="analytics" class="tab-pane <?php echo ( $active_tab === 'analytics' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2 class="pane-title">Analytics &amp; Privacy</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'analytics', $nebula_options); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'analytics_side', $nebula_options); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/tab-pane-->

					<div id="apis" class="tab-pane <?php echo ( $active_tab === 'apis' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2 class="pane-title">APIs</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'apis', $nebula_options); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'apis_side', $nebula_options); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/tab-pane-->

					<div id="administration" class="tab-pane <?php echo ( $active_tab === 'administration' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2 class="pane-title">Administration</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'administration', $nebula_options); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'administration_side', $nebula_options); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/tab-pane-->

					<div id="advanced" class="tab-pane <?php echo ( $active_tab === 'advanced' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2 class="pane-title">Advanced</h2>
								<p><strong>Warning:</strong> these are advanced options and should be modified with care!</p>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'advanced', $nebula_options); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'advanced_side', $nebula_options); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!--/tab-pane-->

					<?php do_action('nebula_options_interface_additional_panes'); //Allow for additional panels to be added from plugins or child theme ?>

					<?php if ( current_user_can('manage_options') ): ?>
						<div id="diagnostic" class="tab-pane <?php echo ( $active_tab === 'diagnostic' )? 'active' : ''; ?>">
							<div class="row title-row">
								<div class="col-xl-8">
									<h2 class="pane-title">Diagnostic</h2>
								</div><!--/col-->
							</div><!--/row-->
							<div class="row">
							<div class="col">
								<div class="nebula-options-widgets-wrap">
									<div class="nebula-options-widgets metabox-holder">
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'diagnostic', $nebula_data); ?>
										</div>
										<div class="postbox-container">
											<?php do_meta_boxes('nebula_options', 'diagnostic_side', $nebula_data); ?>
										</div>
									</div>
								</div>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
						</div><!--/tab-pane-->
					<?php endif; ?>
				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</form>
</div><!-- wrap -->

<script>
	nebula.site.admin_url = '<?php echo get_admin_url(); //Add admin URL to Nebula brain object just for Nebula Options page ?>';

	jQuery(function(){
		<?php if ( $direct_option ): //Automatically highlight options when directly linked (and toggle more info) ?>
			if ( jQuery('#<?php echo $direct_option; ?>').length ){
				if ( jQuery('#<?php echo $direct_option; ?>').parents('.form-group, .multi-form-group').length ){
					jQuery(window).on('load', function(){ //I don't know why we need to wait for window load here since the DOM should be ready by now...
						jQuery('#<?php echo $direct_option; ?>').closest('.form-group, .multi-form-group').addClass('highlight').find('.more-help').slideDown();
					});
				}

				jQuery('html, body').animate({
					scrollTop: jQuery('#<?php echo $direct_option; ?>').offset().top-95
				}, 500);
			}
		<?php endif; ?>

		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		postboxes.add_postbox_toggles('nebula_options_interface');
	});
</script>