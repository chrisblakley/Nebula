window.onload = function(){
	setTimeout(function(){
		var pageSpeed = [];
		if ( performance.navigation.redirectCount == 0 ) {
			pageSpeed[0] = 'Redirections: 0';
		} else {
			pageSpeed[0] = 'Redirections: ' + performance.navigation.redirectCount + ' @ ' + pageSpeed['redirectionTime'];
		}
		pageSpeed[1] = 'Redirection Time: ' + (performance.timing.redirectEnd-performance.timing.redirectStart)/1000 + 's';
		pageSpeed[2] = 'App Cache Time: ' + (performance.timing.domainLookupStart-performance.timing.fetchStart)/1000 + 's';
		pageSpeed[3] = 'DNS Time: ' + (performance.timing.domainLookupEnd-performance.timing.domainLookupStart)/1000 + 's';
		pageSpeed[4] = 'TCP Time: ' + (performance.timing.connectEnd-performance.timing.connectStart)/1000 + 's';
		pageSpeed[5] = 'Request Time: ' + (performance.timing.responseStart-performance.timing.requestStart)/1000 + 's';
		pageSpeed[6] = 'Response Time: ' + (performance.timing.responseEnd-performance.timing.responseStart)/1000 + 's';
		pageSpeed[7] = 'DOM Time: ' + (performance.timing.domComplete-performance.timing.domLoading)/1000 + 's';
		pageSpeed[8] = 'onLoad Time: ' + (performance.timing.loadEventEnd-performance.timing.loadEventStart)/1000 + 's';
		pageSpeed[9] = 'Total Page Load Time: ' + (performance.timing.loadEventEnd-performance.timing.navigationStart)/1000 + 's';
		
		console.log('******* BEGIN SPEED TEST RESULTS *******');	
		for (var i = 0; i < pageSpeed.length; i++) {
			console.log(pageSpeed[i]);
		}
		console.log('******* END SPEED TEST RESULTS *******');	
	
	}, 0);
};
