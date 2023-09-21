window.performance.mark('(Nebula) Inside /admin-modules/options.js');

nebula.optionsInit = function(){
	//These happen on DOM ready
	nebula.checkWindowHeightForStickyNav();
	nebula.liveValidator();
	nebula.logs();

	//These wait for Window load
	nebula.bufferedWindowLoad(function(){
		nebula.optionsFilters();
		nebula.assetScan();

		jQuery(document).on('shown.bs.tab', function(e){
			if ( jQuery('.cookie-label').is(':visible') && !jQuery('#cookie_notification').is(':visible') ){
				jQuery('.nebula-adb-reminder-con').prepend('<strong class="nebula-adb-reminder">Your ad-blocker is hiding this option field!</strong><br/>');
			}
		});
	});

	//Window Resize
	window.addEventListener('resize', function(){
		nebula.checkWindowHeightForStickyNav();
	});

	//If there are no active tabs on load (like if wrong ?tab= parameter was used)
	if ( !jQuery('#options-navigation li a.active').length ){
		jQuery('#options-navigation').find('li:first-child a').addClass('active');
		jQuery('#nebula-options-section').find('.tab-pane:first-child').addClass('active');
	}

	//Scroll to the top when changing tabs
	jQuery('a.nav-link').on('shown.bs.tab', function(){
		//Update the URL to reflect the active tab
		var url = nebula.site.admin_url + 'themes.php?page=nebula_options&tab=' + jQuery('#options-navigation a.active').attr('href').replace('#', '');
		history.replaceState(null, document.title, url);

		jQuery('html, body').animate({
			scrollTop: jQuery('#nebula-options-section').offset().top-100
		}, 500);
	});

	if ( nebula.get('prefiltered') ){
		jQuery('#nebula-option-filter').trigger('keydown').focus(); //Trigger if a ?prefiltered= parameter is used.
	}

	//Sync options checkboxes to their hidden text input values.
	//This prevents an edge case where unchecked checkboxes are NULL rather than false, so they get re-enabled on multisite Nebula updates
	jQuery(document).on('change', '.sync-checkbox', function(){
		let value = ( jQuery(this).is(':checked') )? 1 : 0; //Because the value is stored in text we need 1 and 0 specifically rather than "true" and "false"
		jQuery(this).parents('.form-group').find('input[type="hidden"]').val(value);
	});

	nebula.checkDependents(); //Check all dependents
	nebula.checkImportants();
	jQuery('input').on('keyup change', function(){
		nebula.checkDependents(jQuery(this));
		nebula.checkImportants();
	});

	jQuery('.short-help').each(function(){
		if ( !jQuery(this).parents('.no-help').length ){
			if ( nebula.user.staff === 'developer' ){
				//Dev handle names
				var optionHandle = jQuery(this).closest('.form-group').find('[name^=nebula_options]').attr('id');
				if ( typeof optionHandle !== 'undefined' ){
					var devUsage = '<br/><br/><span class="dev-handle form-text text-muted">Dev usage: <code>nebula()->get_option(\'' + optionHandle + '\');</code></span>';
					if ( jQuery(this).parent().find('.more-help').length ){
						jQuery(this).closest('.form-group').find('.more-help').append(devUsage);
					} else {
						jQuery(this).after('<p class="nebula-help-text more-help form-text text-muted">' + devUsage + '</p>');
					}
				}
			}

			//More Help expander icons
			//if ( jQuery(this).parent().find('.more-help').length ){
				jQuery(this).append('<a class="toggle-more-help" href="#" title="Toggle more information" tabindex="-1"><i class="fa-solid fa-fw fa-question-circle"></i></a>');
			//}
		}
	});

	//Show/hide more information
	jQuery(document).on('click', '.toggle-more-help', function(){
		var $formGroup = jQuery(this).closest('.form-group, .multi-form-group');
		jQuery('.form-group.highlight, .multi-form-group.highlight').not($formGroup).removeClass('highlight').find('.more-help').slideUp(); //Un-highlight all other options
		$formGroup.toggleClass('highlight').find('.more-help').slideToggle(); //Toggle highlight on this option

		var thisTab = jQuery(this).closest('.tab-pane').attr('id');
		var thisOption = jQuery(this).closest('.form-group, .multi-form-group').find('.form-control').attr('id') || jQuery(this).closest('.form-group, .multi-form-group').find('label').attr('for');

		var url = nebula.site.admin_url + 'themes.php?page=nebula_options&tab=' + thisTab;
		if ( $formGroup.hasClass('highlight') ){
			url += '&option=' + thisOption;
		}
		history.replaceState(null, document.title, url); //Modify the URL so the direct link can be copied

		return false;
	});
};

