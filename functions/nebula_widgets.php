<?php

	//Nebula - About the Author
	add_action('widgets_init', 'register_nebula_about_the_author_widget');
	function register_nebula_about_the_author_widget(){
		return register_widget("nebula_about_the_author");
	}

	class nebula_about_the_author extends WP_Widget {
		function nebula_about_the_author(){ //Constructor - name this the same as the class above
			$name = 'Nebula - About the Author';
			$description = 'Display author bio on single posts. Be sure to enable the Author Bios function in <a href="themes.php?page=nebula_options">Nebula Options</a>!';
			parent::WP_Widget(false, $name, array('description' => $description));
		}

		function widget($args, $instance){ //WP_Widget::widget - do not rename this
			extract($args);
			?>


			<?php if ( is_single() && nebula_option('author_bios', 'enabled') ) : ?>
				<?php echo $before_widget; ?>

				<?php echo $before_title . '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . get_the_author_meta('display_name') . $after_title; ?>
				<?php if ( get_the_author_meta('headshot_url') ) : ?>
					<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><img class="nebula-author-bio-thumbnail" src="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" /></a>
				<?php endif; ?>
				<p>This widget is in development.</p>
				<?php
					//Get/echo author name (and link to author archive)
					//Get author bio
				?>

				<?php echo $after_widget; ?>
			<?php endif; ?>


			<?php
		}

		function update($new_instance, $old_instance){ //WP_Widget::update - do not rename this
			$instance = $old_instance;
			return $instance;
		}

		function form($instance){ //WP_Widget::form - do not rename this
			echo "<p>This widget has no form fields.</p>";
		}

	} //End Nebula - About the Author



	//@TODO "Nebula" 0: Nebula Search Widget - pull existing one from sidebar

	//@TODO "Nebula" 0: Nebula Shortcode Widget? User could use the Text widget... anything special we could do for a shortcode-specific widget? Maybe easier instructions?

	//@TODO "Nebula" 0: Share post widget (checkbox for which social network to include, checkbox for bubble counts)

	//@TODO "Nebula" 0: Twitter feed


	//Nebula - Testing Widget
	//add_action('widgets_init', 'register_example_widget');
	function register_example_widget(){
		return register_widget("example_widget");
	}

	class example_widget extends WP_Widget {
		function example_widget(){ //Constructor - name this the same as the class above
			$name = 'Nebula - Testing Widget';
			$description = 'This widget is a template for testing new custom widgets.';
			parent::WP_Widget(false, $name, array('description' => $description));
		}

		function widget($args, $instance){ //WP_Widget::widget - do not rename this
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
			$message = $instance['message'];
			?>


			<?php echo $before_widget; ?>

			<?php if ( $title ) : ?>
				<?php echo $before_title . $title . $after_title; ?>
			<?php endif; ?>

			<ul>
				<li><?php echo $message; ?></li>
			</ul>

			<?php echo $after_widget; ?>


			<?php
		}

		function update($new_instance, $old_instance){ //WP_Widget::update - do not rename this
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['message'] = strip_tags($new_instance['message']);
			return $instance;
		}

		function form($instance){ //WP_Widget::form - do not rename this

			$title = esc_attr($instance['title']);
			$message = esc_attr($instance['message']);
			?>

			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('message'); ?>">Simple Message:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" type="text" value="<?php echo $message; ?>" />
			</p>

			<?php
		}

	} //End Nebula - Testing Widget