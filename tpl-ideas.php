<?php
/**
 * Template Name: Ideas
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
									
									<h1 class="entry-title centered"><?php the_title(); ?></h1>
									
									<hr/>
									
									<div class="entry-content centered">
										<?php the_content(); ?>																
									</div><!-- .entry-content -->
									
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
					
					<br/>
					
					<?php $count = 0; ?>
					<?php query_posts( array( 'category_name' => 'ideas', 'showposts' => 4, 'paged' => get_query_var('paged') ) ); ?>
						<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
							
							<?php if ( $count == 2 ) : ?>
								<?php if ( $GLOBALS["mobile_detect"]->isMobile() || $GLOBALS["mobile_detect"]->isTablet() ) : ?>
									<div class="row listingcon grid">
										<div class="fourteen columns centered">
											<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
											<!-- Gearside Listing Responsive -->
											<ins class="adsbygoogle"
											     style="display:block"
											     data-ad-client="ca-pub-3057391662144745"
											     data-ad-slot="9441075060"
											     data-ad-format="auto"></ins>
											<script>
											(adsbygoogle = window.adsbygoogle || []).push({});
											</script>
										</div><!--/columns-->
									</div><!--/row-->
								<?php else : ?>
									<div class="row listingcon grid">
										<div class="fourteen columns centered">
											<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
											<!-- Gearside Leaderboard -->
											<ins class="adsbygoogle"
											     style="display:inline-block;width:728px;height:90px"
											     data-ad-client="ca-pub-3057391662144745"
											     data-ad-slot="7582565469"></ins>
											<script>
											(adsbygoogle = window.adsbygoogle || []).push({});
											</script>
										</div><!--/columns-->
									</div><!--/row-->
								<?php endif; ?>
							<?php endif; ?>
							
							<div class="row listingcon grid">
								<div class="fourteen columns centered">
									<?php $slides = get_field('slider'); ?>
									<?php $post_background = ( $slides[0]['slide_image'] ) ? $slides[0]['slide_image'] : get_template_directory_uri() . '/images/default_listing.jpg'; ?>
									<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background: url('<?php echo $post_background; ?>') no-repeat;">
										<div class="glassbg" style="background: url('<?php echo $post_background; ?>') no-repeat;"></div>
										<div class="glasscon">
											<?php
												foreach ( get_the_category() as $category ) {
													if ( $category->slug != 'ideas' && $category->slug != 'resources' ) {
														$branded_category = $category->name;
														$category_slug = $category->slug;
														$category_id = $category->term_id;
													}
												}
											?>
											<a class="catbar <?php echo $category_slug; ?>" href="<?php echo get_category_link($category_id); ?>" style="background-color: <?php the_field('color', 'category_' . $category_id); ?>;"><?php echo $branded_category; ?></a>
											
											<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
											<div class="metadata"><?php echo nebula_meta('date'); ?></div>
											<p class="postexcerpt"><?php echo nebula_the_excerpt('Read more &raquo;', 30, 1); ?></p>
											<div class="metadata metatags"><?php echo nebula_meta('tags'); ?></div>
										</div>
									</article>							
								</div><!--/columns-->
							</div><!--/row-->
							
							<?php $count++; ?>
						<?php endwhile; ?>
						<?php wp_pagenavi(); ?>
					<?php wp_reset_query(); ?>

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