<?php
	//Move this to functions.php or nebula_child.php!
	add_action('wp_ajax_nebula_infinite_load', 'nebula_infinite_load');
	add_action('wp_ajax_nopriv_nebula_infinite_load', 'nebula_infinite_load');
	function nebula_infinite_load(){
		if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
		$page_number = $_POST['page'];
		$posts_per_page = $_POST['posts'];

		query_posts(array('category_name' => 'bacon', 'showposts' => $posts_per_page, 'paged' => $page_number)); //@TODO "Nebula" 0: How can these query args be passed through ajax?
		if ( have_posts() ) while ( have_posts() ): the_post(); ?>
		    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		        <p class="news-title entry-title">
			        <a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a><br/>
			        <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
			    </p>
		    </article>
	    <?php endwhile;

	    exit;
	}
?>

<style>
	#infinite-posts-list article {margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px dotted #ccc;}

	.loadmorecon {position: relative; text-align: center; margin: 50px 0;}
		.loadmorecon:before {content: '\f141'; font-family: "FontAwesome"; position: absolute; top: 10px; left: 0; width: 100%; display: block; text-align: center; opacity: 0; pointer-events: none;}
			.loadmorecon.loading:before {animation: fadeinout 2s infinite linear;}
			.loadmorecon.disabled a {color: #444; opacity: 0.3; pointer-events: none;}
				.loadmorecon.disabled a:after {content: ''; opacity: 0; pointer-events: none;}
	.infinite-load-more {position: relative; font-size: 18px; padding: 10px;}
		.loadmorecon.loading .infinite-load-more {opacity: 0; pointer-events: none;}
		.infinite-load-more:after {content: '\f078'; font-family: "FontAwesome"; position: absolute; bottom: -10px; left: 0; width: 100%; display: block; text-align: center; transition: bottom 0.25s ease;}
			.infinite-load-more:hover:after,
			.infinite-load-more.hover:after {bottom: -13px;}

	@keyframes fadeinout {
    	0% {opacity: 0;}
		50% {opacity: 1;}
		100% {opacity: 0;}
	}
</style>

<script>
	jQuery(document).on('ready', function(){

		var pageNumber = 2;
		jQuery('.infinite-load-more').on('click touch tap', function(){
			var maxPages = jQuery('#infinite-posts-list').attr('data-max-pages');
			if ( pageNumber <= maxPages ){
				jQuery('.loadmorecon').addClass('loading');

		        jQuery.ajax({
					type: "POST",
					url: nebula.site.ajax.url,
					data: {
						nonce: nebula.site.ajax.nonce,
						action: 'nebula_infinite_load_example', //Make sure this matches the actual function name!
						posts: 4,
						page: pageNumber,
					},
					success: function(response){
						jQuery("#infinite-posts-list").append(response);
						jQuery('.loadmorecon').removeClass('loading');
						var maxPages = jQuery('#infinite-posts-list').attr('data-max-pages');
						if ( pageNumber >= maxPages ){
							jQuery('.loadmorecon').addClass('disabled').find('a').text('No more posts.');
						}
						ga('set', gaCustomDimensions['timestamp'], localTimestamp());
						ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Infinite Load'));
					},
					error: function(MLHttpRequest, textStatus, errorThrown){
						ga('set', gaCustomDimensions['timestamp'], localTimestamp());
						ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Infinite Load AJAX Error'));
						ga('send', 'event', 'Error', 'AJAX Error', 'Infinite Load AJAX');
					},
					timeout: 60000
				});

		        pageNumber++;
			}

			return false;
		});

	});
</script>

<br/><br/>
<div class="row">
	<div class="sixteen columns">
		<h2>Infinite Load Example</h2><br/><br/>

		<?php query_posts(array('category_name' => 'bacon', 'showposts' => 4, 'paged' => 1)); //@TODO "Nebula" 0: How can this entire query args be passed through ajax? ?>
		<div id="infinite-posts-list" data-max-pages="<?php echo $wp_query->max_num_pages; ?>">
			<?php if ( have_posts() ) while ( have_posts() ): the_post(); ?>
			    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			        <p class="news-title entry-title">
				        <a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a><br/>
				        <?php echo nebula_the_excerpt('Read More &raquo;', 35, 1); ?>
				    </p>
			    </article>
		    <?php endwhile; ?>
		</div>

		<div class="loadmorecon">
			<a class="infinite-load-more" href="#">Load More Posts</a>
		</div>

	</div><!--/columns-->
</div><!--/row-->