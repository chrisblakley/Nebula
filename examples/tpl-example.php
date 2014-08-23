<?php
/**
 * Template Name: Example
 * @TODO: Delete this file before launching the site!
 */

if ( !defined('ABSPATH') ) { exit; } //Exit if accessed directly

get_header(); ?>

<script src="<?php bloginfo('template_directory');?>/js/libs/cssbs.js" <?php echo $GLOBALS["async"]; ?>></script>

<section><!-- Do not duplicate this section because it has inline styles. -->
	<div class="container" style="background: #0098d7;">
		<div class="row">
			<div class="sixteen columns">
				<h1 class="entry-title" style="color: #fff;"><?php the_title(); ?></h1>
				<p style="color: #fff;"><?php the_field('description'); ?></p>				
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="container" style="background-color: rgba(0,0,0,0.0225); margin-bottom: 30px;">
	<div class="row">
		<div class="sixteen columns">
			<?php the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
	<hr/>
</div><!--/container-->

<?php if ( is_page(680) ) : //Hero Slider ?>
	<div class="container">
		<!-- @TODO: bx-viewport box-shadow: none, border: none, background: none. -->
		<ul class="bxslider heroslider">
			<li><img src="http://placehold.it/1600x500" title="Placeholder" /></li>
			<li><img src="http://placebear.com/1600/500" title="PlaceBear" /></li>
			<li><img src="http://placekitten.com/1600/500" title="PlaceKitten" /></li>
		</ul>
	</div><!--/container-->
<?php endif; //End Hero Slider ?>

