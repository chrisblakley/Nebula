<div class="row" style="margin: 20px 0 50px 0;">
	<div class="col-md-6">
		<h2 style="text-align: center; font-size: 42px;color: #0098d7;">HEX Color</h2>
	</div><!--/col-->

	<div class="col-md-6">
		<?php $colortest = hex2rgb('#0098d7'); ?>
		<h2 style="text-align: center; font-size: 42px; color: rgba(<?php echo $colortest['r'] ?>, <?php echo $colortest['g'] ?>, <?php echo $colortest['b'] ?>, 1);">RGB Color</h2>
	</div><!--/col-->
</div><!--/row-->