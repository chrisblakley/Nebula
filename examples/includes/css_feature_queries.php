<style>
	.supportdiv {background: red; color: #fff; width: 100%; height: 150px; text-align: center;}
	
	@supports ( box-shadow: 2px 2px 2px black ) or
	          ( -moz-box-shadow: 2px 2px 2px black ) or
	          ( -webkit-box-shadow: 2px 2px 2px black ) or
	          ( -o-box-shadow: 2px 2px 2px black ) {
		.supportdiv {background: green; -moz-box-shadow: 2px 2px 2px black; -webkit-box-shadow: 2px 2px 2px black; -o-box-shadow: 2px 2px 2px black; box-shadow: 2px 2px 2px black;}
	}
</style>

<div class="supportdiv">If this has a drop shadow, feature queries are supported!</div>