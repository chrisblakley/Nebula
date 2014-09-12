<?php
/**
 * Template Name: Example
 * @TODO: Delete this file before launching the site!
 */

if ( !defined('ABSPATH') ) { exit; } //Exit if accessed directly

get_header(); ?>

<script src="<?php echo get_template_directory_uri();?>/js/libs/cssbs.js" <?php echo $GLOBALS["async"]; ?>></script>

<section><!-- Do not duplicate this section because it has inline styles. -->
	<div class="container" style="background: #0098d7;">
		<div class="row">
			<div class="sixteen columns">
				<h1 class="entry-title" style="color: #fff;"><?php the_title(); ?></h1>
				<p style="color: #fff;"><?php the_field('description'); ?></p>				
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="container" style="background-color: rgba(0,0,0,0.0225); margin-bottom: 30px;">
	<div class="row">
		<div class="sixteen columns">
			<?php the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
	<hr/>
</div><!--/container-->

<?php if ( is_page(680) ) { //Hero Slider (bxslider)
	include_once('includes/hero_slider_bxslider.php');
} ?>

<div class="container">
	<div class="row">
		<div class="eleven columns">
			
			<div class="container">
				<div class="row">
					<div class="sixteen columns entry-content">
						<?php if ( get_field('usage') ) : ?>
							<h2>Usage</h2>
							<?php echo do_shortcode(get_field('usage')); ?>
							<br/>
						<?php endif; ?>
						
						<?php if ( get_field('parameters') ) : ?>
							<h2>Parameters</h2>
							<p><?php echo do_shortcode(get_field('parameters')); ?></p>
						<?php endif; ?>
						
						<?php if ( get_field('example') ) : ?>
							<h2>Example</h2>
							<?php echo do_shortcode(get_field('example')); ?>
							<br/>
						<?php endif; ?>
						<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
							<?php the_content(); ?>
						<?php endwhile; ?>
					</div><!--/columns-->
				</div><!--/row-->
				
				
				<?php
				/*==========================
				 Hard-Code Examples
				 ===========================*/
				?>
					
					<?php if ( is_page(208) ) { //Basic Wordpress Query
						include_once('includes/wp_query_multicolumn.php');
					} ?>
					
					<?php if ( is_page(434) ) { //Sticky Post
						include_once('includes/stick_post.php');
					} ?>
					
					<?php if ( is_page(318) ) { //Vimeo Meta
						include_once('includes/vimeo_meta.php');
					} ?>
					
					<?php if ( is_page(263) ) { //Youtube Meta
						include_once('includes/youtube_meta.php');
					} ?>
					
					<?php if ( is_page(834) ) { //Ooyala Player Modal
						include_once('includes/ooyala_player_modal.php');
					} ?>
					
					<?php if ( is_page(791) ) { //GET()
						include_once('includes/get.php');
					} ?>
					
					<?php if ( is_page(797) ) { //nebula_event()
						include_once('includes/nebula_event.php');
					} ?>
					
					<?php if ( is_page(829) ) { //Social Buttons
						include_once('includes/social_buttons.php');	
					} ?>
					
					<?php if ( is_page(824) ) { //Weather Detection
						include_once('includes/weather_detection.php');	
					} ?>
					
					<?php if ( is_page(819) ) { //Facebook Feed
						include_once('includes/facebook_feed.php');	
					} ?>
					
					<?php if ( is_page(815) ) { //Twitter Feed
						include_once('includes/twitter_feed.php');	
					} ?>
					
					<?php if ( is_page(722) ) { //Currently Open (Business Hours)
						include_once('includes/currently_open.php');
					} ?>
					
					<?php if ( is_page(214) ) { //Nebula Meta
						include_once('includes/nebula_meta.php');	
					} ?>
					
					<?php if ( is_page(224) ) { //Nebula the Excerpt
						include_once('includes/nebula_the_excerpt.php');	
					} ?>
					
					<?php if ( is_page(228) ) { //Nebula Manage
						include_once('includes/nebula_manage.php');	
					} ?>
					
					<?php if ( is_page(648) ) { //Facebook Graph API
						include_once('includes/facebook_graph.php');						
					} ?>
					
					<?php if ( is_page(258) ) { //CSS Browser Selector
						include_once('includes/css_browser_selector.php');	
					} ?>
					
					<?php if ( is_page(582) ) { //IE Compatibility Mode Detection
						include_once('includes/ie_compatibility_mode_detection.php');	
					} ?>
					
					<?php if ( is_page(277) ) { //Retina
						include_once('includes/retina.php');	
					} ?>
					
					<?php if ( is_page(485) ) { //Picture Tag
						include_once('includes/picture.php');	
					} ?>
					
					<?php if ( is_page(351) && 1==2 ) { //Slider (Native)
						include_once('includes/hero_slider_native.php');					
					} ?>

					<?php if ( is_page(428) ) { //Tooltip Shortcode
						include_once('includes/tooltip.php');
					} ?>
					
					<?php if ( is_page(460) ) { //File Type Indicators
						include_once('includes/file_type_indicators.php');	
					} ?>
					
					<?php if ( is_page(443) ) { //Nebula Shadows
						include_once('includes/nebula_shadows.php');
					} ?>
					
					<?php if ( is_page(430) ) { //Accordion Shortcode
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(432) ) { //Bio Shortcode
						include_once('includes/bio.php');
					} ?>
					
					<?php if ( is_page(359) ) { //Level 4 Media Queries
						include_once('includes/level_4_media_queries.php');	
					} ?>
					
					<?php if (is_page(497) ) { //CSS Blending Modes
						include_once('includes/css_blending_modes.php');
					} ?>
					
					<?php if ( is_page(436) ) { //Speech Synthesis
						include_once('includes/speech_synthesis.php');	
					} ?>
					
					<?php if ( is_page(356) ) { //AJAX
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(703) ) { //AJAX Contact Form
						include_once('includes/ajax_contact_form.php');	
					} ?>
					
					<?php if ( is_page(588) ) { //nebula_tel_link and nebula_phone_format
						include_once('includes/nebula_tel_link.php');	
					} ?>
					
					<?php if ( is_page(481) ) { //Video 
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(346) ) { //PHP Mobile Detect
						include_once('includes/php_mobile_detect.php');	
					} ?>
					
					<?php if ( is_page(614) ) { //CSS Position: Sticky
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(742) ) { //History API
						include_once('includes/history_api.php');
					} ?>
					
					<?php if ( is_page(737) ) { //Notification API							
						include_once('includes/notification_api.php');												
					} ?>
					
					<?php if ( is_page(760) ) { //bxSlider
						include_once('includes/bxslider.php');	
					} ?>
					
					<?php if ( is_page(785) ) { //Seamless Iframe
						include_once('includes/seamless_iframe.php');	
					} ?>
					
					<?php if ( is_page(779) ) { //Google Analytics RealTime API
						include_once('includes/google_analytics_realtime_api.php');	
					} ?>
					
					<?php if ( is_page(614) ) { //Device Orientation
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(614) ) { //Device Motion API
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(1234) ) { //Image Orientation
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(617) ) { //CSS Variables
						include_once('includes/css_variables.php');	
					} ?>
					
					<?php if ( is_page(1234) ) { //Proximity API
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(671) ) { //PHP error handling
						include_once('includes/php_error_handling.php');	
					} ?>
					
					<?php if ( is_page(643) ) { //Speech Recognition API
						include_once('includes/speech_recognition.php');						
					} ?>
					
					<?php if ( is_page(624) ) { //CSS Feature Queries
						include_once('includes/css_feature_queries.php');	
					} ?>
					
					<?php if ( is_page(1234) ) { //Clipboard API
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(1234) ) { //CSS Masks
						//@TODO: Coming Soon
					} ?>
					
					<?php if ( is_page(628) ) { //Page Visibility API
						include_once('includes/page_visibility.php');	
					} ?>
					
					<?php if ( is_page(89) ) { //Google Maps Iframe
						include_once('includes/google_maps_iframe.php');
					} ?>
				
					<?php if ( is_page(267) ) { //Google Maps Javascript API v3
						include_once('includes/google_maps_js_api.php');	
					} ?>
				
					<?php if (is_page(397)) { //Cookies
						include_once('includes/cookies_js.php');	
					} ?>
					
					<?php if (is_page(867)) { //DataTables
						include_once('includes/datatables.php');	
					} ?>
					
				<?php
				/*==========================
				 End Hard-Code Examples
				 ===========================*/
				?>
								
				<?php if ( current_user_can('manage_options') ) : ?>
					<div class="container entry-manage">
						<div class="row">
							<div class="sixteen columns">
								<hr/>
								<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
								<hr/>
							</div><!--/columns-->
						</div>
					</div>
				<?php else : ?>
					<div class="container entry-manage">
						<div class="row">
							<div class="sixteen columns">
								<hr/>
								<?php nebula_manage('modified'); ?>
								<hr/>
							</div><!--/columns-->
						</div>
					</div>
				<?php endif; ?>
				
				<div class="row">
					<div class="sixteen columns">
						<?php get_template_part('comments'); ?>
					</div><!--/columns-->
				</div><!--/row-->
				
			</div><!--/container-->
			
		</div><!--/columns-->
		<div class="four columns push_one">
			<ul class="xoxo">
				<li>
					<h3>Documentation</h3>
					<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999')); ?>
				</li>
			</ul>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>