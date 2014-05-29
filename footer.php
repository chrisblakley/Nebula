<?php
/**
 * Theme Footer
 */
?>

<div class="footer">

<?php wp_footer(); ?>

	<?php if ( footerWidgetCounter() != 0 ) : //If no active footer widgets, then this section does not even generate. ?>
	<!-- Footer Widgets -->
		<div class="container footerwidgets">
			<div class="row">
				<?php if ( footerWidgetCounter() == 4 ) : ?>
					<div class="four columns">
						<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('First Footer Widget Area') ) : ?>
							<?php //First Footer Widget Area ?>
						<?php endif; ?>
					</div><!--/columns-->
					<div class="four columns">
						<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Second Footer Widget Area') ) : ?>
							<?php //Second Footer Widget Area ?>
						<?php endif; ?>
					</div><!--/columns-->
					<div class="four columns">
						<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Third Footer Widget Area') ) : ?>
							<?php //Third Footer Widget Area ?>
						<?php endif; ?>
					</div><!--/columns-->
					<div class="four columns">
						<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Fourth Footer Widget Area') ) : ?>
							<?php //Fourth Footer Widget Area ?>
						<?php endif; ?>
					</div><!--/columns-->
				<?php elseif ( footerWidgetCounter() == 3 ) : ?>
					<div class="four columns">
						<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ) : ?>
							<?php //Outputs the first active widget area it finds. ?>
						<?php endif; ?>
					</div><!--/columns-->
					<div class="four columns">
						<?php if ( dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') ) : ?>
							<?php //Outputs the first active widget area it finds. ?>
						<?php endif; ?>
					</div><!--/columns-->
					<div class="eight columns">
						<?php if ( dynamic_sidebar('Fourth Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ) : ?>
							<?php //Outputs the first active widget area it finds. ?>
						<?php endif; ?>
					</div><!--/columns-->
				<?php elseif ( footerWidgetCounter() == 2 ) : ?>
					<div class="eight columns">
						<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') ) : ?>
							<?php //Outputs the first active widget area it finds (between 1-3). ?>
						<?php endif; ?>
					</div><!--/columns-->
					<div class="eight columns">
						<?php if ( dynamic_sidebar('Fourth Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') ) : ?>
							<?php //Outputs the first active widget area it finds (between 4-2). ?>
						<?php endif; ?>
					</div><!--/columns-->
				<?php else : //1 Active Widget ?>
					<div class="sixteen columns">
						<?php if ( dynamic_sidebar('First Footer Widget Area') || dynamic_sidebar('Second Footer Widget Area') || dynamic_sidebar('Third Footer Widget Area') || dynamic_sidebar('Fourth Footer Widget Area') ) : ?>
							<?php //Outputs the first active widget area it finds. ?>
						<?php endif; ?>
					</div><!--/columns-->
				<?php endif; ?>
				
			</div><!--/row-->
		</div><!--/container-->
	<!-- END Footer Widgets -->
	<?php endif; ?>
	
		<div class="container footerlinks">
			<? if ( has_nav_menu('footer') || has_nav_menu('header') ) : ?>
				<div class="row powerfootercon">
					<div class="sixteen columns">
						<p>This is the power footer. Simply change the menu array and the CSS/JS does the rest.</p>
						<nav id="powerfooter">
							<?php
								if ( has_nav_menu('footer') ) {
									wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2'));
								} elseif ( has_nav_menu('header') ) {
									wp_nav_menu(array('theme_location' => 'header', 'depth' => '2'));
								}
							?>
						</nav>
					</div><!--/columns-->
				</div><!--/row-->
			<?php endif; ?>
			<div class="row">
				<div class="ten columns copyright">
					<p>
						<?php date("Y"); ?> &copy; <a href="<?php echo get_permalink(6); ?>"><strong><?php bloginfo('name'); ?></strong></a>, all rights reserved.<br/>
						<a href="https://www.google.com/maps/place/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a>
					</p>
				</div><!--/columns-->
				<div class="five columns push_one">
					<form class="search" method="get" action="<?php echo home_url('/'); ?>">
						<ul>
							<li class="append field">
							    <input class="xwide text input search" type="text" name="s" placeholder="Search" />
							    <input type="submit" class="medium primary btn submit" value="Go" />
						    </li>
						</ul>
					</form><!--/search-->
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->

</div><!--/footer-->

		<script>
			//If Wordpress can't call jQuery, load it from Google's CDN 
			if (typeof jQuery == 'undefined') {
			    var script = document.createElement('script');
			    script.type = "text/javascript";
			    script.src = "http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"; <?php //@TODO: Always verify this is the desired version of jQuery! ?>
			    document.getElementsByTagName('head')[0].appendChild(script);
			}
		</script>
				
		<script>
			try { (function() {
					var afterPrint = function() {
						ga('send', 'event', 'Print (Intent)', document.location.pathname);
					};
					if (window.matchMedia) {
						var mediaQueryList = window.matchMedia('print');
						mediaQueryList.addListener(function(mql) {
							if (!mql.matches)
							afterPrint();
						});
					}
					window.onafterprint = afterPrint;
				}());
			} catch(e) {}
		</script>
		
		<?php if ( array_key_exists('debug', $_GET) ) : //Render-blocking method of loading scripts for debug purposes. These should be identical to the normal site! ?>
		
			<script src="<?php bloginfo('template_directory');?>/js/libs/jquery.mmenu.min.all.js"></script>
		
			<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
			<!-- <script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script> -->
			<!-- <script src="<?php bloginfo('template_directory');?>/js/libs/supplementr.js"></script> -->
			<!--<script src="<?php bloginfo('template_directory');?>/js/libs/cssbs.js"></script>-->
			<!-- <script src="<?php bloginfo('template_directory');?>/js/libs/doubletaptogo.js"></script> -->
			<script src="<?php bloginfo('template_directory');?>/js/libs/gumby.js"></script>
				
			<!--[if lt IE 9]>
				<script src="<?php bloginfo('template_directory');?>/js/libs/html5shiv.js"></script>
				<script src="<?php bloginfo('template_directory');?>/js/libs/respond.js"></script>
			<![endif]-->
				
			<!--<script src="<?php bloginfo('template_directory');?>/js/libs/gumby.init.js"></script>-->
			<script src="<?php bloginfo('template_directory');?>/js/main.js"></script>
		
		<?php else : //HTML5 Asynchronous/Deferred method of loading scripts ?>
		
			<!-- Asynchronously load external scripts using HTML5 -->
			<script src="<?php bloginfo('template_directory');?>/js/libs/jquery.mmenu.min.all.js"></script> <!-- @TODO: Have to make sure this one loads before main.js! Can it be deferred? -->
			<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" async></script>
			<!-- <script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js" async></script> -->
			<!-- <script src="<?php bloginfo('template_directory');?>/js/libs/supplementr.js" async></script> -->
			<!--<script src="<?php bloginfo('template_directory');?>/js/libs/cssbs.js" async></script>-->
			<!-- <script src="<?php bloginfo('template_directory');?>/js/libs/doubletaptogo.js" defer></script> -->
			<script src="<?php bloginfo('template_directory');?>/js/libs/gumby.js" defer></script>
			
			<!--[if lt IE 9]>
				<script src="<?php bloginfo('template_directory');?>/js/libs/html5shiv.js" defer></script>
				<script src="<?php bloginfo('template_directory');?>/js/libs/respond.js" defer></script>
			<![endif]-->
			
			<!-- Defer loading external scripts using HTML5 -->
			<!--<script src="<?php bloginfo('template_directory');?>/js/libs/gumby.init.js" defer></script>-->
			<script src="<?php bloginfo('template_directory');?>/js/main.js" defer></script>
				
		<?php endif; ?>
		
		<script>
			if ( jQuery('.youtubeplayer').length ) {
				var players = {};
				var tag = document.createElement('script');
				tag.src = "http://www.youtube.com/iframe_api";
				var firstScriptTag = document.getElementsByTagName('script')[0];
				firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
			}
	
			function onYouTubeIframeAPIReady(e) {
				jQuery('iframe.youtubeplayer').each(function(i){
					var iframeClass = jQuery(this).attr('id');
					players[iframeClass] = new YT.Player(iframeClass, {
						events: {
							'onReady': onPlayerReady,
							'onStateChange': onPlayerStateChange
						}
					});
				});
			}
	
			//Track Youtube Video Events
			var pauseFlag = false;
			function onPlayerReady(e) {
			   //Do nothing
			}
			function onPlayerStateChange(e) {
			    if (e.data == YT.PlayerState.PLAYING) {
			        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
			        ga('send', 'event', 'Videos', 'Play', videoTitle);
			        pauseFlag = true;
			    }
			    if (e.data == YT.PlayerState.PAUSED && pauseFlag) {
			        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
			        ga('send', 'event', 'Videos', 'Pause', videoTitle);
			        pauseFlag = false;
			    }
			    if (e.data == YT.PlayerState.ENDED) {
			        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
			        ga('send', 'event', 'Videos', 'Finished', videoTitle);
			    }
			}
		</script>

		
		</div><!--/fullbodywrapper-->
	</body>
</html>