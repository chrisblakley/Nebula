<?php
/**
 * The template for displaying Category Archive pages.
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

//Redirect "Ideas" and "Resources" category archives to their actual pages.
if ( is_category('Ideas') ) {
	header('Location: ' . get_permalink(87));
} elseif ( is_category('Resources') ) {
	header('Location: ' . get_permalink(90));
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
							
							<h1>Archive: <span style="white-space: nowrap;"><?php echo single_cat_title('', false); ?></span></h1>
							<?php
								$category_description = category_description();
								if ( !empty($category_description) ) {
									echo '' . $category_description . '';
								}
								get_template_part('loop', 'category');
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