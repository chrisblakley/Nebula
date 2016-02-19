<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		die('Error 403: Forbidden.');
	}
?>

<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') ): ?>
	<meta name="description" content="<?php echo nebula_the_excerpt('', 100, 0); ?>" />
	<meta name="keywords" content="<?php echo get_option('nebula_keywords'); ?>" />
	<?php if ( function_exists('get_field') && get_field('news_keywords') ): //News keywords are <=10 comma separated keywords. ?>
		<meta name="news_keywords" content="<?php echo get_field('news_keywords'); ?>" />
	<?php endif; ?>
	<?php if ( nebula_option('nebula_author_bios', 'enabled') ): ?>
		<meta name="author" content="<?php echo nebula_the_author(); ?>" />
	<?php endif; ?>
<?php endif; ?>


<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') || is_front_page() ): ?>
	<?php if ( get_option('nebula_google_webmaster_tools_verification') ): ?>
		<meta name="google-site-verification" content="<?php echo get_option('nebula_google_webmaster_tools_verification'); ?>" />
	<?php endif; ?>

	<meta property="og:type" content="business.business" />
	<meta property="og:locale" content="<?php echo str_replace('-', '_', get_bloginfo('language')); ?>" />
	<meta property="og:title" content="<?php the_title(); ?>" />
	<meta property="og:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
	<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') ) : ?>
		<meta property="og:url" content="<?php the_permalink(); ?>" />
	<?php endif; ?>
	<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />

	<link rel="canonical" href="<?php the_permalink(); ?>" />

	<meta property="business:contact_data:website" content="<?php echo home_url('/'); ?>" />
	<meta property="business:contact_data:phone_number" content="+<?php echo get_option('nebula_phone_number'); ?>" />
	<meta property="business:contact_data:fax_number" content="+<?php echo get_option('nebula_fax_number'); ?>" />
	<meta property="business:contact_data:street_address" content="<?php echo get_option('nebula_street_address'); ?>" />
	<meta property="business:contact_data:locality" content="<?php echo get_option('nebula_locality'); ?>" />
	<meta property="business:contact_data:region" content="<?php echo get_option('nebula_region'); ?>" />
	<meta property="business:contact_data:postal_code" content="<?php echo get_option('nebula_postal_code'); ?>" />
	<meta property="business:contact_data:country_name" content="<?php echo get_option('nebula_country_name'); ?>" />
<?php endif; ?>


<?php //Business hours of operation. ?>
<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ): ?>
	<?php if ( get_option('nebula_business_hours_' . $weekday . '_enabled') && get_option('nebula_business_hours_' . $weekday . '_open') != '' && get_option('nebula_business_hours_' . $weekday . '_close') != '' ) : ?>
		<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
		<meta property="business:hours:start" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_open'); ?>" />
		<meta property="business:hours:end" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_close'); ?>" />
	<?php endif; ?>
<?php endforeach; ?>


<?php //Facebook Metadata ?>
<meta property="fb:app_id" content="<?php echo get_option('nebula_facebook_app_id'); ?>" />
<meta property="fb:page_id" content="<?php echo get_option('nebula_facebook_page_id'); ?>" />
<meta property="fb:admins" content="<?php echo get_option('facebook_admin_ids'); ?>" />


<?php //Twitter Metadata ?>
<?php //twitter:image is located in includes/metagraphics.php ?>
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="<?php the_title(); ?>" />
<meta name="twitter:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
<meta name="twitter:site" content="" /><?php //"@username" of website ?>
<meta name="twitter:creator" content="" /><?php //"@username" of content creator ?>


<?php //Local/Geolocation Metadata ?>
<meta name="geo.placename" content="<?php echo get_option('nebula_locality'); ?>, <?php echo get_option('nebula_region'); ?>" />
<meta name="geo.position" content="<?php echo get_option('nebula_latitude'); ?>;<?php echo get_option('nebula_longitude'); ?>" />
<meta name="geo.region" content="<?php echo bloginfo('language'); ?>" />
<meta name="ICBM" content="<?php echo get_option('nebula_latitude'); ?>, <?php echo get_option('nebula_longitude'); ?>" />
<meta property="place:location:latitude" content="<?php echo get_option('nebula_latitude'); ?>" />
<meta property="place:location:longitude" content="<?php echo get_option('nebula_longitude'); ?>" />