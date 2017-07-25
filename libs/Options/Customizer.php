<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

trait Customizer {
	public function hooks(){
		add_action('customize_register', array($this, 'customize_register'));
		add_action('customize_save_after', array($this, 'customize_sass_render'));

		add_action('wp_head', array($this, 'customizer_style_overrides'), 100);
	}

	//Render Sass on Customizer Save
	public function customize_sass_render(){
		$this->update_data('need_sass_compile', 'true');
	}

	//Register WordPress Customizer
	public function customize_register($wp_customize){
		/*==========================
			Brand Panel
		 ===========================*/

		$wp_customize->add_panel('brand', array(
			'priority' => 10,
			'title' => 'Brand',
			'description' => 'Brand and other colors',
		));

		$wp_customize->get_section('title_tagline')->panel = 'brand';

		//@todo "Nebula" 0: Get an edit icon to appear on the logo for custom_logo option

		/*==========================
			Brand Colors Section
		 ===========================*/

		$wp_customize->add_section('colors', array(
			'title' => 'Colors',
			'priority' => 50,
			'panel'  => 'brand',
		));

		//Primary color
		$wp_customize->add_setting('nebula_primary_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_primary_color', array(
			'label' => 'Primary Color',
			'section' => 'colors',
			'priority' => 10
		)));

		//Secondary color
		$wp_customize->add_setting('nebula_secondary_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_secondary_color', array(
			'label' => 'Secondary Color',
			'section' => 'colors',
			'priority' => 20
		)));

		//Background color
		$wp_customize->add_setting('nebula_background_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_background_color', array(
			'label' => 'Background Color',
			'section' => 'colors',
			'priority' => 30
		)));
		$wp_customize->selective_refresh->add_partial('nebula_background_color', array( //This doesn't appear to do anything.
			'settings' => array('nebula_background_color'),
			'selector' => 'body',
			'container_inclusive' => false,
		));

