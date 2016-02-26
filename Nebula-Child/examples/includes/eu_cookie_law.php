<style>
	#eu-cookie-notification {position: fixed; bottom: 0; left: 0; width: 100%; padding: 10px 20px; text-align: left; background: #0098d7; color: #fff; box-shadow: 0 4px 12px 4px rgba(0, 0, 0, 0.5); z-index: 99999;}
		#eu-cookie-notification h4 {color: #fff;}
		#eu-cookie-notification p {margin: 0; font-size: 14px; line-height: 18px;}
		#eu-cookie-notification .btncon {text-align: right;}

	@media only screen and (max-width: 767px){
		#eu-cookie-notification h4 {text-align: center;}
		#eu-cookie-notification p {text-align: center; margin-bottom: 12px;}
		#eu-cookie-notification .btncon {text-align: center;}
	}
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
				<h4><strong>This site uses cookies.</strong></h4>
				<p>By continuing to use this website you agree to the terms of service.</p>
			</div><!--/columns-->
			<div class="four columns btncon">
				<div class="btn medium info">
					<a href="#">Continue</a>
				</div>
			</div><!--/columns-->
		</div><!--/row-->
	</div>
<?php endif; ?>