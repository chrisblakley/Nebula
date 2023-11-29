<?php

//Note: Widgets are all in their own class, so Nebula functions must use nebula() and not $this

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Widgets') ){
	trait Widgets {
		public function hooks(){
			add_action('widgets_init', array($this, 'register_widget_areas'));
			add_action('widgets_init', array($this, 'load_nebula_widgets'));
		}

		//Register Widget Areas
		public function register_widget_areas(){
			$override = apply_filters('pre_register_widget_areas', null);
			if ( isset($override) ){return;}

			//Sidebar (Primary) (Must be declared first!)
			register_sidebar(array(
				'name' => 'Sidebar',
				'id' => 'primary-widget-area',
				'description' => 'The sidebar (primary) widget area',
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));

			//Header
			register_sidebar(array(
				'name' => 'Header',
				'id' => 'header-widget-area',
				'description' => 'The vertical widget area that appears in the header',
				'before_widget' => '<div id="%1$s" class="row widget-container justify-content-center"><div class="col align-self-center %2$s">',
				'after_widget' => '</div></div>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));

			//Hero
			register_sidebar(array(
				'name' => 'Hero',
				'id' => 'hero-widget-area',
				'description' => 'The horizontal hero widget area',
				'before_widget' => '<div id="%1$s" class="col-md widget-container align-self-center %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));

			//Single Post
			register_sidebar(array(
				'name' => 'Single Post',
				'id' => 'single-post-widget-area',
				'description' => 'Appears after a single post article.',
				'before_widget' => '<div id="%1$s" class="row widget-container"><div class="col %2$s">',
				'after_widget' => '</div></div>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));

			//Footer
			register_sidebar(array(
				'name' => 'Footer',
				'id' => 'footer-widget-area',
				'description' => 'The horizontal footer widget area',
				'before_widget' => '<div id="%1$s" class="col-md widget-container %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));
		}

		public function load_nebula_widgets(){
			register_widget('nebula_about_the_author');
			register_widget('nebula_crosslinks');
			register_widget('nebula_google_maps_embed');
			register_widget('nebula_linked_image');
			register_widget('nebula_login_form');
			register_widget('nebula_social_page_links');
			register_widget('nebula_social_sharing');
			register_widget('nebula_twitter_tweets');
			register_widget('nebula_video');

			if ( is_plugin_active('advanced-custom-fields-pro/acf.php') ){ //Advanced Custom Fields v5
				register_widget('nebula_acf');
			}
		}
	}

	/*==========================
	 About the Author
	 ===========================*/
	class nebula_about_the_author extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_about_the_author', 'Nebula - About the Author', array('description' => 'A small bio for the author'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('About the Author Widget', 'start', 'Nebula Widgets');
			$user_id = ( !empty($instance['user_id']) )? $instance['user_id'] : get_the_author_meta('ID');
			$author_info = get_userdata($user_id);

			if ( empty($author_info) ){
				return false;
			}

			$author_image = false;
			if ( get_user_meta($user_id, 'headshot', true) ){ //Check for a custom field called "headshot" first
				$author_image = get_user_meta($user_id, 'headshot', true);

				if ( is_array($author_image) ){ //Image stored as an array
					$author_image = $author_image['sizes']['thumbnail'];
				} else { //Image stored as an ID
					$author_image = wp_get_attachment_image_src($author_image);
					$author_image = $author_image[0];
				}
			} elseif ( get_avatar_url($user_id) ){ //Use the WP core gravatar if available
				$author_image = get_avatar_url($user_id);
			}

			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<div class="about-the-author">
					<div class="row">
						<div class="col">
							<div class="headshot-title-con">
								<?php if ( !empty($author_image) ): ?>
									<?php
										$name_text = ( !empty($author_info->first_name) )? 'About ' . $author_info->first_name : 'About the Author';
										nebula()->lazy_img($author_image, 'alt="' . $name_text . '"');
									?>
								<?php endif; ?>
								<div>
									<?php if ( !empty($instance['title']) ): ?>
										<h3><?php echo $instance['title']; ?></h3>
									<?php else: ?>
										<h3><?php echo ( !empty($author_info->first_name) )? 'About ' . $author_info->first_name : 'About the Author'; ?></h3>
									<?php endif; ?>

									<?php
										$job_title_location = array();

										if ( !empty($instance['show_job_info']) ){
											$title_company = array(get_user_meta($user_id, 'jobtitle', true));
											$title_company[] = ( get_user_meta($user_id, 'jobcompanywebsite', true) )? '<a href="' . get_user_meta($user_id, 'jobcompanywebsite', true) . '" target="_blank">' . get_user_meta($user_id, 'jobcompany', true) . '</a>' : get_user_meta($user_id, 'jobcompany', true);
											$job_title_location[] = implode(' at ', array_filter($title_company));
										}

										if ( !empty($instance['show_location']) ){
											$job_title_location[] = implode(',', array_filter(array(get_user_meta($user_id, 'usercity', true), get_user_meta($user_id, 'userstate', true))));
										}

										$job_title_location = implode(', ', array_filter($job_title_location));
									?>
									<?php if ( !empty($job_title_location) ): ?>
										<p><?php echo $job_title_location; ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div><!--/col-->
					</div><!--/row-->
					<div class="row">
						<div class="col">
							<p><?php echo $author_info->description; ?></p>
						</div><!--/col-->
					</div><!--/row-->
					<?php if ( !empty($instance['show_social_links']) ): ?>
						<?php
							$author_social = array(
								'facebook' => ( get_user_meta($user_id, 'facebook', true) )? 'https://www.facebook.com/' . get_user_meta($user_id, 'facebook', true) : false,
								'twitter' => ( get_user_meta($user_id, 'twitter', true) )? nebula()->twitter_url(get_user_meta($user_id, 'twitter', true)) : false,
								'instagram' => ( get_user_meta($user_id, 'instagram', true) )? 'https://www.instagram.com/' . get_user_meta($user_id, 'instagram', true) : false,
								'linkedin' => ( get_user_meta($user_id, 'instagram', true) )? 'https://www.linkedin.com/' . get_user_meta($user_id, 'instagram', true) : false,
								'pinterest' => ( get_user_meta($user_id, 'pinterest', true) )? 'https://www.pinterest.com/' . get_user_meta($user_id, 'pinterest', true) : false,
								'youtube' => ( get_user_meta($user_id, 'youtube', true) )? 'https://www.youtube.com/user/' . get_user_meta($user_id, 'youtube', true) : false,
								'envelope-o' => 'mailto:' . $author_info->user_email
							);
							$author_social = array_filter($author_social);
						?>
						<?php if ( !empty($author_social) ): ?>
							<div class="row">
								<div class="col">
									<ul class="author-social">
										<?php foreach ( $author_social as $icon => $url ): ?>
											<li><a href="<?php echo $url; ?>" target="_blank"><i class="<?php echo ( $icon === 'envelope-o' )? 'fa-solid' : 'fa-brands'; ?> fa-fw fa-<?php echo $icon; ?>"></i></a></li>
										<?php endforeach; ?>
									</ul>
								</div><!--/col-->
							</div><!--/row-->
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
					<span class="nebula-help-text">Leaving this empty will display the author's first name (if available) or fallback to "About the Author".</span>
				</p>

				<p>
					<label for="<?php echo $this->get_field_id('user_id'); ?>">User ID</label>
					<input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>" type="text" value="<?php echo ( isset($instance['user_id']) )? $instance['user_id'] : ''; ?>" />
					<span class="nebula-help-text">This will override the author detection to force a specific user's bio.</span>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['show_job_info'], 'on'); ?> id="<?php echo $this->get_field_id('show_job_info'); ?>" name="<?php echo $this->get_field_name('show_job_info'); ?>" />
					<label for="<?php echo $this->get_field_id('show_job_info'); ?>"> Show Job Title and Company</label>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['show_location'], 'on'); ?> id="<?php echo $this->get_field_id('show_location'); ?>" name="<?php echo $this->get_field_name('show_location'); ?>" />
					<label for="<?php echo $this->get_field_id('show_location'); ?>"> Show Location</label>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['show_social_links'], 'on'); ?> id="<?php echo $this->get_field_id('show_social_links'); ?>" name="<?php echo $this->get_field_name('show_social_links'); ?>" />
					<label for="<?php echo $this->get_field_id('show_social_links'); ?>"> Show Social Media Links</label>
				</p>

				<p>To avoid needing to use Gravatar for user images, this widget listens for a custom field of "headshot" first.</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			$instance['user_id'] = ( !empty($new_instance['user_id']) )? strip_tags($new_instance['user_id']) : '';
			$instance['show_job_info'] = ( !empty($new_instance['show_job_info']) )? strip_tags($new_instance['show_job_info']) : '';
			$instance['show_location'] = ( !empty($new_instance['show_location']) )? strip_tags($new_instance['show_location']) : '';
			$instance['show_social_links'] = ( !empty($new_instance['show_social_links']) )? strip_tags($new_instance['show_social_links']) : '';
			return $instance;
		}
	}

	/*==========================
	 Cross-links (Next/Previous Post)
	 ===========================*/
	class nebula_crosslinks extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_crosslinks', 'Nebula - Crosslinks', array('description' => 'Link to the next/previous post'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Crosslinks Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<?php if ( !empty($instance['title']) ): ?>
					<h3><?php echo $instance['title']; ?></h3>
				<?php endif; ?>

				<div class="row">
					<?php if ( get_previous_post_link() ): ?>
						<div class="col prev-link-con">
							<p class="prevnext-post-heading prev-post-heading">Previous <?php echo ucwords(get_post_type()); ?></p>
							<div class="prevnext-post-link prev-post-link"><?php previous_post_link(); ?></div>
						</div><!--/col-->
					<?php endif; ?>

					<?php if ( get_next_post_link() ): ?>
						<div class="col next-link-con">
							<p class="prevnext-post-heading next-post-heading">Next <?php echo ucwords(get_post_type()); ?></p>
							<div class="prevnext-post-link next-post-link"><?php next_post_link(); ?></div>
						</div><!--/col-->
					<?php endif; ?>
				</div><!--/row-->
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			return $instance;
		}
	}

	/*==========================
	 Google Maps Embed API (Iframe)
	 ===========================*/
	class nebula_google_maps_embed extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_google_maps_embed', 'Nebula - Google Maps Embed', array('description' => 'Embed a Google Map'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Google Maps Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<?php if ( !empty($instance['title']) ): ?>
					<h3><?php echo $instance['title']; ?></h3>
				<?php endif; ?>

				<div class="row">
					<div class="col">
						<iframe class="googlemap" title="Google Map" width="100%" height="<?php echo ( !empty($instance['height']) )? $instance['height'] : '350'; ?>" loading="lazy"
							src="https://www.google.com/maps/embed/v1/<?php echo $instance['map_mode']; ?>?key=<?php echo nebula()->option('google_browser_api_key'); ?>
							<?php echo ( in_array($instance['map_mode'], array('place', 'search')) && !empty($instance['query']) )? '&q=' . $instance['query'] : ''; //Place, Search ?>

							<?php echo ( $instance['map_mode'] === 'directions' && !empty($instance['origin']) )? '&origin=' . $instance['origin'] : ''; //Directions ?>
							<?php echo ( $instance['map_mode'] === 'directions' && !empty($instance['destination']) )? '&destination=' . $instance['destination'] : ''; //Directions ?>
							<?php echo ( $instance['map_mode'] === 'directions' && !empty($instance['waypoints']) )? '&waypoints=' . $instance['waypoints'] : ''; //Directions ?>
							<?php echo ( $instance['map_mode'] === 'directions' && !empty($instance['travel_mode']) )? '&mode=' . $instance['travel_mode'] : ''; //Directions ?>
							<?php echo ( $instance['map_mode'] === 'directions' && !empty($instance['avoid']) )? '&avoid=' . $instance['avoid'] : ''; //Directions ?>
							<?php echo ( $instance['map_mode'] === 'directions' && !empty($instance['units']) )? '&units=' . $instance['units'] : ''; //Directions ?>

							<?php echo ( $instance['map_mode'] === 'view' && !empty($instance['latlng']) )? '&center=' . $instance['latlng'] : ''; //View ?>

							<?php echo ( $instance['map_mode'] === 'streetview' && !empty($instance['latlng']) )? '&location=' . $instance['latlng'] : ''; //Streetview ?>
							<?php echo ( $instance['map_mode'] === 'streetview' && !empty($instance['pano']) )? '&pano=' . $instance['pano'] : ''; //Streetview ?>
							<?php echo ( $instance['map_mode'] === 'streetview' && !empty($instance['heading']) )? '&heading=' . $instance['heading'] : ''; //Streetview ?>
							<?php echo ( $instance['map_mode'] === 'streetview' && !empty($instance['pitch']) )? '&pitch=' . $instance['pitch'] : ''; //Streetview ?>
							<?php echo ( $instance['map_mode'] === 'streetview' && !empty($instance['fov']) )? '&fov=' . $instance['fov'] : ''; //Streetview ?>

							<?php echo ( in_array($instance['map_mode'], array('place', 'search', 'directions', 'view')) && !empty($instance['zoom']) )? '&zoom=' . $instance['zoom'] : ''; ?>
							<?php echo ( in_array($instance['map_mode'], array('place', 'search', 'directions', 'view')) && !empty($instance['map_type']) )? '&maptype=' . $instance['map_type'] : ''; ?>
						"></iframe>
					</div><!--/col-->
				</div><!--/row-->
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<script>
					jQuery('.mode_required').addClass('hidden');

					hideShowMapFields(jQuery('.map_mode_select').val());
					jQuery('.map_mode_select').on('change', function(){
						hideShowMapFields(jQuery(this).val());
					});

					function hideShowMapFields(mode){
						jQuery('.mode_required').each(function(){
							if ( jQuery(this).attr('data-for').indexOf('mode-' + mode) > -1 ){
								jQuery(this).removeClass('hidden');
							} else {
								jQuery(this).addClass('hidden');
							}
						});
					}
				</script>

				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('height'); ?>">Height</label>
					<input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo ( isset($instance['height']) )? $instance['height'] : ''; ?>" placeholder="350" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('map_mode'); ?>">Mode</label>
					<select id="<?php echo $this->get_field_id('map_mode'); ?>" name="<?php echo $this->get_field_name('map_mode'); ?>" class="widefat map_mode_select" style="width:100%;">
						<option <?php selected($instance['map_mode'], 'place'); ?> value="place">Place Mode</option>
						<option <?php selected($instance['map_mode'], 'search'); ?> value="search">Search Mode</option>
						<option <?php selected($instance['map_mode'], 'directions'); ?> value="directions">Directions Mode</option>
						<option <?php selected($instance['map_mode'], 'view'); ?> value="view">View Mode</option>
						<option <?php selected($instance['map_mode'], 'streetview'); ?> value="streetview">Street View Mode</option>
					</select>
				</p>

				<p class="mode_required" data-for="mode-place|mode-search">
					<label for="<?php echo $this->get_field_id('query'); ?>">Query</label>
					<input class="widefat" id="<?php echo $this->get_field_id('query'); ?>" name="<?php echo $this->get_field_name('query'); ?>" type="text" value="<?php echo ( isset($instance['query']) )? $instance['query'] : ''; ?>" />
				</p>

				<p class="mode_required" data-for="mode-directions">
					<label for="<?php echo $this->get_field_id('origin'); ?>">Origin</label>
					<input class="widefat" id="<?php echo $this->get_field_id('origin'); ?>" name="<?php echo $this->get_field_name('origin'); ?>" type="text" value="<?php echo ( isset($instance['origin']) )? $instance['origin'] : ''; ?>" />
				</p>
				<p class="mode_required" data-for="mode-directions">
					<label for="<?php echo $this->get_field_id('destination'); ?>">Destination</label>
					<input class="widefat" id="<?php echo $this->get_field_id('destination'); ?>" name="<?php echo $this->get_field_name('destination'); ?>" type="text" value="<?php echo ( isset($instance['destination']) )? $instance['destination'] : ''; ?>" />
				</p>
				<p class="mode_required" data-for="mode-directions">
					<label for="<?php echo $this->get_field_id('waypoints'); ?>">Waypoints</label>
					<input class="widefat" id="<?php echo $this->get_field_id('waypoints'); ?>" name="<?php echo $this->get_field_name('waypoints'); ?>" type="text" value="<?php echo ( isset($instance['waypoints']) )? $instance['waypoints'] : ''; ?>" placeholder="Destiny USA|Blarney Stone" />
				</p>
				<p class="mode_required" data-for="mode-directions">
					<label for="<?php echo $this->get_field_id('travel_mode'); ?>">Travel Mode</label>
					<select id="<?php echo $this->get_field_id('travel_mode'); ?>" name="<?php echo $this->get_field_name('travel_mode'); ?>" class="widefat" style="width:100%;">
						<option <?php selected($instance['travel_mode'], ''); ?> value="">Default</option>
						<option <?php selected($instance['travel_mode'], 'driving'); ?> value="driving">Driving</option>
						<option <?php selected($instance['travel_mode'], 'walking'); ?> value="walking">Walking</option>
						<option <?php selected($instance['travel_mode'], 'bicycling'); ?> value="bicycling">Bicycling</option>
						<option <?php selected($instance['travel_mode'], 'transit'); ?> value="transit">Transit</option>
						<option <?php selected($instance['travel_mode'], 'flying'); ?> value="flying">Flying</option>
					</select>
				</p>
				<p class="mode_required" data-for="mode-directions">
					<label for="<?php echo $this->get_field_id('avoid'); ?>">Avoid</label>
					<input class="widefat" id="<?php echo $this->get_field_id('avoid'); ?>" name="<?php echo $this->get_field_name('avoid'); ?>" type="text" value="<?php echo ( isset($instance['avoid']) )? $instance['avoid'] : ''; ?>" placeholder="tolls|highways" />
				</p>
				<p class="mode_required" data-for="mode-directions">
					<label for="<?php echo $this->get_field_id('units'); ?>">Units</label>
					<select id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" class="widefat" style="width:100%;">
						<option <?php selected($instance['units'], ''); ?> value="">Match Origin</option>
						<option <?php selected($instance['units'], 'imperial'); ?> value="imperial">Imperial</option>
						<option <?php selected($instance['units'], 'metric'); ?> value="metric">Metric</option>
					</select>
				</p>

				<p class="mode_required" data-for="mode-view|mode-streetview">
					<label for="<?php echo $this->get_field_id('latlng'); ?>">Latitude/Longitude (Center/Location)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('latlng'); ?>" name="<?php echo $this->get_field_name('latlng'); ?>" type="text" value="<?php echo ( isset($instance['latlng']) )? $instance['latlng'] : ''; ?>" placeholder="43.0536832,-76.1656511" />
				</p>
				<p class="mode_required" data-for="mode-streetview">
					<label for="<?php echo $this->get_field_id('pano'); ?>">Panorama ID</label>
					<input class="widefat" id="<?php echo $this->get_field_id('pano'); ?>" name="<?php echo $this->get_field_name('pano'); ?>" type="text" value="<?php echo ( isset($instance['pano']) )? $instance['pano'] : ''; ?>" />
				</p>
				<p class="mode_required" data-for="mode-streetview">
					<label for="<?php echo $this->get_field_id('heading'); ?>">Heading (-180 - 360)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('heading'); ?>" name="<?php echo $this->get_field_name('heading'); ?>" type="text" value="<?php echo ( isset($instance['heading']) )? $instance['heading'] : ''; ?>" placeholder="0" />
				</p>
				<p class="mode_required" data-for="mode-streetview">
					<label for="<?php echo $this->get_field_id('pitch'); ?>">Pitch (-90:Down - 90:Up)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('pitch'); ?>" name="<?php echo $this->get_field_name('pitch'); ?>" type="text" value="<?php echo ( isset($instance['pitch']) )? $instance['pitch'] : ''; ?>" placeholder="0" />
				</p>
				<p class="mode_required" data-for="mode-streetview">
					<label for="<?php echo $this->get_field_id('fov'); ?>">Field of View (0 - 100)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('fov'); ?>" name="<?php echo $this->get_field_name('fov'); ?>" type="text" value="<?php echo ( isset($instance['fov']) )? $instance['fov'] : ''; ?>" placeholder="90" />
				</p>

				<p class="mode_required" data-for="mode-place|mode-directions|mode-search|mode-view">
					<label for="<?php echo $this->get_field_id('map_type'); ?>">Map Type</label>
					<select id="<?php echo $this->get_field_id('map_type'); ?>" name="<?php echo $this->get_field_name('map_type'); ?>" class="widefat" style="width:100%;">
						<option <?php selected($instance['map_type'], 'roadmap'); ?> value="roadmap">Roadmap</option>
						<option <?php selected($instance['map_type'], 'satellite'); ?> value="satellite">Satellite</option>
					</select>
				</p>
				<p class="mode_required" data-for="mode-place|mode-directions|mode-search|mode-view">
					<label for="<?php echo $this->get_field_id('zoom'); ?>">Zoom (0:Far - 21:Close)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('zoom'); ?>" name="<?php echo $this->get_field_name('zoom'); ?>" type="number" value="<?php echo ( isset($instance['zoom']) )? $instance['zoom'] : ''; ?>" min="0" max="21" placeholder="18" />
				</p>

			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			$instance['height'] = ( !empty($new_instance['height']) )? strip_tags($new_instance['height']) : '';
			$instance['map_mode'] = ( !empty($new_instance['map_mode']) )? strip_tags($new_instance['map_mode']) : '';
			$instance['query'] = ( !empty($new_instance['query']) )? strip_tags($new_instance['query']) : '';
			$instance['origin'] = ( !empty($new_instance['origin']) )? strip_tags($new_instance['origin']) : '';
			$instance['destination'] = ( !empty($new_instance['destination']) )? strip_tags($new_instance['destination']) : '';
			$instance['waypoints'] = ( !empty($new_instance['waypoints']) )? strip_tags($new_instance['waypoints']) : '';
			$instance['travel_mode'] = ( !empty($new_instance['travel_mode']) )? strip_tags($new_instance['travel_mode']) : '';
			$instance['avoid'] = ( !empty($new_instance['avoid']) )? strip_tags($new_instance['avoid']) : '';
			$instance['units'] = ( !empty($new_instance['units']) )? strip_tags($new_instance['units']) : '';
			$instance['latlng'] = ( !empty($new_instance['latlng']) )? strip_tags($new_instance['latlng']) : '';
			$instance['pano'] = ( !empty($new_instance['pano']) )? strip_tags($new_instance['pano']) : '';
			$instance['heading'] = ( !empty($new_instance['heading']) )? strip_tags($new_instance['heading']) : '';
			$instance['pitch'] = ( !empty($new_instance['pitch']) )? strip_tags($new_instance['pitch']) : '';
			$instance['fov'] = ( !empty($new_instance['fov']) )? strip_tags($new_instance['fov']) : '';
			$instance['map_type'] = ( !empty($new_instance['map_type']) )? strip_tags($new_instance['map_type']) : '';
			$instance['zoom'] = ( !empty($new_instance['zoom']) )? strip_tags($new_instance['zoom']) : '';
			return $instance;
		}
	}

	/*==========================
	 Image (with Link)
	 ===========================*/
	class nebula_linked_image extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_linked_image', 'Nebula - Linked Image', array('description' => 'Add an image with an optional link.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Linked Image Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<?php if ( !empty($instance['image']) ): ?>
					<?php if ( !empty($instance['url']) ): ?>
						<?php
							//Custom link tags
							if ( $instance['url'] === '[home_url]' ){
								$instance['url'] = home_url();
							}
						?>

						<a href="<?php echo $instance['url']; ?>" <?php echo ( nebula()->url_components('hostname') != nebula()->url_components('hostname', $instance['url']) )? 'target="_blank"' : ''; //Check for external URL ?>>
					<?php endif; ?>

						<?php nebula()->lazy_img($instance['image']); ?>

					<?php if ( !empty($instance['url']) ): ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('image'); ?>">Image</label>
					<input class="widefat" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="text" value="<?php echo ( isset($instance['image']) )? $instance['image'] : ''; ?>" placeholder="http://" />
					<span class="nebula-help-text">Copy the image URL from the <a href="<?php echo admin_url(); ?>upload.php" target="_blank">Media Library</a> or from another location.</span>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('url'); ?>">URL</label>
					<input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo ( isset($instance['url']) )? $instance['url'] : ''; ?>" placeholder="http://" />
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['image'] = ( !empty($new_instance['image']) )? strip_tags($new_instance['image']) : '';
			$instance['url'] = ( !empty($new_instance['url']) )? strip_tags($new_instance['url']) : '';
			return $instance;
		}
	}

	/*==========================
	 Login Form
	 ===========================*/
	class nebula_login_form extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_login_form', 'Nebula - Login Form', array('description' => 'A simple WordPress login form.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Login Form Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<?php if ( !empty($instance['title']) ): ?>
					<h3><?php echo $instance['title']; ?></h3>
				<?php endif; ?>

				<?php if ( is_user_logged_in() ): ?>
					<?php $current_user = wp_get_current_user(); ?>
					<p><?php echo ( $instance['greeting'] )? $instance['greeting'] : 'Welcome back'; ?>, <strong><?php echo $current_user->display_name; ?></strong>! <a class="nowrap" href="<?php echo wp_logout_url(get_permalink() . '?no-cache'); ?>"><i class="fa-solid fa-sign-out"></i> Logout</a></p>
				<?php else: ?>
					<form id="loginform" class="nebula-login-form" name="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
						<div class="form-group login-username">
							<label class="form-control-label" for="user_login"><i class="fa-solid fa-fw fa-user"></i> Username or Email Address</label>
							<input id="user_login" class="input form-control" type="text" name="log" placeholder="Username" />
						</div>
						<div class="form-group login-password">
							<label class="form-control-label" for="user_pass"><i class="fa-solid fa-fw fa-lock"></i> Password</label>
							<input id="user_pass" class="input form-control" type="password" name="pwd" placeholder="Password" />
						</div>
						<div class="form-group login-remember">
							<label class="form-check-label">
								<input id="rememberme" class="form-check-input" name="rememberme" type="checkbox" value="forever" /> Remember Me
							</label>
						</div>
						<div class="form-group login-submit">
							<input id="wp-submit" class="button button-primary btn btn-brand" type="submit" name="wp-submit" value="Log In" />
							<input type="hidden" class="form-control" name="redirect_to" value="<?php echo get_permalink(); ?>?no-cache" />
						</div>
					</form>

					<?php wp_register(); //Link to registration page if allowed in WP settings ?>
					<?php //wp-login.php?action=lostpassword ?>
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('greeting'); ?>">Greeting</label>
					<input class="widefat" id="<?php echo $this->get_field_id('greeting'); ?>" name="<?php echo $this->get_field_name('greeting'); ?>" type="text" value="<?php echo ( isset($instance['greeting']) )? $instance['greeting'] : ''; ?>" placeholder="Welcome back" />
					<span class="nebula-help-text">The text that appears to logged in users.</span>
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			$instance['greeting'] = ( !empty($new_instance['greeting']) )? strip_tags($new_instance['greeting']) : '';
			return $instance;
		}
	}

	/*==========================
	 Social Page Links
	 ===========================*/
	class nebula_social_page_links extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_social_page_links', 'Nebula - Social Page Links', array('description' => 'Add links to social network pages.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Social Page Links Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			$social_pages = array(
				'facebook-square' => ( nebula()->get_option('facebook_url') )? nebula()->get_option('facebook_url') : false,
				'twitter' => ( nebula()->get_option('twitter_username') )? nebula()->twitter_url() : false,
				'instagram' => ( nebula()->get_option('instagram_url') )? nebula()->get_option('instagram_url') : false,
				'linkedin-square' => ( nebula()->get_option('linkedin_url') )? nebula()->get_option('linkedin_url') : false,
				'pinterest' => ( nebula()->get_option('pinterest_url') )? nebula()->get_option('pinterest_url') : false,
				'youtube' => ( nebula()->get_option('youtube_url') )? nebula()->get_option('youtube_url') : false,
				'envelope' => ( nebula()->get_option('contact_email') )? nebula()->get_option('contact_email') : false,
			);
			$social_pages = array_filter($social_pages);
			?>
				<?php if ( !empty($social_pages) ): ?>
					<div class="row">
						<div class="col">
							<?php if ( !empty($instance['title']) ): ?>
								<h3><?php echo $instance['title']; ?></h3>
							<?php endif; ?>

							<ul class="about-social">
								<?php foreach ( $social_pages as $icon => $url ): ?>
									<li><a href="<?php echo $url; ?>" target="_blank"><i class="fa-brands fa-fw fa-<?php echo $icon; ?>"></i></a></li><?php //fa-brands works for everything except email... ?>
								<?php endforeach; ?>
							</ul>
						</div><!--/col-->
					</div><!--/row-->
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			return $instance;
		}
	}

	/*==========================
	 Social Sharing
	 ===========================*/
	class nebula_social_sharing extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_social_sharing', 'Nebula - Social Sharing', array('description' => 'Add social sharing buttons.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Social Sharing Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			$social_networks = array();
			$social_networks[] = ( !empty($instance['facebook']) )? 'facebook' : false;
			$social_networks[] = ( !empty($instance['twitter']) )? 'twitter' : false;
			$social_networks[] = ( !empty($instance['linkedin']) )? 'linkedin' : false;
			$social_networks[] = ( !empty($instance['pinterest']) )? 'pinterest' : false;
				$social_networks[] = ( !empty($instance['native_buttons']) )? 'native_buttons' : false;
			$social_networks = array_filter($social_networks);
			?>
				<?php if ( !empty($social_networks) ): ?>
					<div class="row">
						<div class="col">
							<?php if ( !empty($instance['title']) ): ?>
								<h3><?php echo $instance['title']; ?></h3>
							<?php endif; ?>

							<?php echo ( !empty($instance['native_buttons']) )? nebula()->social($social_networks, nebula()->is_staff()) : nebula()->share($social_networks); ?>
						</div><!--/col-->
					</div><!--/row-->
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['share_api'], 'on'); ?> id="<?php echo $this->get_field_id('share_api'); ?>" name="<?php echo $this->get_field_name('share_api'); ?>" />
					<label for="<?php echo $this->get_field_id('share_api'); ?>"> Share API</label>
					<span class="nebula-help-text">Only appears when supported. If selected (and supported) it will replace any third-party buttons (even if checked below) for optimization!</span>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['facebook'], 'on'); ?> id="<?php echo $this->get_field_id('facebook'); ?>" name="<?php echo $this->get_field_name('facebook'); ?>" />
					<label for="<?php echo $this->get_field_id('facebook'); ?>"> Facebook</label>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['twitter'], 'on'); ?> id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" />
					<label for="<?php echo $this->get_field_id('twitter'); ?>"> Twitter</label>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['linkedin'], 'on'); ?> id="<?php echo $this->get_field_id('linkedin'); ?>" name="<?php echo $this->get_field_name('linkedin'); ?>" />
					<label for="<?php echo $this->get_field_id('linkedin'); ?>"> LinkedIn</label>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['pinterest'], 'on'); ?> id="<?php echo $this->get_field_id('pinterest'); ?>" name="<?php echo $this->get_field_name('pinterest'); ?>" />
					<label for="<?php echo $this->get_field_id('pinterest'); ?>"> Pinterest</label>
				</p>

				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['native_buttons'], 'on'); ?> id="<?php echo $this->get_field_id('native_buttons'); ?>" name="<?php echo $this->get_field_name('native_buttons'); ?>" />
					<label for="<?php echo $this->get_field_id('native_buttons'); ?>"> Native Buttons</label>
					<span class="nebula-help-text">Native social buttons take longer to load and some require additional configuration. Non-native buttons have more consistent styling.</span>
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			$instance['share_api'] = ( !empty($new_instance['share_api']) )? strip_tags($new_instance['share_api']) : '';
			$instance['facebook'] = ( !empty($new_instance['facebook']) )? strip_tags($new_instance['facebook']) : '';
			$instance['twitter'] = ( !empty($new_instance['twitter']) )? strip_tags($new_instance['twitter']) : '';
			$instance['linkedin'] = ( !empty($new_instance['linkedin']) )? strip_tags($new_instance['linkedin']) : '';
			$instance['pinterest'] = ( !empty($new_instance['pinterest']) )? strip_tags($new_instance['pinterest']) : '';
			$instance['native_buttons'] = ( !empty($new_instance['native_buttons']) )? strip_tags($new_instance['native_buttons']) : '';
			return $instance;
		}
	}

	/*==========================
	 Twitter
	 ===========================*/
	class nebula_twitter_tweets extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_twitter_tweets', 'Nebula - Twitter Tweets', array('description' => 'Display one or more tweets from Twitter by a specific user or from a list.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Twitter Tweets Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			$tweet_options = array(
				'user' => ( !empty($instance['username']) )? $instance['username'] : false,
				'list' => ( !empty($instance['list_name']) )? $instance['list_name'] : false,
				'number' => ( !empty($instance['number_tweets']) )? $instance['number_tweets'] : false,
				'retweets' => ( !empty($instance['include_retweets']) )? $instance['include_retweets'] : false,
			);

			$tweets = nebula()->twitter_cache(array_filter($tweet_options));
			?>
				<?php if ( !empty($instance['title']) ): ?>
					<h3><?php echo $instance['title']; ?></h3>
				<?php endif; ?>

				<?php if ( !empty($tweets) && empty($tweets->error) ): ?>
					<?php
						$user_html = '<div class="row">
									<div class="col">
										<div class="user-profile-con">
											<img src="' . $tweets[0]->user->profile_image_url_https . '" loading="lazy" />

											<div>
												<h3>' . $tweets[0]->user->name . '</h3>
												<a href="https://twitter.com/' . $tweets[0]->user->name . '" target="_blank" title="' . $tweets[0]->user->description . '">@' . $tweets[0]->user->screen_name . '</a>
											</div>
										</div>
									</div><!--/col-->
								</div><!--/row-->';
					?>

					<div class="nebula-twitter-widget">
						<?php if ( empty($instance['list_name']) ): ?>
							<?php echo $user_html; ?>
						<?php endif; ?>

						<?php foreach ( $tweets as $tweet ): ?>
							<?php if ( !empty($instance['list_name']) ): ?>
								<?php echo $user_html; ?>
							<?php endif; ?>
							<div class="row">
								<div class="col">
									<p>
										<span class="tweet"><?php echo $tweet->markup; ?></span>
										<br /><a href="<?php echo $tweet->tweet_url; ?>" class="tweet-date" target="_blank" title="<?php echo $tweet->time_formatted; ?>"><?php echo $tweet->time_ago . ' ago'; ?></a>
									</p>
								</div><!--/col-->
							</div><!--/row-->
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('username'); ?>">Username</label>
					<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo ( isset($instance['username']) )? $instance['username'] : ''; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('list_name'); ?>">List Name</label>
					<input class="widefat" id="<?php echo $this->get_field_id('list_name'); ?>" name="<?php echo $this->get_field_name('list_name'); ?>" type="text" value="<?php echo ( isset($instance['list_name']) )? $instance['list_name'] : ''; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('number_tweets'); ?>">Number of Tweets</label>
					<input class="widefat" id="<?php echo $this->get_field_id('number_tweets'); ?>" name="<?php echo $this->get_field_name('number_tweets'); ?>" type="number" value="<?php echo ( isset($instance['number_tweets']) )? $instance['number_tweets'] : ''; ?>" />
				</p>
				<p>
					<input class="checkbox" type="checkbox" <?php checked($instance['include_retweets'], 'on'); ?> id="<?php echo $this->get_field_id('include_retweets'); ?>" name="<?php echo $this->get_field_name('include_retweets'); ?>" />
					<label for="<?php echo $this->get_field_id('include_retweets'); ?>"> Include Retweets</label>
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			$instance['username'] = ( !empty($new_instance['username']) )? strip_tags($new_instance['username']) : '';
			$instance['list_name'] = ( !empty($new_instance['list_name']) )? strip_tags($new_instance['list_name']) : '';
			$instance['number_tweets'] = ( !empty($new_instance['number_tweets']) )? strip_tags($new_instance['number_tweets']) : '';
			$instance['include_retweets'] = ( !empty($new_instance['include_retweets']) )? strip_tags($new_instance['include_retweets']) : '';
			return $instance;
		}
	}

	/*==========================
	 Video
	 ===========================*/
	class nebula_video extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_video', 'Nebula - Video', array('description' => 'Simply add a Youtube or Vimeo video using their native interface (without related videos) with Google Analytics tracking enabled.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Video Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			if ( isset($instance['video_id']) ){
				//Determine if Youtube or Vimeo (may need user input)
				if ( $instance['video_provider'] === 'youtube' ){
					$youtube_data = nebula()->video_meta('youtube', $instance['video_id']);
					?>
					<div class="ratio ratio-16x9">
						<iframe class="youtube" src="//www.youtube.com/embed/<?php echo $instance['video_id']; ?>?wmode=transparent&enablejsapi=1&rel=0" width="560" height="315" title="<?php echo $instance['title']; ?>" loading="lazy"></iframe>
					</div>
					<?php
				} else {
					$vimeo_data = nebula()->video_meta('vimeo', '208432684');
					?>
					<div class="ratio ratio-16x9">
						<iframe id="<?php echo $instance['video_id']; ?>" class="vimeo" src="https://player.vimeo.com/video/<?php echo $instance['video_id']; ?>" width="560" height="315" title="<?php echo $instance['title']; ?>" loading="lazy"></iframe>
					</div>
					<?php
				}
			}

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<p>
					<label for="<?php echo $this->get_field_id('video_provider'); ?>">Provider</label>
					<select id="<?php echo $this->get_field_id('video_provider'); ?>" name="<?php echo $this->get_field_name('video_provider'); ?>" class="widefat" style="width:100%;">
						<option <?php selected( $instance['video_provider'], 'youtube'); ?> value="youtube">Youtube</option>
						<option <?php selected( $instance['video_provider'], 'vimeo'); ?> value="vimeo">Vimeo</option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('video_id'); ?>">Video ID</label>
					<input class="widefat" id="<?php echo $this->get_field_id('video_id'); ?>" name="<?php echo $this->get_field_name('video_id'); ?>" type="text" value="<?php echo ( isset($instance['video_id']) )? $instance['video_id'] : ''; ?>" />
				</p>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['video_provider'] = ( !empty($new_instance['video_provider']) )? strip_tags($new_instance['video_provider']) : '';
			$instance['video_id'] = ( !empty($new_instance['video_id']) )? strip_tags($new_instance['video_id']) : '';
			return $instance;
		}
	}

	/*==========================
	 Advanced Custom Fields
	 Only available when ACF v5 is active. This echoes each field that is assigned to the widget from the Custom Fields settings.
	 ===========================*/
	class nebula_acf extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_acf', 'Nebula - Advanced Custom Fields', array('description' => 'Integrate ACF fields without hijacking other widgets.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			$timer_name = nebula()->timer('Nebula ACF Widget', 'start', 'Nebula Widgets');
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			$acf_fields = get_fields('widget_' . $args['widget_id']);
			foreach ( $acf_fields as $acf_field ): ?>
				<div class="row">
					<div class="col">
						<?php echo $acf_field; ?>
					</div><!--/col-->
				</div><!--/row-->
			<?php endforeach;

			//After widget arguments are defined by themes
			echo $args['after_widget'];
			nebula()->timer($timer_name, 'end');
		}

		public function form($instance){
			echo '<p>Note: This widget may not properly update in the Customizer preview. Try saving and refreshing to view changes.</p>';
		}
	}
}