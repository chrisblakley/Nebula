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


<div class="container socialcon">
	<div class="row">
		<div class="eight columns fb-like-box-con">
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
										<?php echo do_shortcode('[custom-facebook-feed id=PinckneyHugo num=1]'); //This is currently using one of my access tokens. We need to create one for WineGuardian using their FB login creds. ?>
									</div><!--/fbpost-->
								</div><!--/columns-->
							</div><!--/row-->
						</div><!--/fb-feed-->
					</div><!--/container-->
				</div><!--/fbbody-->
			</div><!--/fbcon-->
		</div><!--/columns-->
		<div class="eight columns">
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
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>
