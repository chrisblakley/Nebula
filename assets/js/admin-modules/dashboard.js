window.performance.mark('(Nebula) Inside /admin-modules/dashboard.js');

//Developer Metabox functions
nebula.developerMetaboxes = function(){
	//Developer Info Metabox
	if ( jQuery('div#nebula_developer_info').length ){
		if ( jQuery('.serverdetections').length ){ //If viewing the dashboard
			if ( !jQuery('.nebula-adb-tester').is(':visible') ){
				jQuery('.serverdetections').prepend('<li class="nebula-adb-reminder"><i class="fa-solid fa-shield-halved"></i> Your ad-blocker is enabled</li>');
			} else {
				jQuery('.nebula-adb-tester').remove();
			}
		}

		jQuery('.searchterm').removeClass('button-disabled').removeAttr('disabled title'); //Enable the button now that JS has loaded

		//Nebula filesystem search
		jQuery(document).on('submit', '.searchfiles', function(e){
			if ( jQuery('input.findterm').val().trim().length >= 2 ){
				jQuery('#searchprogress').removeClass('fa-magnifying-glass').addClass('fa-solid fa-spinner fa-spin fa-fw');

				fetch(nebula.site.ajax.url, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
						'Cache-Control': 'no-cache',
					},
					body: new URLSearchParams({
						nonce: nebula.site.ajax.nonce,
						action: 'search_theme_files',
						directory: jQuery('select.searchdirectory').val(),
						searchData: jQuery('input.findterm').val()
					}),
					priority: 'high'
				}).then(function(response){
					if ( response.ok ){
						return response.text();
					}
				}).then(function(response){
					jQuery('#searchprogress').removeClass('fa-spinner fa-spin').addClass('fa-solid fa-magnifying-glass fa-fw');
					jQuery('div.search_results').html(response).addClass('done');
				}).catch(function(error){
					jQuery('div.search_results').html(error).addClass('done');
				});
			} else {
				jQuery('input.findterm').val('').attr('placeholder', 'Minimum 2 characters.');
			}
			e.preventDefault();
			return false;
		});
	}

	//To-Do Metabox
	if ( jQuery('div#todo_manager').length ){
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
		window.requestAnimationFrame(function(){ //Update when Safari supports requestIdleCallback
			nebula.checkPageSpeed(); //Performance Timing
		});
	}

	//At-a-Glance Metabox
	if ( jQuery('div#nebula_ataglance').length ){
		let loadedTime = new Date();
		jQuery(document).on('mouseover', '#last-loaded', function(){
			jQuery(this).attr('title', nebula.timeAgo(loadedTime)); //Update the title tag to be the relative time since the page loaded
		});
	}
};

//Check the page speed using (in this priority) Google Lighthouse, or a rudimentary iframe timing
nebula.checkPageSpeed = function(){
	jQuery('#performance_metabox h2 i').removeClass('fa-stopwatch').addClass('fa-spinner fa-spin');

	if ( location.hostname === 'localhost' || location.hostname === '127.0.0.1' ){ //If localhost or other "invalid" URL. This doesn't catch local TLDs, but the logic below will figure it out eventually.
		jQuery('#performance-sub-status strong').text('Using iframe test due to local development.');
		nebula.runIframeSpeedTest();
		return;
	}

	nebula.getLighthouseResults();
};

