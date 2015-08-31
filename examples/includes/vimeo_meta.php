<div class="row">
	<div class="eight columns">
		<?php $vimeo_id = '137299226'; ?>
		<?php if ( vimeo_meta($vimeo_id) ): ?>
			<article class="vimeo video">
				<iframe id="<?php echo vimeo_meta($vimeo_id, 'safetitle'); ?>" class="vimeoplayer" src="https://player.vimeo.com/video/<?php echo $vimeo_id; ?>?api=1&player_id=<?php echo vimeo_meta($vimeo_id, 'safetitle'); ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			</article>

			<br />
			<div class="container">
				<div class="row">
					<div class="four columns">
						<a href="<?php echo vimeo_meta($vimeo_id, 'url'); ?>" target="_blank"><img src="<?php echo vimeo_meta($vimeo_id, 'thumbnail'); ?>" width="100"/></a>
					</div><!--/columns-->
					<div class="twelve columns">
							<a href="<?php echo vimeo_meta($vimeo_id, 'url'); ?>" target="_blank"><?php echo vimeo_meta($vimeo_id, 'title'); ?></a> <span style="font-size: 12px;">(<?php echo vimeo_meta($vimeo_id, 'duration'); ?>)</span>
							<span style="display: block; font-size: 12px; line-height: 18px;">
								by <?php echo vimeo_meta($vimeo_id, 'user'); ?><br />
								<?php echo vimeo_meta($vimeo_id, 'description'); ?>
							</span>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
		<?php endif; ?>

	</div><!--/columns-->
	<div class="eight columns">
		<?php $vimeo_id = '132454664'; ?>
		<?php if ( vimeo_meta($vimeo_id) ): ?>
			<article class="vimeo video">
				<iframe id="<?php echo vimeo_meta($vimeo_id, 'safetitle'); ?>" class="vimeoplayer" src="https://player.vimeo.com/video/<?php echo vimeo_meta($vimeo_id, 'id'); ?>?api=1&player_id=<?php echo vimeo_meta($vimeo_id, 'safetitle'); ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			</article>

			<br />
			<div class="container">
				<div class="row">
					<div class="four columns">
						<a href="<?php echo vimeo_meta($vimeo_id, 'url'); ?>" target="_blank"><img src="<?php echo vimeo_meta($vimeo_id, 'thumbnail'); ?>" width="100"/></a>
					</div><!--/columns-->
					<div class="twelve columns">
							<a href="<?php echo vimeo_meta($vimeo_id, 'url'); ?>" target="_blank"><?php echo vimeo_meta($vimeo_id, 'title'); ?></a> <span style="font-size: 12px;">(<?php echo vimeo_meta($vimeo_id, 'duration'); ?>)</span>
							<span style="display: block; font-size: 12px; line-height: 18px;">
								by <?php echo vimeo_meta($vimeo_id, 'user'); ?><br />
								<?php echo vimeo_meta($vimeo_id, 'description'); ?>
							</span>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
		<?php endif; ?>

	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	<div class="sixteen columns">
		<br />
		<h4>Available Data</h4>
		<p>Using the 'json' parameter: <?php echo do_shortcode("[code]vimeo_meta('137299226', 'json')[/code]"); ?></p>
		<?php echo do_shortcode('[pre lang=js]' . json_encode(vimeo_meta('137299226', 'json'), JSON_PRETTY_PRINT) . '[/pre]'); ?>
	</div><!--/columns-->
</div><!--/row-->