<?php if ( nebula_settings_conditional('nebula_comments') ) : ?>
<div class="commentcon">
	<?php
		$comments = get_comments(array(
			'post_id' => $post->ID,
			'number' => 10,
			'status' => 'approve'
		));
	?>
		
	<?php if ($comments) : ?>
		<hr/>
		<h3><?php echo ( sizeof($comments) == 0 ) ? 'No' : sizeof($comments); ?> <?php echo ( sizeof($comments) == 1 ) ? 'Comment' : 'Comments'; ?></h3>
		<?php
			$comment_list_args = array(
				'walker' => null,
				'max_depth' => '',
				'style' => 'ul',
				'callback' => 'nebula_comment_theme',
				'end-callback' => null,
				'type' => 'comment',
				'reply_text' => 'Reply',
				//'avatar_size' => 32,
				'reverse_top_level' => 'true'
			);
			wp_list_comments($comment_list_args, $comments);
		?>
	<?php endif; ?>
	
	<hr/>
	<div class="formcon">
					
		<?php if ( is_user_logged_in() ) : ?>
			<?php $userData = get_userdata(get_current_user_id()); ?>
			Logged in as <a href="<?php echo admin_url('profile.php'); ?>"><?php echo $userData->display_name; ?></a> <small><a href="<?php echo wp_logout_url(get_permalink()); ?>">(Log Out)</a></small>
			<?php
				$comment_args = array(
			        'label_submit'=>'Submit',
			        'title_reply'=>'',
			        'logged_in_as' => '',
			        'comment_notes_before' => '',
			        'comment_notes_after' => '',
			        'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true" placeholder="Add a comment"></textarea></p>',
				);
			?>
		<?php else : ?>
			<?php
				$commenter = wp_get_current_commenter();
				$comment_args = array(
			        'label_submit'=>'Submit',
			        'title_reply'=>'',
			        'logged_in_as' => '',
			        'comment_notes_before' => '',
			        'comment_notes_after' => '',
			        'fields' => array(
					    'author' =>
					      '<p class="comment-form-author"><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" aria-required="true" placeholder="Name" /></p>',
					
					    'email' =>
					      '<p class="comment-form-email"><input id="email" name="email" type="text" value="' . esc_attr($commenter['comment_author_email']) . '" aria-required="true" placeholder="Email" /></p>',						
					    ),
			        'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true" placeholder="Add a comment"></textarea></p>',
				);
			?>
		<?php endif; ?>
		
		<?php comment_form($comment_args); ?>
		
	</div><!--formcon-->
	            	
</div><!--/commentscon-->
<?php endif; ?>