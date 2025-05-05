<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

trait Comments {
	public function hooks(){
		if ( !$this->get_option('comments') || $this->get_option('disqus_shortname') ){ //If WP core comments are disabled -or- if Disqus is enabled
			global $pagenow;

			add_filter('wp_count_comments', array($this, 'disable_wp_count_comments'), 10, 2);

			add_action('wp_dashboard_setup', array($this, 'remove_activity_metabox'));
			add_filter('manage_posts_columns', array($this, 'remove_pages_count_columns'));
			add_filter('manage_pages_columns', array($this, 'remove_pages_count_columns'));
			add_filter('manage_media_columns', array($this, 'remove_pages_count_columns'));
			add_filter('comments_open', '__return_false', 20, 2); //Entirely disable comments (including via REST API)
			add_filter('pings_open', '__return_false', 20, 2);
			add_filter('comments_array', '__return_empty_array', 10, 2);

			add_filter('wp_before_admin_bar_render', array($this, 'remove_comments_admin_bar_node'));
			add_filter('rest_endpoints', array($this, 'remove_comments_rest_endpoint'));

			if ( $this->get_option('admin_bar') ){
				add_action('admin_bar_menu', array($this, 'admin_bar_remove_comments' ), 900);
			}

			add_action('admin_menu', array($this, 'disable_comments_admin'));
			add_filter('admin_head', array($this, 'hide_ataglance_comment_counts'));
			add_filter('dashboard_glance_items', array($this, 'remove_comments_from_dashboard_glance'));
			add_action('admin_init', array($this, 'remove_comments_post_type_support'));

			if ( $pagenow === 'edit-comments.php' && $this->get_option('disqus_shortname') ){
				add_action('nebula_warnings', array($this, 'disqus_link'));
			}

			if ( $this->get_option('disqus_shortname') ){
				add_filter('nebula_preconnect', array($this, 'disqus_preconnect'));
			}
		} else { //If WP core comments are enabled
			add_action('comment_form_before', array($this, 'enqueue_comments_reply'));
			add_action('wp_head', array($this, 'comment_author_cookie'));
		}
	}

	//Remove the Activity metabox
	public function remove_activity_metabox(){
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
		unregister_widget('WP_Widget_Recent_Comments');
	}

	//Remove Comments column
	public function remove_pages_count_columns($defaults){
		unset($defaults['comments']);
		return $defaults;
	}

	//Remove comments menu from Admin Bar
	public function admin_bar_remove_comments($wp_admin_bar){
		$wp_admin_bar->remove_menu('comments');
	}

	//Remove comments metabox and comments
	public function disable_comments_admin(){
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		remove_menu_page( 'edit-comments.php' );
		//Note: Do not remove the Discussion settings page. The comment blocklist is still used for other things like CF7 forms.
	}

	//Completely remove the comment count bullet from the "At A Glance" dashboard metabox
	public function remove_comments_from_dashboard_glance($items){
		$items = array_filter($items, function($item){
			return !str_contains(strtolower($item), 'comment'); //Removes any list item that contains 'comment' (case-insensitive)
		});

		return $items;
	}

	//Hide the bullet point for comment counts in the "At A Glance" dashboard, but note that this does not prevent DB queries related to comment counts (see below)
	public function hide_ataglance_comment_counts(){
		if ( $this->get_option('comments') ){
			echo '<style>li.comment-count, li.comment-mod-count {display: none;}</style>'; //Hide comment counts in "At a Glance" metabox
		}
	}

	//Override the WP comment count function to prevent DB queries
	//This will query once to ensure the object key names match (to prevent errors), but subsequent queries will be prevented
	public function disable_wp_count_comments($counts, $post_id){
		$default_keys = wp_cache_get('nebula_comment_count_keys');

		if ( $default_keys === false ){
			remove_filter('wp_count_comments', array($this, 'disable_wp_count_comments'), 10); //Temporarily remove this filter to avoid recursion
			$default = wp_count_comments($post_id); //Call the original function unfiltered to obtain the object key names
			add_filter('wp_count_comments', array($this, 'disable_wp_count_comments'), 10, 2); //Re-add the filter so future calls will be prevented using the obtained key names

			$default_keys = array_keys((array) $default);
			wp_cache_set('nebula_comment_count_keys', $default_keys); //Store the key names in persistent object cache so future page loads won't need even the original query
		}

		$new_counts = array();
		foreach ( $default_keys as $key ){
			$new_counts[$key] = 0;
		}

		return (object) $new_counts;
	}

