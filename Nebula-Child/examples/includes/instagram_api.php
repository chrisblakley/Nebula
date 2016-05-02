<?php
	/*
		Instagram API

	*/
?>

<script>
	jQuery(document).on('ready', function(){

		instagramAPI();
		function instagramAPI(){
			//https://api.instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=token
			//https://instagram.com/oauth/authorize/?client_id=109826c96265458bba3a73081e6c7902&amp;redirect_uri=https://gearside.com/nebula&amp;response_type=token

			console.log('inside instagram api function');

			var accessToken = '3adbe555acce48b1a9c151204358a92a';

			jQuery.ajax({
				url: 'https://api.instagram.com/v1/media/popular',
				dataType: 'jsonp',
				type: 'GET',
				data: {client_id: accessToken},
				success: function(data){
					console.log('ajax success');
					console.log(data);

					for( x in data.data ){
						jQuery('ul.instagramcon').append('<li><img  src="' + data.data[x].images.low_resolution.url + '"></li>');
					}
				}, error: function(data){
					console.log('ajax error');
					console.log(data);
				}
			});
		}


	});
</script>


<div class="row">
	<div class="col-md-12">

		<?php if ( 1==1 ): ?>
			<a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo nebula_option('instagram_client_id'); ?>&redirect_uri=<?php echo get_permalink(); ?>&response_type=code">Click here to authorize Instagram</a>
		<?php endif; ?>

		<ul class="instagramcon"></ul>

	</div><!--/col-->
</div><!--/row-->