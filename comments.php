<?php if ( !nebula_settings_conditional('nebula_comments', 'disabled') && nebula_settings_conditional_text_bool('nebula_disqus_shortname') ) : //nebula_comments is inverted because the condition refers to the Nebula function, not the setting. So, in functions true == disabled, but in this template, true == enabled. ?>

	<div id="nebulacommentswrapper">

		<div id="disqus_thread"></div>

		<script type="text/javascript">
			var disqus_shortname = '<?php echo nebula_settings_conditional_text('nebula_disqus_shortname', ''); ?>';
			var disqus_identifier = '<?php echo 'the-id-' . get_the_id(); ?>';
			var disqus_title = '<?php the_title(); ?>';
			var disqus_url = '<?php the_permalink(); ?>';

			(function() {
				var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
				dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
				(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
			})();
		</script>

	</div><!--/nebulacommentswrapper-->

<?php endif; ?>