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


<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
	html {font-size: 14px;}
	body {background: #f1f1f1; font-family: "Helvetica Neue", sans-serif; font-weight: 400;}

	h2 {font-size: 24px; margin-bottom: 35px;}
	h3 {font-size: 18px; margin-bottom: 25px;}
	h4 {font-size: 14px;}

	#options-navigation {transition: opacity 0.25s;}

	.sticky {position: sticky; top: 0;}

	.nav-link {font-size: 16px;}
		.nav-link i {color: #23282d;}
			.nav-link.active i {color: #fff;}
		i.empty-important-tab-warn {color: #d9534f !important; line-height: 24px; float: right;}

	#preset-filters {margin: 0; padding: 0; list-style: none;}

	.option-group {margin-bottom: 50px;}

	.form-group {margin-bottom: 25px; transition: opacity 0.25s;}
	.form-group label {}
	.form-group .form-text {font-size: 12px;}

	.input-group {transition: opacity 0.25s;}
	.inactive {opacity: 0.4;}
		.inactive .nav-item,
		.inactive input,
		.inactive label {pointer-events: none;}

	.toggle-more-help {margin-left: 5px; color: #666;}
		.toggle-more-help:hover {color: #0098d7;}
	.nebula-help-text {margin: .25rem 0 0 0;}
		.nebula-help-text.more-help {display: none;}

	.direct-link {margin-left: 5px; color: #666;}
		.direct-link:hover {color: #0098d7;}

	.important-empty label {color: #d9534f; font-weight: bold;}
		.important-warning {font-weight: normal; margin: .25rem 0 0 0;}

	.dependent-note {color: #d9534f; margin: .25rem 0 0 0; padding: 0; font-size: 12px;}

	input[type=checkbox] {height: 0; width: 0; visibility: hidden;}
	input[type=checkbox] + label {display: flex; position: relative; align-items: center; cursor: pointer; text-indent: 70px; width: 54px; white-space: nowrap; padding: 0.5rem 0; font-size: 1rem; line-height: 1.25; background: #ddd; border-radius: 1.25rem; margin: 0 15px 0 0; overflow: visible;}
		input[type=checkbox] + label:after {content: ''; position: absolute; left: 5px; width: 1.25rem; height: 1.25rem; background: #fff; border-radius: 50%; transition: 0.25s;}
	input:checked + label {background: #5cb85c;}
		input:checked + label:after {left: calc(100% - 5px); transform: translateX(-100%);}
	input[type=checkbox] + label:active:after {width: 1.5rem;}

	.option-keywords {display: none;}

	.hidden {display: none !important;} /* remove this */

	#reset-filter {text-align: right; margin-top: .25rem;}
		#reset-filter a {transition: all 0.25s;}

	.filtereditem {display: none !important;}

	.save-row {margin-bottom: 35px;}

	.highlight {padding: 10px 15px; background: #fcf8e3; border: 1px dotted #faf2cc;}
</style>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-3">
			<div id="stickynav">
				<ul id="options-navigation" class="nav nav-pills flex-column">
					<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'metadata' )? 'active' : ''; ?>" href="#metadata" data-toggle="tab"><i class="fa fa-fw fa-tags"></i> Metadata</a></li>
					<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'functions' )? 'active' : ''; ?>" href="#functions" data-toggle="tab"><i class="fa fa-fw fa-sliders"></i> Functions</a></li>
					<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'analytics' )? 'active' : ''; ?>" href="#analytics" data-toggle="tab"><i class="fa fa-fw fa-area-chart"></i> Analytics</a></li>
					<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'apis' )? 'active' : ''; ?>" href="#apis" data-toggle="tab"><i class="fa fa-fw fa-key"></i> APIs</a></li>
					<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'administration' )? 'active' : ''; ?>" href="#administration" data-toggle="tab"><i class="fa fa-fw fa-briefcase"></i> Administration</a></li>
					<?php //do_action('nebula_options_interface_additional_tabs'); //@todo: uncomment this ?>
					<?php //if ( current_user_can('manage_options') ): //@todo: uncomment this ?>
						<li class="nav-item"><a class="nav-link <?php echo ( $active_tab === 'diagnostic' )? 'active' : ''; ?>" href="#diagnostic" data-toggle="tab"><i class="fa fa-fw fa-life-ring"></i> Diagnostic</a></li>
					<?php //endif; //@todo: uncomment this ?>
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
					<?php //do_action('nebula_options_interface_preset_filters'); //@todo: uncomment this ?>
				</ul>

				<br/><br/><!-- @todo: use margin here -->

				<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button -->
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
								<input type="text" name="nebula_options[site_owner]" id="site_owner" class="form-control" value="<?php //echo nebula()->option('site_owner'); //@todo: uncomment ?>" placeholder="<?php //echo bloginfo('name'); //@todo: uncomment ?>" />
								<p class="nebula-help-text short-help form-text text-muted">The name of the company (or person) who this website is for.</p>
								<p class="nebula-help-text more-help form-text text-muted">This is used when using nebula()->the_author(0) with author names disabled.</p>
								<p class="option-keywords">recommended seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Contact Email</label>
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-fw fa-envelope"></i></div>
									<input type="email" id="inlineFormInputGroup2" class="form-control nebula-validate-email" placeholder="chris@gearside.com">
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
								<input type="text" name="nebula_options[business_type]" id="business_type" class="form-control" value="<?php //echo nebula()->option('business_type'); //@todo: uncomment ?>" placeholder="LocalBusiness" />
								<p class="nebula-help-text short-help form-text text-muted">This schema is used for Structured Data.</p>
								<p class="nebula-help-text more-help form-text text-muted"><a href="https://schema.org/LocalBusiness" target="_blank">Use this reference under "More specific Types"</a> (click through to get the most specific possible). If you are unsure, you can use Organization, Corporation, EducationalOrganization, GovernmentOrganization, LocalBusiness, MedicalOrganization, NGO, PerformingGroup, or SportsOrganization. Details set using <a href="https://www.google.com/business/" target="_blank">Google My Business</a> will not be overwritten by Structured Data, so it is recommended to sign up and use Google My Business.</p>
								<p class="option-keywords">schema.org json-ld linked data structured data knowledge graph recommended seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Phone Number</label>
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-fw fa-phone"></i></div>
									<input type="tel" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" placeholder="1-315-478-6700">
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The primary phone number used for Open Graph data. Use the format: "1-315-478-6700".</p>
								<p class="option-keywords">recommended seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Fax Number</label>
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-fw fa-fax"></i></div>
									<input type="tel" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="\d-\d{3}-\d{3}-\d{4}" placeholder="1-315-426-1392">
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The fax number used for Open Graph data. Use the format: "1-315-426-1392".</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<label for="inlineFormInputGroup">Geolocation</label>

								<div class="row">
									<div class="col-sm-6">
										<div class="input-group mb-2">
											<div class="input-group-addon">Latitude</div>
											<input type="text" id="inlineFormInputGroup" class="form-control nebula-validate-regex" data-valid-regex="^-?\d+(.\d+)?$" placeholder="43.0536854">
										</div>
									</div><!--/col-->
									<div class="col-sm-6">
										<div class="input-group">
											<div class="input-group-addon">Longitude</div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="^-?\d+(.\d+)?$" placeholder="-76.0536854">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<p class="nebula-help-text short-help form-text text-muted">The latitude and longitude of the physical location (or headquarters if multiple locations). Use the format "43.0536854".</p>
								<p class="option-keywords">location recommended seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Address</label>
								<input type="text" id="example_option" class="form-control mb-2" placeholder="760 West Genesee Street" />

								<div class="row">
									<div class="col-sm-5">
										<label for="example_option">City</label>
										<input type="text" id="example_option" class="form-control mb-2 mr-sm-2 mb-sm-0" placeholder="Syracuse" />
									</div>
									<div class="col-sm-2">
										<label for="example_option">State</label>
										<input type="text" id="example_option" class="form-control mb-2 mr-sm-2 mb-sm-0" placeholder="NY" />
									</div>
									<div class="col-sm-3">
										<label for="example_option">Zip</label>
										<input type="text" id="example_option" class="form-control mb-2 mr-sm-2 mb-sm-0" placeholder="13204" />
									</div>
									<div class="col-sm-2">
										<label for="example_option">Country</label>
										<input type="text" id="example_option" class="form-control mb-2 mr-sm-2 mb-sm-0" placeholder="USA" />
									</div>
								</div>

								<p class="nebula-help-text short-help form-text text-muted">The address of the location (or headquarters if multiple locations).</p>
								<p class="nebula-help-text more-help form-text text-muted">Use <a href="https://gearside.com/nebula/functions/full_address/" target="_blank"><code>nebula()->full_address()</code></a> to get the formatted address in one function.</p>
								<p class="option-keywords">location recommended seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Business Hours</label>

								<?php $time_regex = '^([0-1]?[0-9]:\d{2}\s?[ap]m)|([0-2]?[0-9]:\d{2})$'; ?>

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch5" /><label for="switch5">Sunday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch5">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch5">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch4" /><label for="switch4">Monday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch4">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch4">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch6" /><label for="switch6">Tuesday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch6">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch6">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch7" /><label for="switch7">Wednesday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch7">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch7">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch8" /><label for="switch8">Thursday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch8">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch8">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch9" /><label for="switch9">Friday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch9">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch9">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<div class="row non-filter mb-2">
									<div class="col-3">
										<input type="checkbox" id="switch10" /><label for="switch10">Saturday</label>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch10">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Open</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="8:00am">
										</div>
									</div><!--/col-->
									<div class="col">
										<div class="input-group" dependent-of="switch10">
											<div class="input-group-addon"><span><i class="fa fa-fw fa-clock-o"></i> Close</span></div>
											<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $time_regex; ?>" placeholder="5:30pm">
										</div>
									</div><!--/col-->
								</div><!--/row-->

								<p class="nebula-help-text short-help form-text text-muted">Open/Close times. Times should be in the format "5:30 pm" or "17:30". Uncheck all to disable this meta.</p>
								<p class="option-keywords">seo</p>
							</div>

							<div class="form-group" dependent-or="switch4 switch5 switch6 switch7 switch8 switch9 switch10">
								<label for="example_option">Days Off</label>
								<textarea id="exampleTextarea" class="form-control" rows="2"></textarea>
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
									<input type="text" id="facebookurl" class="form-control nebula-validate-url" placeholder="https://www.facebook.com/PinckneyHugo">
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The full URL of your Facebook page.</p>
								<p class="option-keywords">social seo</p>
							</div>

							<div class="form-group" dependent-of="facebookurl">
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> Page ID</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="0000000000000000000000" />
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The ID of your Facebook page.</p>
								<p class="option-keywords">social</p>
							</div>

							<div class="form-group" dependent-of="facebookurl">
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> Admin ID</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="0000, 0000, 0000" />
								</div>
								<p class="nebula-help-text short-help form-text text-muted">IDs of Facebook administrators.</p>
								<p class="option-keywords">social</p>
							</div>

							<div class="form-group">
								<label for="example_option">Twitter</label>
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Username</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="@PinckeyHugo" />
								</div>
								<p class="nebula-help-text short-help form-text text-muted">Your Twitter username <strong>including the @ symbol</strong>.</p>
								<p class="option-keywords">social seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">LinkedIn</label>
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-linkedin"></i> URL</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-url" placeholder="https://www.linkedin.com/company/pinckney-hugo-group" />
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The full URL of your LinkedIn profile.</p>
								<p class="option-keywords">social seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Youtube</label>
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-youtube"></i> URL</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-url" placeholder="https://www.youtube.com/user/pinckneyhugo">
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The full URL of your Youtube channel.</p>
								<p class="option-keywords">social seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Instagram</label>
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> URL</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-url" placeholder="https://www.instagram.com/pinckneyhugo">
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The full URL of your Instagram profile.</p>
								<p class="option-keywords">social seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Pinterest</label>
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-pinterest"></i> URL</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-url" placeholder="https://www.pinterest.com/pinckneyhugo">
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The full URL of your Pinterest profile.</p>
								<p class="option-keywords">social seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Google+</label>
								<div class="input-group">
									<div class="input-group-addon"><span><i class="fa fa-fw fa-google-plus"></i> URL</span></div>
									<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-url" placeholder="https://plus.google.com/123456789" />
								</div>
								<p class="nebula-help-text short-help form-text text-muted">The full URL of your Google+ page.</p>
								<p class="option-keywords">social seo</p>
							</div>
						</div><!-- /option-group -->

						<?php //do_action('nebula_options_interface_metadata'); //@todo: uncomment this ?>
					</div><!--/col-->
				</div><!--/row-->
				<div class="row save-row">
					<div class="col-xl-8">
						<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button -->
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
								<label for="exampleSelect1">Bootstrap Version</label>
								<select id="exampleSelect1" class="form-control">
									<option>Latest (IE10+)</option>
									<option>Bootstrap 4 alpha 5 (IE9+)</option>
									<option>Bootstrap 3 (IE8+)</option>
								</select>
								<p class="nebula-help-text short-help form-text text-muted">Which Bootstrap version to use. (Default: Latest)</p>
								<p class="nebula-help-text more-help form-text text-muted">Bootstrap 3 will support IE8+. Bootstrap 4 alpha 5 will support IE9+. Bootstrap latest supports IE10+.</p>
								<p class="option-keywords">internet explorer old support</p>
							</div>
						</div><!-- /option-group -->

						<div class="option-group">
							<h3>Front-End</h3>

							<div class="form-group">
								<input type="checkbox" id="switch1" /><label for="switch1">Author Bios</label>
								<p class="nebula-help-text short-help form-text text-muted">Allow authors to have bios that show their info (and post archives). (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">This also enables searching by author, and displaying author names on posts. If disabled, the author page attempts to redirect to an About Us page.</p>
								<p class="option-keywords">seo</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch2" /><label for="switch2">Comments</label>
								<p class="nebula-help-text short-help form-text text-muted">Ability to force disable comments. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">If enabled, comments must also be opened as usual in Wordpress Settings > Discussion (Allow people to post comments on new articles).</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch3" /><label for="switch3">Browser/Device Detection</label>
								<p class="nebula-help-text short-help form-text text-muted">Detect information about the user's device and browser. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Useful for cross-browser support. This also controls the modernizr.js library.</p>
								<p class="option-keywords">remote resource moderate page speed impact</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch15" /><label for="switch15">IP Geolocation</label>
								<p class="nebula-help-text short-help form-text text-muted">Lookup the country, region, and city of the user based on their IP address. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">This can be used for content as well as analytics (including Visitors Database)</p>
								<p class="option-keywords">location remote resource minor page speed impact</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch16" /><label for="switch16">Domain Blacklisting</label>
								<p class="nebula-help-text short-help form-text text-muted">Block traffic from known spambots and other illegitimate domains. (Default: Enabled)</p>
								<p class="option-keywords">security remote resource recommended minor page speed impact</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch17" /><label for="switch17">Ad Block Detection</label>
								<p class="nebula-help-text short-help form-text text-muted">Detect if visitors are using ad blocking software.(Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">To track in Google Analytics, add a dimension index under the "Analytics" tab.</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch18" /><label for="switch18">Console CSS</label>
								<p class="nebula-help-text short-help form-text text-muted">Adds CSS to the browser console. (Default: Enabled)</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch19" /><label for="switch19">Weather Detection</label>
								<p class="nebula-help-text short-help form-text text-muted">Lookup weather conditions for locations. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Can be used for changing content as well as analytics.</p>
								<p class="option-keywords">location remote resource major page speed impact</p>
							</div>
						</div><!-- /option-group -->

						<div class="option-group">
							<h3>Stylesheets</h3>

							<div class="form-group">
								<input type="checkbox" id="switch20" /><label for="switch20">Sass</label>
								<p class="nebula-help-text short-help form-text text-muted">Enable the bundled SCSS compiler. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Save Nebula Options to manually process all SCSS files. This option will automatically be disabled after 30 days without processing. Last processed: <strong><?php echo ( $nebula_data['scss_last_processed'] )? date('l, F j, Y - g:ia', $nebula_data['scss_last_processed']) : 'Never'; ?></strong></p>
								<p class="option-keywords">moderate page speed impact</p>
							</div>

							<div class="form-group" dependent-of="switch20">
								<input type="checkbox" id="switch21" /><label for="switch21">Minify CSS</label>
								<p class="dependent-note hidden">This option is dependent on Sass (above).</p>
								<p class="nebula-help-text short-help form-text text-muted">Minify the compiled CSS. (Default: Disabled)</p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch22" /><label for="switch22">Developer Stylesheets</label>
								<p class="nebula-help-text short-help form-text text-muted">Allows multiple developers to work on stylesheets simultaneously. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Combines CSS files within /assets/css/dev/ into /assets/css/dev.css to allow multiple developers to work on a project without overwriting each other while maintaining a small resource footprint.</p>
								<p class="option-keywords">minor page speed impact</p>
							</div>
						</div><!-- /option-group -->
					</div><!--/col-->
					<div class="col-xl-8">
						<div class="option-group">
							<h3>Admin References</h3>

							<div class="form-group">
								<input type="checkbox" id="switch26" /><label for="switch26">Admin Bar</label>
								<p class="nebula-help-text short-help form-text text-muted">Control the Wordpress Admin bar globally on the frontend for all users. (Default: Enabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Note: When enabled, the Admin Bar can be temporarily toggled using the keyboard shortcut <strong>Alt+A</strong> without needing to disable it permanently for all users.</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch27" /><label for="switch27">Visitors Database</label>
								<p class="nebula-help-text short-help form-text text-muted">Adds a table to the database to store visitor usage information. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">This data can be used for insight as well as retargeting/personalization. General events are automatically captured, but refer to the Nebula documentation for instructions on how to interact with data in both JavaScript and PHP. <a href="http://www.hubspot.com/products/crm" target="_blank">Sign up for Hubspot CRM</a> (free) and add your API key to Nebula Options (under the APIs tab) to send known user data automatically. This integration can cultivate their <a href="http://www.hubspot.com/products/marketing" target="_blank">full marketing automation service</a>.</p>
								<p class="option-keywords">moderate page speed impact discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch28" /><label for="switch28">Remove Unnecessary Metaboxes</label>
								<p class="nebula-help-text short-help form-text text-muted">Remove metaboxes on the Dashboard that are not necessary for most users. (Default: Enabled)</p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group" dependent-or="developer_email_domains developer_ips">
								<input type="checkbox" id="switch29" /><label for="switch29">Developer Info Metabox</label>
								<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
								<p class="nebula-help-text short-help form-text text-muted">Show theme and server information useful to developers. (Default: Enabled)</p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group" dependent-or="developer_email_domains developer_ips">
								<input type="checkbox" id="switch30" /><label for="switch30">Todo Manager</label>
								<p class="dependent-note hidden">This option is dependent on Developer IPs and/or Developer Email Domains (Administration tab).</p>
								<p class="nebula-help-text short-help form-text text-muted">Aggregate todo comments in code. (Default: Enabled)</p>
								<p class="option-keywords"></p>
							</div>
						</div><!-- /option-group -->

						<div class="option-group">
							<h3>Admin Notifications</h3>

							<div class="form-group">
								<input type="checkbox" id="switch23" /><label for="switch23">Nebula Admin Notifications</label>
								<p class="nebula-help-text short-help form-text text-muted">Show Nebula-specific admin notices (Default: Enabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Note: This does not toggle WordPress core, or plugin, admin notices.</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch24" /><label for="switch24">Nebula Theme Update Notification</label>
								<p class="nebula-help-text short-help form-text text-muted">Enable easy updates to the Nebula theme. (Default: Enabled)</p>
								<p class="nebula-help-text more-help form-text text-muted"><strong>Child theme must be activated to work!</strong></p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch25" /><label for="switch25">WordPress Core Update Notification</label>
								<p class="nebula-help-text short-help form-text text-muted">Control whether or not the Wordpress Core update notifications show up on the admin pages. (Default: Enabled)</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch26" /><label for="switch26">Plugin Warning</label>
								<p class="nebula-help-text short-help form-text text-muted">Control whether or not the plugin update warning appears on admin pages. (Default: Enabled)</p>
								<p class="option-keywords">discretionary</p>
							</div>
						</div><!-- /option-group -->

						<div class="option-group">
							<h3>Prototyping</h3>

							<div class="form-group">
								<input type="checkbox" id="switch11" /><label for="switch11">Prototype Mode</label>
								<p class="nebula-help-text short-help form-text text-muted">When prototyping, enable this setting. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">Use the wireframe theme and production theme settings to develop the site while referencing the prototype. Use the staging theme to edit the site or develop new features while the site is live. If the staging theme is the active theme, use the Advanced Setting dropdown for "Theme For Everything" and choose a theme there for general visitors (Note: If using this setting, you may need to select that same theme for the admin-ajax option too!).</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group" dependent-of="switch11">
								<label for="exampleSelect2">Wireframe Theme</label>
								<select id="exampleSelect2" class="form-control">
									<option>None</option>
									<option>Theme Name</option>
									<option>Theme Name</option>
								</select>
								<p class="nebula-help-text short-help form-text text-muted">The theme to use as the wireframe. Viewing this theme will trigger a greyscale view.</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group" dependent-of="switch11">
								<label for="exampleSelect3">Staging Theme</label>
								<select id="exampleSelect3" class="form-control">
									<option>None</option>
									<option>Theme Name</option>
									<option>Theme Name</option>
								</select>
								<p class="nebula-help-text short-help form-text text-muted">The theme to use for staging new features. This is useful for site development after launch.</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group" dependent-of="switch11">
								<label for="exampleSelect4">Production (Live) Theme</label>
								<select id="exampleSelect4" class="form-control">
									<option>None</option>
									<option>Theme Name</option>
									<option>Theme Name</option>
								</select>
								<p class="nebula-help-text short-help form-text text-muted">The theme to use for production/live. This theme will become the live site.</p>
								<p class="option-keywords"></p>
							</div>
						</div><!-- /option-group -->

						<?php //do_action('nebula_options_interface_functions'); //@todo: uncomment this ?>
					</div><!--/col-->
				</div><!--/row-->
				<div class="row save-row">
					<div class="col-xl-8">
						<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button -->
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
								<label for="gatrackingid">Google Analytics Tracking ID</label>
								<input type="text" id="gatrackingid" class="form-control nebula-validate-regex" data-valid-regex="ua-\d+-\d+" placeholder="UA-00000000-1" />
								<p class="nebula-help-text short-help form-text text-muted">This will add the tracking number to the appropriate locations.</p>
								<p class="option-keywords">remote resource recommended minor page speed impact</p>
							</div>

							<div class="form-group" dependent-of="gatrackingid">
								<input type="checkbox" id="switch33" /><label for="switch33">Use WordPress User ID</label>
								<p class="nebula-help-text short-help form-text text-muted">Use the WordPress User ID as the Google Analytics User ID. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">This allows more accurate user reporting. Note: Users who share accounts (including developers/clients) can cause inaccurate reports! This functionality is most useful when opening sign-ups to the public.</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group" dependent-of="gatrackingid">
								<input type="checkbox" id="switch34" /><label for="switch34">Display Features</label>
								<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">Google display features</a> in the analytics tag. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">This enables Advertising Features in Google Analytics, such as Remarketing, Demographics and Interest Reporting, and more.</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group" dependent-of="gatrackingid">
								<input type="checkbox" id="switch35" /><label for="switch35">Enhanced Link Attribution (Link ID)</label>
								<p class="nebula-help-text short-help form-text text-muted">Toggle the <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-link-attribution" target="_blank">Enhanced Link Attribution</a> in the Property Settings of the Google Analytics Admin. (Default: Enabled)</p>
								<p class="nebula-help-text more-help form-text text-muted">This improves the accuracy of your In-Page Analytics report by automatically differentiating between multiple links to the same URL on a single page by using link element IDs.</p>
								<p class="option-keywords">minor page speed impact</p>
							</div>

							<div class="form-group">
								<label for="example_option">AdWords Remarketing Conversion ID</label>
								<input type="text" id="example_option" class="form-control" placeholder="0000000000" />
								<p class="nebula-help-text short-help form-text text-muted">This conversion ID is used to enable the Google AdWords remarketing tag.</p>
								<p class="option-keywords">remote resource minor page speed impact</p>
							</div>

							<div class="form-group">
								<label for="example_option">Google Optimize ID</label>
								<input type="text" id="example_option" class="form-control" placeholder="GTM-0000000000" />
								<p class="nebula-help-text short-help form-text text-muted">The ID used by <a href="https://optimize.google.com/optimize/home/" target="_blank">Google Optimize</a> to enable tests.</p>
								<p class="nebula-help-text more-help form-text text-muted">Entering the ID here will enable both the Google Analytics require tag and the style tag hiding snippet in the head.</p>
								<p class="option-keywords">remote resource minor page speed impact</p>
							</div>

							<div class="form-group">
								<label for="example_option">Valid Hostnames</label>
								<input type="text" id="example_option" class="form-control" />
								<p class="nebula-help-text short-help form-text text-muted">These help generate regex patterns for Google Analytics filters.</p>
								<p class="nebula-help-text more-help form-text text-muted">It is also used for the is_site_live() function! Enter a comma-separated list of all valid hostnames, and domains (including vanity domains) that are associated with this website. Enter only domain and TLD (no subdomains). The wildcard subdomain regex is added automatically. Add only domains you <strong>explicitly use your Tracking ID on</strong> (Do not include google.com, google.fr, mozilla.org, etc.)! Always test the following RegEx on a Segment before creating a Filter (and always have an unfiltered View)! Include this RegEx pattern for a filter/segment <a href="https://gearside.com/nebula/utilities/domain-regex-generator/?utm_campaign=documentation&utm_medium=options&utm_source=valid+hostnames%20help" target="_blank">(Learn how to use this)</a>: <input type="text" value="<?php //echo nebula()->valid_hostname_regex(); //@todo: uncomment ?>" readonly style="width: 50%;" /></p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<label for="example_option">Google Search Console Verification</label>
								<input type="text" id="example_option" class="form-control" placeholder="AAAAAA..." />
								<p class="nebula-help-text short-help form-text text-muted">This is the code provided using the "HTML Tag" option from <a href="https://www.google.com/webmasters/verification/" target="_blank">Google Search Console</a>.</p>
								<p class="nebula-help-text more-help form-text text-muted">Only use the "content" code- not the entire meta tag. Go ahead and paste the entire tag in, the value should be fixed automatically for you!</p>
								<p class="option-keywords">recommended seo</p>
							</div>

							<div class="form-group">
								<label for="example_option">Facebook Custom Audience Pixel ID</label>
								<input type="text" id="example_option" class="form-control" placeholder="0000000000000" />
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
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks the article author's name on single posts. Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Categories</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks the article author's name on single posts. Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Tags</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends a string of all the post's tags to the pageview hit. Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Word Count</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends word count range for single posts. Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Publish Year</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends the year the post was published. Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Scroll Depth</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Information tied to the event such as "Scanner" or "Reader". Scope: Hit</p>
									<p class="nebula-help-text more-help form-text text-muted">This dimension is tied to events, so pageviews will not have data (use the Top Event report).</p>
									<p class="option-keywords"></p>
								</div>
							</div><!-- /sub-group -->

							<div class="option-sub-group">
								<h4>Business Data</h4>

								<div class="form-group" dependent-or="switch4 switch5 switch6 switch7 switch8 switch9 switch10">
									<div class="input-group">
										<div class="input-group-addon">Business Hours</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="dependent-note hidden">This option is dependent on Business Hours (Metadata tab).</p>
									<p class="nebula-help-text short-help form-text text-muted">Passes "During Business Hours", or "Non-Business Hours". Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Relative Time</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends the relative time (Ex: "Late Morning", "Early Evening", etc.) based on the business timezone (via WordPress settings). Scope: Hit</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group" dependent-of="switch19">
									<div class="input-group">
										<div class="input-group-addon">Weather</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="dependent-note hidden">This option is dependent on Weather Detection (Functions tab) being enabled.</p>
									<p class="nebula-help-text short-help form-text text-muted">Sends the current weather conditions (at the business location) as a dimension. Scope: Hit</p>
									<p class="option-keywords">location</p>
								</div>

								<div class="form-group" dependent-of="switch19">
									<div class="input-group">
										<div class="input-group-addon">Temperature</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
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
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends the current user's role (as well as staff affiliation if available) for associated users. Scope: User</p>
									<p class="nebula-help-text more-help form-text text-muted">Session ID does contain this information, but this is explicitly more human readable (and scoped to the user).</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Session ID</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">ID system so that you can group hits into specific user sessions. Scope: Session</p>
									<p class="nebula-help-text more-help form-text text-muted">This ID is not personally identifiable and therefore fits within the <a href="https://support.google.com/analytics/answer/2795983" target="_blank">Google Analytics ToS</a> for PII. <a href="https://gearside.com/nebula/functions/nebula_session_id/?utm_campaign=documentation&utm_medium=options&utm_source=session+id%20help" target="_blank">Session ID Documentation &raquo;</a></p>
									<p class="option-keywords">recommended</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">User ID</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">If allowing visitors to sign up to create WordPress accounts, this will send user IDs to Google Analytics. Scope: User</p>
									<p class="nebula-help-text more-help form-text text-muted">User IDs are also passed in the Session ID, but this scope is tied more specifically to the user (it can often capture data even when they are not currently logged in).</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Facebook ID</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Send Facebook ID to Google Analytics when using Facebook Connect API. Scope: User</p>
									<p class="nebula-help-text more-help form-text text-muted">Add the ID to this URL to view it: <code>https://www.facebook.com/app_scoped_user_id/</code></p>
									<p class="option-keywords">social</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Local Timestamp</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Adds a timestamp (in the user's local time) with timezone offset. Scope: Hit</p>
									<p class="nebula-help-text more-help form-text text-muted">Ex: "1449332547 (2015/12/05 11:22:26.886 UTC-05:00)". Can be compared to the server time stored in the Session ID.</p>
									<p class="option-keywords">location recommended</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">First Interaction</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Stores a timestamp for the first time the user visited the site. Scope: User</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Window Type</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Stores the type of window the site is being accessed from (Ex: Iframe or Standalone App). Scope: Hit</p>
									<p class="nebula-help-text more-help form-text text-muted">This only records alternate window types (non-standard browser windows).</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Geolocation</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Allows latitude and longitude coordinates to be sent after being detected. Scope: Session</p>
									<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work! </p>
									<p class="option-keywords">location</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Geolocation Accuracy</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Allows geolocation accuracy to be sent after being detected. Scope: Session</p>
									<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work!</p>
									<p class="option-keywords">location</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Geolocation Name</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Allows named location information to be sent after being detected using map polygons. Scope: Session</p>
									<p class="nebula-help-text more-help form-text text-muted">Additional code is required for this to work!</p>
									<p class="option-keywords">location</p>
								</div>

								<div class="form-group" dependent-of="switch17">
									<div class="input-group">
										<div class="input-group-addon">Ad Blocker</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="dependent-note hidden">This option is dependent on Ad Block Detection being enabled.</p>
									<p class="nebula-help-text short-help form-text text-muted">Detects if the user is blocking ads. Scope: Session</p>
									<p class="nebula-help-text more-help form-text text-muted">This can be used even if not intending to serve ads on this site. It is important that this dimension is not set to the "hit" scope.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Media Query: Breakpoint</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Detect which media query breakpoint is associated with this hit. Scope: Hit</p>
									<p class="option-keywords">autotrack</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Media Query: Resolution</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Detect the resolution factor associated with this hit. Scope: Hit</p>
									<p class="option-keywords">autotrack</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Media Query: Orientation</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Detect the device orientation associated with this hit. Scope: Hit</p>
									<p class="option-keywords">autotrack</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Notable POI</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
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
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Log whether the event was true, or just a possible intention. Scope: Hit</p>
									<p class="option-keywords">recommended</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Contact Method</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">If the user triggers a contact event, the method of contact is stored here. Scope: Session</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Form Timing</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends form timings along with the each submission. Scope: Hit</p>
									<p class="nebula-help-text more-help form-text text-muted">Timings are automatically sent to Google Analytics in Nebula, but are sampled in the User Timings report. Data will be in milliseconds.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Video Watcher</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sets a dimension when videos are started and finished. Scope: Session</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Ecommerce Cart</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">If the user has any product(s) in their cart. Scope: Hit</p>
									<p class="option-keywords">ecommerce woocommerce</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Ecommerce Customer</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $dimension_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sets a dimension when a user completes the checkout process in WooCommerce. Scope: User</p>
									<p class="nebula-help-text more-help form-text text-muted">Appears in Google Analytics as "Order Received".</p>
									<p class="option-keywords">ecommerce woocommerce</p>
								</div>
							</div><!-- /sub-group -->

							<?php //do_action('nebula_options_interface_custom_dimensions'); //@todo: uncomment this ?>
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
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a user downloads a notable file. Scope: Hit, Format: Integer</p>
									<p class="nebula-help-text short-help form-text text-muted">To use, add the class "notable" to either the or its parent.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Form Page Views</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a user views a page containing a form. Scope: Hit, Format: Integer</p>
									<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Form Impressions</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a form is in view as the user scrolls. Scope: Hit, Format: Integer</p>
									<p class="nebula-help-text more-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Form Starts</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a user begins entering a form. Scope: Hit, Format: Integer</p>
									<p class="nebula-help-text short-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Form Submissions</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a user submits a form. Scope: Hit, Format: Integer</p>
									<p class="nebula-help-text short-help form-text text-muted">To ignore a form, add the class "ignore-form" to the form, somewhere inside it, or to a parent element.</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Engaged Readers</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Counts when a user has completed reading an article (and is not determined to be a "scanner"). Scope: Hit, Format: Integer</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Max Scroll Percent</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
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
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a user begins playing a video. Scope: Hit, Format: Integer</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Video Play Time</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks playing duration when a user pauses or completes a video. Scope: Hit, Format: Time</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Video Completions</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
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
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Sends word count for single posts. Scope: Hit, Format: Integer</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Autocomplete Searches</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a set of autocomplete search results is returned to the user (count is the search, not the result quantity). Scope: Hit, Format: Integer</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Autocomplete Search Clicks</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">Tracks when a user clicks an autocomplete search result. Scope: Hit, Format: Integer</p>
									<p class="option-keywords"></p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Page Visible</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The amount of time (in seconds) the page was in the visible state (tab/window visible) Scope: Hit, Format: Time</p>
									<p class="option-keywords">autotrack</p>
								</div>

								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Page Hidden</div>
										<input type="text" id="inlineFormInputGroup2" class="form-control nebula-validate-regex" data-valid-regex="<?php echo $metric_regex; ?>" />
									</div>
									<p class="nebula-help-text short-help form-text text-muted">The amount of time (in seconds) the page was in the hidden state (tab/window not visible) Scope: Hit, Format: Time</p>
									<p class="option-keywords">autotrack</p>
								</div>
							</div><!-- /sub-group -->

							<?php //do_action('nebula_options_interface_custom_metrics'); //@todo: uncomment this ?>
						</div><!-- /option-group -->

						<?php //do_action('nebula_options_interface_analytics'); //@todo: uncomment this ?>
					</div><!--/col-->
				</div><!--/row-->
				<div class="row save-row">
					<div class="col-xl-8">
						<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button -->
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
							<label for="example_option">Google Font</label>
							<input type="text" id="example_option" class="form-control" placeholder="https://fonts.googleapis.com/css?family=Open+Sans:400,800" />
							<p class="nebula-help-text short-help form-text text-muted">Choose which <a href="https://www.google.com/fonts" target="_blank">Google Font</a> is used by default for this site by pasting the entire font URL.</p>
							<p class="nebula-help-text more-help form-text text-muted">The default font uses the native system font of the user's device.</p>
							<p class="option-keywords">remote resource minor page speed impact</p>
						</div>

						<div class="form-group">
							<label for="inlineFormInputGroup">Google Public API</label>

							<div class="input-group mb-2">
								<div class="input-group-addon">Browser Key</div>
								<input type="text" id="inlineFormInputGroup" class="form-control" placeholder="AAAAAAA...">
							</div>

							<div class="input-group">
								<div class="input-group-addon">Server Key</div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAA...">
							</div>

							<p class="nebula-help-text short-help form-text text-muted">API keys from the <a href="https://console.developers.google.com/project" target="_blank">Google Developers Console</a>.</p>
							<p class="nebula-help-text more-help form-text text-muted">In the Developers Console make a new project (if you don't have one yet). Under "Credentials" create a new key. Your current server IP address is 108.179.243.152 (for server key whitelisting). Do not use the Server Key in JavaScript or any client-side code!</p>
							<p class="option-keywords"></p>
						</div>

						<div class="form-group">
							<label for="example_option">Google Custom Search Engine</label>
							<div class="input-group">
								<div class="input-group-addon">Engine ID</div>
								<input type="text" id="inlineFormInputGroup" class="form-control" placeholder="AAAAAAA...">
							</div>
							<p class="nebula-help-text short-help form-text text-muted">For <a href="https://gearside.com/nebula/functions/pagesuggestion/?utm_campaign=documentation&utm_medium=options&utm_source=gcse+help" target="_blank">page suggestions</a> on 404 and No Search Results pages.</p>
							<p class="nebula-help-text more-help form-text text-muted"><a href="https://www.google.com/cse/manage/all">Register here</a>, then select "Add", input your website's URL in "Sites to Search". Then click the one you just made and click the "Search Engine ID" button.</p>
							<p class="option-keywords">remote resource minor page speed impact</p>
						</div>

						<div class="form-group hidden">
							<label for="example_option">Google Cloud Messaging Sender ID</label>
							<input type="text" id="example_option" class="form-control" />
							<p class="nebula-help-text short-help form-text text-muted">The Google Cloud Messaging (GCM) Sender ID from the <a href="https://console.developers.google.com/project" target="_blank">Developers Console</a>.</p>
							<p class="nebula-help-text more-help form-text text-muted">This is the "Project number" within the project box on the Dashboard. Do not include parenthesis or the "#" symbol. This is used for push notifications. <strong>*Note: This feature is still in development and not currently active!</strong></p>
							<p class="option-keywords"></p>
						</div>
					</div><!--/col-->
					<div class="col-xl-8">
						<div class="form-group">
							<label for="inlineFormInputGroup">Hubspot</label>

							<div class="input-group mb-2">
								<div class="input-group-addon">API Key</div>
								<input type="text" id="inlineFormInputGroup" class="form-control" placeholder="AAAAAAA...">
							</div>

							<div class="input-group">
								<div class="input-group-addon">Portal ID</div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAA...">
							</div>

							<p class="nebula-help-text short-help form-text text-muted">Enter your Hubspot API key here.</p>
							<p class="nebula-help-text more-help form-text text-muted">It can be obtained from your <a href="https://app.hubspot.com/hapikeys">API Keys page under Integrations in your account</a>. Your Hubspot Portal ID (or Hub ID) is located in the upper right of your account screen.</p>
							<p class="option-keywords">remote resource major page speed impact crm</p>
						</div>

						<div class="form-group">
							<label for="example_option">Disqus Shortname</label>
							<input type="text" id="example_option" class="form-control" />
							<p class="nebula-help-text short-help form-text text-muted">Enter your Disqus shortname here.</p>
							<p class="nebula-help-text more-help form-text text-muted"><a href="https://disqus.com/admin/create/" target="_blank">Sign-up for an account here</a>. In your Disqus account settings (where you will find your shortname), please uncheck the "Discovery" box.</p>
							<p class="option-keywords">social remote resource moderate page speed impact comments</p>
						</div>

						<div class="form-group">
							<label for="example_option">Facebook</label>
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> App ID</span></div>
								<input type="text" id="example_option" class="form-control" placeholder="0000000000">
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The App ID of the associated Facebook page/app.</p>
							<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> App Secret</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAAA..." />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The App Secret of the associated Facebook page/app.</p>
							<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-facebook"></i> Access Token</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAA..." />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The Access Token of the associated Facebook page/app.</p>
							<p class="nebula-help-text more-help form-text text-muted">This is used to query the Facebook Graph API. <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">Get a Facebook App ID &amp; Access Token &raquo;</a></p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<label for="example_option">Twitter</label>
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Consumer Key</span></div>
								<input type="text" id="example_option" class="form-control" placeholder="0000000000">
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The Consumer Key key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Consumer Secret</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="00000000000" />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The Consumer Secret key is used for generating a bearer token and/or accessing custom Twitter feeds.</p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-twitter"></i> Bearer Token</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAA..." />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The bearer token is for creating custom Twitter feeds: <a href="https://gearside.com/nebula/utilities/twitter-bearer-token-generator/?utm_campaign=documentation&utm_medium=options&utm_source=twitter+help" target="_blank">Generate a bearer token here</a></p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<label for="example_option">Instagram</label>
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> User ID</span></div>
								<input type="text" id="example_option" class="form-control" placeholder="0000000000">
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
							<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> Access Token</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="00000000000" />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">The user ID and access token are used for creating custom Instagram feeds.</p>
							<p class="nebula-help-text more-help form-text text-muted">Here are instructions for finding your User ID, or generating your access token. This tool can retrieve both at once by connecting to your Instagram account.</p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> Client ID</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAA..." />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
							<p class="option-keywords">social remote resource</p>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span><i class="fa fa-fw fa-instagram"></i> Client Secret</span></div>
								<input type="text" id="inlineFormInputGroup2" class="form-control" placeholder="AAAAAA..." />
							</div>
							<p class="nebula-help-text short-help form-text text-muted">For client ID and client secret, register an application using the Instagram API platform then Register a new Client.</p>
							<p class="option-keywords">social remote resource</p>
						</div>
					</div><!--/col-->

					<?php //do_action('nebula_options_interface_apis'); //@todo: uncomment this ?>
				</div><!--/row-->
				<div class="row save-row">
					<div class="col-xl-8">
						<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button -->
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

							<div class="form-group">
								<label for="developer_ips">Developer IPs</label>
								<input type="text" id="developer_ips" class="form-control" placeholder="72.230.114.133" />
								<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the developer to enable specific console logs and other dev info.<br/>Your current IP address is <code><?php echo $_SERVER['REMOTE_ADDR']; ?></code></p>
								<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/192\.168\./i</code></p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group">
								<label for="developer_email_domains">Developer Email Domains</label>
								<input type="text" id="developer_email_domains" class="form-control" placeholder="gearside.com" />
								<p class="nebula-help-text short-help form-text text-muted">Comma separated domains of the developer emails (without the "@") to enable specific console logs and other dev info.<br/>Your email domain is: <code><?php echo $current_user_domain; ?></code></p>
								<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/@pinckneyhugo\./i</code></p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group">
								<label for="example_option">Client IPs</label>
								<input type="text" id="example_option" class="form-control" placeholder="72.230.114.133" />
								<p class="nebula-help-text short-help form-text text-muted">Comma-separated IP addresses of the client to enable certain features.<br/>Your current IP address is <code><?php echo $_SERVER['REMOTE_ADDR']; ?></code></p>
								<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/192\.168\./i</code></p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group">
								<label for="example_option">Client Email Domains</label>
								<input type="text" id="example_option" class="form-control" placeholder="gearside.com" />
								<p class="nebula-help-text short-help form-text text-muted">Comma separated domains of the developer emails (without the "@") to enable certain features.<br/>Your email domain is: <code><?php echo $current_user_domain; ?></code></p>
								<p class="nebula-help-text more-help form-text text-muted">RegEx may also be used here. Ex: <code>/@pinckneyhugo\./i</code></p>
								<p class="option-keywords">recommended</p>
							</div>

							<div class="form-group">
								<label for="example_option">Notable IPs</label>
								<textarea id="exampleTextarea" class="form-control" rows="4"></textarea>
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
								<label for="example_option">Server Control Panel</label>
								<input type="text" id="example_option" class="form-control nebula-validate-url" placeholder="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" />
								<p class="nebula-help-text short-help form-text text-muted">Link to the control panel of the hosting account.</p>
								<p class="nebula-help-text more-help form-text text-muted">cPanel on this domain would be: <a href="<?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082" target="_blank"><?php echo $serverProtocol . $_SERVER['SERVER_NAME']; ?>:2082</a></p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<label for="example_option">Hosting</label>
								<input type="text" id="example_option" class="form-control nebula-validate-url" placeholder="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>/" />
								<p class="nebula-help-text short-help form-text text-muted">Link to the server host for easy access to support and other information.</p>
								<p class="nebula-help-text more-help form-text text-muted">Server detected as <a href="http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?>" target="_blank">http://<?php echo $hostURL[1] . '.' . $hostURL[2]; ?></a></p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<label for="example_option">Domain Registrar</label>
								<input type="text" id="example_option" class="form-control nebula-validate-url" />
								<p class="nebula-help-text short-help form-text text-muted">Link to the domain registrar used for access to pointers, forwarding, and other information.</p>
								<p class="option-keywords"></p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch45" /><label for="switch18">Google AdSense</label>
								<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://www.google.com/adsense/" target="_blank">Google AdSense</a> account. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch46" /><label for="switch18">Amazon Associates</label>
								<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://affiliate-program.amazon.com/home" target="_blank">Amazon Associates</a> account. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<input type="checkbox" id="switch46" /><label for="switch18">Mention</label>
								<p class="nebula-help-text short-help form-text text-muted">Dashboard reference link to this project's <a href="https://mention.com/" target="_blank">Mention</a> account. (Default: Disabled)</p>
								<p class="nebula-help-text more-help form-text text-muted"><strong>This is only a dashboard link!</strong> It does nothing beyond add a convenient link on the dashboard.</p>
								<p class="option-keywords">discretionary</p>
							</div>

							<div class="form-group">
								<label for="example_option">Notes</label>
								<textarea id="exampleTextarea" class="form-control" rows="4"></textarea>
								<p class="nebula-help-text short-help form-text text-muted">This area can be used to keep notes. It is not used anywhere on the front-end.</p>
								<p class="option-keywords"></p>
							</div>
						</div>

						<?php //do_action('nebula_options_interface_administration'); //@todo: uncomment this ?>
					</div><!--/col-->
				</div><!--/row-->
				<div class="row save-row">
					<div class="col-xl-8">
						<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button --><?php //submit_button(); ?>
					</div><!--/col-->
				</div><!--/row-->
			</div><!-- /tab-pane -->




			<?php //do_action('nebula_options_interface_additional_panes'); //@todo: uncomment this ?>





			<?php //if ( current_user_can('manage_options') ): //@todo: uncomment this ?>
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
									<label for="example_option">Initialization Date</label>
									<input type="text" id="example_option" class="form-control" readonly />
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
									<label for="example_option">Nebula Options Saved Yet?</label>
									<input type="text" class="form-control" value="<?php echo ( $nebula_options['edited_yet'] )? 'Yes' : 'No'; ?>" readonly />
									<input type="text" id="example_option" class="form-control hidden" value="true" readonly />
									<p class="nebula-help-text short-help form-text text-muted"></p>
									<p class="option-keywords">readonly</p>
								</div>

								<div class="form-group">
									<label for="example_option">Current Nebula Version Number</label>
									<input type="text" id="example_option" class="form-control" readonly />
									<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version number when it was last saved. It should match: <strong><?php //echo nebula()->version('raw'); //@todo: uncomment ?></strong></p>
									<p class="option-keywords">readonly</p>
								</div>

								<div class="form-group">
									<label for="example_option">Last Nebula Version Date</label>
									<input type="text" id="example_option" class="form-control" readonly />
									<p class="nebula-help-text short-help form-text text-muted">This is the Nebula version date when it was last saved. It should match: <strong><?php //echo nebula()->version('date'); //@todo: uncomment ?></strong></p>
									<p class="option-keywords">readonly</p>
								</div>

								<div class="form-group">
									<label for="example_option">Legacy Nebula Version?</label>
									<input type="text" id="example_option" class="form-control" readonly />
									<p class="nebula-help-text short-help form-text text-muted">If a future version is deemed incompatible with previous versions, this will become true, and theme update checks will be disabled.</p>
									<p class="nebula-help-text more-help form-text text-muted">Incompatible versions are labeled with a "u" at the end of the version number.</p>
									<p class="option-keywords">readonly</p>
								</div>

								<div class="form-group">
									<label for="example_option">Latest Github Version</label>
									<input type="text" id="example_option" class="form-control" readonly />
									<p class="nebula-help-text short-help form-text text-muted">The latest version available on Github.</p>
									<p class="nebula-help-text more-help form-text text-muted">Re-checks with <a href="/update-core.php">theme update check</a> only when Nebula Child is activated.</p>
									<p class="option-keywords">readonly</p>
								</div>

								<div class="form-group">
									<label for="example_option">Online Users</label>
									<input type="text" id="example_option" class="form-control" readonly />
									<p class="nebula-help-text short-help form-text text-muted">Currently online and last seen times of logged in users.</p>
									<p class="nebula-help-text more-help form-text text-muted">A value of 1 or greater indicates it is working.</p>
									<p class="option-keywords">readonly</p>
								</div>
							</div>

							<?php //do_action('nebula_options_interface_diagnostic'); //@todo: uncomment this ?>
						</div><!--/col-->
					</div><!--/row-->
					<div class="row save-row">
						<div class="col-xl-8">
							<a class="btn btn-primary" href="#">Save Changes</a><!-- @todo: replace with wordpress button -->
						</div><!--/col-->
					</div><!--/row-->
				</div><!-- /tab-pane -->
			<?php //endif; //uncomment this ?>

		</div><!--/col-->
	</div><!--/row-->
</div><!--/container-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script>window.Tether = function(){};</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>

<script>
	jQuery(function(){
		checkWindowHeightForStickyNav();
		nebulaLiveValidator();

		//If there are no active tabs on load (like if wrong ?tab= parameter was used)
		if ( !jQuery('#options-navigation li a.active').length ){
			jQuery('#options-navigation').find('li:first-child a').addClass('active');
			jQuery('#nebula-options-section').find('.tab-pane:first-child').addClass('active');
		}

		//Scroll to the top when changing tabs
		jQuery('a.nav-link').on('shown.bs.tab', function(){
			jQuery('html, body').animate({
				scrollTop: jQuery('#nebula-options-section').offset().top
			}, 500);
		});

		<?php if ( $direct_option ): ?>
			if ( jQuery('#<?php echo $direct_option; ?>').length ){
				jQuery('#<?php echo $direct_option; ?>').closest('.form-group').addClass('highlight'); //@todo: change to JS get() for &option= parameter?
				jQuery('html, body').animate({
					scrollTop: jQuery('#<?php echo $direct_option; ?>').offset().top-35
				}, 500);
			}
		<?php endif; ?>

		jQuery('#nebula-option-filter').trigger('keyup').focus(); //Trigger if a ?filter= parameter is used.

		checkDependents(); //Check all dependents
		checkImportants();
		jQuery('input').on('keyup change', function(){
			checkDependents(jQuery(this));
			checkImportants();
		});

		jQuery('.short-help').each(function(){
			//Direct Link icons
			var thisTab = jQuery(this).closest('.tab-pane').attr('id');
			var thisOption = jQuery(this).closest('.form-group').find('.form-control').attr('id');
			jQuery(this).append('<a class="direct-link" href="<?php echo strstr($_SERVER['REQUEST_URI'], '?', true) ?: $_SERVER['REQUEST_URI']; //@todo: replace this with wordpress get page url (without query strings) ?>?tab=' + thisTab + '&option=' + thisOption + '" title="Link to this option"><i class="fa fa-fw fa-link"></i></a>');

			//More Help expander icons
			if ( jQuery(this).parent().find('.more-help').length ){
				jQuery(this).append('<a class="toggle-more-help" href="#" title="Show more information"><i class="fa fa-fw fa-question-circle"></i></a>');
			}
		});

		jQuery(document).on('click touch tap', '.toggle-more-help', function(){
			jQuery(this).closest('.form-group').find('.more-help').slideToggle();
			return false;
		});
	});

	jQuery(window).resize(function() {
		checkWindowHeightForStickyNav();
	});

	//Make sure the sticky nav is shorter than the viewport height.
	function checkWindowHeightForStickyNav(){
		if ( window.innerHeight > jQuery('#stickynav').outerHeight() ){
			jQuery('#stickynav').addClass('sticky');
		} else {
			jQuery('#stickynav').removeClass('sticky');
		}
	}

	function checkImportants(){
		jQuery('.important-option').each(function(){
			if ( !isCheckedOrHasValue(jQuery(this).find('input')) ){
				if ( !jQuery(this).find('.important-warning').length ){ //If the warning isn't already showing
					jQuery(this).addClass('important-empty').find('label').append('<p class="important-warning">It is highly recommended this option is used!</p>');
				}
			} else {
				jQuery(this).removeClass('important-empty');
				jQuery(this).find('.important-warning').remove();
			}
		});

		jQuery('.tab-pane').each(function(){
			if ( jQuery(this).find('.important-empty').length ){
				if ( !jQuery('.nav-link[href$=' + jQuery(this).attr('id') + '] .empty-important-tab-warn').length ){ //If the warning isn't already showing
					jQuery('.nav-link[href$=' + jQuery(this).attr('id') + ']').append('<i class="fa fa-fw fa-exclamation-triangle empty-important-tab-warn"></i>');
				}
			} else {
				jQuery('.nav-link[href$=' + jQuery(this).attr('id') + ']').find('.empty-important-tab-warn').remove();
			}
		});
	}

	//Use the attribute dependent-of="" with the id of the dependent checkbox
	function checkDependents(inputObject){
		if ( inputObject ){ //Check a single option's dependents
			if ( isCheckedOrHasValue(inputObject) ){
				jQuery('[dependent-of=' + inputObject.attr('id') + ']').removeClass('inactive').find('.dependent-note').addClass('hidden');
				jQuery('[dependent-or~=' + inputObject.attr('id') + ']').removeClass('inactive').find('.dependent-note').addClass('hidden');

				//The dependent-and attribute must have ALL checked
				jQuery('[dependent-and~=' + inputObject.attr('id') + ']').each(function(){
					var oThis = jQuery(this);
					var dependentOrs = jQuery(this).attr('dependent-and').split(' ');
					var totalDependents = dependentAnds.length;
					var dependentsChecked = 0;
					jQuery.each(dependentAnds, function(){
						if ( isCheckedOrHasValue(jQuery('#' + this)) ){
							dependentsChecked++;
						}
					});

					if ( dependentsChecked == totalDependents ){
						oThis.removeClass('inactive').find('.dependent-note').addClass('hidden');
					}
				});
			} else {
				jQuery('[dependent-of=' + inputObject.attr('id') + ']').addClass('inactive').find('.dependent-note').removeClass('hidden');
				jQuery('[dependent-and~=' + inputObject.attr('id') + ']').addClass('inactive').find('.dependent-note').removeClass('hidden');

				//The dependent-or attribute can have ANY checked
				jQuery('[dependent-or~=' + inputObject.attr('id') + ']').each(function(){
					var oThis = jQuery(this);
					var dependentOrs = jQuery(this).attr('dependent-or').split(' ');
					var totalDependents = dependentOrs.length;
					var dependentsUnchecked = 0;
					jQuery.each(dependentOrs, function(){
						if ( !isCheckedOrHasValue(jQuery('#' + this)) ){
							dependentsUnchecked++;
						}
					});

					if ( dependentsUnchecked == totalDependents ){
						oThis.addClass('inactive').find('.dependent-note').removeClass('hidden');
					}
				});
			}
		} else { //Check all dependencies
			jQuery('input').each(function(){
				checkDependents(jQuery(this));
			});
		}
	}




	function isCheckedOrHasValue(inputObject){
		if ( inputObject.is('[type=checkbox]:checked') ){
			return true;
		}

		if ( !inputObject.is('[type=checkbox]') && inputObject.val().length > 0 ){
			return true;
		}

		return false;
	}



	//Option filter
	jQuery('#nebula-option-filter').on('keyup change focus blur', function(){
		if ( jQuery(this).val().length > 0 ){
			jQuery('#reset-filter').removeClass('hidden');

			jQuery('#options-navigation').addClass('inactive').find('li a.active').removeClass('active');

			jQuery('.tab-pane').addClass('active');

			keywordSearch('#nebula-options-section', '.form-group', jQuery(this).val());

			jQuery('.option-group, .option-sub-group').each(function(){
				if ( jQuery(this).find('.form-group:not(.filtereditem)').length > 0 ){
					jQuery(this).removeClass('filtereditem');
				} else {
					jQuery(this).addClass('filtereditem');
				}
			});

			jQuery('#nebula-options-section div[class^=col]').each(function(){
				if ( !jQuery(this).parents('.title-row, .save-row, .non-filter').length ){
					if ( jQuery(this).find('.form-group:not(.filtereditem)').length > 0 ){
						jQuery(this).removeClass('filtereditem');
					} else {
						jQuery(this).addClass('filtereditem');
					}
				}
			});

			jQuery('.tab-pane').each(function(){
				if ( jQuery(this).find('.form-group:not(.filtereditem)').length > 0 ){
					jQuery(this).removeClass('filtereditem');
					jQuery(this).find('.title-row').removeClass('filtereditem');
				} else {
					jQuery(this).addClass('filtereditem');
					jQuery(this).find('.title-row').addClass('filtereditem');
				}
			});
		} else {
			jQuery('#reset-filter').addClass('hidden');

			jQuery('#options-navigation').removeClass('inactive');

			if ( !jQuery('#options-navigation li a.active').length ){
				jQuery('#options-navigation').find('li:first-child a').addClass('active');
			}

			jQuery('.filtereditem').removeClass('filtereditem');
		}
	});

	jQuery('#reset-filter a').on('click touch tap', function(){
		jQuery('#nebula-option-filter').val('').trigger('keyup');
		return false;
	});

	jQuery('#preset-filters a').on('click touch tap', function(){
		jQuery('#nebula-option-filter').val(jQuery(this).attr('filter-text')).trigger('keyup');
		return false;
	});









	//Remove these functions after moving to WP:

	//Regex Patterns
	//Test with: if ( regexPattern.email.test(jQuery('input').val()) ){ ... }
	window.regexPattern = {
		email: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, //From JS Lint: Expected ']' and instead saw '['.
		phone: /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/, //To allow letters, you'll need to convert them to their corresponding number before matching this RegEx.
		date: {
			mdy: /^((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))[.\/-](\d{4}|\d{2})$/,
			ymd: /^(\d{4}|\d{2})[.\/-]((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))$/,
		},
		hex: /^#?([a-f0-9]{6}|[a-f0-9]{3})$/,
		ip: /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
		url: /\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/,
	};


	function keywordSearch(container, parent, value, filteredClass){
		if ( !filteredClass ){
			var filteredClass = 'filtereditem';
		}
		jQuery(container).find("*:not(:Contains(" + value + "))").closest(parent).addClass(filteredClass);
		jQuery(container).find("*:Contains(" + value + ")").closest(parent).removeClass(filteredClass);
	}


	//Custom CSS expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
	//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: keywordSearch(container, parent, value);
	jQuery.expr[":"].Contains=function(e,n,t){return(e.textContent||e.innerText||"").toUpperCase().indexOf(t[3].toUpperCase())>=0};


	//Offset must be an integer
	function nebulaScrollTo(element, milliseconds, offset, onlyWhenBelow){
		if ( !offset ){
			var offset = 0; //Note: This selector should be the height of the fixed header, or a hard-coded offset.
		}

		//Call this function with a jQuery object to trigger scroll to an element (not just a selector string).
		if ( element ){
			var willScroll = true;
			if ( onlyWhenBelow ){
				var elementTop = element.offset().top-offset;
				var viewportTop = nebula.dom.document.scrollTop();
				if ( viewportTop-elementTop <= 0 ){
					willScroll = false;
				}
			}

			if ( willScroll ){
				if ( !milliseconds ){
					var milliseconds = 500;
				}

				jQuery('html, body').animate({
					scrollTop: element.offset().top-offset
				}, milliseconds, function(){
					//callback
				});
			}

			return false;
		}
	}

	function nebulaLiveValidator(){
		//Standard text inputs and select menus
		jQuery('.nebula-validate-text, .nebula-validate-select').on('keyup change blur', function(e){
			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( jQuery.trim(jQuery(this).val()).length ){
				applyValidationClasses(jQuery(this), 'success', false);
			} else {
				if ( e.type === 'keyup' ){
					applyValidationClasses(jQuery(this), 'warning', false);
				} else {
					applyValidationClasses(jQuery(this), 'danger', true);
				}
			}
		});

		//RegEx input
		jQuery('.nebula-validate-regex').on('keyup change blur', function(e){
			var pattern = new RegExp(jQuery(this).attr('data-valid-regex'));

			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( pattern.test(jQuery(this).val()) ){
				applyValidationClasses(jQuery(this), 'success', false);
			} else {
				if ( e.type === 'keyup' ){
					applyValidationClasses(jQuery(this), 'warning', false);
				} else {
					applyValidationClasses(jQuery(this), 'danger', true);
				}
			}
		});

		//URL inputs
		jQuery('.nebula-validate-url').on('keyup change blur', function(e){
			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( regexPattern.url.test(jQuery(this).val()) ){
				applyValidationClasses(jQuery(this), 'success', false);
			} else {
				if ( e.type === 'keyup' ){
					applyValidationClasses(jQuery(this), 'warning', false);
				} else {
					applyValidationClasses(jQuery(this), 'danger', true);
				}
			}
		});

		//Email address inputs
		jQuery('.nebula-validate-email').on('keyup change blur', function(e){
			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( regexPattern.email.test(jQuery(this).val()) ){
				applyValidationClasses(jQuery(this), 'success', false);
			} else {
				if ( e.type === 'keyup' ){
					applyValidationClasses(jQuery(this), 'warning', false);
				} else {
					applyValidationClasses(jQuery(this), 'danger', true);
				}
			}
		});

		//Phone number inputs
		jQuery('.nebula-validate-phone').on('keyup change blur', function(e){
			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( regexPattern.phone.test(jQuery(this).val()) ){
				applyValidationClasses(jQuery(this), 'success', false);
			} else {
				if ( e.type === 'keyup' ){
					applyValidationClasses(jQuery(this), 'warning', false);
				} else {
					applyValidationClasses(jQuery(this), 'danger', true);
				}
			}
		});

		//Date inputs
		jQuery('.nebula-validate-date').on('keyup change blur', function(e){
			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( regexPattern.date.mdy.test(jQuery(this).val()) ){ //Check for MM/DD/YYYY (and flexible variations)
				applyValidationClasses(jQuery(this), 'success', false);
			} else if ( regexPattern.date.ymd.test(jQuery(this).val()) ){ //Check for YYYY/MM/DD (and flexible variations)
				applyValidationClasses(jQuery(this), 'success', false);
			} else if ( strtotime(jQuery(this).val()) && strtotime(jQuery(this).val()) > -2208988800 ){ //Check for textual dates (after 1900) //@TODO "Nebula" 0: The JS version of strtotime() isn't the most accurate function...
				applyValidationClasses(jQuery(this), 'success', false);
			} else {
				applyValidationClasses(jQuery(this), 'danger', true);
			}
		});

		//Textarea
		jQuery('.nebula-validate-textarea').on('keyup change blur', function(e){
			if ( jQuery(this).val() === '' ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else if ( jQuery.trim(jQuery(this).val()).length ){
				if ( e.type === 'blur' ){
					applyValidationClasses(jQuery(this), 'success', false);
				} else {
					applyValidationClasses(jQuery(this), 'reset', false); //Remove green while focused (typing)
				}
			} else {
				if ( e.type === 'blur' ){
					applyValidationClasses(jQuery(this), 'danger', true);
				} else {
					applyValidationClasses(jQuery(this), 'reset', false); //Remove green while focused (typing)
				}
			}
		});

		//Checkbox and Radio
		jQuery('.nebula-validate-checkbox, .nebula-validate-radio').on('change blur', function(e){
			if ( jQuery(this).parents('.form-group').find('input:checked').length ){
				applyValidationClasses(jQuery(this), 'reset', false);
			} else {
				applyValidationClasses(jQuery(this), 'danger', true);
			}
		});
	}

	//Apply Bootstrap appropriate validation classes to appropriate elements
	function applyValidationClasses(element, validation, showFeedback){
		if ( typeof element === 'string' ){
			element = jQuery(element);
		} else if ( typeof element !== 'object' ) {
			return false;
		}

		if ( validation === 'success' || validation === 'valid' ){
			element.removeClass('form-control-success form-control-warning form-control-danger wpcf7-not-valid').addClass('form-control-success')
				.parents('.form-group').removeClass('has-success has-warning has-danger').addClass('has-success')
				.find('.wpcf7-not-valid-tip').remove();
		} else if ( validation === 'warning' ){
			element.removeClass('form-control-success form-control-warning form-control-danger wpcf7-not-valid').addClass('form-control-warning')
				.parents('.form-group').removeClass('has-success has-warning has-danger').addClass('has-warning')
				.find('.wpcf7-not-valid-tip').remove();
		} else if ( validation === 'danger' || validation === 'error' || validation === 'invalid' ){
			element.removeClass('form-control-success form-control-warning form-control-danger wpcf7-not-valid').addClass('form-control-danger')
				.parents('.form-group').removeClass('has-success has-warning has-danger').addClass('has-danger');
		} else if ( validation === 'reset' || validation === 'remove' ){
			element.removeClass('form-control-success form-control-warning form-control-danger wpcf7-not-valid')
				.parents('.form-group').removeClass('has-danger has-warning has-success')
				.find('.wpcf7-not-valid-tip').remove();
		}

		if ( validation === 'feedback' || showFeedback ){
			element.parents('.form-group').find('.form-control-feedback').removeClass('hidden');
		} else {
			element.parents('.form-group').find('.form-control-feedback').addClass('hidden');
		}
	}

</script>