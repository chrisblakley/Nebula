<?php
/**
 * Template Name: Example
 * @TODO "Templates" 5: Delete this file before launching the site!
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<p><?php the_field('description'); ?></p>
			</div><!--/cols-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div id="breadcrumb-section" class="full">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php nebula_breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/breadcrumb-section-->

<?php if ( is_page(680) ){ //Hero Carousel (Slider)
	include_once('includes/hero_carousel.php');
} ?>

<?php if ( is_page(1610) ){ //Hero Video
	include_once('includes/hero_video.php');
} ?>

<div id="content-section">
	<div id="example-container" class="container">
		<div class="row">
			<div class="col-md-8">
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<?php if ( get_field('example_filename') ): ?>
						<div class="example-filename nebulashadow anchored-left">
							<i class="fa fa-github"></i> Example Location: <a href="https://github.com/chrisblakley/Nebula/tree/master/Nebula-Child/examples/includes/<?php echo get_field('example_filename'); ?>" target="_blank" title="View the exact code snippet rendering this example.">/examples/includes/<?php echo get_field('example_filename'); ?></a><br />
							<i class="fa fa-code"></i> Example Include: <code class="nebula-code" title="Copy/Paste this snippet to see this example on the Nebula implementation on your server.">&lt;?php include('examples/includes/<?php echo get_field('example_filename'); ?>'); ?&gt;</code>
						</div>
					<?php endif; ?>

					<div class="entry-content">
						<div class="row">
							<div class="col-md-12">
								<?php if ( get_field('usage') ): ?>
									<h2>Usage</h2>
									<?php echo do_shortcode(get_field('usage')); ?>
									<br />
								<?php endif; ?>

								<?php if ( get_field('parameters') ): ?>
									<h2>Parameters</h2>
									<p><?php echo do_shortcode(get_field('parameters')); ?></p>
								<?php endif; ?>

								<?php if ( get_field('example') ): ?>
									<h2>Example</h2>
									<?php echo do_shortcode(get_field('example')); ?>
									<br />
								<?php endif; ?>

								<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
									<?php the_content(); ?>
								<?php endwhile; ?>


								<?php if ( is_page(15) ): //Top-level Documentation Page ?>
									<style>
										ul.documentation-search-list {padding: 0;}
										li.documentation-search-item {list-style: none; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px dotted #ccc;}
											li.documentation-search-item strong {font-size: 14px;}
											li.documentation-search-item p {font-size: 12px; margin: 0;}
									</style>

									<script>
										jQuery(document).on('keyup', '.documentationsearch', function(){
											jQuery('.documentation-search-list li').find("*:not(:Contains(" + jQuery('.documentationsearch').val().trim() + "))").parents('li').addClass('hidden').removeClass('visible');
											jQuery('.documentation-search-list li').find("*:Contains(" + jQuery('.documentationsearch').val().trim() + ")").parents('li').removeClass('hidden').addClass('visible');

											debounce(function(){
												ga('send', 'event', 'Documentation Filter', 'Keyword', jQuery('.documentationsearch').val());
											}, 1000, 'documentation filter debounce');
										});
									</script>

									<div id="nebula-documentation-search-con">
										<strong><i class="fa fa-search"></i> Documentation Filter</strong>
										<div class="form-group">
											<input class="form-control documentationsearch" type="text" placeholder="Real-time filter" />
										</div>

										<ul class="documentation-search-list">
											<?php
												$nebula_documentation_search_query = new WP_Query(array(
													'post_type' => 'page',
													//Just get example template pages...
													'showposts' => -1,
													'orderby' => 'title',
													'order' => 'asc'
												));
												while ( $nebula_documentation_search_query->have_posts() ): $nebula_documentation_search_query->the_post();
											?>
												<li class="documentation-search-item">
													<p>
														<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong><br />
														<i class="fa fa-fw fa-tag" style="color: #888;"></i> <?php echo get_the_title(wp_get_post_parent_id(get_the_id())); ?> &nbsp;&nbsp; <?php echo ( get_field('example_filename') )? '<i class="fa fa-fw fa-file-text-o" style="color: #888;"></i> <a href="https://github.com/chrisblakley/Nebula/blob/master/examples/includes/' . get_field('example_filename') . '" target="_blank" title="View the exact code snippet rendering this example.">' . get_field('example_filename') . '</a>' : ''; ?><br/>
														<?php echo strip_tags(get_field('description'), '<a>'); ?>
													</p>
													<p class="documentation-search-item-keywords hidden">
														<span><?php echo strip_tags($post->post_content); ?></span>
														<span><?php echo strip_tags(get_field('usage')); ?></span>
														<span><?php echo strip_tags(get_field('parameters')); ?></span>
														<span><?php echo strip_tags(get_field('example')); ?></span>
														<span><?php echo get_field('keywords'); ?></span>
													</p>
												</lil>
											<?php endwhile ?>
											<?php wp_reset_query(); ?>
										</ul>
									</div>
								<?php endif; ?>
							</div><!--/cols-->
						</div><!--/row-->


						<?php if ( is_page(1408) ){ //Basic Wordpress Query
							include_once('includes/wp_query_basic.php');
						} ?>

						<?php if ( is_page(208) ){ //Multicolumn Wordpress Query
							include_once('includes/wp_query_multicolumn.php');
						} ?>

						<?php if ( is_page(434) ){ //Sticky Post
							include_once('includes/stick_post.php');
						} ?>

						<?php if ( is_page(263) ){ //Video Meta
							include_once('includes/video_meta.php');
						} ?>

						<?php if ( is_page(1366) ){ //Ooyala Player
							include_once('includes/ooyala_player.php');
						} ?>

						<?php if ( is_page(834) ){ //Ooyala Player Modal
							include_once('includes/ooyala_player_modal.php');
						} ?>

						<?php if ( is_page(791) ){ //GET()
							include_once('includes/get.php');
						} ?>

						<?php if ( is_page(829) ){ //Social Buttons
							include_once('includes/social_buttons.php');
						} ?>

						<?php if ( is_page(824) ){ //Weather Detection
							include_once('includes/weather_detection.php');
						} ?>

						<?php if ( is_page(815) ){ //Twitter Feed (OLD)
							include_once('includes/twitter_feed_old.php');
						} ?>

						<?php if ( is_page(1391) ){ //Twitter Bearer Token Generator (New)
							include_once('includes/twitter_bearer_token_generator.php');
						} ?>

						<?php if ( is_page(1394) ){ //Twitter Cached Feed (New)
							include_once('includes/twitter_cached_feed.php');
						} ?>

						<?php if ( is_page(722) ){ //Currently Open (Business Hours)
							include_once('includes/currently_open.php');
						} ?>

						<?php if ( is_page(214) ){ //Nebula Meta
							include_once('includes/nebula_meta.php');
						} ?>

						<?php if ( is_page(224) ){ //Nebula Excerpt
							include_once('includes/nebula_excerpt.php');
						} ?>

						<?php if ( is_page(648) ){ //Facebook Graph API
							include_once('includes/facebook_graph.php');
						} ?>

						<?php if ( is_page(258) ){ //CSS Browser Selector
							include_once('includes/css_browser_selector.php');
						} ?>

						<?php if ( is_page(582) ){ //IE Compatibility Mode Detection
							include_once('includes/ie_compatibility_mode_detection.php');
						} ?>

						<?php if ( is_page(277) ){ //Retina
							include_once('includes/retina.php');
						} ?>

						<?php if ( is_page(485) ){ //Picture Tag
							include_once('includes/picture.php');
						} ?>

						<?php if ( is_page(351) && 1==2 ){ //Slider (Native)
							include_once('includes/hero_slider_native.php');
						} ?>

						<?php if ( is_page(460) ){ //File Type Indicators
							include_once('includes/file_type_indicators.php');
						} ?>

						<?php if ( is_page(443) ){ //Nebula Shadows
							include_once('includes/nebula_shadows.php');
						} ?>

						<?php if ( is_page(430) ){ //Accordion Shortcode
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(432) ){ //Bio Shortcode
							include_once('includes/bio.php');
						} ?>

						<?php if ( is_page(359) ){ //CSS Level 4
							include_once('includes/css_level_4.php');
						} ?>

						<?php if ( is_page(497) ){ //CSS Blending Modes
							include_once('includes/css_blending_modes.php');
						} ?>

						<?php if ( is_page(436) ){ //Speech Synthesis
							include_once('includes/speech_synthesis.php');
						} ?>

						<?php if ( is_page(356) ){ //AJAX
							include_once('includes/ajax.php');
						} ?>

						<?php if ( is_page(703) ){ //AJAX Contact Form
							//include_once('includes/ajax_contact_form.php');
						} ?>

						<?php if ( is_page(588) ){ //nebula_tel_link and nebula_phone_format
							include_once('includes/nebula_tel_link.php');
						} ?>

						<?php if ( is_page(481) ){ //Video
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(614) ){ //CSS Position: Sticky
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(1074) ){ //HEX2RGB
							include_once('includes/hex2rgb.php');
						} ?>

						<?php if ( is_page(742) ){ //History API
							include_once('includes/history_api.php');
						} ?>

						<?php if ( is_page(737) ){ //Notification API
							//include_once('includes/notification_api.php');
							include_once('includes/push_notifications.php');
						} ?>

						<?php if ( is_page(760) ){ //Bootstrap Carousel (Slider)
							include_once('includes/bootstrap_carousel.php');
						} ?>

						<?php if ( is_page(785) ){ //Seamless Iframe
							include_once('includes/seamless_iframe.php');
						} ?>

						<?php if ( is_page(779) ){ //Google Analytics RealTime API
							include_once('includes/google_analytics_realtime_api.php');
						} ?>

						<?php if ( is_page(614) ){ //Device Orientation
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(614) ){ //Device Motion API
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(9999) ){ //Image Orientation
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(617) ){ //CSS Variables
							include_once('includes/css_variables.php');
						} ?>

						<?php if ( is_page(9999) ){ //Proximity API
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(643) ){ //Speech Recognition API
							include_once('includes/speech_recognition.php');
						} ?>

						<?php if ( is_page(624) ){ //CSS Feature Queries
							include_once('includes/css_feature_queries.php');
						} ?>

						<?php if ( is_page(9999) ){ //CSS Masks
							//@TODO "Nebula" 0: Coming Soon
						} ?>

						<?php if ( is_page(1015) ){ //Gumby Shuffle
							include_once('includes/gumby_shuffle.php');
						} ?>

						<?php if ( is_page(1025) ){ //Gumby Parallax
							include_once('includes/gumby_parallax.php');
						} ?>

						<?php if ( is_page(1028) ){ //Gumby InView
							include_once('includes/gumby_inview.php');
						} ?>

						<?php if ( is_page(1031) ){ //Gumby FitText
							include_once('includes/gumby_fittext.php');
						} ?>

						<?php if ( is_page(1042) ){ //Gumby Modal
							include_once('includes/gumby_modal.php');
						} ?>

						<?php if ( is_page(628) ){ //Page Visibility API
							include_once('includes/page_visibility.php');
						} ?>

						<?php if ( is_page(89) ){ //Google Maps Iframe
							include_once('includes/google_maps_iframe.php');
						} ?>

						<?php if ( is_page(267) ){ //Google Maps Javascript API v3
							include_once('includes/google_maps_js_api.php');
						} ?>

						<?php if ( is_page(397) ){ //Cookies
							include_once('includes/cookies_js.php');
						} ?>

						<?php if ( is_page(867) ){ //DataTables
							include_once('includes/datatables.php');
						} ?>

						<?php if ( is_page(873) ){ //Vibration API
							include_once('includes/vibration_api.php');
						} ?>

						<?php if ( is_page(943) ){ //Wireframing
							include_once('includes/wireframing.php');
						} ?>

						<?php if ( is_page(1105) ){ //Unsplash.it Image
							include_once('includes/unsplash_it.php');
						} ?>

						<?php if ( is_page(2094) ){ //Placehold.it Image
							include_once('includes/placehold_it.php');
						} ?>

						<?php if ( is_page(1079) ){ //Flash Banner Analytics
							include_once('includes/flash_banner_analytics.php');
						} ?>

						<?php if ( is_page(2407) ){ //HTML5 Banner Analytics
							include_once('includes/html5_banner_analytics.php');
						} ?>

						<?php if ( is_page(1113) ){ //Websockets API
							include_once('includes/websockets_api.php');
						} ?>

						<?php if ( is_page(1121) ){ //LocalStorage
							include_once('includes/localstorage.php');
						} ?>

						<?php if ( is_page(1129) ){ //Nebula URL Components
							include_once('includes/nebula_url_components.php');
						} ?>

						<?php if ( is_page(1203) ){ //Battery API
							include_once('includes/battery_api.php');
						} ?>

						<?php if ( is_page(1206) ){ //Network Information API
							include_once('includes/network_info_api.php');
						} ?>

						<?php if ( is_page(1209) ){ //Ambient Light Events
							include_once('includes/ambient_light_events.php');
						} ?>

						<?php if ( is_page(1227) ){ //Debounce
							include_once('includes/debounce.php');
						} ?>

						<?php if ( is_page(1280) ){ //WHOIS Info
							include_once('includes/whois_info.php');
						} ?>

						<?php if ( is_page(1494) ){ //Multiple Google Analytics
							include_once('includes/multiple_google_analytics.php');
						} ?>

						<?php if ( is_page(1517) ){ //Google Analytics No-JS Events
							include_once('includes/google_analytics_nojs_events.php');
						} ?>

						<?php if ( is_page(1515) ){ //Google Analytics Error Tracking
							include_once('includes/google_analytics_error_tracking.php');
						} ?>

						<?php if ( is_page(1523) ){ //Equalize Column Heights
							include_once('includes/equalize.php');
						} ?>

						<?php if ( is_page(1577) ){ //Chosen.js
							include_once('includes/chosen.php');
						} ?>

						<?php if ( is_page(1590) ){ //selectText()
							include_once('includes/selecttext.php');
						} ?>

						<?php if ( is_page(1601) ){ //getUserMedia API
							include_once('includes/getusermedia_api.php');
						} ?>

						<?php if ( is_page(1621) ){ //Nebula Upload Data
							include_once('includes/nebula_upload_data.php');
						} ?>

						<?php if ( is_page(1731) ){ //Transients API
							include_once('includes/transients.php');
						} ?>

						<?php if ( is_page(1807) ){ //Autocomplete Search
							include_once('includes/autocomplete_search.php');
						} ?>

						<?php if ( is_page(1786) ){ //Autocomplete Address
							include_once('includes/autocomplete_address.php');
						} ?>

						<?php if ( is_page(1989) ){ //Google Maps containsLocation()
							include_once('includes/google_maps_contains_location.php');
						} ?>

						<?php if ( is_page(2037) ){ //User Agent Parsing (Server-side Device Detection)
							include_once('includes/user_agent_parsing.php');
						} ?>

						<?php if ( is_page(2098) ){ //Is Available
							include_once('includes/nebula_is_available.php');
						} ?>

						<?php if ( is_page(2128) ){ //Nebula Timer
							include_once('includes/nebula_timer.php');
						} ?>

						<?php if ( is_page(2229) ){ //Nebula Retargeting
							include_once('includes/nebula_retargeting.php');
						} ?>

						<?php if ( is_page(2400) ){ //Push Notifications using Service Workers
							include_once('includes/push_notifications-serviceworkers.php');
						} ?>

						<?php if ( is_page(2467) ){ //Infinite load via AJAX
							include_once('includes/infinite_load.php');
						} ?>

						<?php if ( is_page(2537) ){ //EU Cookie Law
							include_once('includes/eu_cookie_law.php');
						} ?>

						<?php if ( is_page(2559) ){ //SVG <img> replacement
							include_once('includes/svg_img_replacement.php');
						} ?>

						<?php if ( is_page(2571) ){ //Animations
							include_once('includes/animations.php');
						} ?>

						<?php if ( is_page(2639) ){ //Context Menu
							include_once('includes/context_menu.php');
						} ?>

						<?php if ( is_page(2643) ){ //Instagram API
							include_once('includes/instagram_api.php');
						} ?>


						<?php
						/*==========================
						 Utilities
						 ===========================*/
						?>

						<?php if ( is_page(2404) ){ //Nebula Playground
							include_once('includes/playground.php');
						} ?>

						<?php if ( is_page(1139) ){ //WHOIS Tester
							include_once('includes/whois_tester.php');
						} ?>

						<?php if ( is_page(1186) ){ //Environment and Feature Detection
							include_once('includes/environment_feature_detection_new.php');
						} ?>

						<?php if ( is_page(1259) ){ //Google Campaign URL Builder
							include_once('includes/google_campaign_url_generator.php');
						} ?>

						<?php if ( is_page(1556) ){ //Domain Regex Generators
							include_once('includes/domain_regex_generators.php');
						} ?>

						<?php if ( is_page(1631) ){ //:Contains
							include_once('includes/contains.php');
						} ?>

						<?php if ( is_page(2004) ){ //Google Map Polygon Converter
							include_once('includes/google_polygon_array_converter.php');
						} ?>

						<?php if ( is_page(2109) ){ //Custom SASS Mixins and Functions
							include_once('includes/sass_mixins_functions.php');
						} ?>

						<?php if ( is_page(2150) ){ //Generate a custom Google Analytics utm.gif paramters
							include_once('includes/google_analytics_utm_gif.php');
						} ?>

					</div><!--/entry-content-->

					<div class="row">
						<div class="col-md-12">
							<?php comments_template(); ?>
						</div><!--/cols-->
					</div><!--/row-->
				</article>
			</div><!--/cols-->
			<div class="col-md-3 col-md-offset-1">
				<div id="sidebar">
					<ul class="xoxo">
						<li class="widget-container">
							<?php if ( has_nav_menu('sidebar') ): ?>
								<h3>Documentation</h3>
								<?php wp_nav_menu(array('theme_location' => 'sidebar')); ?>
							<?php endif; ?>
						</li>
					</ul>
				</div>
			</div><!--/cols-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>
<?php do_action('nebula_footer'); ?>