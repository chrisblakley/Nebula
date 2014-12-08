<?php
/**
 * The template for displaying Category Archive pages.
 */

if ( !defined('ABSPATH') ) { //Log and redirect if accessed directly
	ga_send_event('Direct Template Access', 'Template: ' . end(explode('/', $template)), basename($_SERVER['PHP_SELF']));
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")));
	exit;
}

get_header(); ?>

<div class="row">

	<div class="eleven columns">
		<?php the_breadcrumb(); ?>
		<h1>Category Archives: <?php echo single_cat_title('', false); ?></h1>
			<?php
				$category_description = category_description();
				if ( !empty($category_description) ) {
					echo '' . $category_description . '';
				}
				get_template_part('loop', 'category');
			?>
	</div><!--/columns-->

	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->

</div><!--/row-->

<?php get_footer(); ?>