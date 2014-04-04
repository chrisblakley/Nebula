<?php
/**
 * Theme Header
 */
?><!DOCTYPE html>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie6 lt-ie7 lt-ie8 lt-ie9"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie7 lt-ie7 lt-ie8 lt-ie9"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="no-js ie ie8 lt-ie8 lt-ie9"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="no-js ie ie9 lt-ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="<?php echo (array_key_exists('debug', $_GET)) ? 'debug' : ''; ?> no-js "><!--<![endif]-->
	<head>
		<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
		<meta charset="<?php bloginfo('charset'); ?>" />

		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/css/normalize.min.css" />
		<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/css/gumby.css" />
		<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/css/font-awesome.min.css"> <!-- @TODO: Remove if not using Font Awesome! -->
		<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/css/jquery.mmenu.all.css" /> <!-- @TODO: Remove if not using mmenu! -->
		<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" />
        
        <meta name="viewport" content="width=device-width, initial-scale=1" /><?php if (1==2): //@TODO: Determine if maximum-scale should be omitted! ?> <!-- , maximum-scale=1 --> <?php endif; ?>
        
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
		
		<meta name="author" content="<?php bloginfo('template_directory');?>/humans.txt">
		
		<!-- Facebook Metadata -->
		<meta property="fb:page_id" content="" />
		<meta property="og:image" content="" />
		<meta property="og:description" content=""/>
		<meta property="og:title" content="<?php bloginfo('name'); ?>"/>
		<meta property="og:image" content="<?php bloginfo('template_directory');?>/images/fb-thumb1.jpg"/> <!-- @TODO: Create at least one new Facebook Thumbnail: 200x200px -->
	    <meta property="og:image" content="<?php bloginfo('template_directory');?>/images/fb-thumb2.jpg"/>
		
		<!-- Google+ Metadata -->
		<meta itemprop="name" content="<?php bloginfo('name'); ?>">
		<meta itemprop="description" content="">
		<meta itemprop="image" content="">

		<!--Microsoft Windows 8 Tiles /-->
		<meta name="application-name" content="<?php bloginfo('name'); ?>"/>
		<meta name="msapplication-TileColor" content="#ffffff"/>
		<meta name="msapplication-square70x70logo" content="<?php bloginfo('template_directory');?>/images/tiny.png"/><!--128x128-->
		<meta name="msapplication-square150x150logo" content="<?php bloginfo('template_directory');?>/images/square.png"/><!--270x270-->
		<meta name="msapplication-wide310x150logo" content="<?php bloginfo('template_directory');?>/images/wide.png"/><!--558x270-->
		<meta name="msapplication-square310x310logo" content="<?php bloginfo('template_directory');?>/images/large.png"/><!--517x516-->

		<script type='text/javascript' src="<?php bloginfo('template_directory');?>/js/libs/modernizr.custom.42059.js" defer></script>
		
		<script>
			var bloginfo = [];
			bloginfo['name'] = "<?php echo bloginfo('name'); ?>";
			bloginfo['template_directory'] = "<?php echo bloginfo('template_directory'); ?>";
			bloginfo['stylesheet_url'] = "<?php echo bloginfo('stylesheet_url'); ?>";
			bloginfo['home_url'] = "<?php echo home_url(); ?>";
		</script>
		
		<script> //Universal Analytics
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', 'UA-00000000-1', 'domainnamegoeshere.com');
		  ga('send', 'pageview');
		</script>
		
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<div id="fullbodywrapper">
		
		<?php //Facebook App ID: ###############, Access Token: ######################## ?>
		<div id="fb-root"></div>
		<script type="text/javascript">
			window.fbAsyncInit = function() {
		    //Initialize the Facebook JavaScript SDK
		    FB.init({
		      appId      : '###############', //@TODO: Replace with client's FB App ID!
		      channelUrl : '<?php bloginfo("template_directory");?>/includes/channel.html',
		      status     : true,
		      xfbml      : true
		    });
		    							
			//Facebook Likes
			FB.Event.subscribe('edge.create', function(href, widget) {
				var currentPage = jQuery(document).attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Like',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Like', currentPage);
			});
			
			//Facebook Unlikes
			FB.Event.subscribe('edge.remove', function(href, widget) {
				var currentPage = jQuery(document).attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Unlike',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Unlike', currentPage);
			});
			
			//Facebook Send/Share
			FB.Event.subscribe('message.send', function(href, widget) {
				var currentPage = jQuery(document).attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Send',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Share', currentPage);
			});
			
			//Facebook Comments
			FB.Event.subscribe('comment.create', function(href, widget) {
				var currentPage = jQuery(document).attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Comment',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Comment', currentPage);
			});
				
		  };
		 
		  //Load the SDK asynchronously
		  (function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_GB/all.js";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
		</script>
										
		<div id="topbarcon">
			<div class="container mobilenavcon">
				<div class="row">
					<div class="sixteen columns clearfix">
						
						<a class="alignleft" href="#mobilenav"><i class="icon-menu"></i></a>
						<nav id="mobilenav">
							<?php wp_nav_menu(array('theme_location' => 'mobile', 'depth' => '9999')); ?>
						</nav><!--/mobilenav-->
						
						
						<a class="alignright" href="#mobilecontact"><i class="icon-users"></i></a>
						<nav id="mobilecontact" class="unhideonload hidden">
							<ul>
					    		<li>
					    			<a href="#"><i class="icon-phone"></i> (315) 123-4567</a>
					    		</li>
					    		<li>
					    			<a href="#"><i class="icon-phone"></i> (800) 456-7890</a>
					    		</li>
					    		<li>
					    			<a href="#"><i class="icon-mail"></i> info@testing.com</a>
					    		</li>
					    		<li>
					    			<a class="directions" href="https://www.google.com/maps?saddr=My+Location&daddr=760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">
					    				<i class="icon-direction"></i> Directions <br/><div><small>760 West Genesee Street<br/>Syracuse, NY 13204</small></div>
					    			</a>
					    		</li>
					    	</ul>
						</nav><!--/mobilecontact-->
						
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
		</div><!--/topbarcon-->

		<div class="container topnavcon">
			<div class="row">
				<div class="sixteen columns">
					<nav id="topnav">
	        			<?php wp_nav_menu(array('theme_location' => 'topnav', 'depth' => '1')); ?>
	        		</nav>
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
		
		
		<div id="logonavcon" class="container">
			<div class="row">
				<div class="six columns">
					<?php
						//@TODO: Logo should have at least two versions: logo.svg and logo.png - Save them out in the images directory then update the paths (and alt text) below.
						//Important: Do not delete the /phg/ directory from the server; we use our logo in the WP Admin!
					?>
					<a class="logocon" href="<?php echo home_url(); ?>">
						<img src="<?php bloginfo('template_directory');?>/images/logo.svg" onerror="this.onerror=null; this.src='<?php bloginfo('template_directory');?>/images/logo.png'" alt="Pinckney Hugo Group"/>
					</a>
				</div><!--/columns-->
				<div class="ten columns">
					<nav id="mainnav" class="clearfix">
	        			<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '2')); ?>
	        		</nav>
	        	</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
		
		<div class="container fixedbar" style="position: fixed; top: 0; left: 0; z-index: 9999;">
			<div class="row">
				<div class="three columns">
					<a href="<?php echo home_url(); ?>"><i class="icon-home"></i><?php echo bloginfo('name'); ?></a>
				</div>
			</div>
		</div>