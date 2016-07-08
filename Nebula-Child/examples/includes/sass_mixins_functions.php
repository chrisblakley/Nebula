<div class="row">
	<div class="col-md-12">

		<br /><br /><hr /><br />

		<h2>Mixins</h2>
		<p>Mixins require the <?php echo do_shortcode('[code]@include[/code]'); ?> syntax to be used. This is shown in the example code snippets.</p>

		<br /><hr /><br /><br />

		<h3>Arrow</h3>
		<p>This mixin makes it easy to add triangular "arrows" to elements.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include arrow($side, $align, $size, $color, $borderColor, $borderSize);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$side</strong>&nbsp;(string) (optional) Which side of the element the triangle should be on. Default: <em>right</em><br />
			<strong>$align</strong>&nbsp;(string) (optional) The alignment of the arrow. Values can be "top", "bottom", "left", "right", "center". Default: <em>center</em><br />
			<strong>$size</strong>&nbsp;(string) (optional) The size of the triangular arrow. Default: <em>None</em><br />
			<strong>$color</strong>&nbsp;(string) (optional) The color of the arrow. Default: <em>None</em><br />
			<strong>$borderColor</strong>&nbsp;(string) (optional) The color of the arrow's border. Default: <em>None</em><br />
			<strong>$borderSize</strong>&nbsp;(string) (optional) The thickness of the arrow's border. Default: <em>None</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode("[pre lang=scss].element-solid {@include arrow('top','left', 10px, #4fade3);}[/pre]"); ?></p>
		<p><?php echo do_shortcode("[pre lang=scss].element-border {@include arrow('top','left', 10px, #eee, #4fade3, 6px);}[/pre]"); ?></p>

		<br /><hr /><br /><br />
		<h3>Background Color</h3>
		<p>This mixin allows for background colors to be added and automatically manages fallbacks (for browsers that do not support RGBA).</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include background-color($color, $opacity);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$color</strong>&nbsp;(string) (required) The desired color. Using an RGBA value would work, but would defeat the purpose of this mixin. Default: <em>None</em><br />
			<strong>$opacity</strong>&nbsp;(integer) (optional) The desired opacity of the background color. Default: <em>None</em>
		</p>

		<h4>Example</h4>
		<p><?php echo do_shortcode('[pre lang=scss].element {@include background-color(#333, 0.5);}[/pre]'); ?></p>


		<br /><hr /><br /><br />
		<h3>Clearfix</h3>
		<p>This adds an appropriate clearfix to an element using :before and :after pseudo-classes. This mixin has no parameters.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include clearfix();[/pre]'); ?></p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss].element {@include clearfix();}[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Keyframes</h3>
		<p>Description</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include keyframes($animation-name){ @content }[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$animation-name</strong>&nbsp;(string) (required) The reference name of the animation. Default: <em>None</em><br />
			<strong>@content</strong>&nbsp;(string) (required) The CSS attributes for each animation position. Default: <em>None</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss]@include keyframes(slide-down){
		0% {opacity: 1;}
		90% {opacity: 0;}
	}[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Linear Gradient</h3>
		<p>Simplifies the syntax for creating a linear gradient.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include linear-gradient($direction, $colors...);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$direction</strong>&nbsp;(string) (required) Direction of the gradient. These include (but are not limited to) "to right", "to bottom", "45deg". Default: <em>None</em><br />
			<strong>$colors...</strong>&nbsp;(string) (required) The color stops and positions. Default: <em>None</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss].element {@include linear-gradient(#31B7D7, #EDAC7D);}[/pre]'); ?></p>
		<p><?php echo do_shortcode('[pre lang=scss].element {@include linear-gradient(to right, #E47D7D 0%, red 50%, #4FB4E8 100%);}[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Link Colors</h3>
		<p>Easily manage colors for links. Colors can be passed as any mode (HTML, HEX, RGB, RGBA) or even other functions/variables.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include link-colors($normal, $hover, $active, $visited, $focus);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$normal</strong>&nbsp;(string) (required) The initial color of the link. Default: <em>None</em><br />
			<strong>$hover</strong>&nbsp;(string) (optional) The link color when hovered. Default: <em>None</em><br />
			<strong>$active</strong>&nbsp;(string) (optional) The link color when active. Default: <em>None</em><br />
			<strong>$visited</strong>&nbsp;(string) (optional) The link color after visited. Default: <em>None</em><br />
			<strong>$focus</strong>&nbsp;(string) (optional) The link color when focused. Default: <em>None</em>
		</p>

		<h4>Example</h4>
		<p><?php echo do_shortcode('[pre lang=scss].element a {@include link-colors(red, #032786, rgba(123, 234, 56, .78), darken(green, 20%), blue);}[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Media</h3>
		<p>This mixin is used for making media queries. Because the viewport sizes are named, media queries can now live in the same selector (instead of in a group at the bottom of the stylesheet). This also allows for DRY practices; changing the named size will update all media queries that use that name.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include media($name, $custom){ @content };[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$name</strong>&nbsp;(string) (required) The <strong>name</strong> of the viewport size. Names are defined in the _variables.scss file. To use a custom size, pass 'custom' to this parameter. Default: <em>None</em><br />
			<strong>$custom</strong>&nbsp;(string) (optional) Instead of passing a named size (or in addition to one), you can pass a full media string instead. To do so, the $name parameter should be 'custom'. Default: <em>None</em>
			<strong>@content</strong>&nbsp;(string) (required) The styles for this media query. Default: <em>None</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss].element {color: red;
		@include media(tablet){color: orange;};
		@include media(mobile){color: yellow;};
		@include media(custom, "(max-device-width: 320px)"){color: green;};
	}[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Modernizr</h3>
		<p>Apply styles to elements based on modernizr polyfill tests. This mixin allows for the selector to be written once (instead of twice) per test.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include modernizr($polyfill, $selector, $pass, $fail);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$polyfill</strong>&nbsp;(string) (required) The modernizr polyfill to check against. Default: <em>None</em><br />
			<strong>$selector</strong>&nbsp;(string) (required) The element selector of the element to modify. Default: <em>None</em><br />
			<strong>$pass</strong>&nbsp;(string) (required) Styles to apply if the polyfill test is passed (supported). Default: <em>None</em><br />
			<strong>$fail</strong>&nbsp;(string) (optional) Styles to apply if the polyfill fails (unsupported). Default: <em>None</em><br />
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss]@include modernizr("touch", ".element", "border: 1px solid green;", "border: 1px solid red;");[/pre]'); ?></p>
		<p><?php echo do_shortcode('[pre lang=scss]@include modernizr("rgba", ".element", "border: 1px solid green;");[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Prefix</h3>
		<p>Handles complicated prefixing so properties only need to be written once.</p>
		<p><?php echo do_shortcode('[pre lang=scss]@include prefix(($map), $vendors);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$map</strong>&nbsp;(string) (required) The CSS property to be prefixed. Default: <em>None</em><br />
			<strong>$vendors</strong>&nbsp;(string) (optional) URI of resource (or website). Default: <em>webkit moz ms o</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss].element {@include prefix((transition: all 0.25s), webkit ms);}[/pre]'); ?></p>
		<p><?php echo do_shortcode('[pre lang=scss].element {@include prefix((animation: animation-name 2s infinite linear));}[/pre]'); ?></p>

		<br /><br /><hr /><br />
		<h2>Functions</h2>
		<p>Functions do not need the <?php echo do_shortcode('[code]@include[/code]'); ?> to be called.</p>

		<br /><hr /><br /><br />

		<h3>Brand</h3>
		<p>This allows brand colors (like social media) to be used throughout the site without needing to remember them. Updating the brand map in _variables.scss will update the color across the entire site.</p>
		<p><?php echo do_shortcode('[pre lang=scss]brand($brand, $index);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$brand</strong>&nbsp;(string) (required) The name of the brand to use the color of. The brand list is defined in the _variables.scss partial. Default: <em>None</em><br />
			<strong>$index</strong>&nbsp;(string/integer) (required) Which color index of the brand to use. Can be a string ("primary", "secondary") or integer (1, 2). Default: <em>None</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss].example {color: brand(facebook);}[/pre]'); ?></p>
		<p><?php echo do_shortcode('[pre lang=scss].example {color: brand(flickr, secondary);}[/pre]'); ?></p>
		<p><?php echo do_shortcode('[pre lang=scss].example {color: brand(flickr, 2);}[/pre]'); ?></p>

		<br /><hr /><br /><br />
		<h3>Easing</h3>
		<p>This is a very easy way to call complex easings. Instead of needing to copy the position values, easings can be called by name (such as "easeIn" or "easeInOutQuart").</p>
		<p><?php echo do_shortcode('[pre lang=scss]easing($ease);[/pre]'); ?></p>

		<h4>Parameters</h4>
		<p>
			<strong>$ease</strong>&nbsp;(string) (required) The name of the easing to use. Easings are defined in the _mixin.scss partial, and custom easings may be defined in the _variables.scss partial! To use a custom easing, either define it by name, or pass "custom" for this parameter. Default: <em>None</em><br />
			<strong>$custom</strong>&nbsp;(string) (required) If "custom" is passed as the name, pass the position parameters here as a string. Default: <em>None</em>
		</p>

		<h4>Examples</h4>
		<p><?php echo do_shortcode('[pre lang=scss].example {@include prefix((transition: right 0.5s easing(easeOutBack)));}[/pre]'); ?></p>
		<p><?php echo do_shortcode('[pre lang=scss].example {@include prefix((transition: right 0.5s easing(custom, "0.190, 1.000, 0.220, 1.000")));}[/pre]'); ?></p>

		<hr />
	</div><!--/col-->
</div><!--/row-->