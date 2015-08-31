<?php
	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
		die('Error 403: Forbidden.');
	}
?>

<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') ): ?>
	<meta name="description" content="<?php echo nebula_the_excerpt('', 100, 0); ?>" />
	<meta name="keywords" content="<?php echo nebula_options_conditional_text('nebula_keywords', ''); ?>" /><!-- @TODO "Metadata" 1: Replace '' with comma-separated keywords. -->
	<?php if ( function_exists('get_field') && get_field('news_keywords') ): //@TODO "Metadata" 1: The news keywords custom field is not bundled with Nebula and must be created to use this. News keywords are <=10 comma separated keywords. ?>
		<meta name="news_keywords" content="<?php echo get_field('news_keywords'); ?>" /><!-- @TODO "Nebula" 0: W3 Validator Invalid: "Keyword news_keywords is not registered." -->
	<?php endif; ?>
	<meta name="author" content="<?php echo nebula_the_author(); ?>" />
<?php endif; ?>


<?php if ( !file_exists(WP_PLUGIN_DIR . '/wordpress-seo') || is_front_page() ): ?>
	<?php if ( nebula_options_conditional_text_bool('nebula_google_webmaster_tools_verification') ): ?>
		<meta name="google-site-verification" content="<?php echo nebula_options_conditional_text('nebula_google_webmaster_tools_verification', ''); ?>" />
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
	<meta property="business:contact_data:email" content="<?php echo nebula_options_conditional_text('nebula_contact_email', get_option('admin_email', $GLOBALS['admin_user']->user_email)); //@TODO "Metadata" 2: Verify admin email address. ?>" />
	<meta property="business:contact_data:phone_number" content="+<?php echo nebula_options_conditional_text('nebula_phone_number', ''); //Ex: "1-315-478-6700" ?>" />
	<meta property="business:contact_data:fax_number" content="+<?php echo nebula_options_conditional_text('nebula_fax_number', ''); //Ex: "1-315-478-6700" ?>" />
	<meta property="business:contact_data:street_address" content="<?php echo nebula_options_conditional_text('nebula_street_address', ''); ?>" />
	<meta property="business:contact_data:locality" content="<?php echo nebula_options_conditional_text('nebula_locality', ''); //City ?>" />
	<meta property="business:contact_data:region" content="<?php echo nebula_options_conditional_text('nebula_region', ''); //State ?>" />
	<meta property="business:contact_data:postal_code" content="<?php echo nebula_options_conditional_text('nebula_postal_code', ''); //Zip ?>" />
	<meta property="business:contact_data:country_name" content="<?php echo nebula_options_conditional_text('nebula_country_name', 'USA'); //Country ?>" />
<?php endif; ?>


<?php //Business hours of operation. Times should be in the format "5:30 pm" or "17:30". Remove from Foreach loop to override Nebula Options. ?>
<?php foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ): ?>
	<?php if ( get_option('nebula_business_hours_' . $weekday . '_enabled') && get_option('nebula_business_hours_' . $weekday . '_open') != '' && get_option('nebula_business_hours_' . $weekday . '_close') != '' ) : ?>
		<meta property="business:hours:day" content="<?php echo $weekday; ?>" />
		<meta property="business:hours:start" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_open'); ?>" />
		<meta property="business:hours:end" content="<?php echo get_option('nebula_business_hours_' . $weekday . '_close'); ?>" />
	<?php endif; ?>
<?php endforeach; ?>


<!-- Facebook Metadata -->
<?php $GLOBALS['social']['facebook_url'] = nebula_options_conditional_text('nebula_facebook_url', ''); //@TODO "Social" 1: Enter the URL of the Facebook page here. ?>
<?php $GLOBALS['social']['facebook_access_token'] = nebula_options_conditional_text('nebula_facebook_access_token', ''); //@TODO "Social" 1: Enter Facebook Access Token. This only stored in PHP for reference. Do NOT share or store in browser-facing code. ?>
<meta property="fb:app_id" content="<?php echo $GLOBALS['social']['facebook_app_id'] = nebula_options_conditional_text('nebula_facebook_app_id', ''); //@TODO "Social" 1: Enter Facebook App ID. Instructions: http://smashballoon.com/custom-facebook-feed/access-token/ ?>" />
<meta property="fb:page_id" content="<?php echo $GLOBALS['social']['facebook_page_id'] = nebula_options_conditional_text('nebula_facebook_page_id', ''); //@TODO "Social" 1: Enter Facebook Page ID. ?>" />
<meta property="fb:admins" content="<?php echo $GLOBALS['social']['facebook_admin_ids'] = nebula_options_conditional_text('facebook_admin_ids', ''); //@TODO "Social" 1: Comma separated IDs of FB admins. Ex: "1234,2345,3456" ?>" />


<!-- Twitter Metadata -->
<?php //twitter:image is located in includes/metagraphics.php ?>
<?php $GLOBALS['social']['twitter_url'] = nebula_options_conditional_text('nebula_twitter_url', ''); //@TODO "Social" 1: Enter the URL of the Twitter page here. ?>
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="<?php the_title(); ?>" />
<meta name="twitter:description" content="<?php echo nebula_the_excerpt('', 30, 1); ?>" />
<meta name="twitter:site" content="" /> <!-- "@username" of website -->
<meta name="twitter:creator" content="" /> <!-- "@username" of content creator -->


<!-- Other Social Metadata -->
<?php //@TODO "SEO" 3: Create/update information on Google Business! http://www.google.com/business/ ?>
<?php $GLOBALS['social']['google_plus_url'] = nebula_options_conditional_text('nebula_google_plus_url', ''); //@TODO "Social" 1: Enter the URL of the Google+ page here. ?>
<?php $GLOBALS['social']['linkedin_url'] = nebula_options_conditional_text('nebula_linkedin_url', ''); //@TODO "Social" 1: Enter the URL of the LinkedIn page here. ?>
<?php $GLOBALS['social']['youtube_url'] = nebula_options_conditional_text('nebula_youtube_url', ''); //@TODO "Social" 1: Enter the URL of the Youtube page here. ?>
<?php $GLOBALS['social']['instagram_url'] = nebula_options_conditional_text('nebula_instagram_url', ''); //@TODO "Social" 1: Enter the URL of the Instagram page here. ?>


<!-- Local/Geolocation Metadata -->
<meta name="geo.placename" content="<?php echo nebula_options_conditional_text('nebula_locality', ''); ?>, <?php echo nebula_options_conditional_text('nebula_region', ''); ?>" /> <!-- The city (and state if needed). Replace each respective '' with the appropriate value. -->
<meta name="geo.position" content="<?php echo nebula_options_conditional_text('nebula_latitude', ''); ?>;<?php echo nebula_options_conditional_text('nebula_longitude', ''); ?>" /> <!-- Semi-colon separated latitude;longitude. Replace each respsective '' with the appropriate value. -->
<meta name="geo.region" content="<?php echo bloginfo('language'); ?>" />
<meta name="ICBM" content="<?php echo nebula_options_conditional_text('nebula_latitude', ''); ?>, <?php echo nebula_options_conditional_text('nebula_longitude', ''); ?>" /> <!-- Comma and space separated latitude;longitude. Replace each respsective '' with the appropriate value. -->
<meta property="place:location:latitude" content="<?php echo nebula_options_conditional_text('nebula_latitude', ''); ?>" />
<meta property="place:location:longitude" content="<?php echo nebula_options_conditional_text('nebula_longitude', ''); ?>" />