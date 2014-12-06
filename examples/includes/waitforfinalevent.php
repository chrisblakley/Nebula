<style>
	.waitresult {font-size: 18px;}
	.waitchecks p {color: #ccc;}
	.waitchecks .activated {color: green;}
</style>

<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>

<script>
	jQuery(document).ready(function() {

		jQuery(document).on('click', function(){
			inProgress(jQuery('.clicktest'));
			waitForFinalEvent(function(){
				checkThenFade(jQuery('.clicktest'));
			}, 500, "clickexample");
		});

		jQuery(window).resize(function() {
			inProgress(jQuery('.windowresize'));
			waitForFinalEvent(function(){
				checkThenFade(jQuery('.windowresize'));
			}, 500, "resizeexample");
		});

		jQuery(window).on('scroll', function(){
			inProgress(jQuery('.windowscroll'));
			waitForFinalEvent(function(){
				checkThenFade(jQuery('.windowscroll'));
			}, 500, "scrollexample");
		});

		jQuery(window).mousemove(function(){
			inProgress(jQuery('.mousemove'));
			waitForFinalEvent(function(){
				checkThenFade(jQuery('.mousemove'));
			}, 500, "mousemoveexample");
		});

	});


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


	/* This is bundled in main.js, so showing here for reference only.
	//Waits until event (generally resize) finishes before triggering. Call with waitForFinalEvent();
	var waitForFinalEvent = (function () {
		var timers = {};
		return function (callback, ms, uniqueId) {
			if (!uniqueId) {
				uniqueId = "Don't call this twice without a uniqueId";
			}
			if (timers[uniqueId]) {
				clearTimeout (timers[uniqueId]);
			}
			timers[uniqueId] = setTimeout(callback, ms);
		};
	})(); //end waitForFinalEvent()
	*/
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