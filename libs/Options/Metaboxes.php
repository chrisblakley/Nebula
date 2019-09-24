<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Metaboxes') ){
	trait Metaboxes {
		public function hooks(){
			add_action('admin_head', array($this, 'nebula_options_metaboxes'));
		}

		public function nebula_options_metaboxes(){
			$current_screen = get_current_screen();
			if ( $current_screen->base === 'appearance_page_nebula_options' ){
				//Metadata
				add_meta_box('nebula_site_information', 'Site Information', array($this, 'site_information_metabox'), 'nebula_options', 'metadata');
				add_meta_box('nebula_business_information', 'Business Information', array($this, 'nebula_business_information'), 'nebula_options', 'metadata');
				add_meta_box('nebula_social_networks', 'Social Networks', array($this, 'nebula_social_networks'), 'nebula_options', 'metadata_side');

				//Functions
				add_meta_box('nebula_assets_metabox', 'Assets', array($this, 'nebula_assets_metabox'), 'nebula_options', 'functions_side');
				add_meta_box('nebula_front_end_metabox', 'Front-End', array($this, 'nebula_front_end_metabox'), 'nebula_options', 'functions');

				//Analytics
				add_meta_box('nebula_main_analytics_metabox', 'Main', array($this, 'nebula_main_analytics_metabox'), 'nebula_options', 'analytics');
				add_meta_box('nebula_custom_dimensions_metabox', 'Custom Dimensions', array($this, 'nebula_custom_dimensions_metabox'), 'nebula_options', 'analytics_side');
				add_meta_box('nebula_custom_metrics_metabox', 'Custom Metrics', array($this, 'nebula_custom_metrics_metabox'), 'nebula_options', 'analytics');

				//APIs
				add_meta_box('nebula_main_apis_metabox', 'Main', array($this, 'nebula_main_apis_metabox'), 'nebula_options', 'apis');
				add_meta_box('nebula_social_apis_metabox', 'Social', array($this, 'nebula_social_apis_metabox'), 'nebula_options', 'apis_side');
				add_meta_box('nebula_arbitrary_code_metabox', 'Arbitrary Code', array($this, 'nebula_arbitrary_code_metabox'), 'nebula_options', 'apis');

				//Administration
				add_meta_box('nebula_admin_references_metabox', 'Admin References', array($this, 'nebula_admin_references_metabox'), 'nebula_options', 'administration_side');
				add_meta_box('nebula_admin_notifications_metabox', 'Admin Notifications', array($this, 'nebula_admin_notifications_metabox'), 'nebula_options', 'administration_side');
				add_meta_box('nebula_staff_users_metabox', 'Staff & Notable Users', array($this, 'nebula_staff_users_metabox'), 'nebula_options', 'administration');
				add_meta_box('nebula_dashboard_references_metabox', 'Dashboard References', array($this, 'nebula_dashboard_references_metabox'), 'nebula_options', 'administration');
				add_meta_box('nebula_notes_metabox', 'Notes', array($this, 'nebula_notes_metabox'), 'nebula_options', 'administration_side');

				//Diagnostic
				add_meta_box('nebula_troubleshooting_metabox', 'Troubleshooting', array($this, 'nebula_troubleshooting_metabox'), 'nebula_options', 'diagnostic');
				add_meta_box('nebula_installation_metabox', 'Installation', array($this, 'nebula_installation_metabox'), 'nebula_options', 'diagnostic');
				add_meta_box('nebula_version_metabox', 'Nebula Version', array($this, 'nebula_version_metabox'), 'nebula_options', 'diagnostic_side');
				add_meta_box('nebula_users_metabox', 'Users', array($this, 'nebula_users_metabox'), 'nebula_options', 'diagnostic');
			}
		}

		/*==========================
		 Metadata
		 ===========================*/

		public function site_information_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="site_owner">Site Owner</label>
					<input type="text" name="nebula_options[site_owner]" id="site_owner" class="form-control nebula-validate-text" value="<?php echo $this->option('site_owner'); ?>" placeholder="<?php echo get_bloginfo('name'); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">The name of the company (or person) who this website is for.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used when using <code>nebula()->the_author(0)</code> with author names disabled.</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="contact_email">Contact Email</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><i class="far fa-fw fa-envelope"></i></div>
						</div>
						<input type="email" name="nebula_options[contact_email]" id="contact_email" class="form-control nebula-validate-email" value="<?php echo $nebula_options['contact_email']; ?>" placeholder="<?php echo get_option('admin_email', nebula()->get_user_info('user_email', array('id' => 1))); ?>" autocomplete="email" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The main contact email address <strong>(visible in the frontend and in metadata!)</strong>.</p>
					<p class="nebula-help-text more-help form-text text-muted">If left empty, the admin email address will be used (shown by placeholder).</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="notification_email">Notification Email</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><i class="far fa-fw fa-envelope"></i></div>
						</div>
						<input type="email" name="nebula_options[notification_email]" id="notification_email" class="form-control nebula-validate-email" value="<?php echo $nebula_options['notification_email']; ?>" placeholder="<?php echo get_option('admin_email', nebula()->get_user_info('user_email', array('id' => 1))); ?>" autocomplete="email" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The email address for Nebula notifications.</p>
					<p class="nebula-help-text more-help form-text text-muted">If left empty, the admin email address will be used (shown by placeholder).</p>
					<p class="option-keywords"></p>
				</div>
			<?php

			do_action('nebula_options_site_information_metabox', $nebula_options);
		}

		public function nebula_business_information($nebula_options){
			?>
				<div class="form-group">
					<label for="business_type">Business Type</label>
					<input type="text" name="nebula_options[business_type]" id="business_type" class="form-control nebula-validate-text" value="<?php echo $this->option('business_type'); ?>" placeholder="LocalBusiness" />
					<p class="nebula-help-text short-help form-text text-muted">This schema is used for Structured Data.</p>
					<p class="nebula-help-text more-help form-text text-muted"><a href="https://schema.org/LocalBusiness" target="_blank" rel="noopener">Use this reference under "More specific Types"</a> (click through to get the most specific possible). If you are unsure, you can use Organization, Corporation, EducationalOrganization, GovernmentOrganization, LocalBusiness, MedicalOrganization, NGO, PerformingGroup, or SportsOrganization. Details set using <a href="https://www.google.com/business/" target="_blank" rel="noopener">Google My Business</a> will not be overwritten by Structured Data, so it is recommended to sign up and use Google My Business.</p>
					<p class="option-keywords">schema.org json-ld linked data structured data knowledge graph recommended seo</p>
				</div>

				<div class="form-group">
					<label for="phone_number">Phone Number</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><i class="fas fa-fw fa-phone"></i></div>
						</div>
						<input type="tel" name="nebula_options[phone_number]" id="phone_number" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" value="<?php echo $nebula_options['phone_number']; ?>" placeholder="1-315-478-6700" autocomplete="tel" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="fax_number">Fax Number</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><i class="fas fa-fw fa-fax"></i></div>
						</div>
						<input type="tel" name="nebula_options[fax_number]" id="fax_number" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" value="<?php echo $nebula_options['fax_number']; ?>" placeholder="1-315-426-1392" autocomplete="tel" />
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
									<div class="input-group-prepend">
										<div class="input-group-text">Latitude</div>
									</div>
									<input type="text" name="nebula_options[latitude]" id="latitude" class="form-control nebula-validate-regex" data-valid-regex="^-?\d+(.\d+)?$" value="<?php echo $nebula_options['latitude']; ?>" placeholder="43.0536854" />
								</div>
							</div>
						</div><!--/col-->
						<div class="col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text">Longitude</div>
									</div>
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
						<input type="text" name="nebula_options[street_address]" id="street_address" class="form-control nebula-validate-text mb-2" value="<?php echo $nebula_options['street_address']; ?>" placeholder="760 West Genesee Street" autocomplete="street-address" />
					</div>

					<div class="row">
						<div class="col-sm-5">
							<div class="form-group">
								<label for="locality">City</label>
								<input type="text" name="nebula_options[locality]" id="locality" class="form-control nebula-validate-text mb-2 mr-sm-2 mb-sm-0" value="<?php echo $nebula_options['locality']; ?>" placeholder="Syracuse" autocomplete="address-level2" />
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<label for="region">State</label>
								<input type="text" name="nebula_options[region]" id="region" class="form-control nebula-validate-text mb-2 mr-sm-2 mb-sm-0" value="<?php echo $nebula_options['region']; ?>" placeholder="NY" autocomplete="address-level1" />
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="postal_code">Postal Code</label>
								<input type="text" name="nebula_options[postal_code]" id="postal_code" class="form-control nebula-validate-regex mb-2 mr-sm-2 mb-sm-0" data-valid-regex="\d{5,}" value="<?php echo $nebula_options['postal_code']; ?>" placeholder="13204" autocomplete="postal-code" />
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<label for="country_name">Country</label>
								<input type="text" name="nebula_options[country_name]" id="country_name" class="form-control nebula-validate-text mb-2 mr-sm-2 mb-sm-0" value="<?php echo $nebula_options['country_name']; ?>" placeholder="USA" autocomplete="country" />
							</div>
						</div>
					</div>

					<p class="nebula-help-text short-help form-text text-muted">The address of the location (or headquarters if multiple locations).</p>
					<p class="nebula-help-text more-help form-text text-muted">Use <a href="https://gearside.com/nebula/functions/full_address/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=address+help<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener"><code>nebula()->full_address()</code></a> to get the formatted address in one function.</p>
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
										<div class="input-group-prepend">
											<div class="input-group-text"><span><i class="far fa-fw fa-clock"></i> Open</span></div>
										</div>
										<input type="text" name="nebula_options[business_hours_<?php echo $weekday; ?>_open]" id="business_hours_<?php echo $weekday; ?>_open" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" value="<?php echo $nebula_options['business_hours_' . $weekday . '_open']; ?>" />
									</div>
								</div>
							</div><!--/col-->
							<div class="col">
								<div class="form-group">
									<div class="input-group" dependent-of="business_hours_<?php echo $weekday; ?>_enabled">
										<div class="input-group-prepend">
											<div class="input-group-text"><span><i class="far fa-fw fa-clock"></i> Close</span></div>
										</div>
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
					<p class="nebula-help-text more-help form-text text-muted">These can be date formatted, or day of the month (Ex: "7/4" for Independence Day, or "Last Monday of May" for Memorial Day, or "Fourth Thursday of November" for Thanksgiving). <a href="http://mistupid.com/holidays/" target="_blank" rel="noopener">Here is a good reference for holiday occurrences.</a>.<br /><strong>Note:</strong> This function assumes days off that fall on weekends are observed the Friday before or the Monday after.</p>
					<p class="option-keywords">seo</p>
				</div>
			<?php

			do_action('nebula_options_business_information_metabox', $nebula_options);
		}

		public function nebula_social_networks($nebula_options){
			?>
				<div class="form-group">
					<label for="facebookurl">Facebook</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-facebook"></i> URL</span></div>
						</div>
						<input type="text" name="nebula_options[facebook_url]" id="facebook_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['facebook_url']; ?>" placeholder="http://www.facebook.com/PinckneyHugo" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Facebook page.</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group" dependent-of="facebook_url">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-facebook"></i> Page ID</span></div>
						</div>
						<input type="text" name="nebula_options[facebook_page_id]" id="facebook_page_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_page_id']; ?>" placeholder="000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The ID of your Facebook page.</p>
					<p class="option-keywords">social</p>
				</div>

				<div class="form-group" dependent-of="facebook_url">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-facebook"></i> Admin IDs</span></div>
						</div>
						<input type="text" name="nebula_options[facebook_admin_ids]" id="facebook_admin_ids" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_admin_ids']; ?>" placeholder="0000, 0000, 0000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">IDs of Facebook administrators.</p>
					<p class="option-keywords">social</p>
				</div>

				<div class="form-group">
					<label for="twitter_username">Twitter</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-twitter"></i> Username</span></div>
						</div>
						<input type="text" name="nebula_options[twitter_username]" id="twitter_username" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_username']; ?>" placeholder="@pinckneyhugo" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">Your Twitter username <strong>including the @ symbol</strong>.</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="linkedin_url">LinkedIn</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-linkedin"></i> URL</span></div>
						</div>
						<input type="text" name="nebula_options[linkedin_url]" id="linkedin_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['linkedin_url']; ?>" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your LinkedIn profile.</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="youtube_url">Youtube</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-youtube"></i> URL</span></div>
						</div>
						<input type="text" name="nebula_options[youtube_url]" id="youtube_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['youtube_url']; ?>" placeholder="https://www.youtube.com/user/pinckneyhugo" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Youtube channel.</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="instagram_url">Instagram</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-instagram"></i> URL</span></div>
						</div>
						<input type="text" name="nebula_options[instagram_url]" id="instagram_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['instagram_url']; ?>" placeholder="https://www.instagram.com/pinckneyhugo" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Instagram profile.</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="pinterest_url">Pinterest</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-pinterest"></i> URL</span></div>
						</div>
						<input type="text" name="nebula_options[pinterest_url]" id="pinterest_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['pinterest_url']; ?>" placeholder="https://www.pinterest.com/pinckneyhugo" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Pinterest profile.</p>
					<p class="option-keywords">social seo</p>
				</div>
			<?php

			do_action('nebula_options_social_metadata_metabox', $nebula_options);
		}

		/*==========================
		 Functions
		 ===========================*/

		public function nebula_assets_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="jquery_version">jQuery Version (and Load Location)</label>
					<select name="nebula_options[jquery_version]" id="jquery_version" class="form-control nebula-validate-select">
						<option value="wordpress" <?php selected('wordpress', $nebula_options['jquery_version']); ?>>WordPress &lt;head&gt; (Default)</option>
						<option value="latest" <?php selected('latest', $nebula_options['jquery_version']); ?>>Latest &lt;head&gt;</option>
						<option value="footer" <?php selected('footer', $nebula_options['jquery_version']); ?>>Latest &lt;footer&gt; (Performant)</option>
					</select>
					<p class="nebula-help-text short-help form-text text-muted">Which jQuery version to use and where to load it (head is blocking, footer is more performant). (Default: <?php echo $this->user_friendly_default('jquery_version'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Be careful changing this option as some plugins may rely on older versions of jQuery, however some speed improvements may be realized by using alternate versions and locations.<br /><strong>Note:</strong> some plugins may override this and bring jQuery back to the head.<br /><strong>Remember:</strong> if loading in the footer, embedded script tags cannot use jQuery in template files.</p>
					<p class="option-keywords">internet explorer old support plugins minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<label for="bootstrap_version">Bootstrap Version</label>
					<select name="nebula_options[bootstrap_version]" id="bootstrap_version" class="form-control nebula-validate-select">
						<option value="latest" <?php selected('latest', $nebula_options['bootstrap_version']); ?>>Latest (IE10+)</option>
						<option value="grid" <?php selected('grid', $nebula_options['bootstrap_version']); ?>>Grid Only (IE10+)</option>
						<option value="bootstrap4a5" <?php selected('bootstrap4a5', $nebula_options['bootstrap_version']); ?>>Bootstrap 4 alpha 5 (IE9+)</option>
						<option value="bootstrap3" <?php selected('bootstrap3', $nebula_options['bootstrap_version']); ?>>Bootstrap 3 (IE8+)</option>
					</select>
					<p class="nebula-help-text short-help form-text text-muted">Which Bootstrap version to use. (Default: <?php echo $this->user_friendly_default('bootstrap_version'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Bootstrap 3 will support IE8+. Bootstrap 4 alpha 5 will support IE9+. Bootstrap latest supports IE10+. Grid loads only framework (and reboot) CSS. WordPress admin pages will still load Bootstrap latest regardless of this selection.</p>
					<p class="option-keywords">internet explorer old support optimization moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[allow_bootstrap_js]" id="allow_bootstrap_js" value="1" <?php checked('1', !empty($nebula_options['allow_bootstrap_js'])); ?> /><label for="allow_bootstrap_js">Allow Bootstrap JS</label>
					<p class="nebula-help-text short-help form-text text-muted">Allow Bootstrap JavaScript. (Default: <?php echo $this->user_friendly_default('allow_bootstrap_js'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Disabling this saves a resource request, but JS functionality of Bootstrap will not work (accordions, sliders, toggles, etc).</p>
					<p class="option-keywords">moderate page speed impact optimization optimize</p>
				</div>
			<?php

			do_action('nebula_options_assets_metabox', $nebula_options);
		}

		public function nebula_front_end_metabox($nebula_options){
			?>
				<div class="form-group">
					<?php
						$nebula_data = get_option('nebula_data');
						$last_processed_text = 'Never';
						if ( !empty($nebula_data['scss_last_processed']) ){
							$last_processed_text = '<strong>' . date('l, F j, Y \a\t g:ia', $nebula_data['scss_last_processed']) . '</strong> (' . human_time_diff($nebula_data['scss_last_processed']) . ' ago). Will automatically disable if not re-procesed in <strong>' . human_time_diff($nebula_data['scss_last_processed']+(DAY_IN_SECONDS*30)) . '</strong>.';
						}
					?>

					<input type="checkbox" name="nebula_options[scss]" id="scss" value="1" <?php checked('1', !empty($nebula_options['scss'])); ?> /><label for="scss">Sass</label>
					<p class="nebula-help-text short-help form-text text-muted">Enable the bundled SCSS compiler. (Default: <?php echo $this->user_friendly_default('scss'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">
						Save Nebula Options to manually process all SCSS files. This option will automatically be disabled after 30 days without processing. CSS files will automatically be minified, but source maps are available for debugging.<br /><br />
						Last processed: <?php echo $last_processed_text; ?>
					</p>
					<p class="option-keywords">sass scss sccs scass css moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group" dependent-or="scss">
					<input type="checkbox" name="nebula_options[critical_css]" id="critical_css" value="1" <?php checked('1', !empty($nebula_options['critical_css'])); ?> /><label for="critical_css">Critical CSS</label>
					<p class="nebula-help-text short-help form-text text-muted">Output critical CSS for above-the-fold content in the <code>&lt;head&gt;</code> of the document. (Default: <?php echo $this->user_friendly_default('critical_css'); ?>)</p>
					<p class="dependent-note hidden">This option is dependent on the SCSS compiler.</p>
					<p class="nebula-help-text more-help form-text text-muted">Styles in critical.css will be embedded in the HTML while also imported into style.css. This improves perceived page load time for users without overcomplicating stylesheets.</p>
					<p class="option-keywords">sass scss sccs scass css minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[limit_image_dimensions]" id="limit_image_dimensions" value="1" <?php checked('1', !empty($nebula_options['limit_image_dimensions'])); ?> /><label for="limit_image_dimensions">Limit Image Dimensions</label>
					<p class="nebula-help-text short-help form-text text-muted">Limit image sizes to 1200px on the front-end. (Default: <?php echo $this->user_friendly_default('limit_image_dimensions'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This attempts to prevent content managers from accidentally loading large filesize images on the front-end.</p>
					<p class="option-keywords">major page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<label for="jpeg_quality">JPG Quality</label>
					<input type="text" name="nebula_options[jpeg_quality]" id="jpeg_quality" class="form-control nebula-validate-text" value="<?php echo $this->option('jpeg_quality'); ?>" placeholder="<?php echo $this->user_friendly_default('jpeg_quality'); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Set the JPG compression level on resized images. (Default: <?php echo $this->user_friendly_default('jpeg_quality'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This changes the quality of JPG images when WordPress creates scaled sizes. Smaller number is more optimized, but larger number is better quality.</p>
					<p class="option-keywords">moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[author_bios]" id="author_bios" value="1" <?php checked('1', !empty($nebula_options['author_bios'])); ?> /><label for="author_bios">Author Bios</label>
					<p class="nebula-help-text short-help form-text text-muted">Allow authors to have bios that show their info (and post archives). (Default: <?php echo $this->user_friendly_default('author_bios'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This also enables searching by author, and displaying author names on posts.<br />If disabled, the author page attempts to redirect to an <a href="<?php echo home_url('/'); ?>?s=about" target="_blank">About Us page</a> (use the filter hook <code>nebula_no_author_redirect</code> to better control where it redirects- especially if no search results are found when clicking that link).<br />If disabled, remember to also disable the <a href="<?php echo get_admin_url(); ?>/admin.php?page=wpseo_titles#top#archives" target="_blank">Author archives option in Yoast</a> to hide them from the sitemap.</p>
					<p class="option-keywords">seo</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[comments]" id="comments" value="1" <?php checked('1', !empty($nebula_options['comments'])); ?> /><label for="comments">Comments</label>
					<p class="nebula-help-text short-help form-text text-muted">Ability to force disable comments. (Default: <?php echo $this->user_friendly_default('comments'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles).</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[service_worker]" id="service_worker" value="1" <?php checked('1', !empty($nebula_options['service_worker'])); ?> /><label for="service_worker">Service Worker</label>
					<p class="nebula-help-text short-help form-text text-muted">Utilize a service worker to improve speed, provide content when offline, and other benefits of being a progressive web app. (Default: <?php echo $this->user_friendly_default('service_worker'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This also enables HTTP2 Server Push when available. Enabling this feature requires a service worker JavaScript file. Move the provided sw.js into the root directory (or write your own). Service Worker location: <code><?php echo $this->sw_location(); ?></code></p>
					<p class="option-keywords">moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[device_detection]" id="device_detection" value="1" <?php checked('1', !empty($nebula_options['device_detection'])); ?> /><label for="device_detection">Browser/Device Detection</label>
					<p class="nebula-help-text short-help form-text text-muted">Detect information about the user's device and browser. (Default: <?php echo $this->user_friendly_default('device_detection'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Useful for cross-browser support.</p>
					<p class="option-keywords">remote resource moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[domain_blacklisting]" id="domain_blacklisting" value="1" <?php checked('1', !empty($nebula_options['domain_blacklisting'])); ?> /><label for="domain_blacklisting">Domain Blacklisting</label>
					<p class="nebula-help-text short-help form-text text-muted">Block traffic from known spambots and other illegitimate domains. (Default: <?php echo $this->user_friendly_default('domain_blacklisting'); ?>)</p>
					<p class="option-keywords">security remote resource recommended minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[adblock_detect]" id="adblock_detect" value="1" <?php checked('1', !empty($nebula_options['adblock_detect'])); ?> /><label for="adblock_detect">Ad Block Detection</label>
					<p class="nebula-help-text short-help form-text text-muted">Detect if visitors are using ad blocking software.(Default: <?php echo $this->user_friendly_default('adblock_detect'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">It is encouraged to add a custom dimension for "Blocker" within the "Analytics" tab when using this feature.</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[console_css]" id="console_css" value="1" <?php checked('1', !empty($nebula_options['console_css'])); ?> /><label for="console_css">Console CSS</label>
					<p class="nebula-help-text short-help form-text text-muted">Adds CSS to the browser console. (Default: <?php echo $this->user_friendly_default('console_css'); ?>)</p>
					<p class="option-keywords">discretionary</p>
				</div>
			<?php

			do_action('nebula_options_frontend_metabox', $nebula_options);
		}

		public function nebula_admin_references_metabox($nebula_options){
			?>
				<div class="form-group">
					<input type="checkbox" name="nebula_options[admin_bar]" id="admin_bar" value="1" <?php checked('1', !empty($nebula_options['admin_bar'])); ?> /><label for="admin_bar">Admin Bar</label>
					<p class="nebula-help-text short-help form-text text-muted">Control the Wordpress Admin bar globally on the frontend for all users. (Default: <?php echo $this->user_friendly_default('admin_bar'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Note: When enabled, the Admin Bar can be temporarily toggled using the keyboard shortcut <strong>Alt+A</strong> without needing to disable it permanently for all users.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[unnecessary_metaboxes]" id="unnecessary_metaboxes" value="1" <?php checked('1', !empty($nebula_options['unnecessary_metaboxes'])); ?> /><label for="unnecessary_metaboxes">Remove Unnecessary Metaboxes</label>
					<p class="nebula-help-text short-help form-text text-muted">Remove metaboxes on the Dashboard that are not necessary for most users. (Default: <?php echo $this->user_friendly_default('unnecessary_metaboxes'); ?>)</p>
					<p class="option-keywords">recommended</p>
				</div>

				<div class="form-group" dependent-or="developer_email_domains developer_ips">
					<input type="checkbox" name="nebula_options[dev_info_metabox]" id="dev_info_metabox" value="1" <?php checked('1', !empty($nebula_options['dev_info_metabox'])); ?> /><label for="dev_info_metabox">Developer Info Metabox</label>
					<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
					<p class="nebula-help-text short-help form-text text-muted">Show theme and server information useful to developers. (Default: <?php echo $this->user_friendly_default('dev_info_metabox'); ?>)</p>
					<p class="option-keywords">recommended</p>
				</div>

				<div class="form-group" dependent-or="developer_email_domains developer_ips">
					<input type="checkbox" name="nebula_options[todo_manager_metabox]" id="todo_manager_metabox" value="1" <?php checked('1', !empty($nebula_options['todo_manager_metabox'])); ?> /><label for="todo_manager_metabox">Todo Manager</label>
					<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
					<p class="nebula-help-text short-help form-text text-muted">Aggregate todo comments in code. (Default: <?php echo $this->user_friendly_default('todo_manager_metabox'); ?>)</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[performance_metabox]" id="performance_metabox" value="1" <?php checked('1', !empty($nebula_options['performance_metabox'])); ?> /><label for="performance_metabox">Performance Metabox</label>
					<p class="nebula-help-text short-help form-text text-muted">Test load times from the WordPress Dashboard <?php echo ( $this->is_dev() )? '(Note: This always appears for developers even if disabled!)' : ''; ?>. (Default: <?php echo $this->user_friendly_default('performance_metabox'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Tests are prioritized from WebPageTest.org (using an <a href="themes.php?page=nebula_options&tab=apis&option=webpagetest_api" target="_blank">API key</a>), then Google PageSpeed Insights, and finally a simple iframe timer.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[design_reference_metabox]" id="design_reference_metabox" value="1" <?php checked('1', !empty($nebula_options['design_reference_metabox'])); ?> /><label for="design_reference_metabox">Design Reference Metabox</label>
					<p class="nebula-help-text short-help form-text text-muted">Show the Design Reference dashboard metabox. (Default: <?php echo nebula()->user_friendly_default('design_reference_metabox'); ?>)</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="design_reference_link">Design File(s) URL</label>
					<input type="text" name="nebula_options[design_reference_link]" id="design_reference_link" class="form-control nebula-validate-url" value="<?php echo $nebula_options['design_reference_link']; ?>" placeholder="http://" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the design file(s).</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="additional_design_references">Additional Design Notes</label>
					<textarea name="nebula_options[additional_design_references]" id="additional_design_references" class="form-control nebula-validate-textarea" rows="2"><?php echo $nebula_options['additional_design_references']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted">Add design references (such as links to brand guides) to the admin dashboard</p>
					<p class="option-keywords"></p>
				</div>
			<?php

			do_action('nebula_options_admin_references_metabox', $nebula_options);
		}

		public function nebula_admin_notifications_metabox($nebula_options){
			?>
				<div class="form-group">
					<input type="checkbox" name="nebula_options[admin_notices]" id="admin_notices" value="1" <?php checked('1', !empty($nebula_options['admin_notices'])); ?> /><label for="admin_notices">Nebula Admin Notifications</label>
					<p class="nebula-help-text short-help form-text text-muted">Show Nebula-specific admin notices (Default: <?php echo $this->user_friendly_default('admin_notices'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Note: This does not toggle WordPress core or plugin notices.</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[theme_update_notification]" id="theme_update_notification" value="1" <?php checked('1', !empty($nebula_options['theme_update_notification'])); ?> /><label for="theme_update_notification">Nebula Theme Update Notification</label><!-- @todo: this needs a conditional around it (from the old options) -->
					<p class="nebula-help-text short-help form-text text-muted">Enable easy updates to the Nebula theme. (Default: <?php echo $this->user_friendly_default('theme_update_notification'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>Child theme must be activated to work!</strong></p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[wp_core_updates_notify]" id="wp_core_updates_notify" value="1" <?php checked('1', !empty($nebula_options['wp_core_updates_notify'])); ?> /><label for="wp_core_updates_notify">WordPress Core Update Notification</label>
					<p class="nebula-help-text short-help form-text text-muted">Control whether or not the Wordpress Core update notifications show up on the admin pages. (Default: <?php echo $this->user_friendly_default('wp_core_updates_notify'); ?>)</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[plugin_update_warning]" id="plugin_update_warning" value="1" <?php checked('1', !empty($nebula_options['plugin_update_warning'])); ?> /><label for="plugin_update_warning">Plugin Warning</label>
					<p class="nebula-help-text short-help form-text text-muted">Control whether or not the plugin update warning appears on admin pages. (Default: <?php echo $this->user_friendly_default('plugin_update_warning'); ?>)</p>
					<p class="option-keywords">discretionary</p>
				</div>
			<?php

			do_action('nebula_options_admin_notifications_metabox', $nebula_options);
		}

		/*==========================
		 Analytics
		 ===========================*/

		public function nebula_main_analytics_metabox($nebula_options){
			?>
				<div class="form-group important-option" important-or="gtm_id">
					<label for="ga_tracking_id">Google Analytics Tracking ID</label>
					<input type="text" name="nebula_options[ga_tracking_id]" id="ga_tracking_id" class="form-control nebula-validate-regex" data-valid-regex="^UA-\d+-\d+$" value="<?php echo $nebula_options['ga_tracking_id']; ?>" placeholder="UA-00000000-1" />
					<p class="nebula-help-text short-help form-text text-muted">This will add the tracking number to the appropriate locations.</p>
					<p class="option-keywords">remote resource recommended minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group important-option" important-or="ga_tracking_id">
					<label for="gtm_id">Google Tag Manager ID</label>
					<input type="text" name="nebula_options[gtm_id]" id="gtm_id" class="form-control nebula-validate-regex" data-valid-regex="^GTM-\S+$" value="<?php echo $nebula_options['gtm_id']; ?>" placeholder="GTM-0000000" />
					<p class="nebula-help-text short-help form-text text-muted">This will add the Google Tag Manager scripts to the appropriate locations.</p>
					<p class="option-keywords">remote resource recommended minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group" dependent-or="ga_tracking_id gtm_id">
					<input type="checkbox" name="nebula_options[ga_wpuserid]" id="ga_wpuserid" value="1" <?php checked('1', !empty($nebula_options['ga_wpuserid'])); ?> /><label for="ga_wpuserid">Use WordPress User ID</label>
					<p class="nebula-help-text short-help form-text text-muted">Use the WordPress User ID as the Google Analytics User ID. (Default: <?php echo $this->user_friendly_default('ga_wpuserid'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This allows more accurate user reporting. Note: Users who share accounts (including developers/clients) can cause inaccurate reports! This functionality is most useful when opening sign-ups to the public.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group" dependent-or="ga_tracking_id gtm_id">
					<input type="checkbox" name="nebula_options[ga_displayfeatures]" id="ga_displayfeatures" value="1" <?php checked('1', !empty($nebula_options['ga_displayfeatures'])); ?> /><label for="ga_displayfeatures">Display Features</label>
					<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank" rel="noopener">Google display features</a> in the analytics tag. (Default: <?php echo $this->user_friendly_default('ga_displayfeatures'); ?>)</p>
					<p class="dependent-note hidden">This option is dependent on a Google Analytics Tracking ID.</p>
					<p class="nebula-help-text more-help form-text text-muted">This enables Advertising Features in Google Analytics, such as Remarketing, Demographics and Interest Reporting, and more.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group" dependent-or="ga_tracking_id gtm_id">
					<input type="checkbox" name="nebula_options[ga_linkid]" id="ga_linkid" value="1" <?php checked('1', !empty($nebula_options['ga_linkid'])); ?> /><label for="ga_linkid">Enhanced Link Attribution (Link ID)</label>
					<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-link-attribution" target="_blank" rel="noopener">Enhanced Link Attribution</a> in the Property Settings of the Google Analytics Admin. Be sure to enable it in Google Analytics too! (Default: <?php echo $this->user_friendly_default('ga_linkid'); ?>)</p>
					<p class="dependent-note hidden">This option is dependent on a Google Analytics Tracking ID.</p>
					<p class="nebula-help-text more-help form-text text-muted">This improves the accuracy of your In-Page Analytics report by automatically differentiating between multiple links to the same URL on a single page by using link element IDs. Use the <a href="https://chrome.google.com/webstore/detail/page-analytics-by-google/fnbdnhhicmebfgdgglcdacdapkcihcoh" target="_blank" rel="noopener">Page Analytics by Google</a> Chrome extension to view the page overlay.</p>
					<p class="option-keywords">minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group" dependent-or="ga_tracking_id gtm_id">
					<input type="checkbox" name="nebula_options[ga_anonymize_ip]" id="ga_anonymize_ip" value="1" <?php checked('1', !empty($nebula_options['ga_anonymize_ip'])); ?> /><label for="ga_anonymize_ip">Anonymize All IPs</label>
					<p class="nebula-help-text short-help form-text text-muted">Anonymize the IP address in Google Analytics for all visitors. <a href="https://support.google.com/analytics/answer/2763052" target="_blank">How it works &raquo;</a> (Default: <?php echo $this->user_friendly_default('ga_anonymize_ip'); ?>)</p>
					<p class="dependent-note hidden">This option is dependent on a Google Analytics Tracking ID.</p>
					<p class="nebula-help-text more-help form-text text-muted"></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[observe_dnt]" id="observe_dnt" value="1" <?php checked('1', !empty($nebula_options['observe_dnt'])); ?> /><label for="observe_dnt">Observe "Do Not Track" Requests</label>
					<p class="nebula-help-text short-help form-text text-muted">Comply with user requests of "Do Not Track" (DNT). Analytics data will not be collected for these users. (Default: <?php echo $this->user_friendly_default('observe_dnt'); ?>)</p>
					<p class="option-keywords">gdpr</p>
				</div>

				<div class="form-group">
					<label for="adwords_remarketing_conversion_id">AdWords Remarketing Conversion ID</label>
					<input type="text" name="nebula_options[adwords_remarketing_conversion_id]" id="adwords_remarketing_conversion_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['adwords_remarketing_conversion_id']; ?>" placeholder="000000000" />
					<p class="nebula-help-text short-help form-text text-muted">This conversion ID is used to enable the Google AdWords remarketing tag.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<label for="google_optimize_id">Google Optimize ID</label>
					<input type="text" name="nebula_options[google_optimize_id]" id="google_optimize_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_optimize_id']; ?>" placeholder="GTM-0000000" />
					<p class="nebula-help-text short-help form-text text-muted">The ID used by <a href="https://optimize.google.com/optimize/home/" target="_blank" rel="noopener">Google Optimize</a> to enable tests.</p>
					<p class="nebula-help-text more-help form-text text-muted">Entering the ID here will enable both the Google Analytics require tag and the style tag hiding snippet in the head.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<label for="hostnames">Valid Hostnames</label>
					<input type="text" name="nebula_options[hostnames]" id="hostnames" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hostnames']; ?>" placeholder="<?php echo $this->url_components('domain'); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">These help generate regex patterns for Google Analytics filters.</p>
					<p class="nebula-help-text more-help form-text text-muted">It is also used for the is_site_live() function! Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)! Include this RegEx pattern for a filter/segment <a href="https://gearside.com/nebula/utilities/domain-regex-generator/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=valid+hostnames+help<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener">(Learn how to use this)</a>: <input type="text" value="<?php echo $this->valid_hostname_regex(); ?>" readonly style="width: 50%;" /></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="google_search_console_verification">Google Search Console Verification</label>
					<input type="text" name="nebula_options[google_search_console_verification]" id="google_search_console_verification" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_search_console_verification']; ?>" placeholder="AAAAAA..." />
					<p class="nebula-help-text short-help form-text text-muted">This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank" rel="noopener">Google Search Console</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="facebook_custom_audience_pixel_id">Facebook Custom Audience Pixel ID</label>
					<input type="text" name="nebula_options[facebook_custom_audience_pixel_id]" id="facebook_custom_audience_pixel_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_custom_audience_pixel_id']; ?>" placeholder="000000000000000" />
					<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.facebook.com/docs/facebook-pixel" target="_blank" rel="noopener">Facebook Custom Audience Pixel</a> tracking.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>
			<?php

			do_action('nebula_options_analytics_metabox', $nebula_options);
		}

		public function nebula_custom_dimensions_metabox($nebula_options){
			?>
				<p class="text-muted">These are optional dimensions that can be passed into Google Analytics which allows for 20 custom dimensions (or 200 for Google Analytics Premium). To set these up, define the Custom Dimension in the Google Analytics property, then paste the dimension index string ("dimension1", "dimension12", etc.) into the appropriate input field below. The scope for each dimension is noted in their respective help sections. Dimensions that require additional code are marked with a *.</p>

				<?php $dimension_regex = '^dimension([0-9]{1,3})$'; ?>

				<div class="option-sub-group">
					<h4>Hit Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Client ID</div>
							</div>
							<input type="text" name="nebula_options[cd_gacid]" id="cd_gacid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_gacid']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Store the Google Analytics CID in an accessible dimension for reporting. Scope: User</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Hit ID</div>
							</div>
							<input type="text" name="nebula_options[cd_hitid]" id="cd_hitid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_hitid']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Gives each individual hit an ID. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">This will allow for finding median values! All you have to do is add the Hit ID dimension to your report, sort the metric values in ascending order, and then read the middle value.</p>
						<p class="option-keywords">recommended custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Hit Time</div>
							</div>
							<input type="text" name="nebula_options[cd_hittime]" id="cd_hittime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_hittime']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track the time of each individual hit. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">Useful for reporting on specific users.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Hit Type</div>
							</div>
							<input type="text" name="nebula_options[cd_hittype]" id="cd_hittype" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_hittype']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track the type of each hit (such as pageview, event, exception, etc). Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Hit Interactivity</div>
							</div>
							<input type="text" name="nebula_options[cd_hitinteractivity]" id="cd_hitinteractivity" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_hitinteractivity']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track whether the hit is interactive or non-interactive. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">Useful for determining which events are affecting the bounce rate.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Hit Transport Method</div>
							</div>
							<input type="text" name="nebula_options[cd_hitmethod]" id="cd_hitmethod" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_hitmethod']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track the transport method of the hit (such as JavaScript, Beacon, or Server-Side). Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Save Data</div>
							</div>
							<input type="text" name="nebula_options[cd_savedata]" id="cd_savedata" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_savedata']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track when the user has requested less data usage. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">This listens for the <code>HTTP_SAVE_DATA</code> header on the server-side.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Reduced Motion Preference</div>
							</div>
							<input type="text" name="nebula_options[cd_reducedmotion]" id="cd_reducedmotion" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_reducedmotion']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track the users motion preference. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">This listens for the "Reduce motion" preference from the user's operating system.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Offline</div>
							</div>
							<input type="text" name="nebula_options[cd_offline]" id="cd_offline" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_offline']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Note what data was sent while the user was offline. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted"></p>
						<p class="option-keywords">service worker sw.js workbox custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Device Memory</div>
							</div>
							<input type="text" name="nebula_options[cd_devicememory]" id="cd_devicememory" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_devicememory']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track the available memory of the device as "Lite" or "Full". Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">If alternate components are used on the site for "lite" devices, this dimension will show which version was seen by users.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Battery Mode</div>
							</div>
							<input type="text" name="nebula_options[cd_batterymode]" id="cd_batterymode" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_batterymode']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track whether the device battery is charging ("Adaptor") or discharging ("Battery"). Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">This is useful for discerning if users spend more time on the website if their device is plugged in.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Battery Percent</div>
							</div>
							<input type="text" name="nebula_options[cd_batterypercent]" id="cd_batterypercent" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_batterypercent']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track what percentage the device battery level is at currently (rounded to the nearest integer). Scope: Session</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Visibility State</div>
							</div>
							<input type="text" name="nebula_options[cd_visibilitystate]" id="cd_visibilitystate" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_visibilitystate']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Logs the visibilty state of the window with each hit. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Network Connection</div>
							</div>
							<input type="text" name="nebula_options[cd_network]" id="cd_network" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_network']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Logs the connection state of the network (Online/Offline). Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">Note that browsers report online/offline differently. Connection to a LAN without Internet may be reported as "online".</p>
						<p class="option-keywords"></p>
					</div>


					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Server Referrer</div>
							</div>
							<input type="text" name="nebula_options[cd_referrer]" id="cd_referrer" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_network']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Logs the referrer as detected by the server. This populates regardless of UTM acquisition tags. Scope: Session</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Navigation Type</div>
							</div>
							<input type="text" name="nebula_options[cd_navigationtype]" id="cd_navigationtype" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_navigationtype']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Logs the type of navigation used to load the page (navigation, reload, back/forward). Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Redirect Count</div>
							</div>
							<input type="text" name="nebula_options[cd_redirectcount]" id="cd_redirectcount" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_redirectcount']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Logs the number of redirects made before loading the requested page. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>Post Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Author</div>
							</div>
							<input type="text" name="nebula_options[cd_author]" id="cd_author" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_author']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks the article author's name on single posts. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Categories</div>
							</div>
							<input type="text" name="nebula_options[cd_categories]" id="cd_categories" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_categories']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks the article author's name on single posts. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Tags</div>
							</div>
							<input type="text" name="nebula_options[cd_tags]" id="cd_tags" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_tags']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends a string of all the post's tags to the pageview hit. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Word Count</div>
							</div>
							<input type="text" name="nebula_options[cd_wordcount]" id="cd_wordcount" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_wordcount']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends word count range for single posts. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Publish Date</div>
							</div>
							<input type="text" name="nebula_options[cd_publishdate]" id="cd_publishdate" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_publishdate']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends the date the post was published in the format <code>YYYY-MM-DD</code>. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>Business Data</h4>

					<div class="form-group" dependent-or="business_hours_sunday_enabled business_hours_monday_enabled business_hours_tuesday_enabled business_hours_wednesday_enabled business_hours_thursday_enabled business_hours_friday_enabled business_hours_saturday_enabled">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Business Hours</div>
							</div>
							<input type="text" name="nebula_options[cd_businesshours]" id="cd_businesshours" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_businesshours']; ?>" />
						</div>
						<p class="dependent-note hidden">This option is dependent on Business Hours (Metadata tab).</p>
						<p class="nebula-help-text short-help form-text text-muted">Passes "During Business Hours", or "Non-Business Hours". Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Relative Time</div>
							</div>
							<input type="text" name="nebula_options[cd_relativetime]" id="cd_relativetime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_relativetime']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends the relative time (Ex: "Late Morning", "Early Evening", etc.) based on the business timezone (via WordPress settings). Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group" dependent-of="weather">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Weather</div>
							</div>
							<input type="text" name="nebula_options[cd_weather]" id="cd_weather" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_weather']; ?>" />
						</div>
						<p class="dependent-note hidden">This option is dependent on Weather Detection (Functions tab) being enabled.</p>
						<p class="nebula-help-text short-help form-text text-muted">Sends the current weather conditions (at the business location) as a dimension. Scope: Hit</p>
						<p class="option-keywords">location custom dimension</p>
					</div>

					<div class="form-group" dependent-of="weather">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Temperature</div>
							</div>
							<input type="text" name="nebula_options[cd_temperature]" id="cd_temperature" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_temperature']; ?>" />
						</div>
						<p class="dependent-note hidden">This option is dependent on Weather Detection (Functions tab) being enabled.</p>
						<p class="nebula-help-text short-help form-text text-muted">Sends temperature ranges (at the business location) in 5&deg;F intervals. Scope: Hit</p>
						<p class="option-keywords">location custom dimension</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>User Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Role</div>
							</div>
							<input type="text" name="nebula_options[cd_role]" id="cd_role" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_role']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends the current user's role (as well as staff affiliation if available) for associated users. Scope: User</p>
						<p class="nebula-help-text more-help form-text text-muted">Session ID does contain this information, but this is explicitly more human readable (and scoped to the user).</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Session ID</div>
							</div>
							<input type="text" name="nebula_options[cd_sessionid]" id="cd_sessionid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_sessionid']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">ID system so that you can group hits into specific user sessions. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">This ID is not personally identifiable and therefore fits within the <a href="https://support.google.com/analytics/answer/2795983" target="_blank" rel="noopener">Google Analytics ToS</a> for PII. <a href="https://gearside.com/nebula/functions/nebula_session_id/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=session+id+help<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener">Session ID Documentation &raquo;</a></p>
						<p class="option-keywords">recommended custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">User ID</div>
							</div>
							<input type="text" name="nebula_options[cd_userid]" id="cd_userid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_userid']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">If allowing visitors to sign up to create WordPress accounts, this will send user IDs to Google Analytics. Scope: User</p>
						<p class="nebula-help-text more-help form-text text-muted">User IDs are also passed in the Session ID, but this scope is tied more specifically to the user (it can often capture data even when they are not currently logged in).</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Facebook ID</div>
							</div>
							<input type="text" name="nebula_options[cd_fbid]" id="cd_fbid" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_fbid']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Send Facebook ID to Google Analytics when using Facebook Connect API. Scope: User</p>
						<p class="nebula-help-text more-help form-text text-muted">Add the ID to this URL to view it: <code>https://www.facebook.com/app_scoped_user_id/</code></p>
						<p class="option-keywords">social custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Local Timestamp</div>
							</div>
							<input type="text" name="nebula_options[cd_timestamp]" id="cd_timestamp" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_timestamp']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Adds a timestamp (in the user's local time) with timezone offset. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">Ex: "1449332547 (2015/12/05 11:22:26.886 UTC-05:00)". Can be compared to the server time stored in the Session ID.</p>
						<p class="option-keywords">location recommended custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Window Type</div>
							</div>
							<input type="text" name="nebula_options[cd_windowtype]" id="cd_windowtype" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_windowtype']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Stores the type of window the site is being accessed from (Ex: Iframe or Standalone App). Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">This only records alternate window types (non-standard browser windows).</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Geolocation</div>
							</div>
							<input type="text" name="nebula_options[cd_geolocation]" id="cd_geolocation" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_geolocation']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Allows latitude and longitude coordinates to be sent after being detected. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work! </p>
						<p class="option-keywords">location custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Geolocation Accuracy</div>
							</div>
							<input type="text" name="nebula_options[cd_geoaccuracy]" id="cd_geoaccuracy" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_geoaccuracy']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Allows geolocation accuracy to be sent after being detected. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work!</p>
						<p class="option-keywords">location custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Geolocation Name</div>
							</div>
							<input type="text" name="nebula_options[cd_geoname]" id="cd_geoname" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_geoname']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Allows named location information to be sent after being detected using map polygons. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work!</p>
						<p class="option-keywords">location custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">WPML Language</div>
							</div>
							<input type="text" name="nebula_options[cd_wpmllang]" id="cd_wpmllang" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_wpmllang']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Store the language displayed to the user via the WPML plugin. Scope: User</p>
						<p class="nebula-help-text more-help form-text text-muted">This requires the WPML plugin!</p>
						<p class="option-keywords">wpml multi-language translation localization internationalization custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Blocker Detection</div>
							</div>
							<input type="text" name="nebula_options[cd_blocker]" id="cd_blocker" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_blocker']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Detects if the user is blocking resources such as ads or Google Analytics. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">Ad Blocker detection must be enabled for that resource detection to work! This can be used even if not intending to serve ads on this site. It is important that this dimension is not set to the "hit" scope.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Query String</div>
							</div>
							<input type="text" name="nebula_options[cd_querystring]" id="cd_querystring" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_querystring']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Moves the query string from the "page" dimension for cleaner URLs. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">This cleans up page reports by consolidating page paths. Query strings can be shown by using a custom dimension.</p>
						<p class="option-keywords">autotrack recommended custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Media Query: Breakpoint</div>
							</div>
							<input type="text" name="nebula_options[cd_mqbreakpoint]" id="cd_mqbreakpoint" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_mqbreakpoint']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Detect which media query breakpoint is associated with this hit. Scope: Hit</p>
						<p class="option-keywords">autotrack custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Media Query: Resolution</div>
							</div>
							<input type="text" name="nebula_options[cd_mqresolution]" id="cd_mqresolution" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_mqresolution']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Detect the resolution factor associated with this hit. Scope: Hit</p>
						<p class="option-keywords">autotrack custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Media Query: Orientation</div>
							</div>
							<input type="text" name="nebula_options[cd_mqorientation]" id="cd_mqorientation" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_mqorientation']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Detect the device orientation associated with this hit. Scope: Hit</p>
						<p class="option-keywords">autotrack custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Security Note</div>
							</div>
							<input type="text" name="nebula_options[cd_securitynote]" id="cd_securitynote" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_securitynote']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Adds a note to the user for potential security issues and on possible bots. Scope: User</p>
						<p class="option-keywords">recommended custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Notable POI</div>
							</div>
							<input type="text" name="nebula_options[cd_notablepoi]" id="cd_notablepoi" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_notablepoi']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Stores named locations when detected. Scope: User</p>
						<p class="nebula-help-text more-help form-text text-muted">Stores named IP addresses (from the Administration tab). Also passes data using the ?poi query string (useful for email marketing using personalization within links). Also sends value of input fields with class "nebula-poi" on form submits (when applicable).</p>
						<p class="option-keywords">recommended custom dimension</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>Conversion Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Event Intent</div>
							</div>
							<input type="text" name="nebula_options[cd_eventintent]" id="cd_eventintent" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_eventintent']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Log whether the event was true, or just a possible intention. Scope: Hit</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Contact Method</div>
							</div>
							<input type="text" name="nebula_options[cd_contactmethod]" id="cd_contactmethod" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_contactmethod']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">If the user triggers a contact event, the method of contact is stored here. Scope: Session</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Form Timing</div>
							</div>
							<input type="text" name="nebula_options[cd_formtiming]" id="cd_formtiming" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_formtiming']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends form timings along with the each submission. Scope: Hit</p>
						<p class="nebula-help-text more-help form-text text-muted">Timings are automatically sent to Google Analytics in Nebula, but are sampled in the User Timings report. Data will be in milliseconds.</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Form Flow</div>
							</div>
							<input type="text" name="nebula_options[cd_formflow]" id="cd_formflow" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_formflow']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Track the field path the user takes through forms. Scope: Session</p>
						<p class="nebula-help-text more-help form-text text-muted">Because this data is scoped to the session, it will only track the last form of the session. This data can be useful in detecting form abandonment (as submit actions are stored in this dimension too).</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Video Watcher</div>
							</div>
							<input type="text" name="nebula_options[cd_videowatcher]" id="cd_videowatcher" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_videowatcher']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sets a dimension when videos are started and finished. Scope: Session</p>
						<p class="option-keywords">custom dimension</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Ecommerce Cart</div>
							</div>
							<input type="text" name="nebula_options[cd_woocart]" id="cd_woocart" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_woocart']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">If the user has any product(s) in their cart. Scope: Hit</p>
						<p class="option-keywords">ecommerce woocommerce</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Ecommerce Customer</div>
							</div>
							<input type="text" name="nebula_options[cd_woocustomer]" id="cd_woocustomer" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" value="<?php echo $nebula_options['cd_woocustomer']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sets a dimension when a user completes the checkout process in WooCommerce. Scope: User</p>
						<p class="nebula-help-text more-help form-text text-muted">Appears in Google Analytics as "Order Received".</p>
						<p class="option-keywords">ecommerce woocommerce custom dimension</p>
					</div>
				</div><!-- /sub-group -->
			<?php

			do_action('nebula_options_custom_dimensions_metabox', $nebula_options);
		}

		public function nebula_custom_metrics_metabox($nebula_options){
			?>
				<p class="text-muted">These are optional metrics that can be passed into Google Analytics which allows for 20 custom metrics (or 200 for Google Analytics Premium). To set these up, define the Custom Metric in the Google Analytics property, then paste the metric index string ("metric1", "metric12", etc.) into the appropriate input field below. The scope and format for each metric is noted in their respective help sections. Metrics that require additional code are marked with a *. These are useful for manual interpretation of data, or to be included in <a href="https://gearside.com/nebula/get-started/recommendations/google-analytics-calculated-metrics/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=custom+metrics<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank">Calculated Metrics formulas</a>.</p>

				<?php $metric_regex = '^metric([0-9]{1,3})$'; ?>

				<div class="option-sub-group">
					<h4>Timing Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Server Response</div>
							</div>
							<input type="text" name="nebula_options[cm_serverresponsetime]" id="cm_serverresponsetime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_serverresponsetime']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Navigation start until server response finishes (includes PHP rendering time). Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">Use these timing metrics to segment reports based on load times.</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">DOM Ready</div>
							</div>
							<input type="text" name="nebula_options[cm_domreadytime]" id="cm_domreadytime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_domreadytime']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Navigation start until DOM ready. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">Use these timing metrics to segment reports based on load times.</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Window Loaded</div>
							</div>
							<input type="text" name="nebula_options[cm_windowloadedtime]" id="cm_windowloadedtime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_windowloadedtime']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Navigation start until window loaded. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">Use these timing metrics to segment reports based on load times.</p>
						<p class="option-keywords">custom metric</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>Conversion Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Notable Downloads</div>
							</div>
							<input type="text" name="nebula_options[cm_notabledownloads]" id="cm_notabledownloads" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_notabledownloads']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a user downloads a notable file. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">To use, add the class "notable" to either the or its parent.</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Form Impressions</div>
							</div>
							<input type="text" name="nebula_options[cm_formimpressions]" id="cm_formimpressions" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formimpressions']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a form is in view as the user scrolls. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Form Starts</div>
							</div>
							<input type="text" name="nebula_options[cm_formstarts]" id="cm_formstarts" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formstarts']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a user begins entering a form. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Form Submissions</div>
							</div>
							<input type="text" name="nebula_options[cm_formsubmissions]" id="cm_formsubmissions" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_formsubmissions']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a user submits a form. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Max Scroll Percent</div>
							</div>
							<input type="text" name="nebula_options[cm_maxscroll]" id="cm_maxscroll" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_maxscroll']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Calculates the maximum scroll percentage the user reached per page. Scope: Hit, Format: Integer</p>
						<p class="nebula-help-text more-help form-text text-muted">Useful as a calculated metric in Google Analytics called "Avg. Max Scroll Percentage" of <code>{{Max Scroll Percentage}}/(100*{{Unique Pageviews}})</code>. Create a custom report with the metrics "Avg. Max Scroll Percentage" and "Unique Pageviews" and dimensions "Page", "Source / Medium", etc.</p>
						<p class="option-keywords">custom metric</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>Video Data</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Video Starts</div>
							</div>
							<input type="text" name="nebula_options[cm_videostarts]" id="cm_videostarts" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_videostarts']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a user begins playing a video. Scope: Hit, Format: Integer</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Video Play Time</div>
							</div>
							<input type="text" name="nebula_options[cm_videoplaytime]" id="cm_videoplaytime" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_videoplaytime']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks playing duration when a user pauses or completes a video. Scope: Hit, Format: Time</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Video Completions</div>
							</div>
							<input type="text" name="nebula_options[cm_videocompletions]" id="cm_videocompletions" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_videocompletions']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a user completes playing a video. Scope: Hit, Format: Integer</p>
						<p class="option-keywords">custom metric</p>
					</div>
				</div><!-- /sub-group -->

				<div class="option-sub-group">
					<h4>Miscellaneous</h4>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Battery Level</div>
							</div>
							<input type="text" name="nebula_options[cm_batterylevel]" id="cm_batterylevel" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_batterylevel']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Record the individual battery level for each hit. Scope: Hit, Format: Integer</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Word Count</div>
							</div>
							<input type="text" name="nebula_options[cm_wordcount]" id="cm_wordcount" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_wordcount']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Sends word count for single posts. Scope: Hit, Format: Integer</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Autocomplete Searches</div>
							</div>
							<input type="text" name="nebula_options[cm_autocompletesearches]" id="cm_autocompletesearches" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_autocompletesearches']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a set of autocomplete search results is returned to the user (count is the search, not the result quantity). Scope: Hit, Format: Integer</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Autocomplete Search Clicks</div>
							</div>
							<input type="text" name="nebula_options[cm_autocompletesearchclicks]" id="cm_autocompletesearchclicks" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_autocompletesearchclicks']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">Tracks when a user clicks an autocomplete search result. Scope: Hit, Format: Integer</p>
						<p class="option-keywords">custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Page Visible Time</div>
							</div>
							<input type="text" name="nebula_options[cm_pagevisible]" id="cm_pagevisible" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_pagevisible']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">The amount of time (in seconds) the page was in the visible state (tab/window visible) Scope: Hit, Format: Time</p>
						<p class="nebula-help-text more-help form-text text-muted">Useful with calculated metrics in Google Analytics of "Avg. Page Visible Time / Page" <code>{{Page Visible Time}} / {{Unique Pageviews}}</code> or "Avg. Page Visible Time / Session" <code>{{Page Visible Time}} / {{Sessions}}</code>. Create a custom report with the metrics "Avg. Page Visible Time / Page" and "Unique Pageviews" and dimension "Page", and another with the metrics "Avg. Page Visible Time / Session" and "Unique Pageviews" and dimension "Source / Medium"</p>
						<p class="option-keywords">autotrack custom metric</p>
					</div>

					<div class="form-group">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Page Hidden Time</div>
							</div>
							<input type="text" name="nebula_options[cm_pagehidden]" id="cm_pagehidden" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" value="<?php echo $nebula_options['cm_pagehidden']; ?>" />
						</div>
						<p class="nebula-help-text short-help form-text text-muted">The amount of time (in seconds) the page was in the hidden state (tab/window not visible) Scope: Hit, Format: Time</p>
						<p class="option-keywords">autotrack custom metric</p>
					</div>
				</div><!-- /sub-group -->
			<?php

			do_action('nebula_options_custom_metrics_metabox', $nebula_options);
		}

		/*==========================
		 APIs
		 ===========================*/

		public function nebula_main_apis_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="remote_font_url">Remote Font</label>
					<input type="text" name="nebula_options[remote_font_url]" id="remote_font_url" class="form-control nebula-validate-text" value="<?php echo $nebula_options['remote_font_url']; ?>" placeholder="http://fonts.googleapis.com/css?family=Open+Sans:400,800" />
					<p class="nebula-help-text short-help form-text text-muted">Paste the entire URL of your remote font(s). Popular font services include <a href="https://www.google.com/fonts" target="_blank" rel="noopener">Google Fonts</a> and <a href="https://fonts.adobe.com/fonts" target="_blank" rel="noopener">Adobe Fonts</a>. Include all URL parameters here!</p>
					<p class="nebula-help-text more-help form-text text-muted">The default font uses the native system font of the user's device. Be sure to include all desired parameters such as <code>&display=swap</code> or <code>:ital,wght@0,200..900;1,700</code> (for variable Google fonts) in this URL.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group mb-2">
					<label for="google_browser_api_key">Google Public API</label>

					<div class="input-group">
						<div class="input-group-prepend">
									<div class="input-group-text">HTTP Restricted</div>
								</div>
						<input type="text" name="nebula_options[google_browser_api_key]" id="google_browser_api_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_browser_api_key']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
									<div class="input-group-text">IP Restricted</div>
								</div>
						<input type="text" name="nebula_options[google_server_api_key]" id="google_server_api_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_server_api_key']; ?>" />
					</div>

					<p class="nebula-help-text short-help form-text text-muted">API keys from the <a href="https://console.developers.google.com/project" target="_blank" rel="noopener">Google Developers Console</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">In the Developers Console make a new project (if you don't have one yet). Under "Credentials" create a new key. Your current server IP address is <code><?php echo $_SERVER['SERVER_ADDR']; ?></code> (for IP restricting). Do not use an IP restricted key in JavaScript or any client-side code! Use HTTP referrer restrictions for browser keys.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="cse_id">Google Custom Search Engine</label>
					<div class="input-group">
						<div class="input-group-prepend">
									<div class="input-group-text">Engine ID</div>
								</div>
						<input type="text" name="nebula_options[cse_id]" id="cse_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['cse_id']; ?>" placeholder="000000000000000000000:aaaaaaaa_aa" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">For <a href="https://gearside.com/nebula/functions/pagesuggestion/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=gcse+help<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener">page suggestions</a> on 404 and No Search Results pages.</p>
					<p class="nebula-help-text more-help form-text text-muted"><a href="https://www.google.com/cse/manage/all">Register here</a>, then select "Add", input your website's URL in "Sites to Search". Then click the one you just made and click the "Search Engine ID" button.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group hidden">
					<label for="gcm_sender_id">Google Cloud Messaging Sender ID</label>
					<input type="text" name="nebula_options[gcm_sender_id]" id="gcm_sender_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['gcm_sender_id']; ?>" placeholder="000000000000" />
					<p class="nebula-help-text short-help form-text text-muted">The Google Cloud Messaging (GCM) Sender ID from the <a href="https://console.developers.google.com/project" target="_blank" rel="noopener">Developers Console</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is the "Project number" within the project box on the Dashboard. Do not include parenthesis or the "#" symbol. This is used for push notifications. <strong>*Note: This feature is still in development and not currently active!</strong></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="gcm_sender_id">WebPageTest API Key</label>
					<input type="text" name="nebula_options[webpagetest_api]" id="webpagetest_api" class="form-control nebula-validate-text" value="<?php echo $nebula_options['webpagetest_api']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">The API key for programmatic testing from <a href="http://www.webpagetest.org/getkey.php" target="_blank" rel="noopener">WebPageTest.org</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">If this key is used, the Nebula Developer dashboard will obtain timing information from WebPageTest.org rather than a more anecdotal iframe JavaScript timer. Note: The WPT API key registration does not allow + signs in email addresses.</strong></p>
					<p class="option-keywords">wpt</p>
				</div>

				<div class="form-group mb-2">
					<label for="hubspot_api">Hubspot</label>

					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">API Key</div>
						</div>
						<input type="text" name="nebula_options[hubspot_api]" id="hubspot_api" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hubspot_api']; ?>" />
					</div>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">Portal ID</div>
						</div>
						<input type="text" name="nebula_options[hubspot_portal]" id="hubspot_portal" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hubspot_portal']; ?>" />
					</div>

					<p class="nebula-help-text short-help form-text text-muted">Enter your Hubspot API key and Hubspot Portal ID here.</p>
					<p class="nebula-help-text more-help form-text text-muted">It can be obtained from your <a href="https://app.hubspot.com/hapikeys">API Keys page under Integrations in your account</a>. Your Hubspot Portal ID (or Hub ID) is located in the upper right of your <a href="https://app.hubspot.com/" target="_blank">account screen</a> (or within the URL itself). The Portal ID is needed to send data to your Hubspot CRM and the API key will allow for Nebula custom contact properties to be automatically created. Note: You'll still be required to <a href="https://app.hubspot.com/property-settings/<?php echo nebula()->get_option('hubspot_portal'); ?>/contact" target="_blank">create any of your own custom properties</a> (non-Nebula) manually. It is recommended to create your own property group for these separate from the Nebula group.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize crm</p>
				</div>
			<?php

			do_action('nebula_options_apis_metabox', $nebula_options);
		}

		public function nebula_social_apis_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="disqus_shortname">Disqus Shortname</label>
					<input type="text" name="nebula_options[disqus_shortname]" id="disqus_shortname" class="form-control nebula-validate-text" value="<?php echo $nebula_options['disqus_shortname']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Enter your Disqus shortname here.</p>
					<p class="nebula-help-text more-help form-text text-muted"><a href="https://disqus.com/admin/create/" target="_blank" rel="noopener">Sign-up for an account here</a>. In your Disqus account settings (where you will find your shortname), please uncheck the "Discovery" box.</p>
					<p class="option-keywords">social remote resource moderate page speed impact optimization optimize comments</p>
				</div>

				<div class="form-group">
					<label for="facebook_app_id">Facebook</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-facebook"></i> App ID</span></div>
						</div>
						<input type="text" name="nebula_options[facebook_app_id]" id="facebook_app_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_app_id']; ?>" placeholder="000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The App ID of the associated Facebook page/app.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank" rel="noopener">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-facebook"></i> App Secret</span></div>
						</div>
						<input type="text" name="nebula_options[facebook_app_secret]" id="facebook_app_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_app_secret']; ?>" placeholder="00000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The App Secret of the associated Facebook page/app.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank" rel="noopener">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-facebook"></i> Access Token</span></div>
						</div>
						<input type="text" name="nebula_options[facebook_access_token]" id="facebook_access_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_access_token']; ?>" placeholder="000000000000000|000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The Access Token of the associated Facebook page/app.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank" rel="noopener">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<label for="twitter_consumer_key">Twitter</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-twitter"></i> Consumer Key</span></div>
						</div>
						<input type="text" name="nebula_options[twitter_consumer_key]" id="twitter_consumer_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_consumer_key']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The Consumer Key key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-twitter"></i> Consumer Secret</span></div>
						</div>
						<input type="text" name="nebula_options[twitter_consumer_secret]" id="twitter_consumer_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_consumer_secret']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The Consumer Secret key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-twitter"></i> Bearer Token</span></div>
						</div>
						<input type="text" name="nebula_options[twitter_bearer_token]" id="twitter_bearer_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_bearer_token']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The bearer token is for creating custom Twitter feeds: <a href="https://gearside.com/nebula/utilities/twitter-bearer-token-generator/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=twitter+help<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener">Generate a bearer token here</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<label for="instagram_user_id">Instagram</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-instagram"></i> User ID</span></div>
						</div>
						<input type="text" name="nebula_options[instagram_user_id]" id="instagram_user_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_user_id']; ?>" placeholder="00000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
					<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-instagram"></i> Access Token</span></div>
						</div>
						<input type="text" name="nebula_options[instagram_access_token]" id="instagram_access_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_access_token']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
					<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-instagram"></i> Client ID</span></div>
						</div>
						<input type="text" name="nebula_options[instagram_client_id]" id="instagram_client_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_client_id']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><span><i class="fab fa-fw fa-instagram"></i> Client Secret</span></div>
						</div>
						<input type="text" name="nebula_options[instagram_client_secret]" id="instagram_client_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_client_secret']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
					<p class="option-keywords">social remote resource</p>
				</div>
			<?php

			do_action('nebula_options_social_apis_metabox', $nebula_options);
		}

		public function nebula_arbitrary_code_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="arbitrary_code_head">Head Code</label>
					<textarea name="nebula_options[arbitrary_code_head]" id="arbitrary_code_head" class="form-control textarea" rows="3"><?php echo $nebula_options['arbitrary_code_head']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted">Execute this code in the <code>&lt;head&gt;</code> of each page.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="arbitrary_code_body">Body Code</label>
					<textarea name="nebula_options[arbitrary_code_body]" id="arbitrary_code_body" class="form-control textarea" rows="3"><?php echo $nebula_options['arbitrary_code_body']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted">Execute this code just after the opening <code>&lt;body&gt;</code> tag of each page.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="arbitrary_code_footer">Footer Code</label>
					<textarea name="nebula_options[arbitrary_code_footer]" id="arbitrary_code_footer" class="form-control textarea" rows="3"><?php echo $nebula_options['arbitrary_code_footer']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted">Execute this code at the end of of each page just before the closing <code>&lt;/body&gt;</code> tag.</p>
					<p class="option-keywords"></p>
				</div>
			<?php
		}

		/*==========================
		 Administrative
		 ===========================*/

		public function nebula_staff_users_metabox($nebula_options){
			?>
				<?php
					$current_user = wp_get_current_user();
					list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);
				?>

				<div class="form-group">
					<label for="dev_ip">Developer IPs</label>
					<input type="text" name="nebula_options[dev_ip]" id="dev_ip" class="form-control nebula-validate-text" value="<?php echo $nebula_options['dev_ip']; ?>" placeholder="<?php echo $this->get_ip_address(); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the developer to enable specific console logs and other dev info.<br/>Your current IP address is <code><?php echo $this->get_ip_address(); ?></code></p>
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
					<input type="text" name="nebula_options[client_ip]" id="client_ip" class="form-control nebula-validate-text" value="<?php echo $nebula_options['client_ip']; ?>" placeholder="<?php echo $this->get_ip_address(); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the client to enable certain features.<br/>Your current IP address is <code><?php echo $this->get_ip_address(); ?></code></p>
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
					<p class="nebula-help-text more-help form-text text-muted">Name IPs by location to avoid <a href="https://support.google.com/analytics/answer/2795983" target="_blank" rel="noopener">Personally Identifiable Information (PII)</a> issues (Do not use peoples' names). Be sure to set up a Custom Dimension in Google Analytics and add the dimension index in the Analytics tab!<br />Tip: IP data can be sent with <a href="https://gearside.com/nebula/examples/contact-form-7/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=notable+ips+help<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener">Nebula contact forms</a>!</p>
					<p class="option-keywords">recommended</p>
				</div>
			<?php

			do_action('nebula_options_staff_users_metabox', $nebula_options);
		}

		public function nebula_dashboard_references_metabox($nebula_options){
			$serverProtocol = 'http://';
			if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443 ){
				$serverProtocol = 'https://';
			}

			$host_url = explode(".", gethostname());
			$host_domain = '';
			if ( !empty($host_url) ){
				$host_domain = $host_url[1] . '.' . $host_url[2];
			}
			?>
				<div class="form-group">
					<label for="cpanel_url">Server Control Panel URL</label>
					<input type="text" name="nebula_options[cpanel_url]" id="cpanel_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['cpanel_url']; ?>" placeholder="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the control panel of the hosting account.</p>
					<p class="nebula-help-text more-help form-text text-muted">cPanel on this domain would be: <a href="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" target="_blank" rel="noopener"><?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082</a></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="hosting_url">Hosting URL</label>
					<input type="text" name="nebula_options[hosting_url]" id="hosting_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['hosting_url']; ?>" placeholder="http://<?php echo $host_domain; ?>/" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the server host for easy access to support and other information.</p>
					<?php if ( !empty($host_domain) ): ?>
						<p class="nebula-help-text more-help form-text text-muted">Server detected as <a href="http://<?php echo $host_domain; ?>" target="_blank" rel="noopener">http://<?php echo $host_domain; ?></a></p>
					<?php endif; ?>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="registrar_url">Domain Registrar URL</label>
					<input type="text" name="nebula_options[registrar_url]" id="registrar_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['registrar_url']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the domain registrar used for access to pointers, forwarding, and other information.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="registrar_url">Github Repository URL</label>
					<input type="text" name="nebula_options[github_url]" id="github_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['github_url']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the Github repo for this website.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[google_adsense_url]" id="google_adsense_url" value="1" <?php checked('1', !empty($nebula_options['google_adsense_url'])); ?> /><label for="google_adsense_url">Google AdSense URL</label>
					<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://www.google.com/adsense/" target="_blank" rel="noopener">Google AdSense</a> account. (Default: <?php echo $this->user_friendly_default('google_adsense_url'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[amazon_associates_url]" id="amazon_associates_url" value="1" <?php checked('1', !empty($nebula_options['amazon_associates_url'])); ?> /><label for="amazon_associates_url">Amazon Associates URL</label>
					<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://affiliate-program.amazon.com/home" target="_blank" rel="noopener">Amazon Associates</a> account. (Default: <?php echo $this->user_friendly_default('amazon_associates_url'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="checkbox" name="nebula_options[mention_url]" id="mention_url" value="1" <?php checked('1', !empty($nebula_options['mention_url'])); ?> /><label for="mention_url">Mention URL</label>
					<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://mention.com/" target="_blank" rel="noopener">Mention</a> account. (Default: <?php echo $this->user_friendly_default('mention_url'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
					<p class="option-keywords">discretionary</p>
				</div>
			<?php

			do_action('nebula_options_dashboard_references_metabox', $nebula_options);
		}

		public function nebula_notes_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="notes">Notes</label>
					<textarea name="nebula_options[notes]" id="notes" class="form-control textarea" rows="4"><?php echo $nebula_options['notes']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted">This area can be used to keep notes. It is not used anywhere on the front-end.</p>
					<p class="option-keywords"></p>
				</div>
			<?php

			do_action('nebula_options_notes_metabox', $nebula_options);
		}

		/*==========================
		 Diagnostic
		 ===========================*/

		public function nebula_troubleshooting_metabox($nebula_data){
			$nebula_options = get_option('nebula_options');

			?>
				<p>This is a list of possible Nebula configurations that may allow or prevent certain functionality from happening. For more references, be sure to check Nebula warnings in the Admin Dashboard, try running a Nebula Audit (from the admin bar), or check the At-A-Glance metabox on the Dashboard. The other metaboxes in this Diagnostic tab may be helpful as well.</p>
				<ul>
					<li>
						<?php if ( is_child_theme() ): ?>
							<strong>Nebula Child</strong> theme is active<?php echo ( $this->allow_theme_update() )? '. Automated updates <strong class="nebula-enabled">are</strong> allowed.' : ', but automated updates are <strong class="nebula-disabled">not</strong> allowed.'; ?>
						<?php else: ?>
							Child theme is <strong class="nebula-disabled">not</strong> being used. Automated updates will <strong class="nebula-disabled">not</strong> be available.
						<?php endif; ?>
					</li>
					<li>The local Nebula version is <strong><?php echo nebula()->version('full'); ?></strong> and the remote (Github) version is <strong><?php echo $nebula_data['next_version']; ?></strong>.</li>

					<?php if ( !empty($nebula_data['last_automated_update_date']) ): ?>
						<li>Nebula was last updated via the WordPress updater on <strong><?php echo date('F j, Y \a\t g:ia', $nebula_data['last_automated_update_date']); ?></strong> by <strong><?php echo $nebula_data['last_automated_update_user']; ?></strong>.</li>
					<?php endif; ?>

					<li><strong>WordPress Core update notifications</strong> are <?php echo ( empty($nebula_options['wp_core_updates_notify']) )? '<strong class="nebula-disabled">hidden' : '<strong class="nebula-enabled">allowed'; ?></strong> by Nebula.</li>
					<li>Nebula <strong>Sass processing</strong> is <?php echo ( empty($nebula_options['scss']) )? '<strong class="nebula-disabled">disabled' : '<strong class="nebula-enabled">enabled'; ?></strong>.</li>
					<li>The <strong>WordPress Admin Bar</strong> is <?php echo ( empty($nebula_options['admin_bar']) )? '<strong class="nebula-disabled">hidden' : '<strong class="nebula-enabled">allowed'; ?></strong> by Nebula.</li>
					<li><strong>Nebula admin notices</strong> (warnings/errors) are <?php echo ( empty($nebula_options['admin_notices']) )? '<strong class="nebula-disabled">disabled' : '<strong class="nebula-enabled">enabled'; ?></strong>.</li>
					<li>Nebula is <?php echo ( empty($nebula_options['unnecessary_metaboxes']) )? '<strong class="nebula-enabled">allowing' : '<strong class="nebula-disabled">removing'; ?> "unnecessary" Dashboard metaboxes</strong>.</li>
					<li>
						<?php if ( $nebula_options['jquery_version'] === 'wordpress' ): ?>
							Nebula is using the <strong class="nebula-enabled">WordPress Core version of jQuery</strong> without modification.
						<?php else: ?>
							Nebula is using the <strong class="nebula-disabled">latest version of jQuery</strong> and calling it from the <strong><?php echo ( $nebula_options['jquery_version'] === 'latest' )? '&lt;head&gt;' : '&lt;footer&gt;'; ?></strong>.
						<?php endif; ?>
					</li>
					<li>
						<?php if ( $nebula_options['bootstrap_version'] === 'latest' ): ?>
							Nebula is using the <strong class="nebula-enabled">latest version of Bootstrap</strong> with all features.
						<?php elseif ( $nebula_options['bootstrap_version'] === 'grid' ): ?>
							Nebula is using the <strong class="nebula-disabled">latest version of Bootstrap, but only the grid</strong>.
						<?php elseif ( $nebula_options['bootstrap_version'] === 'bootstrap4a5' ): ?>
							Nebula is using <strong class="nebula-disabled">Bootstrap version 4 alpha 5</strong> for support of IE9.
						<?php else: ?>
							Nebula is using <strong class="nebula-disabled">Bootstrap version 3</strong> for support of IE8.
						<?php endif; ?>
					</li>
					<li>Nebula <?php echo ( empty($nebula_options['allow_bootstrap_js']) )? '<strong class="nebula-disabled">has disabled' : '<strong class="nebula-enabled">is allowing'; ?> Bootstrap JavaScript</strong>.</li>
				</ul>

				<a class="button button-primary" href="<?php echo get_admin_url(); ?>update-core.php?force-check=1&force-nebula-theme-update">Re-Install Nebula from Github</a>
			<?php
		}

		public function nebula_installation_metabox($nebula_data){
			$nebula_options = get_option('nebula_options');

			?>
				<div class="form-group">
					<label for="first_activation">First Nebula activation date</label>
					<input type="text" id="first_activation" class="form-control" value="<?php echo $nebula_data['first_activation']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">
						First activated on: <strong><?php echo date('F j, Y \a\t g:ia', $nebula_data['first_activation']); ?></strong> (<?php echo human_time_diff($nebula_data['first_activation']); ?> ago)
					</p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="initialized">Automated initialization date</label>
					<input type="text" id="initialized" class="form-control" value="<?php echo $nebula_data['initialized']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">
						Initialized on:
						<?php if ( !empty($nebula_data['initialized']) ): ?>
							<strong><?php echo date('F j, Y \a\t g:ia', $nebula_data['initialized']); ?></strong> (<?php echo human_time_diff($nebula_data['initialized']); ?> ago)
						<?php else: ?>
							<strong><a href="/themes.php">Nebula Automation</a> has not been run yet!</strong>
						<?php endif; ?>
					</p>
					<p class="nebula-help-text more-help form-text text-muted">Shows the date of the initial Nebula Automation if it has run yet, otherwise it is empty. To run Nebula Automation, <a href="/themes.php">reactivate the Nebula parent theme here</a>.</p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="edited_yet">Have Nebula options been saved yet?</label>
					<input type="text" class="form-control" value="<?php echo ( $nebula_options['edited_yet'] )? 'Yes' : 'No'; ?>" readonly />
					<input type="text" name="nebula_options[edited_yet]" id="edited_yet" class="form-control hidden" value="true" readonly />
					<p class="nebula-help-text short-help form-text text-muted"></p>
					<p class="option-keywords">readonly</p>
				</div>
			<?php

			do_action('nebula_options_installation_metabox', $nebula_data);
		}

		public function nebula_version_metabox($nebula_data){
			?>
				<div class="form-group">
					<label for="first_version">First Nebula version when installed</label>
					<input type="text" id="first_version" class="form-control" value="<?php echo $nebula_data['first_version']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version number when it was first installed.</p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="current_version">Current Nebula version number</label>
					<input type="text" id="current_version" class="form-control" value="<?php echo $nebula_data['current_version']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version number when it was last saved. It should match: <strong><?php echo $this->version('raw'); ?></strong></p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="current_version_date">Current Nebula version date</label>
					<input type="text" id="current_version_date" class="form-control" value="<?php echo $nebula_data['current_version_date']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version date when it was last saved. It should match: <strong><?php echo $this->version('date'); ?></strong></p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="num_theme_updates">Number of Nebula theme updates</label>
					<input type="text" id="num_theme_updates" class="form-control" value="<?php echo $nebula_data['num_theme_updates']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">The number of times the parent Nebula theme has been updated via WordPress Updates.</p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="version_legacy">Legacy Nebula version?</label>
					<input type="text" id="version_legacy" class="form-control" value="<?php echo $nebula_data['version_legacy']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">If a future version is deemed incompatible with previous versions, this will become true, and theme update checks will be disabled.</p>
					<p class="nebula-help-text more-help form-text text-muted">Incompatible versions are labeled with a "u" at the end of the version number.</p>
					<p class="option-keywords">readonly</p>
				</div>

				<div class="form-group">
					<label for="next_version">Next Nebula version</label>
					<input type="text" name="nebula_options[next_version]" id="next_version" class="form-control" value="<?php echo $nebula_data['next_version']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">The latest version available on <a href="https://github.com/chrisblakley/Nebula" target="_blank">Github</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">Re-checks with <a href="/update-core.php">theme update check</a> only when Nebula Child is activated.</p>
					<p class="option-keywords">readonly</p>
				</div>
			<?php

			do_action('nebula_options_version_metabox', $nebula_data);
		}

		public function nebula_users_metabox($nebula_data){
			?>
				<div class="form-group">
					<label for="online_users">Online users</label>
					<input type="text" id="online_users" class="form-control" value="<?php echo $this->online_users(); ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">Currently online and last seen times of logged in users.</p>
					<p class="nebula-help-text more-help form-text text-muted">A value of 1 or greater indicates it is working.</p>
					<p class="option-keywords">readonly</p>
				</div>
			<?php

			do_action('nebula_options_users_metabox', $nebula_data);
		}
	}
}