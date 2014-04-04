<?php
/**
 * The template for displaying all pages.
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div class="ten columns">
			<? the_breadcrumb(); ?>
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>
						
						<?php wp_link_pages( array( 'before' => '' . __( 'Pages:', 'boilerplate' ), 'after' => '' ) ); ?>
						<?php edit_post_link( __( 'Edit', 'boilerplate' ), '<p class="edit-link">', '</p>' ); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-## -->
				<?php //comments_template( '', true ); ?>
			<?php endwhile; ?>
		</div><!--/columns-->
		<div class="five columns push_one">
			<?php get_sidebar(); ?>
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