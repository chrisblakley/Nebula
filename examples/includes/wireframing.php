<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/wireframing.css" />

<!-- FPO -->
<div class="row">
	<div class="sixteen columns">
		<hr/>
		<h1>FPO</h1>
		<p>The <?php echo do_shortcode('[code]fpo()[/code]'); ?> function will create a placeholder box. The class "nebula-fpo" and a websafe version of the $title parameter are added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo($title, $description, $icon, $width, $height, $bg, $color, $styles, $classes); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$title</strong> (string) (required) The text that appears in the center of the placeholder box. Default: <em>None</em><br/>
			<strong>$description</strong> (string) (optional) The description of what this placeholder box represents. It is recommended to use double quotes around this parameter (but not required). Default: <em>None</em><br/>
			<strong>$icon</strong> (string) (optional) <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">Font Awesome</a> icon to appear next to the title. Can use "fa-" syntax or just the icon name. Default: <em>None</em><br/>
			<strong>$bg</strong> (string) (optional) The background color (or CSS style) of the box. Default: <em>#ddd</em><br/>
			<strong>$color</strong> (boolean) (optional) Use dark (0) or light(1) text in the box. Default: <em>0</em><br/>
			<strong>$width</strong> (string) (optional) The width of the placeholder box. Default: <em>100%</em><br/>
			<strong>$height</strong> (string) (optional) The height of the placeholder box. Default: <em>250px</em><br/>
			<strong>$styles</strong> (string) (optional) Additional styles to add to the element. Default: <em>None</em><br/>
			<strong>$classes</strong> (string) (optional) Additional classes to add to the element. Default: <em>None</em><br/>
		</p>

		<h2>Examples</h2>
		<div class="row">
			<div class="eight columns">
				<?php fpo('Test Element', "This element uses only two parameters of the FPO function."); ?>
			</div>
			<div class="eight columns">
				<?php fpo('Advanced syntax', "This element utilizes almost all of the FPO function's parameters.", 'home', '100%', '250px', 'linear-gradient(to bottom, #0098d7, #0073a3)', 1, 'border: 1px solid #025678;', ''); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->


<!-- Image -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Image</h1>
		<p>Placeholder images can be created using the <?php echo do_shortcode('[code]fpo_image()[/code]'); ?> function. The class "nebula-fpo-image" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo($title, $description, $icon, $width, $height, $bg, $color, $styles, $classes); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$type</strong> (string) (optional) The type of placeholder image to use. Options include "none". Default: <em>"none"</em><br/>
			<strong>$width</strong> (string) (optional) The width of the placeholder image. Default: <em>100%</em><br/>
			<strong>$height</strong> (string) (optional) The height of the placeholder image. Default: <em>250px</em><br/>
			<strong>$styles</strong> (string) (optional) Additional styles to add to the element. Default: <em>None</em><br/>
			<strong>$classes</strong> (string) (optional) Additional classes to add to the element. Default: <em>None</em><br/>
		</p>

		<h2>Examples</h2>
		<div class="row">
			<div class="eight columns">
				<?php fpo_image(); ?>
			</div>
			<div class="eight columns">
				<?php fpo_image('unsplash'); ?>
			</div>
		</div>
	</div>
</div><!--/row-->


