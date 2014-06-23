<?php
/**
 * Template Name: Examples
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
	<div class="container" style="background: #0098d7; margin-bottom: 25px;">
		<div class="row">
			<div class="sixteen columns">
				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1 class="entry-title" style="color: #fff;"><?php the_title(); ?></h1>
						<div class="entry-content" style="color: #fff;">
							<?php the_content(); ?>						
						</div><!-- .entry-content -->
					</article><!-- #post-## -->
					<?php //comments_template( '', true ); ?>
				<?php endwhile; ?>
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<? the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<hr/>
			<h2>Basic WP Query</h2>
			<h5><a href="http://codex.wordpress.org/Function_Reference/query_posts" target="_blank">Documentation &raquo;</a></h5>
			<p>This is a basic Wordpress query posts, but also shows how Gumby columns can be integrated into the logic. The function nebula_meta() is shown as well. This also shows the Wordpress plugin WP-PageNavi.</p>
			<div class="container">
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
							            
							        </div><!-- /entry-content -->
							    </article><!-- /post-<?php the_ID(); ?> -->
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
					
				</div><!--/container-->
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Custom Shortcodes</h2>
			<p>These are custom shortcodes that can be used in the Wordpress WYSIWYG Editor. As seen below, they are used via PHP.</p>
			
			<div class="container">
				<div class="row">
					<div class="eight columns">
						<h3>Space</h3>
						<p>Allows vertical space to be added without needing to use any code.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][space height=25][/pre]'); ?>
						<br/>
					</div><!--/columns-->
					<div class="eight columns">
						<h3>Divider</h3>
						<p>Adds a stylized horizontal rule without needing to use code. "Space" (optional) adds equal margin above and below, otherwise declare "above" (optional) and "below" (optional) separately.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][divider space=5][/pre]'); ?>
						<?php echo do_shortcode('[pre lang="shortcode"][divider above=10 below=30][/pre]'); ?>
						<br/>
					</div><!--/columns-->
				</div><!--/row-->
				<div class="row">
					<div class="eight columns">
						<h3>Icon</h3>
						<p>Allows usage of <a href="http://gumbyframework.com/docs/ui-kit/#!/icons" target="_blank">Gumby/Entypo</a> or <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">Font Awesome</a> icons without needing code. Works with <a href="http://fortawesome.github.io/Font-Awesome/examples/" target="_blank">supplemental Font Awesome</a> classes like "fa-spin" too. Add additional classes with the "class" attribute.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][icon type="icon-home" color="#222" size="12px" class="special"][/pre]'); ?>
						<?php echo do_shortcode('[pre lang="shortcode"][icon type="fa-home fa-spin" color="red"][/pre]'); ?>
						<br/>
					</div><!--/columns-->
					<div class="eight columns">
						<h3>Youtube</h3>
						<p>Provides an easy way to insert Youtube videos without needing code (using the necessary syntax for tracking). "height" and "width" attributes are optional. The "rel" attribute (optional) will toggle related videos at the end (default: 0).</p>
						<?php echo do_shortcode('[pre lang="shortcode"][youtube id="jtip7Gdcf0Q" height="500" width="760" rel="1"][/pre]'); ?>
						<?php echo do_shortcode('[pre lang="shortcode"][youtube id="jtip7Gdcf0Q"][/pre]'); ?>
						<br/>
					</div><!--/columns-->
				</div><!--/row-->
				<div class="row">
					<div class="eight columns">
						<h3>Div</h3>
						<p>A way to create div tags inside the content area. Be careful using this as it is possible to open a div without closing it (and vice-versa)!</p>
						<?php echo do_shortcode('[pre lang="shortcode"][div class="look-here aclass" style="background: red;"]Content goes here![/div][/pre]'); ?>
						<?php echo do_shortcode('[pre lang="shortcode"][div][/pre]'); ?>
						<?php echo do_shortcode('[pre lang="shortcode"][div close][/pre]'); ?>
						<br/>
					</div><!--/columns-->
					<div class="eight columns">
						<h3>Buttons</h3>
						<p>This shortcode integrates with Gumby buttons. Required parameters include href and the content itself. All others are options- these include size, type, icon, target, and metro/pretty.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][button size="medium" type="success" pretty icon="icon-mail" href="http://www.google.com/" target="_blank"]Click Here[/button][/pre]'); ?>
						<br/><br/>
						<?php echo do_shortcode('[button size="medium" type="success" metro icon="fa-bomb" href="http://www.google.com/" target="_blank"]Click Here[/button]'); ?>
						<br/><br/>
						<?php echo do_shortcode('[button size="medium" type="success" metro icon="icon-mail" href="http://www.google.com/" target="_blank"]Click Here[/button]'); ?>
						<br/><br/>
						<?php echo do_shortcode('[button href="http://www.google.com/"]Another[/button]'); ?>
						<br/><br/>
						<?php echo do_shortcode('[button size="medium" href="http://www.google.com/"]Another[/button]'); ?>
						<br/><br/>
					</div><!--/columns-->
				</div><!--/row-->
				<div class="row">
					<div class="sixteen columns">
						<h3>Gumby Grid</h3>
						<p>Using this shortcode allows you to add Gumby grids into the Wordpress WYSIWYG area. All shortcodes allow for custom classes and styles.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][colspan twelve]
	[container style="background: blue;"]
		[row class="special"]
			[columns six]Content Here[/columns]
			[columns four push="two"]Content Here[/columns]
		[/row]
	[/container]
[/colspan][/pre]'); ?>
						<br/><br/>
					</div><!--/columns-->
				</div><!--/row-->
				<div class="row">
					<div class="four columns">
						<h4>Colspan</h4>
						<p>(Optional) Use if needing to declare the hybrid grid.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][colspan twelve][/colspan][/pre]'); ?>
					</div><!--/columns-->
					<div class="four columns">
						<h4>Container</h4>
						<p>(Optional) Use if needing to have a full-width section (possibly for a background color).</p>
						<?php echo do_shortcode('[pre lang="shortcode"][container style="background blue;"][/container][/pre]'); ?>
					</div><!--/columns-->
					<div class="four columns">
						<h4>Row</h4>
						<p>(Required) Wraps the columns and resets the column counter.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][row][/row][/pre]'); ?>
					</div><!--/columns-->
					<div class="four columns">
						<h4>Column</h4>
						<p>(Required) The individual columns within each row. Can be pushed or centered. Manually adding "first" or "last" will reset the column counter.</p>
						<?php echo do_shortcode('[pre lang="shortcode"][column eight push="two"]Content Here[/column][/pre]'); ?>
						<?php echo do_shortcode('[pre lang="shortcode"][column ten centered]Content Here[/column][/pre]'); ?>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
			
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Google Map Iframe</h2>
			<h5><a href="https://developers.google.com/maps/documentation/embed/guide" target="_blank">Documentation &raquo;</a></h5>
			<p>This is an iframe integration of Google Maps. Not as flexible as the JavaScript API, but very easy to use.</p>
			<?php echo do_shortcode('[pre lang="html"]<iframe class="googlemap" width="100%" height="250" frameborder="0" src="https://www.google.com/maps/embed/v1/place?key=AIzaSyArNNYFkCtWuMJOKuiqknvcBCyfoogDy3E&q=Pinckney+Hugo+Group&zoom=14&maptype=roadmap"></iframe>[/pre]'); ?>
			<br/>
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
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Google Map API v3</h2>
			<h5><a href="https://developers.google.com/maps/documentation/javascript/tutorial" target="_blank">Documentation &raquo;</a></h5>
			<p>This is the full integration of Google Maps using the API v3. For more advanced functionality, consider using the <a href="https://github.com/HPNeo/gmaps" target="_blank">gmaps library</a>. The Google Maps script only needs to be loaded once per page (Move it to header.php if using it quite a bit, but only load it on required pages!).</p>
			
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
					
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->




<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Youtube Embed</h2>
			<h5><a href="https://developers.google.com/youtube/player_parameters" target="_blank">Documentation &raquo;</a></h5>
			<p>This shows how a Youtube video can be embedded. This iframe integration has corresponding scripts in the footer to track interactions with this video in Google Analytics. Using the Youtube Data API, we can pull information out of the video dynamically. The Gumby wrapper with the class "video" allows for fluid width videos, so it is best to wrap the iframe with that (along with the class of "youtube" (in this case), "vimeo", or "twitch"</p>
			<p><strong>Important:</strong> Make sure to include the query parameter of "enablejsapi=1" for tracking to work (The class "youtubeplayer" must also be present on the iframe element)! It is also recommended to use the query parameter of "wmode=transparent" too.</p>
			
			<?php echo do_shortcode('[pre lang="html"]<article class="youtube video">
	<iframe id="<?php echo $youtube_meta["safetitle"]; ?>" class="youtubeplayer" width="560" height="315" src="http://www.youtube.com/embed/<?php echo $youtube_meta["id"]; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo $youtube_meta["origin"]; ?>&rel=0" frameborder="0" allowfullscreen=""></iframe>
</article>[/pre]'); ?>
			
			<br/>
			
			<div class="container">
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
			</div><!--/container-->
			
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>HTML5 Video</h2>
			<h5><a href="#" target="_blank">Documentation &raquo;</a></h5>
			<p>This is a local video embed that works cross-browser, and soon enough on iOS and mobile devices. File types and codecs will be described here as well.</p>			
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->



<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>CSS Browser Selector</h2>
			<h5><a href="#" target="_blank">Documentation &raquo;</a></h5>
			<p>Useful for debugging, but should only be enabled if feature detection (using Modernizr) will not work!</p>
			
			<div class="container">
				<div class="row">
					<div class="four columns">
						<div class="cssbs"></div>
					</div><!--/columns-->
					<div class="twelve columns">
						Here is text
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
			
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Retina Images</h2>
			<h5><a href="http://gumbyframework.com/docs/components/#!/retina-images" target="_blank">Documentation &raquo;</a></h5>
			<p>Swaps images for a higher resolution on devices with higher pixel density.</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="eight columns">
			<p>This image is only a standard-resolution.</p>
			<img src="<?php bloginfo('template_directory');?>/examples/images/phg/standard.jpg" />
		</div><!--/columns-->
		<div class="eight columns">
			<p>This image has a retina backup.</p>
			<img src="<?php bloginfo('template_directory');?>/examples/images/phg/retina.jpg" gumby-retina />
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>About the Author</h2>
			<p>These boxes are useful for blog posts with multiple authors.</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="ten columns">
			<p>Below content</p>
		</div><!--/columns-->
		<div class="five columns push_one">
			<p>Sidebar</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container phpcookiecon">
	<div class="row">
		<div class="sixteen columns">
			<h2>Cookies (PHP)</h2>
			<p>This shows how cookies can be set using server-side PHP (with AJAX). If toggled on, it should stay on between visits for 30 days (unless cookies are cleared). The benefit here is that there is no FOUC.</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<!-- <p>The cookie is <a class="phpcookie" href="#">OFF</a> <small>(Click to toggle)</small>.</p> -->
						
			<br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Cookies (Javascript)</h2>
			<p>This shows how to easily set, read, and erase cookies using JavaScript. If toggled on, it should stay on between visits for 30 days (unless cookies are cleared).</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<p>Toggle cookie: <a class="jscookie" href="#">OFF</a></p>
			
			<script>
				jQuery(document).ready(function() {	
					checkExample();
					function checkExample() {
						if ( readCookie('examplejs') ) {
							jQuery('.jscookie').text('ON').addClass('cookie-on');
						} else {
							jQuery('.jscookie').text('OFF').removeClass('cookie-on');
						}
					}
					
					jQuery('.jscookie').on('click', function(){
						if ( jQuery(this).hasClass('cookie-on') ) {
							eraseCookie('examplejs');
							checkExample();
						} else {
							createCookie('examplejs', 'true', 30);
							checkExample();
						}
						return false;
					});
				});
			</script>
			
			<br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->



<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Custom Shadows and Nebula Frame</h2>
			<p>Just a couple quick ways to apply elegant shadows. Currently there are two: "Floating" and "Bulging". <a href="http://www.w3.org/TR/css3-values/#attr-value" target="_blank">Once browsers begin to support it</a>, the data attribute of "offset" will allow vertical margin to be applied to the shadow div (current support is for pseudo elements only). <em>Until this happens, shadows will not work properly under iframes.</em></p>
			<p><?php echo do_shortcode('[pre lang="html"]&lt;div class="nebulashadow floating"&gt;&lt;/div&gt;[/pre]'); ?></p>
			<p><?php echo do_shortcode('[pre lang="html"]&lt;div class="nebulashadow bulging" offset="-6"&gt;&lt;/div&gt;[/pre]'); ?></p>
			<p><?php echo do_shortcode('[pre lang="html"]&lt;div class="nebulaframe bulging""&gt;&lt;/div&gt;[/pre]'); ?></p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<div style="background: #fff; outline: 1px solid #f2f2f2;"><h3 style="text-align: center;">Floating</h3></div>
			<div class="nebulashadow floating"></div>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<div style="background: #fff; outline: 1px solid #f2f2f2;"><h3 style="text-align: center;">Bulging</h3></div>
			<div class="nebulashadow bulging"></div>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<div class="nebulaframe bulging"><h3 style="text-align: center; background: #e2e2e2;">Nebula Frame</h3></div>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->



<div class="container">
	<div class="row">
		<div class="eight columns">
			<h2>Native Social Buttons</h2>
			<p>These are social sharing buttons that are generated by loading scripts from the social network's remote servers.</p>
			<p>Documentation: <a href="https://developers.facebook.com/docs/plugins/like-button" target="_blank">Facebook Like</a>, <a href="https://developers.facebook.com/docs/plugins/share-button" target="_blank">Facebook Share</a>, <a href="https://dev.twitter.com/docs/tweet-button" target="_blank">Twitter</a>, <a href="https://developers.google.com/+/web/+1button/" target="_blank">Google+</a>, <a href="https://developer.linkedin.com/plugins/share-plugin-generator" target="_blank">LinkedIn</a></p>
			
			<div class="entry-social">
				<hr/>
				<ul>
					<li class="facebook-like">
						<div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
					</li><!-- /facebook-like -->
					<li class="facebook-share">
						<div class="fb-share-button" data-href="<?php the_permalink(); ?>" data-type="button_count"></div>
					</li><!-- /facebook-share -->
					<li class="twitter">
						<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php the_permalink(); ?>" data-text="<?php the_title(); ?>" data-lang="en">Tweet</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
					</li><!-- /twitter -->
					<li class="google-plus">
						<div class="g-plusone" data-size="medium" data-annotation="bubble" data-callback="googlePlusCallback" data-href="<?php the_permalink(); ?>"></div>
						<script type="text/javascript">
						  (function() {
						    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
						    po.src = 'https://apis.google.com/js/platform.js';
						    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
						  })();
						</script>
					</li><!-- /google-plus -->
					<li class="linked-in">
						<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script>
						<script type="IN/Share" data-url="<?php the_permalink(); ?>" data-counter="right"></script>
					</li><!-- /linkedin -->
				</ul>
			</div>
		</div><!--/columns-->
		<div class="eight columns">
			<h2>Custom Social Buttons</h2>
			<p>These are locally stored social sharing buttons that have hard-coded hrefs. These hrefs can also be modified with javascript to dynamically pull in page titles, etc. There should always be backup hrefs hard-coded in case JS is disabled, these can at least share something.</p>
			<p>
				<a class="fbshare" href="http://www.facebook.com/sharer.php?u=<?php echo the_permalink(); ?>&t=<?php wp_title( '-', true, 'right' ); ?>" target="_blank">Facebook Share</a>,
				<a class="twshare" href="https://twitter.com/intent/tweet?text=<?php wp_title( '-', true, 'right' ); ?>&url=<?php echo the_permalink(); ?>" target="_blank">Twitter</a>,
				<a class="gshare" href="https://plus.google.com/share?url=<?php echo the_permalink(); ?>" target="_blank">Google+</a>,
				<a class="lishare" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo the_permalink(); ?>&title=<?php wp_title( '-', true, 'right' ); ?>" target="_blank">LinkedIn</a>,
				<a class="emshare" href="mailto:?subject=<?php wp_title( '-', true, 'right' ); ?>&body=<?php echo the_permalink(); ?>" target="_blank">Email</a>
			</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->



<div class="container socialcon">
	<div class="row">
		<div class="sixteen columns fb-like-box-con">
			<h2>Facebook Feed</h2>
			<h5><a href="#" target="_blank">Documentation &raquo;</a></h5>
			<p>This is an integrated Facebook feed from a page using the Wordpress plugin Custom Facebook Feed. Also shown is the native Facebook Like Box. If the Custom Facebook Feed is not installed/active, a Like Box will appear instead.</p>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="ten columns">
			<?php if ( is_plugin_active('custom-facebook-feed/custom-facebook-feed.php') ) : ?>
				<div id="fbcon">
					<div class="fbhead">
						<p><a href="<?php echo $social['facebook_url']; ?>" target="_blank"><i class="icon-facebook"></i> Facebook</a></p>
					</div><!--/fbhead-->
					<div class="fbbody">
						<div class="container">
							<div class="fb-feed">
								<div class="row tweetcon">
									<div class="four columns">
										<div class="fbicon"><a href="<?php echo $social['facebook_url']; ?>" target="_blank"><img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash3/s160x160/64307_10150605580729671_377991150_a.jpg"/></a></div>
									</div><!--/columns-->
									<div class="twelve columns">
										<div class="fbuser">
											<a href="<?php echo $social['facebook_url']; ?>" target="_blank"><?php echo bloginfo('name'); ?></a>
										</div><!--/fbuser-->
										<div class="fbpost">
											<?php echo do_shortcode('[custom-facebook-feed id=PinckneyHugo num=3]'); ?>
										</div><!--/fbpost-->
									</div><!--/columns-->
								</div><!--/row-->
							</div><!--/fb-feed-->
						</div><!--/container-->
					</div><!--/fbbody-->
				</div><!--/fbcon-->
			<?php else : ?>
				<div class="fb-like-box" data-href="<?php echo $social['facebook_url']; ?>" data-colorscheme="light" data-show-faces="false" data-header="true" data-stream="true" data-show-border="true"></div>
			<?php endif; ?>
		</div><!--/columns-->
		<div class="five columns push_one">
			<div class="fb-like-box" data-href="<?php echo $social['facebook_url']; ?>" data-colorscheme="light" data-show-faces="false" data-header="true" data-stream="true" data-show-border="true"></div>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<hr/>
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<h2>Twitter Feed</h2>
			<h5><a href="#" target="_blank">Documentation &raquo;</a></h5>
			<p>This is a Twitter integration using twitter.js to pull.</p>
			
			<?php if ( $social['twitter_url'] ) : ?>
				<div id="twittercon">
					<div class="twitterhead">
						<p><a href="<?php echo $social['twitter_url']; ?>" target="_blank"><i class="icon-twitter"></i> Tweets</a></p>
					</div><!--/twitterhead-->
					<div class="twitterbody">
						<div class="container">
							<div class="twitter-feed">		
								<div id="twitter_update_list"></div>
							</div><!--/twitter-feed-->
						</div><!--/container-->
					</div><!--/twitterbody-->
				</div><!--/twittercon-->
			<?php endif; ?>
			
			<br/><br/><hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<h2>Weather Detection</h2>
			<h5><a href="#" target="_blank">Documentation &raquo;</a></h5>
			<p>This is not bundled with Nebula core, but shows how to detect current weather conditions using the Yahoo! Weather feed... It is a small enough script that it could easily be bundled with Nebula... Maybe I'll consider it...</p>
			
			<?php
				//Detect weather for Zip Code (using Yahoo! Weather)
				$locationzip = 13204;
				$url = 'http://weather.yahooapis.com/forecastrss?p=' . $locationzip;
				$use_errors = libxml_use_internal_errors(true);
				$xml = simplexml_load_file($url);
				if (!$xml) {
				  $xml = simplexml_load_file('http://gearside.com/wp-content/themes/gearside2014/includes/static-weather.xml'); //Set a static fallback to prevent PHP errors
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				
				$currentweather = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->text;
				$currenttemp = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->temp;
				
				//Location from zip code
				$weathercity = $xml->channel->children('yweather', TRUE)->location->attributes()->city;
				$weatherstate = $xml->channel->children('yweather', TRUE)->location->attributes()->region;
				
				//Sunrise & Sunset
				$XMLsunrise = $xml->channel->children('yweather', TRUE)->astronomy->attributes()->sunrise;
				$XMLsunset = $xml->channel->children('yweather', TRUE)->astronomy->attributes()->sunset;
				$dayTime["sunrise"] = strtotime($XMLsunrise)-strtotime('today'); //Sunrise in seconds
				$dayTime["sunset"] = strtotime($XMLsunset)-strtotime('today'); //Sunset in seconds
				$dayTime["noon"] = (($dayTime["sunset"]-$dayTime["sunrise"])/2)+$dayTime["sunrise"]; //Solar noon in seconds				
				
				//Determine time of day photo to display
				$currentDayTime = time()-strtotime("today");
			?>
			
			<p>It is currently <strong><?php echo $currenttemp; ?>&deg;F</strong> and <strong><?php echo $currentweather; ?></strong> in <strong><?php echo $weathercity; ?></strong>, <strong><?php echo $weatherstate; ?></strong>.</p>
			<p>Sunrise: <strong><?php echo $XMLsunrise; ?></strong>, Sunset: <strong><?php echo $XMLsunset; ?></strong></p>
			
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>