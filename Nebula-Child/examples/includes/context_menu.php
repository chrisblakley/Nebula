<script>
	function linkTo(url){
	    if ( url.indexOf('//') > -1 ){
		    var encloc = encodeURI(window.location);
		    var enctitle = encodeURI(document.title);

			if ( network == 'facebook' ){
				theURL = 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle;
			} else if ( network == 'twitter' ){
				theURL = 'https://twitter.com/intent/tweet?text=' + enctitle + '&url=' + encloc;
			}
		} else {
			theURL = url;
		}

		if ( typeof theURL !== 'undefined' ){
			window.location = theURL;
		}
	}
</script>


<div class="row" contextmenu="nebulamenu" style="border: 1px solid red; padding: 20px;">
	<div class="col-md-12">
		<h2>Context Menu Example</h2>
		<p><strong>Right-click in this section to see extra options added to the contextual menu.</strong></p>

		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sed libero quam. Praesent dignissim pretium massa. Integer tempus a augue vel porttitor. Nunc in consequat ligula. Sed semper aliquam arcu, at sodales arcu venenatis et. Pellentesque vestibulum dui velit, quis rhoncus felis aliquam a. Sed velit tortor, euismod eget egestas a, consequat sed ipsum. Sed vestibulum fringilla justo, at dapibus dui suscipit ut. Proin mi diam, consequat ut lacinia eu, semper vel risus. Suspendisse sodales iaculis est, vitae ornare nisi imperdiet eu. Etiam vel dolor porta nisi viverra maximus vitae sed dui. In accumsan elit sed mi fringilla mollis. Integer feugiat nisi at sem sodales, eu vestibulum felis ornare.</p>

		<p>Cras facilisis commodo diam, non aliquam lorem. In hac habitasse platea dictumst. Etiam molestie tellus purus, accumsan finibus ante suscipit nec. Nunc in elementum risus. Nullam vitae tempor velit. Fusce quis suscipit ligula. Etiam at bibendum odio. Integer ornare neque magna, at rutrum eros feugiat vitae. Morbi mollis elit erat, sit amet tempor lorem aliquam eget. Nunc suscipit dignissim lacus at condimentum. Quisque eleifend, arcu nec fringilla venenatis, enim nunc maximus ante, non volutpat justo turpis eu diam. In quis efficitur lectus.</p>
	</div><!--/col-->
</div><!--/row-->


<menu type="context" id="nebulamenu">
	<menuitem label="Nebula Context Menu" icon="<?php echo get_template_directory_uri(); ?>/images/meta/favicon-16x16.png" onclick="linkTo('https://gearside.com/nebula/documentation/examples/context-menu/')"></menuitem>
	<menuitem label="Refresh" onclick="window.location.reload();"></menuitem>

	<menu label="Share">
		<menuitem label="Twitter" onclick="linkTo('twitter');"></menuitem>
		<menuitem label="Facebook" onclick="linkTo('facebook');"></menuitem>
	</menu>
</menu>