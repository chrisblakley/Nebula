<style>
	.animatecon {text-align: center; padding-top: 100px;}
		#animatethis {width: 80%; max-width: 500px; display: inline-block;}

	.animationformcon {margin-bottom: 100px;}
		.animationselectcon {text-align: right;}

		#playanimation, #resetanimation {transition: opacity 0.25s;}
			#playanimation.inactive, #resetanimation.inactive {opacity: 0.4; pointer-events: none;}
			#playanimation.animate, #resetanimation.animate {opacity: 1;}

		@media only screen and (max-width: 767px) {
			.animationselectcon,
			.animationresetcon {text-align: center;}
		}
</style>


<script>
	jQuery(document).on('ready', function(){

		jQuery('#animationselect').on('change', function(){ //Enable/Disable the buttons to animate or reset
			if ( jQuery('#animationselect').val() != '' ){
				jQuery('#playanimation').removeClass('inactive').addClass('animate');
				jQuery('#resetanimation').removeClass('inactive').addClass('animate');
			} else {
				jQuery('#playanimation').removeClass('animate').addClass('inactive');
				jQuery('#resetanimation').removeClass('animate').addClass('inactive');
			}
		});

		jQuery('#playanimation a').on('click tap touch', function(){
			if ( jQuery('#animationselect').val() != '' ){
				jQuery('#animatethis').removeClass();
				reflow(jQuery('#animatethis')); //Trigger a reflow so that animation can be repeated.
				jQuery('#animatethis').addClass(jQuery('#animationselect').val() + ' animate');
			} else {
				jQuery('#playanimation').addClass('nebula-shake active');
			}
			return false;
		});

		jQuery('#resetanimation a').on('click tap touch', function(){
			jQuery('#animatethis').removeClass();
			//jQuery('#animationselect').prop('selectedIndex', 0);
			//jQuery('#playanimation').removeClass('animate').addClass('inactive');
			//jQuery('#resetanimation').removeClass('animate').addClass('inactive');
			return false;
		});

	});
</script>


<div class="row">
	<div class="sixteen columns animatecon">
		<img id="animatethis" src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" />
	</div><!--/columns-->
</div><!--/row-->

<div class="row animationformcon">
	<div class="nine columns animationselectcon">
		<div class="picker">
			<select id="animationselect">
				<option value="" disabled selected>Select an animation...</option>
				<option value="" disabled>Repeating:</option>
					<option value="nebula-spin">Spin</option>
					<option value="nebula-fade">Fade</option>
					<option value="nebula-zoom">Zoom</option>
					<option value="nebula-wave-x">Wave X</option>
					<option value="nebula-wave-y">Wave Y</option>

				<option value="" disabled>Non-Repeating:</option>
					<option value="nebula-fade-out">Fade Out</option>
					<option value="nebula-fade-out-up">Fade Out Up</option>
					<option value="nebula-fade-out-down">Fade Out Down</option>
					<option value="nebula-fade-out-left">Fade Out Left</option>
					<option value="nebula-fade-out-right">Fade Out Right</option>

					<option value="nebula-fade-in">Fade In</option>
					<option value="nebula-fade-in-up">Fade In Up</option>
					<option value="nebula-fade-in-down">Fade In Down</option>
					<option value="nebula-fade-in-left">Fade In Left</option>
					<option value="nebula-fade-in-right">Fade In Right</option>

					<option value="nebula-zoom-out">Zoom Out</option>
					<option value="nebula-zoom-in">Zoom In</option>

					<option value="nebula-stretch-out">Stretch Out</option>
					<option value="nebula-stretch-in">Stretch In</option>

					<option value="nebula-flip-in-x">Flip In X</option>
					<option value="nebula-flip-in-y">Flip In Y</option>
					<option value="nebula-flip-out-x">Flip Out X</option>
					<option value="nebula-flip-out-y">Flip Out Y</option>

					<option value="nebula-push active">Push</option>
					<option value="nebula-shake active">Shake</option>
					<option value="nebula-nod active">Nod</option>
			</select>
		</div>
	</div><!--/columns-->
	<div class="seven columns animationresetcon">
		<div id="playanimation" class="btn medium primary inactive">
			<a href="#">Animate</a>
		</div>
		<div id="resetanimation" class="btn medium info inactive">
			<a href="#">Reset</a>
		</div>
	</div><!--/columns-->
</div><!--/row-->