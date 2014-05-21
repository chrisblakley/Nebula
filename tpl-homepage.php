<?php
/**
 * Template Name: Homepage
 */

get_header(); ?>

<div id="heroslidercon">
	<h3>PHG Nebula</h3>
</div><!--/heroslidercon-->

<div class="container">
	<div class="row">
		<div class="ten columns">
			<p>This is a sample listing page using query_posts() and the while loop and pagination using wp_pagenavi.</p>
			<ul class="postscon">
				<?php query_posts( 'post_type=movie', array( 'showposts' => 3, 'paged' => get_query_var('paged') ) ); //@TODO: Remove post_type to query all posts. ?>
					<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
						<li>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<h2 class="entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
								<div class="entry-content">
									<?php echo nebula_the_excerpt('Read more &raquo;', 20, 1); ?>
									<p><?php edit_post_link( 'Edit', '', '' ); ?></p>
								</div><!-- .entry-content -->
							</article><!-- #post-## -->
						</li>
					<?php endwhile; ?>
					<?php wp_pagenavi(); ?>
				<?php wp_reset_query(); ?>
			</ul><!--/postscon-->
		</div><!--/columns-->
		<div class="five columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>