nebula.optionsFilters = function(){
	//Option filter
	jQuery('#nebula-option-filter').on('keydown keyup change focus blur', function(e){
		nebula.debounce(function(){
			var url = nebula.site.admin_url + 'themes.php?page=nebula_options';
			if ( jQuery('#nebula-option-filter').val() !== '' ){
				url = nebula.site.admin_url + 'themes.php?page=nebula_options&prefiltered=' + jQuery('#nebula-option-filter').val();
			}

			history.replaceState(null, document.title, url);
		}, 1000, 'nebula options filter history api');

		//Prevent the form from submitting if pressing enter after searching
		if ( e.type === 'keydown' && e.key === 'Enter' ){
			e.preventDefault();
			return false;
		}

		if ( jQuery(this).val().length > 0 ){
			jQuery('.metabox-holder').addClass('filtering');
			jQuery('#reset-filter').removeClass('hidden');

			jQuery('#options-navigation').addClass('inactive').find('li a.active').removeClass('active');

			jQuery('.tab-pane').addClass('active');

			nebula.keywordFilter('#nebula-options-section', '.form-group', jQuery(this).val());

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
};

//Make sure the sidebar sticky nav is shorter than the viewport height.
nebula.checkWindowHeightForStickyNav = function(){
	if ( window.innerHeight > jQuery('#stickynav').outerHeight() ){
		jQuery('#stickynav').addClass('sticky');
	} else {
		jQuery('#stickynav').removeClass('sticky');
	}
};

//Use the attribute dependent-of="" with the id of the dependent checkbox
nebula.checkDependents = function(inputObject){
	if ( inputObject ){ //Check a single option's dependents
		if ( nebula.isCheckedOrHasValue(inputObject) ){
			jQuery('[dependent-of=' + inputObject.attr('id') + ']').removeClass('inactive').find('.dependent-note').addClass('hidden');
			jQuery('[dependent-or~=' + inputObject.attr('id') + ']').removeClass('inactive').find('.dependent-note').addClass('hidden');

			//The dependent-and attribute must have ALL checked
			jQuery('[dependent-and~=' + inputObject.attr('id') + ']').each(function(){
				var $oThis = jQuery(this);
				var dependentOrs = $oThis.attr('dependent-and').split(' ');
				var totalDependents = dependentAnds.length;
				var dependentsChecked = 0;
				jQuery.each(dependentAnds, function(){
					if ( nebula.isCheckedOrHasValue(jQuery('#' + this)) ){
						dependentsChecked++;
					}
				});

				if ( dependentsChecked === totalDependents ){
					$oThis.removeClass('inactive').find('.dependent-note').addClass('hidden');
				}
			});
		} else {
			jQuery('[dependent-of=' + inputObject.attr('id') + ']').addClass('inactive').find('.dependent-note').removeClass('hidden');
			jQuery('[dependent-and~=' + inputObject.attr('id') + ']').addClass('inactive').find('.dependent-note').removeClass('hidden');

			//The dependent-or attribute can have ANY checked
			jQuery('[dependent-or~=' + inputObject.attr('id') + ']').each(function(){
				var $oThis = jQuery(this);
				var dependentOrs = $oThis.attr('dependent-or').split(' ');
				var totalDependents = dependentOrs.length;
				var dependentsUnchecked = 0;
				jQuery.each(dependentOrs, function(){
					if ( !nebula.isCheckedOrHasValue(jQuery('#' + this)) ){
						dependentsUnchecked++;
					}
				});

				if ( dependentsUnchecked === totalDependents ){
					$oThis.addClass('inactive').find('.dependent-note').removeClass('hidden');
				}
			});
		}
	} else { //Check all dependencies
		jQuery('#nebula-options-section').find('input, textarea').each(function(){ //Never let this touch #nebula-option-filter
			nebula.checkDependents(jQuery(this));
			jQuery(this).trigger('blur'); //Trigger validation on all inputs
		});
	}
};

//Check for empty, but important options and show an icon on the navigation item
nebula.checkImportants = function(){
	jQuery('.important-option').each(function(){
		if ( !nebula.isCheckedOrHasValue(jQuery(this).find('input')) && !nebula.isImportantAlternativeValue(jQuery(this).attr('important-or')) ){
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
			if ( !jQuery('.nav-link[href$=' + jQuery(this).attr('id') + '] .empty-important-tab-warning').length ){ //If the warning isn't already showing
				jQuery('.nav-link[href$=' + jQuery(this).attr('id') + ']').append('<i class="fa-solid fa-fw fa-exclamation-triangle empty-important-tab-warning"></i>');
			}
		} else {
			jQuery('.nav-link[href$=' + jQuery(this).attr('id') + ']').find('.empty-important-tab-warnings').remove();
		}
	});
};

//Check if an alternative important ID has value
nebula.isImportantAlternativeValue = function(alternateIDs){
	var anyImportantAltValue = false;
	jQuery('#' + alternateIDs).each(function(){
		if ( nebula.isCheckedOrHasValue(jQuery(this)) ){
			anyImportantAltValue = true;
			return true;
		}
	});

	return anyImportantAltValue;
};

nebula.isCheckedOrHasValue = function(inputObject){
	if ( inputObject.is('[type=checkbox]:checked') ){
		return true;
	}

	if ( !inputObject.is('[type=checkbox]') && inputObject.val().length > 0 ){
		return true;
	}

	return false;
};

nebula.assetScan = function(){
	//Run automatic asset scan when button is clicked
	jQuery('.scan-frontend-assets').on('click', function(){ //Note there are two of these sections (one for styles, one for scripts). This will handle both simultaneously.
		let $oThis = jQuery(this);
		if ( $oThis.attr('data-skip-fetch') !== 'true' ){ //Use Fetch unless it fails
			var initialText = $oThis.html();

			$oThis.html('<i class="fa-solid fa-fw fa-spin fa-spinner"></i> Scanning Front-End...');
			jQuery('.asset-scan-status').html('Automatic asset scan in progress...');

			fetch(nebula.site.home_url + '?nebula-scan', {
				method: 'GET',
				headers: {
					'Cache-Control': 'no-cache',
				}
			}).then(function(response){
				if ( response.ok ){
					$oThis.html(initialText);
					jQuery('.asset-scan-status').html('<strong class="nebula-enabled"><i class="fa-solid fa-fw fa-check"></i> Automatic scan successful.</strong> You may refresh this page when ready to see available assets.');
				}
			}).catch(function(error){
				$oThis.html('Manually Scan Front-End <i class="fa-solid fa-fw fa-external-link-alt"></i>');
				jQuery('.asset-scan-status').html('<strong class="nebula-disabled">Automatic scan failed.</strong> Click the button again to manually scan the front-end in a new window.').attr('data-skip-fetch', 'true');
			});

			return false;
		}
	});
};

nebula.logs = function(){
	//Add a message to logs
	jQuery(document).on('click', '#submit-log-message', function(){
		var logMessage = jQuery.trim(jQuery('#log-message').val());
		var logImportance = jQuery.trim(jQuery('#log-importance').val()) || 4;

		if ( logMessage ){
			jQuery('#add-log-progress').removeClass('fa-calendar-plus').addClass('fa-spinner fa-spin');

			fetch(nebula.site.ajax.url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Cache-Control': 'no-cache',
				},
				body: new URLSearchParams({
					nonce: nebula.site.ajax.nonce,
					action: 'add_log',
					message: logMessage,
					importance: logImportance,
				})
			}).then(function(response){
				if ( response.ok ){
					jQuery('#add-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-calendar-plus');

					//Reload just the table
					jQuery('#nebula-log-reload-container').load(window.location.href + ' #nebula-logs', function(){
						jQuery('#log-count').text(jQuery('#nebula-logs tr').not('.removed').length); //Re-count rows

						//Empty the inputs
						jQuery('#log-message').val('');
						jQuery('#log-importance').val('5');
					});
				}
			}).catch(function(error){
				jQuery('#add-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-calendar-plus');
			});
		}

		return false;
	});

	//Remove a message from logs
	jQuery(document).on('click', 'table#nebula-logs tbody tr', function(){
		var $oThis = jQuery(this);
		var logID = $oThis.attr('data-id');

		$oThis.addClass('prompted');

		if ( logID && confirm('Are you sure you want to delete this message from the log? There is no undo.') ){
			$oThis.find('.remove').removeClass('fa-ban').addClass('fa-spinner fa-spin');

			var logCount = parseInt(jQuery('#log-count').text()); //Number of log rows before removal

			fetch(nebula.site.ajax.url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Cache-Control': 'no-cache',
				},
				body: new URLSearchParams({
					nonce: nebula.site.ajax.nonce,
					action: 'remove_log',
					id: logID,
				})
			}).then(function(response){
				if ( response.ok ){
					//Artificially update the table without doing a reload of the whole page in case there are unsaved changes!
					$oThis.fadeOut(250).addClass('removed'); //Artificially hide the removed row
					jQuery('#log-count').text(logCount-1); //Artificially update the log count
				}
			}).catch(function(error){
				$oThis.find('.remove').removeClass('fa-spinner fa-spin').addClass('fa-ban');
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
			fetch(nebula.site.ajax.url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Cache-Control': 'no-cache',
				},
				body: new URLSearchParams({
					nonce: nebula.site.ajax.nonce,
					action: 'clean_logs',
					importance: 4,
				})
			}).then(function(response){
				if ( response.ok ){
					jQuery('#nebula-log-reload-container').load(window.location.href + ' #nebula-logs', function(){
						jQuery('#log-count').text(jQuery('#nebula-logs tr').not('.removed').length); //Re-count rows
						jQuery('#clean-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-trash-alt');
					}); //Reload just the table
				}
			}).catch(function(error){
				jQuery('#clean-log-progress').removeClass('fa-spinner fa-spin').addClass('fa-trash-alt');
			});
		}

		return false;
	});
};