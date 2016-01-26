/** BEGIN Nebula Banner GA Tracking **/
(function(){
	window.onload = function(){
		var gaTrackingID = 'UA-36461517-2'; //Ex: 'UA-00000000-2'
		var bannerID = 'Nebula "Example" Medium Rectangle (300x250)'; //Ex: 'Gearside "Click Here" Leaderboard (728x90)' //Project, Campaign/Version, Size (Dimensions)

		var bannerCanvas = document.getElementById('canvas');
		var viewFlag = false;

		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		ga('create', gaTrackingID, 'auto', {'name': 'banner'});

		//Send "Load" when banner is loaded.
		ga('banner.send', 'event', 'External Banner Events', bannerID, 'Load', {'nonInteraction': 1});
		console.log('Sent "Load" event for banner: ' + bannerID + ' to: ' + gaTrackingID); //@TODO: Remove this line for real banners
		bannerInView();

		//When banner is inside the viewport, send "View" (only once per load)
		document.onscroll = function(){
			bannerInView();
		};
		function bannerInView(){
			var viewportView = (window.scrollY+window.innerHeight);
			var bannerOffset = (bannerCanvas.offsetTop+bannerCanvas.height);
			if ( (viewportView-bannerOffset) >= 0 && (window.scrollY+bannerCanvas.height) <= bannerOffset && !viewFlag ){
				ga('banner.send', 'event', 'External Banner Events', bannerID, 'View', {'nonInteraction': 1});
				console.log('Sent "View" event for banner: ' + bannerID + ' to: ' + gaTrackingID); //@TODO: Remove this line for real banners
				viewFlag = true;
			}
		}

		//On click of the overall banner
		bannerCanvas.onclick = function(){
			ga('banner.send', 'event', 'External Banner Events', bannerID, 'Click');
			console.log('Sent "Click" event for banner: ' + bannerID + ' to: ' + gaTrackingID); //@TODO: Remove this line for real banners
		}

		//On hover of the overall banner
		bannerCanvas.onmouseover = function(){
			var hoverDelay = setTimeout(function(){
				ga('banner.send', 'event', 'External Banner Events', bannerID, 'Hover', {'nonInteraction': 1});
				console.log('Sent "Hover" event for banner: ' + bannerID + ' to: ' + gaTrackingID); //@TODO: Remove this line for real banners
			}, 1000);
		}
	}
})();
/** END Nebula Banner GA Tracking **/













