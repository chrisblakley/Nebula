<?php
	/*
		Note: youtube_meta() needs a Google server API key in order to work!
		Set it in Nebula Options (instructions can be found there).

		Note: The &origin=<?php echo youtube_meta($youtube_id, 'origin'); ?> parameter may trigger many console errors (and API ready never triggering). It is removed, but may be needed at some point.

		Once the Player API is ready, you can control videos with the Nebula object "players" and the iframe ID (*not* Youtube video ID).
		Ex: players.youtube['The-ID-From-The-Iframe-Here'].playVideo();
	*/
?>

<br/><br/>

<h2>Youtube</h2>

<div class="row">
	<div class="col-md-6">
		<?php $youtube_data = video_meta('youtube', 'jtip7Gdcf0Q'); ?>
		<?php if ( !empty($youtube_data) ): ?>
			<div class="embed-responsive embed-responsive-16by9">
				<iframe id="<?php echo $youtube_data['safetitle']; ?>" class="youtube embed-responsive-item" width="560" height="315" src="//www.youtube.com/embed/<?php echo $youtube_data['id']; ?>?wmode=transparent&enablejsapi=1&rel=0" frameborder="0" allowfullscreen=""></iframe>
			</div>

			<br />

			<div class="row">
				<div class="col-md-3">
					<a href="<?php echo $youtube_data['url']; ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $youtube_data['id']; ?>/hqdefault.jpg" width="100"/></a>
				</div><!--/col-->
				<div class="col-md-12">
					<a href="<?php echo $youtube_data['url']; ?>" target="_blank"><?php echo $youtube_data['title']; ?></a> <span style="font-size: 12px;">(<?php echo $youtube_data['duration']['time']; ?>)</span>
					<span style="display: block; font-size: 12px; line-height: 18px;">
						by <?php echo $youtube_data['author']; ?><br />
						<?php echo $youtube_data['description']; ?>
					</span>
				</div><!--/col-->
			</div><!--/row-->
		<?php endif; ?>
	</div><!--/col-->

	<div class="col-md-6">
		<?php $youtube_data = video_meta('youtube', 'fjh61K3hyY0'); ?>
		<?php if ( !empty($youtube_data) ): ?>
			<div class="embed-responsive embed-responsive-16by9">
				<iframe id="<?php echo $youtube_data['safetitle']; ?>" class="youtube embed-responsive-item" width="560" height="315" src="//www.youtube.com/embed/<?php echo $youtube_data['id']; ?>?wmode=transparent&enablejsapi=1&rel=0" frameborder="0" allowfullscreen=""></iframe>
			</div>

			<br />

			<div class="row">
				<div class="col-md-3">
					<a href="<?php echo $youtube_data['url']; ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $youtube_data['id']; ?>/hqdefault.jpg" width="100"/></a>
				</div><!--/col-->
				<div class="col-md-12">
					<a href="<?php echo $youtube_data['url']; ?>" target="_blank"><?php echo $youtube_data['title']; ?></a> <span style="font-size: 12px;">(<?php echo $youtube_data['duration']['time']; ?>)</span>
					<span style="display: block; font-size: 12px; line-height: 18px;">
						by <?php echo $youtube_data['author']; ?><br />
						<?php echo $youtube_data['description']; ?>
					</span>
				</div><!--/col-->
			</div><!--/row-->
		<?php endif; ?>
	</div><!--/col-->
</div><!--/row-->

<div class="row">
	<div class="col-md-12">
		<br />
		<h4>Available Youtube Data</h4>
		<?php echo do_shortcode('[pre lang=js]' . json_encode($youtube_data['raw'], JSON_PRETTY_PRINT) . '[/pre]'); ?>
	</div><!--/col-->
</div><!--/row-->

<br/><br/>

<h2>Vimeo</h2>

<div class="row">
	<div class="col-md-6">
		<?php $vimeo_data = video_meta('vimeo', '137299226'); ?>
		<?php if ( !empty($vimeo_data) ): ?>
			<div class="embed-responsive embed-responsive-16by9">
				<iframe id="<?php echo $vimeo_data['safetitle']; ?>" class="vimeo embed-responsive-item" src="https://player.vimeo.com/video/<?php echo $vimeo_data['id']; ?>?api=1&player_id=<?php echo $vimeo_data['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			</div>

			<br />
			<div class="row">
				<div class="col-md-3">
					<a href="<?php echo $vimeo_data['url']; ?>" target="_blank"><img src="<?php echo $vimeo_data['thumbnail']; ?>" width="100"/></a>
				</div><!--/col-->
				<div class="col-md-9">
						<a href="<?php echo $vimeo_data['url']; ?>" target="_blank"><?php echo $vimeo_data['title']; ?></a> <span style="font-size: 12px;">(<?php echo $vimeo_data['duration']['time']; ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo $vimeo_data['author']; ?><br />
							<?php echo $vimeo_data['description']; ?>
						</span>
				</div><!--/col-->
			</div><!--/row-->
		<?php endif; ?>

	</div><!--/col-->
	<div class="col-md-6">
		<?php $vimeo_data = video_meta('vimeo', '132454664'); ?>
		<?php if ( !empty($vimeo_data) ): ?>
			<div class="embed-responsive embed-responsive-16by9">
				<iframe id="<?php echo $vimeo_data['safetitle']; ?>" class="vimeo embed-responsive-item" src="https://player.vimeo.com/video/<?php echo $vimeo_data['id']; ?>?api=1&player_id=<?php echo $vimeo_data['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			</div>

			<br />
			<div class="row">
				<div class="col-md-3">
					<a href="<?php echo $vimeo_data['url']; ?>" target="_blank"><img src="<?php echo $vimeo_data['thumbnail']; ?>" width="100"/></a>
				</div><!--/col-->
				<div class="col-md-9">
						<a href="<?php echo $vimeo_data['url']; ?>" target="_blank"><?php echo $vimeo_data['title']; ?></a> <span style="font-size: 12px;">(<?php echo $vimeo_data['duration']['time']; ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo $vimeo_data['author']; ?><br />
							<?php echo $vimeo_data['description']; ?>
						</span>
				</div><!--/col-->
			</div><!--/row-->
		<?php endif; ?>

	</div><!--/col-->
</div><!--/row-->

<div class="row">
	<div class="col-md-12">
		<br />
		<h4>Available Vimeo Data</h4>
		<?php echo do_shortcode('[pre lang=js]' . json_encode($vimeo_data['raw'], JSON_PRETTY_PRINT) . '[/pre]'); ?>
	</div><!--/col-->
</div><!--/row-->