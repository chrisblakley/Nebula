<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Metaboxes') ){
	trait Metaboxes {
		public function hooks(){
			add_action('admin_head', array($this, 'nebula_options_metaboxes'));
		}

		public function nebula_options_metaboxes(){
			if ( get_current_screen()->base === 'appearance_page_nebula_options' ){
				//Metadata
				add_meta_box('nebula_site_information', 'Site Information', array($this, 'site_information_metabox'), 'nebula_options', 'metadata');
				add_meta_box('nebula_business_information', 'Business Information', array($this, 'nebula_business_information'), 'nebula_options', 'metadata');
				add_meta_box('nebula_social_networks', 'Social Networks', array($this, 'nebula_social_networks'), 'nebula_options', 'metadata_side');

				//Functions
				add_meta_box('nebula_assets_metabox', 'Assets', array($this, 'nebula_assets_metabox'), 'nebula_options', 'functions_side');
				add_meta_box('nebula_front_end_metabox', 'Front-End', array($this, 'nebula_front_end_metabox'), 'nebula_options', 'functions');

				//Analytics
				add_meta_box('nebula_main_analytics_metabox', 'Main', array($this, 'nebula_main_analytics_metabox'), 'nebula_options', 'analytics');
				add_meta_box('nebula_additional_analytics_metabox', 'Additional', array($this, 'nebula_additional_analytics_metabox'), 'nebula_options', 'analytics_side');

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

				//Advanced
				add_meta_box('nebula_dequeue_styles_metabox', 'Dequeue Styles', array($this, 'dequeue_styles_metabox'), 'nebula_options', 'advanced');
				add_meta_box('nebula_dequeue_scripts_metabox', 'Dequeue Scripts', array($this, 'dequeue_scripts_metabox'), 'nebula_options', 'advanced_side');

				//Diagnostic
				add_meta_box('nebula_troubleshooting_metabox', 'Troubleshooting', array($this, 'nebula_troubleshooting_metabox'), 'nebula_options', 'diagnostic');
				add_meta_box('nebula_installation_metabox', 'Installation', array($this, 'nebula_installation_metabox'), 'nebula_options', 'diagnostic');
				add_meta_box('nebula_version_metabox', 'Nebula Version', array($this, 'nebula_version_metabox'), 'nebula_options', 'diagnostic_side');
				add_meta_box('nebula_logs_metabox', 'Automated Logs', array($this, 'nebula_logs_metabox'), 'nebula_options', 'diagnostic_side');
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
						<div class="input-group-text"><i class="fa-regular fa-fw fa-envelope"></i></div>
						<input type="email" name="nebula_options[contact_email]" id="contact_email" class="form-control nebula-validate-email" value="<?php echo $nebula_options['contact_email']; ?>" placeholder="<?php echo get_option('admin_email', $this->get_user_info('user_email', array('id' => 1))); ?>" autocomplete="email" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The main contact email address <strong>(visible in the frontend and in metadata!)</strong>.</p>
					<p class="nebula-help-text more-help form-text text-muted">If left empty, the admin email address will be used (shown by placeholder).</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="notification_email">Notification Email</label>
					<div class="input-group">
						<div class="input-group-text"><i class="fa-regular fa-fw fa-envelope"></i></div>
						<input type="email" name="nebula_options[notification_email]" id="notification_email" class="form-control nebula-validate-email" value="<?php echo $nebula_options['notification_email']; ?>" placeholder="<?php echo get_option('admin_email', $this->get_user_info('user_email', array('id' => 1))); ?>" autocomplete="email" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The email address for Nebula notifications.</p>
					<p class="nebula-help-text more-help form-text text-muted">If left empty, the admin email address will be used (shown by placeholder).</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[force_wp_timezone]" value="<?php echo $nebula_options['force_wp_timezone']; ?>">
					<input id="force_wp_timezone" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['force_wp_timezone'])); ?>><label for="force_wp_timezone">Force WP Timezone</label>

					<p class="nebula-help-text short-help form-text text-muted">Force the timezone to use the WordPress setting (<code><?php echo get_option('timezone_string'); ?></code>). Disabling this will use whatever the server is set to. (Default: <?php echo $this->user_friendly_default('force_wp_timezone'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">With the current setting, the time is <strong><?php echo date('F j, Y - g:ia'); ?></strong>. </p>
					<p class="option-keywords">time zone dst</p>
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
					<p class="nebula-help-text more-help form-text text-muted"><a href="https://schema.org/LocalBusiness" target="_blank" rel="noopener noreferrer">Use this reference under "More specific Types"</a> (click through to get the most specific possible). If you are unsure, you can use Organization, Corporation, EducationalOrganization, GovernmentOrganization, LocalBusiness, MedicalOrganization, NGO, PerformingGroup, or SportsOrganization. Details set using <a href="https://www.google.com/business/" target="_blank" rel="noopener noreferrer">Google My Business</a> will not be overwritten by Structured Data, so it is recommended to sign up and use Google My Business.</p>
					<p class="option-keywords">schema.org json-ld linked data structured data knowledge graph recommended seo</p>
				</div>

				<div class="form-group">
					<label for="phone_number">Phone Number</label>
					<div class="input-group">
						<div class="input-group-text"><i class="fa-solid fa-fw fa-phone"></i></div>
						<input type="tel" name="nebula_options[phone_number]" id="phone_number" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" value="<?php echo $nebula_options['phone_number']; ?>" placeholder="1-315-478-6700" autocomplete="tel" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="fax_number">Fax Number</label>
					<div class="input-group">
						<div class="input-group-text"><i class="fa-solid fa-fw fa-fax"></i></div>
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
									<div class="input-group-text">Latitude</div>
									<input type="text" name="nebula_options[latitude]" id="latitude" class="form-control nebula-validate-regex" data-valid-regex="^-?\d+(.\d+)?$" value="<?php echo $nebula_options['latitude']; ?>" placeholder="43.0536854" />
								</div>
							</div>
						</div><!--/col-->
						<div class="col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-text">Longitude</div>
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
								<input type="text" name="nebula_options[locality]" id="locality" class="form-control nebula-validate-text mb-2 me-sm-2 mb-sm-0" value="<?php echo $nebula_options['locality']; ?>" placeholder="Syracuse" autocomplete="address-level2" />
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<label for="region">State</label>
								<input type="text" name="nebula_options[region]" id="region" class="form-control nebula-validate-text mb-2 me-sm-2 mb-sm-0" value="<?php echo $nebula_options['region']; ?>" placeholder="NY" autocomplete="address-level1" />
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="postal_code">Postal Code</label>
								<input type="text" name="nebula_options[postal_code]" id="postal_code" class="form-control nebula-validate-regex mb-2 me-sm-2 mb-sm-0" data-valid-regex="\d{5,}" value="<?php echo $nebula_options['postal_code']; ?>" placeholder="13204" autocomplete="postal-code" />
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<label for="country_name">Country</label>
								<input type="text" name="nebula_options[country_name]" id="country_name" class="form-control nebula-validate-text mb-2 me-sm-2 mb-sm-0" value="<?php echo $nebula_options['country_name']; ?>" placeholder="USA" autocomplete="country" />
							</div>
						</div>
					</div>

					<p class="nebula-help-text short-help form-text text-muted">The address of the location (or headquarters if multiple locations).</p>
					<p class="nebula-help-text more-help form-text text-muted">Use <a href="https://nebula.gearside.com/functions/full_address/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=address+help" target="_blank" rel="noopener noreferrer"><code>nebula()->full_address()</code></a> to get the formatted address in one function. Get the individual components using <code>nebula()->get_option('street_address')</code> with the respective option names: <code>street_address</code>, <code>locality</code> (city), <code>region</code> (state), <code>postal_code</code> (zip code), and <code>country_name</code></p>
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
								<input type="hidden" name="nebula_options[business_hours_<?php echo $weekday; ?>_enabled]" value="<?php echo $nebula_options['force_wp_timezone']; ?>">
								<input id="business_hours_<?php echo $weekday; ?>_enabled" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['business_hours_' . $weekday . '_enabled'])); ?>><label for="business_hours_<?php echo $weekday; ?>_enabled"><?php echo ucfirst($weekday); ?></label>
							</div><!--/col-->
							<div class="col">
								<div class="form-group">
									<div class="input-group" dependent-of="business_hours_<?php echo $weekday; ?>_enabled">
										<div class="input-group-text"><span><i class="fa-regular fa-fw fa-clock"></i> Open</span></div>
										<input type="text" name="nebula_options[business_hours_<?php echo $weekday; ?>_open]" id="business_hours_<?php echo $weekday; ?>_open" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" value="<?php echo $nebula_options['business_hours_' . $weekday . '_open']; ?>" />
									</div>
								</div>
							</div><!--/col-->
							<div class="col">
								<div class="form-group">
									<div class="input-group" dependent-of="business_hours_<?php echo $weekday; ?>_enabled">
										<div class="input-group-text"><span><i class="fa-regular fa-fw fa-clock"></i> Close</span></div>
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
					<p class="nebula-help-text more-help form-text text-muted">These can be date formatted, or day of the month (Ex: "7/4" for Independence Day, or "Last Monday of May" for Memorial Day, or "Fourth Thursday of November" for Thanksgiving). <a href="http://mistupid.com/holidays/" target="_blank" rel="noopener noreferrer">Here is a good reference for holiday occurrences.</a>.<br /><strong>Note:</strong> This function assumes days off that fall on weekends are observed the Friday before or the Monday after.</p>
					<p class="option-keywords">seo</p>
				</div>

				<div class="form-group">
					<label for="business_type">Google Place ID</label>
					<input type="text" name="nebula_options[google_place_id]" id="google_place_id" class="form-control nebula-validate-text" value="<?php echo $this->option('google_place_id'); ?>" />
					<p class="nebula-help-text short-help form-text text-muted"><a href="https://developers.google.com/places/place-id" target="_blank" rel="noopener noreferrer">Obtain the Google Place ID</a> for your business and enter it here.</p>
					<p class="option-keywords">google maps reviews location business address</p>
				</div>
			<?php

			do_action('nebula_options_business_information_metabox', $nebula_options);
		}

		public function nebula_social_networks($nebula_options){
			?>
				<div class="form-group">
					<label for="facebookurl">Facebook</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-facebook"></i> URL</span></div>
						<input type="text" name="nebula_options[facebook_url]" id="facebook_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['facebook_url']; ?>" placeholder="http://www.facebook.com/Example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Facebook page (starting with <code>https://</code>).</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group" dependent-of="facebook_url">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-facebook"></i> Page ID</span></div>
						<input type="text" name="nebula_options[facebook_page_id]" id="facebook_page_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_page_id']; ?>" placeholder="000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The ID of your Facebook page.</p>
					<p class="option-keywords">social</p>
				</div>

				<div class="form-group" dependent-of="facebook_url">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-facebook"></i> Admin IDs</span></div>
						<input type="text" name="nebula_options[facebook_admin_ids]" id="facebook_admin_ids" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_admin_ids']; ?>" placeholder="0000, 0000, 0000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">IDs of Facebook administrators.</p>
					<p class="option-keywords">social</p>
				</div>

				<div class="form-group">
					<label for="twitter_username">Twitter</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-twitter"></i> Username</span></div>
						<input type="text" name="nebula_options[twitter_username]" id="twitter_username" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_username']; ?>" placeholder="@example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">Your Twitter username <strong>including the @ symbol</strong>.</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="linkedin_url">LinkedIn</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-linkedin"></i> URL</span></div>
						<input type="text" name="nebula_options[linkedin_url]" id="linkedin_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['linkedin_url']; ?>" placeholder="https://www.linkedin.com/company/example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your LinkedIn profile (starting with <code>https://</code>).</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="youtube_url">Youtube</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-youtube"></i> URL</span></div>
						<input type="text" name="nebula_options[youtube_url]" id="youtube_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['youtube_url']; ?>" placeholder="https://www.youtube.com/user/example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Youtube channel (starting with <code>https://</code>).</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="instagram_url">Instagram</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-instagram"></i> URL</span></div>
						<input type="text" name="nebula_options[instagram_url]" id="instagram_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['instagram_url']; ?>" placeholder="https://www.instagram.com/example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Instagram profile (starting with <code>https://</code>).</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="pinterest_url">Pinterest</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-pinterest"></i> URL</span></div>
						<input type="text" name="nebula_options[pinterest_url]" id="pinterest_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['pinterest_url']; ?>" placeholder="https://www.pinterest.com/example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your Pinterest profile (starting with <code>https://</code>).</p>
					<p class="option-keywords">social seo</p>
				</div>

				<div class="form-group">
					<label for="tiktok_url">TikTok</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-tiktok"></i> URL</span></div>
						<input type="text" name="nebula_options[tiktok_url]" id="tiktok_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['tiktok_url']; ?>" placeholder="https://www.tiktok.com/@example" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The full URL of your TikTok profile (starting with <code>https://</code> and including the <code>@</code> symbol).</p>
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
					<label for="jquery_location">jQuery Location</label>
					<select name="nebula_options[jquery_location]" id="jquery_location" class="form-select nebula-validate-select">
						<option value="wordpress" <?php selected('wordpress', $nebula_options['jquery_location']); ?>>&lt;head&gt; (WordPress Default)</option>
						<option value="footer" <?php selected('footer', $nebula_options['jquery_location']); ?>>&lt;footer&gt; (Performant)</option>
					</select>
					<p class="nebula-help-text short-help form-text text-muted">Where to load jQuery (head is blocking, footer is more performant). (Default: <?php echo $this->user_friendly_default('jquery_location'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Be careful changing this option as some plugins/page functionality may rely on jQuery loading in the &lt;head&gt;, however some speed improvements may be realized by loading later.<br /><strong>Note:</strong> some plugins may override this and bring jQuery back to the head.<br /><strong>Remember:</strong> if loading in the footer, embedded script tags cannot use jQuery in template files.</p>
					<p class="option-keywords">old support plugins minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[limit_image_dimensions]" value="<?php echo $nebula_options['limit_image_dimensions']; ?>">
					<input id="limit_image_dimensions" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['limit_image_dimensions'])); ?>><label for="limit_image_dimensions">Limit Image Dimensions</label>

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

					<input type="hidden" name="nebula_options[scss]" value="<?php echo $nebula_options['scss']; ?>">
					<input id="scss" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['scss'])); ?>><label for="scss">Sass</label>

					<p class="nebula-help-text short-help form-text text-muted">Enable the bundled SCSS compiler. (Default: <?php echo $this->user_friendly_default('scss'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Automatically process Sass for logged-in users (who can publish posts). Saving Nebula Options will also process all SCSS files. This option will automatically be disabled after 30 days without processing. CSS files will automatically be minified, but source maps are available for debugging.<br /><br />Last processed: <?php echo $last_processed_text; ?>
					</p>
					<p class="option-keywords">sass scss sccs scass css moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group" dependent-or="scss">
					<input type="hidden" name="nebula_options[critical_css]" value="<?php echo $nebula_options['critical_css']; ?>">
					<input id="critical_css" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['critical_css'])); ?>><label for="critical_css">Critical CSS</label>

					<p class="nebula-help-text short-help form-text text-muted">Output critical CSS for above-the-fold content in the <code>&lt;head&gt;</code> of the document. (Default: <?php echo $this->user_friendly_default('critical_css'); ?>)</p>
					<p class="dependent-note hidden">This option is dependent on the SCSS compiler.</p>
					<p class="nebula-help-text more-help form-text text-muted">Styles in critical.css will be embedded in the HTML while also imported into style.css. This improves perceived page load time for users without overcomplicating stylesheets.</p>
					<p class="option-keywords">sass scss sccs scass css minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[author_bios]" value="<?php echo $nebula_options['author_bios']; ?>">
					<input id="author_bios" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['author_bios'])); ?>><label for="author_bios">Author Bios</label>

					<p class="nebula-help-text short-help form-text text-muted">Allow authors to have bios that show their info (and post archives). (Default: <?php echo $this->user_friendly_default('author_bios'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This also enables searching by author, and displaying author names on posts.<br />If disabled, the author page attempts to redirect to an <a href="<?php echo home_url('/'); ?>?s=about" target="_blank">About Us page</a> (use the filter hook <code>nebula_no_author_redirect</code> to better control where it redirects- especially if no search results are found when clicking that link).<br />If disabled, remember to also disable the <a href="<?php echo admin_url('admin.php?page=wpseo_titles#top#archives'); ?>" target="_blank">Author archives option in Yoast</a> to hide them from the sitemap.</p>
					<p class="option-keywords">seo</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[comments]" value="<?php echo $nebula_options['comments']; ?>">
					<input id="comments" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['comments'])); ?>><label for="comments">Comments</label>

					<p class="nebula-help-text short-help form-text text-muted">Ability to force disable comments. (Default: <?php echo $this->user_friendly_default('comments'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles).</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[store_form_submissions]" value="<?php echo $nebula_options['store_form_submissions']; ?>">
					<input id="store_form_submissions" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['store_form_submissions'])); ?>><label for="store_form_submissions">Store Form Submissions</label>

					<p class="nebula-help-text short-help form-text text-muted">Store CF7 form submissions in WordPress. (Default: <?php echo $this->user_friendly_default('store_form_submissions'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This will capture Contact Form 7 form submissions and store them in WordPress. This will not have any affect on third-party form storage plugins, nor will it change email submission behavior. This will have no effect if the Contact Form 7 plugin is not installed or inactive. Disabling this option will <strong>not</strong> delete form submissions already captured.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[service_worker]" value="<?php echo $nebula_options['service_worker']; ?>">
					<input id="service_worker" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['service_worker'])); ?>><label for="service_worker">Service Worker</label>

					<p class="nebula-help-text short-help form-text text-muted">Utilize a service worker to improve speed, provide content when offline, and other benefits of being a progressive web app. (Default: <?php echo $this->user_friendly_default('service_worker'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Enabling this feature requires a service worker JavaScript file. Move the provided sw.js into the root directory (or write your own). Service Worker location: <code><?php echo $this->sw_location(); ?></code></p>
					<p class="option-keywords">moderate page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[spam_domain_prevention]" value="<?php echo $nebula_options['spam_domain_prevention']; ?>">
					<input id="spam_domain_prevention" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['spam_domain_prevention'])); ?>><label for="spam_domain_prevention">Spam Domain Prevention</label>

					<p class="nebula-help-text short-help form-text text-muted">Block traffic from known spambots and other illegitimate domains. (Default: <?php echo $this->user_friendly_default('spam_domain_prevention'); ?>)</p>
					<p class="option-keywords">security remote resource recommended minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[console_css]" value="<?php echo $nebula_options['console_css']; ?>">
					<input id="console_css" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['console_css'])); ?>><label for="console_css">Console CSS</label>

					<p class="nebula-help-text short-help form-text text-muted">Adds CSS to the browser console. (Default: <?php echo $this->user_friendly_default('console_css'); ?>)</p>
					<p class="option-keywords">discretionary</p>
				</div>
			<?php

			do_action('nebula_options_frontend_metabox', $nebula_options);
		}

		public function nebula_admin_references_metabox($nebula_options){
			?>
				<div class="form-group">
					<input type="hidden" name="nebula_options[admin_bar]" value="<?php echo $nebula_options['admin_bar']; ?>">
					<input id="admin_bar" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['admin_bar'])); ?>><label for="admin_bar">Admin Bar</label>

					<p class="nebula-help-text short-help form-text text-muted">Control the Wordpress Admin bar globally on the frontend for all users. (Default: <?php echo $this->user_friendly_default('admin_bar'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Note: When enabled, the Admin Bar can be temporarily toggled using the keyboard shortcut <strong>Alt+A</strong> without needing to disable it permanently for all users.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[unnecessary_metaboxes]" value="<?php echo $nebula_options['unnecessary_metaboxes']; ?>">
					<input id="unnecessary_metaboxes" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['unnecessary_metaboxes'])); ?>><label for="unnecessary_metaboxes">Remove Unnecessary Metaboxes</label>

					<p class="nebula-help-text short-help form-text text-muted">Remove metaboxes on the Dashboard that are not necessary for most users. (Default: <?php echo $this->user_friendly_default('unnecessary_metaboxes'); ?>)</p>
					<p class="option-keywords">recommended</p>
				</div>

				<div class="form-group" dependent-or="dev_email_domain dev_ip">
					<input type="hidden" name="nebula_options[dev_info_metabox]" value="<?php echo $nebula_options['dev_info_metabox']; ?>">
					<input id="dev_info_metabox" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['dev_info_metabox'])); ?>><label for="dev_info_metabox">Developer Info Metabox</label>

					<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
					<p class="nebula-help-text short-help form-text text-muted">Show theme and server information useful to developers. (Default: <?php echo $this->user_friendly_default('dev_info_metabox'); ?>)</p>
					<p class="option-keywords">recommended</p>
				</div>

				<div class="form-group" dependent-or="dev_email_domain dev_ip">
					<input type="hidden" name="nebula_options[todo_manager_metabox]" value="<?php echo $nebula_options['todo_manager_metabox']; ?>">
					<input id="todo_manager_metabox" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['todo_manager_metabox'])); ?>><label for="todo_manager_metabox">To-Do Manager</label>

					<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
					<p class="nebula-help-text short-help form-text text-muted">Aggregate todo comments in code. (Default: <?php echo $this->user_friendly_default('todo_manager_metabox'); ?>)</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[performance_metabox]" value="<?php echo $nebula_options['performance_metabox']; ?>">
					<input id="performance_metabox" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['performance_metabox'])); ?>><label for="performance_metabox">Performance Metabox</label>

					<p class="nebula-help-text short-help form-text text-muted">Test load times from the WordPress Dashboard <?php echo ( $this->is_dev() )? '(Note: This always appears for developers even if disabled!)' : ''; ?>. (Default: <?php echo $this->user_friendly_default('performance_metabox'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">Tests are prioritized from Google Lighthouse or a simple iframe timer.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[design_reference_metabox]" value="<?php echo $nebula_options['design_reference_metabox']; ?>">
					<input id="design_reference_metabox" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['design_reference_metabox'])); ?>><label for="design_reference_metabox">Design Reference Metabox</label>

					<p class="nebula-help-text short-help form-text text-muted">Show the Design Reference dashboard metabox. (Default: <?php echo $this->user_friendly_default('design_reference_metabox'); ?>)</p>
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
					<label for="warnings">Nebula Warnings</label>
					<select name="nebula_options[warnings]" id="warnings" class="form-select nebula-validate-select">
						<option value="off" <?php selected('off', $nebula_options['warnings']); ?>>Off</option>
						<option value="critical" <?php selected('critical', $nebula_options['warnings']); ?>>Critical (Essential Checks Only)</option>
						<option value="verbose" <?php selected('verbose', $nebula_options['warnings']); ?>>Verbose (Include Significant Checks)</option>
						<option value="strict" <?php selected('strict', $nebula_options['warnings']); ?>>Strict (All Checks)</option>
					</select>
					<p class="nebula-help-text short-help form-text text-muted">Allow Nebula to check for common implementation errors and warnings and report them in the WP Admin and console for logged-in users. (Default: <?php echo $this->user_friendly_default('warnings'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">To ignore certain warnings during development, change the WordPress environment out of "production" using the <code>WP_ENVIRONMENT_TYPE</code> constant. Remember to remove it or change to production when live!</p>
					<p class="option-keywords">discretionary minor page speed impact companion admin notices admin_notices advanced warnings advanced_warnings audit mode audit_mode</p>
				</div>

				<?php if ( $this->is_dev() ): //These are only shown to developers to prevent non-devs from re-enabling updates that could break something ?>
					<div class="form-group">
						<input type="hidden" name="nebula_options[theme_update_notification]" value="<?php echo $nebula_options['theme_update_notification']; ?>">
					<input id="theme_update_notification" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['theme_update_notification'])); ?>><label for="theme_update_notification">Nebula Theme Update Notification</label>

						<p class="nebula-help-text short-help form-text text-muted">Enable easy updates to the Nebula theme. (Default: <?php echo $this->user_friendly_default('theme_update_notification'); ?>)</p>
						<p class="nebula-help-text more-help form-text text-muted"><strong>Child theme must be activated to work!</strong></p>
						<p class="option-keywords">discretionary</p>
					</div>

					<div class="form-group">
						<input type="hidden" name="nebula_options[bundled_plugins_notification]" value="<?php echo $nebula_options['bundled_plugins_notification']; ?>">
					<input id="bundled_plugins_notification" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['bundled_plugins_notification'])); ?>><label for="bundled_plugins_notification">Bundled Plugins Notification</label>

						<p class="nebula-help-text short-help form-text text-muted">Control whether or not the <a href="plugins.php?page=tgmpa-install-plugins&plugin_status=install">Nebula bundled plugins</a> notifications appears on admin pages for all users. (Default: <?php echo $this->user_friendly_default('bundled_plugins_notification'); ?>)</p>
						<p class="nebula-help-text more-help form-text text-muted">When on, each WP user will need to dismiss the prompt individually. When disabled, it is not shown to any user.</p>
						<p class="option-keywords">discretionary</p>
					</div>

					<div class="form-group">
						<input type="hidden" name="nebula_options[wp_core_updates_notify]" value="<?php echo $nebula_options['wp_core_updates_notify']; ?>">
					<input id="wp_core_updates_notify" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['wp_core_updates_notify'])); ?>><label for="wp_core_updates_notify">WordPress Core Update Notification</label>

						<p class="nebula-help-text short-help form-text text-muted">Control whether or not the Wordpress Core update notifications show up on the admin pages. (Default: <?php echo $this->user_friendly_default('wp_core_updates_notify'); ?>)</p>
						<p class="option-keywords">discretionary</p>
					</div>
				<?php endif; ?>
			<?php

			do_action('nebula_options_admin_notifications_metabox', $nebula_options);
		}

		/*==========================
		 Analytics
		 ===========================*/

		public function nebula_main_analytics_metabox($nebula_options){
			?>
				<div class="form-group important-option" important-or="gtm_id">
					<label for="ga_measurement_id">Google Analytics (GA4) Measurement ID</label>
					<input type="text" name="nebula_options[ga_measurement_id]" id="ga_measurement_id" class="form-control nebula-validate-regex" data-valid-regex="^G-.+$" value="<?php echo $nebula_options['ga_measurement_id']; ?>" placeholder="G-0000000000" />
					<p class="nebula-help-text short-help form-text text-muted">This will add Google Analytics tracking to the appropriate locations.</p>
					<p class="option-keywords">remote resource recommended minor page speed impact optimization optimize ga4</p>
				</div>

				<div class="form-group"><!-- @todo "Nebula" 0: Remove after July 2023 -->
					<label for="ga_tracking_id">Google Analytics UA Tracking ID <small><em>(Optional)</em></small></label>
					<input type="text" name="nebula_options[ga_tracking_id]" id="ga_tracking_id" class="form-control nebula-validate-regex" data-valid-regex="^UA-\d+-\d+$" value="<?php echo $nebula_options['ga_tracking_id']; ?>" placeholder="UA-00000000-1" />
					<p class="nebula-help-text short-help form-text text-muted">The Tracking ID for the <strong>Universal Analytics</strong> property (Not GA4!). This should only be used for data redundancy as <strong>GA will stop collecting this data in July 2023</strong>.</p>
					<p class="option-keywords">remote resource recommended minor page speed impact optimization optimize universal analytics ua</p>
				</div>

				<div class="form-group important-option" important-or="ga_measurement_id">
					<label for="gtm_id">Google Tag Manager ID</label>
					<input type="text" name="nebula_options[gtm_id]" id="gtm_id" class="form-control nebula-validate-regex" data-valid-regex="^GTM-\S+$" value="<?php echo $nebula_options['gtm_id']; ?>" placeholder="GTM-0000000" />
					<p class="nebula-help-text short-help form-text text-muted">This will add the Google Tag Manager scripts to the appropriate locations.</p>
					<p class="option-keywords">remote resource recommended minor page speed impact optimization optimize ga4</p>
				</div>

				<div class="form-group" dependent-or="ga_measurement_id">
					<label for="ga_api_secret">Google Analytics API Secret</label>
					<input type="text" name="nebula_options[ga_api_secret]" id="ga_api_secret" class="form-control" value="<?php echo $nebula_options['ga_api_secret']; ?>" placeholder="0000000000000000000000000" />
					<p class="nebula-help-text short-help form-text text-muted">The API Secret key for using the GA4 Measurement Protocol.</p>
					<p class="nebula-help-text more-help form-text text-muted">This allows server-side events to be sent to GA4. Obtain it in GA > Admin > Data Streams > [Select your stream] > Measurement Protocol API secrets (under Additional Settings).</p>
					<p class="option-keywords">ga4 secret key</p>
				</div>

				<div class="form-group" dependent-or="ga_measurement_id">
					<label for="ga_api_secret">Google Analytics Property ID</label>
					<input type="text" name="nebula_options[ga_property_id]" id="ga_property_id" class="form-control" value="<?php echo $nebula_options['ga_property_id']; ?>" placeholder="000000000" />
					<p class="nebula-help-text short-help form-text text-muted">This is the property ID (available in the Property Settings of a GA4 property). This is helpful when searching for properties since the Measurement ID does not trigger results.</p>
					<p class="nebula-help-text more-help form-text text-muted"></p>
					<p class="option-keywords">ga4 property id</p>
				</div>

				<div class="form-group" dependent-or="ga_measurement_id">
					<input type="hidden" name="nebula_options[ga_require_consent]" value="<?php echo $nebula_options['ga_require_consent']; ?>">
					<input id="ga_require_consent" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['ga_require_consent'])); ?>><label for="ga_require_consent">Require Tracking Consent</label>

					<p class="nebula-help-text short-help form-text text-muted">Do not track Google Analytics data unless user consent is given. <strong>Warning: This will <em>dramatically</em> decrease the amount of analytics data collected when enabled!</strong> (Default: <?php echo $this->user_friendly_default('ga_require_consent'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">When enabled, Google Analytics will not be tracked until a user has accepted the cookie notification. The notification message can be customized in the Cookie Notification Nebula option.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group" dependent-or="ga_measurement_id">
					<input type="hidden" name="nebula_options[observe_dnt]" value="<?php echo $nebula_options['observe_dnt']; ?>">
					<input id="observe_dnt" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['observe_dnt'])); ?>><label for="observe_dnt">Observe "Do Not Track" Requests</label>

					<p class="nebula-help-text short-help form-text text-muted">Comply with user requests of "Do Not Track" (DNT) via their browser settings. Analytics data will not be collected for these users at all (even if they consent to tracking via the cookie notification). (Default: <?php echo $this->user_friendly_default('observe_dnt'); ?>)</p>
					<p class="option-keywords">gdpr ccpa privacy</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[attribution_tracking]" value="<?php echo $nebula_options['attribution_tracking']; ?>">
					<input id="attribution_tracking" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['attribution_tracking'])); ?>><label for="attribution_tracking">Attribution Tracking</label>

					<p class="nebula-help-text short-help form-text text-muted">Track "last-non-organic" attribution by storing UTM and other notable tracking query parameters in a cookie to retrieve on users' subsequent returning visits (on the same device). This will fill an input with a class of <code>attribution</code> with the data if it exists as well as be used in CF7 form submissions. This option must be enabled in order to use the PHP function <code>nebula()->utms()</code> and JS function <code>nebula.attributionTracking()</code>. (Default: <?php echo $this->user_friendly_default('attribution_tracking'); ?>)</p>
					<p class="option-keywords">gdpr ccpa privacy ads campaigns cookies</p>
				</div>

				<div class="form-group" dependent-or="ga_measurement_id gtm_id">
					<input type="checkbox" name="nebula_options[ga_wpuserid]" id="ga_wpuserid" value="1" <?php checked('1', !empty($nebula_options['ga_wpuserid'])); ?> /><label for="ga_wpuserid">Use WordPress User ID</label>
					<p class="nebula-help-text short-help form-text text-muted">Use the WordPress User ID as the Google Analytics User ID. (Default: <?php echo $this->user_friendly_default('ga_wpuserid'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This allows more accurate user reporting. Note: Users who share accounts (including developers/clients) can cause inaccurate reports! This functionality is most useful when opening sign-ups to the public.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label class="cookie-label" for="cookie_notification">Cookie Notification</label>
					<textarea name="nebula_options[cookie_notification]" id="cookie_notification" class="form-control textarea" rows="3"><?php echo $nebula_options['cookie_notification']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted nebula-adb-reminder-con">The text that will appear in the cookie notification (leave empty to disable).</p>
					<p class="nebula-help-text more-help form-text text-muted">If a <a href="options-privacy.php">Privacy Policy</a> page is set with WordPress core, a link will appear to that page. This field accepts HTML for cross-linking to additional legal pages.</p>
					<p class="option-keywords">privacy policy data security legal gdpr ccpa privacy notice usage tracking cookies</p>
				</div>
			<?php

			do_action('nebula_options_analytics_metabox', $nebula_options);
		}

		public function nebula_additional_analytics_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="google_ads_id">Google Ads ID</label>
					<input type="text" name="nebula_options[google_ads_id]" id="google_ads_id" class="form-control nebula-validate-regex" data-valid-regex="^AW-.+$" value="<?php echo $nebula_options['google_ads_id']; ?>" placeholder="AW-00000000" />
					<p class="nebula-help-text short-help form-text text-muted">This is a quick way to implement a Google Ads tag on the website.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize google ads adwords</p>
				</div>

				<div class="form-group">
					<label for="hostnames">Valid Hostnames</label>
					<input type="text" name="nebula_options[hostnames]" id="hostnames" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hostnames']; ?>" placeholder="<?php echo $this->url_components('domain'); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">These help generate regex patterns for Google Analytics filters.</p>
					<p class="nebula-help-text more-help form-text text-muted">It is also used for the is_site_live() function! Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)! Include this RegEx pattern for a filter/segment <a href="https://nebula.gearside.com/utilities/domain-regex-generator/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=valid+hostnames+help" target="_blank" rel="noopener noreferrer">(Learn how to use this)</a>: <input type="text" value="<?php echo $this->valid_hostname_regex(); ?>" readonly style="width: 50%;" /></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="google_search_console_verification">Google Search Console Verification</label>
					<input type="text" name="nebula_options[google_search_console_verification]" id="google_search_console_verification" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_search_console_verification']; ?>" placeholder="AAAAAA..." />
					<p class="nebula-help-text short-help form-text text-muted">This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank" rel="noopener noreferrer">Google Search Console</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</p>
					<p class="option-keywords">recommended seo</p>
				</div>

				<div class="form-group">
					<label for="facebook_custom_audience_pixel_id">Facebook Custom Audience Pixel ID</label>
					<input type="text" name="nebula_options[facebook_custom_audience_pixel_id]" id="facebook_custom_audience_pixel_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_custom_audience_pixel_id']; ?>" placeholder="000000000000000" />
					<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.facebook.com/docs/facebook-pixel" target="_blank" rel="noopener noreferrer">Facebook Custom Audience Pixel</a> tracking.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>
			<?php

			do_action('nebula_options_analytics_additional_metabox', $nebula_options);
		}

		/*==========================
		 APIs
		 ===========================*/

		public function nebula_main_apis_metabox($nebula_options){
			?>
				<div class="form-group">
					<label for="remote_font_url">Remote Font</label>
					<input type="text" name="nebula_options[remote_font_url]" id="remote_font_url" class="form-control nebula-validate-text" value="<?php echo $nebula_options['remote_font_url']; ?>" placeholder="http://fonts.googleapis.com/css?family=Open+Sans:400,800" />
					<p class="nebula-help-text short-help form-text text-muted">Paste the entire URL of your remote font(s). Popular font services include <a href="https://www.google.com/fonts" target="_blank" rel="noopener noreferrer">Google Fonts</a> and <a href="https://fonts.adobe.com/fonts" target="_blank" rel="noopener noreferrer">Adobe Fonts</a>. Include all URL parameters here!</p>
					<p class="nebula-help-text more-help form-text text-muted">The default font uses the native system font of the user's device. Be sure to include all desired parameters such as <code>&display=swap</code> or <code>:ital,wght@0,200..900;1,700</code> (for variable Google fonts) in this URL.</p>
					<p class="option-keywords">remote resource minor page speed impact optimization optimize</p>
				</div>

				<div class="form-group mb-2">
					<label for="google_browser_api_key">Google Public API</label>

					<div class="input-group">
						<div class="input-group-text">HTTP Restricted</div>
						<input type="text" name="nebula_options[google_browser_api_key]" id="google_browser_api_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_browser_api_key']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text">IP Restricted</div>
						<input type="text" name="nebula_options[google_server_api_key]" id="google_server_api_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['google_server_api_key']; ?>" />
					</div>

					<p class="nebula-help-text short-help form-text text-muted">API keys from the <a href="https://console.developers.google.com/project" target="_blank" rel="noopener noreferrer">Google Developers Console</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">In the Developers Console make a new project (if you don't have one yet). Under "Credentials" create a new key. Your current server IP address is <code><?php echo $this->super->server['SERVER_ADDR']; ?></code> (for IP restricting). Do not use an IP restricted key in JavaScript or any client-side code! Use HTTP referrer restrictions for browser keys.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group hidden">
					<label for="gcm_sender_id">Google Cloud Messaging Sender ID</label>
					<input type="text" name="nebula_options[gcm_sender_id]" id="gcm_sender_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['gcm_sender_id']; ?>" placeholder="000000000000" />
					<p class="nebula-help-text short-help form-text text-muted">The Google Cloud Messaging (GCM) Sender ID from the <a href="https://console.developers.google.com/project" target="_blank" rel="noopener noreferrer">Developers Console</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is the "Project number" within the project box on the Dashboard. Do not include parenthesis or the "#" symbol. This is used for push notifications. <strong>*Note: This feature is still in development and not currently active!</strong></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="github_pat">GitHub Personal Access Token</label>
					<input type="text" name="nebula_options[github_pat]" id="github_pat" class="form-control" placeholder="0000000000000000000000000000000000000000" value="<?php echo $nebula_options['github_pat']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted"><a href="https://github.com/settings/tokens/new" target="_blank">Generate a Personal Access Token</a> to retrieve Issues and commits on the WordPress Dashboard <a href="https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token" target="_blank">(GitHub instructions here)</a>. Nebula only needs basic repo and read discussion scopes.</p>
					<p class="option-keywords">github api pat personal access token issues commits discussions metabox</p>
				</div>

				<div class="form-group mb-2">
					<label for="hubspot_api">Hubspot</label>

					<div class="input-group">
						<div class="input-group-text">API Key</div>
						<input type="text" name="nebula_options[hubspot_api]" id="hubspot_api" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hubspot_api']; ?>" />
					</div>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text">Portal ID</div>
						<input type="text" name="nebula_options[hubspot_portal]" id="hubspot_portal" class="form-control nebula-validate-text" value="<?php echo $nebula_options['hubspot_portal']; ?>" />
					</div>

					<p class="nebula-help-text short-help form-text text-muted">Enter your Hubspot API key and Hubspot Portal ID here.</p>
					<p class="nebula-help-text more-help form-text text-muted">It can be obtained from your <a href="https://app.hubspot.com/hapikeys">API Keys page under Integrations in your account</a>. Your Hubspot Portal ID (or Hub ID) is located in the upper right of your <a href="https://app.hubspot.com/" target="_blank">account screen</a> (or within the URL itself). The Portal ID is needed to send data to your Hubspot CRM and the API key will allow for Nebula custom contact properties to be automatically created. Note: You'll still be required to <a href="https://app.hubspot.com/property-settings/<?php echo $this->get_option('hubspot_portal'); ?>/contact" target="_blank">create any of your own custom properties</a> (non-Nebula) manually. It is recommended to create your own property group for these separate from the Nebula group.</p>
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
					<p class="nebula-help-text more-help form-text text-muted"><a href="https://disqus.com/admin/create/" target="_blank" rel="noopener noreferrer">Sign-up for an account here</a>. In your Disqus account settings (where you will find your shortname), please uncheck the "Discovery" box.</p>
					<p class="option-keywords">social remote resource moderate page speed impact optimization optimize comments</p>
				</div>

				<div class="form-group">
					<label for="facebook_app_id">Facebook</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-facebook"></i> App ID</span></div>
						<input type="text" name="nebula_options[facebook_app_id]" id="facebook_app_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_app_id']; ?>" placeholder="000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The App ID of the associated Facebook page/app.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank" rel="noopener noreferrer">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-facebook"></i> App Secret</span></div>
						<input type="text" name="nebula_options[facebook_app_secret]" id="facebook_app_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_app_secret']; ?>" placeholder="00000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The App Secret of the associated Facebook page/app.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank" rel="noopener noreferrer">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-facebook"></i> Access Token</span></div>
						<input type="text" name="nebula_options[facebook_access_token]" id="facebook_access_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['facebook_access_token']; ?>" placeholder="000000000000000|000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The Access Token of the associated Facebook page/app.</p>
					<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank" rel="noopener noreferrer">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<label for="twitter_consumer_key">Twitter</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-twitter"></i> Consumer Key</span></div>
						<input type="text" name="nebula_options[twitter_consumer_key]" id="twitter_consumer_key" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_consumer_key']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The Consumer Key key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-twitter"></i> Consumer Secret</span></div>
						<input type="text" name="nebula_options[twitter_consumer_secret]" id="twitter_consumer_secret" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_consumer_secret']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The Consumer Secret key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-twitter"></i> Bearer Token</span></div>
						<input type="text" name="nebula_options[twitter_bearer_token]" id="twitter_bearer_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['twitter_bearer_token']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The bearer token is for creating custom Twitter feeds: <a href="https://nebula.gearside.com/utilities/twitter-bearer-token-generator/?utm_campaign=documentation&utm_medium=options&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=twitter+help" target="_blank" rel="noopener noreferrer">Generate a bearer token here</a></p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<label for="instagram_user_id">Instagram</label>
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-instagram"></i> User ID</span></div>
						<input type="text" name="nebula_options[instagram_user_id]" id="instagram_user_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_user_id']; ?>" placeholder="00000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
					<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-instagram"></i> Access Token</span></div>
						<input type="text" name="nebula_options[instagram_access_token]" id="instagram_access_token" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_access_token']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
					<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-instagram"></i> Client ID</span></div>
						<input type="text" name="nebula_options[instagram_client_id]" id="instagram_client_id" class="form-control nebula-validate-text" value="<?php echo $nebula_options['instagram_client_id']; ?>" placeholder="000000000000000000000000000000" />
					</div>
					<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
					<p class="option-keywords">social remote resource</p>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-text"><span><i class="fa-brands fa-fw fa-instagram"></i> Client Secret</span></div>
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
					<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the developer to enable specific console logs and other dev info.<br/>Your current anonymized IP address is <code><?php echo $this->get_ip_address(); ?></code></p>
					<p class="nebula-help-text more-help form-text text-muted">Matching anonymizes both sides, so you can enter an pre-anonymized IPs here. RegEx may also be used here. Ex: <code>/192\.168\./i</code></p>
					<p class="option-keywords">staff logs recommended</p>
				</div>

				<div class="form-group">
					<label for="dev_email_domain">Developer Email Domains</label>
					<input type="text" name="nebula_options[dev_email_domain]" id="dev_email_domain" class="form-control nebula-validate-text" value="<?php echo $nebula_options['dev_email_domain']; ?>" placeholder="<?php echo $current_user_domain; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Comma separated domains of the developer emails (without the "@") to enable specific console logs and other dev info.<br/>Your email domain is: <code><?php echo $current_user_domain; ?></code></p>
					<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/@example\./i</code></p>
					<p class="option-keywords">staff logs recommended</p>
				</div>

				<div class="form-group">
					<label for="client_ip">Client IPs</label>
					<input type="text" name="nebula_options[client_ip]" id="client_ip" class="form-control nebula-validate-text" value="<?php echo $nebula_options['client_ip']; ?>" placeholder="<?php echo $this->get_ip_address(); ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the client to enable certain features.<br/>Your current anonymized IP address is <code><?php echo $this->get_ip_address(); ?></code></p>
					<p class="nebula-help-text more-help form-text text-muted">Matching anonymizes both sides, so you can enter an pre-anonymized IPs here. RegEx may also be used here. Ex: <code>/192\.168\./i</code></p>
					<p class="option-keywords">staff logs recommended</p>
				</div>

				<div class="form-group">
					<label for="client_email_domain">Client Email Domains</label>
					<input type="text" name="nebula_options[client_email_domain]" id="client_email_domain" class="form-control nebula-validate-text" value="<?php echo $nebula_options['client_email_domain']; ?>" placeholder="<?php echo $current_user_domain; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Comma separated domains of the developer emails (without the "@") to enable certain features.<br/>Your email domain is: <code><?php echo $current_user_domain; ?></code></p>
					<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/@example\./i</code></p>
					<p class="option-keywords">staff logs recommended</p>
				</div>
			<?php

			do_action('nebula_options_staff_users_metabox', $nebula_options);
		}

		public function nebula_dashboard_references_metabox($nebula_options){
			$serverProtocol = 'http://';
			if ( (!empty($this->super->server['HTTPS']) && $this->super->server['HTTPS'] !== 'off') || $this->super->server['SERVER_PORT'] === 443 ){
				$serverProtocol = 'https://';
			}

			$host_url = explode(".", gethostname());
			$host_domain = '';
			if ( !empty($host_url[1]) && !empty($host_url[2]) ){
				$host_domain = $host_url[1] . '.' . $host_url[2];
			}
			?>
				<div class="form-group">
					<label for="cpanel_url">Server Control Panel URL</label>
					<input type="text" name="nebula_options[cpanel_url]" id="cpanel_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['cpanel_url']; ?>" placeholder="<?php echo $serverProtocol . $this->super->server['SERVER_NAME']; ?>:2082" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the control panel of the hosting account.</p>
					<p class="nebula-help-text more-help form-text text-muted">cPanel on this domain would be: <a href="<?php echo $serverProtocol . $this->super->server['SERVER_NAME']; ?>:2082" target="_blank" rel="noopener noreferrer"><?php echo $serverProtocol . $this->super->server['SERVER_NAME']; ?>:2082</a></p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="hosting_url">Hosting URL</label>
					<input type="text" name="nebula_options[hosting_url]" id="hosting_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['hosting_url']; ?>" placeholder="http://<?php echo $host_domain; ?>/" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the server host for easy access to support and other information.</p>
					<?php if ( !empty($host_domain) ): ?>
						<p class="nebula-help-text more-help form-text text-muted">Server detected as <a href="http://<?php echo $host_domain; ?>" target="_blank" rel="noopener noreferrer">http://<?php echo $host_domain; ?></a></p>
					<?php endif; ?>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="registrar_url">DNS URL</label>
					<input type="text" name="nebula_options[dns_url]" id="dns_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['dns_url']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the DNS service where DNS records are located.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="registrar_url">Domain Registrar URL</label>
					<input type="text" name="nebula_options[registrar_url]" id="registrar_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['registrar_url']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the domain registrar used for access to pointers, forwarding, and other information.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<label for="registrar_url">GitHub Repository URL</label>
					<input type="text" name="nebula_options[github_url]" id="github_url" class="form-control nebula-validate-url" value="<?php echo $nebula_options['github_url']; ?>" />
					<p class="nebula-help-text short-help form-text text-muted">Link to the GitHub repo for this website.</p>
					<p class="option-keywords"></p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[google_adsense_url]" value="<?php echo $nebula_options['google_adsense_url']; ?>">
					<input id="google_adsense_url" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['google_adsense_url'])); ?>><label for="google_adsense_url">Google AdSense URL</label>

					<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://www.google.com/adsense/" target="_blank" rel="noopener noreferrer">Google AdSense</a> account. (Default: <?php echo $this->user_friendly_default('google_adsense_url'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[amazon_associates_url]" value="<?php echo $nebula_options['amazon_associates_url']; ?>">
					<input id="amazon_associates_url" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['amazon_associates_url'])); ?>><label for="amazon_associates_url">Amazon Associates URL</label>

					<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://affiliate-program.amazon.com/home" target="_blank" rel="noopener noreferrer">Amazon Associates</a> account. (Default: <?php echo $this->user_friendly_default('amazon_associates_url'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
					<p class="option-keywords">discretionary</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[mention_url]" value="<?php echo $nebula_options['mention_url']; ?>">
					<input id="mention_url" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['mention_url'])); ?>><label for="mention_url">Mention URL</label>

					<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://mention.com/" target="_blank" rel="noopener noreferrer">Mention</a> account. (Default: <?php echo $this->user_friendly_default('mention_url'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
					<p class="option-keywords">discretionary</p>
				</div>
			<?php

			do_action('nebula_options_dashboard_references_metabox', $nebula_options);
		}

		public function nebula_notes_metabox($nebula_options){
			?>
				<div class="form-group" dependent-or="dev_email_domain dev_ip client_email_domain client_ip">
					<input type="hidden" name="nebula_options[administrative_log]" value="<?php echo $nebula_options['administrative_log']; ?>">
					<input id="administrative_log" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['administrative_log'])); ?>><label for="administrative_log">Administrative Logs</label>

					<p class="nebula-help-text short-help form-text text-muted">Automatically log notable administrative events such as when the Nebula theme is updated (Default: <?php echo $this->user_friendly_default('administrative_log'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This does not log any user data.</p>
					<p class="option-keywords">annotations logging database</p>
				</div>

				<div class="form-group">
					<input type="hidden" name="nebula_options[js_error_log]" value="<?php echo $nebula_options['js_error_log']; ?>">
					<input id="js_error_log" class="sync-checkbox" value="1" type="checkbox" <?php checked('1', !empty($nebula_options['js_error_log'])); ?>><label for="js_error_log">Log JavaScript Errors</label>

					<p class="nebula-help-text short-help form-text text-muted">Log JavaScript window errors to a js_error.log file in the child theme directory. (Default: <?php echo $this->user_friendly_default('js_error_log'); ?>)</p>
					<p class="nebula-help-text more-help form-text text-muted">This does not log any user data. Use this only when necessary as it adds a lot of overhead to the browser and server processing!</p>
					<p class="option-keywords">annotations logging database errors exceptions ux user experience log file</p>
				</div>

				<div class="form-group">
					<label for="notes">Manual Notes</label>
					<textarea name="nebula_options[notes]" id="notes" class="form-control textarea" rows="4"><?php echo $nebula_options['notes']; ?></textarea>
					<p class="nebula-help-text short-help form-text text-muted">This area can be used to keep notes. It is not used anywhere on the front-end.</p>
					<p class="option-keywords"></p>
				</div>
			<?php

			do_action('nebula_options_notes_metabox', $nebula_options);
		}

		/*==========================
		 Advanced
		 ===========================*/

		public function dequeue_styles_metabox($nebula_options){
			$all_registered_styles = get_option('optimizable_registered_styles');
			$dequeue_styles = ( !empty($nebula_options['dequeue_styles']) )? $nebula_options['dequeue_styles'] : false;
			$this->output_dequeue_fields($all_registered_styles, $dequeue_styles, 'css');

			do_action('nebula_options_dequeue_styles_metabox', $nebula_options);
		}

		public function dequeue_scripts_metabox($nebula_options){
			$all_registered_scripts = get_option('optimizable_registered_scripts');
			$dequeue_scripts = ( !empty($nebula_options['dequeue_scripts']) )? $nebula_options['dequeue_scripts'] : false;
			$this->output_dequeue_fields($all_registered_scripts, $dequeue_scripts, 'js'); //nebula_options[dequeue_scripts]

			do_action('nebula_options_dequeue_scripts_metabox', $nebula_options);
		}

		public function output_dequeue_fields($all_registered_assets=array(), $dequeue_assets=array(), $type=''){
			if ( empty($all_registered_assets) ){ //If the option has not yet been filled, set an empty array
				$all_registered_assets = array();
			}

			if ( empty($dequeue_assets) ){
				$dequeue_assets = array();
			}

			$option_handle = 'nebula_options[dequeue_scripts]';
			$icon = 'js';
			if ( $type === 'css' || strpos($type, 'style') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$option_handle = 'nebula_options[dequeue_styles]';
				$icon = 'css3-alt';
			}

			//Check for existing handles that have rules that may not be present in the above scan
			if ( is_array($dequeue_assets) ){
				$existing_dequeued_assets = array_filter($dequeue_assets); //This gets any non-empty rules that already exist
				foreach ( $existing_dequeued_assets as $handle => $rule ){
					if ( array_search($handle, array_column($all_registered_assets, 'handle')) ){ //If this handle exists in the scanned assets, then ignore this occurance as it is a duplicate here
						unset($existing_dequeued_assets[$handle]);
					}
				}
			}

			//Temporarily reverse sort by src so that filenames with versioning have the latest first (so earlier versions are the ones removed by the upcoming de-dupe)
			usort($all_registered_assets, function($a, $b) {
				return $b['src'] <=> $a['src'];
			});

			//De-duplicate the remaining handles
			$temp = array_unique(array_column($all_registered_assets, 'handle'));
			$all_registered_assets = array_intersect_key($all_registered_assets, $temp);

			//Alphabetize $all_registered_assets by handle
			usort($all_registered_assets, function($a, $b){
				return strcasecmp($a['handle'], $b['handle']);
			});

			?>
				<div class="option-sub-group">
					<p>
						Enter a comma-separated list of rules where these assets should be dequeued on the front-end (WP Admin is unaffected by these settings).<br>
						Rules can be IDs and simple boolean function names (including inverted and custom functions) without parameters. Ex: <code>123, is_front_page, !is_singular</code><br>
						Use <code>*</code> for <strong>everywhere</strong> on the front-end.<br>
						Remember: Dependent resources will also be dequeued!
					</p>

					<?php foreach ( $all_registered_assets as $asset ): ?>
						<div class="form-group no-help <?php echo ( !empty($dequeue_assets[$asset['handle']]) )? 'active' : ''; ?>">
							<div class="input-group">
								<div class="input-group-text" title="<?php echo ( !empty($dequeue_assets[$asset['handle']]) )? 'This handle has active dequeues!' : ''; ?>"><i class="fa-brands fa-fw fa-<?php echo $icon; ?>"></i> <?php echo $asset['handle']; ?></div>
								<input type="text" name="<?php echo $option_handle; ?>[<?php echo $asset['handle']; ?>]" id="<?php echo $asset['handle'] . '-' . $type; ?>" class="form-control nebula-validate-regex" data-valid-regex="^(\*)$|^(([0-9a-z!_()]+)(,\s?)*)+$" value="<?php echo ( !empty($dequeue_assets[$asset['handle']]) )? $dequeue_assets[$asset['handle']] : ''; ?>" />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">Source: <?php echo str_replace(content_url(), '', $asset['src']); ?></p>
							<p class="option-keywords">deregister dequeue registered enqueued plugins assets resources css styles js scripts optimization</p>
						</div>
					<?php endforeach; ?>

					<?php if ( !empty($existing_dequeued_assets) ): ?>
						<h2>Possibly Unnecessary Dequeue Rules</h2>
						<p>Additionally, the following handles had existing rules, but may not actually be registered on the front-end. Consider removing these rules to optimize server processing time.</p>

						<?php foreach ( $existing_dequeued_assets as $handle => $rule ): ?>
							<div class="form-group no-help <?php echo ( !empty($dequeue_assets[$handle]) )? 'active' : ''; ?>">
								<div class="input-group">
									<div class="input-group-text" title="<?php echo ( !empty($dequeue_assets[$handle]) )? 'This handle has active dequeues!' : ''; ?>"><i class="fa-brands fa-fw fa-js"></i> <?php echo $handle; ?></div>
									<input type="text" name="<?php echo $option_handle; ?>[<?php echo $handle; ?>]" id="<?php echo $handle . '-' . $type; ?>" class="form-control nebula-validate-regex" data-valid-regex="^(\*)$|^(([0-9a-z!_()]+)(,\s?)*)+$" value="<?php echo ( !empty($dequeue_assets[$handle]) )? $dequeue_assets[$handle] : ''; ?>" />
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<h2>Re-Scan for Front-End Assets</h2>
					<p class="asset-scan-status">Assets only appear in this list when a front-end "scan" is performed. Do a quick scan from here, but additional scans can be performed for a more complete list (Use the Nebula admin bar on various front-end pages).</p>
					<a class="button button-secondary scan-frontend-assets" href="<?php echo get_home_url('/'); ?>?nebula-scan=reset" target="_blank">Scan Front-End Assets</a>
				</div>
			<?php
		}

		/*==========================
		 Diagnostic
		 ===========================*/

		public function nebula_troubleshooting_metabox($nebula_data){
			$nebula_options = get_option('nebula_options');

			?>
				<p>This is a list of possible Nebula configurations that may allow or prevent certain functionality from happening. For more references, be sure to check Nebula warnings in the Admin Dashboard, try running a Nebula Audit (from the admin bar), or check the At-A-Glance metabox on the Dashboard. The other metaboxes in this Diagnostic tab may be helpful as well.</p>
				<ul>
					<?php if ( is_multisite() ): ?>
						<li>This is a multi-site installation of WordPress!</li>
					<?php endif; ?>
					<li>
						<?php if ( is_child_theme() ): ?>
							<strong>Nebula Child</strong> theme is active<?php echo ( $this->allow_theme_update() )? '. Automated updates <strong class="nebula-enabled">are</strong> allowed.' : ', but automated updates are <strong class="nebula-disabled">not</strong> allowed.'; ?>
						<?php else: ?>
							Child theme is <strong class="nebula-disabled">not</strong> being used. Automated updates will <strong class="nebula-disabled">not</strong> be available.
						<?php endif; ?>
					</li>
					<li>The local Nebula version is <strong><?php echo $this->version('full'); ?></strong><?php echo ( !empty($nebula_data['next_version']) )? ' and the remote (GitHub) version is <strong>' . $nebula_data['next_version'] . '</strong>.' : '.'; ?></li>

					<?php if ( !empty($nebula_data['last_automated_update_date']) ): ?>
						<li>Nebula was last updated via the WordPress updater <strong><?php echo human_time_diff($nebula_data['last_automated_update_date']); ?> ago</strong> (<strong><?php echo date('F j, Y \a\t g:ia', $nebula_data['last_automated_update_date']); ?></strong>) by <strong><?php echo $nebula_data['last_automated_update_user']; ?></strong>.</li>
					<?php endif; ?>

					<li><strong>WordPress Core update notifications</strong> are <?php echo ( empty($nebula_options['wp_core_updates_notify']) )? '<strong class="nebula-disabled">hidden' : '<strong class="nebula-enabled">allowed'; ?></strong> by Nebula.</li>
					<li>Nebula <strong>Sass processing</strong> is <?php echo ( empty($nebula_options['scss']) )? '<strong class="nebula-disabled">disabled' : '<strong class="nebula-enabled">enabled'; ?></strong>.</li>
					<li>The <strong>WordPress Admin Bar</strong> is <?php echo ( empty($nebula_options['admin_bar']) )? '<strong class="nebula-disabled">hidden' : '<strong class="nebula-enabled">allowed'; ?></strong> by Nebula.</li>
					<li><strong>Nebula admin notices</strong> (warnings/errors) are <?php echo ( empty($nebula_options['admin_notices']) )? '<strong class="nebula-disabled">disabled' : '<strong class="nebula-enabled">enabled'; ?></strong>.</li>
					<li>Nebula is <?php echo ( empty($nebula_options['unnecessary_metaboxes']) )? '<strong class="nebula-enabled">allowing' : '<strong class="nebula-disabled">removing'; ?> "unnecessary" Dashboard metaboxes</strong>.</li>
					<li>
						<?php
							$dequeue_styles = ( !empty($nebula_options['dequeue_styles']) )? $nebula_options['dequeue_styles'] : array(); //Fallback to empty array so it can be filtered without PHP warnings. Change this to nullish coalescing when supported.
							$dequeue_scripts = ( !empty($nebula_options['dequeue_scripts']) )? $nebula_options['dequeue_scripts'] : array(); //Fallback to empty array so it can be filtered without PHP warnings. Change this to nullish coalescing when supported.
						?>
						Nebula is <?php echo ( !empty(array_filter($dequeue_styles)) || !empty(array_filter($dequeue_scripts)) )? '<strong class="nebula-disabled">dequeuing styles and scripts' : '<strong class="nebula-enabled">not dequeuing assets'; ?></strong> on the front-end.
					</li>
					<li>
						<?php if ( $nebula_options['jquery_location'] === 'wordpress' ): ?>
							Nebula is using the <strong class="nebula-enabled">WordPress Core version of jQuery</strong> without modification.
						<?php else: ?>
							Nebula is <strong class="nebula-disabled">moving jQuery to the &lt;footer&gt;</strong>.
						<?php endif; ?>
					</li>
					<li>Google Analytics is <?php echo ( !empty($nebula_options['ga_require_consent']) )? '<strong class="nebula-disabled">only tracking after user consent</strong>' : '<strong class="nebula-enabled">tracking without needing user consent</strong>'; ?>.</li>
					<li><a href="plugins.php?page=tgmpa-install-plugins&plugin_status=install">Nebula bundled plugins page</a> can be accessed here.</li>
				</ul>

				<a class="button button-primary" href="<?php echo admin_url('update-core.php?force-check=1&force-nebula-theme-update'); ?>" <?php echo ( is_multisite() )? 'disabled' : ''; ?>>Re-Install Nebula from GitHub</a>
				<?php if ( is_multisite() ): ?>
					<br /><small class="nebula-disabled">Nebula cannot re-install itself on WordPress multi-site installations because the theme is managed at the Network Admin level and not by the individual site.</small>
				<?php endif; ?>
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
					<label for="next_version">Next Nebula version</label>
					<input type="text" name="nebula_options[next_version]" id="next_version" class="form-control" value="<?php echo $nebula_data['next_version']; ?>" readonly />
					<p class="nebula-help-text short-help form-text text-muted">The latest version available on <a href="https://github.com/chrisblakley/Nebula" target="_blank">GitHub</a>.</p>
					<p class="nebula-help-text more-help form-text text-muted">Re-checks with <a href="update-core.php">theme update check</a> only when Nebula Child is activated.</p>
					<p class="option-keywords">readonly</p>
				</div>
			<?php

			do_action('nebula_options_version_metabox', $nebula_data);
		}

		public function nebula_logs_metabox($nebula_data){
			$nebula_options = get_option('nebula_options');

			if ( function_exists('mb_strimwidth') && !empty($nebula_options['administrative_log']) ): //Double check that multibyte functions are available first
				if ( $this->is_staff() ):
					$this->timer('Nebula Logs Metabox');
					$columns = $this->get_logs(false);
					$rows = $this->get_logs(true);
				?>
					<div id="nebula-add-log">
						<input id="log-message" type="text" placeholder="Log message" /> <input id="log-importance" type="number" min="0" max="10" value="5" /> <a id="submit-log-message" class="button button-primary" href="#"><i id="add-log-progress" class="fa-solid fa-fw fa-calendar-plus"></i> Add Log Message</a>
					</div>

					<div id="nebula-log-reload-container">
						<table id="nebula-logs">
							<thead>
								<tr>
									<?php foreach ( $columns as $column ): ?>
										<td class="<?php echo $column->Field; ?>"><?php echo ucwords(str_replace('_id', '', $column->Field)); ?></td>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $rows as $row ): //Rows ?>
									<tr data-id="<?php echo intval($row->id); ?>" data-importance="<?php echo intval($row->importance); ?>">
										<?php foreach ( $row as $column => $value ): //Columns ?>
											<td class="<?php echo $column; ?>">
												<div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;">
													<?php
														$sanitized_value = sanitize_text_field(mb_strimwidth($value, 0, 153, '...')); //Use multibytye function for more accurate string length slice

														if ( $column === 'user_id' ){
															$sanitized_value = ( $sanitized_value === 0 )? '(Cron)' : get_userdata($sanitized_value)->display_name;
														}

														if ( $column === 'timestamp' ){
															$sanitized_value = '<i class="remove fa-solid fa-fw fa-ban"></i> ' . date('l, F j, Y - g:i:sa', $sanitized_value);
														}

														echo $sanitized_value;
													?>
												</div>
											</td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<p id="nebula-clean-logs">
						<strong id="log-count"><?php echo count($rows); ?></strong> total logs. <a id="clean-log-messages" href="#"><i id="clean-log-progress" class="fa-solid fa-fw fa-trash-alt"></i> Remove Low Importance Logs?</a>
					</p>
					<?php $this->timer('Nebula Logs Metabox', 'end'); ?>
				<?php else: ?>
					<p><strong class="nebula-disabled">Nebula Logs are enabled, but only available to staff.</strong></p>
					<p><a href="themes.php?page=nebula_options&tab=administration&option=dev_email_domain">Set up staff rules</a> to view the logs.</p>
				<?php endif; ?>
			<?php else: ?>
				<p><strong class="nebula-disabled">The Nebula Logs option is not enabled.</strong></p>
				<p><a href="themes.php?page=nebula_options&tab=administration&option=logs">Enable it here</a> to begin automatically logging notable events.</p>
			<?php endif;
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