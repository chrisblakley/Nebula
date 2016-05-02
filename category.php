<?php
/**
 * The template for displaying Category Archive pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1 class="page-title"><i class="fa fa-fw fa-bookmark"></i> <?php echo single_cat_title('', false); ?></h1>
				<?php if ( 1==2 ): //@TODO "Nebula" 0: pull the category description if it exists ?>
					<p>Category description from WordPress here...</p>
				<?php endif; ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="breadcrumbbar">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php the_breadcrumb(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
	<hr />
</div>

<div class="container fullcontentcon">
	<div class="row">
		<div class="col-md-8">
			<?php
				$category_description = category_description();
				if ( !empty($category_description) ){
					echo $category_description . '';
				}
				get_template_part('loop', 'category');
				wp_pagenavi();
			?>
		</div><!--/col-->
		<div class="col-md-4">
			<?php get_sidebar(); ?>
		</div><!--/col-->
	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>