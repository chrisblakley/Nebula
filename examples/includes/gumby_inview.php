<style>
	div.inview {background: #eee; margin-bottom: 400px; padding: 40px 0; -webkit-transition: all 0.5s; -moz-transition: all 0.5s; -o-transition: all 0.5s; transition: all 0.5s;}

	div.example1 {text-align: center; color: white;}
		div.example1.active {background: #3085d6;}


	div.example2 {text-align: center; color: white;}
		div.example2.onscreen {background: orange;}


	div.example3 {text-align: center; color: white;}
		div.example3.onscreen {background: #b83d78;}
		div.example3.offbottom {background: #440000;}
		div.example3.offtop {background: #1b0044;}
</style>

<div class="row">
	<div class="sixteen columns">
		<p>Scroll down!</p>
	</div>
</div><!--/row-->

<div class="row">
	<div class="sixteen columns">
		<div class="inview example1">
			<p style="font-size: 36px;">Active</p>
			<p>This div will get an active class once it is in view because it does not have declared classes (this class will remain after it is out of view).</p>
		</div>

		<div class="inview example2" gumby-classname="onscreen" gumby-offset="50">
			<p style="font-size: 36px;">Onscreen</p>
			<p>This div will get an onscreen class when it is in view, and lose it when it is out of view.</p>
		</div>

		<div class="inview example3" gumby-classname="onscreen|offbottom|offtop" gumby-offset="100">
			<p style="font-size: 36px;">Full</p>
			<p>This div starts with an "offbottom" class and switches to an "onscreen" when in view. Once it is scrolled passed, it get an "offtop" class.</p>
		</div>
	</div>
</div><!--/row-->