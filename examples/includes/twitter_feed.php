<div class="row">
	<div class="sixteen columns">
		<?php if ( $social['twitter_url'] ) : ?>
			<div id="twittercon">
				<div class="twitterhead">
					<p><a href="<?php echo $social['twitter_url']; ?>" target="_blank"><i class="fa fa-twitter"></i> Tweets</a></p>
				</div><!--/twitterhead-->
				<div class="twitterbody">
					<div class="container">
						<div class="twitter-feed">		
							<div id="twitter_update_list" style="outline: 1px solid red; min-height: 100px;"></div>
						</div><!--/twitter-feed-->
					</div><!--/container-->
				</div><!--/twitterbody-->
			</div><!--/twittercon-->
		<?php endif; ?>
		<br/><br/><hr/>
	</div><!--/columns-->
</div><!--/row-->