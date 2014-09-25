<?php
/**
 * Theme Footer
 */
?>
			<hr class="zero" style="margin-top: 30px;"/>
			
			<div class="footer">
			
				<?php if ( footerWidgetCounter() != 0 ) : //If no active footer widgets, then this section does not generate. ?>
					<div class="row footerwidgets">
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
				<?php endif; ?>
				
					<div class="container footerlinks">
						<?php if ( has_nav_menu('footer') || has_nav_menu('header') ) : ?>
							<div class="row powerfootercon">
								<div class="sixteen columns">
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
					</div><!--/container-->
					
					<div class="container copyright">
						<div class="row">
							<div class="eleven columns ">
								<p>
									<?php echo date("Y"); ?> &copy; <a href="<?php echo home_url(); ?>"><strong><?php bloginfo('name'); ?></strong></a>, all rights reserved.<br/>
										<a href="https://www.google.com/maps/place/<?php echo nebula_settings_conditional_text_bool('nebula_street_address', $GLOBALS['enc_address'], '760+West+Genesee+Street+Syracuse+NY+13204'); //@TODO: Add address here. ?>" target="_blank"><?php echo nebula_settings_conditional_text_bool('nebula_street_address', $GLOBALS['full_address'], '760 West Genesee Street, Syracuse, NY 13204'); ?></a>
								</p>
							</div><!--/columns-->
							<div class="four columns push_one">
								<form class="search align-right" method="get" action="<?php echo home_url('/'); ?>">
									<input class="nebula-search open input search" type="search" name="s" placeholder="Search" x-webkit-speech/>
								</form>
							</div><!--/columns-->
						</div><!--/row-->
					</div><!--/container-->
			
			</div><!--/footer-->
			
			<?php wp_footer(); ?>
			
			<script>
				//Pull query strings from URL
				function getQueryStrings() {
					queries = new Array(); 
				    var q = document.URL.split('?')[1];
				    if ( q != undefined ){
				        q = q.split('&');
				        for ( var i = 0; i < q.length; i++ ){
				            hash = q[i].split('=');
				            queries.push(hash[1]);
				            queries[hash[0]] = hash[1];
				        }
					}
				}
				
				//Search query strings for the passed parameter
				function GET(query) {
					if ( typeof query === 'undefined' ) {
						return queries;
					}
					
					if ( typeof queries[query] !== 'undefined' ) {
						return queries[query];
					} else if ( queries.hasOwnProperty(query) ) {
						return query;
					}
					return false;
				}
			</script>
			
			<script>
				//Check for Youtube Videos
				if ( jQuery('.youtubeplayer').length ) {
					var players = {};
					var tag = document.createElement('script');
					tag.src = "http://www.youtube.com/iframe_api";
					var firstScriptTag = document.getElementsByTagName('script')[0];
					firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
				}
		
				function onYouTubeIframeAPIReady(e) {
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
		
				//Track Youtube Video Events
				var pauseFlag = false;
				function onPlayerReady(e) {
				   //Do nothing
				}
				function onPlayerStateChange(e) {
				    if (e.data == YT.PlayerState.PLAYING) {
				        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
				        nebula_event('Videos', 'Play', videoTitle);
				        pauseFlag = true;
				    }
				    if (e.data == YT.PlayerState.ENDED) {
				        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
				        nebula_event('Videos', 'Finished', videoTitle, {'nonInteraction': 1});
				    } else if (e.data == YT.PlayerState.PAUSED && pauseFlag) {
				        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
				        nebula_event('Videos', 'Pause', videoTitle);
				        pauseFlag = false;
				    }
				}
			</script>
			
		</div><!--/fullbodywrapper-->
	</body>
</html>