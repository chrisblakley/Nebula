<?php

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
			register_widget('nebula_linked_image');
			register_widget('nebula_login_form');
			register_widget('nebula_social_page_links');
			register_widget('nebula_social_sharing');
			register_widget('nebula_video');
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
			$user_id = ( !empty($instance['user_id']) )? $instance['user_id'] : get_the_author_id();
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
									<img src="<?php echo $author_image; ?>" />
								<?php endif; ?>

								<div>
									<?php if ( !empty($instance['title']) ): ?>
										<h3><?php echo $instance['title']; ?></h3>
									<?php else: ?>
										<h3><?php echo ( !empty($author_info->first_name) )? 'About ' . $author_info->first_name : 'About the Author'; ?></h3>
									<?php endif; ?>

									<?php
										$job_title_location = array();
										$job_title_location[] = ( !empty($instance['show_job_info']) )? get_user_meta($user_id, 'jobtitle', true) . ' at ' . get_user_meta($user_id, 'jobcompany', true) : false; //@todo "Nebula" 0: Link the company website
										$job_title_location[] = ( !empty($instance['show_location']) )? get_user_meta($user_id, 'usercity', true) . ', ' . get_user_meta($user_id, 'userstate', true) : false;
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
								'facebook-official' => ( get_user_meta($user_id, 'facebook', true) )? 'https://www.facebook.com/' . get_user_meta($user_id, 'facebook', true) : false,
								'twitter' => ( get_user_meta($user_id, 'twitter', true) )? nebula()->twitter_url(get_user_meta($user_id, 'twitter', true)) : false,
								'instagram' => ( get_user_meta($user_id, 'instagram', true) )? 'https://www.instagram.com/' . get_user_meta($user_id, 'instagram', true) : false,
								'linkedin-square' => ( get_user_meta($user_id, 'instagram', true) )? 'https://www.linkedin.com/' . get_user_meta($user_id, 'instagram', true) : false,
								'google-plus' => ( get_user_meta($user_id, 'googleplus', true) )? 'https://plus.google.com/' . get_user_meta($user_id, 'googleplus', true) : false,
								'pinterest' => ( get_user_meta($user_id, 'pinterest', true) )? 'https://www.pinterest.com/' . get_user_meta($user_id, 'pinterest', true) : false,
								'youtube-play' => ( get_user_meta($user_id, 'youtube', true) )? 'https://www.youtube.com/user/' . get_user_meta($user_id, 'youtube', true) : false,
								'envelope-o' => 'mailto:' . $author_info->user_email
							);
							$author_social = array_filter($author_social);
						?>
						<?php if ( !empty($author_social) ): ?>
							<div class="row">
								<div class="col">
									<ul class="author-social">
										<?php foreach ( $author_social as $icon => $url ): ?>
											<li><a href="<?php echo $url; ?>" target="_blank"><i class="fa fa-fw fa-<?php echo $icon; ?>"></i></a></li>
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
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<div>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
					<span class="nebula-help-text">Leaving this empty will display the author's first name (if available) or fallback to "About the Author".</span>
				</div>

				<div>
					<label for="<?php echo $this->get_field_id('user_id'); ?>">User ID</label>
					<input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>" type="text" value="<?php echo ( isset($instance['user_id']) )? $instance['user_id'] : ''; ?>" />
					<span class="nebula-help-text">This will override the author detection to force a specific user's bio.</span>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['show_job_info'], 'on'); ?> id="<?php echo $this->get_field_id('show_job_info'); ?>" name="<?php echo $this->get_field_name('show_job_info'); ?>" />
				    <label for="<?php echo $this->get_field_id('show_job_info'); ?>"> Show Job Title and Company</label>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['show_location'], 'on'); ?> id="<?php echo $this->get_field_id('show_location'); ?>" name="<?php echo $this->get_field_name('show_location'); ?>" />
				    <label for="<?php echo $this->get_field_id('show_location'); ?>"> Show Location</label>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['show_social_links'], 'on'); ?> id="<?php echo $this->get_field_id('show_social_links'); ?>" name="<?php echo $this->get_field_name('show_social_links'); ?>" />
				    <label for="<?php echo $this->get_field_id('show_social_links'); ?>"> Show Social Media Links</label>
				</div>

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
	 Image (with Link)
	 ===========================*/
	class nebula_linked_image extends WP_Widget {
		function __construct(){
			parent::__construct('nebula_linked_image', 'Nebula - Linked Image', array('description' => 'Add an image with an optional link.'));
		}

		//Creating widget front-end
		public function widget($args, $instance){
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<?php if ( !empty($instance['image']) ): ?>
					<?php if ( !empty($instance['url']) ): ?>
						<a href="<?php echo $instance['url']; ?>" <?php echo ( nebula()->url_components('hostname') != nebula()->url_components('hostname', $instance['url']) )? 'target="_blank"' : ''; //Check for external URL ?>>
					<?php endif; ?>
							<img src="<?php echo $instance['image']; ?>" />
					<?php if ( !empty($instance['url']) ): ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
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
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			?>
				<?php if ( !empty($instance['title']) ): ?>
					<h3><?php echo $instance['title']; ?></h3>
				<?php endif; ?>

				<?php if ( is_user_logged_in() ): ?>
					<?php $current_user = wp_get_current_user(); ?>
					<p><?php echo ( $instance['greeting'] )? $instance['greeting'] : 'Welcome back'; ?>, <strong><?php echo $current_user->display_name; ?></strong>! <a class="nowrap" href="<?php echo wp_logout_url(get_permalink() . '?no-cache'); ?>"><i class="fa fa-sign-out"></i> Logout</a></p>
				<?php else: ?>
					<form id="loginform" class="nebula-login-form" name="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
						<div class="form-group login-username">
							<label class="form-control-label" for="user_login"><i class="fa fa-fw fa-user"></i> Username or Email Address</label>
							<input id="user_login" class="input form-control" type="text" name="log" placeholder="Username" />
						</div>
						<div class="form-group login-password">
							<label class="form-control-label" for="user_pass"><i class="fa fa-fw fa-lock"></i> Password</label>
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
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			$social_pages = array(
				'facebook-official' => ( nebula()->get_option('facebook_url') )? nebula()->get_option('facebook_url') : false,
				'twitter' => ( nebula()->get_option('twitter_username') )? nebula()->twitter_url() : false,
				'instagram' => ( nebula()->get_option('instagram_url') )? nebula()->get_option('instagram_url') : false,
				'linkedin-square' => ( nebula()->get_option('linkedin_url') )? nebula()->get_option('linkedin_url') : false,
				'google-plus' => ( nebula()->get_option('google_plus_url') )? nebula()->get_option('google_plus_url') : false,
				'pinterest' => ( nebula()->get_option('pinterest_url') )? nebula()->get_option('pinterest_url') : false,
				'youtube-play' => ( nebula()->get_option('youtube_url') )? nebula()->get_option('youtube_url') : false,
				'envelope-o' => ( nebula()->get_option('contact_email') )? nebula()->get_option('contact_email') : false,
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
									<li><a href="<?php echo $url; ?>" target="_blank"><i class="fa fa-fw fa-<?php echo $icon; ?>"></i></a></li>
								<?php endforeach; ?>
							</ul>
						</div><!--/col-->
					</div><!--/row-->
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
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
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			$social_networks = array();
			$social_networks[] = ( !empty($instance['facebook']) )? 'facebook' : false;
			$social_networks[] = ( !empty($instance['twitter']) )? 'twitter' : false;
			$social_networks[] = ( !empty($instance['googleplus']) )? 'googleplus' : false;
			$social_networks[] = ( !empty($instance['linkedin']) )? 'linkedin' : false;
			$social_networks[] = ( !empty($instance['pinterest']) )? 'pinterest' : false;
			$social_networks = array_filter($social_networks);
			?>
				<?php if ( !empty($social_networks) ): ?>
					<div class="row">
						<div class="col">
							<?php if ( !empty($instance['title']) ): ?>
								<h3><?php echo $instance['title']; ?></h3>
							<?php endif; ?>

							<?php echo nebula()->social($social_networks, nebula()->is_staff()); ?>
						</div><!--/col-->
					</div><!--/row-->
				<?php endif; ?>
			<?php

			//After widget arguments are defined by themes
			echo $args['after_widget'];
		}

		//Widget Backend (admin form)
		public function form($instance){
			?>
				<div>
					<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ( isset($instance['title']) )? $instance['title'] : ''; ?>" />
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['facebook'], 'on'); ?> id="<?php echo $this->get_field_id('facebook'); ?>" name="<?php echo $this->get_field_name('facebook'); ?>" />
				    <label for="<?php echo $this->get_field_id('facebook'); ?>"> Facebook</label>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['twitter'], 'on'); ?> id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" />
				    <label for="<?php echo $this->get_field_id('twitter'); ?>"> Twitter</label>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['googleplus'], 'on'); ?> id="<?php echo $this->get_field_id('googleplus'); ?>" name="<?php echo $this->get_field_name('googleplus'); ?>" />
				    <label for="<?php echo $this->get_field_id('googleplus'); ?>"> Google+</label>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['linkedin'], 'on'); ?> id="<?php echo $this->get_field_id('linkedin'); ?>" name="<?php echo $this->get_field_name('linkedin'); ?>" />
				    <label for="<?php echo $this->get_field_id('linkedin'); ?>"> LinkedIn</label>
				</div>

				<div>
				    <input class="checkbox" type="checkbox" <?php checked($instance['pinterest'], 'on'); ?> id="<?php echo $this->get_field_id('pinterest'); ?>" name="<?php echo $this->get_field_name('pinterest'); ?>" />
				    <label for="<?php echo $this->get_field_id('pinterest'); ?>"> Pinterest</label>
				</div>
			<?php
		}

		//Updating widget replacing old instances with new
		public function update($new_instance, $old_instance){
			$instance = array();
			$instance['title'] = ( !empty($new_instance['title']) )? strip_tags($new_instance['title']) : '';
			$instance['facebook'] = ( !empty($new_instance['facebook']) )? strip_tags($new_instance['facebook']) : '';
			$instance['twitter'] = ( !empty($new_instance['twitter']) )? strip_tags($new_instance['twitter']) : '';
			$instance['googleplus'] = ( !empty($new_instance['googleplus']) )? strip_tags($new_instance['googleplus']) : '';
			$instance['linkedin'] = ( !empty($new_instance['linkedin']) )? strip_tags($new_instance['linkedin']) : '';
			$instance['pinterest'] = ( !empty($new_instance['pinterest']) )? strip_tags($new_instance['pinterest']) : '';
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
			//Before widget arguments are defined by themes
			echo $args['before_widget'];

			if ( isset($instance['video_id']) ){
				//Determine if Youtube or Vimeo (may need user input)
				if ( $instance['video_provider'] === 'youtube' ){
					$youtube_data = nebula()->video_meta('youtube', $instance['video_id']);
					?>
					<div class="embed-responsive embed-responsive-16by9">
					    <iframe class="youtube embed-responsive-item" src="//www.youtube.com/embed/<?php echo $instance['video_id']; ?>?wmode=transparent&enablejsapi=1&rel=0" width="560" height="315"></iframe>
					</div>
					<?php
				} else {
					$vimeo_data = nebula()->video_meta('vimeo', '208432684');
					?>
					<div class="embed-responsive embed-responsive-16by9">
					    <iframe id="<?php echo $instance['video_id']; ?>" class="vimeo embed-responsive-item" src="https://player.vimeo.com/video/<?php echo $instance['video_id']; ?>" width="560" height="315"></iframe>
					</div>
					<?php
				}
			}

			//After widget arguments are defined by themes
			echo $args['after_widget'];
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

}