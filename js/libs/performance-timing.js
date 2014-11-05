window.onload = function(){
	setTimeout(function(){
		if ( document.getElementsByTagName("html")[0].className.indexOf('lte-ie8') > -1 ) {
			return;
		}

		var pageSpeed = [];
		var values = [];
		var colors = [];

		if ( performance.navigation.redirectCount == 0 ) {
			pageSpeed[0] = 'Redirections: 0';
			colors[0] = 'lightgrey';
		} else {
			values[0] = (performance.timing.redirectEnd-performance.timing.redirectStart)/1000;
			pageSpeed[0] = 'Redirections: ' + performance.navigation.redirectCount + ' @ ' + values[0];
			colors[0] = (values[0] >= 0.05 || performance.navigation.redirectCount > 2 ? 'red' : 'black');
		}

		values[1] = (performance.timing.domainLookupStart-performance.timing.fetchStart)/1000;
		pageSpeed[1] = 'App Cache Time: ' + values[1];
		colorLogic(1, 0.5, 0);

		values[2] = (performance.timing.domainLookupEnd-performance.timing.domainLookupStart)/1000;
		pageSpeed[2] = 'DNS Time: ' + values[2];
		colorLogic(2, 0.05, 0.01);

		values[3] = (performance.timing.connectEnd-performance.timing.connectStart)/1000;
		pageSpeed[3] = 'TCP Time: ' + values[3];
		colorLogic(3, 0.05, 0.02);

		values[4] = (performance.timing.responseStart-performance.timing.requestStart)/1000;
		pageSpeed[4] = 'Request Time: ' + values[4];
		colorLogic(4, 0.5, 0.2);

		values[5] = (performance.timing.responseEnd-performance.timing.responseStart)/1000;
		pageSpeed[5] = 'Response Time: ' + values[5];
		colorLogic(5, 0.015, 0.003);

		values[6] = (performance.timing.domComplete-performance.timing.domLoading)/1000;
		pageSpeed[6] = 'DOM Time: ' + values[6];
		colorLogic(6, 2, 1);

		values[7] = (performance.timing.loadEventEnd-performance.timing.loadEventStart)/1000;
		pageSpeed[7] = 'onLoad Time: ' + values[7];
		colorLogic(7, 0.1, 0.03);

		values[8] = (performance.timing.loadEventEnd-performance.timing.navigationStart)/1000;
		pageSpeed[8] = 'Total Page Load Time: ' + values[8];
		colorLogic(8, 5, 2);

		console.group('******* PERFORMANCE TIMING RESULTS *******');
		if ( !!window.chrome || navigator.userAgent.toLowerCase().indexOf('firefox') > -1 || navigator.userAgent.toLowerCase().indexOf('safari') != -1 ) {
			for (var i = 0; i < pageSpeed.length; i++) {
				var perc = '%c';
				var css = 'color:' + colors[i] + ';';

				if ( colors[i] == 'red' ) {
					console.warn(perc + pageSpeed[i] + 's', css);
				} else {
					console.log(perc + pageSpeed[i] + 's', css);
				}
			}
		} else {
			if ( colors[i] == 'red' ) {
				console.warn(pageSpeed[i] + 's');
			} else {
				console.log(pageSpeed[i] + 's');
			}
		}
		console.groupEnd();

		function colorLogic(i, bad, good) {
			if ( values[i] >= bad ) {
				colors[i] = 'orange';
			} else if ( values[i] == 0 ) {
				colors[i] = 'lightgrey';
			} else if ( values[i] < good ) {
				colors[i] = 'green';
			} else {
				colors[i] = 'black';
			}
		}
	}, 0);
};