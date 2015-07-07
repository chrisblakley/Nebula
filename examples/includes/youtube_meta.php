<div class="row">
	<div class="eight columns">
		<?php $youtube_id = 'jtip7Gdcf0Q'; ?>
		<?php if ( youtube_meta($youtube_id) ): ?>
			<article class="youtube video">
				<iframe id="<?php echo youtube_meta($youtube_id, 'safetitle'); ?>" class="youtubeplayer" width="560" height="315" src="//www.youtube.com/embed/<?php echo $youtube_id; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo youtube_meta($youtube_id, 'origin'); ?>&rel=0" frameborder="0" allowfullscreen=""></iframe>
			</article>
			<br/>
			<div class="container">
				<div class="row">
					<div class="four columns">
						<a href="<?php echo youtube_meta($youtube_id, 'href'); ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $youtube_id; ?>/hqdefault.jpg" width="100"/></a>
					</div><!--/columns-->
					<div class="twelve columns">
						<a href="<?php echo youtube_meta($youtube_id, 'href'); ?>" target="_blank"><?php echo youtube_meta('jtip7Gdcf0Q', 'title'); ?></a> <span style="font-size: 12px;">(<?php echo youtube_meta($youtube_id, 'duration'); ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo youtube_meta($youtube_id, 'author'); ?><br/>
							<?php echo youtube_meta($youtube_id, 'description'); ?>
						</span>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
		<?php endif; ?>
	</div><!--/columns-->

	<div class="eight columns">
		<?php $youtube_id = 'fjh61K3hyY0'; ?>
		<?php if ( youtube_meta($youtube_id) ): ?>
			<article class="youtube video">
				<iframe id="<?php echo youtube_meta($youtube_id, 'safetitle'); ?>" class="youtubeplayer" width="560" height="315" src="//www.youtube.com/embed/<?php echo $youtube_id; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo youtube_meta($youtube_id, 'origin'); ?>" frameborder="0" allowfullscreen=""></iframe>
			</article>
			<br/>
			<div class="container">
				<div class="row">
					<div class="four columns">
						<a href="<?php echo youtube_meta($youtube_id, 'href'); ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $youtube_id; ?>/hqdefault.jpg" width="100"/></a>
					</div><!--/columns-->
					<div class="twelve columns">
						<a href="<?php echo youtube_meta($youtube_id, 'href'); ?>"><?php echo youtube_meta($youtube_id, 'title'); ?></a> <span style="font-size: 12px;">(<?php echo youtube_meta($youtube_id, 'duration'); ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo youtube_meta($youtube_id, 'author'); ?><br/>
							<?php echo youtube_meta($youtube_id, 'description'); ?>
						</span>
					</div><!--/columns-->
				</div><!--/row-->
			</div><!--/container-->
		<?php endif; ?>
	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	<div class="sixteen columns">
		<br/>
		<h4>Available Data</h4>
		<p>Using the 'json' parameter: <?php echo do_shortcode("[code]youtube_meta('fjh61K3hyY0', 'json')[/code]"); ?></p>
		<?php echo do_shortcode('[pre lang=js]' . json_encode(youtube_meta('fjh61K3hyY0', 'json'), JSON_PRETTY_PRINT) . '[/pre]'); ?>
	</div><!--/columns-->
</div><!--/row-->