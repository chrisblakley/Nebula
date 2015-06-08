<style>
	.waitresult {font-size: 18px;}
	.waitchecks p {color: #ccc;}
	.waitchecks .activated {color: green;}
</style>

<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>

<script>
	jQuery(document).ready(function(){


		jQuery(document).on('click', function(){
			inProgress(jQuery('.clicktest'));
			debounce(function(){
				checkThenFade(jQuery('.clicktest'));
			}, 1000, 'click test');
		});

		jQuery(window).resize(function() {
			inProgress(jQuery('.windowresize'));
			debounce(function(){
				checkThenFade(jQuery('.windowresize'));
			}, 1000, 'resize test');
		});

		jQuery(window).on('scroll', function(){
			inProgress(jQuery('.windowscroll'));
			debounce(function(){
				checkThenFade(jQuery('.windowscroll'));
			}, 1000, 'scroll test');
		});

		jQuery(window).mousemove(function(){
			inProgress(jQuery('.mousemove'));
			debounce(function(){
				checkThenFade(jQuery('.mousemove'));
			}, 1000, 'mouse move test');
		});

	});


	//The following functions are only for this example and are not needed for debounce.

	function inProgress(oThis) {
		if ( !oThis.hasClass('activated') ) {
			oThis.find('i').removeClass('fa-circle-thin').addClass('fa-spinner fa-spin');
		}
	}

	function checkThenFade(oThis) {
		oThis.addClass('activated').find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
		setTimeout(function(){
			oThis.removeClass('activated', 500, function(){
				jQuery(this).find('i').removeClass('fa-check').addClass('fa-circle-thin');
			});
		}, 1500);
	}
</script>


<div class="row">
	<div class="eight columns waitchecks">

		<p class="waitresult clicktest"><i class="fa fa-circle-thin"></i> Click Test</p>
		<p class="waitresult mousemove"><i class="fa fa-circle-thin"></i> Mouse Movement</p>

	</div><!--/columns-->

	<div class="eight columns waitchecks">

		<p class="waitresult windowresize"><i class="fa fa-circle-thin"></i> Window Resize</p>
		<p class="waitresult windowscroll"><i class="fa fa-circle-thin"></i> Window Scroll</p>

	</div><!--/columns-->
</div><!--/row-->