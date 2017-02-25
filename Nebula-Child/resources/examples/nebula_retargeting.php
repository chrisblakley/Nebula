<script>
	jQuery(document).on('ready', function(){
		hoverFlag = false;
		jQuery('#testbutton').hover(function(){
			console.log('hovered over button');
			if ( !hoverFlag ){
				console.log('hover flag is false');
				nv('send', {'nebula_retargeting_example': 'hovered'}, function(){
					hoverFlag = true;
				});
			}
		});

		jQuery('#testbutton, #hoveredbutton').on('click tap touch', function(){
			nv('send', {'nebula_retargeting_example': 'clicked'}, function(){
				document.location.reload(true);
			});

			return false;
		});

		jQuery('#testreset').on('click tap touch', function(){
			nv('remove', 'nebula_retargeting_example', function(){
				document.location.reload(true);
			});

			return false;
		});

	});
</script>

<div class="row">
	<div class="col-md-12">
		<div style="padding: 30px; padding-bottom: 60px; text-align: center;">
			<?php if ( nebula_retarget('nebula_retargeting_example', 'clicked') ): ?>
				<h3><strong>Thanks</strong> for testing this out!</h3>
				<a id="testreset" class="btn btn-primary" href="#">Click here to reset (or clear cookies).</a>
			<?php elseif ( nebula_retarget('nebula_retargeting_example', 'hovered') ): ?>
				<h3>Ooh, you hovered over it, but <strong>didn't click</strong>.</h3>
				<a id="hoveredbutton" class="btn btn-primary" href="#">What, are you scared? Click here!</a>
			<?php else: ?>
				<h3>You <strong>have not</strong> tested this example yet.</h3>
				<a id="testbutton" class="btn btn-primary" href="#">Click here to give it a try!</a>
			<?php endif; ?>
		</div>
	</div><!--/col-->
</div><!--/row-->