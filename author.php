<?php
/**
 * The template for displaying Author Archive pages.
 */

if ( !defined('ABSPATH') ){ //Redirect (for logging) if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?ndaat=' . basename($_SERVER['PHP_SELF']));
	die('Error 403: Forbidden.');
}

if ( nebula_option('author_bios', 'disabled') ){
	header('Location: ' . home_url('/') . '?s=about');
	die('Error 403: Forbidden.');
}

do_action('nebula_preheaders');
get_header(); ?>

<section id="bigheadingcon">
	<div class="container">
		<div class="row">
			<div class="sixteen columns">
				<h1 class="page-title articles-by">Articles by <strong><?php echo ( get_the_author_meta('first_name') != '' )? get_the_author_meta('first_name') : get_the_author_meta('display_name'); ?></strong></h1>
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

<?php if ( have_posts() ){ the_post(); } //Queue the first post then reset it before the loop. ?>
<div id="about-the-author" class="container">
	<div class="row">
	<?php if ( get_the_author_meta('headshot_url') ): ?>
		<div class="three columns">
			<div class="author-headshot">
				<img src="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" />
			</div>
		</div><!--/columns-->
		<div class="thirteen columns">
	<?php else : ?>
		<div class="sixteen columns">
	<?php endif; ?>

			<h2 class="author-name">
				<?php if ( get_the_author_meta('user_url') ): ?>
					<a href="<?php echo esc_url(get_the_author_meta('user_url')); ?>" target="_blank">
				<?php endif; ?>
						<?php echo get_the_author(); ?>
				<?php if ( get_the_author_meta('user_url') ): ?>
					</a>
				<?php endif; ?>
			</h2>
			<?php if ( get_the_author_meta('userlocation') ): ?>
				<span class="author-location"><i class="fa fa-map-marker"></i> <a href="https://www.google.com/maps?q=<?php echo urlencode(str_replace(',', '', get_the_author_meta('userlocation'))); ?>" target="_blank"><?php echo get_the_author_meta('userlocation'); ?></a></span>
			<?php endif; ?>


			<span class="author-social">
				<?php if ( get_the_author_meta('facebook', $user->ID) ): ?>
					<a class="facebook" href="http://www.facebook.com/<?php echo get_the_author_meta('facebook', $user->ID); ?>" target="_blank" title="<?php echo get_the_author_meta('facebook', $user->ID); ?>"><i class="fa fa-facebook-square"></i></a> <!-- add tooltips or titles -->
				<?php endif; ?>

				<?php if ( get_the_author_meta('twitter', $user->ID) ): ?>
					<a class="twitter" href="http://www.twitter.com/<?php echo get_the_author_meta('twitter', $user->ID); ?>" target="_blank" title="@<?php echo get_the_author_meta('twitter', $user->ID); ?>"><i class="fa fa-twitter-square"></i></a>
				<?php endif; ?>

				<?php if ( get_the_author_meta('googleplus', $user->ID) ): ?>
					<a class="googleplus" href="https://plus.google.com/+<?php echo get_the_author_meta('googleplus', $user->ID); ?>" target="_blank" title="<?php echo get_the_author_meta('googleplus', $user->ID); ?>"><i class="fa fa-google-plus-square"></i></a>
				<?php endif; ?>

				<?php if ( get_the_author_meta('linkedin', $user->ID) ): ?>
					<a class="linkedin" href="https://www.linkedin.com/profile/view?id=<?php echo get_the_author_meta('linkedin', $user->ID); ?>" target="_blank" title="<?php echo get_the_author_meta('linkedin', $user->ID); ?>"><i class="fa fa-linkedin-square"></i></a>
				<?php endif; ?>

				<?php if ( get_the_author_meta('youtube', $user->ID) ): ?>
					<a class="youtube" href="https://www.youtube.com/channel/<?php echo get_the_author_meta('youtube', $user->ID); ?>" target="_blank" title="<?php echo get_the_author_meta('youtube', $user->ID); ?>"><i class="fa fa-youtube"></i></a>
				<?php endif; ?>

				<?php if ( get_the_author_meta('instagram', $user->ID) ): ?>
					<a class="instagram" href="http://instagram.com/<?php echo get_the_author_meta('instagram', $user->ID); ?>" target="_blank" title="<?php echo get_the_author_meta('instagram', $user->ID); ?>"><i class="fa fa-instagram"></i></a>
				<?php endif; ?>
			</span>


			<div class="author-meta">
				<hr />
				<span class="author-jobtitle">
					<?php if ( get_the_author_meta('jobtitle') || get_the_author_meta('jobcompany') ): ?>
						<i class="fa fa-building"></i>
					<?php endif; ?>
					<?php if ( get_the_author_meta('jobtitle') ): ?>
						<?php echo esc_html(get_the_author_meta('jobtitle')); ?>
						<?php if ( get_the_author_meta('jobcompany') ): ?>
							&nbsp;at
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( get_the_author_meta('jobcompany') ): ?>
						<span style="white-space: nowrap;">
							<?php if ( get_the_author_meta('jobcompanywebsite') ): ?>
								<a href="<?php echo esc_url(get_the_author_meta('jobcompanywebsite')); ?>" target="_blank">
							<?php endif; ?>
									<?php echo get_the_author_meta('jobcompany'); ?>
							<?php if ( get_the_author_meta('jobcompanywebsite') ): ?>
								</a>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</span>
				<span class="author-contact">
					<?php if ( get_the_author_meta('user_email') ): ?>
						<span class="author-email"><i class="fa fa-envelope"></i> <a href="mailto:<?php echo get_the_author_meta('user_email'); ?>" target="_blank"><?php echo get_the_author_meta('user_email'); ?></a></span>&nbsp;
					<?php endif; ?>
					<?php if ( get_the_author_meta('phonenumber') ): ?>
						<span class="author-phonenumber"><i class="fa fa-phone"></i> <?php echo nebula_tel_link(get_the_author_meta('phonenumber')); ?></span>
					<?php endif; ?>
				</span>
				<hr />
			</div>

			<p class="authorbio"><?php echo esc_html(the_author_meta('description')); ?></p>
		</div><!--/columns-->
	</div><!--/row-->
</div><!--/container-->

<div class="container fullcontentcon">
	<div class="row">
		<div class="eleven columns">
			<?php
				rewind_posts();
				get_template_part('loop', 'author');
				wp_pagenavi();
			?>
		</div><!--/columns-->

		<div class="four columns push_one">
			<?php get_sidebar(); ?>
		</div><!--/columns-->

	</div><!--/row-->
</div><!--/container-->

<?php get_footer(); ?>