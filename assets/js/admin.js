jQuery.noConflict();

//Document Ready
jQuery(function(){
	userHeadshotFields();
	initializationStuff();

	jQuery(function(){
		jQuery('#post textarea').allowTabChar();
	});

	if ( !jQuery('li#menu-comments').is(':visible') ){
		jQuery('#dashboard_right_now .main').append('Comments are disabled <small>(via <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a>)</small>.');
	}

	//If Nebula Options Page
	if ( window.location.href.indexOf('themes.php?page=nebula_options') > 0 ){
		checkWindowHeightForStickyNav();
		nebulaLiveValidator();
		nebulaLogs();

		//If there are no active tabs on load (like if wrong ?tab= parameter was used)
		if ( !jQuery('#options-navigation li a.active').length ){
			jQuery('#options-navigation').find('li:first-child a').addClass('active');
			jQuery('#nebula-options-section').find('.tab-pane:first-child').addClass('active');
		}

		//Scroll to the top when changing tabs
		jQuery('a.nav-link').on('shown.bs.tab', function(){
			//Update the URL to reflect the active tab
			var url = nebula.site.admin_url + 'themes.php?page=nebula_options' + '&tab=' + jQuery('#options-navigation a.active').attr('href').replace('#', '');
			history.replaceState(null, document.title, url);

			jQuery('html, body').animate({
				scrollTop: jQuery('#nebula-options-section').offset().top-100
			}, 500);
		});

		jQuery('#nebula-option-filter').trigger('keydown').focus(); //Trigger if a ?filter= parameter is used.

		checkDependents(); //Check all dependents
		checkImportants();
		jQuery('input').on('keyup change', function(){
			checkDependents(jQuery(this));
			checkImportants();
		});

		jQuery('.short-help').each(function(){
			if ( !jQuery(this).parents('.no-help').length ){
				if ( nebula.user.staff === 'developer' ){
					//Dev handle names
					var optionHandle = jQuery(this).closest('.form-group').find('[name^=nebula_options]').attr('id');
					if ( typeof optionHandle !== 'undefined' ){
						var devUsage = '<span class="dev-handle form-text text-muted">Dev usage: <code>nebula()->get_option(\'' + optionHandle + '\');</code></span>';
						if ( jQuery(this).parent().find('.more-help').length ){
							jQuery(this).closest('.form-group').find('.more-help').append(devUsage);
						} else {
							jQuery(this).after('<p class="nebula-help-text more-help form-text text-muted">' + devUsage + '</p>');
						}
					}
				}

				//More Help expander icons
				//if ( jQuery(this).parent().find('.more-help').length ){
					jQuery(this).append('<a class="toggle-more-help" href="#" title="Toggle more information" tabindex="-1"><i class="fas fa-fw fa-question-circle"></i></a>');
				//}
			}
		});

		//Show/hide more information
		jQuery(document).on('click', '.toggle-more-help', function(){
			var formGroup = jQuery(this).closest('.form-group, .multi-form-group');
			jQuery('.form-group.highlight, .multi-form-group.highlight').not(formGroup).removeClass('highlight').find('.more-help').slideUp(); //Un-highlight all other options
			formGroup.toggleClass('highlight').find('.more-help').slideToggle(); //Toggle highlight on this option

			var thisTab = jQuery(this).closest('.tab-pane').attr('id');
			var thisOption = jQuery(this).closest('.form-group, .multi-form-group').find('.form-control').attr('id') || jQuery(this).closest('.form-group, .multi-form-group').find('label').attr('for');

			var url = nebula.site.admin_url + 'themes.php?page=nebula_options&tab=' + thisTab;
			if ( formGroup.hasClass('highlight') ){
				url += '&option=' + thisOption;
			}
			history.replaceState(null, document.title, url); //Modify the URL so the direct link can be copied

			return false;
		});
	}

	//Remove Sass render trigger query
	if ( get('sass') && !get('persistent') && window.history.replaceState ){ //IE10+
		window.history.replaceState({}, document.title, removeQueryParameter('sass', window.location.href));
	}
}); //End Document Ready

