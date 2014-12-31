<?php
/**
 * Template Name: Advanced Search
 */

if ( !defined('ABSPATH') ) { //Log and redirect if accessed directly
	ga_send_event('Security Precaution', 'Direct Template Access Prevention', 'Template: ' . end(explode('/', $template)), basename($_SERVER['PHP_SELF']));
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")));
	exit;
}

get_header(); ?>

<div class="row">
	<div class="sixteen columns">
		<?php the_breadcrumb(); ?>
		<hr/>
	</div><!--/columns-->
</div><!--/row-->

<div class="row fullcontentcon">

	<div class="eleven columns">
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>

				<?php
					/* @TODO "Nebula" 0:
						- When an autocomplete selection is made, trigger that page location instead of using the search. Could the datalist not be powerful enough for this? Maybe we need to AJAX into a div with links?

					*/
				?>

				<!-- Eh, I'd like to opt for an AJAX autocomplete over a datalist.
<form class="search" method="get" action="<?php echo home_url('/'); ?>">
					<input type="text" list="advancedsearch" name="s" style="width: 100%; font-size: 28px; padding: 2px 10px; outline: none;" placeholder="Search" required/>
					<datalist id="advancedsearch">
						<?php //@TODO "Nebula" 0: These will be dynamically created. ?>
						<option value="Page title example">
						<option value="This is an example post title">
						<option value="Menu Item Here">
						<option value="Category 1">
						<option value="Category 2">
						<option value="A Tag">
						<option value="Another Tag Here">
						<option value="This is yet another tag">
					</datalist>
				</form>
-->

				<hr/><br/><br/><br/><br/><br/>


				<script>
					jQuery(document).ready(function ($){
					    var acs_action = 'myprefix_autocompletesearch';
					    $("#s").autocomplete({
					        source: function(req, response){
					            $.getJSON(bloginfo["admin_ajax"]+'?callback=?&action='+acs_action, req, response);
					        },
					        select: function(event, ui) {
					            window.location.href=ui.item.link;
					        },
					        minLength: 3,
					    });
					});
				</script>


				<?php get_search_form(); ?>


				<!--
<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				    <label for="s"><?php _e( 'Search', 'twentyeleven' ); ?></label>
				    <input type="text" name="s" id="s" placeholder="<?php esc_attr_e( 'Search', 'twentyeleven' ); ?>" />
				    <input type="submit" name="submit" id="searchsubmit" value="<?php esc_attr_e( 'Search', 'twentyeleven' ); ?>" />
				</form>
-->






				<br/><br/><br/><br/><br/><hr/>















				<div class="entry-content">
					<?php the_content(); ?>

					<?php if ( current_user_can('manage_options') ) : ?>
						<div class="container entry-manage">
							<div class="row">
								<hr/>
								<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
								<hr/>
							</div>
						</div>
					<?php endif; ?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->
		<?php endwhile; ?>
	</div><!--/columns-->

	<div class="four columns push_one">
		<h3>Contact Us</h3>
		<?php if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) : ?>
			<ul id="cform7-container">
				<?php echo do_shortcode('[contact-form-7 id="384" title="Contact Form 7 Documentation"]'); ?>
			</ul>
		<?php else : ?>
			<div class="row">
				<div class="sixteen columns">
					<?php nebula_backup_contact_form(); ?>
				</div><!--/columns-->
			</div><!--/row-->
		<?php endif; ?>
	</div><!--/columns-->

</div><!--/row-->

<?php get_footer(); ?>