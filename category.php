<?php
/**
 * The template for displaying Category Archive pages.
 */

get_header(); ?>

<div class="row">
	
	<div class="ten columns">
		<? the_breadcrumb(); ?>
		<h1>Category Archives: <?php echo single_cat_title('', false); ?></h1>
			<?php
				$category_description = category_description();
				if ( !empty($category_description) ) {
					echo '' . $category_description . '';
				}
				get_template_part('loop', 'category');
			?>
	</div><!--/columns-->
	
	<div class="five columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>