jQuery(window).on('load', function(){
	nebulaUniqueSlugChecker();
	performanceMetrics();
	developerMetaboxes();

	//Option filter
	jQuery('#nebula-option-filter').on('keydown keyup change focus blur', function(e){
		debounce(function(){
			if ( jQuery('#nebula-option-filter').val() !== '' ){
				var url = nebula.site.admin_url + 'themes.php?page=nebula_options' + '&filter=' + jQuery('#nebula-option-filter').val();
			} else {
				var url = nebula.site.admin_url + 'themes.php?page=nebula_options';
			}

			history.replaceState(null, document.title, url);
		}, 1000, 'nebula options filter history api');

		//Prevent the form from submitting if pressing enter after searching
		if ( e.type === 'keydown' && e.keyCode === 13 ){
			e.preventDefault();
			return false;
		}

		if ( jQuery(this).val().length > 0 ){
			jQuery('.metabox-holder').addClass('filtering');
			jQuery('#reset-filter').removeClass('hidden');

			jQuery('#options-navigation').addClass('inactive').find('li a.active').removeClass('active');

			jQuery('.tab-pane').addClass('active');

			keywordSearch('#nebula-options-section', '.form-group', jQuery(this).val());

			jQuery('.postbox, .option-sub-group').each(function(){
				if ( jQuery(this).find('.form-group:not(.filtereditem)').length > 0 ){
					jQuery(this).removeClass('filtereditem');
				} else {
					jQuery(this).addClass('filtereditem');
				}
			});

			jQuery('#nebula-options-section div[class^=col]').each(function(){
				if ( !jQuery(this).parents('.title-row, .save-row, .non-filter').length ){
					if ( jQuery(this).find('.form-group:not(.filtereditem)').length > 0 ){
						jQuery(this).removeClass('filtereditem');
					} else {
						jQuery(this).addClass('filtereditem');
					}
				}
			});

			jQuery('.tab-pane').each(function(){
				if ( jQuery(this).find('.form-group:not(.filtereditem)').length > 0 ){
					jQuery(this).removeClass('filtereditem');
					jQuery(this).find('.title-row').removeClass('filtereditem');
				} else {
					jQuery(this).addClass('filtereditem');
					jQuery(this).find('.title-row').addClass('filtereditem');
				}
			});
		} else {
			jQuery('.metabox-holder').removeClass('filtering');
			jQuery('#reset-filter').addClass('hidden');

			jQuery('#options-navigation').removeClass('inactive');

			if ( !jQuery('#options-navigation li a.active').length ){
				jQuery('#options-navigation').find('li:first-child a').addClass('active');
			}

			jQuery('.filtereditem').removeClass('filtereditem');
			jQuery('.tab-pane').removeClass('active').first().addClass('active');
		}
	});

	//Trigger the filter if linking to a pre-filtered search
	if ( jQuery('#nebula-option-filter').val() ){
		jQuery('#nebula-option-filter').trigger('keyup');
	}

	jQuery('#reset-filter a').on('click', function(){
		jQuery('#nebula-option-filter').val('').trigger('keydown');
		jQuery('.tab-pane').removeClass('active').first().addClass('active');
		return false;
	});
});

jQuery(window).resize(function() {
	//If Nebula Options Page
	if ( window.location.href.indexOf('themes.php?page=nebula_options') > 0 ){
		checkWindowHeightForStickyNav();
	}
});

