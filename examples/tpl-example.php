<?php
/**
 * Template Name: Example
 * @TODO: Delete this file before launching the site!
 */

get_header(); ?>

<style>
	.jscookie, .phpcookie {background: red; color: white; padding: 5px;}
		.jscookie:hover, .phpcookie:hover {background: grey; color: white;}
		.jscookie.cookie-on, .phpcookie.cookie-on {background: green; color: white;}
			.jscookie.cookie-on:hover, .phpcookie.cookie-on:hover {background: grey; color: white;}
		
/* CSS Browser Selector Example Styles */
div.cssbs {position: relative; display: table; height: 150px; border: 2px solid #222;}
	div.cssbs:after {width: 100%; height: 100%; line-height: 17px; color: #fff; text-align: center; font-family: 'FontAwesome'; white-space: pre; display: table-cell; vertical-align: middle;}
	div.cssbs:before {content: ''; position: absolute; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.2));}
	.win.chrome div.cssbs {background: #4884b8;} .win.chrome div.cssbs:after {content: '\f17a \00A0 Chrome on Windows \A 2008-';}
	.mac.chrome div.cssbs {background: #4884b8;} .mac.chrome div.cssbs:after {content: '\f179 \00A0 Chrome on Mac \A 2008-';}
	.linux.chrome div.cssbs {background: #4884b8;} .linux.chrome div.cssbs:after {content: '\f17c \00A0 Chrome on Linux \A 2008-';}
	.win.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .win.firefox div.cssbs:after {content: '\f17a \00A0 Firefox on Windows \A 2002-';}
	.mac.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .mac.firefox div.cssbs:after {content: '\f179 \00A0 Firefox on Mac \A 2002-';}
	.linux.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .linux.firefox div.cssbs:after {content: '\f17c \00A0 Firefox on Linux \A 2002-';}
	.safari div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .safari div.cssbs:after {content: '\f179 \00A0 Safari \A 2003-';}
	.opera div.cssbs {background: #e53141; border: 2px solid #9b1624;} .opera div.cssbs:after {content: 'Opera \A 1995-';}
	.ie div.cssbs {background: #2ebaee;} .ie div.cssbs:after {content: '\f17a \00A0 Internet Explorer \A 1995-';}
	.ie5 div.cssbs {background: #3ea3e2;} .ie5 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 5 \A 1998-';}
	.ie6 div.cssbs {background: #3696e9; border: 2px solid #72f0fc;} .ie6 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 6 \A 2001-2014';}
	.ie7 div.cssbs {background: #1374ae; border: 2px solid #f4b619;} .ie7 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 7 \A 2006-2017';}
	.ie8 div.cssbs {background: #1374ae; border: 2px solid #f4b619;} .ie8 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 8 \A 2009-2020';}
	.ie9 div.cssbs {background: #3aa8de; border: 2px solid #fbd21e;} .ie9 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 9 \A 2011-2020';}
	.ie10 div.cssbs {background: #2b6bec;} .ie10 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 10 \A 2012-2023';}
	.ie11 div.cssbs {background: #2ebaee;} .ie11 div.cssbs:after {content: '\f17a \00A0 Internet Explorer 11 \A 2013-2023';}
	.android div.cssbs {background: #a5c93a; border: 2px solid #a5c93a;} .android div.cssbs:after {content: '\f17b \00A0 Android \A 2008-';}
	.android.chrome div.cssbs {background: #4884b8;} .android.chrome div.cssbs:after {content: '\f17b \00A0 Chrome on Android \A 2012-';}
	.iphone div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .iphone div.cssbs:after {content: '\f179 \00A0 iPhone \A 2007-';}
	.iphone.chrome div.cssbs {background: #4884b8;} .iphone.chrome div.cssbs:after {content: '\f179 \00A0 Chrome on iPhone \A 2012-';}
	.ipad div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .ipad div.cssbs:after {content: '\f179 \00A0 iPad \A 2010-';}
	.ipad.chrome div.cssbs {background: #4884b8;} .ipad.chrome div.cssbs:after {content: '\f179 \00A0 Chrome on iPad \A 2012-';}
</style>

<script src="<?php bloginfo('template_directory');?>/js/libs/cssbs.js" <?php echo $GLOBALS["async"]; ?>></script> <!-- This script is found in the footer. It is disabled by default, so it needs to be called again here to show the CSS Browser Selector example section. -->

<section><!-- Do not duplicate this section because it has inline styles. -->
	<div class="container" style="background: #0098d7;">
		<div class="row">
			<div class="sixteen columns">
				<h1 class="entry-title" style="color: #fff;"><?php the_title(); ?></h1>
				<p style="color: #fff;"><?php echo $cfs->get('description'); ?></p>				
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="container" style="background-color: rgba(0,0,0,0.0225); margin-bottom: 30px;">
	<div class="row">
		<div class="sixteen columns">
			<? the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
	<hr/>
</div><!--/container-->

<div class="container">
	<div class="row">
		<div class="eleven columns">
			
			<div class="container">
				<div class="row">
					<div class="sixteen columns entry-content">
						<?php if ( $cfs->get('usage') ) : ?>
							<h2>Usage</h2>
							<?php echo do_shortcode($cfs->get('usage')); ?>
							<br/>
						<?php endif; ?>
						
						<?php if ( $cfs->get('parameters') ) : ?>
							<h2>Parameters</h2>
							<p><?php echo $cfs->get('parameters'); ?></p>
						<?php endif; ?>
						
						<?php if ( $cfs->get('example') ) : ?>
							<h2>Example</h2>
							<?php echo do_shortcode($cfs->get('example')); ?>
							<br/>
						<?php endif; ?>
						<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
							<?php the_content(); ?>
						<?php endwhile; ?>
					</div><!--/columns-->
				</div><!--/row-->
				
				
				<?php
				/*==========================
				 Hard-Code Example
				 ===========================*/
				?>
					
					<?php if ( is_page(208) ) : //Basic Wordpress Query ?>
					
						<div class="row multi-column-query">
							<?php $count = 0; ?>
							<?php query_posts( array( 'category_name' => 'Documentation', 'showposts' => 4, 'paged' => get_query_var('paged') ) ); ?>
								<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
							        <?php if ( $count%2 == 0 && $count != 0 ) : ?>
							            </div><!--/row-->
							            <div class="row multi-column-query">
							        <?php endif; ?>
							                     
							        <div class="eight columns">
									    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									        <h2 class="news-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
									        							        
									        <div class="entry-meta">
									        	<hr/>
									        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
									        	<hr/>
									        </div>
									        
									        <div class="entry-content">
									            <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
									            
									            <?php if ( current_user_can('manage_options') ) : ?>
													<div class="container entry-manage">
														<div class="row">
															<div class="sixteen columns">
																<hr/>
																<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
																<hr/>
															</div><!--/columns-->
														</div>
													</div>
												<?php endif; ?>
									            
									        </div><!-- .entry-content -->
									    </article><!-- #post-## -->
									</div><!--/columns-->
							                         
							        <?php $count++; ?>
							    <?php endwhile; ?>
							    
							</div><!--/row-->
							
							<?php if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ) : ?>
						    	<?php wp_pagenavi(); ?>
						    <?php else : ?>
						    	<?php
									global $wp_query;
									$big = 999999999; //An unlikely integer
									echo '<div class="wp-pagination">' . paginate_links(array(
										'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
										'format' => '?paged=%#%',
										'current' => max(1, get_query_var('paged')),
										'total' => $wp_query->max_num_pages
									)) . '</div>';
								?>
						    <?php endif; ?>
						    
						    <?php wp_reset_query(); ?>
						    
					<?php endif; //End Basic Wordpress Query ?>
					
					
					<?php if ( is_page(318) ) : //Vimeo Meta ?>
							<div class="row">
								<div class="eight columns">
									<?php vimeo_meta('97428427'); ?>
									
									<article class="vimeo video">
										<iframe id="<?php echo $vimeo_meta['safetitle']; ?>" class="vimeoplayer" src="http://player.vimeo.com/video/<?php echo $vimeo_meta['id']; ?>?api=1&player_id=<?php echo $vimeo_meta['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
									</article>
												
									<br/>
									<div class="container">
										<div class="row">
											<div class="four columns">
												<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><img src="<?php echo $vimeo_meta['thumbnail']; ?>" width="100"/></a>
											</div><!--/columns-->
											<div class="twelve columns">
													<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><?php echo $vimeo_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $vimeo_meta['duration']; ?>)</span>
													<span style="display: block; font-size: 12px; line-height: 18px;">
														by <?php echo $vimeo_meta['user']; ?><br/>
														<?php echo $vimeo_meta['description']; ?>
													</span>
											</div><!--/columns-->
										</div><!--/row-->
									</div><!--/container-->
															
								</div><!--/columns-->
								<div class="eight columns">
									
									<?php vimeo_meta('27855315'); ?>
									
									<article class="vimeo video">
										<iframe id="<?php echo $vimeo_meta['safetitle']; ?>" class="vimeoplayer" src="http://player.vimeo.com/video/<?php echo $vimeo_meta['id']; ?>?api=1&player_id=<?php echo $vimeo_meta['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
									</article>
																
									<br/>
									<div class="container">
										<div class="row">
											<div class="four columns">
												<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><img src="<?php echo $vimeo_meta['thumbnail']; ?>" width="100"/></a>
											</div><!--/columns-->
											<div class="twelve columns">
													<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><?php echo $vimeo_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $vimeo_meta['duration']; ?>)</span>
													<span style="display: block; font-size: 12px; line-height: 18px;">
														by <?php echo $vimeo_meta['user']; ?><br/>
														<?php echo $vimeo_meta['description']; ?>
													</span>
											</div><!--/columns-->
										</div><!--/row-->
									</div><!--/container-->
									
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Vimeo Meta ?>
					
					
					<?php if ( is_page(263) ) : //Youtube Meta ?>
							<div class="row">
								<div class="eight columns">
									<?php youtube_meta('jtip7Gdcf0Q'); ?>
									
									<article class="youtube video">
										<iframe id="<?php echo $youtube_meta['safetitle']; ?>" class="youtubeplayer" width="560" height="315" src="http://www.youtube.com/embed/<?php echo $youtube_meta['id']; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo $youtube_meta['origin']; ?>&rel=0" frameborder="0" allowfullscreen=""></iframe>
									</article>
												
									<br/>
									<div class="container">
										<div class="row">
											<div class="four columns">
												<a href="<?php echo $youtube_meta['href']; ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $youtube_meta['id']; ?>/hqdefault.jpg" width="100"/></a>
											</div><!--/columns-->
											<div class="twelve columns">
													<a href="<?php echo $youtube_meta['href']; ?>" target="_blank"><?php echo $youtube_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $youtube_meta['duration']; ?>)</span>
													<span style="display: block; font-size: 12px; line-height: 18px;">
														by <?php echo $youtube_meta['author']; ?><br/>
														<?php echo $youtube_meta['content']; ?>
													</span>
											</div><!--/columns-->
										</div><!--/row-->
									</div><!--/container-->
															
								</div><!--/columns-->
								<div class="eight columns">
									
									<?php youtube_meta('fjh61K3hyY0'); ?>
									
									<article class="youtube video">
										<iframe id="<?php echo $youtube_meta['safetitle']; ?>" class="youtubeplayer" width="560" height="315" src="http://www.youtube.com/embed/<?php echo $youtube_meta['id']; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo $youtube_meta['origin']; ?>" frameborder="0" allowfullscreen=""></iframe>
									</article>
																
									<br/>
									<div class="container">
										<div class="row">
											<div class="four columns">
												<a href="<?php echo $youtube_meta['href']; ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $youtube_meta['id']; ?>/hqdefault.jpg" width="100"/></a>
											</div><!--/columns-->
											<div class="twelve columns">
													<a href="<?php echo $youtube_meta['href']; ?>"><?php echo $youtube_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $youtube_meta['duration']; ?>)</span>
													<span style="display: block; font-size: 12px; line-height: 18px;">
														by <?php echo $youtube_meta['author']; ?><br/>
														<?php echo $youtube_meta['content']; ?>
													</span>
											</div><!--/columns-->
										</div><!--/row-->
									</div><!--/container-->
									
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Youtube Meta ?>
					

					<?php if ( is_page(214) ) : //Nebula Meta ?>
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
									<hr/>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Nebula Meta ?>
					
					
					<?php if ( is_page(224) ) : //Nebula the Excerpt ?>
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php echo nebula_the_excerpt(1, 'Read More &raquo;', 35, 1); ?>
									<hr/>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Nebula the Excerpt ?>
					
					
					<?php if ( is_page(228) ) : //Nebula Manage ?>
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
									<hr/>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Nebula Manage ?>
					
					
					<?php if ( is_page(258) ) : //CSS Browser Selector ?>
							<div class="row">
								<div class="six columns">
									<div class="cssbs"></div>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End CSS Browser Selectore ?>
					
					<?php if ( is_page(277) ) : //Retina ?>
						<div class="row">
							<div class="eight columns">
								<p>This image is only a standard-resolution.</p>
								<img src="<?php bloginfo('template_directory');?>/examples/images/example.jpg" />
							</div><!--/columns-->
							<div class="eight columns">
								<p>This image has a retina backup.</p>
								<img src="<?php bloginfo('template_directory');?>/examples/images/example.jpg" gumby-retina />
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Retina ?>
	
					
					<?php if ( is_page(3519) ) : //Slider @TODO: In progress ?>
						<style>
							div.nebula-slider {position: relative; overflow: hidden;}
								
								ul.nebula-slide-con.reset {position: relative; width: 300%; /* 300% will be calculated in PHP */ left: 0; margin: 0;}
									ul.nebula-slide-con.reset li.nebula-slide {position: relative; display: inline-block; width: 33.334%; /* width will be calculated in PHP */ margin: 0; padding: 0; float: left;}
										ul.nebula-slide-con.reset li.nebula-slide img {width: 100%;}
										
								ul.nebula-slide-con.fade {position: relative; width: 100%; left: 0; margin: 0; height: 0px;}
									ul.nebula-slide-con.fade li.nebula-slide {position: absolute; top: 0; display: block; width: 100%; margin: 0; padding: 0;}
										ul.nebula-slide-con.fade li.nebula-slide img {width: 100%;}
						</style>
						
						<script>
							//@TODO: All selectors and variables MUST be unique to that slider (have an ID as a required parameter)
							
							/*
								Parameters:
									[slider]
										mode
										transition time
										hold time
										
								
							*/
							
							jQuery(document).ready(function() {
								jQuery('ul.nebula-slide-con.fade li:nth-last-child(2)').addClass('next'); //nth-child number will have to be calculated via PHP (total-1) [or do css nth from end]
								jQuery('ul.nebula-slide-con.fade li:last-child').addClass('active');
							});
							
							jQuery(window).on('load', function() {
								var nebulaSlideCount = 3; //this number will be sent via PHP counting the slides
								var currentSlide = 1;
								
								var activeHeight = jQuery('ul.nebula-slide-con.fade li:last-child').height();
								jQuery('ul.nebula-slide-con.fade').css('height', activeHeight);
								
								if (nebulaSlideCount > 1) {
									var nebulaSlider = setInterval(function(){
										
										//@TODO: Only the chosen mode will actually return to the frontend
										
										//With carriage mode animation to first frame
										if ( currentSlide < nebulaSlideCount ) {
											jQuery('ul.nebula-slide-con.reset').animate({
												left: '-=100%',
											}, 1000, 'easeInOutCubic', function() { //easing can be a parameter, same with transition speed
												currentSlide++;
											});
										} else {
											jQuery('ul.nebula-slide-con.reset').animate({
												left: '0',
											}, 1000, 'easeInOutCubic', function() { //easing can be a parameter, same with transition speed
												currentSlide = 1;
											});
										}
										
										//Just keeps going using fade mode
										jQuery('ul.nebula-slide-con.fade li:last-child').fadeOut(1000, function(){ //transition speed will be a parameter
											jQuery('ul.nebula-slide-con.fade li.next').removeClass('next');
											jQuery('ul.nebula-slide-con.fade li.active').removeClass('active');
											jQuery(this).clone().prependTo('ul.nebula-slide-con.fade');
											jQuery(this).remove();
											jQuery('ul.nebula-slide-con.fade li:first-child').css('display', 'block');
											jQuery('ul.nebula-slide-con.fade li:last-child').addClass('active');
											jQuery('ul.nebula-slide-con.fade li:nth-child(2)').addClass('next'); //nth-child number will have to be calculated via PHP (total-1)
										});
										
										activeHeight = jQuery('ul.nebula-slide-con.fade li.nebula-slide.active img').height();
										nextHeight = jQuery('ul.nebula-slide-con.fade li.nebula-slide.next img').height();
										if ( nextHeight >= activeHeight ) {
											jQuery('ul.nebula-slide-con.fade').delay(500).animate({ //delay will be calculated based on transition speed
												height: nextHeight,
											}, 500, 'easeInOutCubic'); //resize speed will be calculated based on transition speed
										} else {
											jQuery('ul.nebula-slide-con.fade').animate({
												height: nextHeight,
											}, 500, 'easeInOutCubic'); //resize speed will be calculated based on transition speed
										}
																											
									}, 5000); //Slide time will be a parameter
								}
							});
						</script>
						
						<div class="row">
							<div class="sixteen columns">
								<div class="nebulaframe">
									<div class="nebula-slider">
										<ul class="nebula-slide-con clearfix fade">
											<li class="nebula-slide clearfix">
												<img src="http://www.placebear.com/700/300"/>
											</li>
											<li class="nebula-slide clearfix">
												<img src="http://www.placebear.com/700/400"/>
											</li>
											<li class="nebula-slide clearfix">
												<img src="http://placehold.it/700x500"/>
											</li>
										</ul>
									</div>
								</div>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Slider ?>
					
					
					<?php if ( is_page(359) ) : //AJAX @TODO: In progress ?>
						<style>
							.level-four-media-query { border: 1px solid red; padding: 5px;}
								.level-four-media-query:before {content: 'Script not supported'; display: block; text-align: center; color: red;}
								.level-four-media-query:after {content: 'Luminosity not supported'; display: block; text-align: center; color: red;}
								.level-four-media-query div:before {content: 'Pointer not supported'; display: block; text-align: center; color: red;}
								.level-four-media-query div:after {content: 'Hover not supported'; display: block; text-align: center; color: red;}
							
							@media (script) {
								.level-four-media-query { border: 1px solid green; }
									.level-four-media-query:before {content: 'Scripts Enabled'; color: green;}
							}
							
							@media (luminosity: dim) {
								.level-four-media-query { background: white; }
									.level-four-media-query:after {content: 'Dim Luminosity'; color: green;}
							}
							
							@media (luminosity: washed) {
								.level-four-media-query { background: black; }
									.level-four-media-query:after {content: 'Washed Luminosity'; color: green;}
							}
							
							@media (luminosity: normal) {
								.level-four-media-query { background: grey; }
									.level-four-media-query:after {content: 'Normal Luminosity'; color: green;}
							}
							
							@media (pointer: coarse) {
								.level-four-media-query { height: 100px; }
									.level-four-media-query div:before {content: 'Coarse Pointer'; color: green;}
							}
							
							@media (pointer: fine) {
								.level-four-media-query { height: auto; }
									.level-four-media-query div:before {content: 'Fine Pointer'; color: green;}
							}
							
							@media (pointer: fine) {
								.level-four-media-query { height: auto; }
									.level-four-media-query div:before {content: 'No Pointer...?'; color: green;}
							}
							
							@media (hover) { /* ...or is it (hover: 1) */
								.level-four-media-query:hover { background: green; border: 2px solid forestgreen; font-weight: bold; text-decoration: underline; }
									.level-four-media-query div:after {content: 'Hover Available'; color: green;}
							}							
						</style>
						<div class="row">
							<div class="sixteen columns">
								<div class="level-four-media-query">
									<div></div>
								</div>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End AJAX ?>
					
					<?php if ( is_page(356) ) : //AJAX @TODO: In progress ?>
						<div class="row">
							<div class="sixteen columns">
								
								<p>AJAX Example coming soon</p>
								
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End AJAX ?>
					
					
					<?php if ( is_page(346) ) : //PHP Mobile Detect ?>
						<div class="row">
							<div class="sixteen columns">
								<?php if ( $GLOBALS["mobile_detect"]->isMobile() ) : ?>
									<?php if ( $GLOBALS["mobile_detect"]->isTablet() ) : ?>
										<?php if ( $GLOBALS["mobile_detect"]->isIOS() ) : ?>
											<p>You are using an <strong>iPad</strong>.</p>
										<?php elseif ( $GLOBALS["mobile_detect"]->is('AndroidOS') ) : ?>
											<?php if ( $GLOBALS["mobile_detect"]->isSamsung() ) : ?>
												<p>You are using a Samsung tablet.</p>
											<?php else : ?>
												<p>You are using an Android tablet or other tablet device.</p>
											<?php endif; ?>
										<?php else : ?>
											<p>You are using a tablet.</p>
										<?php endif; ?>
									<?php else : ?>
										<?php if ( $GLOBALS["mobile_detect"]->isIOS() ) : ?>
											<p>You are using an <strong>iPhone</strong>.</p>
										<?php elseif ( $GLOBALS["mobile_detect"]->is('AndroidOS') ) : ?>
											<?php if ( $GLOBALS["mobile_detect"]->isSamsung() ) : ?>
												<p>You are using a Samsung phone or other Samsung mobile device.</p>
											<?php else : ?>
												<p>You are using an Android phone or other mobile device.</p>
											<?php endif; ?>
										<?php else : ?>
											<p>You are using a phone or other mobile device.</p>
										<?php endif; ?>
									<?php endif; ?>
								<?php else : ?>
									<p>You are <strong>not</strong> using a mobile device or tablet.</p>
								<?php endif; ?>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End PHP Mobile Detect ?>
					
				
					<?php if ( is_page(89) ) : //Google Maps Iframe ?>
							<div class="row">
								<div class="sixteen columns">
									<iframe class="googlemap nebulaborder"
										width="100%"
										height="250"
										frameborder="0"
										src="https://www.google.com/maps/embed/v1/place
										?key=AIzaSyArNNYFkCtWuMJOKuiqknvcBCyfoogDy3E
										&q=Pinckney+Hugo+Group
										&zoom=14
										&maptype=roadmap">
									</iframe>
									<div class="nebulashadow floating" offset="-6"></div>
									
									<small>Seen above with a nebulaborder and floating nebulashadow (to examine when browsers begin supporting better data-attributes in CSS)</small>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Google Maps Iframe ?>
				
				
					<?php if ( is_page(267) ) : //Google Maps Javascript API v3 ?>
						<div class="row">
							<div class="sixteen columns">
								
								<div class="container">
									<div class="row">
										<div class="eight columns">
											<ul>
												<li><strong>Example Locations</strong></li>
												<li class="latlngcon"><i class="icon-location" style="color: #fe7569;"></i> <span class="lat">43.109205</span>, <span class="lng">-76.095831</span></li>
												<li class="latlngcon"><i class="icon-location" style="color: #fe7569;"></i> <span class="lat">43.093068</span>, <span class="lng">-76.163809</span></li>
												<li class="latlngcon"><i class="icon-location" style="color: #fe7569;"></i> <span class="lat">43.100150</span>, <span class="lng">-76.207207</span></li>
											</ul>
										</div><!--/columns-->
										<div class="eight columns">
											<ul>
												<li><i class="mapweather-icon fa fa-cloud fa-fw inactive"></i> <a class="mapweather" href="#">Enable Weather</a></li>
												<li><i class="maptraffic-icon fa fa-car fa-fw inactive"></i> <a class="maptraffic" href="#">Enable Traffic</a></li>
												<li><i class="mapgeolocation-icon fa fa-location-arrow fa-fw inactive"></i> <a class="mapgeolocation" href="#">Detect Location</a></li>
												<li><i class="maprefresh-icon fa fa-refresh fa-fw inactive"></i> <a class="maprefresh" href="#">Refresh Map</a></li>
											</ul>
										</div><!--/columns-->
									</div><!--/row-->
								</div><!--/container-->
								
								<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=weather"></script>
								<div class="googlemapcon nebulaframe">
									<div id="map_canvas" class="googlemap"></div>
								</div>
								<br/>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Google Maps Javascript API v3 ?>
				
				<?php
				/*==========================
				 End Hard-Code Example
				 ===========================*/
				?>
				 
			</div><!--/container-->
			
		</div><!--/columns-->
		<div class="four columns push_one">
			<ul class="xoxo">
				<li>
					<?php wp_nav_menu(array('menu' => 'Documentation', 'depth' => '9999')); ?>
				</li>
			</ul>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>