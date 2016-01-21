<script src="http://code.createjs.com/easeljs-0.7.1.min.js"></script>
<script src="http://code.createjs.com/tweenjs-0.5.1.min.js"></script>
<script src="http://code.createjs.com/movieclip-0.7.1.min.js"></script>

<script src="<?php echo get_template_directory_uri(); ?>/examples/components/html5_banner.js"></script>

<script>
	var canvas, stage, exportRoot;

	function init(){
		canvas = document.getElementById("canvas");
		exportRoot = new lib.Flash_HTML5_Test();

		stage = new createjs.Stage(canvas);
		stage.addChild(exportRoot);
		stage.update();
		stage.enableMouseOver();

		createjs.Ticker.setFPS(lib.properties.fps);
		createjs.Ticker.addEventListener("tick", stage);
	}

	jQuery(window).on('load', function(){
		init();
	});
</script>

<canvas id="canvas" width="300" height="250" style="background-color:#0099FF"></canvas>