nebula.getLighthouseResults = function(){
	jQuery('#performance_metabox h2 span span').html('Measuring Performance <small>(via Google Lighthouse)</small>');
	jQuery('#performance-sub-status strong').text('Google Lighthouse report in-progress.');

	var sourceURL = jQuery('#testloadcon').attr('data-src') + '?noga'; //No GA so it does not get flooded with bot traffic
	fetch('https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' + encodeURIComponent(sourceURL), {
		cache: 'no-cache',
		priority: 'low'
	}).then(function(response){
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
				console.log('Lighthouse Performance Data:', json.lighthouseResult.audits);

				//Server Response Time
				var serverResponseTime = json.lighthouseResult.audits['server-response-time'];
				jQuery('#performance-ttfb').remove(); //Remove the PHP-timed data
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-hdd',
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
					'icon': 'fa-solid fa-stopwatch',
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
					'icon': 'fa-solid fa-stopwatch',
					'label': 'Window Load',
					'text': (windowLoad/1000).toFixed(3) + ' seconds',
					'value': windowLoad,
					'warning': 5000,
					'error': 7000,
					'diff': ((windowLoad - domReady)/1000).toFixed(3) + 's'
				});

				//First Contentful Paint
				var firstContentfulPaint = json.lighthouseResult.audits['first-contentful-paint'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-paint-brush',
					'label': 'First Contentful Paint (FCP)',
					'text': (firstContentfulPaint.numericValue/1000).toFixed(3) + ' seconds',
					'description': firstContentfulPaint.description,
					'value': firstContentfulPaint.numericValue,
					'warning': 2000,
					'error': 4000
				});

				//Largest Contentful Paint
				var largestContentfulPaint = json.lighthouseResult.audits['largest-contentful-paint'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-paint-roller',
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
					'icon': 'fa-solid fa-mouse-pointer',
					'label': 'First Input Delay (FID)',
					'text': (firstInputDelay.numericValue/1000).toFixed(3) + ' seconds',
					'description': firstInputDelay.description,
					'value': firstInputDelay.numericValue,
					'warning': 100,
					'error': 300
				});

				//Time to Interactive
				var timeToInteractive = json.lighthouseResult.audits['interactive'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-regular fa-hand-pointer',
					'label': 'Time to Interactive (TTI)',
					'text': (timeToInteractive.numericValue/1000).toFixed(3) + ' seconds',
					'description': timeToInteractive.description,
					'value': timeToInteractive.numericValue,
					'warning': 5300,
					'error': 7300
				});

				//Speed Index
				var speedIndex = json.lighthouseResult.audits['speed-index'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-tachometer-alt',
					'label': 'Speed Index',
					'text': (speedIndex.numericValue/1000).toFixed(3) + ' seconds',
					'description': speedIndex.description,
					'value': speedIndex.numericValue,
					'warning': 4400,
					'error': 5800
				});

				//Total Blocking Time
				var totalBlockingTime = json.lighthouseResult.audits['total-blocking-time'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-shield-halved',
					'label': 'Total Blocking Time (TBT)',
					'text': (totalBlockingTime.numericValue/1000).toFixed(3) + ' seconds',
					'description': totalBlockingTime.description,
					'value': totalBlockingTime.numericValue,
					'warning': 300,
					'error': 600
				});

				//Cumulative Layout Shift
				var cumulativeLayoutShift = json.lighthouseResult.audits['cumulative-layout-shift'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-arrows-alt-v',
					'label': 'Cumulative Layout Shift (CLS)',
					'text': cumulativeLayoutShift.numericValue.toFixed(3), //cumulativeLayoutShift.displayValue
					'description': cumulativeLayoutShift.description,
					'value': cumulativeLayoutShift.numericValue,
					'warning': 0.1,
					'error': 0.25
				});

				//Total Byte Weight
				var totalByteWeight = json.lighthouseResult.audits['total-byte-weight'];
				nebula.appendPerformanceMetric({
					'icon': 'fa-solid fa-weight-hanging',
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
					'icon': 'fa-solid fa-list-ol',
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
			if ( json.code ){
				console.warn('Received Lighthouse error code:', json.code, json.message);
			}

			console.warn('Fetch data is not expected from Lighthouse. Running iframe test instead.', json);
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
		console.log('Iframe Performance Data:', JSON.parse(JSON.stringify(iframe.contentWindow.performance))); //Needs to stringify/parse to de-synchronize the object and retain the actual values (just for this output)

		//Server Response Time
		var iframeResponseEnd = Math.round(iframe.contentWindow.performance.timing.responseEnd-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until server response finishes
		jQuery('#performance-ttfb').remove(); //Remove the PHP-timed data
		nebula.appendPerformanceMetric({
			'icon': 'fa-solid fa-hdd',
			'label': 'Server Response Time',
			'text': iframeResponseEnd/1000 + ' seconds',
			'value': iframeResponseEnd,
			'warning': 500,
			'error': 1000,
		});

		//DOM Ready
		var iframeDomReady = Math.round(iframe.contentWindow.performance.timing.domContentLoadedEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until DOM ready
		nebula.appendPerformanceMetric({
			'icon': 'fa-solid fa-stopwatch',
			'label': 'DOM Ready',
			'text': iframeDomReady/1000 + ' seconds',
			'value': iframeDomReady,
			'warning': 3000,
			'error': 5000,
		});

		//Window Load
		var iframeWindowLoaded = Math.round(iframe.contentWindow.performance.timing.loadEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until window load
		nebula.appendPerformanceMetric({
			'icon': 'fa-solid fa-stopwatch',
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
				icon = '<i class="fa-fw fa-solid fa-exclamation-triangle"></i>';
			} else if ( data.value > data.warning ){
				warningLevel = 'warning';
				icon = '<i class="fa-fw fa-solid fa-exclamation-circle"></i>';
			}
		}

		var diff = '';
		if ( data.diff ){
			diff = ' <small>(' + data.diff + ' from previous)</small>';
		}

		jQuery('ul#nebula-performance-metrics').append('<li class="' + warningLevel + '" title="' + description + '">' + icon + ' ' + data.label + ': <strong>' + data.text + '</strong>' + diff + '</li>');
	}
};