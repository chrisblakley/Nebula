<div class="row">
	<div class="ten columns">
		<?php if ( is_plugin_active('custom-facebook-feed/custom-facebook-feed.php') ) : ?>
			<div id="fbcon">
				<div class="fbhead">
					<p><a href="<?php echo $social['facebook_url']; ?>" target="_blank"><i class="fa fa-facebook-square"></i> Facebook</a></p>
				</div><!--/fbhead-->
				<div class="fbbody">
					<div class="container">
						<div class="fb-feed">
							<div class="row tweetcon">
								<div class="four columns">
									<div class="fbicon"><a href="<?php echo $social['facebook_url']; ?>" target="_blank"><img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash3/s160x160/64307_10150605580729671_377991150_a.jpg"/></a></div>
								</div><!--/columns-->
								<div class="twelve columns">
									<div class="fbuser">
										<a href="<?php echo $social['facebook_url']; ?>" target="_blank"><?php echo bloginfo('name'); ?></a>
									</div><!--/fbuser-->
									<div class="fbpost">
										<?php echo do_shortcode('[custom-facebook-feed id=PinckneyHugo num=3]'); ?>
									</div><!--/fbpost-->
								</div><!--/columns-->
							</div><!--/row-->
						</div><!--/fb-feed-->
					</div><!--/container-->
				</div><!--/fbbody-->
			</div><!--/fbcon-->
		<?php else : ?>
			<div class="fb-like-box" data-href="<?php echo $social['facebook_url']; ?>" data-colorscheme="light" data-show-faces="false" data-header="true" data-stream="true" data-show-border="true"></div>
		<?php endif; ?>
	</div><!--/columns-->
	<div class="five columns push_one">
		<div class="fb-like-box" data-href="<?php echo $social['facebook_url']; ?>" data-colorscheme="light" data-show-faces="false" data-header="true" data-stream="true" data-show-border="true"></div>
	</div><!--/columns-->
</div><!--/row-->