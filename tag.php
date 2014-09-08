<?php
/**
 * The template for displaying Tag Archive pages.
 */

if ( !defined('ABSPATH') ) { exit; } //Exit if accessed directly

get_header(); ?>

<div class="row">
	
	<div class="eleven columns">
		<?php the_breadcrumb(); ?>
		<h1>Tag Archives: <?php echo single_tag_title('', false); ?></h1>
		<?php get_template_part('loop', 'tag'); ?>
	</div><!--/columns-->
	
	<div class="four columns push_one">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
	
</div><!--/row-->

<?php get_footer(); ?>