<style>
	#heroslider {margin-top: -30px;} /* Do not copy over this line - it is for this example page only! */


	div#heroslider {position: relative; height: 500px; overflow: hidden; -webkit-transition: all 1s; -moz-transition: all 1s; -o-transition: all 1s; transition: all 1s;}
		div#heroslider.closed {height: 0;}
	    div#heroslider .bx-wrapper {}
	        div#heroslider .bx-wrapper .bx-viewport {min-height: 500px; border: none; left: 0; z-index: 1;}
	            div#heroslider ul.bxslider {margin: 0; background: #222 url("images/black-linen.png"); height: 100%;}
	                div#heroslider ul.bxslider li {padding: 0; height: 100%;}
	                    div#heroslider ul.bxslider li div {position: absolute; top: 30%; left: 50%; width: 70%; max-width: 940px;}
	                        div#heroslider ul.bxslider li div p {display: table; position: relative; margin-left: -50%; padding: 10px 30px; color: #000; text-align: center; text-transform: uppercase; margin-bottom: 5px; background: #fff; background: rgba(255, 255, 255, 0.9); box-shadow: 1px 1px 3px 0 rgba(0,0,0,0.2);}
	                        div#heroslider ul.bxslider li div p.line1 {font-size: 48px;}
	                        div#heroslider ul.bxslider li div p.line2 {font-size: 24px;}
	                        div#heroslider ul.bxslider li div a {display: inline-block; position: relative; margin-left: -50%; padding: 10px 30px; color: #fff; font-size: 24px; text-align: center; text-transform: uppercase; background: #0098d7;}
	                            div#heroslider ul.bxslider li div a:hover,
	                            div#heroslider ul.bxslider li div a:focus {background: #95d600;}
	                    div#heroslider ul.bxslider li img {min-height: 100%; min-width: 1600px; /* width: 100%; */ margin: 0 auto; box-shadow: 0 0 50px 0 rgba(0,0,0,0.8)} /* Uncomment the width for the image to stretch across the entire viewport. */
	div#heroslider .bx-wrapper .bx-has-controls-direction {position: absolute; top: 50%; left: 50%; width: 100%; max-width: 1100px; height: 32px;}
	    div#heroslider .bx-wrapper .bx-has-controls-direction .bx-controls-direction {position: relative; height: 32px; left: -50%;}
	        div#heroslider .bx-wrapper .bx-controls-direction a {top: 0; margin-top: 0; z-index: 100;}
</style>

<div class="container">
	<div id="heroslider" class="closed">
	    <div class="nebulashadow inner-top bulging" style="z-index: 2;"></div>
	    <ul class="heroslider bxslider">
	        <li>
	            <div>
	                <p class="line1">Lorem Ipsum</p>
	                <p class="line2">dolor sit amet, consectetur adipiscing elit.</p>
	                <a class="nebulaframe anchored-right" href="#" onclick="return false;">bibendum hendrerit sed</a>
	            </div>
	            <img class="random-unsplash" src="<?php echo random_unsplash(1600, 500); ?>" alt="Slide 1" />
	        </li>
	        <li>
	            <div>
	                <p class="line1">Nullam ex odio</p>
	                <p class="line2">luctus ac metus elementum, lacinia aliquam nisi.</p>
	                <a class="nebulaframe anchored-right" href="#" onclick="return false;">Aenean euismod justo</a>
	            </div>
	            <img class="random-unsplash" src="<?php echo random_unsplash(1600, 500); ?>" alt="Slide 2" />
	        </li>
	        <li>
	            <div>
	                <p class="line1">Cras at lectus a libero vestibulum</p>
	                <p class="line2">vulputate sollicitudin in diam Phasellus.</p>
	                <a class="nebulaframe anchored-right" href="#" onclick="return false;">ultricies sit amet </a>
	            </div>
	            <img class="random-unsplash" src="<?php echo random_unsplash(1600, 500); ?>" alt="Slide 3" />
	        </li>
	        <li>
	            <div>
	                <p class="line1">placerat commodo velit</p>
	                <p class="line2">sed ullamcorper magna volutpat sed.</p>
	                <a class="nebulaframe anchored-right" href="#" onclick="return false;">Nullam egestas faucibus</a>
	            </div>
	            <img class="random-unsplash" src="<?php echo random_unsplash(1600, 500); ?>" alt="Slide 4" />
	        </li>
	        <li>
	            <div>
	                <p class="line1">Aenean euismod justo</p>
	                <p class="line2">in augue cursus, ac bibendum ante vestibulum.</p>
	                <a class="nebulaframe anchored-right" href="#" onclick="return false;">Vivamus faucibus eget</a>
	            </div>
	            <img class="random-unsplash" src="<?php echo random_unsplash(1600, 500); ?>" alt="Slide 5" />
	        </li>
	    </ul>
	    <div class="nebulashadow inner-bottom bulging" style="z-index: 2;"></div>
	</div><!-- /heroslider -->
	<hr style="margin-bottom: 15px;"/>
</div><!--/container-->

<script>
	jQuery(window).on('load', function() {
		setTimeout(function(){
			jQuery('#heroslider').removeClass('closed');
		}, 1000);
	}); //End Window Load
</script>