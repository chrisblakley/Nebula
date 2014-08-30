<script>
	var sandboxSupported = "sandbox" in document.createElement("iframe");
	console.log(sandboxSupported);
</script>

<style>
	.iframeswrap iframe {background-color: none !important;}
	.iframeswrap iframe h1, .iframeswrap iframe p {color: white;}
	.iframeswrap iframe a {color: green;}
</style>

<div class="iframeswrap">
	<h3>Standard Iframe:</h3>
	<iframe src="<?php echo get_template_directory_uri(); ?>/examples/includes/seamless.html" style="width: 100%;"></iframe>
	<br/>
	<hr/>
	<br/>
	<h3>Seamless Iframe:</h3>
	<iframe src="<?php echo get_template_directory_uri(); ?>/examples/includes/seamless.html" seamless style="width: 100%;"></iframe>
</div>