//Developer Metaboxe functions
function developerMetaboxes(){
	if ( jQuery('div#phg_developer_info').length ){
		//Developer Info Metabox
		jQuery(document).on('keyup', 'input.findterm', function(){
			jQuery('input.findterm').attr('placeholder', 'Search files');
		});

		jQuery(document).on('submit', '.searchfiles', function(e){
			if ( jQuery('input.findterm').val().trim().length >= 3 ){
				jQuery('#searchprogress').removeClass('fa-search').addClass('fas fa-spinner fa-spin fa-fw');

				jQuery.ajax({
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
					timeout: 60000
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
		checkPageSpeed(); //Performance Timing
	}
}

//Check the page speed using (in this priority) WebPageTest.org, Google Lighthouse, or a rudimentary iframe timing
function checkPageSpeed(){
	jQuery('#performance_metabox h2 i').removeClass('fa-stopwatch').addClass('fa-spinner fa-spin');

	if ( location.hostname === 'localhost' || location.hostname === '127.0.0.1' ){ //If localhost or other "invalid" URL. This doesn't catch local TLDs, but the logic below will figure it out eventually.
		jQuery('#performance-sub-status strong').text('Using iframe test due to local development.');
		runIframeSpeedTest();
		return;
	}

	//If WebPageTest JSON URL exists, use it!
	if ( typeof wptTestJSONURL !== 'undefined' ){
		checkWPTresults();
		return;
	} else if ( typeof fetch === 'function' && !window.MSInputMethodContext && !document.documentMode ){ //MS Edge+ (No IE11)
		getLighthouseResults();
		return;
	}

	jQuery('#performance-sub-status strong').text('Using iframe test because nothing else is available.');
	runIframeSpeedTest(); //If fetch() is not available (IE11)
}

//Check on the WebPageTest API results (initiated on the server-side then called repetatively by JS)
function checkWPTresults(){
	jQuery('#performance_metabox h2 span span').html('Measuring Performance <small>(via WebPageTest.org)</small>');

	jQuery.get({
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
					appendPerformanceMetric({
						'icon': 'fas fa-hdd',
						'label': 'Time to First Byte',
						'text': ttfb/1000 + ' seconds',
						'value': ttfb,
						'warning': 500,
						'error': 1000
					});

					//DOM Ready
					var domLoadTime = response.data.median.firstView.domComplete;
					appendPerformanceMetric({
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
					appendPerformanceMetric({
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
						appendPerformanceMetric({
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
						appendPerformanceMetric({
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
						appendPerformanceMetric({
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
						appendPerformanceMetric({
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
						appendPerformanceMetric({
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
						appendPerformanceMetric({
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
						appendPerformanceMetric({
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
					appendPerformanceMetric({
						'icon': 'fas fa-weight-hanging',
						'label': 'Bytes Downloaded',
						'text': bytesIn + 'mb',
						'value': bytesIn,
						'warning': 1,
						'error': 2
					});

					//Total Requests
					var totalRequests = response.data.median.firstView.requestsFull;
					appendPerformanceMetric({
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
					getLighthouseResults();
				}
			} else if ( response.statusCode < 200 ){ //Testing still in progress
				var waitingBehind = response.statusText.match(/behind (\d+) other/) || 0;

				if ( waitingBehind ){
					waitingBehind = parseInt(waitingBehind[1]);
				}

				if ( waitingBehind < 25 ){ //Wait in line if fewer than 25 tests ahead
					jQuery('#performance-sub-status strong').text(response.statusText);
					var pollTime = ( response.statusCode === 100 )? 3000 : 8000; //Poll slowly when behind other tests and quickly once the test has started
					setTimeout(checkWPTresults, pollTime);
				} else {
					console.warn('Behind too many other WebPageTest.org tests. Check back later for results.', waitingBehind);
					jQuery('#performance-sub-status strong').text('Behind too many other WebPageTest.org tests. Check back later for results.');
					getLighthouseResults();
				}
			} else if ( response.statusCode >= 400 ){ //An API error has occurred
				console.warn('A WebPageTest API error has occurred.', response);
				jQuery('#performance-sub-status strong').text('An API error has occurred.');
				getLighthouseResults();
			}
		}
	});
}

function getLighthouseResults(){
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
				appendPerformanceMetric({
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
			runIframeSpeedTest();
		}
	}).catch(function(error){
		console.warn('Google Lighthouse failed. Reverting to iframe test.', error);
		jQuery('#performance-sub-status strong').text('Google Lighthouse failed. Reverting to iframe test.');
		runIframeSpeedTest(); //If Google Lighthouse check fails, time with an iframe instead...
	});
}

//Load the home page in an iframe and time the DOM and Window load times
function runIframeSpeedTest(){
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
		appendPerformanceMetric({
			'icon': 'fas fa-hdd',
			'label': 'Server Response Time',
			'text': iframeResponseEnd/1000 + ' seconds',
			'value': iframeResponseEnd,
			'warning': 500,
			'error': 1000,
		});

		//DOM Ready
		var iframeDomReady = Math.round(iframe.contentWindow.performance.timing.domContentLoadedEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until DOM ready
		appendPerformanceMetric({
			'icon': 'fas fa-clock',
			'label': 'DOM Ready',
			'text': iframeDomReady/1000 + ' seconds',
			'value': iframeDomReady,
			'warning': 3000,
			'error': 5000,
		});

		//Window Load
		var iframeWindowLoaded = Math.round(iframe.contentWindow.performance.timing.loadEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until window load
		appendPerformanceMetric({
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
}

//Append a performance metric to the list
function appendPerformanceMetric(data){
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
}

//Initialization alerts
function initializationStuff(){
	//Initialize confirm dialog.
	jQuery('#run-nebula-initialization').on('click', function(){
		if ( !confirm('This will reset some WordPress settings, all Nebula options, and reset the homepage content! Are you sure you want to initialize?') ) {
			return false;
		}
	});

	//Remove query string once initialized.
	if ( window.location.href.indexOf('?nebula-initialization=true') >= 0 ){
		var cleanURL = window.location.href.split('?');
		history.replaceState(null, document.title, cleanURL[0]);
	}
}

//Add user fields for headshot image
function userHeadshotFields(){
	if ( jQuery('body').hasClass('profile-php') ){
		jQuery('#headshot_button').on('click', function(){
			tb_show('Uploading a new headshot!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
			return false;
		});

		window.send_to_editor = function(html){
			var imageURL = jQuery(html).attr('src');
			jQuery('#headshot_url').val(imageURL); //updates our hidden field that will update our author's meta when the form is saved
			tb_remove();
			jQuery('#headshot_preview').html('<img src="' + imageURL + '" style="max-width: 100%; max-height: 100%;" />');

			jQuery('#submit_options_form').trigger('click');
			jQuery('#upload_success').text('Here is a preview of the profile picture you chose.');
		};

		jQuery('#headshot_remove').on('click', function(){
			jQuery('#headshot_url').val('');
			jQuery('#headshot_preview').remove();
			jQuery('#upload_success').text('Picture removed.');
		});



		jQuery('#avatar_button').on('click', function(){
			tb_show('Uploading a new avatar!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
			return false;
		});

		jQuery('#avatar_remove').on('click', function(){
			jQuery('#avatar_url').val('');
			jQuery('#avatar_preview').remove();
			jQuery('#upload_success').text('Picture removed.');
		});
	}
}

//Notify for possible duplicate post slug
function nebulaUniqueSlugChecker(){
	if ( jQuery('.edit-post-post-link__link-post-name').length ){
		if ( jQuery('.edit-post-post-link__link-post-name').text().match(/(-\d+)\/?$/) ){
			jQuery('a.edit-post-post-link__link').css('color', 'red');
			jQuery('.edit-post-post-link__preview-label').html('<span title="This likely indicates a duplicate post, but will not prevent saving or publishing." style="cursor: help;">Possible duplicate:</span>');
		}
	}
}

//Allow tab character in textareas
(function($){
    function pasteIntoInput(el, text){
        el.focus();
        var val = el.value;
        if ( typeof el.selectionStart === 'number' ){
            var selStart = el.selectionStart;
            el.value = val.slice(0, selStart) + text + val.slice(el.selectionEnd);
            el.selectionEnd = el.selectionStart = selStart + text.length;
        } else if ( typeof document.selection !== 'undefined' ){
            var textRange = document.selection.createRange();
            textRange.text = text;
            textRange.collapse(false);
            textRange.select();
        }
    }

    function allowTabChar(el){
        jQuery(el).keydown(function(e){
            if ( e.which === 9 ){
                pasteIntoInput(this, '\t');
                return false;
            }
        });

        // For Opera, which only allows suppression of keypress events, not keydown
        jQuery(el).keypress(function(e){
            if ( e.which === 9 ){
                return false;
            }
        });
    }

    $.fn.allowTabChar = function(){
        if (this.jquery){
            this.each(function(){
                if ( this.nodeType === 1 ){
                    var nodeName = this.nodeName.toLowerCase();
                    if ( nodeName === 'textarea' || (nodeName === 'input' && this.type === 'text') ){
                        allowTabChar(this);
                    }
                }
            });
        }
        return this;
    };
})(jQuery);

//container is the parent container, parent is the individual item, value is usually the input val.
function keywordSearch(container, parent, value, filteredClass){
	if ( !filteredClass ){
		var filteredClass = 'filtereditem';
	}
	jQuery(container).find('*:not(:Contains(' + value + '))').closest(parent).addClass(filteredClass);
	jQuery(container).find('*:Contains(' + value + ')').closest(parent).removeClass(filteredClass);
}

/*==========================
 Utility Functions
 These functions simplify and enhance other JavaScript functions
 ===========================*/

//Get query string parameters
function getQueryStrings(url){
	if ( !url ){
		url = document.URL;
	}

	var queries = {};
	var queryString = url.split('?')[1];

	if ( queryString ){
		var queryStrings = queryString.split('&');
		for ( var i = 0; i < queryStrings.length; i++ ){
			var hash = queryStrings[i].split('=');
			if ( hash[1] ){
				queries[hash[0]] = hash[1];
			} else {
				queries[hash[0]] = true;
			}
		}
	}

	return queries;
}

//Search query strings for the passed parameter
function get(parameter, url){
	var queries = getQueryStrings(url);

	if ( !parameter ){
		return queries;
	}

	return queries[parameter] || false;
}

//Remove an array of parameters from the query string.
function removeQueryParameter(keys, sourceURL){
	if ( typeof keys === 'string' ){
		keys = [keys];
	}

	jQuery.each(keys, function(index, item){
		var url = sourceURL;
		if ( typeof newURL !== 'undefined' ){
			url = newURL;
		}

		var baseURL = url.split('?')[0];
		var param;
		var params_arr = [];
		var queryString = ( url.indexOf('?') !== -1 )? url.split('?')[1] : '';

		if ( queryString !== '' ){
			params_arr = queryString.split('&');

			for ( var i = params_arr.length-1; i >= 0; i -= 1 ){
				param = params_arr[i].split('=')[0];
				if ( param === item ){
					params_arr.splice(i, 1);
				}
			}

			var newURL = baseURL + '?' + params_arr.join('&');
		}
	});

	//Check if it is empty after parameter removal
	if ( typeof newURL !== 'undefined' && newURL.split('?')[1] === '' ){
		return newURL.split('?')[0]; //Return the URL without a query
	}

	return newURL;
}

//Custom CSS expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: nebula.keywordSearch(container, parent, value);
jQuery.expr.pseudos.Contains = function(element, index, match){
	return (element.textContent || element.innerText || '').toUpperCase().indexOf(match[3].toUpperCase()) >= 0;
};

//Nebula Options Functions

//Make sure the sticky nav is shorter than the viewport height.
function checkWindowHeightForStickyNav(){
	if ( window.innerHeight > jQuery('#stickynav').outerHeight() ){
		jQuery('#stickynav').addClass('sticky');
	} else {
		jQuery('#stickynav').removeClass('sticky');
	}
}

//Check for empty, but important options and show an icon on the navigation item
function checkImportants(){
	jQuery('.important-option').each(function(){
		if ( !isCheckedOrHasValue(jQuery(this).find('input')) && !isImportantAlternativeValue(jQuery(this).attr('important-or')) ){
			if ( !jQuery(this).find('.important-warning').length ){ //If the warning isn't already showing
				jQuery(this).addClass('important-empty').find('label').append('<p class="important-warning">It is highly recommended this option (or a related option) is used!</p>');
			}
		} else {
			jQuery(this).removeClass('important-empty');
			jQuery(this).find('.important-warning').remove();
		}
	});

	jQuery('.tab-pane').each(function(){
		if ( jQuery(this).find('.important-empty').length ){
			if ( !jQuery('.nav-link[href$=' + jQuery(this).attr('id') + '] .empty-important-tab-warn').length ){ //If the warning isn't already showing
				jQuery('.nav-link[href$=' + jQuery(this).attr('id') + ']').append('<i class="fas fa-fw fa-exclamation-triangle empty-important-tab-warn"></i>');
			}
		} else {
			jQuery('.nav-link[href$=' + jQuery(this).attr('id') + ']').find('.empty-important-tab-warn').remove();
		}
	});
}

//Check if an alternative important ID has value
function isImportantAlternativeValue(alternateIDs){
	var anyImportantAltValue = false;
	jQuery('#' + alternateIDs).each(function(){
		if ( isCheckedOrHasValue(jQuery(this)) ){
			anyImportantAltValue = true;
			return true;
		}
	});

	return anyImportantAltValue;
}

//Use the attribute dependent-of="" with the id of the dependent checkbox
function checkDependents(inputObject){
	if ( inputObject ){ //Check a single option's dependents
		if ( isCheckedOrHasValue(inputObject) ){
			jQuery('[dependent-of=' + inputObject.attr('id') + ']').removeClass('inactive').find('.dependent-note').addClass('hidden');
			jQuery('[dependent-or~=' + inputObject.attr('id') + ']').removeClass('inactive').find('.dependent-note').addClass('hidden');

			//The dependent-and attribute must have ALL checked
			jQuery('[dependent-and~=' + inputObject.attr('id') + ']').each(function(){
				var oThis = jQuery(this);
				var dependentOrs = jQuery(this).attr('dependent-and').split(' ');
				var totalDependents = dependentAnds.length;
				var dependentsChecked = 0;
				jQuery.each(dependentAnds, function(){
					if ( isCheckedOrHasValue(jQuery('#' + this)) ){
						dependentsChecked++;
					}
				});

				if ( dependentsChecked === totalDependents ){
					oThis.removeClass('inactive').find('.dependent-note').addClass('hidden');
				}
			});
		} else {
			jQuery('[dependent-of=' + inputObject.attr('id') + ']').addClass('inactive').find('.dependent-note').removeClass('hidden');
			jQuery('[dependent-and~=' + inputObject.attr('id') + ']').addClass('inactive').find('.dependent-note').removeClass('hidden');

			//The dependent-or attribute can have ANY checked
			jQuery('[dependent-or~=' + inputObject.attr('id') + ']').each(function(){
				var oThis = jQuery(this);
				var dependentOrs = jQuery(this).attr('dependent-or').split(' ');
				var totalDependents = dependentOrs.length;
				var dependentsUnchecked = 0;
				jQuery.each(dependentOrs, function(){
					if ( !isCheckedOrHasValue(jQuery('#' + this)) ){
						dependentsUnchecked++;
					}
				});

				if ( dependentsUnchecked === totalDependents ){
					oThis.addClass('inactive').find('.dependent-note').removeClass('hidden');
				}
			});
		}
	} else { //Check all dependencies
		jQuery('input, textarea').each(function(){
			checkDependents(jQuery(this));
			jQuery(this).trigger('blur'); //Trigger validation on all inputs
		});
	}
}




function isCheckedOrHasValue(inputObject){
	if ( inputObject.is('[type=checkbox]:checked') ){
		return true;
	}

	if ( !inputObject.is('[type=checkbox]') && inputObject.val().length > 0 ){
		return true;
	}

	return false;
}



















//Functions pulled from nebula.js for various admin usages (mostly Nebula Options)
//Use modules and import them here in the future to reduce tech debt

//Regex Patterns
//Test with: if ( regexPattern.email.test(jQuery('input').val()) ){ ... }
window.regexPattern = {
	email: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, //From JS Lint: Expected ']' and instead saw '['.
	phone: /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/, //To allow letters, you'll need to convert them to their corresponding number before matching this RegEx.
	date: {
		mdy: /^((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))[.\/-](\d{4}|\d{2})$/,
		ymd: /^(\d{4}|\d{2})[.\/-]((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))$/,
	},
	hex: /^#?([a-f0-9]{6}|[a-f0-9]{3})$/,
	ip: /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
	url: /\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/,
};

//Offset must be an integer
function nebulaScrollTo(element, milliseconds, offset, onlyWhenBelow){
	if ( !offset ){
		var offset = 0; //Note: This selector should be the height of the fixed header, or a hard-coded offset.
	}

	//Call this function with a jQuery object to trigger scroll to an element (not just a selector string).
	if ( element ){
		var willScroll = true;
		if ( onlyWhenBelow ){
			var elementTop = element.offset().top-offset;
			var viewportTop = jQuery(document).scrollTop();
			if ( viewportTop-elementTop <= 0 ){
				willScroll = false;
			}
		}

		if ( willScroll ){
			if ( !milliseconds ){
				var milliseconds = 500;
			}

			jQuery('html, body').animate({
				scrollTop: element.offset().top-offset
			}, milliseconds, function(){
				//callback
			});
		}

		return false;
	}
}

//Remove a parameter from the query string.
function removeQueryParameter(key, sourceURL){
    var rtn = sourceURL.split('?')[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf('?') !== -1) ? sourceURL.split('?')[1] : '';

    if ( queryString !== '' ){
        params_arr = queryString.split('&');

        for ( i = params_arr.length-1; i >= 0; i -= 1 ){
            param = params_arr[i].split('=')[0];
            if ( param === key ){
                params_arr.splice(i, 1);
            }
        }

        rtn = rtn + '?' + params_arr.join('&');
    }

	//Check if it is empty after parameter removal
	if ( rtn.split('?')[1] === '' ){
		return rtn.split('?')[0]; //Return the URL without a query
	}

    return rtn;
}

//Form live (soft) validator
function nebulaLiveValidator(){
	//Standard text inputs and select menus
	jQuery(document).on('keyup change blur', '.nebula-validate-text, .nebula-validate-textarea, .nebula-validate-select', function(e){
		if ( jQuery(this).val() === '' ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( jQuery.trim(jQuery(this).val()).length ){
			applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//RegEx input
	jQuery(document).on('keyup change blur', '.nebula-validate-regex', function(e){
		var pattern = new RegExp(jQuery(this).attr('data-valid-regex'), 'i');
		if ( jQuery(this).val() === '' ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( pattern.test(jQuery(this).val()) ){
			applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//URL inputs
	jQuery(document).on('keyup change blur', '.nebula-validate-url', function(e){
		if ( jQuery(this).val() === '' ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( regexPattern.url.test(jQuery(this).val()) ){
			applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Email address inputs
	jQuery(document).on('keyup change blur', '.nebula-validate-email', function(e){
		if ( jQuery(this).val() === '' ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( regexPattern.email.test(jQuery(this).val()) ){
			applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Phone number inputs
	jQuery(document).on('keyup change blur', '.nebula-validate-phone', function(e){
		if ( jQuery(this).val() === '' ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( regexPattern.phone.test(jQuery(this).val()) ){
			applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Date inputs
	jQuery(document).on('keyup change blur', '.nebula-validate-date', function(e){
		if ( jQuery(this).val() === '' ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( regexPattern.date.mdy.test(jQuery(this).val()) ){ //Check for MM/DD/YYYY (and flexible variations)
			applyValidationClasses(jQuery(this), 'valid', false);
		} else if ( regexPattern.date.ymd.test(jQuery(this).val()) ){ //Check for YYYY/MM/DD (and flexible variations)
			applyValidationClasses(jQuery(this), 'valid', false);
		} else if ( strtotime(jQuery(this).val()) && strtotime(jQuery(this).val()) > -2208988800 ){ //Check for textual dates (after 1900) //The JS version of strtotime() isn't the most accurate function...
			applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Checkbox and Radio
	jQuery(document).on('change blur', '.nebula-validate-checkbox, .nebula-validate-radio', function(e){
		if ( jQuery(this).closest('.form-group').find('input:checked').length ){
			applyValidationClasses(jQuery(this), 'reset', false);
		} else {
			applyValidationClasses(jQuery(this), 'invalid', true);
		}
	});
}

//Apply Bootstrap appropriate validation classes to appropriate elements
function applyValidationClasses(element, validation, showFeedback){
	if ( typeof element === 'string' ){
		element = jQuery(element);
	} else if ( typeof element !== 'object' ) {
		return false;
	}

	if ( validation === 'success' || validation === 'valid' ){
		element.removeClass('wpcf7-not-valid is-invalid').addClass('is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	} else if ( validation === 'danger' || validation === 'error' || validation === 'invalid' ){
		element.removeClass('wpcf7-not-valid is-valid').addClass('is-invalid');
	} else if ( validation === 'reset' || validation === 'remove' ){
		element.removeClass('wpcf7-not-valid is-invalid is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	}

	if ( validation === 'feedback' || showFeedback ){
		element.parent().find('.invalid-feedback').removeClass('hidden');
	} else {
		element.parent().find('.invalid-feedback').addClass('hidden');
	}
}

//Record performance timing
function performanceMetrics(){
	if ( window.performance && window.performance.timing && !window.MSInputMethodContext && !document.documentMode ){ //Safari 11+ and no IE
		setTimeout(function(){
			var responseEnd = Math.round(performance.timing.responseEnd-performance.timing.navigationStart); //Navigation start until server response finishes
			var domReady = Math.round(performance.timing.domContentLoadedEventStart-performance.timing.navigationStart); //Navigation start until DOM ready
			var windowLoaded = Math.round(performance.timing.loadEventStart-performance.timing.navigationStart); //Navigation start until window load

			var clientTimings = {
				'[JS] Server Response': {
					'start': 0,
					'duration': responseEnd,
					'elapsed': responseEnd
				},
				'[JS] DOM Ready': {
					'start': responseEnd,
					'duration': domReady-responseEnd,
					'elapsed': domReady
				},
				'[JS] Window Load': {
					'start': domReady,
					'duration': windowLoaded-domReady,
					'elapsed': windowLoaded
				},
				'[JS] Load Time (Total)': {
					'start': 0,
					'duration': windowLoaded,
					'elapsed': windowLoaded
				}
			};

			console.groupCollapsed('Performance');
			console.table(jQuery.extend(nebula.site.timings, clientTimings));
			console.groupEnd();
		}, 0);
	}
}

//Waits for events to finish before triggering
//Passing immediate triggers the function on the leading edge (instead of the trailing edge).
function debounce(callback, wait, uniqueID, immediate){
	if ( typeof debounceTimers === 'undefined' ){
		var debounceTimers = {};
	}

	if ( !uniqueID ){
		uniqueID = 'Do not call this twice without a uniqueID';
	}

	var context = this, args = arguments;
	var later = function(){
		debounceTimers[uniqueID] = null;
		if ( !immediate ){
			callback.apply(context, args);
		}
	};
	var callNow = immediate && !debounceTimers[uniqueID];

	clearTimeout(debounceTimers[uniqueID]);
	debounceTimers[uniqueID] = setTimeout(later, wait);
	if ( callNow ){
		callback.apply(context, args);
	}
}


function nebulaLogs(){
	//Add a message to logs
	jQuery(document).on('click', '#submit-log-message', function(){
		var logMessage = jQuery.trim(jQuery('#log-message').val());
		var logImportance = jQuery.trim(jQuery('#log-importance').val()) || 4;

		if ( logMessage ){
			jQuery('#add-log-progress').removeClass('fa-calendar-plus').addClass('fa-spinner fa-spin');

			jQuery.ajax({
				type: 'POST',
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'add_log',
					data: [{
						message: logMessage,
						importance: logImportance,
					}]
				},
				success: function(response){
					jQuery('#add-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-calendar-plus');

					//Reload just the table
					jQuery('#nebula-log-reload-container').load(window.location.href +  ' #nebula-logs', function(){
						jQuery('#log-count').text(jQuery('#nebula-logs tr').not('.removed').length); //Re-count rows

						//Empty the inputs
						jQuery('#log-message').val('');
						jQuery('#log-importance').val('5');
					});
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					jQuery('#add-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-calendar-plus');
				},
				timeout: 60000
			});
		}

		return false;
	});

	//Remove a message from logs
	jQuery(document).on('click', 'table#nebula-logs tbody tr', function(){
		var oThis = jQuery(this);
		var logID = oThis.attr('data-id');

		oThis.addClass('prompted');

		if ( logID && confirm('Are you sure you want to delete this message from the log? There is no undo.') ){
			oThis.find('.remove').removeClass('fa-ban').addClass('fa-spinner fa-spin');

			var logCount = parseInt(jQuery('#log-count').text()); //Number of log rows before removal

			jQuery.ajax({
				type: 'POST',
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'remove_log',
					data: [{
						id: logID,
					}]
				},
				success: function(response){
					//Artificially update the table without doing a reload of the whole page in case there are unsaved changes!
					oThis.fadeOut(250).addClass('removed'); //Artificially hide the removed row
					jQuery('#log-count').text(logCount-1); //Artificially update the log count
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					oThis.find('.remove').removeClass('fa-spinner fa-spin').addClass('fa-ban');
				},
				timeout: 60000
			});
		} else {
			jQuery(this).removeClass('prompted');
		}

		return false;
	});

	//Clean low importance logs
	jQuery(document).on('click', '#clean-log-messages', function(){
		if ( confirm('Are you sure you want to delete low importance log messages? There is no undo.') ){
			jQuery('#clean-log-progress').removeClass('fa-trash-alt').addClass('fa-spinner fa-spin');

			jQuery.ajax({
				type: 'POST',
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'clean_logs',
					data: [{
						importance: 4,
					}]
				},
				success: function(response){
					jQuery('#nebula-log-reload-container').load(window.location.href +  ' #nebula-logs', function(){
						jQuery('#log-count').text(jQuery('#nebula-logs tr').not('.removed').length); //Re-count rows
						jQuery('#clean-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-trash-alt');
					}); //Reload just the table
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					jQuery('#clean-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-trash-alt');
				},
				timeout: 60000
			});
		}

		return false;
	});
}