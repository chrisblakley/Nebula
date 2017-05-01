<?php //if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly ?>

<?php
	$active_tab = 'metadata';
	if ( !empty($_GET['tab']) ){
		$active_tab = strtolower($_GET['tab']);
	}

	$direct_option = false;
	if ( !empty($_GET['option']) ){
		$direct_option = $_GET['option'];
	}

	$pre_filter = false;
	if ( !empty($_GET['filter']) ){
		$pre_filter = $_GET['filter'];
	}

	$serverProtocol = 'http://';
	if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
		$serverProtocol = 'https://';
	}

	$hostURL = explode(".", gethostname());
?>

<div class="wrap">
	<h2>Nebula Options</h2>
	<?php
		if ( !current_user_can('manage_options') && !nebula()->is_dev() ){
		    wp_die('You do not have sufficient permissions to access this page.');
		}
	?>

	<?php if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' ): ?>
	    <div class="updated notice is-dismissible">
	        <p><strong>Nebula Options</strong> have been updated. All SCSS files have been re-processed.</p>
	        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	    </div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php
			//register_setting('nebula_options', 'nebula_options');
			settings_fields('nebula_options'); //This must be inside the <form> tag!
			do_settings_sections('nebula_options');
			$nebula_data = get_option('nebula_data');
			$nebula_options = get_option('nebula_options');
			$nebula_options_defaults = nebula()->default_options();
		?>

		<div id="all-nebula-options" class="container-fluid">
			<div class="row">
				<div class="col-md-3">
					<div id="stickynav">
						<ul id="options-navigation" class="nav nav-pills flex-column">
							<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'metadata' )? 'active' : ''; ?>" href="#metadata" data-toggle="tab"><i class="fa fa-fw fa-tags"></i> Metadata</a></li>
							<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'functions' )? 'active' : ''; ?>" href="#functions" data-toggle="tab"><i class="fa fa-fw fa-sliders"></i> Functions</a></li>
							<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'analytics' )? 'active' : ''; ?>" href="#analytics" data-toggle="tab"><i class="fa fa-fw fa-area-chart"></i> Analytics</a></li>
							<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'apis' )? 'active' : ''; ?>" href="#apis" data-toggle="tab"><i class="fa fa-fw fa-key"></i> APIs</a></li>
							<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'administration' )? 'active' : ''; ?>" href="#administration" data-toggle="tab"><i class="fa fa-fw fa-briefcase"></i> Administration</a></li>
							<?php do_action('nebula_options_interface_additional_tabs'); ?>
							<?php if ( current_user_can('manage_options') ): ?>
								<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'diagnostic' )? 'active' : ''; ?>" href="#diagnostic" data-toggle="tab"><i class="fa fa-fw fa-life-ring"></i> Diagnostic</a></li>
							<?php endif; ?>
						</ul>

						<br/><br/><!-- @todo: use margin here -->

						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-fw fa-search"></i></div>
							<input type="text" id="nebula-option-filter" class="form-control" value="<?php echo $pre_filter; ?>" placeholder="Filter options" />
						</div>
						<p id="reset-filter" class="hidden"><a class="btn btn-danger" href="#"><i class="fa fa-fw fa-times"></i> Reset Filter</a></p>

						<br/><br/><!-- @todo: use margin here -->

						<h3>Preset Filters</h3>
						<ul id="preset-filters">
							<li><a href="#" filter-text="recommended">Recommended</a></li>
							<li><a href="#" filter-text="social">Social</a></li>
							<li>
								<a href="#" filter-text="page speed impact">Page Speed Impact</a>
								<ul>
									<li><a href="#" filter-text="minor page speed impact">Minor</a></li>
									<li><a href="#" filter-text="moderate page speed impact">Moderate</a></li>
									<li><a href="#" filter-text="major page speed impact">Major</a></li>
								</ul>
							</li>
							<?php do_action('nebula_options_interface_preset_filters'); ?>
						</ul>

						<br/><br/><!-- @todo: use margin here -->

						<?php submit_button(); ?>
					</div>
				</div><!--/col-->
				<div id="nebula-options-section" class="col-md-9 tab-content">



					<div id="metadata" class="tab-pane <?php echo ( $active_tab === 'metadata' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2>Metadata</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col-xl-8">
								<div class="option-group">
									<h3>Site Information</h3>

									<div class="form-group">
										<label for="site_owner">Site Owner</label>
										<input type="text" name="nebula_options[site_owner]" id="site_owner" class="form-control nebula-validate-text" value="<?php echo nebula()->option('site_owner'); ?>" placeholder="<?php echo bloginfo('name'); ?>" />
										<p class="nebula-help-text short-help form-text text-muted">The name of the company (or person) who this website is for.</p>
										<p class="nebula-help-text more-help form-text text-muted">This is used when using nebula()->the_author(0) with author names disabled.</p>
										<p class="option-keywords">recommended seo</p>
									</div>

									<div class="form-group">
										<label for="contact_email">Contact Email</label>
										<div class="input-group">
											<div class="input-group-addon"><i class="fa fa-fw fa-envelope"></i></div>
											<input type="email" name="nebula_options[contact_email]" id="contact_email" class="form-control nebula-validate-email" value="<?php echo $nebula_options['contact_email']; ?>" placeholder="<?php echo get_option('admin_email', get_userdata(1)->user_email); ?>" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The main contact email address.</p>
										<p class="nebula-help-text more-help form-text text-muted">If left empty, the admin email address will be used (shown by placeholder).</p>
										<p class="option-keywords">recommended seo</p>
									</div>
								</div><!-- /option-group -->

								<div class="option-group">
									<h3>Business Information</h3>

									<div class="form-group">
										<label for="business_type">Business Type</label>
										<input type="text" name="nebula_options[business_type]" id="business_type" class="form-control nebula-validate-text" value="<?php echo nebula()->option('business_type'); ?>" placeholder="LocalBusiness" />
										<p class="nebula-help-text short-help form-text text-muted">This schema is used for Structured Data.</p>
										<p class="nebula-help-text more-help form-text text-muted"><a href="https://schema.org/LocalBusiness" target="_blank">Use this reference under "More specific Types"</a> (click through to get the most specific possible). If you are unsure, you can use Organization, Corporation, EducationalOrganization, GovernmentOrganization, LocalBusiness, MedicalOrganization, NGO, PerformingGroup, or SportsOrganization. Details set using <a href="https://www.google.com/business/" target="_blank">Google My Business</a> will not be overwritten by Structured Data, so it is recommended to sign up and use Google My Business.</p>
										<p class="option-keywords">schema.org json-ld linked data structured data knowledge graph recommended seo</p>
									</div>

									<div class="form-group">
										<label for="phone_number">Phone Number</label>
										<div class="input-group">
											<div class="input-group-addon"><i class="fa fa-fw fa-phone"></i></div>
											<input type="tel" name="nebula_options[phone_number]" id="phone_number" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" value="<?php echo $nebula_options['phone_number']; ?>" placeholder="1-315-478-6700" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</p>
										<p class="option-keywords">recommended seo</p>
									</div>

									<div class="form-group">
										<label for="fax_number">Fax Number</label>
										<div class="input-group">
											<div class="input-group-addon"><i class="fa fa-fw fa-fax"></i></div>
											<input type="tel" name="nebula_options[fax_number]" id="fax_number" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" value="<?php echo $nebula_options['fax_number']; ?>" placeholder="1-315-426-1392" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The fax number used for Open Graph data. Use the format: "1-315-426-1392".</p>
										<p class="option-keywords"></p>
									</div>

									<div class="multi-form-group">
										<label for="latitude">Geolocation</label>

										<div class="row">
											<div class="col-sm-6">
												<div class="form-group">
													<div class="input-group mb-2">
														<div class="input-group-addon">Latitude</div>
														<input type="text" name="nebula_options[latitude]" id="latitude" class="form-control nebula-validate-regex" data-valid-regex="^-?\d+(.\d+)?$" value="<?php echo $nebula_options['latitude']; ?>" placeholder="43.0536854" />
													</div>
												</div>
											</div><!--/col-->
											<div class="col-sm-6">
												<div class="form-group">
													<div class="input-group">
														<div class="input-group-addon">Longitude</div>
														<input type="text" name="nebula_options[longitude]" id="longitude" class="form-control nebula-validate-regex" data-valid-regex="^-?\d+(.\d+)?$" value="<?php echo $nebula_options['longitude']; ?>" placeholder="-76.1654569" />
													</div>
												</div>
											</div><!--/col-->
										</div><!--/row-->

										<p class="nebula-help-text short-help form-text text-muted">The latitude and longitude of the physical location (or headquarters if multiple locations). Use the format "43.0536854".</p>
										<p class="option-keywords">location recommended seo</p>
									</div>

									<div class="multi-form-group">
										<div class="form-group">
											<label for="street_address">Address</label>
											<input type="text" name="nebula_options[street_address]" id="street_address" class="form-control nebula-validate-text mb-2" value="<?php echo $nebula_options['street_address']; ?>" placeholder="760 West Genesee Street" />
										</div>

										<div class="row">
											<div class="col-sm-5">
												<div class="form-group">
													<label for="locality">City</label>
													<input type="text" name="nebula_options[locality]" id="locality" class="form-control nebula-validate-text mb-2 mr-sm-2 mb-sm-0" value="<?php echo $nebula_options['locality']; ?>" placeholder="Syracuse" />
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<label for="region">State</label>
													<input type="text" name="nebula_options[region]" id="region" class="form-control nebula-validate-text mb-2 mr-sm-2 mb-sm-0" value="<?php echo $nebula_options['region']; ?>" placeholder="NY" />
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<label for="postal_code">Zip</label>
													<input type="text" name="nebula_options[postal_code]" id="postal_code" class="form-control nebula-validate-regex mb-2 mr-sm-2 mb-sm-0" data-valid-regex="\d{5,}" value="<?php echo $nebula_options['postal_code']; ?>" placeholder="13204" />
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<label for="country_name">Country</label>
													<input type="text" name="nebula_options[country_name]" id="country_name" class="form-control nebula-validate-text mb-2 mr-sm-2 mb-sm-0" value="<?php echo $nebula_options['country_name']; ?>" placeholder="USA" />
												</div>
											</div>
										</div>

										<p class="nebula-help-text short-help form-text text-muted">The address of the location (or headquarters if multiple locations).</p>
										<p class="nebula-help-text more-help form-text text-muted">Use <a href="https://gearside.com/nebula/functions/full_address/" target="_blank"><code>nebula()->full_address()</code></a> to get the formatted address in one function.</p>
										<p class="option-keywords">location recommended seo</p>
									</div>

									<div class="multi-form-group">
										<label>Business Hours</label>

										<?php
											$weekdays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
											$time_regex = '^([0-1]?[0-9]:\d{2}\s?[ap]m)|([0-2]?[0-9]:\d{2})$';
										?>

										<?php foreach ( $weekdays as $weekday ): ?>
											<div class="row non-filter mb-4 mb-sm-2">
												<div class="col-sm-3 mb-2 mb-sm-0">
													<input type="checkbox" name="nebula_options[business_hours_<?php echo $weekday; ?>_enabled]" id="business_hours_<?php echo $weekday; ?>_enabled" value="1" <?php checked('1', !empty($nebula_options['business_hours_' . $weekday . '_enabled'])); ?> /><label for="business_hours_<?php echo $weekday; ?>_enabled"><?php echo ucfirst($weekday); ?></label>
												</div><!--/col-->
												<div class="col">
													<div class="form-group">
														<div class="input-group" dependent-of="business_hours_<?php echo $weekday; ?>_enabled">
															<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
															<input type="text" name="nebula_options[business_hours_<?php echo $weekday; ?>_open]" id="business_hours_<?php echo $weekday; ?>_open" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" value="<?php echo $nebula_options['business_hours_' . $weekday . '_open']; ?>" />
														</div>
													</div>
												</div><!--/col-->
												<div class="col">
													<div class="form-group">
														<div class="input-group" dependent-of="business_hours_<?php echo $weekday; ?>_enabled">
															<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
															<input type="text" name="nebula_options[business_hours_<?php echo $weekday; ?>_close]" id="business_hours_<?php echo $weekday; ?>_close" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" value="<?php echo $nebula_options['business_hours_' . $weekday . '_close']; ?>" />
														</div>
													</div>
												</div><!--/col-->
											</div><!--/row-->
										<?php endforeach; ?>

										<p class="nebula-help-text short-help form-text text-muted">Open/Close times. Times should be in the format "5:30 pm" or "17:30". Uncheck all to disable this meta.</p>
										<p class="option-keywords">seo</p>
									</div>

									<div class="form-group" dependent-or="business_hours_sunday_enabled business_hours_monday_enabled business_hours_tuesday_enabled business_hours_wednesday_enabled business_hours_thursday_enabled business_hours_friday_enabled business_hours_saturday_enabled">
										<label for="business_hours_closed">Days Off</label>
										<textarea name="nebula_options[business_hours_closed]" id="business_hours_closed" class="form-control nebula-validate-textarea" rows="3"><?php echo $nebula_options['business_hours_closed']; ?></textarea>
										<p class="dependent-note hidden">This option is dependent on Business Hours (above).</p>
										<p class="nebula-help-text short-help form-text text-muted">Comma-separated list of special days the business is closed (like holidays).</p>
										<p class="nebula-help-text more-help form-text text-muted">These can be date formatted, or day of the month (Ex: "7/4" for Independence Day, or "Last Monday of May" for Memorial Day, or "Fourth Thursday of November" for Thanksgiving). <a href="http://mistupid.com/holidays/" target="_blank">Here is a good reference for holiday occurrences.</a>. Note: This function assumes days off that fall on weekends are observed the Friday before or the Monday after.</p>
										<p class="option-keywords">seo</p>
									</div>
								</div><!-- /option-group -->
							</div><!--/col-->
							<div class="col-xl-8">
								<div class="option-group">
									<h3>Social Networks</h3>

									<div class="form-group">
										<label for="facebookurl">Facebook</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> URL</span></div>
											<input type="text" name="nebula_options[facebook_url]" id="facebook_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['facebook_url']; ?>" placeholder="http://www.facebook.com/PinckneyHugo" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The full URL of your Facebook page.</p>
										<p class="option-keywords">social seo</p>
									</div>

									<div class="form-group" dependent-of="facebook_url">
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> Page ID</span></div>
											<input type="text" name="nebula_options[facebook_page_id]" id="facebook_page_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_page_id']; ?>" placeholder="000000000000000" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The ID of your Facebook page.</p>
										<p class="option-keywords">social</p>
									</div>

									<div class="form-group" dependent-of="facebook_url">
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> Admin IDs</span></div>
											<input type="text" name="nebula_options[facebook_admin_ids]" id="facebook_admin_ids" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_admin_ids']; ?>" placeholder="0000, 0000, 0000" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">IDs of Facebook administrators.</p>
										<p class="option-keywords">social</p>
									</div>

									<div class="form-group">
										<label for="twitter_username">Twitter</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Username</span></div>
											<input type="text" name="nebula_options[twitter_username]" id="twitter_username" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_username']; ?>" placeholder="@pinckneyhugo" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">Your Twitter username <strong>including the @ symbol</strong>.</p>
										<p class="option-keywords">social seo</p>
									</div>

									<div class="form-group">
										<label for="linkedin_url">LinkedIn</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-linkedin"></i> URL</span></div>
											<input type="text" name="nebula_options[linkedin_url]" id="linkedin_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['linkedin_url']; ?>" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The full URL of your LinkedIn profile.</p>
										<p class="option-keywords">social seo</p>
									</div>

									<div class="form-group">
										<label for="youtube_url">Youtube</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-youtube"></i> URL</span></div>
											<input type="text" name="nebula_options[youtube_url]" id="youtube_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['youtube_url']; ?>" placeholder="https://www.youtube.com/user/pinckneyhugo" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The full URL of your Youtube channel.</p>
										<p class="option-keywords">social seo</p>
									</div>

									<div class="form-group">
										<label for="instagram_url">Instagram</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> URL</span></div>
											<input type="text" name="nebula_options[instagram_url]" id="instagram_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['instagram_url']; ?>" placeholder="https://www.instagram.com/pinckneyhugo" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The full URL of your Instagram profile.</p>
										<p class="option-keywords">social seo</p>
									</div>

									<div class="form-group">
										<label for="pinterest_url">Pinterest</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-pinterest"></i> URL</span></div>
											<input type="text" name="nebula_options[pinterest_url]" id="pinterest_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['pinterest_url']; ?>" placeholder="https://www.pinterest.com/pinckneyhugo" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The full URL of your Pinterest profile.</p>
										<p class="option-keywords">social seo</p>
									</div>

									<div class="form-group">
										<label for="google_plus_url">Google+</label>
										<div class="input-group">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-google-plus"></i> URL</span></div>
											<input type="text" name="nebula_options[google_plus_url]" id="google_plus_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['google_plus_url']; ?>" placeholder="https://plus.google.com/106644717328415684498/about" />
										</div>
										<p class="nebula-help-text short-help form-text text-muted">The full URL of your Google+ page.</p>
										<p class="option-keywords">social seo</p>
									</div>
								</div><!-- /option-group -->

								<?php do_action('nebula_options_interface_metadata'); ?>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col-xl-8">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!-- /tab-pane -->







					<div id="functions" class="tab-pane <?php echo ( $active_tab === 'functions' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2>Functions</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col-xl-8">
								<div class="option-group">
									<div class="form-group">
										<label for="bootstrap_version">Bootstrap Version</label>
										<select name="nebula_options[bootstrap_version]" id="bootstrap_version" class="form-control nebula-validate-select">
											<option value="latest" <?php selected('latest', $nebula_options['bootstrap_version']); ?>>Latest (IE10+)</option>
											<option value="bootstrap4a5" <?php selected('bootstrap4a5', $nebula_options['bootstrap_version']); ?>>Bootstrap 4 alpha 5 (IE9+)</option>
											<option value="bootstrap3" <?php selected('bootstrap3', $nebula_options['bootstrap_version']); ?>>Bootstrap 3 (IE8+)</option>
										</select>
										<p class="nebula-help-text short-help form-text text-muted">Which Bootstrap version to use. (Default: <?php echo ucwords($nebula_options_defaults['bootstrap_version']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Bootstrap 3 will support IE8+. Bootstrap 4 alpha 5 will support IE9+. Bootstrap latest supports IE10+.</p>
										<p class="option-keywords">internet explorer old support</p>
									</div>
								</div><!-- /option-group -->

								<div class="option-group">
									<h3>Front-End</h3>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[author_bios]" id="author_bios" value="1" <?php checked('1', !empty($nebula_options['author_bios'])); ?> /><label for="author_bios">Author Bios</label>
										<p class="nebula-help-text short-help form-text text-muted">Allow authors to have bios that show their info (and post archives). (Default: <?php echo ucwords($nebula_options_defaults['author_bios']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">This also enables searching by author, and displaying author names on posts. If disabled, the author page attempts to redirect to an About Us page.</p>
										<p class="option-keywords">seo</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[comments]" id="comments" value="1" <?php checked('1', !empty($nebula_options['comments'])); ?> /><label for="comments">Comments</label>
										<p class="nebula-help-text short-help form-text text-muted">Ability to force disable comments. (Default: <?php echo ucwords($nebula_options_defaults['comments']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles).</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[device_detection]" id="device_detection" value="1" <?php checked('1', !empty($nebula_options['device_detection'])); ?> /><label for="device_detection">Browser/Device Detection</label>
										<p class="nebula-help-text short-help form-text text-muted">Detect information about the user's device and browser. (Default: <?php echo ucwords($nebula_options_defaults['device_detection']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Useful for cross-browser support. This also controls the modernizr.js library.</p>
										<p class="option-keywords">remote resource moderate page speed impact</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[ip_geolocation]" id="ip_geolocation" value="1" <?php checked('1', !empty($nebula_options['ip_geolocation'])); ?> /><label for="ip_geolocation">IP Geolocation</label>
										<p class="nebula-help-text short-help form-text text-muted">Lookup the country, region, and city of the user based on their IP address. (Default: <?php echo ucwords($nebula_options_defaults['ip_geolocation']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">This can be used for content as well as analytics (including Visitors Database)</p>
										<p class="option-keywords">location remote resource minor page speed impact</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[domain_blacklisting]" id="domain_blacklisting" value="1" <?php checked('1', !empty($nebula_options['domain_blacklisting'])); ?> /><label for="domain_blacklisting">Domain Blacklisting</label>
										<p class="nebula-help-text short-help form-text text-muted">Block traffic from known spambots and other illegitimate domains. (Default: <?php echo ucwords($nebula_options_defaults['domain_blacklisting']); ?>)</p>
										<p class="option-keywords">security remote resource recommended minor page speed impact</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[adblock_detect]" id="adblock_detect" value="1" <?php checked('1', !empty($nebula_options['adblock_detect'])); ?> /><label for="adblock_detect">Ad Block Detection</label>
										<p class="nebula-help-text short-help form-text text-muted">Detect if visitors are using ad blocking software.(Default: <?php echo ucwords($nebula_options_defaults['adblock_detect']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">To track in Google Analytics, add a dimension index under the "Analytics" tab.</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[console_css]" id="console_css" value="1" <?php checked('1', !empty($nebula_options['console_css'])); ?> /><label for="console_css">Console CSS</label>
										<p class="nebula-help-text short-help form-text text-muted">Adds CSS to the browser console. (Default: <?php echo ucwords($nebula_options_defaults['console_css']); ?>)</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[weather]" id="weather" value="1" <?php checked('1', !empty($nebula_options['weather'])); ?> /><label for="weather">Weather Detection</label>
										<p class="nebula-help-text short-help form-text text-muted">Lookup weather conditions for locations. (Default: <?php echo ucwords($nebula_options_defaults['weather']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Can be used for changing content as well as analytics.</p>
										<p class="option-keywords">location remote resource major page speed impact</p>
									</div>
								</div><!-- /option-group -->

								<div class="option-group">
									<h3>Stylesheets</h3>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[scss]" id="scss" value="1" <?php checked('1', !empty($nebula_options['scss'])); ?> /><label for="scss">Sass</label>
										<p class="nebula-help-text short-help form-text text-muted">Enable the bundled SCSS compiler. (Default: <?php echo ucwords($nebula_options_defaults['scss']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Save Nebula Options to manually process all SCSS files. This option will automatically be disabled after 30 days without processing. Last processed: <strong><?php echo ( $nebula_data['scss_last_processed'] )? date('l, F j, Y - g:ia', $nebula_data['scss_last_processed']) : 'Never'; ?></strong></p>
										<p class="option-keywords">moderate page speed impact</p>
									</div>

									<div class="form-group" dependent-of="scss">
										<input type="checkbox" name="nebula_options[minify_css]" id="minify_css" value="1" <?php checked('1', !empty($nebula_options['minify_css'])); ?> /><label for="minify_css">Minify CSS</label>
										<p class="dependent-note hidden">This option is dependent on Sass (above).</p>
										<p class="nebula-help-text short-help form-text text-muted">Minify the compiled CSS. (Default: <?php echo ucwords($nebula_options_defaults['minify_css']); ?>)</p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[dev_stylesheets]" id="dev_stylesheets" value="1" <?php checked('1', !empty($nebula_options['dev_stylesheets'])); ?> /><label for="dev_stylesheets">Developer Stylesheets</label>
										<p class="nebula-help-text short-help form-text text-muted">Allows multiple developers to work on stylesheets simultaneously. (Default: <?php echo ucwords($nebula_options_defaults['dev_stylesheets']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Combines CSS files within /assets/css/dev/ into /assets/css/dev.css to allow multiple developers to work on a project without overwriting each other while maintaining a small resource footprint.</p>
										<p class="option-keywords">minor page speed impact</p>
									</div>
								</div><!-- /option-group -->
							</div><!--/col-->
							<div class="col-xl-8">
								<div class="option-group">
									<h3>Admin References</h3>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[admin_bar]" id="admin_bar" value="1" <?php checked('1', !empty($nebula_options['admin_bar'])); ?> /><label for="admin_bar">Admin Bar</label>
										<p class="nebula-help-text short-help form-text text-muted">Control the Wordpress Admin bar globally on the frontend for all users. (Default: <?php echo ucwords($nebula_options_defaults['admin_bar']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Note: When enabled, the Admin Bar can be temporarily toggled using the keyboard shortcut <strong>Alt+A</strong> without needing to disable it permanently for all users.</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[visitors_db]" id="visitors_db" value="1" <?php checked('1', !empty($nebula_options['visitors_db'])); ?> /><label for="visitors_db">Visitors Database</label>
										<p class="nebula-help-text short-help form-text text-muted">Adds a table to the database to store visitor usage information. (Default: <?php echo ucwords($nebula_options_defaults['visitors_db']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">This data can be used for insight as well as retargeting/personalization. General events are automatically captured, but refer to the Nebula documentation for instructions on how to interact with data in both JavaScript and PHP. <a href="http://www.hubspot.com/products/crm" target="_blank">Sign up for Hubspot CRM</a> (free) and add your API key to Nebula Options (under the APIs tab) to send known user data automatically. This integration can cultivate their <a href="http://www.hubspot.com/products/marketing" target="_blank">full marketing automation service</a>.</p>
										<p class="option-keywords">moderate page speed impact discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[unnecessary_metaboxes]" id="unnecessary_metaboxes" value="1" <?php checked('1', !empty($nebula_options['unnecessary_metaboxes'])); ?> /><label for="unnecessary_metaboxes">Remove Unnecessary Metaboxes</label>
										<p class="nebula-help-text short-help form-text text-muted">Remove metaboxes on the Dashboard that are not necessary for most users. (Default: <?php echo ucwords($nebula_options_defaults['unnecessary_metaboxes']); ?>)</p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group" dependent-or="developer_email_domains developer_ips">
										<input type="checkbox" name="nebula_options[dev_info_metabox]" id="dev_info_metabox" value="1" <?php checked('1', !empty($nebula_options['dev_info_metabox'])); ?> /><label for="dev_info_metabox">Developer Info Metabox</label>
										<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
										<p class="nebula-help-text short-help form-text text-muted">Show theme and server information useful to developers. (Default: <?php echo ucwords($nebula_options_defaults['dev_info_metabox']); ?>)</p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group" dependent-or="developer_email_domains developer_ips">
										<input type="checkbox" name="nebula_options[todo_manager_metabox]" id="todo_manager_metabox" value="1" <?php checked('1', !empty($nebula_options['todo_manager_metabox'])); ?> /><label for="todo_manager_metabox">Todo Manager</label>
										<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
										<p class="nebula-help-text short-help form-text text-muted">Aggregate todo comments in code. (Default: <?php echo ucwords($nebula_options_defaults['todo_manager_metabox']); ?>)</p>
										<p class="option-keywords"></p>
									</div>
								</div><!-- /option-group -->

								<div class="option-group">
									<h3>Admin Notifications</h3>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[admin_notices]" id="admin_notices" value="1" <?php checked('1', !empty($nebula_options['admin_notices'])); ?> /><label for="admin_notices">Nebula Admin Notifications</label>
										<p class="nebula-help-text short-help form-text text-muted">Show Nebula-specific admin notices (Default: <?php echo ucwords($nebula_options_defaults['admin_notices']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Note: This does not toggle WordPress core, or plugin, admin notices.</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[theme_update_notification]" id="theme_update_notification" value="1" <?php checked('1', !empty($nebula_options['theme_update_notification'])); ?> /><label for="theme_update_notification">Nebula Theme Update Notification</label><!-- @todo: this needs a conditional around it (from the old options) -->
										<p class="nebula-help-text short-help form-text text-muted">Enable easy updates to the Nebula theme. (Default: <?php echo ucwords($nebula_options_defaults['theme_update_notification']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted"><strong>Child theme must be activated to work!</strong></p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[wp_core_updates_notify]" id="wp_core_updates_notify" value="1" <?php checked('1', !empty($nebula_options['wp_core_updates_notify'])); ?> /><label for="wp_core_updates_notify">WordPress Core Update Notification</label>
										<p class="nebula-help-text short-help form-text text-muted">Control whether or not the Wordpress Core update notifications show up on the admin pages. (Default: <?php echo ucwords($nebula_options_defaults['wp_core_updates_notify']); ?>)</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[plugin_update_warning]" id="plugin_update_warning" value="1" <?php checked('1', !empty($nebula_options['plugin_update_warning'])); ?> /><label for="plugin_update_warning">Plugin Warning</label>
										<p class="nebula-help-text short-help form-text text-muted">Control whether or not the plugin update warning appears on admin pages. (Default: <?php echo ucwords($nebula_options_defaults['plugin_update_warning']); ?>)</p>
										<p class="option-keywords">discretionary</p>
									</div>
								</div><!-- /option-group -->

								<div class="option-group">
									<h3>Prototyping</h3>

									<?php $themes = wp_get_themes(); ?>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[prototype_mode]" id="prototype_mode" value="1" <?php checked('1', !empty($nebula_options['prototype_mode'])); ?> /><label for="prototype_mode">Prototype Mode</label>
										<p class="nebula-help-text short-help form-text text-muted">When prototyping, enable this setting. (Default: <?php echo ucwords($nebula_options_defaults['prototype_mode']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">Use the wireframe theme and production theme settings to develop the site while referencing the prototype. Use the staging theme to edit the site or develop new features while the site is live. If the staging theme is the active theme, use the Advanced Setting dropdown for "Theme For Everything" and choose a theme there for general visitors (Note: If using this setting, you may need to select that same theme for the admin-ajax option too!).</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group" dependent-of="prototype_mode">
										<label for="wireframe_theme">Wireframe Theme</label>
										<select name="nebula_options[wireframe_theme]" id="wireframe_theme" class="form-control nebula-validate-select">
											<option value="" <?php selected('', $nebula_options['wireframe_theme']); ?>>None</option>
		                                    <?php foreach ( $themes as $key => $value ): ?>
		                                        <option value="<?php echo $key; ?>" <?php selected($key, $nebula_options['wireframe_theme']); ?>><?php echo $value->get('Name') . ' (' . $key . ')'; ?></option>
		                                    <?php endforeach; ?>
										</select>
										<p class="nebula-help-text short-help form-text text-muted">The theme to use as the wireframe. Viewing this theme will trigger a greyscale view.</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group" dependent-of="prototype_mode">
										<label for="staging_theme">Staging Theme</label>
										<select name="nebula_options[staging_theme]" id="staging_theme" class="form-control nebula-validate-select">
											<option value="" <?php selected('', $nebula_options['staging_theme']); ?>>None</option>
		                                    <?php foreach ( $themes as $key => $value ): ?>
		                                        <option value="<?php echo $key; ?>" <?php selected($key, $nebula_options['staging_theme']); ?>><?php echo $value->get('Name') . ' (' . $key . ')'; ?></option>
		                                    <?php endforeach; ?>
										</select>
										<p class="nebula-help-text short-help form-text text-muted">The theme to use for staging new features. This is useful for site development after launch.</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group" dependent-of="prototype_mode">
										<label for="production_theme">Production (Live) Theme</label>
										<select name="nebula_options[production_theme]" id="production_theme" class="form-control nebula-validate-select">
											<option value="" <?php selected('', $nebula_options['production_theme']); ?>>None</option>
		                                    <?php foreach ( $themes as $key => $value ): ?>
		                                        <option value="<?php echo $key; ?>" <?php selected($key, $nebula_options['production_theme']); ?>><?php echo $value->get('Name') . ' (' . $key . ')'; ?></option>
		                                    <?php endforeach; ?>
										</select>
										<p class="nebula-help-text short-help form-text text-muted">The theme to use for production/live. This theme will become the live site.</p>
										<p class="option-keywords"></p>
									</div>
								</div><!-- /option-group -->

								<?php do_action('nebula_options_interface_functions'); ?>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col-xl-8">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!-- /tab-pane -->









					<div id="analytics" class="tab-pane <?php echo ( $active_tab === 'analytics' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2>Analytics</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col-xl-8">
								<div class="option-group">
									<div class="form-group important-option">
										<label for="ga_tracking_id">Google Analytics Tracking ID</label>
										<input type="text" name="nebula_options[ga_tracking_id]" id="ga_tracking_id" class="form-control nebula-validate-regex" data-valid-regex="^UA-\d+-\d+$" value="<?php echo $nebula_options['ga_tracking_id']; ?>" placeholder="UA-00000000-1" />
										<p class="nebula-help-text short-help form-text text-muted">This will add the tracking number to the appropriate locations.</p>
										<p class="option-keywords">remote resource recommended minor page speed impact</p>
									</div>

									<div class="form-group" dependent-of="gatrackingid">
										<input type="checkbox" name="nebula_options[ga_wpuserid]" id="ga_wpuserid" value="1" <?php checked('1', !empty($nebula_options['ga_wpuserid'])); ?> /><label for="ga_wpuserid">Use WordPress User ID</label>
										<p class="nebula-help-text short-help form-text text-muted">Use the WordPress User ID as the Google Analytics User ID. (Default: <?php echo ucwords($nebula_options_defaults['ga_wpuserid']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">This allows more accurate user reporting. Note: Users who share accounts (including developers/clients) can cause inaccurate reports! This functionality is most useful when opening sign-ups to the public.</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group" dependent-of="gatrackingid">
										<input type="checkbox" name="nebula_options[ga_displayfeatures]" id="ga_displayfeatures" value="1" <?php checked('1', !empty($nebula_options['ga_displayfeatures'])); ?> /><label for="ga_displayfeatures">Display Features</label>
										<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">Google display features</a> in the analytics tag. (Default: <?php echo ucwords($nebula_options_defaults['ga_displayfeatures']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">This enables Advertising Features in Google Analytics, such as Remarketing, Demographics and Interest Reporting, and more.</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group" dependent-of="gatrackingid">
										<input type="checkbox" name="nebula_options[ga_linkid]" id="ga_linkid" value="1" <?php checked('1', !empty($nebula_options['ga_linkid'])); ?> /><label for="ga_linkid">Enhanced Link Attribution (Link ID)</label>
										<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-link-attribution" target="_blank">Enhanced Link Attribution</a> in the Property Settings of the Google Analytics Admin. (Default: <?php echo ucwords($nebula_options_defaults['ga_linkid']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted">This improves the accuracy of your In-Page Analytics report by automatically differentiating between multiple links to the same URL on a single page by using link element IDs.</p>
										<p class="option-keywords">minor page speed impact</p>
									</div>

									<div class="form-group">
										<label for="adwords_remarketing_conversion_id">AdWords Remarketing Conversion ID</label>
										<input type="text" name="nebula_options[adwords_remarketing_conversion_id]" id="adwords_remarketing_conversion_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['adwords_remarketing_conversion_id']; ?>" placeholder="000000000" />
										<p class="nebula-help-text short-help form-text text-muted">This conversion ID is used to enable the Google AdWords remarketing tag.</p>
										<p class="option-keywords">remote resource minor page speed impact</p>
									</div>

									<div class="form-group">
										<label for="google_optimize_id">Google Optimize ID</label>
										<input type="text" name="nebula_options[google_optimize_id]" id="google_optimize_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_optimize_id']; ?>" placeholder="GTM-0000000" />
										<p class="nebula-help-text short-help form-text text-muted">The ID used by <a href="https://optimize.google.com/optimize/home/" target="_blank">Google Optimize</a> to enable tests.</p>
										<p class="nebula-help-text more-help form-text text-muted">Entering the ID here will enable both the Google Analytics require tag and the style tag hiding snippet in the head.</p>
										<p class="option-keywords">remote resource minor page speed impact</p>
									</div>

									<div class="form-group">
										<label for="hostnames">Valid Hostnames</label>
										<input type="text" name="nebula_options[hostnames]" id="hostnames" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hostnames']; ?>" placeholder="<?php echo nebula()->url_components('domain'); ?>" />
										<p class="nebula-help-text short-help form-text text-muted">These help generate regex patterns for Google Analytics filters.</p>
										<p class="nebula-help-text more-help form-text text-muted">It is also used for the is_site_live() function! Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)! Include this RegEx pattern for a filter/segment <a href="https://gearside.com/nebula/utilities/domain-regex-generator/?utm_campaign=documentation&utm_medium=options&utm_source=valid+hostnames%20help" target="_blank">(Learn how to use this)</a>: <input type="text" value="<?php echo nebula()->valid_hostname_regex(); ?>" readonly style="width: 50%;" /></p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group">
										<label for="google_search_console_verification">Google Search Console Verification</label>
										<input type="text" name="nebula_options[google_search_console_verification]" id="google_search_console_verification" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_search_console_verification']; ?>" placeholder="AAAAAA..." />
										<p class="nebula-help-text short-help form-text text-muted">This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank">Google Search Console</a>.</p>
										<p class="nebula-help-text more-help form-text text-muted">Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</p>
										<p class="option-keywords">recommended seo</p>
									</div>

									<div class="form-group">
										<label for="facebook_custom_audience_pixel_id">Facebook Custom Audience Pixel ID</label>
										<input type="text" name="nebula_options[facebook_custom_audience_pixel_id]" id="facebook_custom_audience_pixel_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_custom_audience_pixel_id']; ?>" placeholder="000000000000000" />
										<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.facebook.com/docs/facebook-pixel" target="_blank">Facebook Custom Audience Pixel</a> tracking.</p>
										<p class="option-keywords">remote resource minor page speed impact</p>
									</div>
								</div><!-- /option-group -->
							</div><!--/col-->
							<div class="col-xl-8">

								<div class="option-group">
									<h3>Custom Dimensions</h3>
									<p class="text-muted">These are optional dimensions that can be passed into Google Analytics which allows for 20 custom dimensions (or 200 for Google Analytics Premium). To set these up, define the Custom Dimension in the Google Analytics property, then paste the dimension index string ("dimension1", "dimension12", etc.) into the appropriate input field below. The scope for each dimension is noted in their respective help sections. Dimensions that require additional code are marked with a *.</p>

									<?php $dimension_regex = '^dimension([0-9]{1,3})$'; ?>

									<div class="option-sub-group">
										<h4>Post Data</h4>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Author</div>
												<input type="text" name="nebula_options[cd_author]" id="cd_author" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_author']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks the article author's name on single posts. Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Categories</div>
												<input type="text" name="nebula_options[cd_categories]" id="cd_categories" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_categories']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks the article author's name on single posts. Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Tags</div>
												<input type="text" name="nebula_options[cd_tags]" id="cd_tags" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_tags']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends a string of all the post's tags to the pageview hit. Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Word Count</div>
												<input type="text" name="nebula_options[cd_wordcount]" id="cd_wordcount" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_wordcount']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends word count range for single posts. Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Publish Year</div>
												<input type="text" name="nebula_options[cd_publishyear]" id="cd_publishyear" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_publishyear']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends the year the post was published. Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Scroll Depth</div>
												<input type="text" name="nebula_options[cd_scrolldepth]" id="cd_scrolldepth" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_scrolldepth']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Information tied to the event such as "Scanner" or "Reader". Scope: Hit</p>
											<p class="nebula-help-text more-help form-text text-muted">This dimension is tied to events, so pageviews will not have data (use the Top Event report).</p>
											<p class="option-keywords"></p>
										</div>
									</div><!-- /sub-group -->

									<div class="option-sub-group">
										<h4>Business Data</h4>

										<div class="form-group" dependent-or="business_hours_sunday_enabled business_hours_monday_enabled business_hours_tuesday_enabled business_hours_wednesday_enabled business_hours_thursday_enabled business_hours_friday_enabled business_hours_saturday_enabled">
											<div class="input-group">
												<div class="input-group-addon">Business Hours</div>
												<input type="text" name="nebula_options[cd_businesshours]" id="cd_businesshours" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_businesshours']; ?>" />
											</div>
											<p class="dependent-note hidden">This option is dependent on Business Hours (Metadata tab).</p>
											<p class="nebula-help-text short-help form-text text-muted">Passes "During Business Hours", or "Non-Business Hours". Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Relative Time</div>
												<input type="text" name="nebula_options[cd_relativetime]" id="cd_relativetime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_relativetime']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends the relative time (Ex: "Late Morning", "Early Evening", etc.) based on the business timezone (via WordPress settings). Scope: Hit</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group" dependent-of="weather">
											<div class="input-group">
												<div class="input-group-addon">Weather</div>
												<input type="text" name="nebula_options[cd_weather]" id="cd_weather" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_weather']; ?>" />
											</div>
											<p class="dependent-note hidden">This option is dependent on Weather Detection (Functions tab) being enabled.</p>
											<p class="nebula-help-text short-help form-text text-muted">Sends the current weather conditions (at the business location) as a dimension. Scope: Hit</p>
											<p class="option-keywords">location</p>
										</div>

										<div class="form-group" dependent-of="weather">
											<div class="input-group">
												<div class="input-group-addon">Temperature</div>
												<input type="text" name="nebula_options[cd_temperature]" id="cd_temperature" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_temperature']; ?>" />
											</div>
											<p class="dependent-note hidden">This option is dependent on Weather Detection (Functions tab) being enabled.</p>
											<p class="nebula-help-text short-help form-text text-muted">Sends temperature ranges (at the business location) in 5&deg;F intervals. Scope: Hit</p>
											<p class="option-keywords">location</p>
										</div>
									</div><!-- /sub-group -->

									<div class="option-sub-group">
										<h4>User Data</h4>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Role</div>
												<input type="text" name="nebula_options[cd_role]" id="cd_role" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_role']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends the current user's role (as well as staff affiliation if available) for associated users. Scope: User</p>
											<p class="nebula-help-text more-help form-text text-muted">Session ID does contain this information, but this is explicitly more human readable (and scoped to the user).</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Session ID</div>
												<input type="text" name="nebula_options[cd_sessionid]" id="cd_sessionid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_sessionid']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">ID system so that you can group hits into specific user sessions. Scope: Session</p>
											<p class="nebula-help-text more-help form-text text-muted">This ID is not personally identifiable and therefore fits within the <a href="https://support.google.com/analytics/answer/2795983" target="_blank">Google Analytics ToS</a> for PII. <a href="https://gearside.com/nebula/functions/nebula_session_id/?utm_campaign=documentation&utm_medium=options&utm_source=session+id%20help" target="_blank">Session ID Documentation &raquo;</a></p>
											<p class="option-keywords">recommended</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">User ID</div>
												<input type="text" name="nebula_options[cd_userid]" id="cd_userid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_userid']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">If allowing visitors to sign up to create WordPress accounts, this will send user IDs to Google Analytics. Scope: User</p>
											<p class="nebula-help-text more-help form-text text-muted">User IDs are also passed in the Session ID, but this scope is tied more specifically to the user (it can often capture data even when they are not currently logged in).</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Facebook ID</div>
												<input type="text" name="nebula_options[cd_fbid]" id="cd_fbid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_fbid']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Send Facebook ID to Google Analytics when using Facebook Connect API. Scope: User</p>
											<p class="nebula-help-text more-help form-text text-muted">Add the ID to this URL to view it: <code>https://www.facebook.com/app_scoped_user_id/</code></p>
											<p class="option-keywords">social</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Local Timestamp</div>
												<input type="text" name="nebula_options[cd_timestamp]" id="cd_timestamp" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_timestamp']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Adds a timestamp (in the user's local time) with timezone offset. Scope: Hit</p>
											<p class="nebula-help-text more-help form-text text-muted">Ex: "1449332547 (2015/12/05 11:22:26.886 UTC-05:00)". Can be compared to the server time stored in the Session ID.</p>
											<p class="option-keywords">location recommended</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">First Interaction</div>
												<input type="text" name="nebula_options[cd_firstinteraction]" id="cd_firstinteraction" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_firstinteraction']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Stores a timestamp for the first time the user visited the site. Scope: User</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Window Type</div>
												<input type="text" name="nebula_options[cd_windowtype]" id="cd_windowtype" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_windowtype']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Stores the type of window the site is being accessed from (Ex: Iframe or Standalone App). Scope: Hit</p>
											<p class="nebula-help-text more-help form-text text-muted">This only records alternate window types (non-standard browser windows).</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Geolocation</div>
												<input type="text" name="nebula_options[cd_geolocation]" id="cd_geolocation" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_geolocation']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Allows latitude and longitude coordinates to be sent after being detected. Scope: Session</p>
											<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work! </p>
											<p class="option-keywords">location</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Geolocation Accuracy</div>
												<input type="text" name="nebula_options[cd_geoaccuracy]" id="cd_geoaccuracy" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_geoaccuracy']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Allows geolocation accuracy to be sent after being detected. Scope: Session</p>
											<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work!</p>
											<p class="option-keywords">location</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Geolocation Name</div>
												<input type="text" name="nebula_options[cd_geoname]" id="cd_geoname" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_geoname']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Allows named location information to be sent after being detected using map polygons. Scope: Session</p>
											<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work!</p>
											<p class="option-keywords">location</p>
										</div>

										<div class="form-group" dependent-of="adblock_detect">
											<div class="input-group">
												<div class="input-group-addon">Ad Blocker</div>
												<input type="text" name="nebula_options[cd_adblocker]" id="cd_adblocker" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_adblocker']; ?>" />
											</div>
											<p class="dependent-note hidden">This option is dependent on Ad Block Detection being enabled.</p>
											<p class="nebula-help-text short-help form-text text-muted">Detects if the user is blocking ads. Scope: Session</p>
											<p class="nebula-help-text more-help form-text text-muted">This can be used even if not intending to serve ads on this site. It is important that this dimension is not set to the "hit" scope.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Media Query: Breakpoint</div>
												<input type="text" name="nebula_options[cd_mqbreakpoint]" id="cd_mqbreakpoint" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_mqbreakpoint']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Detect which media query breakpoint is associated with this hit. Scope: Hit</p>
											<p class="option-keywords">autotrack</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Media Query: Resolution</div>
												<input type="text" name="nebula_options[cd_mqresolution]" id="cd_mqresolution" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_mqresolution']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Detect the resolution factor associated with this hit. Scope: Hit</p>
											<p class="option-keywords">autotrack</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Media Query: Orientation</div>
												<input type="text" name="nebula_options[cd_mqorientation]" id="cd_mqorientation" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_mqorientation']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Detect the device orientation associated with this hit. Scope: Hit</p>
											<p class="option-keywords">autotrack</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Notable POI</div>
												<input type="text" name="nebula_options[cd_notablepoi]" id="cd_notablepoi" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_notablepoi']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Stores named locations when detected. Scope: User</p>
											<p class="nebula-help-text more-help form-text text-muted">Stores named IP addresses (from the Administration tab). Also passes data using the ?poi query string (useful for email marketing using personalization within links). Also sends value of input fields with class "nebula-poi" on form submits (when applicable).</p>
											<p class="option-keywords">recommended</p>
										</div>
									</div><!-- /sub-group -->

									<div class="option-sub-group">
										<h4>Conversion Data</h4>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Event Intent</div>
												<input type="text" name="nebula_options[cd_eventintent]" id="cd_eventintent" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_eventintent']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Log whether the event was true, or just a possible intention. Scope: Hit</p>
											<p class="option-keywords">recommended</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Contact Method</div>
												<input type="text" name="nebula_options[cd_contactmethod]" id="cd_contactmethod" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_contactmethod']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">If the user triggers a contact event, the method of contact is stored here. Scope: Session</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Form Timing</div>
												<input type="text" name="nebula_options[cd_formtiming]" id="cd_formtiming" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_formtiming']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends form timings along with the each submission. Scope: Hit</p>
											<p class="nebula-help-text more-help form-text text-muted">Timings are automatically sent to Google Analytics in Nebula, but are sampled in the User Timings report. Data will be in milliseconds.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Video Watcher</div>
												<input type="text" name="nebula_options[cd_videowatcher]" id="cd_videowatcher" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_videowatcher']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sets a dimension when videos are started and finished. Scope: Session</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Ecommerce Cart</div>
												<input type="text" name="nebula_options[cd_woocart]" id="cd_woocart" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_woocart']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">If the user has any product(s) in their cart. Scope: Hit</p>
											<p class="option-keywords">ecommerce woocommerce</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Ecommerce Customer</div>
												<input type="text" name="nebula_options[cd_woocustomer]" id="cd_woocustomer" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_woocustomer']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sets a dimension when a user completes the checkout process in WooCommerce. Scope: User</p>
											<p class="nebula-help-text more-help form-text text-muted">Appears in Google Analytics as "Order Received".</p>
											<p class="option-keywords">ecommerce woocommerce</p>
										</div>
									</div><!-- /sub-group -->

									<?php do_action('nebula_options_interface_custom_dimensions'); ?>
								</div><!-- /option-group -->

								<div class="option-group">
									<h3>Custom Metrics</h3>
									<p class="text-muted">These are optional metrics that can be passed into Google Analytics which allows for 20 custom metrics (or 200 for Google Analytics Premium). To set these up, define the Custom Metric in the Google Analytics property, then paste the metric index string ("metric1", "metric12", etc.) into the appropriate input field below. The scope and format for each metric is noted in their respective help sections. Metrics that require additional code are marked with a *. These are useful for manual interpretation of data, or to be included in Calculated Metrics formulas.</p>

									<?php $metric_regex = '^metric([0-9]{1,3})$'; ?>

									<div class="option-sub-group">
										<h4>Conversion Data</h4>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Notable Downloads</div>
												<input type="text" name="nebula_options[cm_notabledownloads]" id="cm_notabledownloads" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_notabledownloads']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user downloads a notable file. Scope: Hit, Format: Integer</p>
											<p class="nebula-help-text short-help form-text text-muted">To use, add the class "notable" to either the or its parent.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Form Page Views</div>
												<input type="text" name="nebula_options[cm_formpageviews]" id="cm_formpageviews" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formpageviews']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user views a page containing a form. Scope: Hit, Format: Integer</p>
											<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Form Impressions</div>
												<input type="text" name="nebula_options[cm_formimpressions]" id="cm_formimpressions" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formimpressions']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a form is in view as the user scrolls. Scope: Hit, Format: Integer</p>
											<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Form Starts</div>
												<input type="text" name="nebula_options[cm_formstarts]" id="cm_formstarts" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formstarts']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user begins entering a form. Scope: Hit, Format: Integer</p>
											<p class="nebula-help-text short-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Form Submissions</div>
												<input type="text" name="nebula_options[cm_formsubmissions]" id="cm_formsubmissions" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formsubmissions']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user submits a form. Scope: Hit, Format: Integer</p>
											<p class="nebula-help-text short-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Engaged Readers</div>
												<input type="text" name="nebula_options[cm_engagedreaders]" id="cm_engagedreaders" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_engagedreaders']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Counts when a user has completed reading an article (and is not determined to be a "scanner"). Scope: Hit, Format: Integer</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Max Scroll Percent</div>
												<input type="text" name="nebula_options[cm_maxscroll]" id="cm_maxscroll" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_maxscroll']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Calculates the maximum scroll percentage the user reached per page. Scope: Hit, Format: Integer</p>
											<p class="nebula-help-text short-help form-text text-muted">Use a calculated Metric in Google Analytics called "Avg. Max Scroll Percentage" of {{Max Scroll Percentage}}/(100*{{Unique Pageviews}}) to show Average Max Scroll Percentage per page. Create a custom report with the metrics "Avg. Max Scroll Percentage" and "Unique Pageviews" and dimensions "Page", "Referral Source", etc.</p>
											<p class="option-keywords"></p>
										</div>
									</div><!-- /sub-group -->

									<div class="option-sub-group">
										<h4>Video Data</h4>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Video Starts</div>
												<input type="text" name="nebula_options[cm_videostarts]" id="cm_videostarts" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_videostarts']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user begins playing a video. Scope: Hit, Format: Integer</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Video Play Time</div>
												<input type="text" name="nebula_options[cm_videoplaytime]" id="cm_videoplaytime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_videoplaytime']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks playing duration when a user pauses or completes a video. Scope: Hit, Format: Time</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Video Completions</div>
												<input type="text" name="nebula_options[cm_videocompletions]" id="cm_videocompletions" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_videocompletions']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user completes playing a video. Scope: Hit, Format: Integer</p>
											<p class="option-keywords"></p>
										</div>
									</div><!-- /sub-group -->

									<div class="option-sub-group">
										<h4>Miscellaneous</h4>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Word Count</div>
												<input type="text" name="nebula_options[cm_wordcount]" id="cm_wordcount" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_wordcount']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Sends word count for single posts. Scope: Hit, Format: Integer</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Autocomplete Searches</div>
												<input type="text" name="nebula_options[cm_autocompletesearches]" id="cm_autocompletesearches" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_autocompletesearches']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a set of autocomplete search results is returned to the user (count is the search, not the result quantity). Scope: Hit, Format: Integer</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Autocomplete Search Clicks</div>
												<input type="text" name="nebula_options[cm_autocompletesearchclicks]" id="cm_autocompletesearchclicks" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_autocompletesearchclicks']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">Tracks when a user clicks an autocomplete search result. Scope: Hit, Format: Integer</p>
											<p class="option-keywords"></p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Page Visible</div>
												<input type="text" name="nebula_options[cm_pagevisible]" id="cm_pagevisible" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_pagevisible']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">The amount of time (in seconds) the page was in the visible state (tab/window visible) Scope: Hit, Format: Time</p>
											<p class="option-keywords">autotrack</p>
										</div>

										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Page Hidden</div>
												<input type="text" name="nebula_options[cm_pagehidden]" id="cm_pagehidden" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_pagehidden']; ?>" />
											</div>
											<p class="nebula-help-text short-help form-text text-muted">The amount of time (in seconds) the page was in the hidden state (tab/window not visible) Scope: Hit, Format: Time</p>
											<p class="option-keywords">autotrack</p>
										</div>
									</div><!-- /sub-group -->

									<?php do_action('nebula_options_interface_custom_metrics'); ?>
								</div><!-- /option-group -->

								<?php do_action('nebula_options_interface_analytics'); ?>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col-xl-8">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!-- /tab-pane -->








					<div id="apis" class="tab-pane <?php echo ( $active_tab === 'apis' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2>APIs</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col-xl-8">
								<div class="form-group">
									<label for="google_font_url">Google Font</label>
									<input type="text" name="nebula_options[google_font_url]" id="google_font_url" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_font_url']; ?>" placeholder="http://fonts.googleapis.com/css?family=Open+Sans:400,800" />
									<p class="nebula-help-text short-help form-text text-muted">Choose which <a href="https://www.google.com/fonts" target="_blank">Google Font</a> is used by default for this site by pasting the entire font URL.</p>
									<p class="nebula-help-text more-help form-text text-muted">The default font uses the native system font of the user's device.</p>
									<p class="option-keywords">remote resource minor page speed impact</p>
								</div>

								<div class="form-group mb-2">
									<label for="google_browser_api_key">Google Public API</label>

									<div class="input-group">
										<div class="input-group-addon">Browser Key</div>
										<input type="text" name="nebula_options[google_browser_api_key]" id="google_browser_api_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_browser_api_key']; ?>" />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Server Key</div>
										<input type="text" name="nebula_options[google_server_api_key]" id="google_server_api_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_server_api_key']; ?>" />
									</div>

									<p class="nebula-help-text short-help form-text text-muted">API keys from the <a href="https://console.developers.google.com/project" target="_blank">Google Developers Console</a>.</p>
									<p class="nebula-help-text more-help form-text text-muted">In the Developers Console make a new project (if you don't have one yet). Under "Credentials" create a new key. Your current server IP address is <code><?php echo $_SERVER['SERVER_ADDR']; ?></code> (for server key whitelisting). Do not use the Server Key in JavaScript or any client-side code!</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<label for="cse_id">Google Custom Search Engine</label>
									<div class="input-group">
										<div class="input-group-addon">Engine ID</div>
										<input type="text" name="nebula_options[cse_id]" id="cse_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['cse_id']; ?>" placeholder="000000000000000000000:aaaaaaaa_aa" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">For <a href="https://gearside.com/nebula/functions/pagesuggestion/?utm_campaign=documentation&utm_medium=options&utm_source=gcse+help" target="_blank">page suggestions</a> on 404 and No Search Results pages.</p>
									<p class="nebula-help-text more-help form-text text-muted"><a href="https://www.google.com/cse/manage/all">Register here</a>, then select "Add", input your website's URL in "Sites to Search". Then click the one you just made and click the "Search Engine ID" button.</p>
									<p class="option-keywords">remote resource minor page speed impact</p>
								</div>

								<div class="form-group hidden">
									<label for="gcm_sender_id">Google Cloud Messaging Sender ID</label>
									<input type="text" name="nebula_options[gcm_sender_id]" id="gcm_sender_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['gcm_sender_id']; ?>" placeholder="000000000000" />
									<p class="nebula-help-text short-help form-text text-muted">The Google Cloud Messaging (GCM) Sender ID from the <a href="https://console.developers.google.com/project" target="_blank">Developers Console</a>.</p>
									<p class="nebula-help-text more-help form-text text-muted">This is the "Project number" within the project box on the Dashboard. Do not include parenthesis or the "#" symbol. This is used for push notifications. <strong>*Note: This feature is still in development and not currently active!</strong></p>
									<p class="option-keywords"></p>
								</div>
							</div><!--/col-->
							<div class="col-xl-8">
								<div class="form-group mb-2">
									<label for="hubspot_api">Hubspot</label>

									<div class="input-group">
										<div class="input-group-addon">API Key</div>
										<input type="text" name="nebula_options[hubspot_api]" id="hubspot_api" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hubspot_api']; ?>" />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Portal ID</div>
										<input type="text" name="nebula_options[hubspot_portal]" id="hubspot_portal" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hubspot_portal']; ?>" />
									</div>

									<p class="nebula-help-text short-help form-text text-muted">Enter your Hubspot API key and Hubspot Portal ID here.</p>
									<p class="nebula-help-text more-help form-text text-muted">It can be obtained from your <a href="https://app.hubspot.com/hapikeys">API Keys page under Integrations in your account</a>. Your Hubspot Portal ID (or Hub ID) is located in the upper right of your account screen.</p>
									<p class="option-keywords">remote resource major page speed impact crm</p>
								</div>

								<div class="form-group">
									<label for="disqus_shortname">Disqus Shortname</label>
									<input type="text" name="nebula_options[disqus_shortname]" id="disqus_shortname" class="form-control nebula-validate-text" value="<?php echo $nebula_options['disqus_shortname']; ?>" />
									<p class="nebula-help-text short-help form-text text-muted">Enter your Disqus shortname here.</p>
									<p class="nebula-help-text more-help form-text text-muted"><a href="https://disqus.com/admin/create/" target="_blank">Sign-up for an account here</a>. In your Disqus account settings (where you will find your shortname), please uncheck the "Discovery" box.</p>
									<p class="option-keywords">social remote resource moderate page speed impact comments</p>
								</div>

								<div class="form-group">
									<label for="facebook_app_id">Facebook</label>
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> App ID</span></div>
										<input type="text" name="nebula_options[facebook_app_id]" id="facebook_app_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_app_id']; ?>" placeholder="000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The App ID of the associated Facebook page/app.</p>
									<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> App Secret</span></div>
										<input type="text" name="nebula_options[facebook_app_secret]" id="facebook_app_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_app_secret']; ?>" placeholder="00000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The App Secret of the associated Facebook page/app.</p>
									<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> Access Token</span></div>
										<input type="text" name="nebula_options[facebook_access_token]" id="facebook_access_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_access_token']; ?>" placeholder="000000000000000|000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The Access Token of the associated Facebook page/app.</p>
									<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<label for="twitter_consumer_key">Twitter</label>
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Consumer Key</span></div>
										<input type="text" name="nebula_options[twitter_consumer_key]" id="twitter_consumer_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_consumer_key']; ?>" placeholder="000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The Consumer Key key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Consumer Secret</span></div>
										<input type="text" name="nebula_options[twitter_consumer_secret]" id="twitter_consumer_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_consumer_secret']; ?>" placeholder="000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The Consumer Secret key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Bearer Token</span></div>
										<input type="text" name="nebula_options[twitter_bearer_token]" id="twitter_bearer_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_bearer_token']; ?>" placeholder="000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The bearer token is for creating custom Twitter feeds: <a href="https://gearside.com/nebula/utilities/twitter-bearer-token-generator/?utm_campaign=documentation&utm_medium=options&utm_source=twitter+help" target="_blank">Generate a bearer token here</a></p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<label for="instagram_user_id">Instagram</label>
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> User ID</span></div>
										<input type="text" name="nebula_options[instagram_user_id]" id="instagram_user_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_user_id']; ?>" placeholder="00000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
									<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> Access Token</span></div>
										<input type="text" name="nebula_options[instagram_access_token]" id="instagram_access_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_access_token']; ?>" placeholder="000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
									<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> Client ID</span></div>
										<input type="text" name="nebula_options[instagram_client_id]" id="instagram_client_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_client_id']; ?>" placeholder="000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
									<p class="option-keywords">social remote resource</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> Client Secret</span></div>
										<input type="text" name="nebula_options[instagram_client_secret]" id="instagram_client_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_client_secret']; ?>" placeholder="000000000000000000000000000000" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
									<p class="option-keywords">social remote resource</p>
								</div>
							</div><!--/col-->

							<?php do_action('nebula_options_interface_apis'); ?>
						</div><!--/row-->
						<div class="row save-row">
							<div class="col-xl-8">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!-- /tab-pane -->







					<div id="administration" class="tab-pane <?php echo ( $active_tab === 'administration' )? 'active' : ''; ?>">
						<div class="row title-row">
							<div class="col-xl-8">
								<h2>Administration</h2>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row">
							<div class="col-xl-8">
								<div class="option-group">
									<h3>Staff and Notable Users</h3>

									<?php
										$current_user = wp_get_current_user();
										list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
									?>

									<div class="form-group">
										<label for="dev_ip">Developer IPs</label>
										<input type="text" name="nebula_options[dev_ip]" id="dev_ip" class="form-control nebula-validate-text" value="<?php echo $nebula_options['dev_ip']; ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />
										<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the developer to enable specific console logs and other dev info.<br/>Your current IP address is <code><?php echo $_SERVER['REMOTE_ADDR']; ?></code></p>
										<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/192\.168\./i</code></p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group">
										<label for="dev_email_domain">Developer Email Domains</label>
										<input type="text" name="nebula_options[dev_email_domain]" id="dev_email_domain" class="form-control nebula-validate-text" value="<?php echo $nebula_options['dev_email_domain']; ?>" placeholder="<?php echo $current_user_domain; ?>" />
										<p class="nebula-help-text short-help form-text text-muted">Comma separated domains of the developer emails (without the "@") to enable specific console logs and other dev info.<br/>Your email domain is: <code><?php echo $current_user_domain; ?></code></p>
										<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/@pinckneyhugo\./i</code></p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group">
										<label for="client_ip">Client IPs</label>
										<input type="text" name="nebula_options[client_ip]" id="client_ip" class="form-control nebula-validate-text" value="<?php echo $nebula_options['client_ip']; ?>" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />
										<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the client to enable certain features.<br/>Your current IP address is <code><?php echo $_SERVER['REMOTE_ADDR']; ?></code></p>
										<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/192\.168\./i</code></p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group">
										<label for="client_email_domain">Client Email Domains</label>
										<input type="text" name="nebula_options[client_email_domain]" id="client_email_domain" class="form-control nebula-validate-text" value="<?php echo $nebula_options['client_email_domain']; ?>" placeholder="<?php echo $current_user_domain; ?>" />
										<p class="nebula-help-text short-help form-text text-muted">Comma separated domains of the developer emails (without the "@") to enable certain features.<br/>Your email domain is: <code><?php echo $current_user_domain; ?></code></p>
										<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/@pinckneyhugo\./i</code></p>
										<p class="option-keywords">recommended</p>
									</div>

									<div class="form-group">
										<label for="notableiplist">Notable IPs</label>
										<textarea name="nebula_options[notableiplist]" id="notableiplist" class="form-control nebula-validate-textarea" rows="6" placeholder="192.168.0.1 Name Here"><?php echo $nebula_options['notableiplist']; ?></textarea>
										<p class="nebula-help-text short-help form-text text-muted">A list of named IP addresses. Enter each IP (or RegEx to match) on a new line with a space separating the IP address and name.</p>
										<p class="nebula-help-text more-help form-text text-muted">Name IPs by location to avoid <a href="https://support.google.com/analytics/answer/2795983" target="_blank">Personally Identifiable Information (PII)</a> issues (Do not use peoples' names). Be sure to set up a Custom Dimension in Google Analytics and add the dimension index in the Analytics tab! Tip: IP data is sent with <a href="https://gearside.com/nebula/examples/contact-form-7/?utm_campaign=documentation&utm_medium=options&utm_source=notable+ips%20help" target="_blank">Nebula contact forms</a>!</p>
										<p class="option-keywords">recommended</p>
									</div>
								</div>
							</div><!--/col-->
							<div class="col-xl-8">
								<div class="option-group">
									<h3>Dashboard Reference Links</h3>

									<div class="form-group">
										<label for="cpanel_url">Server Control Panel</label>
										<input type="text" name="nebula_options[cpanel_url]" id="cpanel_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['cpanel_url']; ?>" placeholder="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" />
										<p class="nebula-help-text short-help form-text text-muted">Link to the control panel of the hosting account.</p>
										<p class="nebula-help-text more-help form-text text-muted">cPanel on this domain would be: <a href="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" target="_blank"><?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082</a></p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group">
										<label for="hosting_url">Hosting</label>
										<input type="text" name="nebula_options[hosting_url]" id="hosting_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['hosting_url']; ?>" placeholder="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>/" />
										<p class="nebula-help-text short-help form-text text-muted">Link to the server host for easy access to support and other information.</p>
										<p class="nebula-help-text more-help form-text text-muted">Server detected as <a href="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>" target="_blank">http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?></a></p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group">
										<label for="registrar_url">Domain Registrar</label>
										<input type="text" name="nebula_options[registrar_url]" id="registrar_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['registrar_url']; ?>" />
										<p class="nebula-help-text short-help form-text text-muted">Link to the domain registrar used for access to pointers, forwarding, and other information.</p>
										<p class="option-keywords"></p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[google_adsense_url]" id="google_adsense_url" value="1" <?php checked('1', !empty($nebula_options['google_adsense_url'])); ?> /><label for="google_adsense_url">Google AdSense</label>
										<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://www.google.com/adsense/" target="_blank">Google AdSense</a> account. (Default: <?php echo ucwords($nebula_options_defaults['google_adsense_url']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[amazon_associates_url]" id="amazon_associates_url" value="1" <?php checked('1', !empty($nebula_options['amazon_associates_url'])); ?> /><label for="amazon_associates_url">Amazon Associates</label>
										<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://affiliate-program.amazon.com/home" target="_blank">Amazon Associates</a> account. (Default: <?php echo ucwords($nebula_options_defaults['amazon_associates_url']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<input type="checkbox" name="nebula_options[mention_url]" id="mention_url" value="1" <?php checked('1', !empty($nebula_options['mention_url'])); ?> /><label for="mention_url">Mention</label>
										<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://mention.com/" target="_blank">Mention</a> account. (Default: <?php echo ucwords($nebula_options_defaults['mention_url']); ?>)</p>
										<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
										<p class="option-keywords">discretionary</p>
									</div>

									<div class="form-group">
										<label for="notes">Notes</label>
										<textarea name="nebula_options[notes]" id="notes" class="form-control textarea" rows="4"><?php echo $nebula_options['notes']; ?></textarea>
										<p class="nebula-help-text short-help form-text text-muted">This area can be used to keep notes. It is not used anywhere on the front-end.</p>
										<p class="option-keywords"></p>
									</div>
								</div>

								<?php do_action('nebula_options_interface_administration'); ?>
							</div><!--/col-->
						</div><!--/row-->
						<div class="row save-row">
							<div class="col-xl-8">
								<?php submit_button(); ?>
							</div><!--/col-->
						</div><!--/row-->
					</div><!-- /tab-pane -->




					<?php do_action('nebula_options_interface_additional_panes'); ?>





					<?php if ( current_user_can('manage_options') ): ?>
						<div id="diagnostic" class="tab-pane <?php echo ( $active_tab === 'diagnostic' )? 'active' : ''; ?>">
							<div class="row title-row">
								<div class="col-xl-8">
									<h2>Diagnostic</h2>
								</div><!--/col-->
							</div><!--/row-->
							<div class="row">
								<div class="col-xl-8">
									<div class="option-group">
										<div class="form-group">
											<label for="example_option">First Nebula Activation</label>
											<input type="text" id="example_option" class="form-control" readonly />
											<p class="nebula-help-text short-help form-text text-muted">The date when the Nebula theme was first activated.</p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="initialized">Initialization Date</label>
											<input type="text" id="initialized" class="form-control" value="<?php echo $nebula_data['initialized']; ?>" readonly />
											<p class="nebula-help-text short-help form-text text-muted">
												Initialized on:
												<?php if ( !empty($nebula_data['initialized']) ): ?>
													<strong><?php echo date('F j, Y \a\t g:ia', $nebula_data['initialized']); ?></strong> (<?php echo $years_ago = number_format((time()-$nebula_data['initialized'])/31622400, 2); ?> <?php echo ( $years_ago == 1 )? 'year' : 'years'; ?> ago)
												<?php else: ?>
													<strong><a href="/themes.php">Nebula Automation</a> has not been run yet!</strong>
												<?php endif; ?>
											</p>
											<p class="nebula-help-text more-help form-text text-muted">Shows the date of the initial Nebula Automation if it has run yet, otherwise it is empty. To run Nebula Automation, <a href="/themes.php">reactivate the Nebula parent theme here</a>.</p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="edited_yet">Nebula Options Saved Yet?</label>
											<input type="text" class="form-control" value="<?php echo ( $nebula_options['edited_yet'] )? 'Yes' : 'No'; ?>" readonly />
											<input type="text" name="nebula_options[edited_yet]" id="edited_yet" class="form-control hidden" value="true" readonly />
											<p class="nebula-help-text short-help form-text text-muted"></p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="current_version">Current Nebula Version Number</label>
											<input type="text" id="current_version" class="form-control" value="<?php echo $nebula_data['current_version']; ?>" readonly />
											<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version number when it was last saved. It should match: <strong><?php echo nebula()->version('raw'); ?></strong></p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="current_version_date">Last Nebula Version Date</label>
											<input type="text" id="current_version_date" class="form-control" value="<?php echo $nebula_data['current_version_date']; ?>" readonly />
											<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version date when it was last saved. It should match: <strong><?php echo nebula()->version('date'); ?></strong></p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="version_legacy">Legacy Nebula Version?</label>
											<input type="text" id="version_legacy" class="form-control" value="<?php echo $nebula_data['version_legacy']; ?>" readonly />
											<p class="nebula-help-text short-help form-text text-muted">If a future version is deemed incompatible with previous versions, this will become true, and theme update checks will be disabled.</p>
											<p class="nebula-help-text more-help form-text text-muted">Incompatible versions are labeled with a "u" at the end of the version number.</p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="next_version">Latest Github Version</label>
											<input type="text" name="nebula_options[next_version]" id="next_version" class="form-control" value="<?php echo $nebula_data['next_version']; ?>" readonly />
											<p class="nebula-help-text short-help form-text text-muted">The latest version available on Github.</p>
											<p class="nebula-help-text more-help form-text text-muted">Re-checks with <a href="/update-core.php">theme update check</a> only when Nebula Child is activated.</p>
											<p class="option-keywords">readonly</p>
										</div>

										<div class="form-group">
											<label for="online_users">Online Users</label>
											<input type="text" id="online_users" class="form-control" value="<?php echo nebula()->online_users(); ?>" readonly />
											<p class="nebula-help-text short-help form-text text-muted">Currently online and last seen times of logged in users.</p>
											<p class="nebula-help-text more-help form-text text-muted">A value of 1 or greater indicates it is working.</p>
											<p class="option-keywords">readonly</p>
										</div>
									</div>

									<?php do_action('nebula_options_interface_diagnostic'); ?>
								</div><!--/col-->
							</div><!--/row-->
							<div class="row save-row">
								<div class="col-xl-8">
									<?php submit_button(); ?>
								</div><!--/col-->
							</div><!--/row-->
						</div><!-- /tab-pane -->
					<?php endif; ?>

				</div><!--/col-->
			</div><!--/row-->
		</div><!--/container-->
	</form>
</div>

<?php if ( $direct_option ): ?>
	<script>
		if ( jQuery('#<?php echo $direct_option; ?>').length ){
			if ( jQuery('#<?php echo $direct_option; ?>').parents('.multi-form-group').length ){
				jQuery('#<?php echo $direct_option; ?>').closest('.multi-form-group').addClass('highlight');
			} else {
				jQuery('#<?php echo $direct_option; ?>').closest('.form-group').addClass('highlight');
			}

			jQuery('html, body').animate({
				scrollTop: jQuery('#<?php echo $direct_option; ?>').offset().top-95
			}, 500);
		}
	</script>
<?php endif; ?>