<!-- Form -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Form</h1>
		<p>The <?php echo do_shortcode('[code]fpo_form()[/code]'); ?> function will create a placeholder form. The class "nebula-fpo-form" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_form( $fields, $submit, $action ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$fields</strong> (array of strings) (optional) An array of field names to be used in the mockup form. Default: <em>array('Name', 'Email', 'Message')</em><br/>
			<strong>$submit</strong> (string) (optional) The text to use on the submit button. Default: <em>Send</em><br/>
			<strong>$action</strong> (string) (optional) A URL to redirect to on submission. Default: <em>(AJAX Mailer)</em><br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_form(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->


<!-- Slider -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Slider</h1>
		<p>The <?php echo do_shortcode('[code]fpo_slider()[/code]'); ?> function will create a placeholder slider. The class "nebula-fpo-slider" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_slider( $slides, $options ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$slides</strong> (integer or array of strings) (optional) Either an integer of the amount of sample slides (random <a href="https://unsplash.com/" target="_blank">Unsplash</a> images), or an array of specific image URLs. Default: <em>3</em><br/>
			<strong>$options</strong> (object) (optional) Slider options *NOT IMPLEMENTED YET*.<br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_slider(5); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->


<!-- Video -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Video</h1>
		<p>The <?php echo do_shortcode('[code]fpo_video()[/code]'); ?> function will create a placeholder video. The class "nebula-fpo-video" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_video( $id, $service ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$id</strong> (string) (optional) The video ID. Default: <em>jtip7Gdcf0Q</em><br/>
			<strong>$service</strong> (string) (optional) Which video service the ID is associated with. Default: <em>youtube</em><br/>
			<strong>$width</strong> (string) (optional) The width of the video iframe. Default: <em>560</em><br/>
			<strong>$height</strong> (string) (optional) The height of the video iframe. Default: <em>315</em><br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_video(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->


<!-- Breadcrumbs -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Breadcrumbs</h1>
		<p>The <?php echo do_shortcode('[code]fpo_breadcrumbs()[/code]'); ?> function will create a placeholder breadcrumb. The class "nebula-fpo-breadcrumb" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_breadcrumbs( $crumbs ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$crumbs</strong> (array) (optional) An array of hard-coded crumb names. Default: <em>None</em><br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_component('Breadcrumbs'); ?>
				<?php fpo_breadcrumbs(array('Parent', 'Child', 'Current Page')); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->



<!-- Menu -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Menu</h1>
		<p>The <?php echo do_shortcode('[code]fpo_menu()[/code]'); ?> function will create a placeholder menu. The class "nebula-fpo-menu" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_menu( $name, $items ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$name</strong> (string) (optional) The name of a specific WordPress menu to pull from. Default: <em>None</em><br/>
			<strong>$items</strong> (array) (optional) Hard-coded strings for placeholder links. Default: <em>None</em><br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_component('Menu'); ?>
				<?php fpo_menu(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->




<!-- Social Links -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Social (Links)</h1>
		<p>The <?php echo do_shortcode('[code]fpo_social_links()[/code]'); ?> function will create placeholder social media links. Please note that these are meant to be for links to social media profiles/pages (for like/share button placeholders, use [code]fpo_social_share()[/code]). The class "nebula-fpo-social-links" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_social_links( $accounts ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$accounts</strong> (array) (optional) An array of which networks to use. These include "Facebook", "Twitter", "Google+", "LinkedIn", "Youtube", and "Instagram". They are not case-sensitive, and have aliases for various spellings/abbreviations. You can also add custom icons from Font Awesome (use the "fa-" syntax), or use an absolute URL to an image (it will be scaled down). If none are declared, they all appear. Default: <em>None</em><br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_component('Social Media Accounts'); ?>
				<?php fpo_social_links(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->




<!-- Social Share -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Social (Share)</h1>
		<p>The <?php echo do_shortcode('[code]fpo_social_share()[/code]'); ?> function will create placeholder social media sharing buttons. Please note that these are meant to be for like/share buttons (for social media link placeholders, use [code]fpo_social_links()[/code]). The class "nebula-fpo-social-links" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_social_share( $accounts ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$accounts</strong> (array) (optional) An array of which networks to use. These include "Facebook", "Twitter", "Google+", "LinkedIn", "Youtube", and "Email". They are not case-sensitive, and have aliases for various spellings/abbreviations. You can also add custom icons from Font Awesome (use the "fa-" syntax), or use an absolute URL to an image (it will be scaled down). If none are declared, they all appear. Default: <em>None</em><br/>
		</p>

		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_component('Social Media Sharing'); ?>
				<?php fpo_social_share(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->







<br/><br/><br/><br/><br/><br/>