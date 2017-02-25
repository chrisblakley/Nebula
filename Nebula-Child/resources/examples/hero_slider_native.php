<style>
	#theslider {transition: all .5s ease 0s;}
	#theslider .sliderwrap {position: relative; overflow: hidden;}

		#theslider .status {position: absolute; display: block; width: 100px; top: 5px; right: 5px; background: rgba(0,0,0,0.4); text-align: center; color: #fff; text-decoration: none; border-radius: 25px; z-index: 1500; cursor: default; opacity: 0; -webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
		.no-js #theslider .status {display: none;}
			#theslider .status.pause {opacity: 1; pointer-events: none;}
			#theslider:hover .status.stop {opacity: 1;}
				#theslider .status.stop:hover,
				#theslider .status.stop.hover {cursor: pointer; background: rgba(0,0,0,0.7);}

	    #theslider .slider-arrow {position: relative; display: inline-block; color: #fff;}
	    .no-js #theslider .slider-arrow {display: none;}

	ul#theslides {position: relative; overflow: hidden; margin: 0; padding: 0;}
	    ul#theslides li {position: absolute; top: 0; left: 0; width: 100%; height: auto; margin-bottom: -7px; /* Don't like this margin... */ opacity: 0; z-index: 0; transition: all 1s ease 0s;}
	        ul#theslides li.active {position: relative; opacity: 1; z-index: 500;}

	.no-js .slider-nav-con {display: none;}
	.slider-nav-con {position: absolute; bottom: -50px; width: 100%; background: rgba(0,0,0,0.7); z-index: 1000; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
	    #slider-nav {position: relative; display: table; margin: 0 auto;}
	        #slider-nav li {display: inline-block; margin-right: 15px; padding: 0; text-align: center; vertical-align: middle;}
	            #slider-nav li:last-child,
	            #slider-nav li.last-child {margin-right: 0;}
	            #slider-nav li a {display: table-cell; vertical-align: middle; padding: 5px 0; position: relative; height: 100%; color: #fff;}
	            	#slider-nav li a:hover {color: #aaa;}
	                #slider-nav li.active a {color: #fff; font-weight: bold;}
	                	#slider-nav li.active a:hover {color: #aaa;}
</style>

<div id="theslider" class="container nebulaframe">
    <div class="row">
        <div class="col-md-12 sliderwrap">

            <a href="#" class="status">
            	<i class="fa fa-pause"></i> <span>Paused</span>
            </a><!--/status-->

            <ul id="theslides">
                <li><img src="http://www.placebear.com/700/300"/></li>
                <li><img src="http://www.placebear.com/700/400"/></li>
                <li><img src="http://placehold.it/700x500"/></li>
            </ul>

            <div class="slider-nav-con">
                <ul id="slider-nav" class="clearfix">
                    <li><a class="slider-arrow slider-left " href="#"><i class="fa fa-chevron-left"></i></a></li>
                    <li class="slide-nav-item"><a href="#">One</a></li>
                    <li class="slide-nav-item"><a href="#">Two</a></li>
                    <li class="slide-nav-item"><a href="#">Three</a></li>
                    <li><a class="slider-arrow slider-right " href="#"><i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </div><!--/slider-nav-con-->

        </div><!--/col-->
    </div><!--/row-->
</div><!--/container-->

<script>
	jQuery(document).ready(function() {

	    strictPause = 0;
	    autoSlider();
		jQuery("#theslides li").eq(0).addClass("active");
		jQuery("#slider-nav li.slide-nav-item").eq(0).addClass("active");

	    function autoSlider() {
	        autoSlide = setInterval(function(){
	            theIndex = jQuery("#theslides li.active").index();
	            if ( strictPause == 0 ) {
	                activateSlider(theIndex, "next");
	            }
	        }, 5000);
	    } //End autoSlider()

		jQuery("#theslider").hover(function(){
	        clearInterval(autoSlide);
	        jQuery("#slider-nav").addClass("pause");
	        if ( !jQuery(".status").hasClass("stop") ) {
	        	jQuery(".status i").removeClass("fa fa-stop fa fa-play").addClass("fa fa-pause");
				jQuery(".status span").text("Paused");
		        jQuery(".status").addClass("pause");
	        }
	    }, function(){
	        if ( strictPause == 0 ) {
	            autoSlider();
	            jQuery("#slider-nav").removeClass("pause");
	            jQuery(".status").removeClass("pause");
	        }
	    });

	    //Navigation
	    jQuery("#slider-nav li.slide-nav-item a").on("click", function(){
	        strictPause = 1;
	        jQuery(".status i").removeClass("fa fa-pause").addClass("fa fa-stop");
	        jQuery(".status").removeClass("pause").addClass("stop").find("span").text("Stopped");
	        jQuery("#slider-nav").removeClass("pause").addClass("stop");
	        theIndex = jQuery(this).parent().index();
	        activateSlider(theIndex-1, "goto");
	        return false;
	    });

		//Status
		jQuery("#theslider").on("mouseenter", ".status.stop", function(){
			jQuery(this).find("i").removeClass("fa fa-stop").addClass("fa fa-play");
			jQuery(this).find("span").text("Resume");
		});
		jQuery("#theslider").on("mouseleave", ".status.stop", function(){
			jQuery(this).find("i").removeClass("fa fa-play").addClass("fa fa-stop");
			jQuery(this).find("span").text("Stopped");
		});
		jQuery("#theslider").on("click", ".status.stop", function(){
			strictPause = 0;
			jQuery("#slider-nav").removeClass("stop");
	        jQuery(".status").removeClass("pause stop");
	        return false;
		});

	    //Arrows
	    jQuery(".slider-arrow").on("click", function(){
	        strictPause = 1;
	        jQuery(".status i").removeClass("fa fa-pause").addClass("fa fa-stop");
	        jQuery(".status").addClass("stopped").find("span").text("Stopped");
	        jQuery("#slider-nav").removeClass("pause").addClass("stop");
	        jQuery("#slider-nav").removeClass("pause").addClass("stop");
	        theIndex = jQuery("#theslides li.active").index();
	        if ( jQuery(this).hasClass("slider-right") ) {
	            activateSlider(theIndex, "next");
	        } else {
	            activateSlider(theIndex, "prev");
	        }
	        return false;
	    });

	    function activateSlider(theIndex, buttoned) {
	        slideCount = jQuery("#theslides li").length;
	        activeHeight = jQuery("#theslides li.active img").height();

	        if ( buttoned == "next" ) {
	            newIndex = ( theIndex+1 >= slideCount ? 0 : theIndex+1 );
	        } else if ( buttoned == "prev" ) {
	            newIndex = ( theIndex-1 <= -1 ? slideCount-1 : theIndex-1 );
	        } else {
	            newIndex = theIndex;
	        }

			nextHeight = jQuery("#theslides li").eq(newIndex).find("img").height();

			jQuery("#theslides li.active").removeClass("active");
		    jQuery("#slider-nav li.slide-nav-item.active").removeClass("active");

		    jQuery("#theslides li").eq(newIndex).addClass("active");
		    jQuery("#slider-nav li.slide-nav-item").eq(newIndex).addClass("active");

			if ( nextHeight >= activeHeight ) {
				console.log("delaying then resizing");
				jQuery("#theslides").delay(500).animate({ //delay will be calculated based on transition speed
					height: nextHeight,
				}, 500, "easeInOutCubic"); //resize speed will be calculated based on transition speed
			} else {
				console.log("just resizing");
				jQuery("#theslides").animate({
					height: nextHeight,
				}, 500, "easeInOutCubic"); //resize speed will be calculated based on transition speed
			}
	    } //End activateSlider()

    }); //End Document Ready

    jQuery(window).on("load", function() {
	    jQuery(".slider-nav-con").css("bottom", "0");
    }); //End Window Load
</script>

<?php if ( 1==2 ) : //Old Slider (but still has good carriage) ?>
	<style>
		div.nebula-slider {position: relative; overflow: hidden;}

			ul.nebula-slide-con.reset {position: relative; width: 300%; /* 300% will be calculated in PHP */ left: 0; margin: 0;}
				ul.nebula-slide-con.reset li.nebula-slide {position: relative; display: inline-block; width: 33.334%; /* width will be calculated in PHP */ margin: 0; padding: 0; float: left;}
					ul.nebula-slide-con.reset li.nebula-slide img {width: 100%;}

			ul.nebula-slide-con.fade {position: relative; width: 100%; left: 0; margin: 0; height: 0px;}
				ul.nebula-slide-con.fade li.nebula-slide {position: absolute; top: 0; display: block; width: 100%; margin: 0; padding: 0;}
					ul.nebula-slide-con.fade li.nebula-slide img {width: 100%;}
	</style>

	<script>
		//@TODO "Nebula" 0: All selectors and variables MUST be unique to that slider (have an ID as a required parameter)

		jQuery(document).ready(function() {
			jQuery('ul.nebula-slide-con.fade li:nth-last-child(2)').addClass('next'); //nth-child number will have to be calculated via PHP (total-1) [or do css nth from end]
			jQuery('ul.nebula-slide-con.fade li:last-child').addClass('active');
		});

		jQuery(window).on('load', function() {
			var nebulaSlideCount = 3; //this number will be sent via PHP counting the slides
			var currentSlide = 1;

			var activeHeight = jQuery('ul.nebula-slide-con.fade li:last-child').height();
			jQuery('ul.nebula-slide-con.fade').css('height', activeHeight);

			if (nebulaSlideCount > 1) {
				var nebulaSlider = setInterval(function(){

					//@TODO "Nebula" 0: Only the chosen mode will actually return to the frontend

					//With carriage mode animation to first frame
					if ( currentSlide < nebulaSlideCount ) {
						jQuery('ul.nebula-slide-con.reset').animate({
							left: '-=100%',
						}, 1000, 'easeInOutCubic', function() { //easing can be a parameter, same with transition speed
							currentSlide++;
						});
					} else {
						jQuery('ul.nebula-slide-con.reset').animate({
							left: '0',
						}, 1000, 'easeInOutCubic', function() { //easing can be a parameter, same with transition speed
							currentSlide = 1;
						});
					}

					//Just keeps going using fade mode
					jQuery('ul.nebula-slide-con.fade li:last-child').fadeOut(1000, function(){ //transition speed will be a parameter
						jQuery('ul.nebula-slide-con.fade li.next').removeClass('next');
						jQuery('ul.nebula-slide-con.fade li.active').removeClass('active');
						jQuery(this).clone().prependTo('ul.nebula-slide-con.fade');
						jQuery(this).remove();
						jQuery('ul.nebula-slide-con.fade li:first-child').css('display', 'block');
						jQuery('ul.nebula-slide-con.fade li:last-child').addClass('active');
						jQuery('ul.nebula-slide-con.fade li:nth-child(2)').addClass('next'); //nth-child number will have to be calculated via PHP (total-1)
					});

					activeHeight = jQuery('ul.nebula-slide-con.fade li.nebula-slide.active img').height();
					nextHeight = jQuery('ul.nebula-slide-con.fade li.nebula-slide.next img').height();
					if ( nextHeight >= activeHeight ) {
						jQuery('ul.nebula-slide-con.fade').delay(500).animate({ //delay will be calculated based on transition speed
							height: nextHeight,
						}, 500, 'easeInOutCubic'); //resize speed will be calculated based on transition speed
					} else {
						jQuery('ul.nebula-slide-con.fade').animate({
							height: nextHeight,
						}, 500, 'easeInOutCubic'); //resize speed will be calculated based on transition speed
					}

				}, 5000); //Slide time will be a parameter
			}
		});
	</script>

	<div class="row">
		<div class="col-md-12">
			<div class="nebulaframe">
				<div class="nebula-slider">
					<ul class="nebula-slide-con clearfix fade">
						<li class="nebula-slide clearfix">
							<img src="http://www.placebear.com/700/300"/>
						</li>
						<li class="nebula-slide clearfix">
							<img src="http://www.placebear.com/700/400"/>
						</li>
						<li class="nebula-slide clearfix">
							<img src="http://placehold.it/700x500"/>
						</li>
					</ul>
				</div>
			</div>
		</div><!--/col-->
	</div><!--/row-->
<?php endif; //End Slider ?>