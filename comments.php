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
				'reverse_top_level' => 'true',
				'reverse_children' => 'true'
			);
			wp_list_comments($comment_list_args, $comments);
		?>
	<?php endif; ?>
	
	<hr/>
	<div class="formcon" style="margin-top: 5px;">
		
		<!-- @TODO: This should change to Reply to XXXXX when replying. -->
		<h3 style="margin-bottom: 10px;">Add a Comment</h3>
		
		<?php if ( is_user_logged_in() ) : ?>
			<?php $userData = get_userdata(get_current_user_id()); ?>
			
			<div class="user-avatar">
				<?php
					$currentUser = get_current_user_id();
					$currentUserData = get_userdata($currentUser);
					$currentHeadshot = str_replace('.jpg', '-150x150.jpg' , $currentUserData->headshot_url);
				?>
				
				<?php if ( $currentUserData->headshot_url ) : ?>
					<img src="<?php echo $currentHeadshot; ?>" width="50" height="50" style="border-radius: 25px; border: 2px solid #fff; box-shadow: 0px 0px 6px 0 rgba(0,0,0,0.2);" />
				<?php endif; ?>
			</div>
			
			<div style="float: left; width: 65%;">
				<p class="logged-in-as"><a href="<?php echo admin_url('profile.php'); ?>"><strong><?php echo $userData->display_name; ?></strong></a> <small><a href="<?php echo wp_logout_url(get_permalink()); ?>">(Log Out)</a></small></p>
				
				<?php
					$comment_args = array(
				        'label_submit'=>'Submit',
				        'title_reply'=>'',
				        'logged_in_as' => '',
				        'comment_notes_before' => '',
				        'comment_notes_after' => '',
				        'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true" placeholder="Comment"></textarea></p>',
					);
				?>
				<?php comment_form($comment_args); ?>
			</div>
		<?php else : ?>
			<?php nebula_facebook_link(); ?>
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
					      '<p class="comment-form-author-heading fb-form-name">Name</p><p class="comment-form-author"><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" aria-required="true" placeholder="Name" /></p>',
					
					    'email' =>
					      '<p class="comment-form-email-heading fb-form-email">Email</p><p class="comment-form-email"><input id="email" name="email" type="text" value="' . esc_attr($commenter['comment_author_email']) . '" aria-required="true" placeholder="Email" /></p>',						
					    ),
			        'comment_field' => '<p class="comment-form-comment-heading">Add a comment</p><p class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true" placeholder="Comment"></textarea></p>',
				);
			?>
			<?php comment_form($comment_args); ?>
		<?php endif; ?>
		
		
		
	</div><!--formcon-->
	            	
</div><!--/commentscon-->
<?php endif; ?>