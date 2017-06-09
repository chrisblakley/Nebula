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

		//Site Title
		//$wp_customize->get_setting('blogname')->transport = 'postMessage';
		//$wp_customize->get_control('blogname')->priority = 20;
		$wp_customize->selective_refresh->add_partial('blogname', array(
			'settings' => array('blogname'),
			'selector' => '#hero-section h1',
			'container_inclusive' => true,
		));

		//Site Description
		//$wp_customize->get_setting('blogdescription')->transport = 'postMessage';
		//$wp_customize->get_control('blogdescription')->priority = 30;
		//$wp_customize->get_control('blogdescription')->label = 'Site Description'; //Changes "Tagline" label to "Site Description"
		$wp_customize->selective_refresh->add_partial('blogdescription', array(
			'settings' => array('blogdescription'),
			'selector' => '#hero-section h2',
			'container_inclusive' => true,
		));

		//@todo "Nebula" 0: Figure out how the favicon will work (and if metagraphics will be affected). The source image will be 512x512, so we could set some image sizes based off that...


		/*==========================
			Colors
			//@todo "Nebula" 0: Consider moving these options under Site Identity?
		 ===========================*/

		$wp_customize->add_section('colors', array(
			'title' => 'Colors',
			'priority' => 40,
		));

		//Primary color
		$wp_customize->add_setting('nebula_primary_color', array('default' => '#0098d7'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_primary_color', array(
			'label' => 'Primary Color',
			'section' => 'colors',
			'priority' => 10
		)));

		//Secondary color
		$wp_customize->add_setting('nebula_secondary_color', array('default' => '#95d600'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_secondary_color', array(
			'label' => 'Secondary Color',
			'section' => 'colors',
			'priority' => 20
		)));

		//Background color
		$wp_customize->add_setting('nebula_background_color', array('default' => '#f6f6f6'));
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_background_color', array(
			'label' => 'Background Color',
			'section' => 'colors',
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
		$wp_customize->add_setting('nebula_hide_hero', array('default' => 0));
		$wp_customize->add_control('nebula_hide_hero', array(
			'label' => 'Hide Hero Section',
			'section' => 'static_front_page',
			'priority' => 1,
			'type' => 'checkbox',
		));


		$wp_customize->add_setting('nebula_hide_blogname', array('default' => 0));
		$wp_customize->add_control('nebula_hide_blogname', array(
			'label' => 'Hide Title Heading',
			'section' => 'static_front_page',
			'priority' => 2,
			'type' => 'checkbox',
		));

		$wp_customize->add_setting('nebula_hide_blogdescription', array('default' => 0));
		$wp_customize->add_control('nebula_hide_blogdescription', array(
			'label' => 'Hide Tagline',
			'section' => 'static_front_page',
			'priority' => 3,
			'type' => 'checkbox',
		));

/*
		//Hero title
		$wp_customize->add_setting('nebula_hero_title', array('default' => 'Nebula'));
		$wp_customize->add_control('nebula_hero_title', array(
			'label' => 'Title',
			'section' => 'static_front_page',
			'priority' => 2,
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_title', array(
			'settings' => array('nebula_hero_title'),
			'selector' => '#hero-section h1',
			'container_inclusive' => false,
		));
*/

/*
		//Hero subtitle
		$wp_customize->add_setting('nebula_hero_subtitle', array('default' => 'Advanced Starter WordPress Theme for Developers'));
		$wp_customize->add_control('nebula_hero_subtitle', array(
			'label' => 'Subtitle',
			'section' => 'static_front_page',
			'priority' => 3,
		));
		$wp_customize->selective_refresh->add_partial('nebula_hero_subtitle', array(
			'settings' => array('nebula_hero_subtitle'),
			'selector' => '#hero-section h2',
			'container_inclusive' => false,
		));
*/

		//Search in front page
		$wp_customize->add_setting('nebula_hide_hero_search', array('default' => 0));
		$wp_customize->add_control('nebula_hide_hero_search', array(
			'label' => 'Hide Hero Search',
			'section' => 'static_front_page',
			'priority' => 4,
			'type' => 'checkbox',
		));
		$wp_customize->selective_refresh->add_partial('nebula_hide_hero_search', array(
			'settings' => array('nebula_hide_hero_search'),
			'selector' => '#hero-section #nebula-hero-formcon',
			'container_inclusive' => false,
		));

		//@todo "Nebula" 0: CTA Button 1 (Text and URL)
		//@todo "Nebula" 0: CTA Button 2 (Text and URL)

		//@todo "Nebula" 0: Hero background image (how do we do a suggested image?)
		//@todo "Nebula" 0: Hero color overlay (Default to #000)
		//@todo "Nebula" 0: Hero overlay opacity (Default to 60%)


		/*==========================
			Footer
		 ===========================*/

		$wp_customize->add_section('footer', array(
			'title' => 'Footer',
			'priority' => 130,
		));

		//Footer logo
		//@todo "Nebula" 0: Footer logo is appearing, but broken (looks like it was expecting a small square image)
		$wp_customize->add_setting('nebula_footer_logo', array('default' => null));
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_footer_logo', array(
			'label' => 'Footer Logo',
			'section' => 'footer',
			'settings' => 'nebula_footer_logo',
			'priority' => 10
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
			'priority' => 20,
		));
		$wp_customize->selective_refresh->add_partial('nebula_footer_text', array(
			'settings' => array('nebula_footer_text'),
			'selector' => '.copyright span',
			'container_inclusive' => false,
		));

		//Search in footer
		$wp_customize->add_setting('nebula_hide_footer_search', array('default' => 0));
		$wp_customize->add_control('nebula_hide_footer_search', array(
			'label' => 'Hide Footer Search',
			'section' => 'footer',
			'priority' => 30,
			'type' => 'checkbox',
		));
		//Partial to search in footer
		$wp_customize->selective_refresh->add_partial('nebula_hide_footer_search', array(
			'settings' => array('nebula_hide_footer_search'),
			'selector' => '#footer-section .footer-search',
			'container_inclusive' => false,
		));

		//@todo "Nebula" 0: Footer BG Image







		//@todo "Nebula" 0: Add support for "Additional CSS" option. Should it just be appended to the end of style.scss (via the PHP function) or enqueue a new file, or just embed in the header?
	}
}