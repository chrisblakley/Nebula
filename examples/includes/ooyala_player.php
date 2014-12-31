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

<?php //Gumby Modal Implementation Here... Currently getting a 403 Forbidden error from Ooyala. Might be because of the Branding ID? ?>
<script>
	jQuery(document).ready(function() {
		if ( typeof OO !== 'undefined' ) {
			var thismovieplayer = OO.Player.create('playerwrapper', jQuery('#playerwrapper').data('ooyala'), {
				height:'100%',
				width:'100%'
			});

			jQuery(document).on('click', '.simplemodal-close', function(){ //@TODO "Nebula" 0: Need gumby modal close event here.
				thismovieplayer.destroy();
			});
		}
	});
</script>

<div class="modal" id="modal1">
	<div class="content">
		<a class="close switch" gumby-trigger="|#modal1"><i class="icon-cancel" /></i></a>
		<div class="row">
			<div class="sixteen columns centered text-center">
				<div id="playerwrapper" data-ooyala="44azdwNDpSWUvfd8F30d55tXY0YH9njH"></div> <!-- Embed Code (Asset ID) here -->
			</div>
		</div>
	</div>
</div>

<p class="btn primary medium">
	<a href="#" class="switch" gumby-trigger="#modal1">Play Video</a>
</p>






<?php //Bootstrap Modal Implementation Here (for reference only)
	if ( 1==2 ) : //Falsed because Nebula does not use Bootstrap.
?>
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
<?php endif; ?>