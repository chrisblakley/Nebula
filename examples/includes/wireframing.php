<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/wireframing.css" />

<?php if ( nebula_options_conditional('nebula_wireframing') == 'disabled' || nebula_options_conditional('nebula_wireframing') == 'default' ) : ?>
	<p style="background: maroon; color: #fff; padding: 10px 15px;"><strong>Warning:</strong> Wireframing is currently <strong>disabled</strong> in this instance of WordPress. JavaScript functions will not work unless it is re-enabled on the Nebula Options page.</p>
<?php endif; ?>

<div class="row" style="background: #0098d7; color: #fff; padding: 10px 15px;">
	<div class="sixteen columns">
		<h2 style="color: #fff;">Components</h2>
		<p>Components are the containers of either a single element, a group of elements, or even markup and elements. Elements can be custom Nebula wireframing functions, custom Nebula functions, or custom code of your own.</p>
	</div><!--/columns-->
</div><!--/row-->

<br /><br />
<hr />
<br /><br />

<!-- Component -->
<div class="row">
	<div class="sixteen columns">
		<h2><strong>Component</strong></h2>
		<p>The <?php echo do_shortcode('[code]fpo_component()[/code]'); ?> function will create the named component bar.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_component( $name ); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$name</strong> (string) (required) The name of the component. Default: <em>Component</em><br />
			<strong>$icon</strong> (string) (optional) A Font Awesome icon to use next to the component name. Can be an empty string for no icon. Default: <em>fa-cube</em><br />
		</p>

		<h2>Example</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_component("Breadcrumbs"); ?>[/pre]'); ?>
		<div class="row">
			<div class="sixteen columns">
				<?php //fpo_component('Breadcrumbs'); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<br /><br />
<hr />
<br /><br />

<!-- Component Start/End -->
<div class="row">
	<div class="sixteen columns">
		<h2><strong>Component Start/End</strong></h2>
		<p>The <?php echo do_shortcode('[code]fpo_component_start()[/code]'); ?> and <?php echo do_shortcode('[code]fpo_component_end()[/code]'); ?> function will create the named component bar and wrap the elements in a contained div.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_component_start( $name ); ?>[elements_here]<?php fpo_component_end(); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$name</strong> (string) (required) The name of the component. Default: <em>Component</em><br />
			<strong>$icon</strong> (string) (optional) A Font Awesome icon to use next to the component name. Can be an empty string for no icon. Default: <em>fa-cube</em><br />
		</p>

		<h2>Example</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_component_start("Breadcrumbs"); ?>
<p>Breadcrumbs would go here.</p>
<?php fpo_component_end(); ?>[/pre]'); ?>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_component_start('Breadcrumbs'); ?>
				<p>Breadcrumbs would go here.</p>
				<?php fpo_component_end(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<br /><br />
<hr />
<br />

<div class="row" style="background: #0098d7; color: #fff; padding: 10px 15px; margin-top: 50px;">
	<div class="sixteen columns">
		<h2 style="color: #fff;">Elements</h2>
		<p>Elements are what makes components unique to the project. FPO Elements (as seen below) are made specifically to make wireframing easy and consistent. Markup and other code can be used alongside elements for an even more unique mockup. During development, elements will be replaced with finalized code. Upon launch, there should be <strong>no</strong> FPO functions anywhere in the project.</p>
	</div><!--/columns-->
</div><!--/row-->

<br /><br />

<!-- FPO -->
<div class="row">
	<div class="sixteen columns">
		<h2><strong>FPO</strong></h2>
		<p>The <?php echo do_shortcode('[code]fpo()[/code]'); ?> function will create a placeholder box. The class "nebula-fpo" and a websafe version of the $title parameter are added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo($title, $description, $width, $height, $bg, $icon, $styles, $classes); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$title</strong> (string) (required) The text that appears in the center of the placeholder box. Default: <em>None</em><br />
			<strong>$description</strong> (string) (optional) The description of what this placeholder box represents. It is recommended to use double quotes around this parameter (but not required). Default: <em>None</em><br />
			<strong>$width</strong> (string) (optional) The width of the placeholder box. Default: <em>100%</em><br />
			<strong>$height</strong> (string) (optional) The height of the placeholder box. Default: <em>250px</em><br />
			<strong>$bg</strong> (string) (optional) The background color (or CSS style) of the box. Use "placeholder" to denote a placeholder background image. Default: <em>#ddd</em><br />
			<strong>$icon</strong> (string) (optional) <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">Font Awesome</a> icon to appear next to the title. Can use "fa-" syntax or just the icon name. Default: <em>None</em><br />
			<strong>$styles</strong> (string) (optional) Additional styles to add to the element. Default: <em>None</em><br />
			<strong>$classes</strong> (string) (optional) Additional classes to add to the element. Default: <em>None</em><br />
		</p>

		<h2>Examples</h2>
		<div class="row">
			<div class="eight columns">
				<?php fpo('Test Element', "This element uses only two parameters of the FPO function."); ?>
			</div>
			<div class="eight columns">
				<?php fpo('Advanced syntax', "This element utilizes almost all of the FPO function's parameters.", '100%', '250px', 'linear-gradient(to bottom, #0098d7, #0073a3)', 'home', 'border: 1px solid #025678;', ''); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<br /><br />
<hr />
<br /><br />

<!-- Image -->
<div class="row">
	<div class="sixteen columns">
		<h2><strong>Image</strong></h2>
		<p>Placeholder images can be created using the <?php echo do_shortcode('[code]fpo_image()[/code]'); ?> function. The class "nebula-fpo-image" is added to the element.</p>

		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_image($width, $height, $type, $color, $styles, $classes); ?>[/pre]'); ?>

		<h2>Parameters</h2>
		<p>
			<strong>$width</strong> (string) (optional) The width of the placeholder image. Default: <em>100%</em><br />
			<strong>$height</strong> (string) (optional) The height of the placeholder image. Default: <em>250px</em><br />
			<strong>$type</strong> (string) (optional) The type of placeholder image to use. Options include "none", "photo". Default: <em>"none"</em><br />
			<strong>$color</strong> (string) (optional) The color of the placeholder image strokes. Default: <em>"#ddd"</em><br />
			<strong>$styles</strong> (string) (optional) Additional styles to add to the element. Default: <em>None</em><br />
			<strong>$classes</strong> (string) (optional) Additional classes to add to the element. Default: <em>None</em><br />
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

<br /><br />
<hr />
<br /><br />






<br /><br /><br /><br /><br /><br />