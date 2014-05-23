<?php
/**
 * Template Name: Examples
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<? the_breadcrumb(); ?>
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>
						
						<?php wp_link_pages( array( 'before' => '' . 'Pages:', 'after' => '' ) ); ?>
						<?php edit_post_link( 'Edit', '<p class="edit-link">', '</p>' ); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-## -->
				<?php //comments_template( '', true ); ?>
			<?php endwhile; ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
			<hr/>
				<h2>Basic WP Query</h2>
				<p>This is a basic Wordpress query posts, but also shows how Gumby columns can be integrated into the logic.</p>
				<div class="container">
					<div class="row">
					    <?php $count = 0; ?>
					    <?php query_posts( array ( 'category_name' => 'Examples', 'showposts' => 4 ) ); ?>
					        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
					            <?php if ( $count%2 == 0 && $count != 0 ) : ?>
					                </div><!--/row-->
					                <div class="row">
					            <?php endif; ?>
					                     
					            <div class="eight columns"><!--This example uses Gumby columns-->
							        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							            <h5 class="news-title entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h5>
							            <span class="newsdate"><em><a href="#"><?php the_time('F j, Y'); ?></a></em></span>
							            <div class="entry-content">
							                <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
							                <p><?php edit_post_link( 'Edit', '', '' ); ?></p>
							            </div><!-- .entry-content -->
							        </article><!-- #post-## -->
							    </div><!--/columns-->
					                         
					            <?php $count++; ?>
					                         
					        <?php endwhile; ?>
					    <?php wp_reset_query(); ?>
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
				<p>This is an iframe integration of Google Maps. Not as flexible as the JavaScript API, but very easy to use.</p>
				<iframe
					width="100%"
					height="250"
					frameborder="0" style="border:0"
					src="https://www.google.com/maps/embed/v1/place
					?key=AIzaSyArNNYFkCtWuMJOKuiqknvcBCyfoogDy3E
					&q=Pinckney+Hugo+Group
					&zoom=14
					&maptype=roadmap">
				</iframe>
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->



<div class="container">
	<div class="row">
		<div class="sixteen columns">
				<h2>Google Map API v3</h2>
				<p>This is the full integration of Google Maps using the API v3.</p>
				
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->




<div class="container">
	<div class="row">
		<div class="sixteen columns">
				<h2>Youtube Embed</h2>
				<p>This shows how a Youtube video can be embedded. This iframe integration has corresponding scripts in the footer to track interactions with this video in Google Analytics.</p>
				
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->


<div class="container">
	<div class="row">
		<div class="sixteen columns">
				<h2>HTML5 Video</h2>
				<p>This is a local video embed that works cross-browser, and soon enough on iOS and mobile devices. File types and codecs will be described here as well.</p>
				
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->



<div class="container socialcon">
	<div class="row">
		<div class="sixteen columns fb-like-box-con">
			<h2>Facebook Feed</h2>
			<p>This is an integrated Facebook feed from a page using the Wordpress plugin Custom Facebook Feed.</p>
			<div id="fbcon">
				<div class="fbhead">
					<p><a href="https://www.facebook.com/PinckneyHugo" target="_blank"><i class="icon-facebook"></i>Facebook</a></p>
				</div><!--/fbhead-->
				<div class="fbbody">
					<div class="container">
						<div class="fb-feed">
							<div class="row tweetcon">
								<div class="four columns">
									<div class="fbicon"><a href="https://www.facebook.com/PinckneyHugo" target="_blank"><img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash3/s160x160/64307_10150605580729671_377991150_a.jpg"/></a></div>
								</div><!--/columns-->
								<div class="twelve columns">
									<div class="fbuser">
										<a href="https://www.facebook.com/PinckneyHugo" target="_blank">Pinckney Hugo Group</a>
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
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
	<div class="row">
		<div class="sixteen columns">
			<h2>Twitter Feed</h2>
			<p>This is a Twitter integration using twitter.js to pull.</p>
			<div id="twittercon">
				<div class="twitterhead">
					<p><a href="https://twitter.com/pinckneyhugo" target="_blank"><i class="icon-twitter"></i>Tweets</a></p>
				</div><!--/twitterhead-->
				<div class="twitterbody">
					<div class="container">
						<div class="twitter-feed">		
							<div id="twitter_update_list"></div>
						</div><!--/twitter-feed-->
					</div><!--/container-->
				</div><!--/twitterbody-->
			</div><!--/twittercon-->
			<hr/>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>
