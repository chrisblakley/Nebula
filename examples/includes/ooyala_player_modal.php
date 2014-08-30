<style>
.simple_overlay {display: none;}
#lgVid1 {width: 100%; /* max-width: 646px; */}
    .closemod {position: absolute; top: -33px; right: 0; display: block; font-size: 24px; line-height: 33px; width: 40px; height: 33px; text-align: center; color: #fff; background: #003395; z-index: 10001;}
    	.closemod:hover,
    	.closemod.hover {color: #fff;}
	    .lte-ie8 .closemod {top: -48px;}
	    .ie7 .closemod {top: -33px;}
	    .ie7x {display: none; font-size: 21px; font-weight: bold; margin-top: 3px;}
	    .ie7 .ie7x {display: block;}
	    .ie7 .closemod i {display: none;}
	        .closemod:hover {cursor: pointer; background: #001c54;}
	        .closemod i {margin-top: 3px; color: #fff;}
#simplemodal-container {position: fixed; top: 120px !important; max-width: 640px; height: auto !important; left: 50% !important; margin-left: -320px; zoom: 1; outline: 15px solid #000; outline: 15px solid rgba(0,0,0,0.4); z-index: 1002;}
	.firefox #simplemodal-container {width: 670px; margin-left: -335px; outline: 0; border: 15px solid #000; border: 15px solid rgba(0,0,0,0.4);}
	.ie7 #simplemodal-container {width: 640px; margin-left: -335px; outline: 0; border: 15px solid #000; border: 15px solid rgba(0,0,0,0.4);}
	#simplemodal-overlay {background-color: #000;}
#playerwrapper {width: 100%; height: 360px; position: relative;}
#playerwrapper .video {position: static;}
.lte-ie8 .video {height: 100% !important; padding-bottom: 0 !important;}
</style>

<script>
jQuery(document).ready(function() {	
	ooyalaVideo();

    function ooyalaVideo() {
		jQuery(document).on('click', '.ooyalalink', function(e) {
			if ( typeof OO !== 'undefined' ) {
				var thisID = jQuery(this).attr('id');
				var thisOOcode = jQuery(this).data('ooyala');
				var thismovieplayer = OO.Player.create('playerwrapper', thisOOcode, {
					height:'100%',
	      			width:'100%'
				});

				jQuery('#lgVid1').modal({
					autoResize: true,
					overlayClose: false
				});
	
				jQuery(document).on('click', '.simplemodal-close', function() {
					thismovieplayer.destroy();
				});
			}
			e.preventDefault();
		});
	}
});
</script>
						
<?php
	/* Known Branding IDs and Asset IDs:
		Branding ID: MzZiMzc1ZDUzZGVlYmMxNzA3Y2MzNjBk
		
		Embed Code: 45cmZqNDrKn7TvtpfGa9k9fQSeyK4VaI (Avatar)
		Embed Code: UxaXI5MzruPkO9medlrVQ9sZbgpqgMxr (Dark Knight Trailer)
		Embed Code: llMDQ6rMWxVWbvdxs2yduVEtSrNCJUk1 (Tron Legacy Trailer)
		Embed Code: s0MmVvMTrSlB1ZLzaWXnKZaa42Ib5rJV (Ooyala Careers)
		Embed Code: 8wNTqa-6MkpEB1c7fNGOpoSJytLptmm9 (Say Ooyala 2)
		Embed Code: 44azdwNDpSWUvfd8F30d55tXY0YH9njH (Ooyala ESP)
	*/
?>
												
<script src='http://player.ooyala.com/v3/MzZiMzc1ZDUzZGVlYmMxNzA3Y2MzNjBk'></script> <!-- Branding ID here -->
<script src="//cdnjs.cloudflare.com/ajax/libs/simplemodal/1.4.4/jquery.simplemodal.min.js" defer></script>
	
<div id="lgVid1" class="simple_overlay">
	<a class="closemod simplemodal-close">X</a>
	<div id='playerwrapper'></div>
</div>

<div class="medium primary btn">
	<a id="ooyalalink1" class="ooyalalink" href="#" rel="#lgVid1" data-ooyala="44azdwNDpSWUvfd8F30d55tXY0YH9njH">Play Video</a> <!-- Embed Code (Asset ID) here -->
</div>