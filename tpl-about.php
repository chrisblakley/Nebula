<?php
/**
 * Template Name: About
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
					
					<?php heroslidercon(); ?>
					
					<div class="row">
						<div class="fourteen columns centered">
							
							<ul class="aboutsocial">
								<li><a class="facebook" href="<?php echo $GLOBALS['social']['facebook_url']; ?>" title="Facebook"><i class="fa fw fa-facebook-square"></i></a></li>
								<li><a class="twitter" href="<?php echo $GLOBALS['social']['twitter_url']; ?>" title="Twitter"><i class="fa fw fa-twitter"></i></a></li>
								<li><a class="googleplus" href="<?php echo $GLOBALS['social']['google_plus_url']; ?>" title="Google+"><i class="fa fw fa-google-plus-square"></i></a></li>
								<li><a class="linkedin" href="<?php echo $GLOBALS['social']['linkedin_url']; ?>" title="LinkedIn"><i class="fa fw fa-linkedin-square"></i></a></li>
								<li><a class="github" href="https://github.com/chrisblakley" title="GitHub"><i class="fa fw fa-github"></i></a></li>
								<li><a class="instagram" href="<?php echo $GLOBALS['social']['instagram_url']; ?>" title="Instagram"><i class="fa fw fa-instagram"></i></a></li>
								<li><a class="youtube" href="<?php echo $GLOBALS['social']['youtube_url']; ?>" title="Youtube"><i class="fa fw fa-youtube-square"></i></a></li>
								<li><a class="soundcloud" href="https://soundcloud.com/greatblakes" title="Soundcloud"><i class="fa fw fa-soundcloud"></i></a></li>
							</ul>
							
							<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
																		
									<!-- <p><?php the_content(); ?></p> -->
									
									<h1>
										<label id="iamatitle" for="iama">I am a</label> <input id="iama" type="text" list="iamalist" placeholder="graphic designer"/> <span class="loading faded"><i></i></span>
										<datalist id="iamalist">
											<?php if ( have_rows('hats') ): ?>
												<?php while( have_rows('hats') ): the_row(); ?>
													<?php if ( get_sub_field('datalist') ) : ?>
														<option value="<?php the_sub_field('keyphrase') ?>">
													<?php endif; ?>
												<?php endwhile; ?>
											<?php endif; ?>
										</datalist>
									</h1>
									
									<div id="iamadesc">
										<p>From print to web and even a little animation, music, and video/photography, I've had my hand in every aspect of graphic design. I try to make myself as versatile as possible because in a busy industry, it's good to have someone that can work on nearly any project regardless of the skill set required.</p>
										<p>I've been published in several books and even had work appear in a Dreamworks movie. My work has also been seen across the web on sites like SmashingApps and Gizmodo in addition to television channels like the Showtime/Smithsonian Channel. Work that I've had a hand in designing and printing can be seen all across Central New York, and other parts of the country.</p>
									</div>
									
									<?php if ( current_user_can('manage_options') ) : ?>
										<div class="container entry-manage">
											<div class="row">
												<hr/>
												<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
												<hr/>
											</div>
										</div>
									<?php else : ?>
										<hr class="articleend" />
									<?php endif; ?>
								</article><!-- #post-## -->
							<?php endwhile; ?>
						</div><!--/columns-->
					</div><!--/row-->
										
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