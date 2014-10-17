<style>
	/* Do not copy over these styles - they are for this example page only! */
	#heroslider {margin-top: -30px;}
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