<?php
/**
 * Theme Footer
 */
?>
					<div class="footer" data-0="margin-top: -75px;" data-end="margin-top: -250px;">
						<div class="row logodivider">
							<img src="<?php bloginfo('template_directory');?>/images/footerlogo.png" alt="Footer Logo"/>
						</div><!--/row-->
						<div class="container copyright">
							<div class="row">
								<div class="eight columns">
									<p>
										<?php echo date("Y"); ?> &copy; <a href="<?php echo home_url('/'); ?>"><strong><?php bloginfo('name'); ?></strong></a>, all rights reserved &bull; <a href="http://gearside.com/sitemap.xml" target="_blank">Sitemap</a><br/>
									</p>
								</div><!--/columns-->
								<div class="eight columns">
									<p style="text-align: right;">
										Assistant Interactive Designer at <a class="phg" href="http://www.pinckneyhugo.com/" target="_blank" rel="external"><span class="pinckney">Pinckney</span> <span class="hugo">Hugo</span> <span class="group">Group</span></a>
									</p>
								</div><!--/columns-->
							</div><!--/row-->
						</div><!--/container-->

						<?php if ( current_user_can('manage_options') || array_key_exists('chris', $_GET) || $_SERVER["REMOTE_ADDR"] == '67.249.66.89' ) : //Update to nebula settings fields ?>
							<div class="row adminlinkscon">
								<div class="sixteen columns">
									<?php if ( current_user_can('manage_options') ) : ?>
										<i class="fa fw fa-wrench" title="Logged into Wordpress"></i>
									<?php elseif ( array_key_exists('chris', $_GET) ) : ?>
										<i class="fa fw fa-code" title="Using Admin Query"></i>
									<?php elseif ( $_SERVER["REMOTE_ADDR"] == '67.249.66.89' ) : ?>
										<i class="fa fw fa-home" title="At Home IP Address"></i>
									<?php endif; ?>
									<a href="<?php echo get_admin_url(); ?>" target="_blank">Admin</a> | <a href="<?php echo get_option('nebula_cpanel_url'); ?>" target="_blank">cPanel</a> | <a href="<?php echo get_option('nebula_hosting_url'); ?>" target="_blank">HostGator</a> | <a href="<?php echo get_option('nebula_registrar_url'); ?>">NameCheap</a> | <a href="<?php echo get_option('nebula_ga_url'); ?>" target="_blank">Analytics</a> | <a href="<?php echo get_option('nebula_google_webmaster_tools_url'); ?>" target="_blank">Webmaster Tools</a> | <a href="<?php echo get_option('nebula_google_adsense_url'); ?>" target="_blank">AdSense</a> | <a href="https://console.developers.google.com/project/neon-research-491/apiui/api?authuser=0" target="_blank">Google Dev Console</a>

									<br/>

									<i class="fa fw fa-tachometer"></i> <a href="http://www.alexa.com/siteinfo/gearside.com" target="_blank">Alexa</a> | <a href="http://www.opensiteexplorer.org/comparisons?site=gearsidecreative.com%2F&comparisons[0]=gearsidecreative.com&comparisons[1]=groggie.com" target="_blank">Open Site Explorer</a> | <a href="http://www.webpagetest.org/" target="_blank">WebPageTest</a> | <a href="http://developers.google.com/speed/pagespeed/insights/?url=<?php echo nebula_requested_url(); ?>" target="_blank">Google Page Speed</a> | <a href="http://tools.pingdom.com/fpt/#!/<?php echo nebula_requested_url(); ?>" target="_blank">Pingdom</a> | <a href="https://docs.google.com/spreadsheet/ccc?key=0AtjinqqkCYqpdG1ibDJRU0JQM3E5X0wyVWN5dGh5X3c&usp=sharing" target="_blank">Log</a>
								</div><!--/columns-->
							</div><!--/row-->
						<?php endif; ?>

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
						        ga('send', 'event', 'Videos', 'Play', videoTitle);
						        pauseFlag = true;
						    }
						    if (e.data == YT.PlayerState.ENDED) {
						        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
						        ga('send', 'event', 'Videos', 'Finished', videoTitle);
						    } else if (e.data == YT.PlayerState.PAUSED && pauseFlag) {
						        var videoTitle = e['target']['a']['id'].replace(/-/g, ' ');
						        ga('send', 'event', 'Videos', 'Pause', videoTitle);
						        pauseFlag = false;
						    }
						}
					</script>
				</div><!--/fullbodywrapper-->
			</div><!--/bgsky-->
	</body>
</html>