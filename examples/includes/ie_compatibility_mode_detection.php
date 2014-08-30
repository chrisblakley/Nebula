<style>
	div.notie {color: red; font-weight: bold;}
	.ie div.notie {display: none;}
	div.iemode {display: none; position: relative; height: 150px; width: 280px; border: 2px solid #222; background: #ddd;}
		
	/* IE Standards */
	.ie div.iemode {display: table;}
	.ie6 div.iemode {background: #3696e9; border: 2px solid #72f0fc;}
	.ie7 div.iemode {background: #1374ae; border: 2px solid #f4b619;}
	.ie8 div.iemode {background: #1374ae; border: 2px solid #f4b619;}
	.ie9 div.iemode {background: #3aa8de; border: 2px solid #fbd21e;}
	.ie10 div.iemode {background: #2b6bec;}
	.ie11 div.iemode {background: #2ebaee;}
	
	/* IE Alternate Modes */
	.ie8.no-hashchange.no-rgba-no.applicationcache.no-pointerevents div.iemode {} /* IE8 w/ IE7 Standards */
	
	.ie9.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE9 w/ IE8 Standards */
	.ie9.no-hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE9 w/ IE7 Standards */
	
	.ie10.hashchange.rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 w/ IE9 Standards */
	.ie10.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 w/ IE8 Standards */
	.ie10.no-hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 w/ IE7 Standards */
	.ie7.hashchange.rgba.applicationcache.no-pointerevents div.iemode {} /* IE10 Compatibility w/ IE10 Standards */
	.ie7.hashchange.rgba.no-applicationcache.no-pointerevents div.iemode {} /* IE10 Compatibility w/ IE9 Standards */
	
	.ie10.hashchange.rgba.applicationcache.pointerevents div.iemode {} /* IE11 w/ IE10 Standards */
	
	/* IE Duplicates */
	.ie7.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode {} /* (IE10 Compatiblity w/ IE8 Standards) -OR- (IE8 Compatiblity w/ IE8 Standards) */
	
	/* Alternate Device IE Versions */
	/* Add selectors here for Windows Phone, Xbox One, Xbox 360, or any other IE devices that do not have different rendering options. */
	
	/* Non-Unique IE Environments:
		
		.ie7.no-hashchange.no-rgba.no-applicationcache.no-pointerevents
			IE7
			IE8 Compatibility w/ IE7 Standards
			IE9 Compatibility w/ IE7 Standards
			IE10 Compatibility w/ IE7 Standards
			IE11 w/ IE7 Standards (Could be isolated with OS detection [.win7 ..., .win8 ...])
		
		.ie7.hashchange.no-rgba.no-applicationcache.no-pointerevents
			IE8 Compatibility w/ IE8 Standards
			IE9 Compatibility w/ IE8 Standards
			IE10 Compatibility w/ IE8 Standards
		
		.ie7.hashchange.rgba.no-applicationcache.no-pointerevents
			IE9 Compatibility w/ IE9 Standards
			IE10 Compatibility w/ IE9 Standards
		
		.ie8.hashchange.no-rgba.no-applicationcache.no-pointerevents
			IE8
			IE11 w/ IE8 Standards (Could be isolated with OS detection [.win7 ..., .win8 ...])
		
		.ie9.hashchange.rgba.no-applicationcache.no-pointerevents
			IE9
			IE11 w/ IE9 Standards (Could be isolated with OS detection [.win7 ..., .win8 ...])
	*/
		
</style>
	
<div class="row">
	<div class="sixteen columns">
		<div class="notie">This example only works with Internet Explorer.</div>
		<div class="iemode"></div>
	</div><!--/columns-->
</div><!--/row-->

<script>
	//This script is only used on this examples page to show which browser mode is detected. It is not needed for actual implementation!
	jQuery('.ie .iemode').html('Internet Explorer');
	jQuery('.ie6 .iemode').html('Internet Explorer 6');
	jQuery('.ie7 .iemode').html('Internet Explorer 7');
	jQuery('.ie8 .iemode').html('Internet Explorer 8');
	jQuery('.ie9 .iemode').html('Internet Explorer 9');
	jQuery('.ie10 .iemode').html('Internet Explorer 10');
	jQuery('.ie11 .iemode').html('Internet Explorer 11');
	
	jQuery('.ie8.no-hashchange.no-rgba-no.applicationcache.no-pointerevents .iemode').html('IE8 w/ IE7 Standards');
	
	jQuery('.ie10.hashchange.rgba.no-applicationcache.no-pointerevents .iemode').html('IE10 w/ IE9 Standards');
	jQuery('.ie10.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 w/ IE8 Standards');
	jQuery('.ie10.no-hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 w/ IE7 Standards');
	jQuery('.ie7.hashchange.rgba.applicationcache.no-pointerevents div.iemode').html('IE10 Compatibility w/ IE10 Standards');
	jQuery('.ie7.hashchange.rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 Compatibility w/ IE9 Standards');
	
	jQuery('.ie7.hashchange.no-rgba.no-applicationcache.no-pointerevents div.iemode').html('IE10 Compatiblity w/ IE8 Standards <br/> -or- <br/> IE8 Compatiblity w/ IE8 Standards');
	
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
	jQuery(' .iemode').html('IE');
</script>