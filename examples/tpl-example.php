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
					<div class="sixteen columns">
						
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
							<?php query_posts( array( 'category_name' => 'Examples', 'showposts' => 4, 'paged' => get_query_var('paged') ) ); ?>
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
				
					
					<?php if ( is_page(214) ) : //Nebula Meta ?>
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
									<hr/>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Google Maps Iframe ?>
					
					
					<?php if ( is_page(224) ) : //Nebula Meta ?>
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php echo nebula_the_excerpt(1, 'Read More &raquo;', 35, 1); ?>
									<hr/>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Google Maps Iframe ?>
					
					
					<?php if ( is_page(228) ) : //Nebula Manage ?>
							<div class="row">
								<div class="sixteen columns">
									<hr/>
									<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
									<hr/>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Google Maps Iframe ?>
					
					
					<?php if ( is_page(258) ) : //CSS Browser Selector ?>
							<div class="row">
								<div class="six columns">
									<div class="cssbs"></div>
								</div><!--/columns-->
							</div><!--/row-->
					<?php endif; //End Google Maps Iframe ?>
					
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
					<?php wp_nav_menu(array('menu' => 'Examples', 'depth' => '9999')); ?>
				</li>
			</ul>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>