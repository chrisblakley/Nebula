<?php
/**
 * The template for displaying Archive pages.
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
					
					<div class="row">
						<div class="fourteen columns centered">
							
							<?php if ( have_posts() ) { the_post(); } //Queue the first post, then reset before running the loop. ?>
							<h1 class="page-title">
								<?php if ( is_day() ) : ?>
									<?php //header('Location: ' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/') ; //This does not work on all servers (because it's called after headers are already sent). Uncomment to test if will work on your server. ?>
									Archive for <span style="white-space: nowrap;"><?php echo get_the_date(); ?></span>
								<?php elseif ( is_month() ) : ?>
									Archive for <span style="white-space: nowrap;"><?php echo get_the_date('F Y'); ?></span>
								<?php elseif ( is_year() ) : ?>
									Archive for <span style="white-space: nowrap;"><?php echo get_the_date('Y'); ?></span>
								<?php else : ?>
									Archives
								<?php endif; ?>
							</h1>
							<?php
								rewind_posts(); //Reset the queue before running the loop.
								get_template_part('loop', 'archive');
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