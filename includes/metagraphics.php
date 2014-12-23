<?php
	/* Favicons
		Favicons for various usage. PNG icons are used as needed by browsers in addition to Android homescreen bookmarks.
	*/
?>
<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon.ico"> <!-- @TODO "Graphics" 5: Need to create a 16x16 ICO favicon. Consider transparent BG. -->
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-16x16.png" sizes="16x16"> <!-- @TODO "Graphics" 1: 16x16 PNG favicon. Consider transparent BG. -->
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-32x32.png" sizes="32x32"> <!-- @TODO "Graphics" 1: 32x32 PNG favicon. Consider transparent BG. -->
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-96x96.png" sizes="96x96"> <!-- @TODO "Graphics" 1: 96x96 PNG favicon. Also used by Manifest JSON. Transparent BG not recommended. -->
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-160x160.png" sizes="160x160"> <!-- @TODO "Graphics" 1: 160x160 PNG favicon. Transparent BG not recommended. -->
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-192x192.png" sizes="192x192"> <!-- @TODO "Graphics" 1: 192x192 PNG favicon. Also used by Manifest JSON. Transparent BG not recommended. -->



<?php
	/* Apple iOS
		iOS icons for homescreen bookmarks and startup image. For certain Android devices the apple-touch-icon and apple-touch-icon-precomposed are used for homescreen icons.
	*/
?>
<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-startup.png"> <!-- @TODO "Graphics" 1: Create an Apple startup screen 320x480px. -->
<link rel="apple-touch-icon" sizes="36x36" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-36x36.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 36x36px. Used by Manifest JSON. -->
<link rel="apple-touch-icon" sizes="48x48" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-48x48.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 48x48px. Used by Manifest JSON. -->
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-57x57.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 57x57px. -->
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-60x60.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 60x60px. -->
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-72x72.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 72x72px. Also used by Manifest JSON. -->
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-76x76.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 76x76px. -->
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-114x114.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 114x114px. -->
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-120x120.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 120x120px. -->
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-144x144.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 144x144px. Also used by Manifest JSON. -->
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-152x152.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 152x152px. -->
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-180x180.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 180x180px. -->
<link rel="apple-touch-icon-precomposed" sizes="128x128" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-128x128.png"> <!-- @TODO "Graphics" 1: Create an Apple icon 128x128px. -->



<?php
	/* Open Graph
		Open Graph images are used primarily by Facebook and Google+, but Nebula also utilizes this image for other various functions (ex: desktop notifications) as the default image. The Twitter image also uses og-thumb.png as declared below. Create at least one og-thumb.png image, but this meta can be declared multiple times for alternate graphics! Use PNG to avoid compression artifacts!.
	*/
?>
<?php if ( has_post_thumbnail($post->ID) ) : ?>
	<meta property="og:image" content="<?php get_the_post_thumbnail($post->ID, 'open_graph_large'); ?>" />
	<meta property="og:image" content="<?php get_the_post_thumbnail($post->ID, 'open_graph_small'); ?>" />
<?php endif; ?>

<meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/images/meta/og-thumb.png" /> <!-- @TODO "Graphics" 4: Create at least one Open Graph image. Minimum Size: 600x315px. -->
<meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/images/meta/og-thumb2.png" /> <!-- @TODO "Graphics" 1: Minimum Size: 600x315px. -->



<?php
	/* Twitter
		The default Twitter Card image is the same og-thumb.png that is declared above. Other Twitter Card metadata is set in header.php.
	*/
?>
<meta name="twitter:image" content="<?php echo get_template_directory_uri(); ?>/images/meta/og-thumb.png" />



<?php
	/* Windows Tiles
		Windows Tiles are declared below and four additional sizes (and a duplicate color declaration) are set within browserconfig.xml. Note: I don't know why the image files name dimensions are different than the actual dimensions... It was the recommended name/values.
	*/
?>
<meta name="application-name" content="<?php bloginfo('name') ?>" />
<meta name="msapplication-TileColor" content="#0098d7" /> <!-- @TODO "Graphics" 2: Update this color to match the brand. Be careful if this color is the same as the favicon logo because it won't show up when the live tile triggers. -->
<meta name="msapplication-square70x70logo" content="<?php echo get_template_directory_uri(); ?>/images/meta/tiny.png" /> <!-- @TODO "Graphics" 1: Create Windows Tile graphic 70x70px (max: 200kb). -->
<meta name="msapplication-square150x150logo" content="<?php echo get_template_directory_uri(); ?>/images/meta/square.png" /> <!-- @TODO "Graphics" 1: Create Windows Tile graphic 150x150px (max: 200kb, make sure there is enough padding around the edges for the application name). -->
<meta name="msapplication-wide310x150logo" content="<?php echo get_template_directory_uri(); ?>/images/meta/wide.png" /> <!-- @TODO "Graphics" 1: Create Windows Tile graphic 310x150px (max: 200kb, make sure there is enough padding around the edges for the application name). -->
<meta name="msapplication-square310x310logo" content="<?php echo get_template_directory_uri(); ?>/images/meta/large.png" /> <!-- @TODO "Graphics" 1: Create Windows Tile graphic 310x310px (max: 200kb, make sure there is enough padding around the edges for the application name). -->
<meta name="msapplication-notification" content="frequency=30;polling-uri=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=1;polling-uri2=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=2;polling-uri3=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=3;polling-uri4=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=4;polling-uri5=http://notifications.buildmypinnedsite.com/?feed=<?php bloginfo('rss_url'); ?>&amp;id=5; cycle=1" />