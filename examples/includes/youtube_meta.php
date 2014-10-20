<div class="row">
	<div class="eight columns">
		<?php youtube_meta('jtip7Gdcf0Q'); ?>
		
		<article class="youtube video">
			<iframe id="<?php echo $GLOBALS['youtube_meta']['safetitle']; ?>" class="youtubeplayer" width="560" height="315" src="http://www.youtube.com/embed/<?php echo $GLOBALS['youtube_meta']['id']; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo $GLOBALS['youtube_meta']['origin']; ?>&rel=0" frameborder="0" allowfullscreen=""></iframe>
		</article>
					
		<br/>
		<div class="container">
			<div class="row">
				<div class="four columns">
					<a href="<?php echo $GLOBALS['youtube_meta']['href']; ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $GLOBALS['youtube_meta']['id']; ?>/hqdefault.jpg" width="100"/></a>
				</div><!--/columns-->
				<div class="twelve columns">
						<a href="<?php echo $GLOBALS['youtube_meta']['href']; ?>" target="_blank"><?php echo $GLOBALS['youtube_meta']['title']; ?></a> <span style="font-size: 12px;">(<?php echo $GLOBALS['youtube_meta']['duration']; ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo $GLOBALS['youtube_meta']['author']; ?><br/>
							<?php echo $GLOBALS['youtube_meta']['content']; ?>
						</span>
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
								
	</div><!--/columns-->
	<div class="eight columns">
		<?php youtube_meta('fjh61K3hyY0'); ?>
		
		<article class="youtube video">
			<iframe id="<?php echo $GLOBALS['youtube_meta']['safetitle']; ?>" class="youtubeplayer" width="560" height="315" src="http://www.youtube.com/embed/<?php echo $GLOBALS['youtube_meta']['id']; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo $GLOBALS['youtube_meta']['origin']; ?>" frameborder="0" allowfullscreen=""></iframe>
		</article>
									
		<br/>
		<div class="container">
			<div class="row">
				<div class="four columns">
					<a href="<?php echo $GLOBALS['youtube_meta']['href']; ?>" target="_blank"><img src="http://i1.ytimg.com/vi/<?php echo $GLOBALS['youtube_meta']['id']; ?>/hqdefault.jpg" width="100"/></a>
				</div><!--/columns-->
				<div class="twelve columns">
						<a href="<?php echo $GLOBALS['youtube_meta']['href']; ?>"><?php echo $youtube_meta['title']; ?></a> <span style="font-size: 12px;">(<?php echo $GLOBALS['youtube_meta']['duration']; ?>)</span>
						<span style="display: block; font-size: 12px; line-height: 18px;">
							by <?php echo $GLOBALS['youtube_meta']['author']; ?><br/>
							<?php echo $GLOBALS['youtube_meta']['content']; ?>
						</span>
				</div><!--/columns-->
			</div><!--/row-->
		</div><!--/container-->
		
	</div><!--/columns-->
</div><!--/row-->