<style>
div.cssbs {position: relative; display: table; height: 150px; min-width: 300px; border: 2px solid #222;}
	div.cssbs:after {width: 100%; height: 100%; line-height: 28px; color: #fff; text-align: center; font-family: 'FontAwesome', 'Open Sans', sans-serif; font-size: 16px; white-space: pre; display: table-cell; vertical-align: middle;}
	div.cssbs:before {content: ''; position: absolute; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.2));}

	.windows.chrome div.cssbs {background: #4884b8;} .windows.chrome div.cssbs:after {content: '\f17a \00A0 Windows \A \f268 \00A0 Chrome';}
	.mac.chrome div.cssbs {background: #4884b8;} .mac.chrome div.cssbs:after {content: '\f179 \00A0 Mac \A \f179 \f268 Chrome';}
	.linux.chrome div.cssbs {background: #4884b8;} .linux.chrome div.cssbs:after {content: '\f17c \00A0 Linux \A \f268 \00A0 Chrome';}

	.windows.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .windows.firefox div.cssbs:after {content: '\f17a \00A0 Windows \A \f269 \00A0 Firefox';}
	.mac.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .mac.firefox div.cssbs:after {content: '\f179 \00A0 Mac \A \f269 \00A0 Firefox';}
	.linux.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .linux.firefox div.cssbs:after {content: '\f17c \00A0 Linux \A \f269 \00A0 Firefox';}

	.mac.safari div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .mac.safari div.cssbs:after {content: '\f179 \00A0 Mac \A \f267 \00A0 Safari';}
	.windows.safari div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .windows.safari div.cssbs:after {content: '\f17a \00A0 Windows \A \f267 \00A0 Safari';}

	.opera div.cssbs {background: #e53141; border: 2px solid #9b1624;} .opera div.cssbs:after {content: '\f26a \00A0 Opera';}

	.ie div.cssbs {background: #2ebaee;} .ie div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer';}
	.ie5 div.cssbs {background: #3ea3e2;} .ie5 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 5';}
	.ie6 div.cssbs {background: #3696e9; border: 2px solid #72f0fc;} .ie6 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 6';}
	.ie7 div.cssbs {background: #1374ae; border: 2px solid #f4b619;} .ie7 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 7';}
	.ie8 div.cssbs {background: #1374ae; border: 2px solid #f4b619;} .ie8 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 8';}
	.ie9 div.cssbs {background: #3aa8de; border: 2px solid #fbd21e;} .ie9 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 9';}
	.ie10 div.cssbs {background: #2b6bec;} .ie10 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 10';}
	.ie11 div.cssbs {background: #2ebaee;} .ie11 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 11';}

	.edge div.cssbs {background: #2ebaee;} .edge div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Microsoft Edge';}

	.android div.cssbs {background: #a5c93a; border: 2px solid #a5c93a;} .android div.cssbs:after {content: '\f17b \00A0 Android';}
	.android.chrome div.cssbs {background: #4884b8;} .android.chrome div.cssbs:after {content: '\f17b \00A0 Android \A \f268 \00A0 Chrome';}

	.ios div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .ios div.cssbs:after {content: '\f179 \00A0 iOS';}
	.iphone div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .iphone div.cssbs:after {content: '\f179 \00A0 iPhone';}
	.iphone.chrome div.cssbs {background: #4884b8;} .iphone.chrome div.cssbs:after {content: '\f179 \00A0 iPhone \A \f179 \f268 Chrome';}
	.ipad div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .ipad div.cssbs:after {content: '\f179 \00A0 iPad';}
	.ipad.chrome div.cssbs {background: #4884b8;} .ipad.chrome div.cssbs:after {content: '\f179 \00A0 iPad \A \f179 \f268 Chrome';}
</style>

<div class="row">
	<div class="six columns">
		<div class="cssbs"></div>
	</div><!--/columns-->
</div><!--/row-->