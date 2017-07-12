<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

trait Customizer {

	public function hooks(){

		//Register WordPress Customizer
		add_action('customize_register', array($this, 'customize_register'));

	}

	//Register WordPress Customizer
	public function customize_register($wp_customize){

		/*==========================
			Site Identity
		 ===========================*/

		//@todo "Nebula" 0: Get an edit icon to appear on the logo for custom_logo option

		//@todo "Nebula" 0: Figure out how the favicon will work (and if metagraphics will be affected). The source image will be 512x512, so we could set some image sizes based off that...


		//Primary color
		$wp_customize->add_setting('nebula_primary_color', array('default' => '#0098d7'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_primary_color', array(
			'label' => 'Primary Color',
			'section' => 'title_tagline',
			'priority' => 10
		)));

		//Secondary color
		$wp_customize->add_setting('nebula_secondary_color', array('default' => '#95d600'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_secondary_color', array(
			'label' => 'Secondary Color',
			'section' => 'title_tagline',
			'priority' => 20
		)));

		//Background color
		$wp_customize->add_setting('nebula_background_color', array('default' => '#f6f6f6'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_background_color', array(
			'label' => 'Background Color',
			'section' => 'title_tagline',
			'priority' => 30
		)));


		/*==========================
			Overall Site Stuff...... need new name
		 ===========================*/

		 //@todo "Nebula" 0: Hide Mobile Search

		 //@todo "Nebula" 0: Hide Offcanvas Menu (will need to dequeue Mmenu and change the dependencies on main.js)


		/*==========================
			Static Front Page
		 ===========================*/

		//Hero header in front page
		$wp_customize->add_setting('nebula_hero', array('default' => 1));
		$wp_customize->add_control('nebula_hero', array(
			'label' => 'Show Hero Section',
			'section' => 'static_front_page',
			'priority' => 30,
			'type' => 'checkbox',
		));

		//Hero Site Title
		$wp_customize->add_setting('nebula_show_hero_title', array('default' => 1));
		$wp_customize->add_control('nebula_show_hero_title', array(
			'label' => 'Show Hero Title',
			'section' => 'static_front_page',
			'priority' => 31,
			'type' => 'checkbox',
		));

		//Custom Hero Title
		$wp_customize->add_setting('nebula_hero_custom_title', array('default' => ''));
		$wp_customize->add_control('nebula_hero_custom_title', array(
			'label' => 'Custom Hero Title',
			'description' => 'Customize the H1 text instead of using the site title',
			'section' => 'static_front_page',
			'priority' => 32
		));

		//Hero Site Description
		$wp_customize->add_setting('nebula_show_hero_description', array('default' => 1));
		$wp_customize->add_control('nebula_show_hero_description', array(
			'label' => 'Show Hero Description',
			'section' => 'static_front_page',
			'priority' => 33,
			'type' => 'checkbox',
		));

		//Hero Description Text
		$wp_customize->add_setting('nebula_hero_custom_description', array('default' => ''));
		$wp_customize->add_control('nebula_hero_custom_description', array(
			'label' => 'Custom Hero Description',
			'description' => 'Customize the description text instead of using the site tagline',
			'section' => 'static_front_page',
			'priority' => 34
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_custom_description', array(
			'settings' => array('nebula_hero_custom_description'),
			'selector' => '#hero-section h2',
			'container_inclusive' => false,
		));

		//Hero Search
		$wp_customize->add_setting('nebula_hero_search', array('default' => 1));
		$wp_customize->add_control('nebula_hero_search', array(
			'label' => 'Show Hero Search',
			'section' => 'static_front_page',
			'priority' => 35,
			'type' => 'checkbox',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_search', array(
			'settings' => array('nebula_hero_search'),
			'selector' => '#hero-section #nebula-hero-formcon',
			'container_inclusive' => false,
		));

		//Hero BG Image
		$wp_customize->add_setting('nebula_hero_bg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_hero_bg_image', array(
			'label' => 'Hero Background Image',
			'section' => 'static_front_page',
			'settings' => 'nebula_hero_bg_image',
			'priority' => 39
		)));
		$wp_customize->selective_refresh->add_partial('nebula_hero_bg_image', array(
			'settings' => array('nebula_hero_bg_image'),
			'selector' => '#hero-section',
			'container_inclusive' => false,
		));

		//Hero Overlay Color
		$wp_customize->add_setting('nebula_hero_overlay_color', array('default' => '#000000'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_hero_overlay_color', array(
			'label' => 'Hero BG Overlay Color',
			'section' => 'static_front_page',
			'priority' => 40
		)));

		//Hero Overlay Opacity
		$wp_customize->add_setting('nebula_hero_overlay_opacity', array('default' => '0.6'));
		$wp_customize->add_control('nebula_hero_overlay_opacity', array(
			'label' => 'Hero BG Overlay Opacity',
			'description' => 'Enter a value between 0 (transparent) and 1 (opaque). Default: 0.6',
			'section' => 'static_front_page',
			'priority' => 41
		));

		//Hero FG Image
		$wp_customize->add_setting('nebula_hero_fg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_hero_fg_image', array(
			'label' => 'Hero Foreground Image',
			'section' => 'static_front_page',
			'settings' => 'nebula_hero_fg_image',
			'priority' => 42
		)));
		$wp_customize->selective_refresh->add_partial('nebula_hero_fg_image', array(
			'settings' => array('nebula_hero_fg_image'),
			'selector' => '#hero-section img',
			'container_inclusive' => false,
		));

		//Hero FG Image Link
		$wp_customize->add_setting('nebula_hero_fg_image_link', array('default' => ''));
		$wp_customize->add_control('nebula_hero_fg_image_link', array(
			'label' => 'Hero Foreground Image Link',
			'section' => 'static_front_page',
			'priority' => 43
		));

		//Hero Youtube
		$wp_customize->add_setting('nebula_hero_youtube_id', array('default' => null));
		$wp_customize->add_control('nebula_hero_youtube_id', array(
			'label' => 'Hero Youtube Video ID',
			'section' => 'static_front_page',
			'priority' => 44
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_youtube_id', array(
			'settings' => array('nebula_hero_youtube_id'),
			'selector' => '#hero-section iframe',
			'container_inclusive' => false,
		));

		//CTA Button 1 Text
		//@todo "Nebula" 0: How to allow for Font Awesome icons here?
		$wp_customize->add_setting('nebula_hero_cta_btn_1_text', array('default' => ''));
		$wp_customize->add_control('nebula_hero_cta_btn_1_text', array(
			'label' => 'Hero CTA Button 1 Text',
			'section' => 'static_front_page',
			'priority' => 50
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_cta_btn_1_text', array(
			'settings' => array('nebula_hero_cta_btn_1_text'),
			'selector' => '#hero-section .btn-primary',
			'container_inclusive' => false,
		));

		//CTA Button 1 URL
		$wp_customize->add_setting('nebula_hero_cta_btn_1_url', array('default' => ''));
		$wp_customize->add_control('nebula_hero_cta_btn_1_url', array(
			'label' => 'Hero CTA Button 1 URL',
			'section' => 'static_front_page',
			'priority' => 51
		));

		//CTA Button 2 Text
		//@todo "Nebula" 0: How to allow for Font Awesome icons here?
		$wp_customize->add_setting('nebula_hero_cta_btn_2_text', array('default' => ''));
		$wp_customize->add_control('nebula_hero_cta_btn_2_text', array(
			'label' => 'Hero CTA Button 2 Text',
			'section' => 'static_front_page',
			'priority' => 52
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_cta_btn_2_text', array(
			'settings' => array('nebula_hero_cta_btn_2_text'),
			'selector' => '#hero-section .btn-secondary',
			'container_inclusive' => false,
		));

		//CTA Button 2 URL
		$wp_customize->add_setting('nebula_hero_cta_btn_2_url', array('default' => ''));
		$wp_customize->add_control('nebula_hero_cta_btn_2_url', array(
			'label' => 'Hero CTA Button 2 URL',
			'section' => 'static_front_page',
			'priority' => 53
		));



		/*==========================
			Footer
		 ===========================*/

		$wp_customize->add_section('footer', array(
			'title' => 'Footer',
			'priority' => 130,
		));

		//Footer BG Image
		$wp_customize->add_setting('nebula_footer_bg_image', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_footer_bg_image', array(
			'label' => 'Footer Background Image',
			'section' => 'footer',
			'settings' => 'nebula_footer_bg_image',
			'priority' => 20
		)));
		$wp_customize->selective_refresh->add_partial('nebula_footer_bg_image', array(
			'settings' => array('nebula_footer_bg_image'),
			'selector' => '#footer-section',
			'container_inclusive' => false,
		));

		//Footer Overlay Color
		$wp_customize->add_setting('nebula_footer_overlay_color', array('default' => '#000000'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_footer_overlay_color', array(
			'label' => 'Footer BG Overlay Color',
			'section' => 'footer',
			'priority' => 21
		)));

		//Footer Overlay Opacity
		$wp_customize->add_setting('nebula_footer_overlay_opacity', array('default' => '0.85'));
		$wp_customize->add_control('nebula_footer_overlay_opacity', array(
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
		$wp_customize->add_setting('nebula_footer_text', array('default' => ''));
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





		//@todo "Nebula" 0: Add support for "Additional CSS" option. Should it just be appended to the end of style.scss (via the PHP function) or enqueue a new file, or just embed in the header?
	}
}