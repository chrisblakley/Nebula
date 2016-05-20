<style>
	#heroslider {margin-top: -30px;} /* Do not copy over this line - it is for this example page only! */


	div#heroslider {position: relative; height: 500px; overflow: hidden; -webkit-transition: all 1s; -moz-transition: all 1s; -o-transition: all 1s; transition: all 1s;}
		div#heroslider.closed {height: 0;}
            div#heroslider #example-carousel {margin: 0; background: #222 url("http://www.transparenttextures.com/patterns/black-linen.png"); height: 100%;}
				div#heroslider #example-carousel .carousel-item {}

                    div#heroslider #example-carousel .carousel-item div {position: absolute; top: 25%; left: 50%; width: 70%; max-width: 940px;}
                        div#heroslider #example-carousel .carousel-item div p {display: table; position: relative; margin-left: -50%; padding: 10px 30px; color: #000; text-align: center; text-transform: uppercase; margin-bottom: 5px; background: #fff; background: rgba(255, 255, 255, 0.9); box-shadow: 1px 1px 3px 0 rgba(0, 0, 0, 0.2);}
	                        div#heroslider #example-carousel .carousel-item div p.line1 {font-size: 48px;}
	                        div#heroslider #example-carousel .carousel-item div p.line2 {font-size: 24px;}
                        div#heroslider #example-carousel .carousel-item div a {display: inline-block; position: relative; margin-left: -50%; padding: 10px 30px; color: #fff; font-size: 24px; text-align: center; text-transform: uppercase; background: #0098d7;}
                            div#heroslider #example-carousel .carousel-item div a:hover,
                            div#heroslider #example-carousel .carousel-item div a:focus {background: #95d600;}

                        div#heroslider #example-carousel img {margin: 0 auto; height: 100%; /* width: 100%; */ box-shadow: 0 0 50px 0 rgba(0, 0, 0, 0.8);} /* Set width to 100% to stretch to fit */
</style>

<div>
	<div id="heroslider" class="closed">
	    <div class="nebulashadow inner-top bulging" style="z-index: 2;"></div>
	    <div id="example-carousel" class="carousel slide" data-ride="carousel">
			<ol class="carousel-indicators">
				<li data-target="#example-carousel" data-slide-to="0" class="active"></li>
				<li data-target="#example-carousel" data-slide-to="1"></li>
				<li data-target="#example-carousel" data-slide-to="2"></li>
			</ol>

			<div class="carousel-inner">
				<div class="carousel-item active">
					<div>
						<p class="line1">Lorem ipsum</p>
						<p class="line1">dolor sit amet</p>
						<a class="nebulaframe anchored-right" href="#" onclick="return false;">bibendum hendrerit sed</a>
					</div>
					<img src="<?php echo unsplash_it(1600, 500); ?>">
				</div>
				<div class="carousel-item">
					<div>
						<p class="line1">Lorem ipsum</p>
						<p class="line1">dolor sit amet</p>
						<a class="nebulaframe anchored-right" href="#" onclick="return false;">bibendum hendrerit sed</a>
					</div>
					<img src="<?php echo unsplash_it(1600, 500); ?>">
				</div>
				<div class="carousel-item">
					<div>
						<p class="line1">Lorem ipsum</p>
						<p class="line1">dolor sit amet</p>
						<a class="nebulaframe anchored-right" href="#" onclick="return false;">bibendum hendrerit sed</a>
					</div>
					<img src="<?php echo unsplash_it(1600, 500); ?>">
				</div>
			</div>

			<a class="left carousel-control" href="#example-carousel" data-slide="prev">
				<span class="icon-prev"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="right carousel-control" href="#example-carousel" data-slide="next">
				<span class="icon-next"></span>
				<span class="sr-only">Next</span>
			</a>
		</div>
	    <div class="nebulashadow inner-bottom bulging" style="z-index: 2;"></div>
	</div><!-- /heroslider -->
	<hr style="margin-bottom: 15px;"/>
</div>

<script>
	jQuery(window).on('load', function() {
		setTimeout(function(){
			jQuery('#heroslider').removeClass('closed');
		}, 1000);
	}); //End Window Load
</script>