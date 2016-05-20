<div id="example-carousel" class="carousel slide" data-ride="carousel"><!-- Use the class "auto-indicators" to automatically add the <ol> indicators. -->
	<ol class="carousel-indicators">
		<li data-target="#example-carousel" data-slide-to="0" class="active"></li>
		<li data-target="#example-carousel" data-slide-to="1"></li>
		<li data-target="#example-carousel" data-slide-to="2"></li>
	</ol>

	<div class="carousel-inner">
		<div class="carousel-item active">
			<img src="<?php echo unsplash_it(800, 400); ?>">
		</div>
		<div class="carousel-item">
			<img src="<?php echo unsplash_it(800, 400); ?>">
		</div>
		<div class="carousel-item">
			<img src="<?php echo unsplash_it(800, 400); ?>">
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