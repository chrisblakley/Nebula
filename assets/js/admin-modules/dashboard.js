//Developer Metabox functions
nebula.developerMetaboxes = function(){
	if ( jQuery('div#phg_developer_info').length ){
		//Developer Info Metabox
		jQuery(document).on('keyup', 'input.findterm', function(){
			jQuery('input.findterm').attr('placeholder', 'Search files');
		});

		//Nebula filesystem search
		jQuery(document).on('submit', '.searchfiles', function(e){
			if ( jQuery('input.findterm').val().trim().length >= 3 ){
				jQuery('#searchprogress').removeClass('fa-search').addClass('fas fa-spinner fa-spin fa-fw');

				jQuery.ajax({ //Consider switching to fetch
					type: 'POST',
					url: nebula.site.ajax.url,
					data: {
						nonce: nebula.site.ajax.nonce,
						action: 'search_theme_files',
						data: [{
							directory: jQuery('select.searchdirectory').val(),
							searchData: jQuery('input.findterm').val()
						}]
					},
					success: function(response){
						jQuery('#searchprogress').removeClass('fa-spinner fa-spin').addClass('fas fa-search fa-fw');
						jQuery('div.search_results').html(response).addClass('done');
					},
					error: function(XMLHttpRequest, textStatus, errorThrown){
						jQuery('div.search_results').html(errorThrown).addClass('done');
					},
					timeout: 60_000
				});
			} else {
				jQuery('input.findterm').val('').attr('placeholder', 'Minimum 3 characters.');
			}
			e.preventDefault();
			return false;
		});

		//Dynamic height for TODO results
		if ( jQuery('.todo_results').length ){
			jQuery(document).on('click', '.linenumber', function(){
				jQuery(this).parents('.linewrap').find('.precon').slideToggle();
				return false;
			});

			jQuery('.todo_results').addClass('height-check');
			if ( jQuery('.todo_results')[0].scrollHeight <= 300 ){
				jQuery('.todo_results').css('height', jQuery('.todo_results')[0].scrollHeight + 'px');
			}
			jQuery('.todo_results').removeClass('height-check');

			//Hide TODO files with only hidden items
			jQuery('.todofilewrap').each(function(){
				if ( jQuery(this).find('.linewrap').length === jQuery(this).find('.todo-priority-0').length ){
					jQuery(this).addClass('hidden');
				}
			});
		}
	}

	if ( jQuery('div#performance_metabox').length ){
		nebula.checkPageSpeed(); //Performance Timing
	}
};

//Check the page speed using (in this priority) WebPageTest.org, Google Lighthouse, or a rudimentary iframe timing
nebula.checkPageSpeed = function(){
	jQuery('#performance_metabox h2 i').removeClass('fa-stopwatch').addClass('fa-spinner fa-spin');

	if ( location.hostname === 'localhost' || location.hostname === '127.0.0.1' ){ //If localhost or other "invalid" URL. This doesn't catch local TLDs, but the logic below will figure it out eventually.
		jQuery('#performance-sub-status strong').text('Using iframe test due to local development.');
		nebula.runIframeSpeedTest();
		return;
	}

	//If WebPageTest JSON URL exists, use it!
	if ( typeof wptTestJSONURL !== 'undefined' ){
		nebula.checkWPTresults();
		return;
	}

	nebula.getLighthouseResults();
};

