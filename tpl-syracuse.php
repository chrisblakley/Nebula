<?php
/**
 * Template Name: Syracuse
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
					
					<?php heroslidercon(); //If for a content page (else use: <br/><br/>) ?>
					
					<div class="row">
						<div class="fourteen columns centered">
							
							[Content Here]
							
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