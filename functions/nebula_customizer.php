<?php

// Register nebula customizer
function nebula_customize_register( $wp_customize ) {
    // Site Logo
    $wp_customize->add_setting( 'nebula_logo', array( 'default' => null ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'nebula_logo', array(
        'label'     => __( 'Site Logo' ),
        'section'   => 'title_tagline',
        'settings'  => 'nebula_logo',
        'priority'  => 10
    ) ) );

    // Site Title
    $wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
    $wp_customize->get_control( 'blogname' )->priority = 20;

    // Partial to site title
    $wp_customize->selective_refresh->add_partial( 'blogname', array(
        'settings'            => array( 'blogname' ),
        'selector'            => '#hero-section h1',
        'container_inclusive' => true,
    ) );

    // Site Description
    $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
    $wp_customize->get_control( 'blogdescription' )->priority = 30;
    $wp_customize->get_control( 'blogdescription' )->label = __( 'Site Description' ); // Changes "Titletag" label to "Site Description"

    // Partial to site description
    $wp_customize->selective_refresh->add_partial( 'blogdescription', array(
        'settings'            => array( 'blogdescription' ),
        'selector'            => '#hero-section h2',
        'container_inclusive' => true,
    ) );

    // Colors section
    $wp_customize->add_section( 'colors', array(
        'title'          => __( 'Colors' ),
        'priority'       => 40,
    ) );

    // Primary color
    $wp_customize->add_setting( 'nebula_primary_color', array( 'default' => '#0098d7' ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nebula_primary_color', array(
        'label'     => __( 'Primary Color' ),
        'section'   => 'colors',
        'priority'  => 10
    ) ) );

    // Secondary color
    $wp_customize->add_setting( 'nebula_secondary_color', array( 'default' => '#95d600' ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nebula_secondary_color', array(
        'label'     => __( 'Secondary Color' ),
        'section'   => 'colors',
        'priority'  => 20
    ) ) );

    // Background color
    $wp_customize->add_setting( 'nebula_background_color', array( 'default' => '#f6f6f6' ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nebula_background_color', array(
        'label'     => __( 'Bakcground Color' ),
        'section'   => 'colors',
        'priority'  => 30
    ) ) );


}
add_action( 'customize_register', 'nebula_customize_register' );

// Apply changes from nebula customizer
function nebula_customizer_head() {
    $primary_color       = get_theme_mod( 'nebula_primary_color', '#0098d7' );
    $secondary_color     = get_theme_mod( 'nebula_secondary_color', '#95d600' );
    $background_color    = get_theme_mod( 'nebula_background_color', '#f6f6f6' );

    ?>
    <style type="text/css">
        h1 a,
        h2 a,
        h3 a,
        h4 a,
        h5 a,
        h6 a,
        a,
        a:visited {
            color: <?php echo $primary_color; ?>;
        }

        a:hover,
        a:active,
        a:focus {
            color: <?php echo $secondary_color; ?>;
        }

        body {
            background: <?php echo $background_color; ?>;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'nebula_customizer_head' );