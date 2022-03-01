<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

trait Comments {
	public function hooks(){
		if ( !$this->get_option('comments') || $this->get_option('disqus_shortname') ){ //If WP core comments are disabled -or- if Disqus is enabled
			global $pagenow;

			add_action('wp_dashboard_setup', array($this, 'remove_activity_metabox'));
			add_filter('manage_posts_columns', array($this, 'remove_pages_count_columns'));
			add_filter('manage_pages_columns', array($this, 'remove_pages_count_columns'));
			add_filter('manage_media_columns', array($this, 'remove_pages_count_columns'));
			add_filter('comments_open', '__return_false', 20, 2); //Entirely disable comments (including via REST API)
			add_filter('pings_open', '__return_false', 20, 2);

			if ( $this->get_option('admin_bar') ){
				add_action('admin_bar_menu', array($this, 'admin_bar_remove_comments' ), 900);
			}

			add_action('admin_menu', array($this, 'disable_comments_admin'));
			add_filter('admin_head', array($this, 'hide_ataglance_comment_counts'));
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
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
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

	public function hide_ataglance_comment_counts(){
		if ( $this->get_option('comments') ){
			echo '<style>li.comment-count, li.comment-mod-count {display: none;}</style>'; //Hide comment counts in "At a Glance" metabox
		}
	}

	//Disable support for comments in post types
	public function remove_comments_post_type_support(){
		foreach ( get_post_types() as $post_type ){
			if ( post_type_supports($post_type, 'comments') ){
				remove_post_type_support($post_type, 'comments');
			}
		}
	}

	//Add Disqus to preconnect optimizations
	public function disqus_preconnect($default_preconnects){
		if ( is_single() && $this->get_option('comments') ){
			$default_preconnects[] = '//' . $this->get_option('disqus_shortname') . '.disqus.com';
		}

		return $default_preconnects;
	}

	//Link to Disqus on comments page (if using Disqus)
	public function disqus_link($nebula_warnings){
		$nebula_warnings['disqus'] = array(
			'level' => 'info',
			'description' => '<i class="fa-brands fa-fw fa-php"></i> You are using the Disqus commenting system. <a href="https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate" target="_blank" rel="noopener">View the comment listings on Disqus &raquo;</a>',
			'url' => 'https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate',
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