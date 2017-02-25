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

<style>
	/* Responsive Ooyala Player */
	.ooyalacon {position: relative; width: 100%; padding: 56.25% 0 0 0;} /* 56.25% = 16:9 ratio */
		.ooyalacon .innerWrapper {position: absolute !important; top: 0; left: 0; width: 100%; height: 100%;}
			.ooyalacon .video {position: absolute; top: 0; padding-bottom: 0 !important;}
</style>

<script src="http://player.ooyala.com/v3/MzZiMzc1ZDUzZGVlYmMxNzA3Y2MzNjBk?platform=html5"></script> <!-- Branding ID here -->

<script>
	jQuery(document).ready(function() {
		if ( typeof OO !== 'undefined' ) {
			var thismovieplayer = OO.Player.create('playerwrapper', jQuery('#playerwrapper').data('ooyala'), {
				height:'100%',
				width:'100%'
			});

			jQuery('#videomodal').on('hidden.bs.modal', function() {
				thismovieplayer.destroy();
			});
		}
	});
</script>

<div class="modal fade" id="videomodal" tabindex="-1" role="dialog" aria-labelledby="videomodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<div id="playerwrapper" data-ooyala="44azdwNDpSWUvfd8F30d55tXY0YH9njH"></div><!-- Update asset ID here -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<a class="playcon" href="#" data-toggle="modal" data-target="#videomodal">Play Video</a>