/*
		//Hero Navigation Scheme
		$wp_customize->add_setting('hero_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('hero_nav_scheme', array(
		    'label' => 'Hero Navigation Scheme',
		    'section' => 'colors',
		    'priority' => 40,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    )
		));

		//Header Navigation Scheme
		$wp_customize->add_setting('header_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('header_nav_scheme', array(
		    'label' => 'Subpage Header Navigation Scheme',
		    'section' => 'colors',
		    'priority' => 41,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    )
		));

		//Footer Widget Area Navigation Scheme
		$wp_customize->add_setting('fwa_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('fwa_nav_scheme', array(
		    'label' => 'Footer Widget Area Navigation Scheme',
		    'section' => 'colors',
		    'priority' => 42,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    )
		));

		//Footer Navigation Scheme
		$wp_customize->add_setting('footer_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('footer_nav_scheme', array(
		    'label' => 'Footer Navigation Scheme',
		    'section' => 'colors',
		    'priority' => 43,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    )
		));
*/


		/*==========================
			Site Features Section
		 ===========================*/

		$wp_customize->add_section('site_features', array(
			'title' => 'Site Features',
			'priority' => 15,
		));

		//Menu Position
		$wp_customize->add_setting('menu_position', array('default' => 'absolute'));
		$wp_customize->add_control('menu_position', array(
		    'label' => 'Menu Position',
		    'section' => 'site_features',
		    'priority' => 10,
		    'type' => 'select',
		    'choices' => array(
		        'absolute' => 'Over Header',
		        'relative' => 'Normal',
		    )
		));

		//Sticky Nav
		$wp_customize->add_setting('sticky_nav', array('default' => 0));
		$wp_customize->add_control('sticky_nav', array(
			'label' => 'Use Sticky Nav',
			'section' => 'site_features',
			'priority' => 15,
			'type' => 'checkbox',
		));

		//Offcanvas Menu
		//@TODO "Nebula" 0: Ideally need to dequeue Mmenu and change the dependencies on main.js if the user unchecks this one
		$wp_customize->add_setting('nebula_offcanvas_menu', array('default' => 1));
		$wp_customize->add_control('nebula_offcanvas_menu', array(
			'label' => 'Show Offcanvas Menu (Mobile)',
			'section' => 'site_features',
			'priority' => 35,
			'type' => 'checkbox',
		));
		$wp_customize->selective_refresh->add_partial('nebula_offcanvas_menu', array(
			'settings' => array('nebula_offcanvas_menu'),
			'selector' => '#mobilenavtrigger',
			'container_inclusive' => false,
		));

		//Mobile Search
		$wp_customize->add_setting('nebula_mobile_search', array('default' => 1));
		$wp_customize->add_control('nebula_mobile_search', array(
			'label' => 'Show Mobile Search',
			'section' => 'site_features',
			'priority' => 36,
			'type' => 'checkbox',
		));
		$wp_customize->selective_refresh->add_partial('nebula_mobile_search', array(
			'settings' => array('nebula_mobile_search'),
			'selector' => '#mobileheadersearch',
			'container_inclusive' => false,
		));


		/*==========================
			Home Panel
		 ===========================*/

		$wp_customize->add_panel('home', array(
			'priority' => 20,
			'title' => 'Home',
			'description' => 'Home page settings',
		));

		$wp_customize->get_section('static_front_page')->panel = 'home';

		/*==========================
			Home Hero Section
		 ===========================*/

		$wp_customize->add_section('hero', array(
			'title' => 'Hero',
			'panel' => 'home',
			'priority' => 500,
		));

		//Hero Navigation Scheme
		$wp_customize->add_setting('hero_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('hero_nav_scheme', array(
		    'label' => 'Hero Navigation Scheme',
		    'section' => 'hero',
		    'priority' => 20,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    )
		));

		//Hero header in front page
		$wp_customize->add_setting('nebula_hero', array('default' => 1));
		$wp_customize->add_control('nebula_hero', array(
			'label' => 'Show Hero Section',
			'section' => 'hero',
			'priority' => 29,
			'type' => 'checkbox',
			'active_callback' => 'is_front_page', //Only show on Front Page
		));

		//Hero BG Image
		$wp_customize->add_setting('nebula_hero_bg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_hero_bg_image', array(
			'label' => 'Hero Background Image',
			'description' => 'Using an optimized .jpg is strongly recommended!',
			'section' => 'hero',
			'settings' => 'nebula_hero_bg_image',
			'priority' => 30,
			'active_callback' => 'is_front_page',
		)));
		$wp_customize->selective_refresh->add_partial('nebula_hero_bg_image', array(
			'settings' => array('nebula_hero_bg_image'),
			'selector' => '#hero-section',
			'container_inclusive' => false,
		));

		//Hero Overlay Color
		$wp_customize->add_setting('nebula_hero_overlay_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_hero_overlay_color', array(
			'label' => 'Hero BG Overlay Color',
			'section' => 'hero',
			'priority' => 32,
			'active_callback' => 'is_front_page',
		)));

		//Hero Overlay Opacity
		$wp_customize->add_setting('nebula_hero_overlay_opacity', array('default' => '0.6'));
		$wp_customize->add_control('nebula_hero_overlay_opacity', array(
			'type' => 'number',
			'input_attrs' => array(
		        'min' => 0,
		        'max' => 1,
		        'step' => 0.1,
		    ),
		    'label' => 'Hero BG Overlay Opacity',
			'description' => 'Enter a value between 0 (transparent) and 1 (opaque). Default: 0.6',
			'section' => 'hero',
			'priority' => 33,
			'active_callback' => 'is_front_page',
		));

		//Hero Site Title
		$wp_customize->add_setting('nebula_show_hero_title', array('default' => 1));
		$wp_customize->add_control('nebula_show_hero_title', array(
			'label' => 'Show Hero Title',
			'section' => 'hero',
			'priority' => 34,
			'type' => 'checkbox',
			'active_callback' => 'is_front_page',
		));

		//Custom Hero Title
		$wp_customize->add_setting('nebula_hero_custom_title', array('default' => null));
		$wp_customize->add_control('nebula_hero_custom_title', array(
			'label' => 'Custom Hero Title',
			'description' => 'Customize the H1 text instead of using the site title',
			'section' => 'hero',
			'priority' => 35,
			'active_callback' => 'is_front_page',
		));

		//Hero Site Description
		$wp_customize->add_setting('nebula_show_hero_description', array('default' => 1));
		$wp_customize->add_control('nebula_show_hero_description', array(
			'label' => 'Show Hero Description',
			'section' => 'hero',
			'priority' => 36,
			'type' => 'checkbox',
			'active_callback' => 'is_front_page',
		));

		//Hero Description Text
		$wp_customize->add_setting('nebula_hero_custom_description', array('default' => null));
		$wp_customize->add_control('nebula_hero_custom_description', array(
			'label' => 'Custom Hero Description',
			'description' => 'Customize the description text instead of using the site tagline',
			'section' => 'hero',
			'priority' => 37,
			'active_callback' => 'is_front_page',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_custom_description', array(
			'settings' => array('nebula_hero_custom_description'),
			'selector' => '#hero-section h2',
			'container_inclusive' => false,
		));

		//Hero Text Color
		$wp_customize->add_setting('nebula_hero_text_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_hero_text_color', array(
			'label' => 'Hero Text Color',
			'section' => 'hero',
			'priority' => 38,
			'active_callback' => 'is_front_page',
		)));

		//Hero Search
		$wp_customize->add_setting('nebula_hero_search', array('default' => 1));
		$wp_customize->add_control('nebula_hero_search', array(
			'label' => 'Show Hero Search',
			'description' => 'Add an autocomplete search field to your hero section',
			'section' => 'hero',
			'priority' => 39,
			'type' => 'checkbox',
			'active_callback' => 'is_front_page',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_search', array(
			'settings' => array('nebula_hero_search'),
			'selector' => '#hero-section #nebula-hero-formcon',
			'container_inclusive' => false,
		));

		//Hero FG Image
		$wp_customize->add_setting('nebula_hero_fg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_hero_fg_image', array(
			'label' => 'Hero Foreground Image',
			'section' => 'hero',
			'settings' => 'nebula_hero_fg_image',
			'priority' => 42,
			'active_callback' => 'is_front_page',
		)));
		$wp_customize->selective_refresh->add_partial('nebula_hero_fg_image', array(
			'settings' => array('nebula_hero_fg_image'),
			'selector' => '#hero-section img',
			'container_inclusive' => false,
		));

		//Hero FG Image Link
		$wp_customize->add_setting('nebula_hero_fg_image_link', array('default' => null));
		$wp_customize->add_control('nebula_hero_fg_image_link', array(
			'label' => 'Hero Foreground Image Link',
			'section' => 'hero',
			'priority' => 43,
			'active_callback' => 'is_front_page',
		));

		//Hero Youtube
		$wp_customize->add_setting('nebula_hero_youtube_id', array('default' => null));
		$wp_customize->add_control('nebula_hero_youtube_id', array(
			'label' => 'Hero Youtube Video ID',
			'description' => 'The ID of a Youtube video to embed in the hero section',
			'section' => 'hero',
			'priority' => 44,
			'active_callback' => 'is_front_page',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_youtube_id', array(
			'settings' => array('nebula_hero_youtube_id'),
			'selector' => '#hero-section iframe',
			'container_inclusive' => false,
		));

		//CTA Button 1 Text
		//@todo "Nebula" 0: How to allow for Font Awesome icons here?
		$wp_customize->add_setting('nebula_hero_cta_btn_1_text', array('default' => null));
		$wp_customize->add_control('nebula_hero_cta_btn_1_text', array(
			'label' => 'Hero CTA Button 1 Text',
			'section' => 'hero',
			'priority' => 50,
			'active_callback' => 'is_front_page',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_cta_btn_1_text', array(
			'settings' => array('nebula_hero_cta_btn_1_text'),
			'selector' => '#hero-section .btn-primary',
			'container_inclusive' => false,
		));

		//CTA Button 1 URL
		$wp_customize->add_setting('nebula_hero_cta_btn_1_url', array('default' => null));
		$wp_customize->add_control('nebula_hero_cta_btn_1_url', array(
			'type' => 'url',
			'label' => 'Hero CTA Button 1 URL',
			'section' => 'hero',
			'priority' => 51,
			'active_callback' => 'is_front_page',
		));

		//CTA Button 2 Text
		//@todo "Nebula" 0: How to allow for Font Awesome icons here?
		$wp_customize->add_setting('nebula_hero_cta_btn_2_text', array('default' => null));
		$wp_customize->add_control('nebula_hero_cta_btn_2_text', array(
			'label' => 'Hero CTA Button 2 Text',
			'section' => 'hero',
			'priority' => 52,
			'active_callback' => 'is_front_page',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_cta_btn_2_text', array(
			'settings' => array('nebula_hero_cta_btn_2_text'),
			'selector' => '#hero-section .btn-secondary',
			'container_inclusive' => false,
		));

		//CTA Button 2 URL
		$wp_customize->add_setting('nebula_hero_cta_btn_2_url', array('default' => null));
		$wp_customize->add_control('nebula_hero_cta_btn_2_url', array(
			'type' => 'url',
			'label' => 'Hero CTA Button 2 URL',
			'section' => 'hero',
			'priority' => 53,
			'active_callback' => 'is_front_page',
		));


		/*==========================
			Posts Panel
		 ===========================*/

		$wp_customize->add_panel('posts', array(
			'priority' => 30,
			'title' => 'Posts',
			'description' => 'Post listing and detail settings',
		));

		/*==========================
			Posts Header Section
		 ===========================*/

		$wp_customize->add_section('posts_header', array(
			'title' => 'Header',
			'panel' => 'posts',
			'priority' => 10,
		));

		//Featured Image Location
		$wp_customize->add_setting('featured_image_location', array('default' => 'content'));
		$wp_customize->add_control('featured_image_location', array(
		    'label' => 'Featured Image Location',
		    'section' => 'posts_header',
		    'priority' => 20,
		    'type' => 'select',
		    'choices' => array(
		        'hero' => 'Hero',
		        'content' => 'In Content',
		        'disabled' => 'Disabled',
		    ),
		    //'active_callback' => 'is_singular',
		));

		//Header Navigation Color Scheme (Same as under Brand panel)
		$wp_customize->add_setting('header_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('header_nav_scheme', array(
		    'label' => 'Navigation Color Scheme',
		    'section' => 'posts_header',
		    'priority' => 25,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    ),
		    //'active_callback' => 'is_singular',
		));

		//Header Overlay Color
		$wp_customize->add_setting('nebula_header_overlay_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_header_overlay_color', array(
			'label' => 'Header BG Overlay Color',
			'section' => 'posts_header',
			'priority' => 30,
			//'active_callback' => 'is_singular',
		)));

		//Header Overlay Opacity
		$wp_customize->add_setting('nebula_header_overlay_opacity', array('default' => '0.6'));
		$wp_customize->add_control('nebula_header_overlay_opacity', array(
			'type' => 'number',
			'input_attrs' => array(
		        'min' => 0,
		        'max' => 1,
		        'step' => 0.1,
		    ),
		    'label' => 'Header BG Overlay Opacity',
			'description' => 'Enter a value between 0 (transparent) and 1 (opaque). Default: 0.6',
			'section' => 'posts_header',
			'priority' => 33,
			//'active_callback' => 'is_singular',
		));

		/*==========================
			Posts Meta Section
		 ===========================*/

		$wp_customize->add_section('posts_meta', array(
			'title' => 'Meta',
			'panel' => 'posts',
			'priority' => 30,
		));

		//Featured Image Location (Same as in Posts Header section)
		$wp_customize->add_control('featured_image_location', array(
		    'label' => 'Featured Image Location',
		    'section' => 'posts_meta',
		    'priority' => 10,
		    'type' => 'select',
		    'choices' => array(
		        'hero' => 'Hero',
		        'content' => 'In Content',
		        'disabled' => 'Disabled',
		    ),
		    //'active_callback' => 'is_singular',
		));

		//Post Date Format
		$wp_customize->add_setting('post_date_format', array('default' => 'relative'));
		$wp_customize->add_control('post_date_format', array(
		    'label' => 'Post Date Format',
		    'section' => 'posts_meta',
		    'priority' => 20,
		    'type' => 'select',
			'choices' => array(
				//'F j, Y' => 'Month, Day, Year',
				//'F Y' => 'Month, Year',
				'absolute' => 'Absolute',
				'relative' => 'Relative',
				'disabled' => 'Disabled',
			)
		));

		//Show Post Author
		$wp_customize->add_setting('post_author', array('default' => 0));
		$wp_customize->add_control('post_author', array(
			'label' => 'Show Post Author',
			'description' => 'Author Bios must also be enabled in Nebula Options',
			'section' => 'posts_meta',
			'priority' => 25,
			'type' => 'checkbox',
		));

		//Show Post Categories
		$wp_customize->add_setting('post_categories', array('default' => 1));
		$wp_customize->add_control('post_categories', array(
			'label' => 'Show Post Categories',
			'section' => 'posts_meta',
			'priority' => 30,
			'type' => 'checkbox',
		));

		//Show Post Tags
		$wp_customize->add_setting('post_tags', array('default' => 1));
		$wp_customize->add_control('post_tags', array(
			'label' => 'Show Post Tags',
			'section' => 'posts_meta',
			'priority' => 31,
			'type' => 'checkbox',
		));

		//Show Post Comment Count
		$wp_customize->add_setting('post_comment_count', array('default' => 0));
		$wp_customize->add_control('post_comment_count', array(
			'label' => 'Show Post Comment Count',
			'description' => 'Comments must also be enabled in Nebula Options',
			'section' => 'posts_meta',
			'priority' => 35,
			'type' => 'checkbox',
		));

		//Excerpt Length
		$wp_customize->add_setting('nebula_excerpt_length', array('default' => null));
		$wp_customize->add_control('nebula_excerpt_length', array(
			'type' => 'number',
			'input_attrs' => array(
		        'min' => 0,
		        'step' => 1,
		    ),
		    'label' => 'Nebula Excerpt Length',
			'section' => 'posts_meta',
			'priority' => 50,
		));

		//Excerpt "More" Text
		$wp_customize->add_setting('nebula_excerpt_more_text', array('default' => null));
		$wp_customize->add_control('nebula_excerpt_more_text', array(
			'input_attrs' => array(
		        'placeholder' => 'Read More &raquo;',
		    ),
			'label' => 'Nebula Excerpt "More" Text',
			'section' => 'posts_meta',
			'priority' => 51,
		));

		/*==========================
			Posts Bottom Section
		 ===========================*/

		$wp_customize->add_section('posts_bottom', array(
			'title' => 'Bottom Stuff',
			'panel' => 'posts',
			'priority' => 100,
		));

		//Social Sharing
		$wp_customize->add_setting('post_social_sharing', array('default' => 0));
		$wp_customize->add_control('post_social_sharing', array(
			'label' => 'Show Social Share Buttons',
			'section' => 'posts_bottom',
			'priority' => 20,
			'type' => 'checkbox',
		));

		//Social Sharing Location
		$wp_customize->add_setting('post_social_location', array('default' => 'title'));
		$wp_customize->add_control('post_social_location', array(
		    'label' => 'Social Sharing Location',
		    'section' => 'posts_bottom',
		    'priority' => 21,
		    'type' => 'select',
		    'choices' => array(
		        'header' => 'Header',
		        'title' => 'After Title',
		        'bottom' => 'Bottom',
		    )
		));

		$wp_customize->add_setting('crosslinks', array('default' => 0));
		$wp_customize->add_control('crosslinks', array(
			'label' => 'Show Next/Previous Crosslinks',
			'section' => 'posts_bottom',
			'priority' => 40,
			'type' => 'checkbox',
		));


		/*==========================
			Sidebar Section
		 ===========================*/

		$wp_customize->add_section('sidebar', array(
			'title' => 'Sidebar',
			'priority' => 40,
		));

		//Accordion Expanders
		$wp_customize->add_setting('sidebar_accordion_expanders', array('default' => 1));
		$wp_customize->add_control('sidebar_accordion_expanders', array(
			'label' => 'Enable Menu Expander Accordions',
			'section' => 'sidebar',
			'priority' => 20,
			'type' => 'checkbox',
		));

		//@TODO "Nebula" 0: Add options for sidebar background color?


		/*==========================
			Footer Widget Area Section
		 ===========================*/

		$wp_customize->add_section('footer_widget_area', array(
			'title' => 'Footer Widget Area',
			'priority' => 120,
		));

		//Footer Widget Area Navigation Color Scheme (Same as under Brand panel)
		$wp_customize->add_setting('fwa_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('fwa_nav_scheme', array(
		    'label' => 'Navigation Color Scheme',
		    'section' => 'footer_widget_area',
		    'priority' => 25,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    ),
		));

		//Footer Widget Area BG Image
		$wp_customize->add_setting('nebula_fwa_bg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_fwa_bg_image', array(
			'label' => 'Footer Widget Area Background Image',
			'description' => 'Using an optimized .jpg is strongly recommended!',
			'section' => 'footer_widget_area',
			'settings' => 'nebula_fwa_bg_image',
			'priority' => 30,
		)));
		$wp_customize->selective_refresh->add_partial('nebula_fwa_bg_image', array(
			'settings' => array('nebula_hero_bg_image'),
			'selector' => '#footer-widget-section',
			'container_inclusive' => false,
		));

		//Footer Widget Area Overlay Color
		$wp_customize->add_setting('nebula_fwa_overlay_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_fwa_overlay_color', array(
			'label' => 'Footer Widget Area BG Overlay Color',
			'section' => 'footer_widget_area',
			'priority' => 32,
		)));

		//Footer Widget Area Overlay Opacity
		$wp_customize->add_setting('nebula_fwa_overlay_opacity', array('default' => '0.6'));
		$wp_customize->add_control('nebula_fwa_overlay_opacity', array(
			'type' => 'number',
			'input_attrs' => array(
		        'min' => 0,
		        'max' => 1,
		        'step' => 0.1,
		    ),
		    'label' => 'Footer Widget Area BG Overlay Opacity',
			'description' => 'Enter a value between 0 (transparent) and 1 (opaque). Default: 0.6',
			'section' => 'footer_widget_area',
			'priority' => 33,
		));


		/*==========================
			Footer Section
		 ===========================*/

		$wp_customize->add_section('footer', array(
			'title' => 'Footer',
			'priority' => 130,
		));

		//Footer Navigation Color Scheme (Same as under Brand panel)
		$wp_customize->add_setting('footer_nav_scheme', array('default' => 'light'));
		$wp_customize->add_control('footer_nav_scheme', array(
		    'label' => 'Navigation Color Scheme',
		    'section' => 'footer',
		    'priority' => 10,
		    'type' => 'select',
		    'choices' => array(
		        'light' => 'Light',
		        'brand' => 'Brand',
		        'dark' => 'Dark',
		    ),
		));

		//Footer BG Image
		$wp_customize->add_setting('nebula_footer_bg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_footer_bg_image', array(
			'label' => 'Footer Background Image',
			'description' => 'Using an optimized .jpg is strongly recommended!',
			'section' => 'footer',
			'settings' => 'nebula_footer_bg_image',
			'priority' => 19
		)));
		$wp_customize->selective_refresh->add_partial('nebula_footer_bg_image', array(
			'settings' => array('nebula_footer_bg_image'),
			'selector' => '#footer-section',
			'container_inclusive' => false,
		));

		//Footer Overlay Color
		$wp_customize->add_setting('nebula_footer_overlay_color', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_footer_overlay_color', array(
			'label' => 'Footer BG Overlay Color',
			'section' => 'footer',
			'priority' => 21
		)));

		//Footer Overlay Opacity
		$wp_customize->add_setting('nebula_footer_overlay_opacity', array('default' => '0.85'));
		$wp_customize->add_control('nebula_footer_overlay_opacity', array(
			'type' => 'number',
			'input_attrs' => array(
		        'min' => 0,
		        'max' => 1,
		        'step' => 0.1,
		    ),
			'label' => 'Footer BG Overlay Opacity',
			'description' => 'Enter a value between 0 (transparent) and 1 (opaque). Default: 0.85',
			'section' => 'footer',
			'priority' => 22
		));

		//Footer logo
		$wp_customize->add_setting('nebula_footer_logo', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_footer_logo', array(
			'label' => 'Footer Logo',
			'section' => 'footer',
			'settings' => 'nebula_footer_logo',
			'priority' => 30
		)));
		$wp_customize->selective_refresh->add_partial('nebula_footer_logo', array(
			'settings' => array('nebula_footer_logo'),
			'selector' => '#footer-section .footerlogo',
			'container_inclusive' => false,
		));

		//Footer text
		$wp_customize->add_setting('nebula_footer_text', array('default' => null));
		$wp_customize->add_control('nebula_footer_text', array(
			'label' => 'Footer Text',
			'section' => 'footer',
			'priority' => 40,
		));
		$wp_customize->selective_refresh->add_partial('nebula_footer_text', array(
			'settings' => array('nebula_footer_text'),
			'selector' => '.copyright span',
			'container_inclusive' => false,
		));

		//Search in footer
		$wp_customize->add_setting('nebula_footer_search', array('default' => 1));
		$wp_customize->add_control('nebula_footer_search', array(
			'label' => 'Show Footer Search',
			'section' => 'footer',
			'priority' => 50,
			'type' => 'checkbox',
		));
		//Partial to search in footer
		$wp_customize->selective_refresh->add_partial('nebula_footer_search', array(
			'settings' => array('nebula_footer_search'),
			'selector' => '#footer-section .footer-search',
			'container_inclusive' => false,
		));
	}

	//Styles from Customizer settings
	public function customizer_style_overrides(){
		//@TODO "Nebula" 0: I'd love to find a way to not print these <style> tags if the Customizer has not been used... without checking every single option.

		$nav_schemes = array(
			'light' => '#fff',
			'light_alt' => '#aaa',
			'dark' => '#333',
			'dark_alt' => '#999',
			'brand' => get_theme_mod('nebula_primary_color'),
			'brand_alt' => get_theme_mod('nebula_secondary_color'),
		);

		?>
			<style>
				<?php if ( get_theme_mod('nebula_background_color') ): //Background Color ?>
					body {background: <?php echo get_theme_mod('nebula_background_color'); ?>;}
				<?php endif; ?>

				<?php if ( get_theme_mod('nebula_hero_bg_image') && get_theme_mod('nebula_hero_overlay_opacity') != 1 ): //Hero BG ?>
					#hero-section {background-image: url( "<?php echo get_theme_mod('nebula_hero_bg_image'); ?>");}
				<?php endif; ?>

				<?php if ( get_theme_mod('header_nav_scheme') ): //Subpage Header Nav Scheme ?>
					#primarynav ul li.menu-item a {color: <?php echo $nav_schemes[get_theme_mod('header_nav_scheme')]; ?>;}
						#primarynav ul li.menu-item a:hover {color: <?php echo $nav_schemes[get_theme_mod('header_nav_scheme') . '_alt']; ?>;}
				<?php endif; ?>

				<?php if ( get_theme_mod('hero_nav_scheme') ): //Hero Nav Scheme ?>
					.home #primarynav ul li.menu-item a {color: <?php echo $nav_schemes[get_theme_mod('hero_nav_scheme')]; ?>;}
						.home #primarynav ul li.menu-item a:hover {color: <?php echo $nav_schemes[get_theme_mod('hero_nav_scheme') . '_alt']; ?>;}
				<?php endif; ?>

				<?php if ( get_theme_mod('nebula_primary_color') && !nebula()->get_option('scss') ): //If set primary without Sass enabled ?>
					#bigheadingcon {background: <?php echo get_theme_mod('nebula_primary_color'); ?>;}
				<?php endif; ?>

				<?php if ( get_theme_mod('nebula_hero_overlay_color') || get_theme_mod('nebula_hero_overlay_opacity') ): ?>
					<?php if ( get_theme_mod('nebula_hero_overlay_color') ):?>
						#hero-section .custom-color-overlay {background: <?php echo get_theme_mod('nebula_hero_overlay_color'); ?>;}
					<?php endif;?>

					<?php if ( !is_null(get_theme_mod('nebula_hero_overlay_opacity')) ): ?> 
						#hero-section .custom-color-overlay {opacity: <?php echo get_theme_mod('nebula_hero_overlay_opacity'); ?>;}
					<?php endif;?>

					#hero-section .custom-color-overlay{animation: none;}
				
				<?php endif; ?>

				<?php if ( get_theme_mod('nebula_show_hero_title', true) ): ?>
					#hero-section h1{<?php echo ( get_theme_mod('nebula_hero_text_color') )? 'color:' . get_theme_mod('nebula_hero_text_color') . ';' : ''; ?>}
				<?php endif; ?>

				<?php if ( get_theme_mod('nebula_show_hero_description', true) ): ?>
					#hero-section h2{<?php echo ( get_theme_mod('nebula_hero_text_color') )? 'color:' . get_theme_mod('nebula_hero_text_color') . ';' : ''; ?>}
				<?php endif; ?>

				<?php if ( get_theme_mod('fwa_nav_scheme') ): //Footer Widget Area Nav Scheme ?>
					#footer-widget-section a {color: <?php echo $nav_schemes[get_theme_mod('fwa_nav_scheme')]; ?>;}
						#footer-widget-section a:hover {color: <?php echo $nav_schemes[get_theme_mod('fwa_nav_scheme') . '_alt']; ?>;}
				<?php endif; ?>

				<?php if ( get_theme_mod('nebula_footer_bg_image') && get_theme_mod('nebula_footer_overlay_opacity') != 1 ):?>
					#footer-section{background-image: url("<?php echo get_theme_mod('nebula_footer_bg_image'); ?>");}
				<?php endif; ?>

				<?php if ( get_theme_mod('footer_nav_scheme') ): //Footer Nav Scheme ?>
					#footer-section a {color: <?php echo $nav_schemes[get_theme_mod('footer_nav_scheme')]; ?>;}
						#footer-section a:hover {color: <?php echo $nav_schemes[get_theme_mod('footer_nav_scheme') . '_alt']; ?>;}
				<?php endif; ?>
				
				<?php if ( get_theme_mod('nebula_footer_overlay_color') || get_theme_mod('nebula_footer_overlay_opacity') ): ?>

					<?php if(get_theme_mod('nebula_footer_overlay_color')): ?>
						#footer-section .custom-color-overlay{background: <?php echo get_theme_mod('nebula_footer_overlay_color'); ?>;}
					<?php endif; ?>

					<?php if( !is_null(get_theme_mod('nebula_footer_overlay_opacity')) ): ?>
						#footer-section .custom-color-overlay{opacity: <?php echo get_theme_mod('nebula_footer_overlay_opacity'); ?>;}
					<?php endif; ?>

					#footer-section .custom-color-overlay{animation: none;}
				
				<?php endif; ?>

			</style>
		<?php
	}
}
