<style>
	#eu-cookie-notification {position: fixed; bottom: 0; left: 0; width: 100%; padding: 10px 20px; text-align: center; background: #4a4d50; color: #fff; box-shadow: 0 4px 12px 4px rgba(0, 0, 0, 0.5); z-index: 99999;}
		#eu-cookie-notification p {margin: 0; height: 34px; line-height: 34px;}
		#eu-cookie-notification .btn.default:hover {background: #aaa;}
			#eu-cookie-notification .btn > a:hover {color: #444;}
</style>


<script>
	jQuery(document).on('ready', function(){

		if ( !nebula.user.sessions.last && nebula.user.sessions.first === nebula.user.sessions.current || 1==1 ){ //If first pageview of first visit enable functionality (remove || 1==1 for live site)
			hasAcceptedEUCookies = false;

			jQuery('#eu-cookie-notification .btn a').on('click tap touch', function(){
				nebulaAcceptEUCookies();
				return false;
			});

			jQuery(window).on('scroll', function(){
				if ( jQuery(document).scrollTop() > 100 && !hasAcceptedEUCookies ){ //If scrolled futher than 100px
					nebulaAcceptEUCookies();
				}
			});

			function nebulaAcceptEUCookies(){
				if ( !hasAcceptedEUCookies ){
					jQuery('#eu-cookie-notification').animate({
					    bottom: jQuery('#eu-cookie-notification').outerHeight()*-1
					}, 250, 'easeInQuad', function(){
					    jQuery('#eu-cookie-notification').remove();
					});

					hasAcceptedEUCookies = true;
				} else {
					return false;
				}
			}
		}

	});
</script>


<?php if ( !$nebula['user']['sessions']['last'] && $nebula['user']['sessions']['first'] == $nebula['user']['sessions']['current'] || 1==1 ): //Only show if first pageview of first visit (remove || 1==1 for live site) ?>
	<div id="eu-cookie-notification" class="container">
		<div class="row">
			<div class="twelve columns">
				<p><strong>This site uses cookies.</strong> By continuing to use this website you agree to the terms of service.</p>
			</div><!--/columns-->
			<div class="four columns">
				<div class="btn medium default">
					<a href="#">Continue</a>
				</div>
			</div><!--/columns-->
		</div><!--/row-->
	</div>
<?php endif; ?>