//Check on the WebPageTest API results (initiated on the server-side then called repetatively by JS)
nebula.checkWPTresults = function(){
	jQuery('#performance_metabox h2 span span').html('Measuring Performance <small>(via WebPageTest.org)</small>');

	jQuery.get({ //Eventually use fetch here
		url: wptTestJSONURL,
	}).success(function(response){
		if ( response ){
			if ( response.statusCode === 200 ){ //Test results are ready
				if ( response.data.successfulFVRuns > 0 ){
					//Screenshot
					jQuery('#performance-screenshot').attr('src', response.data.median.firstView.images.screenShot).removeClass('hidden');

					//Sub-status Completed Date/Time
					var wptCompletedDate = new Date(response.data.completed*1000).toLocaleDateString(false, {year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit'});
					jQuery('#performance-sub-status i').removeClass('fa-comment').addClass('fa-calendar-check');
					jQuery('#performance-sub-status span.label').text('Completed');
					jQuery('#performance-sub-status strong').html('<a href="' + response.data.summary + '" target="_blank" rel="noopener">' + wptCompletedDate + '</a>');

					//Time to First Byte
					var ttfb = response.data.median.firstView.TTFB;
					jQuery('#performance-ttfb').remove(); //Remove the PHP-timed data
					nebula.appendPerformanceMetric({
						'icon': 'fas fa-hdd',
						'label': 'Time to First Byte',
						'text': ttfb/1000 + ' seconds',
						'value': ttfb,
						'warning': 500,
						'error': 1000
					});

					//DOM Ready
					var domLoadTime = response.data.median.firstView.domComplete;
					nebula.appendPerformanceMetric({
						'icon': 'fas fa-clock',
						'label': 'DOM Ready',
						'text': domLoadTime/1000 + ' seconds',
						'value': domLoadTime,
						'warning': 3000,
						'error': 5000,
						'diff': ((domLoadTime - ttfb)/1000).toFixed(3) + 's'
					});

					//Window Load
					var fullyLoadedTime = response.data.median.firstView.fullyLoaded;
					nebula.appendPerformanceMetric({
						'icon': 'fas fa-clock',
						'label': 'Window Load',
						'text': fullyLoadedTime/1000 + ' seconds',
						'value': fullyLoadedTime,
						'warning': 5000,
						'error': 7000,
						'diff': ((fullyLoadedTime - domLoadTime)/1000).toFixed(3) + 's'
					});

					if ( response.data.lighthouse.audits ){
						//First Contentful Paint (15%)
						var firstContentfulPaint = response.data.lighthouse.audits['first-contentful-paint'];
						nebula.appendPerformanceMetric({
							'icon': 'fas fa-paint-brush',
							'label': 'First Contentful Paint (FCP)',
							'text': (firstContentfulPaint.numericValue/1000).toFixed(3) + ' seconds',
							'description': firstContentfulPaint.description,
							'value': firstContentfulPaint.numericValue,
							'warning': 2000,
							'error': 4000
						});

						//Largest Contentful Paint (25%)
						var largestContentfulPaint = response.data.lighthouse.audits['largest-contentful-paint'];
						nebula.appendPerformanceMetric({
							'icon': 'fas fa-paint-roller',
							'label': 'Largest Contentful Paint (LCP)',
							'text': (largestContentfulPaint.numericValue/1000).toFixed(3) + ' seconds',
							'description': largestContentfulPaint.description,
							'value': largestContentfulPaint.numericValue,
							'warning': 2500,
							'error': 4000
						});

						//First Input Delay
						var firstInputDelay = response.data.lighthouse.audits['max-potential-fid'];
						nebula.appendPerformanceMetric({
							'icon': 'fas fa-mouse-pointer',
							'label': 'First Input Delay (FID)',
							'text': (firstInputDelay.numericValue/1000).toFixed(3) + ' seconds',
							'description': firstInputDelay.description,
							'value': firstInputDelay.numericValue,
							'warning': 100,
							'error': 300
						});

						//Time to Interactive (15%)
						var timeToInteractive = response.data.lighthouse.audits['interactive'];
						nebula.appendPerformanceMetric({
							'icon': 'far fa-hand-pointer',
							'label': 'Time to Interactive (TTI)',
							'text': (timeToInteractive.numericValue/1000).toFixed(3) + ' seconds',
							'description': timeToInteractive.description,
							'value': timeToInteractive.numericValue,
							'warning': 5300,
							'error': 7300
						});

						//Speed Index (15%)
						var speedIndex = response.data.lighthouse.audits['speed-index'];
						nebula.appendPerformanceMetric({
							'icon': 'fas fa-tachometer-alt',
							'label': 'Speed Index',
							'text': (speedIndex.numericValue/1000).toFixed(3) + ' seconds',
							'description': speedIndex.description,
							'value': speedIndex.numericValue,
							'warning': 4400,
							'error': 5800
						});

						//Total Blocking Time (25%)
						var totalBlockingTime = response.data.lighthouse.audits['total-blocking-time'];
						nebula.appendPerformanceMetric({
							'icon': 'fas fa-hand-paper',
							'label': 'Total Blocking Time (TBT)',
							'text': (totalBlockingTime.numericValue/1000).toFixed(3) + ' seconds',
							'description': totalBlockingTime.description,
							'value': totalBlockingTime.numericValue,
							'warning': 300,
							'error': 600
						});

						//Cumulative Layout Shift (5%)
						var cumulativeLayoutShift = response.data.lighthouse.audits['cumulative-layout-shift'];
						nebula.appendPerformanceMetric({
							'icon': 'fas fa-arrows-alt-v',
							'label': 'Cumulative Layout Shift (CLS)',
							'text': cumulativeLayoutShift.displayValue,
							'description': cumulativeLayoutShift.description,
							'value': cumulativeLayoutShift.numericValue,
							'warning': 0.1,
							'error': 0.25
						});
					}

					//Bytes Downloaded
					var bytesIn = (response.data.median.firstView.bytesIn/1024/1024).toFixed(2);
					nebula.appendPerformanceMetric({
						'icon': 'fas fa-weight-hanging',
						'label': 'Bytes Downloaded',
						'text': bytesIn + 'mb',
						'value': bytesIn,
						'warning': 1,
						'error': 2
					});

					//Total Requests
					var totalRequests = response.data.median.firstView.requestsFull;
					nebula.appendPerformanceMetric({
						'icon': 'fas fa-list-ol',
						'label': 'Total Requests',
						'text': totalRequests,
						'value': totalRequests,
						'warning': 80,
						'error': 120
					});

					//Status
					jQuery('#performance_metabox h2 i').removeClass('fa-spinner fa-spin').addClass('fa-stopwatch');
					jQuery('#performance_metabox h2 span span').html('Performance <small>(via WebPageTest.org)</small>');
				} else {
					console.warn('WebPageTest.org did not have a successful run.', response);
					jQuery('#performance-sub-status strong').text('WebPageTest.org did not have a successful run.');
					nebula.getLighthouseResults();
				}
			} else if ( response.statusCode < 200 ){ //Testing still in progress
				var waitingBehind = response.statusText.match(/behind (\d+) other/) || 0;

				if ( waitingBehind ){
					waitingBehind = parseInt(waitingBehind[1]);
				}

				if ( waitingBehind < 25 ){ //Wait in line if fewer than 25 tests ahead
					jQuery('#performance-sub-status strong').text(response.statusText);
					var pollTime = ( response.statusCode === 100 )? 3000 : 8000; //Poll slowly when behind other tests and quickly once the test has started
					setTimeout(nebula.checkWPTresults, pollTime);
				} else {
					console.warn('Behind too many other WebPageTest.org tests. Check back later for results.', waitingBehind);
					jQuery('#performance-sub-status strong').text('Behind too many other WebPageTest.org tests. Check back later for results.');
					nebula.getLighthouseResults();
				}
			} else if ( response.statusCode >= 400 ){ //An API error has occurred
				console.warn('A WebPageTest API error has occurred.', response);
				jQuery('#performance-sub-status strong').text('An API error has occurred.');
				nebula.getLighthouseResults();
			}
		}
	});
};

nebula.getLighthouseResults = function(){
	jQuery('#performance_metabox h2 span span').html('Measuring Performance <small>(via Google Lighthouse)</small>');
	jQuery('#performance-sub-status strong').text('Google Lighthouse report in-progress.');

	var sourceURL = jQuery('#testloadcon').attr('data-src') + '?noga'; //No GA so it does not get flooded with bot traffic
	fetch('https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' + encodeURIComponent(sourceURL)).then(function(response){
		return response.json(); //This returns a promise
	}).then(function(json){
		if ( json && json.captchaResult === 'CAPTCHA_NOT_NEEDED' ){
			var pagespeedCompletedDate = new Date(json.analysisUTCTimestamp).toLocaleDateString(false, {year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit'});

			//Screenshot
			jQuery('#performance-screenshot').attr('src', json.lighthouseResult.audits['final-screenshot'].details.data).removeClass('hidden');

			//Sub-status Completed Date/Time
			jQuery('#performance-sub-status i').removeClass('fa-comment').addClass('fa-calendar-check');
			jQuery('#performance-sub-status span.label').text('Completed');
			jQuery('#performance-sub-status strong').text(pagespeedCompletedDate);
			jQuery('#performance-sub-status strong').html('<a href="https://developers.google.com/speed/pagespeed/insights/?url=' + encodeURIComponent(sourceURL) + '" target="_blank" rel="noopener">' + pagespeedCompletedDate + '</a>');

			if ( json.lighthouseResult.audits ){
				//Server Response Time
				var serverResponseTime = json.lighthouseResult.audits['server-response-time'];
				jQuery('#performance-ttfb').remove(); //Remove the PHP-timed data
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-hdd',
					'label': 'Server Response Time',
					'text': (serverResponseTime.numericValue/1000).toFixed(3) + ' seconds',
					'description': serverResponseTime.description,
					'value': serverResponseTime.numericValue,
					'warning': 500,
					'error': 1000
				});

				//DOM Ready
				var domReady = json.lighthouseResult.audits['metrics'].details.items[0].observedDomContentLoaded;
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-clock',
					'label': 'DOM Ready',
					'text': (domReady/1000).toFixed(3) + ' seconds',
					'value': domReady,
					'warning': 3000,
					'error': 5000,
					'diff': ((domReady - serverResponseTime.numericValue)/1000).toFixed(3) + 's'
				});

				//Window Load
				var windowLoad = json.lighthouseResult.audits['metrics'].details.items[0].observedLoad;
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-clock',
					'label': 'Window Load',
					'text': (windowLoad/1000).toFixed(3) + ' seconds',
					'value': windowLoad,
					'warning': 5000,
					'error': 7000,
					'diff': ((windowLoad - domReady)/1000).toFixed(3) + 's'
				});

				//First Contentful Paint (15%)
				var firstContentfulPaint = json.lighthouseResult.audits['first-contentful-paint'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-paint-brush',
					'label': 'First Contentful Paint (FCP)',
					'text': (firstContentfulPaint.numericValue/1000).toFixed(3) + ' seconds',
					'description': firstContentfulPaint.description,
					'value': firstContentfulPaint.numericValue,
					'warning': 2000,
					'error': 4000
				});

				//Largest Contentful Paint (25%)
				var largestContentfulPaint = json.lighthouseResult.audits['largest-contentful-paint'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-paint-roller',
					'label': 'Largest Contentful Paint (LCP)',
					'text': (largestContentfulPaint.numericValue/1000).toFixed(3) + ' seconds',
					'description': largestContentfulPaint.description,
					'value': largestContentfulPaint.numericValue,
					'warning': 2500,
					'error': 4000
				});

				//First Input Delay
				var firstInputDelay = json.lighthouseResult.audits['max-potential-fid'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-mouse-pointer',
					'label': 'First Input Delay (FID)',
					'text': (firstInputDelay.numericValue/1000).toFixed(3) + ' seconds',
					'description': firstInputDelay.description,
					'value': firstInputDelay.numericValue,
					'warning': 100,
					'error': 300
				});

				//Time to Interactive (15%)
				var timeToInteractive = json.lighthouseResult.audits['interactive'];
				nebula.appendPerformanceMetric({
					'icon': 'far fa-hand-pointer',
					'label': 'Time to Interactive (TTI)',
					'text': (timeToInteractive.numericValue/1000).toFixed(3) + ' seconds',
					'description': timeToInteractive.description,
					'value': timeToInteractive.numericValue,
					'warning': 5300,
					'error': 7300
				});

				//Speed Index (15%)
				var speedIndex = json.lighthouseResult.audits['speed-index'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-tachometer-alt',
					'label': 'Speed Index',
					'text': (speedIndex.numericValue/1000).toFixed(3) + ' seconds',
					'description': speedIndex.description,
					'value': speedIndex.numericValue,
					'warning': 4400,
					'error': 5800
				});

				//Total Blocking Time (25%)
				var totalBlockingTime = json.lighthouseResult.audits['total-blocking-time'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-hand-paper',
					'label': 'Total Blocking Time (TBT)',
					'text': (totalBlockingTime.numericValue/1000).toFixed(3) + ' seconds',
					'description': totalBlockingTime.description,
					'value': totalBlockingTime.numericValue,
					'warning': 300,
					'error': 600
				});

				//Cumulative Layout Shift (5%)
				var cumulativeLayoutShift = json.lighthouseResult.audits['cumulative-layout-shift'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-arrows-alt-v',
					'label': 'Cumulative Layout Shift (CLS)',
					'text': cumulativeLayoutShift.displayValue,
					'description': cumulativeLayoutShift.description,
					'value': cumulativeLayoutShift.numericValue,
					'warning': 0.1,
					'error': 0.25
				});

				//Total Byte Weight
				var totalByteWeight = json.lighthouseResult.audits['total-byte-weight'];
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-weight-hanging',
					'label': 'Total Byte Weight',
					'text': (totalByteWeight.numericValue/1024/1024).toFixed(2) + 'mb',
					'description': totalByteWeight.description,
					'value': totalByteWeight.numericValue/1024/1024,
					'warning': 1,
					'error': 2
				});

				//Network Requests
				var networkRequests = json.lighthouseResult.audits['network-requests'].details.items.length;
				nebula.appendPerformanceMetric({
					'icon': 'fas fa-list-ol',
					'label': 'Network Requests',
					'text': networkRequests,
					'value': networkRequests,
					'warning': 80,
					'error': 120
				});
			}

			jQuery('#performance_metabox h2 i').removeClass('fa-spinner fa-spin').addClass('fa-stopwatch');
			jQuery('#performance_metabox h2 span span').html('Performance <small>(via Google Lighthouse)</small>');
		} else { //If the fetch data is not expected, run iframe test instead...
			console.warn('Fetch data is not expected from Lighthouse.', json);
			nebula.runIframeSpeedTest();
		}
	}).catch(function(error){
		console.warn('Google Lighthouse failed. Reverting to iframe test.', error);
		jQuery('#performance-sub-status strong').text('Google Lighthouse failed. Reverting to iframe test.');
		nebula.runIframeSpeedTest(); //If Google Lighthouse check fails, time with an iframe instead...
	});
};

