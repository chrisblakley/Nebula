window.performance.mark('(Nebula) Inside /modules/forms.js');

nebula.cf7Functions = async function(){
	if ( !jQuery('.wpcf7-form').length ){
		return false;
	}

	jQuery('.wpcf7-form p:empty').remove(); //Remove empty <p> tags within CF7 forms

	let formStarted = {};

	//Replace submit input with a button so a spinner icon can be used instead of the CF7 spin gif (unless it has the class "no-button")
	jQuery('.wpcf7-form input[type=submit]').each(function(){
		if ( !jQuery(this).hasClass('no-button') ){
			jQuery(this).replaceWith('<button id="submit" type="submit" class="' + nebula.sanitize(jQuery(this).attr('class')) + '">' + nebula.sanitize(jQuery(this).val()) + '</button>'); //Sanitized to prevent XSS
		}
	});

	//Observe CF7 Forms when they scroll into the viewport
	try {
		//Observe the entries that are identified and added later (below)
		let cf7Observer = new IntersectionObserver(function(entries){
			entries.forEach(function(entry){
				if ( entry.intersectionRatio > 0 ){
					let thisEvent = {
						event_name: 'cf7_form_impression',
						event_category: 'CF7 Form',
						event_action: 'Impression',
						event_label: jQuery(entry.target).closest('.wpcf7').attr('id') || jQuery(entry.target).attr('id'),
						form_id: jQuery(entry.target).closest('.wpcf7').attr('id') || jQuery(entry.target).attr('id'),
						non_interaction: true
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					if ( typeof gaEventObject === 'function' ){ //If the page is loaded pre-scrolled this may not be available for the very first intersection
						gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					}
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_cf7_impression'}));

					cf7Observer.unobserve(entry.target); //Stop observing the element
				}
			});
		}, {
			rootMargin: '0px',
			threshold: 0.10
		});

		//Create the entries and add them to the observer
		jQuery('.wpcf7-form').each(function(){
			cf7Observer.observe(jQuery(this)[0]); //Observe the element
		});
	} catch(error){
		nebula.help('CF7 Impression Observer: ' + error.message, '/functions/cf7Functions/', true);
	}

	//Re-init forms inside Bootstrap modals (to enable AJAX submission) when needed
	nebula.dom.document.on('shown.bs.modal', function(e){
		if ( typeof wpcf7.initForm === 'function' && jQuery(e.target).find('.wpcf7-form').length && !jQuery(e.target).find('.ajax-loader').length ){ //If initForm function exists, and a form is inside the modal, and if it has not yet been initialized (The initForm function adds the ".ajax-loader" span)
			wpcf7.initForm(jQuery(e.target).find('.wpcf7-form'));
		}
	});

	//Form starts and field focuses
	nebula.dom.document.on('focus', '.wpcf7-form input, .wpcf7-form select, .wpcf7-form button, .wpcf7-form textarea', function(e){
		let formID = jQuery(this).closest('div.wpcf7').attr('id'); //This wraps the form element

		let thisField = e.target.name || jQuery(this).closest('.form-group').find('label').text() || e.target.id || 'Unknown';
		let fieldInfo = '';
		if ( jQuery(this).attr('type') === 'checkbox' || jQuery(this).attr('type') === 'radio' ){
			fieldInfo = jQuery(this).attr('value');
		}

		if ( !jQuery(this).hasClass('.ignore-form') && !jQuery(this).find('.ignore-form').length && !jQuery(this).parents('.ignore-form').length ){
			let thisEvent = {
				event: e,
				event_category: 'CF7 Form',
				event_action: 'Started Form (Focus)',
				event_label: formID,
				form_id: formID, //Actual ID (not Unit Tag)
				form_field: thisField,
				form_field_info: fieldInfo
			};

			thisEvent.form_flow = nebula.updateFormFlow(thisEvent.form_id, thisEvent.form_field, thisEvent.form_field_info);

			//Form starts
			if ( typeof formStarted[formID] === 'undefined' || !formStarted[formID] ){
				thisEvent.event_name = 'form_start';
				thisEvent.event_label = 'Began filling out form ID: ' + thisEvent.form_id + ' (' + thisEvent.form_field + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.form_id + ') Started'}, false);
				nebula.crm('event', 'Contact Form (' + thisEvent.form_id + ') Started (' + thisEvent.form_field + ')');
				formStarted[formID] = true;
			}

			//Track each individual field focuses
			if ( !jQuery(this).is('button') ){
				thisEvent.event_name = 'form_field_focus';
				thisEvent.event_action = 'Individual Field Focused';
				thisEvent.event_label = 'Focus into ' + thisEvent.form_field + ' (Form ID: ' + thisEvent.form_id + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_started'}));
			}
		}

		nebula.timer(formID, 'start', thisField);

		//Individual form field timings
		if ( nebula.timings && typeof nebula.timings[formID] !== 'undefined' && typeof nebula.timings[formID].lap[nebula.timings[formID].laps-1] !== 'undefined' ){
			let labelText = '';
			if ( jQuery(this).parent('.label') ){
				labelText = jQuery(this).parent('.label').text();
			} else if ( jQuery('label[for="' + jQuery(this).attr('id') + '"]').length ){
				labelText = jQuery('label[for="' + jQuery(this).attr('id') + '"]').text();
			} else if ( jQuery(this).attr('placeholder').length ){
				labelText = ' "' + jQuery(this).attr('placeholder') + '"';
			}

			gtag('event', 'timing_complete', {
				name: nebula.timings[formID].lap[nebula.timings[formID].laps-1].name + labelText + ' (Form ID: ' + formID + ')',
				value: Math.round(nebula.timings[formID].lap[nebula.timings[formID].laps-1].duration),
				event_category: 'CF7 Form',
				event_label: 'Amount of time on this input field (until next focus or submit).',
			});
		}
	});

	//CF7 Submit "Attempts" (submissions of any CF7 form on the HTML-side: before REST API)
	//This metric should always match the "Submit (Processing)" metric or else something is wrong!
	nebula.dom.document.on('wpcf7beforesubmit', function(e){
		try {
			jQuery(e.target).find('button#submit').addClass('active');

			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_attempt',
				event_category: 'CF7 Form',
				event_action: 'Submit (Attempt)',
				event_label: e.detail.unitTag,
				form_id: e.detail.contactFormId, //CF7 Form ID
				post_id: e.detail.containerPostId, //Post/Page ID
				unit_tag: e.detail.unitTag, //CF7 Unit Tag
			};

			//If timing data exists
			if ( nebula.timings && typeof nebula.timings[e.detail.unitTag] !== 'undefined' ){
				thisEvent.form_time = nebula.timer(e.detail.unitTag, 'lap', 'wpcf7-submit-attempt');
				thisEvent.inputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			thisEvent.form_timing = nebula.millisecondsToString(thisEvent.form_time) + 'ms (' + thisEvent.inputs + ')';
			thisEvent.event_label = 'HTML submission attempt for form ID: ' + thisEvent.unit_tag;

			gtag('set', 'user_properties', {
				contact_method: 'CF7 Form (Attempt)'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent)); //This event is required for the notable form metric!
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_submit_attempt'}));
			//nebula.fbq('track', 'Lead', {content_name: 'Form Submit (Attempt)'});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
		} catch {
			gtag('event', 'exception', {
				message: '(JS) CF7 Catch (cf7 HTML form submit): ' + error,
				fatal: false
			});
			nebula.usage('CF7 (HTML) Catch: ' + error);
		}
	});

	//CF7 Submit "Processing" (CF7 AJAX response after any submit attempt). This triggers after the other submit triggers.
	//This metric should always match the "Submit (Attempt)" metric or else something is wrong!
	nebula.dom.document.on('wpcf7submit', function(e){
		try {
			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_processing',
				event_category: 'CF7 Form',
				event_action: 'Submit (Processing)',
				event_label: e.detail.unitTag,
				form_id: e.detail.contactFormId, //CF7 Form ID
				post_id: e.detail.containerPostId, //Post/Page ID
				unit_tag: e.detail.unitTag, //CF7 Unit Tag
			};

			thisEvent.event_label = 'Submission processing for form ID: ' + thisEvent.unitTag;
			thisEvent.form_timing = nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')'; //This is a backup for the HTML form listener

			nebula.crmForm(thisEvent.unitTag); //nebula.crmForm() here because it triggers after all others. No nebula.crm() here so it doesn't overwrite the other (more valuable) data.

			gtag('set', 'user_properties', {
				contact_method: 'CF7 Form (Processing)'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent)); //This event is required for the notable form metric!
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_submit_processing'}));
			//nebula.fbq('track', 'Lead', {content_name: 'Form Submit (Processing)'});
			nebula.clarity('track', 'Lead', {content_name: 'Form Submit (Processing)'});

			jQuery('#' + e.detail.unitTag).find('button#submit').removeClass('active');
			jQuery('.invalid-feedback').addClass('hidden'); //Reset all of the "live" feedback to let CF7 handle its feedback
			jQuery('#cf7-privacy-acceptance').trigger('change'); //Until CF7 has a native invalid indicator for the privacy acceptance checkbox, force the Nebula validator here
		} catch(error){
			gtag('event', 'exception', {
				message: '(JS) CF7 Catch (wpcf7submit): ' + error,
				fatal: false
			});
			nebula.usage('CF7 Catch: ' + error);
		}
	});

	//CF7 Invalid (CF7 AJAX response after invalid form)
	nebula.dom.document.on('wpcf7invalid', function(e){
		try {
			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_invalid',
				event_category: 'CF7 Form',
				event_action: 'Submit (CF7 Invalid)',
				event_label: e.detail.unitTag,
				description: '(JS) Invalid form submission for form ID ' + e.detail.unitTag,
				form_id: e.detail.contactFormId, //CF7 Form ID
				post_id: e.detail.containerPostId, //Post/Page ID
				unit_tag: e.detail.unitTag, //CF7 Unit Tag
				fatal: false
			};

			thisEvent.form_flow = nebula.updateFormFlow(thisEvent.unitTag, '[Invalid]');

			//If timing data exists
			if ( nebula.timings && typeof nebula.timings[e.detail.unitTag] !== 'undefined' ){
				thisEvent.formTime = nebula.timer(e.detail.unitTag, 'lap', 'wpcf7-submit-invalid');
				thisEvent.inputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			thisEvent.event_label = 'Form validation errors occurred on form ID: ' + thisEvent.unitTag;
			thisEvent.form_timing = nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')';

			//Apply Bootstrap validation classes to invalid fields
			jQuery('.wpcf7-not-valid').each(function(){
				jQuery(this).addClass('is-invalid');
			});

			gtag('set', 'user_properties', {
				contact_method: 'CF7 Form (Invalid)'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			//gtag('event', 'exception', nebula.gaEventObject(thisEvent)); //This breaks because thisEvent gets modified by gaEventObject()
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_invalid'}));
			nebula.scrollTo(jQuery('.wpcf7-not-valid').first(), 35); //Scroll to the first invalid input
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Invalid'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Invalid');
		} catch(error){
			gtag('event', 'exception', {
				message: '(JS) CF7 Catch (wpcf7invalid): ' + error,
				fatal: false
			});
			nebula.usage('CF7 Catch: ' + error);
		}
	});

	//General HTML5 validation errors
	jQuery('.wpcf7-form input').on('invalid', function(e){ //Would it be more useful to capture all inputs (rather than just CF7)? How would we categorize this in GA?
		nebula.debounce(function(){
			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_invalid',
				event_category: 'CF7 Form',
				event_action: 'Submit (HTML5 Invalid)',
				event_label: 'General HTML5 validation error',
				description: 'General HTML5 validation error',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_invalid'}));
			nebula.crm('identify', {'form_contacted': 'CF7 HTML5 Validation Error'});
		}, 50, 'invalid form');
	});

	//CF7 Spam (CF7 AJAX response after spam detection)
	nebula.dom.document.on('wpcf7spam', function(e){
		try {
			let formInputs = 'Unknown';
			if ( nebula.timings[e.detail.unitTag] && nebula.timings[e.detail.unitTag].laps ){
				formInputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_spam',
				event_category: 'CF7 Form',
				event_action: 'Submit (Spam)',
				event_label: e.detail.unitTag,
				form_id: e.detail.contactFormId, //CF7 Form ID
				post_id: e.detail.containerPostId, //Post/Page ID
				unit_tag: e.detail.unitTag, //CF7 Unit Tag
				description: '(JS) Spam form submission for form ID ' + e.detail.unitTag,
				form_time: nebula.timer(e.detail.unitTag, 'end'),
				form_inputs: formInputs,
				fatal: true //Fatal because the user was unable to submit
			};

			thisEvent.form_flow = nebula.updateFormFlow(thisEvent.unitTag, '[Spam]');
			thisEvent.event_label = 'Form submission failed spam tests on form ID: ' + thisEvent.unitTag;
			thisEvent.form_timing = nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')';

			gtag('set', 'user_properties', {
				contact_method: 'CF7 Form (Spam)'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			gtag('event', 'exception', nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_spam'}));
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Submit Spam'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Spam');
		} catch(error){
			gtag('event', 'exception', {
				message: '(JS) CF7 Catch (wpcf7spam): ' + error,
				fatal: false
			});
			nebula.usage('CF7 Catch: ' + error);
		}
	});

	//CF7 Mail Send Failure (CF7 AJAX response after mail failure)
	nebula.dom.document.on('wpcf7mailfailed', function(e){
		try {
			let formInputs = 'Unknown';
			if ( nebula.timings[e.detail.unitTag] && nebula.timings[e.detail.unitTag].laps ){
				formInputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_failed',
				event_category: 'CF7 Form',
				event_action: 'Submit (Mail Failed)',
				event_label: e.detail.unitTag,
				form_id: e.detail.contactFormId, //CF7 Form ID
				post_id: e.detail.containerPostId, //Post/Page ID
				unit_tag: e.detail.unitTag, //CF7 Unit Tag
				description: '(JS) Mail failed to send for form ID ' + e.detail.unitTag,
				form_time: nebula.timer(e.detail.unitTag, 'end'),
				form_inputs: formInputs,
				fatal: true //Fatal because the user was unable to submit
			};

			thisEvent.form_flow = nebula.updateFormFlow(thisEvent.unitTag, '[Failed]');
			thisEvent.event_label = 'Form submission email send failed for form ID: ' + thisEvent.unitTag;
			thisEvent.form_timing = nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')';

			gtag('set', 'user_properties', {
				contact_method: 'CF7 Form (Failed)'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			gtag('event', 'exception', nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_failed'}));
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Submit Failed'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Failed');
		} catch(error){
			gtag('event', 'exception', {
				message: '(JS) CF7 Catch (wpcf7mailfailed): ' + error,
				fatal: false
			});
			nebula.usage('CF7 Catch: ' + error);
		}
	});

	//CF7 Mail Sent Success (CF7 AJAX response after submit success)
	nebula.dom.document.on('wpcf7mailsent', function(e){
		try {
			formStarted[e.detail.unitTag] = false; //Reset abandonment tracker for this form.

			let formInputs = 'Unknown';
			if ( nebula.timings[e.detail.unitTag] && nebula.timings[e.detail.unitTag].laps ){
				formInputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			let thisEvent = {
				event: e,
				event_name: 'cf7_form_submit_success',
				event_category: 'CF7 Form',
				event_action: 'Submit (Success)',
				event_label: e.detail.unitTag,
				form_id: e.detail.contactFormId, //CF7 Form ID
				post_id: e.detail.containerPostId, //Post/Page ID
				unit_tag: e.detail.unitTag, //CF7 Unit Tag ("f" is CF7 form ID, "p" is WP post ID, and "o" is the count if there are multiple per page)
				form_time: nebula.timer(e.detail.unitTag, 'end'),
				form_inputs: formInputs,
			};

			thisEvent.form_flow = nebula.updateFormFlow(thisEvent.unitTag, '[Success]');
			thisEvent.form_timing = nebula.millisecondsToString(thisEvent.form_time) + 'ms (' + thisEvent.form_inputs + ')';
			thisEvent.event_label = 'Form ID: ' + thisEvent.unitTag;

			gtag('set', 'user_properties', {
				contact_method: 'CF7 Form'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent)); //Note that this event is often received by GA before attempt/processing events
			gtag('event', 'timing_complete', {
				name: 'Form Completion (ID: ' + thisEvent.unitTag + ')',
				value: Math.round(thisEvent.formTime),
				event_category: thisEvent.event_category,
				event_label: 'Initial form focus until valid submit',
			});
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_form_submit_success'}));
			nebula.fbq('track', 'Lead', {content_name: 'Form Submit (Success)'});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Submit Success'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Submit Success');

			//Clear localstorage on submit success on non-persistent forms
			if ( !jQuery('#' + e.detail.unitTag).hasClass('nebula-persistent') && !jQuery('#' + e.detail.unitTag).parents('.nebula-persistent').length ){
				jQuery('#' + e.detail.unitTag + ' .wpcf7-textarea, #' + e.detail.unitTag + ' .wpcf7-text').each(function(){
					jQuery(this).trigger('keyup'); //Clear validation
					localStorage.removeItem('cf7_' + jQuery(this).attr('name'));
				});
			}

			jQuery('#' + e.detail.unitTag).find('button#submit').removeClass('active');
			jQuery('#' + e.detail.unitTag).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid'); //Clear all validation classes
		} catch(error){
			gtag('event', 'exception', {
				message: '(JS) CF7 Catch (wpcf7mailsent): ' + error,
				fatal: false
			});
			nebula.usage('CF7 Catch: ' + error);
		}
	});
};

nebula.updateFormFlow = function(formID, field, info = ''){
	if ( typeof nebula.formFlow === 'undefined' ){
		nebula.formFlow = {};
	}

	if ( info !== '' ){
		if ( info.length > 25 ){
			info = info.substring(0, 25) + '...'; //Truncate long info text
		}

		info = ' (' + info + ')';
	}

	if ( nebula.formFlow[formID] ){ //If this form ID already exists
		nebula.formFlow[formID] += ' > ' + field + info; //Append the next field to the string
	} else {
		nebula.formFlow[formID] = formID + ': ' + field + info; //Otherwise start a new form flow string beginning with the form ID
	}

	//Set the user property. @todo "Nebula" 0: When GA4 allows session-scoped custom dimensions, update this to session scope!
	gtag('set', 'user_properties', {
		form_flow: nebula.formFlow[formID]
	});

	return nebula.formFlow[formID];
};

//Enable localstorage on CF7 text inputs and textareas
nebula.cf7LocalStorage = function(){
	if ( !jQuery('.wpcf7-form').length ){
		return false;
	}

	jQuery('.wpcf7-textarea, .wpcf7-text').each(function(){
		let thisLocalStorageVal = localStorage.getItem('cf7_' + jQuery(this).attr('name'));

		//Fill textareas with localstorage data on load
		if ( !jQuery(this).hasClass('do-not-store') && !jQuery(this).hasClass('.wpcf7-captchar') && thisLocalStorageVal && thisLocalStorageVal !== 'undefined' && thisLocalStorageVal !== '' ){
			if ( jQuery(this).val() === '' ){ //Don't overwrite a field that already has text in it!
				jQuery(this).val(thisLocalStorageVal).trigger('keyup');
			}
			jQuery(this).blur();
		} else {
			localStorage.removeItem('cf7_' + jQuery(this).attr('name')); //Remove localstorage if it is undefined or inelligible
		}

		//Update localstorage data
		jQuery(this).on('keyup blur', function(){
			if ( !jQuery(this).hasClass('do-not-store') && !jQuery(this).hasClass('.wpcf7-captchar') ){
				localStorage.setItem('cf7_' + jQuery(this).attr('name'), jQuery(this).val());
			}
		});
	});

	//Update matching form fields on other windows/tabs
	nebula.dom.window.on('storage', function(e){
		jQuery('.wpcf7-textarea, .wpcf7-text').each(function(){
			if ( !jQuery(this).hasClass('do-not-store') && !jQuery(this).hasClass('.wpcf7-captchar') ){
				jQuery(this).val(localStorage.getItem('cf7_' + jQuery(this).attr('name'))).trigger('keyup');
			}
		});
	});

	//Clear localstorage when AJAX submit fails (but submit still succeeds)
	if ( window.location.hash.includes('wpcf7') ){
		if ( jQuery(escape(window.location.hash) + ' .wpcf7-mail-sent-ok').length ){
			jQuery(escape(window.location.hash) + ' .wpcf7-textarea, ' + escape(window.location.hash) + ' .wpcf7-text').each(function(){
				localStorage.removeItem('cf7_' + jQuery(this).attr('name'));
				jQuery(this).val('').trigger('keyup');
			});
		}
	}
};

//Form live (soft) validator
nebula.liveValidator = function(){
	//CF7 Invalid events
	nebula.dom.document.on('wpcf7invalid', function(e){
		setTimeout(function(){ //This triggers before these classes are added by CF7 so wait for 1ms
			jQuery('#' + e.detail.unitTag).find('.wpcf7-not-valid').each(function(){ //Find invalid fields only within this CF7 form
				nebula.applyValidationClasses(jQuery(this), 'invalid', true);
			});
		}, 1);
	});

	//Standard text inputs and select menus
	nebula.dom.document.on('keyup change blur', '.nebula-validate-text, .nebula-validate-textarea, .nebula-validate-select', function(e){
		if ( e.type === 'focusout' ){
			jQuery(this).val(jQuery(this).val().trim()); //Trim leading/trailing whitespace on blur
		}

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( jQuery(this).val().trim().length ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//RegEx input
	nebula.dom.document.on('keyup change blur', '.nebula-validate-regex', function(e){
		if ( e.type === 'focusout' ){
			jQuery(this).val(jQuery(this).val().trim()); //Trim leading/trailing whitespace on blur
		}

		let pattern = new RegExp(jQuery(this).attr('data-valid-regex'), 'i');

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( pattern.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//URL inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-url', function(e){
		if ( e.type === 'focusout' ){
			jQuery(this).val(jQuery(this).val().trim()); //Trim leading/trailing whitespace on blur
		}

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( nebula.regex.url.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Email address inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-email', function(e){
		if ( e.type === 'focusout' ){
			jQuery(this).val(jQuery(this).val().trim()); //Trim leading/trailing whitespace on blur
		}

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( nebula.regex.email.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Phone number inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-phone', function(e){
		if ( e.type === 'focusout' ){
			jQuery(this).val(jQuery(this).val().trim()); //Trim leading/trailing whitespace on blur
		}

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( nebula.regex.phone.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Date inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-date', function(e){
		if ( e.type === 'focusout' ){
			jQuery(this).val(jQuery(this).val().trim()); //Trim leading/trailing whitespace on blur
		}

		//Used to use day.js to validate the date and check that it was between 1800-2999. Now just check that it is not empty.

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'valid', ( e.type !== 'keyup' )); //This indicates it is valid as long as it isn't empty
		}
	});

	//Checkbox and Radio
	//Note: The CF7 "Privacy Acceptance" checkbox does not accept custom classes, so we must use its ID directly here
	nebula.dom.document.on('change blur', '#cf7-privacy-acceptance, .nebula-validate-checkbox, .nebula-validate-radio', function(e){
		if ( jQuery(this).closest('.form-group, .form-check').find('input:checked').length ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', true);
		}
	});

	//Highlight empty required fields when focusing/hovering on submit button
	nebula.dom.document.on('mouseover focus', 'form [type="submit"], form #submit', function(){ //Must be deferred because Nebula replaces CF7 submit inputs with buttons
		let invalidCount = 0;

		//This is a non-essential, cosmetic helper, so escape if any errors occur
		try {
			jQuery(this).closest('form').find('[required], .wpcf7-validates-as-required').each(function(){
				//Look for checked checkboxes or radio buttons
				if ( jQuery(this).find('input:checked').length ){
					return; //Continue
				}

				//Look for empty fields
				if ( jQuery(this).val().trim().length === 0 ){ //Sometimes jQuery(this) is null and errors on .trim(), so wrapped the whole thing in a try
					jQuery(this).addClass('nebula-empty-required');
					invalidCount++;
				}
			});
		} catch {
			//Ignore
		}

		if ( invalidCount > 0 ){
			let invalidCountText = ( invalidCount === 1 )? ' invalid field remains' : ' invalid fields remain';
			jQuery('form [type="submit"], form #submit').attr('title', invalidCount + invalidCountText);
		}
	});

	nebula.dom.document.on('mouseout blur', 'form [type="submit"], form #submit', function(){ //Must be deferred because Nebula replaces CF7 submit inputs with buttons
		jQuery(this).closest('form').find('.nebula-empty-required').removeClass('nebula-empty-required');
		jQuery('form [type="submit"], form #submit').removeAttr('title');
	});
};

//Apply Bootstrap and CF7 appropriate validation classes to appropriate elements
nebula.applyValidationClasses = function($element, validation, showFeedback){
	if ( typeof $element === 'string' ){
		$element = jQuery($element);
	} else if ( typeof $element !== 'object' ){
		return false;
	}

	if ( validation === 'success' || validation === 'valid' ){
		$element.removeClass('wpcf7-not-valid is-invalid').addClass('is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	} else if ( validation === 'danger' || validation === 'error' || validation === 'invalid' ){
		$element.removeClass('wpcf7-not-valid is-valid').addClass('is-invalid');
	} else if ( validation === 'reset' || validation === 'remove' ){
		$element.removeClass('wpcf7-not-valid is-invalid is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	}

	//Find the invalid feedback element (if it exists)
	let parentElement = $element.parent();
	let feedbackElement = false;
	if ( $element.parent().find('.invalid-feedback').length ){
		parentElement = $element.parent();
		feedbackElement = $element.parent().find('.invalid-feedback');
	} else if ( $element.closest('.form-group, .form-check').find('.invalid-feedback').length ){
		parentElement = $element.closest('.form-group, .form-check');
		feedbackElement = $element.closest('.form-group, .form-check').find('.invalid-feedback');
	} else if ( $element.parents('.nebula-form-group').find('.invalid-feedback').length ){
		parentElement = $element.parents('.nebula-form-group');
		feedbackElement = $element.parents('.nebula-form-group').find('.invalid-feedback');
	}

	if ( feedbackElement ){
		if ( validation === 'feedback' || showFeedback ){
			feedbackElement.removeClass('hidden').show();

			if ( parentElement.find('.wpcf7-not-valid-tip').length ){
				parentElement.find('.wpcf7-not-valid-tip').addClass('hidden'); //Hide the default CF7 message if we have a more helpful one for this field
			}
		} else {
			feedbackElement.addClass('hidden').hide();
		}
	}
};

//Nebula Feedback System
nebula.initFeedbackSystem = function(){
	//User clicks "Yes"
	nebula.dom.document.on('click', '#nebula-feedback-yes', function(e){
		let thisEvent = {
			event: e,
			event_name: 'user_feedback',
			event_category: 'User Feedback',
			event_action: 'Helpful',
			event_label: 'The user indicated that this page was helpful!',
			response: 'Helpful',
		};

		nebula.dom.document.trigger('nebula_event', thisEvent);
		gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
		window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_user_feedback_helpful'}));

		//Thank the user
		jQuery('#nebula-feedback-question').slideUp();
		jQuery('#nebula-feedback-thanks').slideDown();

		return false;
	});

	//User clicks "No"
	nebula.dom.document.on('click', '#nebula-feedback-no', function(e){
		let thisEvent = {
			event: e,
			event_name: 'user_feedback',
			event_category: 'User Feedback',
			event_action: 'Not Helpful',
			event_label: 'The user indicated that this page was not helpful.',
			response: 'Not Helpful',
		};

		nebula.dom.document.trigger('nebula_event', thisEvent);
		gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
		window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_user_feedback_not_helpful'}));

		if ( jQuery('.has-feedback-form').length ){ //If a CF7 form exists for additional feedback
			jQuery('#nebula-feedback-question').addClass('not-helpful-active');
			jQuery('#nebula-feedback-yes').animate({
				width: 0,
				paddingLeft: 0,
				paddingRight: 0,
				marginRight: -15 //This is to remove the extra grid gap
			}, 250);

			//Show the CF7 form
			jQuery('#nebula-feedback-form-container').slideDown();

			//Listen for submission of this feedback message form
			nebula.dom.document.on('wpcf7mailsent', function(e){
				if ( e.detail.contactFormId === parseInt(jQuery('#nebula-feedback-form-container').attr('data-form-id')) ){ //We only care about the feedback form
					jQuery('#nebula-feedback-form-container').slideUp();
					jQuery('#nebula-feedback-question').slideUp();
					jQuery('#nebula-feedback-thanks').slideDown();

					let feedbackMessage = jQuery('#nebula-feedback-system textarea').val();
					if ( !feedbackMessage.includes('@') && (/\d/).test(feedbackMessage) ){ //If the message does NOT include PII such as "@" or any number at all
						if ( feedbackMessage.length > 95 ){ //If the message string is longer than 95 characters
							feedbackMessage = feedbackMessage.slice(0, 95) + '...'; //Limit to 95 characters plus an ellipsis
						}

						thisEvent = {
							event: e,
							event_name: 'user_feedback_message',
							event_category: 'User Feedback',
							event_action: 'Message',
							event_label: feedbackMessage,
							response: 'Message',
							message: feedbackMessage,
						};

						nebula.dom.document.trigger('nebula_event', thisEvent);
						gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
						window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_user_feedback_message'}));
					}
				}
			});
		} else {
			//Thank the user
			jQuery('#nebula-feedback-question').slideUp();
			jQuery('#nebula-feedback-thanks').slideDown();
		}

		return false;
	});
};