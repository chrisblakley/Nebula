<?php
	/* Favicons
		Favicons for various usage. PNG icons are used as needed by browsers in addition to Android homescreen bookmarks.
	*/
?>
<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon.ico">
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-16x16.png" sizes="16x16">
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-32x32.png" sizes="32x32">
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-96x96.png" sizes="96x96">
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-160x160.png" sizes="160x160">
<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-192x192.png" sizes="192x192">



<?php
	/* Apple iOS
		iOS icons for homescreen bookmarks and startup image. For certain Android devices the apple-touch-icon and apple-touch-icon-precomposed are used for homescreen icons.
	*/
?>
<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-startup.png">
<link rel="apple-touch-icon" sizes="36x36" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-36x36.png">
<link rel="apple-touch-icon" sizes="48x48" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-48x48.png">
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-180x180.png">
<link rel="apple-touch-icon-precomposed" sizes="128x128" href="<?php echo get_template_directory_uri(); ?>/images/meta/apple-touch-icon-128x128.png">



<?php
	/* Open Graph
		Open Graph images are used primarily by Facebook and Google+, but Nebula also utilizes this image for other various functions (ex: desktop notifications) as the default image. The Twitter image also uses og-thumb.png as declared below. Create at least one og-thumb.png image, but this meta can be declared multiple times for alternate graphics! Use og-temp.png as a template (Use PNG to avoid compression artifacts!).
	*/
?>
<meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/images/meta/og-thumb.png" />
<meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/images/meta/og-thumb2.png" />



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
<meta name="msapplication-TileColor" content="#222222">
<meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/images/meta/mstile-144x144.png">
<meta name="msapplication-config" content="<?php echo get_template_directory_uri(); ?>/includes/ieconfig.xml">

