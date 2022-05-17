<?php
	/**
	 * Template Name: Grid Test
	 */

	if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
		header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF'])); //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		exit;
	}

	do_action('nebula_preheaders');
	get_header();
?>

<div class="grid-container">
	<?php if ( has_nav_menu('utility') ): ?>
		<div class="Utility-Navigation">
			<div class="container">
				<div class="row">
					<div class="col">
						<nav id="utility-nav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Utility navigation">
							<meta itemprop="name" content="Utility Menu">
							<?php wp_nav_menu(array('theme_location' => 'utility')); ?>
						</nav>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="container">
		<div class="row">
			<div class="col">
				<div class="Logo">
					<a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name'); ?>">
						<?php $logo = nebula()->logo('header'); ?>
						<?php if ( !empty($logo) ): ?>
							<img class="svg" src="<?php echo $logo; ?>" alt="<?php bloginfo('name'); ?>" importance="high" />
						<?php else: //Otherwise fallback to the Site Title text ?>
							<?php bloginfo('name'); ?>
						<?php endif; ?>
					</a>
				</div>
			</div>
			<div class="col">
				<?php if ( has_nav_menu('primary') ): ?>
					<div class="Primary-Navigation">
						<nav id="primary-nav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Primary navigation">
							<meta itemprop="name" content="Primary Menu">
							<?php wp_nav_menu(array('theme_location' => 'primary')); ?>
						</nav>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>


	<div class="Hero">
		<div class="container">
			<div class="row">
				<div class="col">
					<h1 class="entry-title"><?php echo esc_html(get_the_title()); ?></h1>
				</div>
			</div>
		</div>
	</div>

	<div class="Content">
		<div class="container">
			<div class="row">
				<div class="col">
					<div id="breadcrumb-section" class="full inner dark">
						<?php nebula()->breadcrumbs(); ?>
					</div><!--/breadcrumb-section-->

					<main id="top" role="main">
						<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<div class="entry-content">
									<?php the_content(); ?>
								</div>
							</article>

							<?php if ( is_active_sidebar('single-post-widget-area') ): ?>
								<div id="single-post-widget-area">
									<?php dynamic_sidebar('single-post-widget-area'); ?>
								</div>
							<?php endif; ?>

							<?php comments_template(); ?>
						<?php endwhile; ?>
					</main>
				</div>

				<?php if ( 1==2 ): //Toggle the sidebar on and off to ensure the grid compensates ?>
					<div class="col-4">
						<div class="Sidebar">
							<p>Sidebar</p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>



	<div class="Footer">
		<div class="container">
			<div class="row">
				<div class="col">
					<nav id="powerfooter" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" aria-label="Footer navigation">
						<meta itemprop="name" content="Footer Menu">
						<?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => 2)); ?>
					</nav>

					<p class="copyright">
						<?php if ( get_theme_mod('nebula_footer_text') ): ?>
							<?php echo get_theme_mod('nebula_footer_text'); ?>
						<?php else:?>
							&copy; <?php echo date('Y'); ?> <a href="<?php echo home_url('/'); ?>"><strong><?php echo ( nebula()->get_option('site_owner') )? esc_html(nebula()->get_option('site_owner')) : get_bloginfo('name'); ?></strong></a>, <em><?php _e('all rights reserved', 'nebula'); ?></em>.
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>