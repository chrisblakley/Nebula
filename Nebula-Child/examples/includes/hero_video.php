<style>
	#herovideo {margin-top: -30px;} /* Do not copy over this line - it is for this example page only! */


	#herovideo {position: relative; overflow: hidden; min-height: 492px;}

	#video-background,
	.mobile-video-background {position: absolute; bottom: 0px; right: 0px; top: 0px; left:0px; margin: auto; width: auto; height: auto; min-width: 100%; min-height: 100%; z-index: 2; overflow: hidden;}
	.mobile-video-background {display: none !important;}

	@media only screen and (max-width: 767px) {
	    #video-background {display: none !important;}
	    .mobile-video-background {display: block !important;}
	}

	.heroshading {position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3;
		/* Various example backgrounds: */
		background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.2));
		background: rgba(0, 0, 0, 0.6) url('https://www.transparenttextures.com/patterns/subtlenet.png') repeat; /* Texture by https://www.transparenttextures.com/ - Please save image locally before deploying! */
	}

	#herocontent {position: relative; padding-top: 100px; z-index: 100;}
		#herocontent h2 {color: #fff; font-weight: 700;}
		#herocontent p {color: #fff;}

	#fullherovideocon {position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 1;}
		#fullherovideo {position: relative; height: 100%;}
			#fullherovideo iframe {width: 100%; min-height: 492px;}
</style>


<script>
	jQuery(document).ready(function() {
		jQuery('.herovideobtn').on('click tap touch', function(){
			jQuery('#video-background').fadeOut(1000);
			jQuery('#herocontent').fadeOut(1000);
			jQuery('.heroshading').fadeOut(1000);
			player[0].api("play");

			nebulaConversion('hero_video', 'played');
			return false;
		});
	});
</script>


<div class="container">
	<div id="herovideo">
	    <?php if ( nebula_is_desktop() ): ?>
		    <video id="video-background" width="872" height="492" autobuffer autoplay muted loop>
		        <source src="https://clips.vorwaerts-gmbh.de/big_buck_bunny.webm" type="video/webm" />
		        <source src="https://clips.vorwaerts-gmbh.de/big_buck_bunny.mp4" type="video/mp4" />
		        <source src="https://clips.vorwaerts-gmbh.de/big_buck_bunny.ogv" type="video/ogg" />
		    </video>
		<?php endif; ?>
		<img class="mobile-video-background" src="https://placehold.it/872x492"/>

		<div class="heroshading"></div>

		<div id="herocontent" class="row valign">
			<div class="eight columns">
				<h2>Title Text</h2>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc dapibus ante eget quam ullamcorper congue vel at diam. Curabitur sagittis turpis in nisi tincidunt, at pharetra magna dictum.</p>
				<div class="large primary btn herovideobtn">
					<a href="#">Watch now!</a>
				</div>
			</div><!--/column-->
		</div><!--/row-->

		<div id="fullherovideocon">
			<div id="fullherovideo" class="row">
				<div class="col-md-12">
					<?php vimeo_meta('1084537'); ?>
					<iframe id="<?php echo $GLOBALS['vimeo_meta']['safetitle']; ?>" class="vimeoplayer" src="https://player.vimeo.com/video/<?php echo $GLOBALS['vimeo_meta']['id']; ?>?api=1&player_id=<?php echo $GLOBALS['vimeo_meta']['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div><!--/col-->
			</div><!--/row-->
		</div>
	</div><!--/herovideo -->
</div><!--/container-->

<div class="nebulashadow bulging"></div><br />