(function (lib, img, cjs) {

var p; // shortcut to reference prototypes

// library properties:
lib.properties = {
	width: 300,
	height: 250,
	fps: 30,
	color: "#0099FF",
	manifest: []
};



// symbols:



(lib.replay_btn = function() {
	this.initialize();

	// Layer 1
	this.replay_txt = new cjs.Text("REPLAY", "24px 'Arial'", "#FFFFFF");
	this.replay_txt.name = "replay_txt";
	this.replay_txt.textAlign = "center";
	this.replay_txt.lineHeight = 26;
	this.replay_txt.lineWidth = 193;
	this.replay_txt.setTransform(-1.8,-11);

	this.shape = new cjs.Shape();
	this.shape.graphics.f("rgba(0,0,0,0.247)").s().p("A3bTiMAAAgnDMAu3AAAMAAAAnDg");

	this.addChild(this.shape,this.replay_txt);
}).prototype = p = new cjs.Container();
p.nominalBounds = new cjs.Rectangle(-150,-125,300,250);


(lib.outerRing_mc = function() {
	this.initialize();

	// Layer 1
	this.shape = new cjs.Shape();
	this.shape.graphics.f().s("#FFFFFF").ss(2,1,1).p("AWgAAQAAkkhxkLQhukCjIjIQjGjHkDhuQkMhxkkAAQkjAAkMBxQkDBujHDHQjHDIhuECQhxELAAEkQAAElBxELQBuEDDHDHQDHDHEDBuQEMBxEjAAQEkAAEMhxQEDhuDGjHQDIjHBukDQBxkLAAklg");
	this.shape.setTransform(0,0,0.75,0.75);

	this.addChild(this.shape);
}).prototype = p = new cjs.Container();
p.nominalBounds = new cjs.Rectangle(-109,-109,218,218);


(lib.line_mc = function() {
	this.initialize();

	// Layer 1
	this.shape = new cjs.Shape();
	this.shape.graphics.f().s("#FFFFFF").ss(2,1,1).p("AnGAAIONAA");
	this.shape.setTransform(34.1,0,0.75,0.75);

	this.addChild(this.shape);
}).prototype = p = new cjs.Container();
p.nominalBounds = new cjs.Rectangle(-1,-1,70.3,2);


(lib.innerRing_mc = function() {
	this.initialize();

	// Layer 1
	this.shape = new cjs.Shape();
	this.shape.graphics.f().s("#FFFFFF").ss(5,1,1).p("AVMAAQAAkThqj8Qhnjzi8i8Qi8i8jzhnQj8hqkUAAQkSAAj8BqQj0Bni8C8Qi8C8hnDzQhqD8AAETQAAETBqD8QBnD0C8C8QC8C8D0BnQD8BqESAAQEUAAD8hqQDzhnC8i8QC8i8Bnj0QBqj8AAkTg");
	this.shape.setTransform(0,0,0.75,0.75);

	this.addChild(this.shape);
}).prototype = p = new cjs.Container();
p.nominalBounds = new cjs.Rectangle(-104.2,-104.2,208.5,208.5);


(lib.clipboard_mc = function() {
	this.initialize();

	// Layer 1
	this.shape = new cjs.Shape();
	this.shape.graphics.f().s("#FFFFFF").ss(2,1,1).p("AGktHIDSAAIAAaPIzrAAIAA6PIDwAA");
	this.shape.setTransform(-1.1,7.3,0.75,0.75);

	this.shape_1 = new cjs.Shape();
	this.shape_1.graphics.f().s("#FFFFFF").ss(2,1,1).p("AA0AAQAAgUgQgPQgPgPgVAAQgUAAgPAPQgQAPAAAUQAAAVAQAQQAPAPAUAAQAVAAAPgPQAQgQAAgVg");
	this.shape_1.setTransform(0,-70.4,0.75,0.75);

	this.shape_2 = new cjs.Shape();
	this.shape_2.graphics.f().s("#FFFFFF").ss(2,1,1).p("AFECvIA+iWIiQAAIhMjHIlLAAIhMDHIiQAAIA+CWg");
	this.shape_2.setTransform(0,-64.3,0.75,0.75);

	this.shape_3 = new cjs.Shape();
	this.shape_3.graphics.f().s("#FFFFFF").ss(2,1,1).p("Am/upIiIgBQg8AAgrArQgrArAAA8IAAYxQAAA8ArArQArAqA8AAISPAAQA8AAArgqQArgrAAg8IAA4xQAAg8grgrQgrgrg8AAIiIAB");
	this.shape_3.setTransform(0,7,0.75,0.75);

	this.addChild(this.shape_3,this.shape_2,this.shape_1,this.shape);
}).prototype = p = new cjs.Container();
p.nominalBounds = new cjs.Rectangle(-55.7,-78.4,111.6,156.9);


// stage content:
(lib.Flash_HTML5_Test = function(mode,startPosition,loop) {
	this.initialize(mode,startPosition,loop,{intro:0});

	// timeline functions:
	this.frame_61 = function() {
		self = this;
		self.stop();

		this.replay_btn.on("click", myClickReaction);

		function myClickReaction (){
			//ga('banner.send', 'event', bannerID, 'Click', 'Label Goes Here');
			//console.log('Sent "Click" event for banner: ' + bannerID + ' to: ' + gaTrackingID);

			self.gotoAndPlay("intro");
		}
	}

	// actions tween:
	this.timeline.addTween(cjs.Tween.get(this).wait(61).call(this.frame_61).wait(1));

	// Button
	this.replay_btn = new lib.replay_btn();
	this.replay_btn.setTransform(150,125);
	this.replay_btn._off = true;
	new cjs.ButtonHelper(this.replay_btn, 0, 1, 1);

	this.timeline.addTween(cjs.Tween.get(this.replay_btn).wait(61).to({_off:false},0).wait(1));

	// Line 9
	this.instance = new lib.line_mc();
	this.instance.setTransform(116.5,173.3,0.02,1);
	this.instance._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance).wait(31).to({_off:false},0).to({scaleX:1},19,cjs.Ease.get(1)).wait(12));

	// Line 8
	this.instance_1 = new lib.line_mc();
	this.instance_1.setTransform(116.5,162.1,0.02,1);
	this.instance_1._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_1).wait(30).to({_off:false},0).to({scaleX:1},17,cjs.Ease.get(1)).wait(15));

	// Line 7
	this.instance_2 = new lib.line_mc();
	this.instance_2.setTransform(116.5,151.6,0.02,1);
	this.instance_2._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_2).wait(29).to({_off:false},0).to({scaleX:1},15,cjs.Ease.get(1)).wait(18));

	// Line 6
	this.instance_3 = new lib.line_mc();
	this.instance_3.setTransform(116.5,140.3,0.02,1);
	this.instance_3._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_3).wait(28).to({_off:false},0).to({scaleX:1},14,cjs.Ease.get(1)).wait(20));

	// Line 5
	this.instance_4 = new lib.line_mc();
	this.instance_4.setTransform(116.5,129.1,0.02,1);
	this.instance_4._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_4).wait(27).to({_off:false},0).to({scaleX:1},14,cjs.Ease.get(1)).wait(21));

	// Line 4
	this.instance_5 = new lib.line_mc();
	this.instance_5.setTransform(116.5,117.8,0.02,1);
	this.instance_5._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_5).wait(26).to({_off:false},0).to({scaleX:1},14,cjs.Ease.get(1)).wait(22));

	// Line 3
	this.instance_6 = new lib.line_mc();
	this.instance_6.setTransform(116.5,107.3,0.02,1);
	this.instance_6._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_6).wait(25).to({_off:false},0).to({scaleX:1},14,cjs.Ease.get(1)).wait(23));

	// Line 2
	this.instance_7 = new lib.line_mc();
	this.instance_7.setTransform(116.5,96.1,0.02,1);
	this.instance_7._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_7).wait(24).to({_off:false},0).to({scaleX:1},14,cjs.Ease.get(1)).wait(24));

	// Line 1
	this.instance_8 = new lib.line_mc();
	this.instance_8.setTransform(116.5,85.6,0.02,1);
	this.instance_8._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_8).wait(23).to({_off:false},0).to({scaleX:1},14,cjs.Ease.get(1)).wait(25));

	// Clipboard
	this.instance_9 = new lib.clipboard_mc();
	this.instance_9.setTransform(150.5,121,0.02,0.02);
	this.instance_9._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_9).wait(8).to({_off:false},0).to({scaleX:1,scaleY:1},20,cjs.Ease.get(1)).wait(34));

	// Inner Ring
	this.instance_10 = new lib.innerRing_mc();
	this.instance_10.setTransform(150.5,125,0.02,0.02);
	this.instance_10._off = true;

	this.timeline.addTween(cjs.Tween.get(this.instance_10).wait(3).to({_off:false},0).to({scaleX:1,scaleY:1},23,cjs.Ease.get(1)).wait(36));

	// Outer Ring
	this.instance_11 = new lib.outerRing_mc();
	this.instance_11.setTransform(150.5,125,0.02,0.02);

	this.timeline.addTween(cjs.Tween.get(this.instance_11).to({scaleX:1,scaleY:1},23,cjs.Ease.get(1)).wait(39));

}).prototype = p = new cjs.MovieClip();
p.nominalBounds = new cjs.Rectangle(298.3,247.8,4.4,4.4);

})(lib = lib||{}, images = images||{}, createjs = createjs||{});
var lib, images, createjs;