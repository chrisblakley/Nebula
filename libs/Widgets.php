<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Widgets') ){
	trait Widgets {
		public function hooks(){
			add_action('widgets_init', array($this, 'load_nebula_widgets'));
		}

		public function load_nebula_widgets(){
			register_widget('nebula_video');
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