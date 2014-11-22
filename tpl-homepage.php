<?php
/**
 * Template Name: Homepage
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div id="maincontentareawrap" class="row">
	<div class="thirteen columns">

		<section class="sixteen colgrid">
			<div class="container">

				<div id="bcrumbscon" class="row">
					<?php the_breadcrumb(); ?>
				</div><!--/row-->

				<div class="contentbg">
					<div class="corner-left"></div>
					<div class="corner-right"></div>

					<div class='heroslidercon'>
						<div class="row catbarcon">
							<div class="fourteen columns push_one">
								<span class="catbar featured-posts">Featured Posts</span>
							</div>
						</div>

						<?php if( have_rows('post_slider') ): ?>
							<?php $bxslider_class = ( count(get_field('post_slider')) > 1 && !$GLOBALS["mobile_detect"]->isMobile() ) ? 'bxslider' : ''; ?>
							<ul class="heroslider <?php echo $bxslider_class; ?>">
								<?php while( have_rows('post_slider') ): the_row(); ?>
									<li class="heroslide">
										<div class="row featuredimage">
											<?php $post_object = get_sub_field('post_slide'); ?>
											<?php $this_posts_first_slide = get_field('slider', $post_object->ID); ?>
											<img src="<?php echo $this_posts_first_slide[0]['slide_image']; ?>" />
											<hr style="margin-top: 5px;" />
										</div><!--/row-->

										<?php if ( $post_object->post_title || $post_object->post_content ) : ?>
											<div class="row">
													<div class="eleven columns push_four slidecaptionwrap"> <!-- Default: six push_nine -->
														<div class="slidecaptioncon">
															<a class="captiontitle" href="<?php echo get_permalink($post_object->ID); ?>"><?php echo $post_object->post_title; ?></a>
															<span class="captiondesc"><?php echo nebula_the_excerpt($post_object->ID, 'View &raquo;', 25, 1); ?></span>
														</div>
													<div class="nebulashadow anchored-right"></div>
												</div><!--/columns-->
											</div><!--/row-->
										<?php endif; ?>
									</li><!--/heroslide-->

									<?php if ( $GLOBALS["mobile_detect"]->isMobile() ) { break; } //Only load one featured post on mobile devices. ?>
								<?php endwhile; ?>
							</ul>
						<?php endif; ?>
					</div><!--/heroslidercon-->

					<div class="row">
						<div class="fourteen columns centered">
							<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

									<h1 class="home-title"><?php the_title(); ?></h1>
									<hr/>
									<div class="home-content">
										<?php the_content(); ?>
									</div><!-- .entry-content -->
									<hr/>
								</article><!-- #post-## -->
							<?php endwhile; ?>
						</div><!--/columns-->
					</div><!--/row-->


					<?php if ( isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') === false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) ) : ?>
						<?php $mobile_hover = ( $GLOBALS["mobile_detect"]->isMobile() || $GLOBALS["mobile_detect"]->isTablet() ) ? 'hover' : ''; ?>
						<div class="row homebucketscon grid">
							<div class="six columns push_one bucketwrap">
								<figure class="<?php echo $mobile_hover; ?>">
									<img src="<?php echo get_template_directory_uri(); ?>/images/bucket-ideas.jpg" />
									<figcaption>
										<h2><i class="fa fa-lightbulb-o"></i></h2>
										<p>Ideas</p>
										<a href="http://gearside.com/ideas/">View more</a>
									</figcaption>
								</figure>
								<div class="nebulashadow floating"></div>
							</div><!--/columns-->
							<div class="six columns push_two bucketwrap">
								<figure class="<?php echo $mobile_hover; ?>">
									<img src="<?php echo get_template_directory_uri(); ?>/images/bucket-resources.jpg" />
									<figcaption>
										<h2><i class="fa fa-book"></i></h2>
										<p>Resources</p>
										<a href="http://gearside.com/resources/">View more</a>
									</figcaption>
								</figure>
								<div class="nebulashadow floating"></div>
							</div><!--/columns-->
						</div><!--/row-->
					<?php else : ?>
						<div class="row homebucketscon grid">
							<div class="six columns push_one ieadcon">
								<div class="googleresources iead">
									<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
									<!-- Gearside 2015 IE Skyscraper -->
									<ins class="adsbygoogle"
									     style="display:inline-block;width:300px;height:600px"
									     data-ad-client="ca-pub-3057391662144745"
									     data-ad-slot="7518988268"></ins>
									<script>
									(adsbygoogle = window.adsbygoogle || []).push({});
									</script>
									<span>
										This ad is shown to IE users. <a class="iewhylink" href="#">(Why?)</a><br/>
										<span class="iewhy hidden">In order to cater to older versions of Internet Explorer, some websites serve the same degraded version of a site to all IE users. You may be seeing downgraded versions of websites even if you are using the latest version of Internet Explorer!</span>
										<a href="http://www.google.com/chrome/‎">Upgrade to Google Chrome &raquo;</a>
									</span>
								</div>
							</div><!--/columns-->
							<div class="six columns push_two">
								<figure class="effect-chico">
									<img src="<?php echo get_template_directory_uri(); ?>/images/bucket-ideas.jpg" />
									<figcaption>
										<h2><i class="fa fa-lightbulb-o"></i></h2>
										<p>Ideas</p>
										<a href="#">View more</a>
									</figcaption>
								</figure>
								<div class="nebulashadow floating"></div>
								<br/><br/>
								<figure class="effect-chico">
									<img src="<?php echo get_template_directory_uri(); ?>/images/bucket-resources.jpg" />
									<figcaption>
										<h2><i class="fa fa-book"></i></h2>
										<p>Resources</p>
										<a href="#">View more</a>
									</figcaption>
									<div class="nebulashadow floating"></div>
								</figure>
							</div><!--/columns-->
						</div><!--/row-->
					<?php endif; ?>









				</div><!--/contentbg-->
				<div class="nebulashadow floating"></div>
			</div><!--/container-->
		</section><!--/colgrid-->

	</div><!--/columns-->
	<div class="three columns">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>