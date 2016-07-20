<style>
	.jscookie, .phpcookie {background: red; color: white; padding: 5px;}
		.jscookie:hover, .phpcookie:hover {background: grey; color: white;}
		.jscookie.cookie-on, .phpcookie.cookie-on {background: green; color: white;}
			.jscookie.cookie-on:hover, .phpcookie.cookie-on:hover {background: grey; color: white;}
</style>

<div class="row">
	<div class="col-md-12">
		<p>Toggle cookie: <a class="jscookie" href="#">OFF</a> Last set on: <span class="setdate">(not set)</span></p>

		<script>
			jQuery(document).ready(function() {
				checkExample();
				function checkExample() {
					if ( readCookie('examplejs') ) {
						jQuery('.jscookie').text('ON').addClass('cookie-on');
						jQuery('.setdate').text(readCookie('examplejs'));
					} else {
						jQuery('.jscookie').text('OFF').removeClass('cookie-on');
						jQuery('.setdate').text('(not set)');
					}
				}

				jQuery('.jscookie').on('click tap touch', function(){
					if ( jQuery(this).hasClass('cookie-on') ) {
						eraseCookie('examplejs');
						checkExample();
						ga('send', 'event', 'Cookie Example', 'Disabled');
					} else {
						var weekday = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
						var month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
						var currentTime = new Date();
						dddd = currentTime.getDay(); //Get day of week (0-6)
						dddd = weekday[dddd]; //Convert day to named weekday
						MMMM = currentTime.getMonth(); //Get month (0-11)
						MMMM = month[MMMM]; //Convert month to named month
						d = currentTime.getDate(); //Get date (1-31)
						yyyy = currentTime.getFullYear(); //Get year (2014)
						h = currentTime.getHours(); //Get hours (0-23)
						tt = ( h >=12 ? 'pm' : 'am' ); //Determine AM or PM
						h = ( h > 12 ? h-12 : h ); //Convert hours to 12 hour format
						h = ( h == 0 ? 12 : h ); //Convert hours to 12 hour format
						mm = currentTime.getMinutes(); //Get minutes (0-59)
						mm = ( mm <10 ? '0'+mm : mm ); //Add leading 0 to minutes (as needed)
						ss = currentTime.getSeconds(); //Get seconds (0-59)
						ss = ( ss <10 ? '0'+ss : ss ); //Add leading 0 to seconds (as needed)
						currentTime = dddd + ', ' + MMMM + ' ' + d + ', ' + yyyy + ' @ ' + h + ':' + mm + ' ' + tt;

						ga('send', 'event', 'Cookie Example', 'Enabled', currentTime);
						createCookie('examplejs', currentTime, 9999);
						checkExample();
					}


					return false;
				});
			});
		</script>

		<br />
	</div><!--/col-->
</div><!--/row-->