	//Disable support for comments in post types
	public function remove_comments_post_type_support(){
		foreach ( get_post_types() as $post_type ){
			if ( post_type_supports($post_type, 'comments') ){
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}

	public function remove_comments_admin_bar_node(){
		global $wp_admin_bar;
		$wp_admin_bar->remove_node('comments');
	}

	public function remove_comments_rest_endpoint($endpoints){
		unset($endpoints['/wp/v2/comments']);
		return $endpoints;
	}

	//Add Disqus to preconnect optimizations
	public function disqus_preconnect($default_preconnects){
		if ( is_single() && $this->get_option('comments') ){
			$default_preconnects[] = '//' . $this->get_option('disqus_shortname', '') . '.disqus.com';
		}

		return $default_preconnects;
	}

	//Link to Disqus on comments page (if using Disqus)
	public function disqus_link($nebula_warnings){
		$nebula_warnings['disqus'] = array(
			'level' => 'info',
			'description' => '<i class="fa-brands fa-fw fa-php"></i> You are using the Disqus commenting system. <a href="https://' . $this->get_option('disqus_shortname', '') . '.disqus.com/admin/moderate" target="_blank" rel="noopener">View the comment listings on Disqus &raquo;</a>',
			'url' => 'https://' . $this->get_option('disqus_shortname', '') . '.disqus.com/admin/moderate',
			'meta' => array('target' => '_blank', 'rel' => 'noopener')
		);

		return $nebula_warnings;
	}

	//Enqueue threaded comments script only as needed
	public function enqueue_comments_reply(){
		if ( get_option('thread_comments') ){
			wp_enqueue_script('comment-reply');
		}
	}

	//Prefill form fields with comment author cookie
	public function comment_author_cookie(){
		if ( $this->get_option('comments') ){
			echo '<script>';
				echo 'cookieAuthorName = "";';
				echo 'cookieAuthorEmail = "";';

				if ( isset($this->super->cookie['comment_author_' . COOKIEHASH]) ){
					echo 'cookieAuthorName = "' . $this->super->cookie['comment_author_' . COOKIEHASH] . '";';
					echo 'cookieAuthorEmail = "' . $this->super->cookie['comment_author_email_' . COOKIEHASH] . '";';
				}
			echo '</script>';
		}
	}

	//Comments post meta
	public function post_comments($options=array()){
		$defaults = array(
			'icon' => true, //Show icon
			'linked' => true, //Link to comment
			'empty' => true, //Show if 0 comments
			'force' => false
		);

		$data = array_merge($defaults, $options);

		if ( get_theme_mod('post_comment_count', true) || $data['force'] ){
			$comment_show = '';
			$comments_text = 'Comments';

			if ( get_comments_number() == 0 ){
				$comment_icon = 'fa-regular fa-comment';
				$comment_show = ( $data['empty'] )? '' : 'hidden'; //If comment link should show if no comments. True = show, False = hidden
			} elseif ( get_comments_number() == 1 ){
				$comment_icon = 'fa-solid fa-comment';
				$comments_text = 'Comment';
			} elseif ( get_comments_number() > 1 ){
				$comment_icon = 'fa-solid fa-comments';
			}

			$the_icon = '';
			if ( $data['icon'] ){
				$the_icon = '<i class="fa-fw ' . $comment_icon . '"></i> ';
			}

			if ( $data['linked'] ){
				$postlink = ( is_single() )? '' : get_the_permalink();
				return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . '<a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
			} else {
				return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . get_comments_number() . ' ' . $comments_text . '</span>';
			}
		}
	}
}