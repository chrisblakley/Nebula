<style>
	h2.browsersupport,
	h2.serversupport {text-align: center; padding: 10px 15px; margin-bottom: 15px;}
</style>


<script>
	jQuery(document).ready(function() {

	});

	jQuery(window).on('load', function() {
			if ("WebSocket" in window) {

				jQuery('.browsersupport').text('WebSocket is supported by your Browser!').css('border', '1px solid green');

				// Let us open a web socket
				var ws = new WebSocket("ws://localhost:9998/echo");
				ws.onopen = function() {
					jQuery('.serversupport').addClass('success').text('WebSocket is properly configured on your sever.').css('border', '1px solid green');

					//Web Socket is connected, send data using send()
					ws.send("Message to send");
					console.log("Message is sent...");
				};

				ws.onmessage = function(evt) {
					var received_msg = evt.data;
					console.log("Message is received...");
				};

				ws.onclose = function() {
					if ( !jQuery('.serversupport').hasClass('success') ) {
						jQuery('.serversupport').text('WebSocket is not configured on your sever.').css('border', '1px solid red');
					}

					//websocket is closed.
					console.log("Connection is closed...");
				};
			} else { //The browser doesn't support WebSocket
				jQuery('.browsersupport').text('WebSocket NOT supported by your Browser!').css('border', '1px solid red');
			}
	});
</script>


<div class="row">
	<div class="col-md-12">

		<h2 class="browsersupport">Detecting browser support...</h2>
		<h2 class="serversupport">Detecting server connection...</h2>
		<p>This instance of the Websocket API is listening on port 9998. To install websockets, download <a href="https://code.google.com/p/pywebsocket/" target="_blank">pywebsocket</a> and install on your server.</p>

	</div><!--/col-->
</div><!--/row-->