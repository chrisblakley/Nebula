<?php
/**
 * Theme Footer
 */
?>
			<hr class="zero" style="margin-top: 30px;"/>

			<div id="footer">
				<?php include_once('includes/footer_widgets.php'); //Footer widget logic. ?>

				<?php if ( has_nav_menu('footer') ): ?>
					<div class="container footerlinks">
						<div class="row powerfootercon">
							<div class="sixteen columns">
								<nav id="powerfooter">
									<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => '2')); ?>
								</nav>
							</div><!--/columns-->
						</div><!--/row-->
					</div><!--/container-->
				<?php endif; ?>

				<div class="container copyright">
					<div class="row">
						<div class="eleven columns ">
							<p>
								<a class="footerlogo" href="<?php echo home_url(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-36x36.png" /></a>
								<?php echo date("Y"); ?> &copy; <a href="<?php echo home_url(); ?>"><strong><?php bloginfo('name'); ?></strong> (v<?php $nebula_theme_info = wp_get_theme(); echo $nebula_theme_info->get('Version'); ?>)</a>, all rights reserved.<br />
								<a href="https://www.google.com/maps/place/<?php echo nebula_full_address(1); ?>" target="_blank"><?php echo nebula_full_address(); ?></a>
							</p>
						</div><!--/columns-->
						<div class="four columns push_one">
							<form class="nebula-search-iconable search footer-search" method="get" action="<?php echo home_url('/'); ?>">
								<input class="nebula-search open input search" type="search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech />
							</form>
						</div><!--/columns-->
					</div><!--/row-->
				</div><!--/container-->

			</div><!--/footer-->

			<?php //Pass data to JavaScript. Not localized with WP because needs to be able to be modified in header.php if desired. ?>
			<script>
				social = [];
				social['facebook_url'] = "<?php echo $GLOBALS['social']['facebook_url']; ?>";
				social['facebook_app_id'] = "<?php echo $GLOBALS['social']['facebook_app_id']; ?>";
				social['twitter_url'] = "<?php echo $GLOBALS['social']['twitter_url']; ?>";
				social['google_plus_url'] = "<?php echo $GLOBALS['social']['google_plus_url']; ?>";
				social['linkedin_url'] = "<?php echo $GLOBALS['social']['linkedin_url']; ?>";
				social['youtube_url'] = "<?php echo $GLOBALS['social']['youtube_url']; ?>";
				social['instagram_url'] = "<?php echo $GLOBALS['social']['instagram_url']; ?>";
			</script>

			<?php //Scripts are loaded in functions.php (so they can be registerred and enqueued). ?>
			<?php wp_footer(); ?>
			<?php do_action('nebula_footer'); ?>

			<script>
				//Check for Youtube Videos
				if ( jQuery('.youtubeplayer').length ){
					var players = {};
					var tag = document.createElement('script');
					tag.src = "https://www.youtube.com/iframe_api";
					var firstScriptTag = document.getElementsByTagName('script')[0];
					firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
				}

				function onYouTubeIframeAPIReady(e){
					jQuery('iframe.youtubeplayer').each(function(i){
						var youtubeiframeClass = jQuery(this).attr('id');
						players[youtubeiframeClass] = new YT.Player(youtubeiframeClass, {
							events: {
								'onReady': onPlayerReady,
								'onStateChange': onPlayerStateChange
							}
						});
					});
				}

				var pauseFlag = false;
				function onPlayerReady(e){
				   //Do nothing
				}
				function onPlayerStateChange(e){
					var videoTitle = e['target']['B']['videoData']['title'];
				    if ( e.data == YT.PlayerState.PLAYING ){
				        ga('send', 'event', 'Videos', 'Play', videoTitle);
				        pauseFlag = true;
				    }
				    if ( e.data == YT.PlayerState.ENDED ){
				        ga('send', 'event', 'Videos', 'Finished', videoTitle, {'nonInteraction': 1});
				    } else if ( e.data == YT.PlayerState.PAUSED && pauseFlag ){
				        ga('send', 'event', 'Videos', 'Pause', videoTitle);
				        pauseFlag = false;
				    }
				}
			</script>

		</div><!--/fullbodywrapper-->
	</body>
</html>