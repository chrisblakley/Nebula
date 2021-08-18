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
						category: 'CF7 Form',
						action: 'Impression', //GA4 Name: "form_impression"?
						formID: jQuery(entry.target).closest('.wpcf7').attr('id') || jQuery(entry.target).attr('id'),
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.formID, {'nonInteraction': true});
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-cf7-impression'}));

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
		let formID = jQuery(this).closest('div.wpcf7').attr('id');

		let thisField = e.target.name || jQuery(this).closest('.form-group').find('label').text() || e.target.id || 'Unknown';
		let fieldInfo = '';
		if ( jQuery(this).attr('type') === 'checkbox' || jQuery(this).attr('type') === 'radio' ){
			fieldInfo = jQuery(this).attr('value');
		}

		if ( !jQuery(this).hasClass('.ignore-form') && !jQuery(this).find('.ignore-form').length && !jQuery(this).parents('.ignore-form').length ){
			let thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Started Form (Focus)', //GA4 Name: "form_start"?
				formID: formID, //Actual ID (not Unit Tag)
				field: thisField,
				fieldInfo: fieldInfo
			};

			//Form starts
			if ( typeof formStarted[formID] === 'undefined' || !formStarted[formID] ){
				thisEvent.label = 'Began filling out form ID: ' + thisEvent.formID + ' (' + thisEvent.field + ')';

				ga('set', nebula.analytics.metrics.formStarts, 1);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.formID + ') Started'}, false);
				nebula.crm('event', 'Contact Form (' + thisEvent.formID + ') Started (' + thisEvent.field + ')');
				formStarted[formID] = true;
			}

			nebula.updateFormFlow(thisEvent.formID, thisEvent.field, thisEvent.fieldInfo);

			//Track each individual field focuses
			if ( !jQuery(this).is('button') ){
				thisEvent.action = 'Individual Field Focused';
				thisEvent.label = 'Focus into ' + thisEvent.field + ' (Form ID: ' + thisEvent.formID + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-started'}));
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
			ga('send', 'timing', 'CF7 Form', nebula.timings[formID].lap[nebula.timings[formID].laps-1].name + labelText + ' (Form ID: ' + formID + ')', Math.round(nebula.timings[formID].lap[nebula.timings[formID].laps-1].duration), 'Amount of time on this input field (until next focus or submit).');
		}
	});

	//CF7 Submit "Attempts" (submissions of any CF7 form on the HTML-side: before REST API)
	//This metric should always match the "Submit (Processing)" metric or else something is wrong!
	nebula.dom.document.on('wpcf7beforesubmit', function(e){
		try {
			jQuery(e.target).find('button#submit').addClass('active');

			let thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Submit (Attempt)', //GA4 Name: "form_attempt"?
				formID: e.detail.contactFormId, //CF7 Form ID
				postID: e.detail.containerPostId, //Post/Page ID
				unitTag: e.detail.unitTag, //CF7 Unit Tag
			};

			//If timing data exists
			if ( nebula.timings && typeof nebula.timings[e.detail.unitTag] !== 'undefined' ){
				thisEvent.formTime = nebula.timer(e.detail.unitTag, 'lap', 'wpcf7-submit-attempt');
				thisEvent.inputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			thisEvent.label = 'HTML submission attempt for form ID: ' + thisEvent.unitTag;

			ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Attempt)');
			ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label); //This event is required for the notable form metric!
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-submit-attempt'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Attempt)'});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
		} catch {
			ga('send', 'exception', {'exDescription': '(JS) CF7 Catch (cf7 HTML form submit): ' + error, 'exFatal': false});
			nebula.usage('CF7 (HTML) Catch: ' + error);
		}
	});

	//CF7 Submit "Processing" (CF7 AJAX response after any submit attempt). This triggers after the other submit triggers.
	//This metric should always match the "Submit (Attempt)" metric or else something is wrong!
	nebula.dom.document.on('wpcf7submit', function(e){
		try {
			let thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Submit (Processing)', //GA4 Name: "form_processing"?
				formID: e.detail.contactFormId, //CF7 Form ID
				postID: e.detail.containerPostId, //Post/Page ID
				unitTag: e.detail.unitTag, //CF7 Unit Tag
			};

			thisEvent.label = 'Submission processing for form ID: ' + thisEvent.unitTag;

			nebula.crmForm(thisEvent.unitTag); //nebula.crmForm() here because it triggers after all others. No nebula.crm() here so it doesn't overwrite the other (more valuable) data.

			ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Processing)');
			ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')'); //This is a backup for the HTML form listener
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label); //This event is required for the notable form metric!
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-submit-processing'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Processing)'});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}

			jQuery('#' + e.detail.unitTag).find('button#submit').removeClass('active');
			jQuery('.invalid-feedback').addClass('hidden'); //Reset all of the "live" feedback to let CF7 handle its feedback
			jQuery('#cf7-privacy-acceptance').trigger('change'); //Until CF7 has a native invalid indicator for the privacy acceptance checkbox, force the Nebula validator here
		} catch(error){
			ga('send', 'exception', {'exDescription': '(JS) CF7 Catch (wpcf7submit): ' + error, 'exFatal': false});
			nebula.usage('CF7 Catch: ' + error);
		}
	});

	//CF7 Invalid (CF7 AJAX response after invalid form)
	nebula.dom.document.on('wpcf7invalid', function(e){
		try {
			let thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Submit (CF7 Invalid)', //GA4 Name: "form_invalid"?
				formID: e.detail.contactFormId, //CF7 Form ID
				postID: e.detail.containerPostId, //Post/Page ID
				unitTag: e.detail.unitTag, //CF7 Unit Tag
			};

			//If timing data exists
			if ( nebula.timings && typeof nebula.timings[e.detail.unitTag] !== 'undefined' ){
				thisEvent.formTime = nebula.timer(e.detail.unitTag, 'lap', 'wpcf7-submit-invalid');
				thisEvent.inputs = nebula.timings[e.detail.unitTag].laps + ' inputs';
			}

			thisEvent.label = 'Form validation errors occurred on form ID: ' + thisEvent.unitTag;

			//Apply Bootstrap validation classes to invalid fields
			jQuery('.wpcf7-not-valid').each(function(){
				jQuery(this).addClass('is-invalid');
			});

			nebula.updateFormFlow(thisEvent.unitTag, '[Invalid]');
			ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Invalid)');
			ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-invalid'}));
			ga('send', 'exception', {'exDescription': '(JS) Invalid form submission for form ID ' + thisEvent.unitTag, 'exFatal': false});
			nebula.scrollTo(jQuery('.wpcf7-not-valid').first(), 35); //Scroll to the first invalid input
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Invalid'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Invalid');
		} catch(error){
			ga('send', 'exception', {'exDescription': '(JS) CF7 Catch (wpcf7invalid): ' + error, 'exFatal': false});
			nebula.usage('CF7 Catch: ' + error);
		}
	});

	//General HTML5 validation errors
	jQuery('.wpcf7-form input').on('invalid', function(e){ //Would it be more useful to capture all inputs (rather than just CF7)? How would we categorize this in GA?
		nebula.debounce(function(){
			let thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Submit (HTML5 Invalid)', //GA4 Name: "form_invalid"?
				label: 'General HTML5 validation error',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-invalid'}));
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
				category: 'CF7 Form',
				action: 'Submit (Spam)', //GA4 Name: "form_spam"?
				formID: e.detail.contactFormId, //CF7 Form ID
				postID: e.detail.containerPostId, //Post/Page ID
				unitTag: e.detail.unitTag, //CF7 Unit Tag
				formTime: nebula.timer(e.detail.unitTag, 'end'),
				inputs: formInputs
			};

			thisEvent.label = 'Form submission failed spam tests on form ID: ' + thisEvent.unitTag;

			nebula.updateFormFlow(thisEvent.unitTag, '[Spam]');
			ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Spam)');
			ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-spam'}));
			ga('send', 'exception', {'exDescription': '(JS) Spam form submission for form ID ' + thisEvent.unitTag, 'exFatal': false});
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Submit Spam'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Spam');
		} catch(error){
			ga('send', 'exception', {'exDescription': '(JS) CF7 Catch (wpcf7spam): ' + error, 'exFatal': false});
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
				category: 'CF7 Form',
				action: 'Submit (Mail Failed)', //GA4 Name: "form_failed"?
				formID: e.detail.contactFormId, //CF7 Form ID
				postID: e.detail.containerPostId, //Post/Page ID
				unitTag: e.detail.unitTag, //CF7 Unit Tag
				formTime: nebula.timer(e.detail.unitTag, 'end'),
				inputs: formInputs
			};

			thisEvent.label = 'Form submission email send failed for form ID: ' + thisEvent.unitTag;

			nebula.updateFormFlow(thisEvent.unitTag, '[Failed]');
			ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Failed)');
			ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-failed'}));
			ga('send', 'exception', {'exDescription': '(JS) Mail failed to send for form ID ' + thisEvent.unitTag, 'exFatal': true});
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Submit Failed'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Failed');
		} catch(error){
			ga('send', 'exception', {'exDescription': '(JS) CF7 Catch (wpcf7mailfailed): ' + error, 'exFatal': false});
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

			//These event may want to correspond to the GA4 event name "generate_lead" and use "value" and "currency" as parameters: https://support.google.com/analytics/answer/9267735 (or consider multiple events?)

			let thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Submit (Success)', //GA4 Name: "form_submit" (and also somehow "generate_lead"?)
				formID: e.detail.contactFormId, //CF7 Form ID
				postID: e.detail.containerPostId, //Post/Page ID
				unitTag: e.detail.unitTag, //CF7 Unit Tag ("f" is CF7 form ID, "p" is WP post ID, and "o" is the count if there are multiple per page)
				formTime: nebula.timer(e.detail.unitTag, 'end'),
				inputs: formInputs
			};

			thisEvent.label = 'Form ID: ' + thisEvent.unitTag;

			nebula.updateFormFlow(thisEvent.unitTag, '[Success]');
			if ( !jQuery('#' + e.detail.unitTag).hasClass('.ignore-form') && !jQuery('#' + e.detail.unitTag).find('.ignore-form').length && !jQuery('#' + e.detail.unitTag).parents('.ignore-form').length ){
				ga('set', nebula.analytics.metrics.formSubmissions, 1);
			}
			ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Success)');
			ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
			ga('send', 'timing', thisEvent.category, 'Form Completion (ID: ' + thisEvent.unitTag + ')', Math.round(thisEvent.formTime), 'Initial form focus until valid submit');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-form-submit-success'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Success)'});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.unitTag + ') Submit Success'}, false);
			nebula.crm('event', 'Contact Form (' + thisEvent.unitTag + ') Submit Success');

			//Clear localstorage on submit success on non-persistent forms
			if ( !jQuery('#' + e.detail.unitTag).hasClass('nebula-persistent') && !jQuery('#' + e.detail.unitTag).parents('.nebula-persistent').length ){
				jQuery('#' + e.detail.unitTag + ' .wpcf7-textarea, #' + e.detail.unitTag + ' .wpcf7-text').each(function(){
					jQuery(this).trigger('keyup'); //Clear validation
					localStorage.removeItem('cf7_' + jQuery(this).attr('name'));
				});
			}

			jQuery('#' + e.detail.unitTag).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid'); //Clear all validation classes
		} catch(error){
			ga('send', 'exception', {'exDescription': '(JS) CF7 Catch (wpcf7mailsent): ' + error, 'exFatal': false});
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

	ga('set', nebula.analytics.dimensions.formFlow, nebula.formFlow[formID]); //Update form field history. scope: session
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
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
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
nebula.applyValidationClasses = function(element, validation, showFeedback){
	if ( typeof element === 'string' ){
		element = jQuery(element);
	} else if ( typeof element !== 'object' ){
		return false;
	}

	if ( validation === 'success' || validation === 'valid' ){
		element.removeClass('wpcf7-not-valid is-invalid').addClass('is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	} else if ( validation === 'danger' || validation === 'error' || validation === 'invalid' ){
		element.removeClass('wpcf7-not-valid is-valid').addClass('is-invalid');
	} else if ( validation === 'reset' || validation === 'remove' ){
		element.removeClass('wpcf7-not-valid is-invalid is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	}

	//Find the invalid feedback element (if it exists)
	let parentElement = element.parent();
	let feedbackElement = false;
	if ( element.parent().find('.invalid-feedback').length ){
		parentElement = element.parent();
		feedbackElement = element.parent().find('.invalid-feedback');
	} else if ( element.closest('.form-group, .form-check').find('.invalid-feedback').length ){
		parentElement = element.closest('.form-group, .form-check');
		feedbackElement = element.closest('.form-group, .form-check').find('.invalid-feedback');
	} else if ( element.parents('.nebula-form-group').find('.invalid-feedback').length ){
		parentElement = element.parents('.nebula-form-group');
		feedbackElement = element.parents('.nebula-form-group').find('.invalid-feedback');
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
			category: 'User Feedback',
			action: 'Helpful',
			label: 'The user indicated that this page was helpful!',
		};

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-feedback-system'}));

		//Thank the user
		jQuery('#nebula-feedback-question').slideUp();
		jQuery('#nebula-feedback-thanks').slideDown();

		return false;
	});

	//User clicks "No"
	nebula.dom.document.on('click', '#nebula-feedback-no', function(e){
		let thisEvent = {
			event: e,
			category: 'User Feedback',
			action: 'Not Helpful',
			label: 'The user indicated that this page was not helpful.',
		};

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-feedback-system'}));

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

			//Listen for submission of this form
			nebula.dom.document.on('wpcf7mailsent', function(e){
				if ( e.detail.contactFormId === parseInt(jQuery('#nebula-feedback-form-container').attr('data-form-id')) ){ //We only care about the feedback form
					jQuery('#nebula-feedback-form-container').slideUp();
					jQuery('#nebula-feedback-question').slideUp();
					jQuery('#nebula-feedback-thanks').slideDown();
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