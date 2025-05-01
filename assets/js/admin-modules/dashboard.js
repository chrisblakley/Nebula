window.performance.mark('(Nebula) Inside /admin-modules/dashboard.js');

//Developer Metabox functions
nebula.developerMetaboxes = function(){
	//Developer Info Metabox
	if ( jQuery('div#nebula_developer_info').length ){
		if ( jQuery('.serverdetections').length ){ //If viewing the dashboard
			if ( !jQuery('.nebula-adb-tester').is(':visible') ){
				jQuery('.serverdetections').prepend('<li class="nebula-adb-reminder essential"><i class="fa-solid fa-shield-halved"></i> Your ad-blocker is enabled</li>');
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

	//File Size Monitor Metabox
	if ( jQuery('div#nebula_file_size_monitor').length ){
		nebula.fileSizeMonitorTableFilter(); //Run once immediately on load

		//Dropdown Filters
		jQuery(document).on('change', '#nebula_file_size_monitor .filter-row select', function(e){
			jQuery('#nebula_file_size_monitor .simplify').removeClass('simplify');
			jQuery('#filegroup-filter').removeClass('initial-state'); //It is now no longer in the initial state
			nebula.fileSizeMonitorTableFilter();
		});

		//Change to all files when intending to filter by keyword
		jQuery(document).on('focus keydown', '#nebula_file_size_monitor #filekeyword-filter', function(e){
			jQuery('#nebula_file_size_monitor .simplify').removeClass('simplify');

			//If keyword searching but viewing the default selection, automatically change to all files
			if ( jQuery('#filegroup-filter option:selected').data('default') === true && jQuery('#filegroup-filter').hasClass('initial-state') ){ //If it is still in the initial state of the default selected group
				jQuery('#filegroup-filter').val(''); //Automatically change to search all files
				jQuery('#filegroup-filter').trigger('change');
				jQuery('#filegroup-filter').removeClass('initial-state'); //It is now no longer in the initial state
			}
		});

		//Keyword Search Filter
		jQuery(document).on('keyup', '#nebula_file_size_monitor #filekeyword-filter', function(e){
			//Ignore meta keys
			if ( ['Shift', 'Control', 'Alt', 'Meta'].includes(e.key) ){
				return;
			}

			nebula.keywordFilter('#nebula_file_size_monitor table tbody', 'tr', jQuery(this).val()); //Run the filter

			setTimeout(function(){
				if ( jQuery('#nebula_file_size_monitor #filekeyword-filter').val().length ){
					jQuery('.clear-keywords').removeClass('transparent');
				} else {
					jQuery('.clear-keywords').addClass('transparent');
				}

				let visibleRowCount = jQuery('#nebula_file_size_monitor table tbody tr:not(.filtereditem):visible').length;
				jQuery('.totals-row .total-showing').text(visibleRowCount);

				//Show or hide the "No Files" message depending if we have any results
				if ( visibleRowCount === 0 ){
					jQuery('.no-files-message').removeClass('hidden');
				} else {
					jQuery('.no-files-message').addClass('hidden');
				}
			}, 10);
		});

		jQuery(document).on('click', '.show-optimization-tips', function(){
			jQuery('#nebula-optimization-tips').slideDown();
			jQuery('.show-optimization-tips').remove(); //Once it is clicked it stays open
			return false;
		});

		//Clear keyword search input
		jQuery(document).on('click', '#nebula_file_size_monitor .clear-keywords', function(){
			jQuery('#nebula_file_size_monitor #filekeyword-filter').val('');
			jQuery('#nebula_file_size_monitor #filekeyword-filter').trigger('keyup');
			return false;
		});

		//Reset all filters to default
		jQuery(document).on('click', '#nebula_file_size_monitor .reset-filters', function(){
			jQuery('#nebula_file_size_monitor #filekeyword-filter').val('');
			jQuery('#filegroup-filter').val('largest'); //Use this as the default value now regardless of initial state
			jQuery('#filetype-filter').val(''); //First value
			jQuery('#nebula_file_size_monitor #filekeyword-filter').trigger('keyup');
			jQuery('#nebula_file_size_monitor #filegroup-filter').trigger('change');
			return false;
		});
	}
};

nebula.fileSizeMonitorTableFilter = function(){
	let selectedGroup = jQuery('#filegroup-filter').val();
	let selectedType = jQuery('#filetype-filter').val();
	let visibleIndex = 0; //The number of visible rows
	let anyVisibleRows = false; //If any rows are visible after filtering

	//Loop through the rows now
	jQuery('#nebula_file_size_monitor table tbody tr').each(function(i, row){
		let $thisRow = jQuery(row);
		let showRow = false;

		$thisRow.removeClass('alt-row'); //Remove the zebra-striping so it can be re-added after filtering

		let matchesGroup = (!selectedGroup || $thisRow.data('group') === selectedGroup); //Boolean if this row matches the selected file group (or if no group is selected)
		let matchesType = (!selectedType || $thisRow.data('type') === selectedType); //Boolean if this row matches the selected file type (or if no type is selected)

		//Check if this row meets the dropdown filter criteria
		if ( selectedGroup === 'largest' && matchesType ){
			if ( i < 10 ){
				showRow = true;
			}
		} else if ( selectedGroup === 'overbudget' && $thisRow.hasClass('overbudget') && matchesType ){
			showRow = true;
		} else if ( selectedGroup === 'nearbudget' && $thisRow.hasClass('approaching-budget') && matchesType ){
			showRow = true;
		} else if ( selectedGroup === 'recent' && $thisRow.is(':has(.recently-modified)') && matchesType ){
			showRow = true;
		} else if ( selectedGroup === 'security' && $thisRow.is(':has(.security-concern)') && matchesType ){
			showRow = true;
		} else if ( matchesGroup && matchesType ){
			showRow = true;
		}

		$thisRow.toggle(showRow); //Show or hide the row

		if ( showRow ){
			anyVisibleRows = true; //We do have results

			//Prep for zebra striping
			if ( visibleIndex%2 === 0 ){
				$thisRow.addClass('alt-row');
			}

			visibleIndex++;
		}
	});

	//Check if a "generic" group or type filter is selected
	let genericGroups = ['All Groups', 'largest', 'overbudget', 'nearbudget', 'recent', 'security'];

	let hasSpecificFilter = false;
	if ( (selectedType && selectedType !== 'All Types') || (selectedGroup && !genericGroups.includes(selectedGroup)) ){
		hasSpecificFilter = true;
	}

	//Show or hide certain columns
	if ( hasSpecificFilter ){
		jQuery('#nebula_file_size_monitor table th.file-group, #nebula_file_size_monitor table td.file-group').addClass('hidden');
	} else {
		jQuery('#nebula_file_size_monitor table th.file-group, #nebula_file_size_monitor table td.file-group').removeClass('hidden');
	}

	//Update the file type text to match the selected filter
	if ( selectedType && selectedType !== 'All Types' ){
		jQuery('.filetype').text(selectedType);
	} else if ( selectedGroup && !genericGroups.includes(selectedGroup) ){
		jQuery('.filetype').text(selectedGroup);
	} else {
		jQuery('.filetype').text('');
	}

	//Show or hide the modified date
	jQuery('.modified-info').addClass('hidden');
	if ( selectedGroup == 'recent' ){
		jQuery('.modified-info').removeClass('hidden');
	}

	//Show the budgeted size for this group (based on the first visible result)
	let visibleGroupBudget = jQuery('#nebula_file_size_monitor table tbody tr').filter(':visible').first();
	let budgetText = (visibleGroupBudget.data('budget') || '').toString().trim().toLowerCase();
	jQuery('.sizebudget').text(budgetText);
	let hideBudget = ( !budgetText || budgetText === '0' || budgetText === '0b' ); //Boolean if the budget for this group is empty or 0

	//Show or hide the budget description text and percent column
	if ( hasSpecificFilter && !hideBudget ){
		jQuery('.budget-description').removeClass('hidden');
		jQuery('#nebula_file_size_monitor table th.budget-percent, #nebula_file_size_monitor table td.budget-percent').removeClass('hidden');
	} else {
		jQuery('.budget-description').addClass('hidden');
		jQuery('#nebula_file_size_monitor table th.budget-percent, #nebula_file_size_monitor table td.budget-percent').addClass('hidden');
	}

	jQuery('.no-files-message').toggle(!anyVisibleRows); //Show or hide the "No Files" message depending if we have any results
	jQuery('.totals-row .total-showing').text(visibleIndex.toLocaleString());

	//Only show tips for this file group
	if ( jQuery('#nebula-optimization-tips li[data-group*="' + selectedGroup.toLowerCase() + '"]').length ){
		jQuery('.show-optimization-tips').removeClass('hidden');
		jQuery('#nebula-optimization-tips').removeClass('hidden');
	} else {
		jQuery('.show-optimization-tips').addClass('hidden');
		jQuery('#nebula-optimization-tips').addClass('hidden');
	}

	jQuery('#nebula-optimization-tips li').addClass('hidden');
	jQuery('#nebula-optimization-tips li[data-group*="' + selectedGroup.toLowerCase() + '"]').removeClass('hidden'); //Maybe use the case-insensitive "i" here eventually?
	jQuery('#nebula-optimization-tips li.general').removeClass('hidden');
};






//Check the page speed using (in this priority) Google Lighthouse, or a rudimentary iframe timing
nebula.checkPageSpeed = function(){
	jQuery('#performance_metabox h2 i').removeClass('fa-stopwatch').addClass('fa-spinner fa-spin');

	if ( location.hostname === 'localhost' || location.hostname === '127.0.0.1' ){ //If localhost or other "invalid" URL. This doesn't catch local TLDs, but the logic below will figure it out eventually.
		jQuery('#performance-sub-status strong').text('Using iframe test due to local development.');
		nebula.runIframeSpeedTest('Local Development Environment');
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
	}).then(async function(json){
		if ( json && json.captchaResult === 'CAPTCHA_NOT_NEEDED' ){
			await nebula.yield();

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
			let reason = '';

			if ( json.error ){
				reason = 'Lighthouse Error Code ' + json.error.code;
				console.warn('Received Lighthouse error code:', json.error.code, json.error.message);

				if ( json.error.details[0].reason ){
					reason = 'Lighthouse ' + json.error.details[0].reason.replaceAll('_', ' ').toLowerCase();
				}
			}

			console.warn('Fetch data is not expected from Lighthouse. Running iframe test instead.', json);
			nebula.runIframeSpeedTest(reason);
		}
	}).catch(function(error){
		console.warn('Google Lighthouse failed. Reverting to iframe test.', error);
		jQuery('#performance-sub-status strong').text('Google Lighthouse failed. Reverting to iframe test.');
		nebula.runIframeSpeedTest('Google Lighthouse Failed'); //If Google Lighthouse check fails, time with an iframe instead...
	});
};

//Load the home page in an iframe and time the DOM and Window load times
nebula.runIframeSpeedTest = async function(reason=''){
	await nebula.yield();

	jQuery('#performance_metabox h2 span span').html('Measuring Performance <small>(via Iframe)</small>');

	var iframe = document.createElement('iframe');
	iframe.style.width = '1200px';
	iframe.style.height = '0px';
	iframe.src = jQuery('#testloadcon').attr('data-src') + '?noga'; //Cannot use nebula.site.home_url here for some reason even though it obeys https. No GA so it does not get flooded with bot traffic
	jQuery('#testloadcon').append(iframe);

	jQuery('#testloadcon iframe').on('load', async function(){
		await nebula.yield();

		console.log('Iframe Performance Data:', JSON.parse(JSON.stringify(iframe.contentWindow.performance))); //Needs to stringify/parse to de-synchronize the object and retain the actual values (just for this output)

		if ( reason ){
			jQuery('#performance-sub-reason').removeClass('hidden').find('.label').html('Reason: <strong>' + reason + '</strong>');
		}

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
		jQuery('#performance-sub-status strong').text('Iframe test completed');
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

nebula.simplifiedViewToggle = function(){
	jQuery('.expand-simplified-view, .expand-simplified-view a').on('click', function(){
		jQuery(this).parents('.inside').find('ul li:not(.ignore-simplify)').slideDown(); //Show the rest of the list items
		jQuery(this).parents('.inside').find('.expand-simplified-view, .expand-simplified-view a').slideUp(); //Hide the toggle link itself
		return false;
	});
};