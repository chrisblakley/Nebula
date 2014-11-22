<?php
/**
 * Template Name: Advanced Search
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
							<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									<h1 class="entry-title"><?php the_title(); ?></h1>
									<div class="entry-content">
										<?php the_content(); ?>
																
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
									</div><!-- .entry-content -->
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