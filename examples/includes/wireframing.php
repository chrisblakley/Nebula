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


<!-- Form -->
<div class="row">
	<div class="sixteen columns">
		<br/><hr/>
		<h1>Form</h1>
		<p>The <?php echo do_shortcode('[code]fpo_form()[/code]'); ?> function will create a placeholder form. The class "nebula-fpo-form" added to the element.</p>
		
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
		<p>The <?php echo do_shortcode('[code]fpo_slider()[/code]'); ?> function will create a placeholder slider. The class "nebula-fpo-slider" added to the element.</p>
		
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
		<p>The <?php echo do_shortcode('[code]fpo_video()[/code]'); ?> function will create a placeholder video. The class "nebula-fpo-video" added to the element.</p>
		
		<h2>Usage</h2>
		<?php echo do_shortcode('[pre lang=php]<?php fpo_video( $id, $service ); ?>[/pre]'); ?>
		
		<h2>Parameters</h2>
		<p>
			<strong>$id</strong> (integer or array of strings) (optional) Either an integer of the amount of sample slides (random <a href="https://unsplash.com/" target="_blank">Unsplash</a> images), or an array of specific image URLs. Default: <em>3</em><br/>
			<strong>$service</strong> (object) (optional) Slider options *NOT IMPLEMENTED YET*.<br/>
		</p>
		
		<h2>Example</h2>
		<div class="row">
			<div class="sixteen columns">
				<?php fpo_video(); ?>
			</div>
		</div>
	</div><!--/columns-->
</div><!--/row-->