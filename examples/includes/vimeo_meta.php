<div class="row">
	<div class="eight columns">
		<?php vimeo_meta('97428427'); ?>
		
		<article class="vimeo video">
			<iframe id="<?php echo $vimeo_meta['safetitle']; ?>" class="vimeoplayer" src="http://player.vimeo.com/video/<?php echo $vimeo_meta['id']; ?>?api=1&player_id=<?php echo $vimeo_meta['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
		</article>
					
		<br/>
		<div class="container">
			<div class="row">
				<div class="four columns">
					<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><img src="<?php echo $vimeo_meta['thumbnail']; ?>" width="100"/></a>
				</div><!--/columns-->
				<div class="twelve columns">
						<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><?php echo $vimeo_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $vimeo_meta['duration']; ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo $vimeo_meta['user']; ?><br/>
							<?php echo $vimeo_meta['description']; ?>
						</span>
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
								
	</div><!--/columns-->
	<div class="eight columns">
		
		<?php vimeo_meta('27855315'); ?>
		
		<article class="vimeo video">
			<iframe id="<?php echo $vimeo_meta['safetitle']; ?>" class="vimeoplayer" src="http://player.vimeo.com/video/<?php echo $vimeo_meta['id']; ?>?api=1&player_id=<?php echo $vimeo_meta['safetitle']; ?>" width="560" height="315" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
		</article>
									
		<br/>
		<div class="container">
			<div class="row">
				<div class="four columns">
					<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><img src="<?php echo $vimeo_meta['thumbnail']; ?>" width="100"/></a>
				</div><!--/columns-->
				<div class="twelve columns">
						<a href="<?php echo $vimeo_meta['url']; ?>" target="_blank"><?php echo $vimeo_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $vimeo_meta['duration']; ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo $vimeo_meta['user']; ?><br/>
							<?php echo $vimeo_meta['description']; ?>
						</span>
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
		
	</div><!--/columns-->
</div><!--/row-->