<div class="container">
	<div class="row">
		<div class="eleven columns">
			
			<div class="container">
				<div class="row">
					<div class="sixteen columns entry-content">
						<?php if ( get_field('usage') ) : ?>
							<h2>Usage</h2>
							<?php echo do_shortcode(get_field('usage')); ?>
							<br/>
						<?php endif; ?>
						
						<?php if ( get_field('parameters') ) : ?>
							<h2>Parameters</h2>
							<p><?php echo do_shortcode(get_field('parameters')); ?></p>
						<?php endif; ?>
						
						<?php if ( get_field('example') ) : ?>
							<h2>Example</h2>
							<?php echo do_shortcode(get_field('example')); ?>
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
					
					
					<?php if ( is_page(434) ) : //Sticky Post ?>
						<div class="row">
							<div class="sixteen columns sticky-con">
								<?php
			               			$sticky = get_option('sticky_posts');
			               			$args = array(
			               				'posts_per_page' => 1,
			               				'post__in'  => $sticky,
			               				'caller_get_posts' => 1
			               				);
			               			query_posts($args);
			               		?>
								<?php if ($sticky[0]) : ?>
									<ul>
										<?php while ( have_posts() ) : the_post(); ?>
											<li class="sticky-post">
												<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
													<h2 class="news-title entry-title sticky-title"><i class="fa fa-thumb-tack"></i> <a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
									    
													<div class="entry-meta">
														<hr/>
														<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
														<hr/>
													</div>
									
													<div class="entry-content sticky-content">
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
											</li>
										<?php endwhile; ?>
									</ul>
								<?php endif; ?>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Sticky Post ?>
					
					
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
					
					
					<?php if ( is_page(791) ) : //GET() ?>
						<script>
							jQuery(window).on('load', function() {
								//This can be called on document ready normally, but must reside within main.js or else it becomes a race condition.
								if ( GET('hello') ) {
									console.log(GET('hello'));
									nebula_event('GET Example', 'Test query string: ' + GET('hello'));
								}
							});
						</script>
					<?php endif; //End GET() ?>
					
					
					<?php if ( is_page(797) ) : //GET() ?>
						<?php if ( !array_key_exists('debug', $_GET) ) : ?>
							<h2>Refreshing to enable debug mode!</h2>
							<script>
								document.location = "<?php the_permalink(); ?>?debug";
							</script>
						<?php else : ?>
							<script>
								jQuery(document).on('click', 'a.nebula_event', function(){
									nebula_event('Example Nebula Event', 'User Triggered', 'This is the label');
									return false;
								});
							</script>
							
							<div class="medium primary btn">
								<a class="nebula_event" href="#">Trigger sample event</a>
							</div>
						<?php endif; ?>
					<?php endif; //End GET() ?>
					
					
					<?php if ( is_page(722) ) : //Currently Open (Business Hours) ?>
						<div class="row">
							<div class="sixteen columns">
								<?php if ( currently_open() ) : ?>
									<p style="font-size: 32px;"><i class="fa fa-building-o"></i> We are currently <strong style="color: green;">open</strong>!</p>
								<?php else : ?>
									<p style="font-size: 32px;"><i class="fa fa-building"></i> We are currently <strong style="color: maroon;">closed</strong>.</p>
								<?php endif; ?>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Nebula Meta ?>
					
					
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
					
					
					<?php if ( is_page(648) ) : //Facebook Graph API ?>
						<div class="row">
							<div class="sixteen columns">
								<div id="facebook-connect" style="margin-top: 15px;">
									<p><strong>The Facebook SDK has not loaded yet.</strong></p>									
									<img class="fbpicture" /><br/><br/>
									<div class="fb-login-button" data-max-rows="1" data-size="large" data-show-faces="false" data-auto-logout-link="true" scope="public_profile,email" onlogin="checkFacebookLogin();"></div>
								</div>
							</div><!--/columns-->
						</div><!--/row-->
						
						<script>
							jQuery(window).on('load', function(){
								if ( !window.fbAsyncInit ) {
									jQuery('#facebook-connect p strong').text('The Facebook SDK has not loaded yet. Main.js may have triggered before the async FB SDK loaded- Need to fix this bug! (Try refreshing)').css('color', 'red');
								} else {
									jQuery('#facebook-connect p strong').text('Connect with Facebook for this example:');
								}
							});
						</script>
					<?php endif; //End Facebook Graph API ?>
					
					
					<?php if ( is_page(258) ) : //CSS Browser Selector ?>
						<style>
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
							
							<div class="row">
								<div class="six columns">
									<div class="cssbs"></div>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End CSS Browser Selectore ?>
					
					
					<?php if ( is_page(582) ) : //IE Compatibility Mode Detection ?>
						<style>
							div.notie {color: red; font-weight: bold;}
							.ie div.notie {display: none;}
							div.iemode {display: none; position: relative; height: 150px; width: 280px; border: 2px solid #222; background: #ddd;}
								
							/* IE Standards */
							.ie div.iemode {display: table;}
							.ie6 div.iemode {background: #3696e9; border: 2px solid #72f0fc;}
							.ie7 div.iemode {background: #1374ae; border: 2px solid #f4b619;}
							.ie8 div.iemode {background: #1374ae; border: 2px solid #f4b619;}
							.ie9 div.iemode {background: #3aa8de; border: 2px solid #fbd21e;}
							.ie10 div.iemode {background: #2b6bec;}
							.ie11 div.iemode {background: #2ebaee;}
							
							/* IE Alternate Modes */
							.ie8.no-hashchange.no-rgba-no.applicationcache.no-pointerevents div.iemode {} /* IE8 w/ IE7 Standards */
							
							.ie9.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE9 w/ IE8 Standards */
							.ie9.no-hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE9 w/ IE7 Standards */
							
							.ie10.hashchange.rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 w/ IE9 Standards */
							.ie10.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 w/ IE8 Standards */
							.ie10.no-hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 w/ IE7 Standards */
							.ie7.hashchange.rgba.applicationcache.no-pointerevents div.iemode {} /* IE10 Compatibility w/ IE10 Standards */
							.ie7.hashchange.rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 Compatibility w/ IE9 Standards */
							
							.ie10.hashchange.rgba.applicationcache.pointerevents div.iemode {} /* IE11 w/ IE10 Standards */
							
							/* IE Duplicates */
							.ie7.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* (IE10 Compatiblity w/ IE8 Standards) -OR- (IE8 Compatiblity w/ IE8 Standards) */
							
							/* Alternate Device IE Versions */
							/* Add selectors here for Windows Phone, Xbox One, Xbox 360, or any other IE devices that do not have different rendering options. */
							
							/* Non-Unique IE Environments:
								
								.ie7.no-hashchange.no-rgba.no-applicationcache.no-pointerevents
									IE7
									IE8 Compatibility w/ IE7 Standards
									IE9 Compatibility w/ IE7 Standards
									IE10 Compatibility w/ IE7 Standards
									IE11 w/ IE7 Standards (Could be isolated with OS detection [.win7 ..., .win8 ...])
								
								.ie7.hashchange.no-rgba.no-applicationcache.no-pointerevents
									IE8 Compatibility w/ IE8 Standards
									IE9 Compatibility w/ IE8 Standards
									IE10 Compatibility w/ IE8 Standards
								
								.ie7.hashchange.rgba.no-applicationcache.no-pointerevents
									IE9 Compatibility w/ IE9 Standards
									IE10 Compatibility w/ IE9 Standards
								
								.ie8.hashchange.no-rgba.no-applicationcache.no-pointerevents
									IE8
									IE11 w/ IE8 Standards (Could be isolated with OS detection [.win7 ..., .win8 ...])
								
								.ie9.hashchange.rgba.no-applicationcache.no-pointerevents
									IE9
									IE11 w/ IE9 Standards (Could be isolated with OS detection [.win7 ..., .win8 ...])
							*/
								
						</style>
							
						<div class="row">
							<div class="sixteen columns">
								<div class="notie">This example only works with Internet Explorer.</div>
								<div class="iemode"></div>
							</div><!--/columns-->
						</div><!--/row-->
						
						<script>
							//This script is only used on this examples page to show which browser mode is detected. It is not needed for actual implementation!
							jQuery('.ie .iemode').html('Internet Explorer');
							jQuery('.ie6 .iemode').html('Internet Explorer 6');
							jQuery('.ie7 .iemode').html('Internet Explorer 7');
							jQuery('.ie8 .iemode').html('Internet Explorer 8');
							jQuery('.ie9 .iemode').html('Internet Explorer 9');
							jQuery('.ie10 .iemode').html('Internet Explorer 10');
							jQuery('.ie11 .iemode').html('Internet Explorer 11');
							
							jQuery('.ie8.no-hashchange.no-rgba-no.applicationcache.no-pointerevents .iemode').html('IE8 w/ IE7 Standards');
							
							jQuery('.ie10.hashchange.rgba.no-applicationcache.no-pointerevents .iemode').html('IE10 w/ IE9 Standards');
							jQuery('.ie10.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 w/ IE8 Standards');
							jQuery('.ie10.no-hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 w/ IE7 Standards');
							jQuery('.ie7.hashchange.rgba.applicationcache.no-pointerevents div.iemode').html('IE10 Compatibility w/ IE10 Standards');
							jQuery('.ie7.hashchange.rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 Compatibility w/ IE9 Standards');
							
							jQuery('.ie7.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 Compatiblity w/ IE8 Standards <br/> -or- <br/> IE8 Compatiblity w/ IE8 Standards');
							
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
							jQuery(' .iemode').html('IE');
						</script>
					<?php endif; //End IE Compatibility Mode Detection ?>
					
					
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
					
					
					<?php if ( is_page(485) ) : //Picture Tag ?>
						<div class="row">
							<div class="sixteen columns">
								<picture alt="This is the alt tag for the picture">
									<source src="<?php bloginfo('template_directory');?>/examples/images/example_320x212.jpg">
									<source media="(min-width: 640px)" src="<?php bloginfo('template_directory');?>/examples/images/example_640x424.jpg">
									<source media="(min-width: 1000px)" src="<?php bloginfo('template_directory');?>/examples/images/example@2x.jpg">
									<img src="<?php bloginfo('template_directory');?>/examples/images/example.jpg" alt="This is the alt tag for the picture" />
								</picture>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Picture Tag ?>
					
					
					<?php if ( is_page(351) && 1==2 ) : //Slider without ?>
						
						<style>
							#theslider {transition: all .5s ease 0s;}
							#theslider .sliderwrap {position: relative; overflow: hidden;}
								
								#theslider .status {position: absolute; display: block; width: 100px; top: 5px; right: 5px; background: rgba(0,0,0,0.4); text-align: center; color: #fff; text-decoration: none; border-radius: 25px; z-index: 1500; cursor: default; opacity: 0; -webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
								.no-js #theslider .status {display: none;}
									#theslider .status.pause {opacity: 1; pointer-events: none;}
									#theslider:hover .status.stop {opacity: 1;}
										#theslider .status.stop:hover,
										#theslider .status.stop.hover {cursor: pointer; background: rgba(0,0,0,0.7);}
								
							    #theslider .slider-arrow {position: relative; display: inline-block; color: #fff;}
							    .no-js #theslider .slider-arrow {display: none;}
							
							ul#theslides {position: relative; overflow: hidden; margin: 0; padding: 0;}
							    ul#theslides li {position: absolute; top: 0; left: 0; width: 100%; height: auto; margin-bottom: -7px; /* Don't like this margin... */ opacity: 0; z-index: 0; transition: all 1s ease 0s;}
							        ul#theslides li.active {position: relative; opacity: 1; z-index: 500;}
							
							.no-js .slider-nav-con {display: none;}
							.slider-nav-con {position: absolute; bottom: -50px; width: 100%; background: rgba(0,0,0,0.7); z-index: 1000; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
							    #slider-nav {position: relative; display: table; margin: 0 auto;}							
							        #slider-nav li {display: inline-block; margin-right: 15px; padding: 0; text-align: center; vertical-align: middle;}
							            #slider-nav li:last-child,
							            #slider-nav li.last-child {margin-right: 0;}
							            #slider-nav li a {display: table-cell; vertical-align: middle; padding: 5px 0; position: relative; height: 100%; color: #fff;}
							            	#slider-nav li a:hover {color: #aaa;}
							                #slider-nav li.active a {color: #fff; font-weight: bold;}
							                	#slider-nav li.active a:hover {color: #aaa;}
						</style>
						
						<div id="theslider" class="container nebulaframe">
					        <div class="row">
					            <div class="sixteen columns sliderwrap">
					                
					                <a href="#" class="status">
					                	<i class="fa fa-pause"></i> <span>Paused</span>
					                </a><!--/status-->
					                
					                <ul id="theslides">
				                        <li><img src="http://www.placebear.com/700/300"/></li>
				                        <li><img src="http://www.placebear.com/700/400"/></li>
				                        <li><img src="http://placehold.it/700x500"/></li>
					                </ul>
					
					                <div class="slider-nav-con">
					                    <ul id="slider-nav" class="clearfix">
				                            <li><a class="slider-arrow slider-left " href="#"><i class="fa fa-chevron-left"></i></a></li>
				                            <li class="slide-nav-item"><a href="#">One</a></li>
				                            <li class="slide-nav-item"><a href="#">Two</a></li>
				                            <li class="slide-nav-item"><a href="#">Three</a></li>
				                            <li><a class="slider-arrow slider-right " href="#"><i class="fa fa-chevron-right"></i></a></li>
					                    </ul>
					                </div><!--/slider-nav-con-->
					                					                
					            </div><!--/columns-->
					        </div><!--/row-->
					    </div><!--/container-->
						
						<script>
							jQuery(document).ready(function() {
							
							    strictPause = 0;
							    autoSlider();
								jQuery("#theslides li").eq(0).addClass("active");
								jQuery("#slider-nav li.slide-nav-item").eq(0).addClass("active");
								
							    function autoSlider() {
							        autoSlide = setInterval(function(){
							            theIndex = jQuery("#theslides li.active").index();
							            if ( strictPause == 0 ) {
							                activateSlider(theIndex, "next");
							            }
							        }, 5000);
							    } //End autoSlider()
								
								jQuery("#theslider").hover(function(){
							        clearInterval(autoSlide);
							        jQuery("#slider-nav").addClass("pause");
							        if ( !jQuery(".status").hasClass("stop") ) {
							        	jQuery(".status i").removeClass("fa fa-stop fa fa-play").addClass("fa fa-pause");
										jQuery(".status span").text("Paused");
								        jQuery(".status").addClass("pause");
							        }
							    }, function(){
							        if ( strictPause == 0 ) {
							            autoSlider();
							            jQuery("#slider-nav").removeClass("pause");
							            jQuery(".status").removeClass("pause");
							        }
							    });
							    
							    //Navigation
							    jQuery("#slider-nav li.slide-nav-item a").on("click", function(){       
							        strictPause = 1;
							        jQuery(".status i").removeClass("fa fa-pause").addClass("fa fa-stop");
							        jQuery(".status").removeClass("pause").addClass("stop").find("span").text("Stopped");
							        jQuery("#slider-nav").removeClass("pause").addClass("stop");
							        theIndex = jQuery(this).parent().index();
							        activateSlider(theIndex-1, "goto");
							        return false;
							    });
								
								//Status
								jQuery("#theslider").on("mouseenter", ".status.stop", function(){
									jQuery(this).find("i").removeClass("fa fa-stop").addClass("fa fa-play");
									jQuery(this).find("span").text("Resume");
								});
								jQuery("#theslider").on("mouseleave", ".status.stop", function(){
									jQuery(this).find("i").removeClass("fa fa-play").addClass("fa fa-stop");
									jQuery(this).find("span").text("Stopped");
								});
								jQuery("#theslider").on("click", ".status.stop", function(){
									strictPause = 0;
									jQuery("#slider-nav").removeClass("stop");
							        jQuery(".status").removeClass("pause stop");
							        return false;
								});
								
							    //Arrows
							    jQuery(".slider-arrow").on("click", function(){
							        strictPause = 1;
							        jQuery(".status i").removeClass("fa fa-pause").addClass("fa fa-stop");
							        jQuery(".status").addClass("stopped").find("span").text("Stopped");
							        jQuery("#slider-nav").removeClass("pause").addClass("stop");
							        jQuery("#slider-nav").removeClass("pause").addClass("stop");
							        theIndex = jQuery("#theslides li.active").index();
							        if ( jQuery(this).hasClass("slider-right") ) {
							            activateSlider(theIndex, "next");
							        } else {
							            activateSlider(theIndex, "prev");
							        }
							        return false;
							    });
							
							    function activateSlider(theIndex, buttoned) {
							        slideCount = jQuery("#theslides li").length;
							        activeHeight = jQuery("#theslides li.active img").height();
							        
							        if ( buttoned == "next" ) {
							            newIndex = ( theIndex+1 >= slideCount ? 0 : theIndex+1 );
							        } else if ( buttoned == "prev" ) {
							            newIndex = ( theIndex-1 <= -1 ? slideCount-1 : theIndex-1 );
							        } else {
							            newIndex = theIndex;
							        }
									
									nextHeight = jQuery("#theslides li").eq(newIndex).find("img").height();
									
									jQuery("#theslides li.active").removeClass("active");
								    jQuery("#slider-nav li.slide-nav-item.active").removeClass("active");
								
								    jQuery("#theslides li").eq(newIndex).addClass("active");
								    jQuery("#slider-nav li.slide-nav-item").eq(newIndex).addClass("active");
							        							
									if ( nextHeight >= activeHeight ) {
										console.log("delaying then resizing");
										jQuery("#theslides").delay(500).animate({ //delay will be calculated based on transition speed
											height: nextHeight,
										}, 500, "easeInOutCubic"); //resize speed will be calculated based on transition speed
									} else {
										console.log("just resizing");
										jQuery("#theslides").animate({
											height: nextHeight,
										}, 500, "easeInOutCubic"); //resize speed will be calculated based on transition speed
									}
							    } //End activateSlider()
							
						    }); //End Document Ready
						    
						    jQuery(window).on("load", function() {
							    jQuery(".slider-nav-con").css("bottom", "0");
						    }); //End Window Load
						</script>
						
					<?php endif; ?>
					

					<?php if ( 1==2 ) : //Old Slider (but still has good carriage) ?>
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
					
					
					<?php if ( is_page(428) ) : //Tooltip Shortcode ?>
						
						<style>
							.nebula-tooltip {position: relative; top: 0; cursor: default;}
								.nebula-tooltip:before {content: 'The tip.'; position: absolute; display: block; width: auto; min-width: 50px; max-width: 320px; padding: 0 5px; color: #fff; font-size: 12px; text-align: center; background: rgba(0,0,0,0); opacity: 0; top: 0; border-radius: 20px; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
									.nebula-tooltip:hover:before {top: -25px; background: rgba(0,0,0,0.8); opacity: 1;}
								.nebula-tooltip:after {content: 'v'; position: absolute; display: block; color: #000; top: 0; opacity: 0; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
									.nebula-tooltip:hover:after {top: -15px; opacity: 1;}
						</style>
						
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <a class="nebula-tooltip">Vivamus</a> feugiat lorem a enim rhoncus, vitae varius arcu dignissim. Nunc mollis quis orci ut ullamcorper. Praesent rutrum vitae sem eget lacinia. Aenean sem leo, bibendum sit amet velit ut, <a class="nebula-tooltip">condimentum vehicula</a> enim. Duis quis lectus non nibh luctus dignissim vel vitae turpis. Aenean non porttitor leo. Nullam ac diam cursus, pharetra eros ut, iaculis augue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Curabitur rhoncus placerat auctor. Aliquam bibendum hendrerit felis, at pellentesque metus lacinia nec.</p>
						
					<?php endif; //End Tooltip Shortcode ?>
					
					
					<?php if ( is_page(460) ) : //File Type Indicators ?>
						<div class="row">
							<div class="eight columns">
								<h3>No Parameters</h3>
								<a href="document.doc" onclick="return false;">MS Word Document</a><br/><br/>
								<a href="document.xls" onclick="return false;">MS Excel Document</a><br/><br/>
								<a href="document.pdf" onclick="return false;">PDF Document</a><br/><br/>
								<a href="document.ppt" onclick="return false;">MS Powerpoint Document</a><br/><br/>
								<a href="document.zip" onclick="return false;">Archive</a><br/><br/>
								<a href="document.txt" onclick="return false;">Text Document</a><br/><br/>
							</div><!--/columns-->
							<div class="eight columns">
								<h3>With Icons</h3>
								<a class="icon" href="document.doc" onclick="return false;">MS Word Document</a><br/><br/>
								<a class="icon" href="document.xls" onclick="return false;">MS Excel Document</a><br/><br/>
								<a class="icon" href="document.pdf" onclick="return false;">PDF Document</a><br/><br/>
								<a class="icon" href="document.ppt" onclick="return false;">MS Powerpoint Document</a><br/><br/>
								<a class="icon" href="document.zip" onclick="return false;">Archive</a><br/><br/>
								<a class="icon" href="document.txt" onclick="return false;">Text Document</a><br/><br/>
								<a class="icon" href="document.html" onclick="return false;">HTML Document</a> <small>(Only available with declared icon)</small><br/><br/>
								<a class="icon" href="document.php" onclick="return false;">PHP Document</a> <small>(Only available with declared icon)</small><br/><br/>
								<a class="icon" href="document.css" onclick="return false;">CSS Document</a> <small>(Only available with declared icon)</small><br/><br/>
								<a class="icon" href="document.js" onclick="return false;">JS Document</a> <small>(Only available with declared icon)</small><br/><br/>
								<a class="icon" href="document.swf" onclick="return false;">SWF Document</a> <small>(Only available with declared icon)</small><br/><br/>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; ?>
					
					<?php if ( is_page(443) ) : //Shadows ?>
						<div class="row">
							<div class="sixteen columns">
							
								<h3>Nebula Frame</h3>
								<p>With various shadow types.</p>
							
								<div class="nebulaframe floating valign row" style="text-align: center; min-height: 60px; width: 100%;">
									<div>
										Nebula Frame using "floating"
									</div>
								</div>
							
								<div class="nebulaframe bulging valign row" style="text-align: center; min-height: 60px; width: 100%; margin-top: 30px;">
									<div>
										Nebula Frame using "bulging"
									</div>
								</div>
								
								<div class="nebulaframe curling valign row" style="text-align: center; min-height: 60px; width: 100%; margin-top: 30px;">
									<div>
										Nebula Frame using "curling"
									</div>
								</div>
								
								<div class="nebulaframe hovering valign row" style="text-align: center; min-height: 60px; width: 100%; margin-top: 30px;">
									<div>
										Nebula Frame using "hovering"
									</div>
								</div>
								
								<div class="nebulaframe raising-left valign row" style="text-align: center; min-height: 60px; width: 100%; margin-top: 30px;">
									<div>
										Nebula Frame using "raising-left"
									</div>
								</div>
								
								<div class="nebulaframe raising-right valign row" style="text-align: center; min-height: 60px; width: 100%; margin-top: 30px;">
									<div>
										Nebula Frame using "raising-right"
									</div>
								</div>
								
								<br/>
								
								<h3>Nebula Shadow</h3>
								<p>With various shadow types. Displayed below with both "above" and "below" (for demonstration purposes only).</p>
								
								<div class="nebulashadow above"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "Above" and "Below"
									</div>
								</div>
								<div class="nebulashadow below"></div>
								
								<br/>
								
								<div class="nebulashadow above floating"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "floating"
									</div>
								</div>
								<div class="nebulashadow floating"></div>
								
								<br/>
								
								<div class="nebulashadow above bulging"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "bulging"
									</div>
								</div>
								<div class="nebulashadow bulging"></div>
								
								<br/>
								
								<div class="nebulashadow above curling"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "curling"
									</div>
								</div>
								<div class="nebulashadow curling"></div>
								
								<br/>
								
								<div class="nebulashadow above hovering"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "hovering"
									</div>
								</div>
								<div class="nebulashadow hovering"></div>
								
								<br/>
								
								<div class="nebulashadow above raising-left"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "raising-left"
									</div>
								</div>
								<div class="nebulashadow raising-left"></div>
								
								<br/>
								
								<div class="nebulashadow above raising-right"></div>
								<div class="valign row" style="background: lightblue; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px;">
									<div>
										Nebula Shadow using "raising-right"
									</div>
								</div>
								<div class="nebulashadow raising-right"></div>
								
								<br/>
								
								<h3>Inner Nebula Shadow</h3>
								<p>With various shadow types. All custom shadow types are available, but only a few are demonstrated here.</p>
								
								<div style="position: relative; background: tan; color: #fff; text-align: center; min-height: 120px; width: 100%; margin-top: 30px;">
									<div class="nebulashadow inner-top"></div>
									<div class="valign row">
										<div>
											<br/><br/>Nebula Shadow using "Inner-Top" and "Inner-Bottom"
										</div>
									</div>
									<div class="nebulashadow inner-bottom"></div>
								</div>
								
								<br/>
								
								<div style="position: relative; background: tan; color: #fff; text-align: center; min-height: 120px; width: 100%; margin-top: 30px;">
									<div class="nebulashadow inner-top floating"></div>
									<div class="valign row">
										<div>
											<br/><br/>Nebula Shadow using "Inner-Top" and "Inner-Bottom" with "floating"
										</div>
									</div>
									<div class="nebulashadow inner-bottom floating"></div>
								</div>
								
								<br/>
								
								<div style="position: relative; background: tan; color: #fff; text-align: center; min-height: 120px; width: 100%; margin-top: 30px;">
									<div class="nebulashadow inner-top bulging"></div>
									<div class="valign row">
										<div>
											<br/><br/>Nebula Shadow using "Inner-Top" and "Inner-Bottom" with "bulging"
										</div>
									</div>
									<div class="nebulashadow inner-bottom bulging"></div>
								</div>
								
								<br/>
								
								<h3>Advanced data attribute</h3>
								<p>If there <strong>is</strong> a space between the box and the shadow in the below example, then browsers are beginning to support advanced data attributes! Until then, iframes may not work well with Nebula Shadows.</p>
								
								<div class="valign row" style="background: lightcoral; color: #fff; text-align: center; min-height: 60px; width: 100%; padding: 3px 10px; margin-top: 30px;">
									<div>
										<strong>Offset support:</strong><br/>
										If there is a space between this box and its shadow, then offset is now supported!
									</div>
								</div>
								<div class="nebulashadow floating" offset="10"></div>						
								
								<p><small>Note: The Gumby "Vertical Align" component is also used in these examples! See the homepage for another example of Gumby Vertical Align.</small></p>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Shadows ?>
					
					
					
					<?php if ( is_page(430) ) : //Accordion Shortcode ?>
						<div class="row">
							<div class="sixteen columns">
								<p>Accordion coming soon</p>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Accordion Shortcode ?>
					
					
					<?php if ( is_page(432) ) : //Bio Shortcode ?>
						
						<?php
							/*
								Parameters to use:
									Name
									Photo path
									Title
									Email
									Phone
									Extension
									vCard path
									
									External Links/Social (using just icons if they exist)
										Website
										Twitter
										Facebook
										Instagram
										LinkedIn
									
									
									Excerpt (shortcode $content)
							*/
						?>
						<div class="row">
							<div class="sixteen columns">
								<div class="nebula-bio">
							
								</div>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Bio Shortcode ?>
					
					
					<?php if ( is_page(359) ) : //Level 4 Media Queries ?>
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
					<?php endif; //End Level 4 Media Queries ?>
					
					
					<?php if (is_page(497) ) : //CSS Blending Modes ?>
					
						<style>
							.blend {width: 100%; min-height: 200px; background: #0098d7 url('<?php bloginfo('template_directory'); ?>/examples/images/example_640x424.jpg') no-repeat center center; background-size: contain; margin-bottom: 50px;}
								.blend.multiply {background-blend-mode: multiply;}
								.blend.screen {background-blend-mode: screen;}
								.blend.overlay {background-blend-mode: overlay;}
								.blend.darken {background-blend-mode: darken;}
								.blend.lighten {background-blend-mode: lighten;}
								.blend.color-dodge {background-blend-mode: color-dodge;}
								.blend.color-burn {background-blend-mode: color-burn;}
								.blend.hard-light {background-blend-mode: hard-light;}
								.blend.soft-light {background-blend-mode: soft-light;}
								.blend.difference {background-blend-mode: difference;}
								.blend.exclusion {background-blend-mode: exclusion;}
								.blend.hue {background-blend-mode: hue;}
								.blend.saturation {background-blend-mode: saturation;}
								.blend.color {background-blend-mode: color;}
								.blend.luminosity {background-blend-mode: luminosity;}
						</style>
						
						<div class="row">
							<div class="sixteen columns">
								<div class="blend"></div>								
							</div><!--/columns-->
						</div><!--/row-->
						
						<div class="row">
							<div class="four columns">
								<div class="blend multiply"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend screen"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend overlay"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend darken"></div>								
							</div><!--/columns-->
						</div><!--/row-->
						
						<div class="row">
							<div class="four columns">
								<div class="blend lighten"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend color-dodge"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend color-burn"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend hard-light"></div>								
							</div><!--/columns-->
						</div><!--/row-->
						
						<div class="row">
							<div class="four columns">
								<div class="blend soft-light"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend difference"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend exclusion"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend hue"></div>								
							</div><!--/columns-->
						</div><!--/row-->
						
						<div class="row">
							<div class="four columns">
								<div class="blend saturation"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend color"></div>								
							</div><!--/columns-->
							<div class="four columns">
								<div class="blend luminosity"></div>								
							</div><!--/columns-->
						</div><!--/row-->
					
					<?php endif; //End CSS Blending Modes ?>
					
					
					<?php if ( is_page(436) ) : //Speech Synthesis ?>
						<script>
							jQuery(document).ready(function() {
									
								var voiceSelect = document.getElementById('voice');
								// Check for browser support
								var supportMsg = document.getElementById('msg');
																	
								if ('speechSynthesis' in window) {
									supportMsg.innerHTML = 'Your browser <strong>supports</strong> speech synthesis.';
								} else {
									supportMsg.innerHTML = 'Sorry your browser <strong>does not support</strong> speech synthesis.';
								}
								
								jQuery('#speakit').on('click', function(){
									var textToSay = jQuery('#speaktext').val();
									speak(textToSay);
									//console.log('sending to speak');
									return false;
								});
								
								loadVoices();
								// Fetch the list of voices and populate the voice options.
								function loadVoices() {
								  // Fetch the available voices.
									var voices = speechSynthesis.getVoices();
								  									  
								  // Loop through each of the voices.
									voices.forEach(function(voice, i) {
								    // Create a new option element.
										var option = document.createElement('option');
								    
								    // Set the options value and text.
										option.value = voice.name;
										option.innerHTML = voice.name;
										  
								    // Add the option to the voice selector.
										voiceSelect.appendChild(option);
									});
								}
								
								// Chrome loads voices asynchronously.
								window.speechSynthesis.onvoiceschanged = function(e) {
									loadVoices();
								};
								
								
								// Create a new utterance for the specified text and add it to the queue.
								function speak(text) {
									// Create a new instance of SpeechSynthesisUtterance.
									var msg = new SpeechSynthesisUtterance();
									
									//Remove URLs
									var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i; //Strip URLs
								    text = text.replace(exp, "");
									// Set the text.
									msg.text = text;
									
									var voiceName = jQuery('#voice').val();
									
									msg.voice = speechSynthesis.getVoices().filter(function(voice){ return voice.name == voiceName; })[0];
									
									window.speechSynthesis.speak(msg);	
								}
							});
						</script>
						
						<div class="row">
							<div class="sixteen columns">
								<div id="msg">Enable JavaScript to use speech synthesis...</div>
								
								<select name="voice" id="voice"></select><br/><br/>
								<input id="speaktext" type="text" placeholder="Text to speak..."/>
								<a id="speakit" href="#">Speak It!</a>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Speech Synthesis ?>
					
					
					<?php if ( is_page(356) ) : //AJAX @TODO: In progress ?>
						<div class="row">
							<div class="sixteen columns">
								
								<p>AJAX Example coming soon</p>
								
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End AJAX ?>
					
					
					<?php if ( is_page(703) ) : //AJAX Contact Form ?>
						<script>
							jQuery(document).on('submit', '#ajax-contact', function(e){
								var contactData = [{
									'name': jQuery("#ajax-contact input.name").val(),
									'email': jQuery("#ajax-contact input.email").val(),
									'message': jQuery("#ajax-contact textarea.message").val(),
								}];
								
								jQuery('#form-messages').html('<i class="fa fa-spinner fa-spin sending"></i> Sending...');
								
								jQuery.ajax({
									type: "POST",
									url: jQuery('#ajax-contact').attr('action'),
									data: {
										data: contactData,
									},
									success: function(response){
										if ( response.indexOf('Thank you') > -1 ) {					
											jQuery('#ajax-contact input:not(#contact-submit), #ajax-contact textarea').val('').trigger('keyup');
											jQuery('#ajax-contact').slideUp();
											
											//conversionTracker();
											nebula_event('Contact', 'Submit', 'AJAX Example Form Submission from ' + contactData[0]['name'] + ': "' + contactData[0]['message'] + '"');
										}
										jQuery('#form-messages').html(response);
									},
									error: function(MLHttpRequest, textStatus, errorThrown){
										jQuery('#form-messages').text(errorThrown);
										nebula_event('Contact', 'Error', 'Contact Form AJAX Error');
									},
									timeout: 60000
								});
								
								e.preventDefault();
								return false;
							});
						</script>
						
						<div class="row">
							<div class="sixteen columns">
								<form id="ajax-contact" method="post" action="<?php echo bloginfo('template_directory'); ?>/includes/mailer.php">
									<ul>
										<li class="field">
											<span class="contact-form-heading">Name*</span>
											<input class="input name" type="text" placeholder="Name" required/>
										</li>
										<li class="field">
											<span class="contact-form-heading">Email*</span>
											<input class="input email" type="email" placeholder="Email" required/>
										</li>
										<li class="field">
											<span class="contact-form-heading">Message*</span>
											<textarea class="input textarea message" placeholder="Message" required></textarea>
										</li>
										<li class="field">
											<input class="submit" type="submit" value="Send">
										</li>
									</ul>
								</form>
								<div id="form-messages"></div>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End AJAX Contact Form ?>
					
					
					<?php if ( is_page(588) ) : //nebula_tel_link and nebula_phone_format ?>
						<div class="row">
							<div class="sixteen columns">
								<p>
									<span>This number will be linked on mobile devices:</span><br/>
									<code>nebula_tel_link('(315) 478-6700');</code><br/>
									<?php echo nebula_tel_link('(315) 478-6700'); ?>
								</p>
								
								<br/><br/><hr/>
								
								<p><strong>Here be in-progress testing! Post-dial numbers are not working on Android (and not tested on iOS or Windows Phone)</strong></p>
								
								<p>
									<span>This number will be linked on mobile devices and uses DMTF tones:</span><br/>
									<code>nebula_tel_link('3154786700', 'p239');</code><br/>
									<?php echo nebula_tel_link('3154786700', 'p239'); ?>
								</p>
								
								<p>
									<span>Hard-coded for testing DMTF tones:</span><br/>
									<span>Note which devices each format works on!</span><br/>
									
									<a href="tel:+13154786700,239">+13154786700,239</a><br/><br/>
									<a href="tel:+13154786700;239">+13154786700;239</a><br/><br/>
								</p>
								
								
								<p>
									<span>Hard-coded for testing SMS:</span><br/>
									<span>Note which devices each format works on!</span><br/>
									
									<a href="sms:+13154805016">SMS no body</a><br/><br/>
									<a href="sms:+13154805016;body=This is a message!">SMS ;body (should work on iOS)</a><br/><br/>
									<a href="sms:+13154805016?body=This is a message!">SMS ?body</a><br/><br/>
									<a href="sms:+13154805016;body=This%20is%20a%20message!">SMS ?body encoded spaces</a><br/><br/>
									<a href="sms:+13154805016?body=This%20is%20a%20message!">SMS ?body encoded spaces</a><br/><br/>
								</p>
								<hr/><br/><br/>
								
								<p>
									<span>This SMS number will be linked on mobile devices:</span><br/>
									<code>nebula_sms_link('3154786700');</code><br/>
									<?php echo nebula_tel_link('3154786700'); ?>
								</p>
								
								<p>
									<span>This SMS number will be linked on mobile devices and has a preset message:</span><br/>
									<code>nebula_sms_link('3154786700', 'This is a message!');</code><br/>
									<?php echo nebula_tel_link('3154786700', 'This is a message!'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('(315) 478-6700');</code><br/>
									<?php echo nebula_phone_format('(315) 478-6700'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('315.478.6700');</code><br/>
									<?php echo nebula_phone_format('(315) 478-6700'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('+13154786700');</code><br/>
									<?php echo nebula_phone_format('+13154786700'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('3154786700');</code><br/>
									<?php echo nebula_phone_format('3154786700'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('4786700');</code><br/>
									<?php echo nebula_phone_format('4786700'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('123');</code><br/>
									<?php echo nebula_phone_format('123'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('3154786700', 'tel');</code><br/>
									<?php echo nebula_phone_format('3154786700', 'tel'); ?>
								</p>
																
								<p>
									<code>nebula_phone_format('(315) 478-6700 x123');</code><br/>
									<?php echo nebula_phone_format('(315) 478-6700 x123'); ?>
								</p>
								
								<p>
									<code>nebula_phone_format('(315) 478-6700', 'human');</code><br/>
									<?php echo nebula_phone_format('(315) 478-6700', 'human'); ?>
								</p>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End nebula_tel_link and nebula_phone_format ?>
					
					
					
					<?php if ( is_page(481) ) : //Video @TODO: In progress ?>
						<div class="row">
							<div class="sixteen columns">
								
								<p>Video Example coming soon</p>
								
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; //End Video ?>
					
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
					
					
					<?php if ( is_page(614) ) : //CSS Position: Sticky ?>
						<!--STUFF-->
					<?php endif; //End CSS Position: Sticky ?>
					
					
					<?php if ( is_page(742) ) : //History API ?>
						<script>
							jQuery(document).ready(function() {	
								history.replaceState(null, document.title, location);
								history.pushState(null, document.title, location);
								console.log('History state modified.');
								
								if (window.history && window.history.pushState) {
									window.addEventListener("popstate", function(e) {
										if ( !window.dontnavigate ) {
											window.location = "http://gearside.com/";
										}
										e.stopPropagation();
									}, false);
								}
							});
							
							function modifyURL() {
								window.dontnavigate = 1;
								history.replaceState(null, "Changing the Title Too", "http://gearside.com/new-url");
								console.log('URL modified.');
								
								jQuery('div.btn a').fadeOut();
								
								setTimeout(function(){
									window.dontnavigate = 0;
								}, 1000);							
								return false;
							}
						</script>
						
						<div class="medium primary btn">
							<a href="#" onclick="modifyURL()">Modify the URL</a>
						</div>
					<?php endif; //End History API ?>
					
					
					<?php if ( is_page(737) ) : //Notification API ?>
						
						<script>
							jQuery(document).ready(function() {	
								if ( !checkNotificationPermission() ) {
									jQuery('.notsupported').removeClass('hidden');
									jQuery('.basicnotify, .fullnotify').parents('div').removeClass('primary').addClass('danger');
								}
								
								jQuery('.basicnotify').on('click', function(){
									desktopNotification("Basic Notification", "This is the message");
									return false;
								});
								
								jQuery('.fullnotify').on('click', function(){
									var message = {
										dir: "ltr",
										lang: "en-US",
										body: "This is a fully customized notification with callback functions!",
										icon: bloginfo['template_directory'] + "/images/og-thumb2.png"
									}
									desktopNotification("Fully Customized Notification", message, clickNotify, closeNotify, showNotify, errorNotify);
									
									function clickNotify() {
										jQuery('.fullnotify').parents('div').removeClass('primary danger info warning').addClass('success');
									}
									
									function closeNotify() {
										jQuery('.fullnotify').parents('div').removeClass('warning primary danger success').addClass('info');
									}
									
									function showNotify() {
										jQuery('.fullnotify').parents('div').removeClass('primary success info danger').addClass('warning');
									}
									
									function errorNotify() {
										jQuery('.fullnotify').parents('div').removeClass('primary warning success info').addClass('danger');
									}
									
									return false;
								});
							});
						</script>
						
						
						<p class="notsupported hidden" style="font-weight: bold; color: red;">Desktop Notifications are not supported in your browser!</p>
						
						
						<p>The following button passes only a title and body and uses Nebula defaults for everything else:</p>
						<div class="medium primary btn">
							<a class="basicnotify" href="#">Basic Notification</a>
						</div>
						
						<br/><br/><br/><p>The following button passes everything and uses the callbacks too:</p>
						<div class="medium primary btn">
							<a class="fullnotify" href="#">Fully Customized</a>
						</div>
						
						<!-- @TODO: Make an example of how to close a notification with instance.close(); -->
																		
					<?php endif; //End Notification API ?>
					
					
					<?php if ( is_page(760) ) : //bxSlider ?>
						<ul class="bxslider exampleslider">
							<li><img src="http://placehold.it/800x400" title="Placeholder" /></li>
							<li><img src="http://placebear.com/800/400" title="PlaceBear" /></li>
							<li><img src="http://placekitten.com/800/400" title="PlaceKitten" /></li>
						</ul>
					<?php endif; //End bxSlider ?>
					
					
					<?php if ( is_page(785) ) : //Seamless Iframe ?>
						<script>
							var sandboxSupported = "sandbox" in document.createElement("iframe");
							console.log(sandboxSupported);
						</script>
						
						<style>
							.iframeswrap iframe {background-color: none !important;}
							.iframeswrap iframe h1, .iframeswrap iframe p {color: white;}
							.iframeswrap iframe a {color: green;}
						</style>
						
						<div class="iframeswrap">
							<h3>Standard Iframe:</h3>
							<iframe src="<?php echo bloginfo('template_directory'); ?>/examples/includes/seamless.html" style="width: 100%;"></iframe>
							<br/>
							<hr/>
							<br/>
							<h3>Seamless Iframe:</h3>
							<iframe src="<?php echo bloginfo('template_directory'); ?>/examples/includes/seamless.html" seamless style="width: 100%;"></iframe>
						</div>
					<?php endif; //End Seamless Iframes ?>
					
					
					<?php if ( is_page(779) ) : //Google Analytics RealTime API ?>
						<?php
							/*
								Request Beta access here (need a project ID from below): https://docs.google.com/forms/d/1qfRFysCikpgCMGqgF3yXdUyQW4xAlLyjKuOoOEFN2Uw/viewform
								
								Instructions:
									Go to: https://console.developers.google.com/
									Create Project
									APIs & auth > APIs: Make sure Real Time Reporting API is "ON"
									Credentials > 
									
									Full instructions here: https://developers.google.com/analytics/devguides/reporting/realtime/v3/authorization
									Test it here: https://developers.google.com/apis-explorer/#p/analytics/v3/analytics.data.realtime.get
							*/
						?>
						
						<?php
							/* Not sure if this stuff is needed yet or not
								$client = new Google_Client();
								$client->setApplicationName("My Application");
								$client->setDeveloperKey(MY_SIMPLE_API_KEY);
								
								$service = new Google_Service_Books($client);
							*/
							
							/*
$optParams = array(
								'dimensions' => 'rt:medium'
							);
							
							try {
								$results = $analytics->data_realtime->get(
									'ga:#####',
									'rt:activeUsers',
									$optParams
								);
								// Success.
								echo $results;
							} catch (apiServiceException $e) {
								// Handle API service exceptions.
								$error = $e->getMessage();
							}
*/
							
						?>
					<?php endif; //End Google Analytics RealTime API ?>
					
					
					<?php if ( is_page(614) ) : //Device Orientation ?>
						<!--STUFF-->
					<?php endif; //End Device Orientation ?>
					
					
					<?php if ( is_page(614) ) : //Device Motion API ?>
						<!--STUFF-->
					<?php endif; //End Device Motion API ?>
					
					
					<?php if ( is_page(1234) ) : //Image Orientation ?>
						<!--STUFF-->
					<?php endif; //End Image Orientation ?>
					
					
					<?php if ( is_page(617) ) : //CSS Variables ?>
						<style>
							:root {--main-color: green;}
							#cssvariablebox {background-color: var(--main-color); border: 1px solid black; color: #fff; width: 100%; height: 200px; text-align: center;}
						</style>
						
						<div id="cssvariablebox">If this has a background color, CSS variables are working!</div>
					<?php endif; //End CSS Variables ?>
					
					
					<?php if ( is_page(1234) ) : //Proximity API ?>
						<!--STUFF-->
					<?php endif; //End Proximity API ?>
					
					
					<?php if ( is_page(671) ) : //PHP error handling ?>
						<?php if ( !array_key_exists('debug', $_GET) ) : ?>
							<h2>Refreshing to enable debug mode!</h2>
							<script>
								document.location = "<?php the_permalink(); ?>?debug";
							</script>
						<?php else : ?>
							<?php trigger_error('This is an example of a warning', E_USER_WARNING); ?>	
							<?php trigger_error('This is an example of a notice', E_USER_NOTICE); ?>
							<?php trigger_error('This is an example of a deprecated function', E_USER_DEPRECATED); ?>
						<?php endif; ?>
					<?php endif; //End PHP error handling ?>
					
					
					<?php if ( is_page(643) ) : //Speech Recognition API ?>
						<style>
							#start_button {background: #0098d7; font-size: 16px; color: #fff; padding: 3px 10px; -webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
								#start_button:hover {background: #95D600;}
								#start_button.active {-webkit-animation: recording 3s infinite; -moz-animation: recording 3s infinite; -o-animation: recording 3s infinite; animation: recording 3s infinite;}
									@-webkit-keyframes recording {
										0%, 100% {background: red;}
										50% {background: maroon;}
									}
									@-moz-keyframes recording {
										0%, 100% {background: red;}
										50% {background: maroon;}
									}
									@-o-keyframes recording {
										0%, 100% {background: red;}
										50% {background: maroon;}
									}
									@keyframes recording {
										0%, 100% {background: red;}
										50% {background: maroon;}
									}
								#start_button.pending {background: lightgrey;}
									#start_button.active:hover,
									#start_button.active.hover,
									#start_button.pending:hover,
									#start_button.pending.hover {background: maroon;}
									
							#functionlist ul li {margin-bottom: 10px;}
						</style>
						
						<h4 id="speech-help" style="text-align: center;"></h4>
						
						<div id="startbuttoncon" style="text-align: center; margin: 15px 0;">
							<a id="start_button" href="#">
								<i id="start_button_icon" class="fa fa-microphone"></i> <span id="start_button_text"> Start</span>
							</a>
						</div>
						
						<p class="speechconfidence" style="margin: 0; font-size: 12px;"></p>
						
						<div id="results" style="border: 1px solid #ccc; background: #fafafa; margin-bottom: 15px; padding: 15px; text-align: left; min-height: 150px; width: 100%;">
							<span id="final_span" style="font-weight: bold; color: black;"></span>
							<span id="interim_span" style="color: #777;"></span>
						</div>
						
						<p id="ajaxnavtext" style="font-size: 12px; margin: 0; display: none;">Navigation is still being developed. Your request would have sent you here:</p>
						<input id="ajaxarea" type="text" disabled style="display: none; width: 100%; font-size: 12px; margin-bottom: 15px; padding: 3px 15px; border: 1px solid red;" />
						
						<div id="functionlist">
							<h4>Functions</h4>
							
							<ul style="font-size: 12px;">
								<li>
									<strong>"My name is ________"</strong><br/>
									<span>Introduce yourself.</span>
								</li>
								<li>
									<strong>"Search for _________"</strong><br/>
									<span>Trigger a Wordpress search.</span>
								</li>
								<li>
									<strong>"Navigate to _________"</strong><br/>
									<span>Query through page titles, post titles, menu items, categories, and tags (in that order) to find the request. If not found, trigger search results.</span>
								</li>
								<li>
									<strong>"Driving Directions"</strong><br/>
									<span>Receive directions to PHG from your current location.</span>
								</li>
								<li>
									<strong>"Stop Listening"</strong><br/>
									<span>Stop all speech recognition.</span>
								</li>
							</ul>
						</div>
						
						<p style="margin: 0; margin-top: 5px; font-size: 12px;">Say <strong>"I love Nebula"</strong> or better yet, introduce yourself and then say it!</p>
						<input id="ilovenebula" type="text" disabled style="width: 100%; font-size: 12px; padding: 3px 15px;" />
						
						<script>
							jQuery(document).ready(function() {
								jQuery('#speech-help').text('Click on the microphone icon and begin speaking.');
								
								var final_transcript = '';
								var recognizing = false;
								var ignore_onend;
								var start_timestamp;
								
								window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
								
								if ( window.SpeechRecognition === null ) {
									noSpeechRecognition();
								} else {
									var recognition = new window.SpeechRecognition();
									recognition.continuous = true;
									recognition.interimResults = true;
									recognition.lang = 'en-US';
									recognition.maxAlternatives = 1;
									
									recognition.onstart = function() {
										recognizing = true;
										//Animated start button icon to recording here
										jQuery('#speech-help').text('Speak now.');
										jQuery('#start_button').removeClass().addClass('active');
										jQuery('#start_button_text').text(' Listening...');
										jQuery('#start_button_icon').removeClass().addClass('fa fa-comment');
									};
									
									recognition.onaudiostart = function(event) { console.log('onaudiostart'); } //When audio is being listened for...?
									recognition.onsoundstart = function(event) { console.log('onsoundstart'); } //When a sound is detected
									recognition.onspeechstart = function(event) { console.log('onspeechstart'); } //When human speech is detected
									
									recognition.onerror = function(event) {
										if ( event.error == 'no-speech' ) {
											//start_img.src = 'mic.gif';
											jQuery('#speech-help').text('No speech was detected. You may need to adjust your microphone settings.');
											jQuery('#start_button').removeClass();
											jQuery('#start_button_text').text(' No Speech');
											jQuery('#start_button_icon').removeClass().addClass('fa fa-volume-off');
											ignore_onend = true;
											nebula_event('Speech Recognition', 'Error', 'No speech was detected.');
										}
									
										if ( event.error == 'audio-capture' ) {
											//start_img.src = 'mic.gif';
											jQuery('#speech-help').text('No microphone was found. Ensure that a microphone is installed and that microphone settings are configured correctly.');
											jQuery('#start_button').removeClass();
											jQuery('#start_button_text').text(' No Microphone');
											jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone-slash');
											ignore_onend = true;
											nebula_event('Speech Recognition', 'Error', 'No microphone was found.');
										}
									
										if ( event.error == 'not-allowed' ) {
											if (event.timeStamp - start_timestamp < 100) {
												jQuery('#speech-help').text('Permission to use microphone is blocked. To change, go to chrome://settings/contentExceptions#media-stream');
												jQuery('#start_button').removeClass();
												jQuery('#start_button_text').text(' Blocked');
												jQuery('#start_button_icon').removeClass().addClass('fa fa-times-circle');
												nebula_event('Speech Recognition', 'Error', 'Permission to use microphone is blocked.');
											} else {
												jQuery('#speech-help').text('Permission to use microphone was denied.');
												jQuery('#start_button').removeClass();
												jQuery('#start_button_text').text(' Denied');
												jQuery('#start_button_icon').removeClass().addClass('fa fa-times-circle-o');
												nebula_event('Speech Recognition', 'Error', 'Permission to use microphone was denied.');
											}
											ignore_onend = true;
										}
									};
									
									recognition.onspeechend = function(event) { console.log('onspeechend'); } //When human speech has stopped
									recognition.onsoundend = function(event) { console.log('onsoundend'); } //When sound has stopped
									recognition.onaudioend = function(event) { console.log('onaudioend'); } //When audio is no longer being listened for...?
									recognition.onnomatch = function(event) { console.log('onnomatch'); } // No idea...
									
									recognition.onresult = function(event) {
										var interim_transcript = '';
										for ( var i = event.resultIndex; i < event.results.length; ++i ) {
											if ( event.results[i].isFinal ) {
												final_transcript += event.results[i][0].transcript;
												jQuery('.speechconfidence').html('I am <strong>' + (event.results[i][0].confidence*100).toFixed(2) + '%</strong> sure you said:');
												keyPhrases(final_transcript);
											} else {
												interim_transcript += event.results[i][0].transcript;
												jQuery('.speechconfidence').html('I am <strong>' + (event.results[i][0].confidence*100).toFixed(2) + '%</strong> sure you said:');
												keyPhrases(interim_transcript);
											}
										}
										final_transcript = capitalize(final_transcript);
										jQuery('#final_span').text(linebreak(final_transcript));
										jQuery('#interim_span').text(linebreak(interim_transcript));
									};
									
									recognition.onend = function() {
										recognizing = false;
										
										if ( final_transcript ) {
											if ( final_transcript.indexOf('*') > -1 ) {
												nebula_event('Speech Recognition', 'Transcript (Swearing)', '"' + final_transcript + '"');
											} else {
												nebula_event('Speech Recognition', 'Transcript', '"' + final_transcript + '"');
											}
										}
										
										if ( ignore_onend ) {
											return;
										}
										
										resetStartButton();
										jQuery('#speech-help').text('Click on the microphone icon and begin speaking.');
									};
								}
								
								function noSpeechRecognition() {
									jQuery('#results, #startbuttoncon').hide();
									jQuery('#speech-help').text('Speech detection is not supported in your browser.').css('color', 'red');
									nebula_event('Speech Recognition', 'Not Supported');
								}
									
								var two_line = /\n\n/g;
								var one_line = /\n/g;
								function linebreak(s) {
									return s.replace(two_line, '<p></p>').replace(one_line, '<br>');
								}
									
								var first_char = /\S/;
								function capitalize(s) {
									return s.replace(first_char, function(m) { return m.toUpperCase(); });
								}
									
								function startListening(event) {
									if ( recognizing ) {
										recognition.stop();
										return;
									}
									
									final_transcript = '';
									recognition.start();
									ignore_onend = false;
									final_span.innerHTML = '';
									interim_span.innerHTML = '';
									//start_img.src = 'mic-slash.gif';
									jQuery('#speech-help').text('Click the "Allow" button above to enable your microphone.');
									jQuery('#start_button').removeClass().addClass('pending');
									jQuery('#start_button_text').text(' Requesting Permission...');
									jQuery('#start_button_icon').removeClass().addClass('fa fa-external-link-square');
									start_timestamp = event.timeStamp;
								}
								
								function keyPhrases(transcript) {
									transcript = transcript.toLowerCase();
									speakerName = '';
									
									//"My Name is _______"
									phraseMyNameIs = ['my name is'];
									if ( checkAlternates(transcript, phraseMyNameIs) ) {
										speakerName = transcript.substr( transcript.indexOf('my name is')+11, 25);
										speakerName = speakerName.substr( 0, speakerName.indexOf(' ') );
										speakerName = speakerName.charAt(0).toUpperCase() + speakerName.slice(1);
										
										if ( (speakerName == 'Jeff' || speakerName == 'Geoff') && clientinfo["remote_addr"] == '72.43.235.106' ) {
											speakerName = 'Jef';
										}
									}
									
									//"I Love Nebula"
									phraseILoveNebula = ['i love nebula', 'isle of nebula', 'i love allah', 'i love nutella', 'isle of nutella', 'isle of allah'];
									if ( checkAlternates(transcript, phraseILoveNebula) ) {
										if ( speakerName != '' ) {
											jQuery('#ilovenebula').val('I love you too, ' + speakerName + '.' );
										} else {
											jQuery('#ilovenebula').val('I love you too.');
										}
									}									
									
									//"Search for ________"
									phraseSearchFor = ['search for'];
									if ( checkAlternates(transcript, phraseSearchFor) ) {
										searchQuery = transcript.substr( transcript.indexOf('search for ')+11, 99);
										
										jQuery('#speech-help').text('About to search. Say "Stop Listening" or click the button to cancel.');
										setTimeout(function(){
											if ( recognizing ) { //This allows the user to cancel navigation by stopping.
												jQuery('#speech-help').text('Searching now...');
												resetStartButton();
												ignore_onend = true;
												recognition.stop();
												nebula_event('Speech Recognition', 'Search for: ' + searchQuery);
												searchQuery = searchQuery.replace(' ', '+');
												window.location.href = bloginfo['home_url'] + '?s=' + searchQuery;
											}
										}, 3000);
									}
																	
									//"Driving Directions"
									phraseDrivingDirections = ['driving directions'];
									if ( checkAlternates(transcript, phraseDrivingDirections) ) {
										jQuery('#speech-help').text('Let\'s get you here...');
										jQuery('#start_button').removeClass();
										jQuery('#start_button_text').text(' Start');
										jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
										ignore_onend = true;
										recognition.stop();
										nebula_event('Speech Recognition', 'Driving Directions');
										window.location.href = 'https://www.google.com/maps/dir/Current+Location/<?php echo nebula_settings_conditional_text_bool('nebula_street_address', $GLOBALS['enc_address'], '760+West+Genesee+Street+Syracuse+NY+13204'); ?>';
									}
									
									//"Navigate to ________"
									phraseNavigateTo = ['navigate to', 'browse to', 'go to'];
									if ( checkAlternates(transcript, phraseNavigateTo) ) {
										navigationRequest = transcript.substr( transcript.indexOf('navigate to ')+12, 99);
										//@TODO: Need to set navigationRequest to alt phrases if user said "browser to" or "go to"
										jQuery('#speech-help').text('About to navigate. Say "Stop Listening" or click the button to cancel.');
										jQuery('#ajaxarea').fadeIn();
										setTimeout(function(){
											if ( recognizing ) { //This allows the user to cancel navigation by stopping.
												jQuery('#speech-help').text('Navigating...');
												jQuery('#start_button').removeClass();
												jQuery('#start_button_text').text(' Start');
												jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
												ignore_onend = true;
												recognition.stop();
												
												jQuery('#ajaxarea').css('border', '1px solid grey');
												
												jQuery.ajax({
													type: "POST",
													url: '<?php echo admin_url('admin-ajax.php'); ?>',
													data: {
														action: 'navigator',
														data: navigationRequest,
														nonce: '<?php echo wp_create_nonce('nebula_ajax_navigator_nonce'); ?>'
													},
													//dataType: 'html',
													success: function(response){
														jQuery('#ajaxnavtext').fadeIn();
														jQuery('#ajaxarea').val(response).css('border', '1px solid green');
														//@TODO: window location href here
														console.log(response);
														nebula_event('Speech Recognition', 'Navigate to: ' + navigationRequest, 'Response: ' + response);
													},
													error: function(MLHttpRequest, textStatus, errorThrown){
														console.log('There was an AJAX error: ' + errorThrown);
														nebula_event('Speech Recognition', 'Error', 'Navigation error: ' + errorThrown);
													},
													timeout: 60000
												});
												
											}
										}, 3000);
									}
									
									//"Stop Listening" (should always be the last check)
									phraseStopListening = ['stop listening', 'topless'];
									if ( checkAlternates(transcript, phraseStopListening) ) {
										jQuery('#speech-help').text('Stopped because you said so.');
										console.log('you requested stop listening');
										jQuery('#start_button').removeClass();
										jQuery('#start_button_text').text(' Start');
										jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
										ignore_onend = true;
										recognition.stop();
									}
								}
								
								
								function checkAlternates(transcript, altPhrases) {
									var length = altPhrases.length;
									while ( length-- ) {
										if ( transcript.indexOf(altPhrases[length]) != -1 ) {
											return true;
										}
									}
									return false;
								}
								
								function resetStartButton() {
									jQuery('#start_button').removeClass();
									jQuery('#start_button_text').text(' Start');
									jQuery('#start_button_icon').removeClass().addClass('fa fa-microphone');
								}
									
								jQuery('#start_button').on('click', function(event){
									startListening(event);
									return false;
								});
							});
							
							/*
								@TODO:
									- Recording button should pulsate color when recording.
							*/
							
						</script>						
					<?php endif; //End Speech Recognition API ?>
										
					
					<?php if ( is_page(624) ) : //CSS Feature Queries ?>
						<style>
							.supportdiv {background: red; color: #fff; width: 100%; height: 150px; text-align: center;}
							
							@supports ( box-shadow: 2px 2px 2px black ) or
							          ( -moz-box-shadow: 2px 2px 2px black ) or
							          ( -webkit-box-shadow: 2px 2px 2px black ) or
							          ( -o-box-shadow: 2px 2px 2px black ) {
								.supportdiv {background: green; -moz-box-shadow: 2px 2px 2px black; -webkit-box-shadow: 2px 2px 2px black; -o-box-shadow: 2px 2px 2px black; box-shadow: 2px 2px 2px black;}
							}
						</style>
						
						<div class="supportdiv">If this has a drop shadow, feature queries are supported!</div>
					<?php endif; //End CSS Feature Queries ?>
					
					
					<?php if ( is_page(1234) ) : //Clipboard API ?>
						<!--STUFF-->
					<?php endif; //End Clipboard API ?>
					
					
					<?php if ( is_page(1234) ) : //CSS Masks ?>
						<!--STUFF-->
					<?php endif; //End CSS Masks ?>
					
					
					<?php if ( is_page(628) ) : //Page Visibility API ?>
						<script>
							function getPageVisibility(){
								if ( typeof document.hidden != "undefined" ) {
									return document.hidden;
								} else {
									console.log('page visibility api is not supported');
									return false;
								}
							}
							
							if ( document.visibilityState == 'prerender' ) {
								console.log('This page was prerendered!');
							}
							
							jQuery(document).on('visibilitychange', function(){								
								var pagevislog = jQuery('.pagevislog').text();
								
								if ( getPageVisibility() ) {
									console.log('tab hidden');
									jQuery('.pagevislog').text(pagevislog + '-Hidden-');
								} else {
									console.log('tab visible');
									jQuery('.pagevislog').text(pagevislog + '-Visible-');
								}
							});
							
							jQuery(window).on('load', function(){
								
							});					
						</script>
						
						<p><strong>Change tabs to see the log:</strong></p>
						<div class="pagevislog" style="min-height: 50px; border: 1px solid blue; padding: 15px;"></div>
					<?php endif; //End Page Visibility API ?>
					
					
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
												<li class="latlngcon"><i class="fa fa-location-arrow" style="color: #fe7569;"></i> <span class="lat">43.109205</span>, <span class="lng">-76.095831</span></li>
												<li class="latlngcon"><i class="fa fa-location-arrow" style="color: #fe7569;"></i> <span class="lat">43.093068</span>, <span class="lng">-76.163809</span></li>
												<li class="latlngcon"><i class="fa fa-location-arrow" style="color: #fe7569;"></i> <span class="lat">43.100150</span>, <span class="lng">-76.207207</span></li>
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
				
					<?php if (is_page(397)) : //Cookies ?>
						<style>
							.jscookie, .phpcookie {background: red; color: white; padding: 5px;}
								.jscookie:hover, .phpcookie:hover {background: grey; color: white;}
								.jscookie.cookie-on, .phpcookie.cookie-on {background: green; color: white;}
									.jscookie.cookie-on:hover, .phpcookie.cookie-on:hover {background: grey; color: white;}
						</style>
						
						<div class="row">
							<div class="sixteen columns">
								<p>Toggle cookie: <a class="jscookie" href="#">OFF</a> Last set on: <span class="setdate">(not set)</span></p>
								
								<script>
									jQuery(document).ready(function() {	
										checkExample();
										function checkExample() {
											if ( readCookie('examplejs') ) {
												jQuery('.jscookie').text('ON').addClass('cookie-on');
												jQuery('.setdate').text(readCookie('examplejs'));
											} else {
												jQuery('.jscookie').text('OFF').removeClass('cookie-on');
												jQuery('.setdate').text('(not set)');
											}
										}
										
										jQuery('.jscookie').on('click', function(){
											if ( jQuery(this).hasClass('cookie-on') ) {
												eraseCookie('examplejs');
												checkExample();
											} else {
												var weekday = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
												var month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
												var currentTime = new Date();
												dddd = currentTime.getDay(); //Get day of week (0-6)
												dddd = weekday[dddd]; //Convert day to named weekday
												MMMM = currentTime.getMonth(); //Get month (0-11)
												MMMM = month[MMMM]; //Convert month to named month
												d = currentTime.getDate(); //Get date (1-31)
												yyyy = currentTime.getFullYear(); //Get year (2014)
												h = currentTime.getHours(); //Get hours (0-23)
												tt = ( h >=12 ? 'pm' : 'am' ); //Determine AM or PM
												h = ( h > 12 ? h-12 : h ); //Convert hours to 12 hour format
												h = ( h == 0 ? 12 : h ); //Convert hours to 12 hour format
												mm = currentTime.getMinutes(); //Get minutes (0-59)
												mm = ( mm <10 ? '0'+mm : mm ); //Add leading 0 to minutes (as needed)
												ss = currentTime.getSeconds(); //Get seconds (0-59)
												ss = ( ss <10 ? '0'+ss : ss ); //Add leading 0 to seconds (as needed)
												currentTime = dddd + ', ' + MMMM + ' ' + d + ', ' + yyyy + ' @ ' + h + ':' + mm + ' ' + tt;
												
												createCookie('examplejs', currentTime);
												checkExample();
											}
											return false;
										});
									});
								</script>
								
								<br/>
							</div><!--/columns-->
						</div><!--/row-->
						
					<?php endif; ///End Cookies?>
					
					
				<?php
				/*==========================
				 End Hard-Code Example
				 ===========================*/
				?>
								
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
				
				<div class="row">
					<div class="sixteen columns">
						<?php get_template_part('comments'); ?>
					</div><!--/columns-->
				</div><!--/row-->
				
			</div><!--/container-->
			
		</div><!--/columns-->
		<div class="four columns push_one">
			<ul class="xoxo">
				<li>
					<h3>Documentation</h3>
					<?php wp_nav_menu(array('theme_location' => 'header', 'depth' => '9999')); ?>
				</li>
			</ul>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>