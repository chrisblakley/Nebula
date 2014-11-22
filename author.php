<?php
/**
 * The template for displaying Author Archive pages.
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
					
					<br/><br/>
					
					<?php if ( have_posts() ) { the_post(); } //Queue the first post then reset it before the loop. ?>
					<div id="about-the-author" class="row">
						
						<?php if ( get_the_author_meta('headshot_url') ) : ?>
							<div class="three columns">
								<div class="author-headshot">
									<img src="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" />
								</div>
							</div><!--/columns-->
							<div class="thirteen columns">
						<?php else : ?>
							<div class="sixteen columns">
						<?php endif; ?>
							
							<h1><?php if ( get_the_author_meta('user_url') ) : ?><a href="<?php echo get_the_author_meta('user_url'); ?>" target="_blank"><?php endif; ?><?php echo get_the_author(); ?><?php if ( get_the_author_meta('user_url') ) : ?></a><?php endif; ?></h1>
							
							<hr/>
								<div class="author-meta">
									<span class="author-jobtitle"><?php echo get_the_author_meta('jobtitle'); ?></span>
									<span class="author-contact">
										<?php if ( get_the_author_meta('user_email') ) : ?><span class="author-email"><i class="icon-mail"></i> <a href="mailto:<?php echo get_the_author_meta('user_email'); ?>" target="_blank"><?php echo get_the_author_meta('user_email'); ?></a></span>&nbsp;<?php endif; ?>
										<?php if ( get_the_author_meta('phonenumber') ) : ?><span class="author-phonenumber"><i class="icon-phone"></i> <?php echo nebula_tel_link(get_the_author_meta('phonenumber')); ?></span><?php endif; ?>
									</span>
								</div>
							<hr/>
							<br/>
							<p class="authorbio"><?php echo the_author_meta('description'); ?></p>
						</div><!--/columns-->
					</div><!--/row-->
					
					<div class="row">
						<div class="fourteen columns centered">
							
							<h2>Articles by <?php echo get_the_author_meta('first_name'); ?></h2>
							<?php
								rewind_posts();
								get_template_part('loop', 'author');
							?>
							
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