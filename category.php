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
			<div class="sixteen columns">
				<h1><i class="fa fa-fw fa-bookmark"></i> <?php echo single_cat_title('', false); ?></h1>
				<?php if ( 1==2 ): //@TODO "Nebula" 0: pull the category description if it exists ?>
				<p>Category description from WordPress here...</p>
				<?php endif; ?>
			</div><!--/columns-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div class="breadcrumbbar">
	<div class="row">
		<div class="sixteen columns">
			<?php the_breadcrumb(); ?>
		</div><!--/columns-->
	</div><!--/row-->
	<hr />
</div><!--/container-->

<div class="container fullcontentcon">
	<div class="row">

		<div class="eleven columns">
			<?php
				$category_description = category_description();
				if ( !empty($category_description) ){
					echo $category_description . '';
				}
				get_template_part('loop', 'category');
			?>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>