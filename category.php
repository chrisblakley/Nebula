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
				<?php echo category_description(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</section>

<div id="breadcrumb-section" class="full">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php nebula_breadcrumbs(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/breadcrumb-section-->

<div id="content-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php get_template_part('loop', 'category'); ?>
				<?php wp_pagenavi(); ?>
			</div><!--/col-->
			<div class="col-md-3 col-md-offset-1">
				<?php get_sidebar(); ?>
			</div><!--/col-->
		</div><!--/row-->
	</div><!--/container-->
</div><!--/content-section-->

<?php get_footer(); ?>