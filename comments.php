<?php if ( nebula()->get_option('comments') ): ?>
	<section id="nebulacommentswrapper">
		<?php if ( nebula()->get_option('disqus_shortname') ): ?>
			<div id="disqus_thread"></div>
			<script type="text/javascript">
				<?php //Note this is a manual implementation of Disqus; we are NOT using the WordPress plugin for implementation. ?>
				var disqus_shortname = '<?php echo nebula()->get_option('disqus_shortname'); ?>';
				var disqus_identifier = '<?php echo 'the-id-' . get_the_id(); ?>';
				var disqus_title = '<?php echo esc_html(the_title('', '', false)); ?>';
				var disqus_url = '<?php esc_url(the_permalink()); ?>';

				(function(){
					var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
					dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
					(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
				})();

				function disqus_config(){
					<?php /* Available Disqus Callbacks:
						afterRender - fires when the template is rendered but before it is shown.
						beforeComment
						onIdentify
						onInit - fires when all dependencies are resolved but before the DTPL template is rendered.
						onNewComment
						onPaginate
						onReady - fires when everything is done.
						preData - fires before sending the request for initial data.
						preInit - fires after receiving the initial data but before loading any dependencies.
						preReset
					*/ ?>

					//Track comments in Google Analytics
					this.callbacks.onNewComment = [function(comment){
						gtag('event', 'Submit', {
							event_category: 'Comment (via Disqus)',
							comment_id: comment.id,
						});
					}];
				}
			</script>
		<?php else : ?>
			<section id="comments" class="comments">
				<?php if ( have_comments() ): ?>
					<h2><?php printf(_nx('One response to &ldquo;%2$s&rdquo;', '%1$s responses to &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'nebula'), number_format_i18n(get_comments_number()), '<span>' . get_the_title() . '</span>'); ?></h2>

					<ol class="comment-list">
						<?php wp_list_comments(array('style' => 'ol', 'short_ping' => true)); ?>
					</ol>

					<?php if ( get_comment_pages_count() > 1 && get_option('page_comments') ): ?>
						<nav>
							<ul class="pager">
								<?php if ( get_previous_comments_link() ): ?>
									<li class="previous"><?php previous_comments_link('&larr; ' . __('Older comments', 'nebula')); ?></li>
								<?php endif; ?>
								<?php if ( get_next_comments_link() ): ?>
									<li class="next"><?php next_comments_link(__('Newer comments', 'nebula') . ' &rarr;'); ?></li>
								<?php endif; ?>
							</ul>
						</nav>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( !comments_open() && get_comments_number() != '0' && post_type_supports(get_post_type(), 'comments') ): ?>
					<div class="alert alert-warning">
						<?php _e('Comments are closed.', 'nebula'); ?>
					</div>
				<?php endif; ?>

				<?php comment_form(); ?>
			</section>
		<?php endif; ?>
	</section>
<?php endif; ?>