//Load the home page in an iframe and time the DOM and Window load times
nebula.runIframeSpeedTest = function(){
	jQuery('#performance_metabox h2 span span').html('Measuring Performance <small>(via Iframe)</small>');

	var iframe = document.createElement('iframe');
	iframe.style.width = '1200px';
	iframe.style.height = '0px';
	iframe.src = jQuery('#testloadcon').attr('data-src') + '?noga'; //Cannot use nebula.site.home_url here for some reason even though it obeys https. No GA so it does not get flooded with bot traffic
	jQuery('#testloadcon').append(iframe);

	jQuery('#testloadcon iframe').on('load', function(){
		//Server Response Time
		var iframeResponseEnd = Math.round(iframe.contentWindow.performance.timing.responseEnd-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until server response finishes
		jQuery('#performance-ttfb').remove(); //Remove the PHP-timed data
		nebula.appendPerformanceMetric({
			'icon': 'fas fa-hdd',
			'label': 'Server Response Time',
			'text': iframeResponseEnd/1000 + ' seconds',
			'value': iframeResponseEnd,
			'warning': 500,
			'error': 1000,
		});

		//DOM Ready
		var iframeDomReady = Math.round(iframe.contentWindow.performance.timing.domContentLoadedEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until DOM ready
		nebula.appendPerformanceMetric({
			'icon': 'fas fa-clock',
			'label': 'DOM Ready',
			'text': iframeDomReady/1000 + ' seconds',
			'value': iframeDomReady,
			'warning': 3000,
			'error': 5000,
		});

		//Window Load
		var iframeWindowLoaded = Math.round(iframe.contentWindow.performance.timing.loadEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until window load
		nebula.appendPerformanceMetric({
			'icon': 'fas fa-clock',
			'label': 'Window Load',
			'text': iframeWindowLoaded/1000 + ' seconds',
			'value': iframeWindowLoaded,
			'warning': 5000,
			'error': 7000,
		});

		jQuery('#testloadcon, #testloadscript').remove(); //Remove the iframe

		jQuery('#performance_metabox h2 i').removeClass('fa-spinner fa-spin').addClass('fa-stopwatch');
		jQuery('#performance_metabox h2 span span').html('Performance <small>(via Iframe)</small>');
		jQuery('#performance-sub-status strong').text('Iframe test completed.');
	});
};

//Append a performance metric to the list
nebula.appendPerformanceMetric = function(data){
	if ( data ){
		var description = '';
		if ( data.description ){
			description = data.description.replace(/( \[.*\]\(.*\)\.?)/ig, '');
		}

		var icon = '<i class="fa-fw ' + data.icon + '"></i>';
		var warningLevel = '';

		//Check to show warning indicators
		if ( data.value ){
			if ( data.value > data.error ){
				warningLevel = 'error';
				icon = '<i class="fa-fw fas fa-exclamation-triangle"></i>';
			} else if ( data.value > data.warning ){
				warningLevel = 'warn';
				icon = '<i class="fa-fw fas fa-exclamation-circle"></i>';
			}
		}

		var diff = '';
		if ( data.diff ){
			diff = ' <small>(' + data.diff + ' from previous)</small>';
		}

		jQuery('ul#nebula-performance-metrics').append('<li class="' + warningLevel + '" title="' + description + '">' + icon + ' ' + data.label + ': <strong>' + data.text + '</strong>' + diff + '</li>');
	}
};