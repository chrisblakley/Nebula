<style>
	#debugresponse {width: 100%; height: 200px; padding: 5px 10px; display: none;}
</style>


<script>
	jQuery(document).ready(function() {

		jQuery('#datatostore').on('keyup', function(){
			console.log('checking length');
			if ( jQuery(this).val().trim().length > 0 ) {
				jQuery(this).parent('.field').removeClass('danger');
			}
		});

		jQuery('#uploadtest').on('click touch tap', function(){

			if ( jQuery('#datatostore').val().trim().length < 1 ) {
				jQuery('#datatostore').attr('placeholder', 'This is a required field.').parent('.field').addClass('danger');
				return false;
			}

			nebula_upload_data(jQuery('#datatostore').val(), 'testing', 'txt', 'Nebula Upload Data Example', 'Upload', function(response){

				if ( response != '' ) {
					jQuery('#debugresponse').val(response).slideDown();
				} else {
					jQuery('#debugresponse').val('Upload was successful.').slideDown();
				}

				jQuery('#datauploadcon').slideUp(function(){
					jQuery('#datauploadcon').remove();
				});
			});
			return false;
		});

	});

	//This is a holding location until this function is finalized, tested, and vetted before moving into main.js
	function nebula_upload_data(data, directory, filetype, category, action, callback){

		if ( typeof data == 'undefined' ){
			console.log('data is undefined');
			return false;
		}

		directory = ( typeof directory == 'undefined' )? '' : directory;
		filetype = ( typeof filetype == 'undefined' )? '' : filetype;
		category = ( typeof category == 'undefined' )? '' : category;
		action = ( typeof action == 'undefined' )? '' : action;

		if ( typeof category == 'function' ){
			console.log('category is a function. changing to callback.');
			callback = category;
			category = '';
		}

		if ( typeof action == 'function' ){
			console.log('action is a function. changing to callback.');
			callback = action;
			action = '';
		}

		console.log('attempting ajax...');
		jQuery.ajax({
		    type: "POST",
		    url: bloginfo["admin_ajax"],
		    data: {
		        action: 'nebula_upload_data',
		        data: {
		            'directory': directory,
		            'data': data,
		            'filetype': filetype,
		            'category': category,
		            'action': action,
		            'url': window.location.href,
		        },
		    },
		    success: function(response){
		        console.log('upload data success');
		        if ( typeof callback == 'function' ){
			        callback(response);
		        }
		    },
		    error: function(MLHttpRequest, textStatus, errorThrown){
		        console.log('upload data ajax error');
		        if ( typeof callback == 'function' ){
			        callback(false);
		        }
		    },
		    timeout: 60000
		});
	}
</script>


<div class="row">
	<div class="sixteen columns">

		<div id="datauploadcon">
			<div class="field">
				<input id="datatostore" class="input" type="text" placeholder="Type something here!" required/>
			</div>

			<div class="primary large btn">
				<a id="uploadtest" href="#">Upload a test!</a>
			</div>

			<br/><br/>
		</div>
		<textarea id="debugresponse" placeholder="AJAX response information will appear here."></textarea>

	</div><!--/columns-->
</div><!--/row-->