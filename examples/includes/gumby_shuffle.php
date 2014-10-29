<style>
	.testbox {text-align: center; margin-bottom: 15px;}
		.textbox1 {background: #b58b30;}
		.textbox2 {background: #6464ed;}
		.textbox3 {background: #339e44;}
		.testbox h6 {color: #fff; font-weight: bold; font-size: 24px; padding: 40px 0;}
</style>

<div class="twelve colgrid">
	<div class="container">
		<div class="row">
			<div class="twelve columns">
				<p>The following columns will change order at various viewport widths.</p>
			</div>
		</div><!--/row-->
		
		<div class="row" gumby-shuffle="only screen and (max-width: 860px) and (min-width: 768px)|2-1-0,only screen and (max-width: 767px)|1-0-2">
			<div class="four columns testbox textbox1">
				<h6>1</h6>
			</div>
			<div class="four columns testbox textbox2">
				<h6>2</h6>
			</div>
			<div class="four columns testbox textbox3">
				<h6>3</h6>
			</div>
		</div><!--/row-->
	</div><!--/container-->
</div><!--/colgrid-->