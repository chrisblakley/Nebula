jQuery.noConflict();

//Document Ready
jQuery(function(){
	userHeadshotFields();
	initializationStuff();
	developerMetaboxes();
	wysiwygMods();

	if ( jQuery('#edit-slug-box').length ){
		nebulaUniqueSlugChecker();
	}

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

		//If there are no active tabs on load (like if wrong ?tab= parameter was used)
		if ( !jQuery('#options-navigation li a.active').length ){
			jQuery('#options-navigation').find('li:first-child a').addClass('active');
			jQuery('#nebula-options-section').find('.tab-pane:first-child').addClass('active');
		}

		//Scroll to the top when changing tabs
		jQuery('a.nav-link').on('shown.bs.tab', function(){
			jQuery('html, body').animate({
				scrollTop: jQuery('#nebula-options-section').offset().top-1000
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
			//Direct Link icons
			var thisTab = jQuery(this).closest('.tab-pane').attr('id');
			var thisOption = jQuery(this).closest('.form-group, .multi-form-group').find('.form-control').attr('id');
			//jQuery(this).append('<a class="direct-link" href="themes.php?page=nebula_options&tab=' + thisTab + '&option=' + thisOption + '" title="Link to this option" tabindex="-1"><i class="fas fa-fw fa-link"></i></a>'); //@todo "Nebula" 0: this should confirm leaving on click if there are changes to the form!

			//More Help expander icons
			if ( jQuery(this).parent().find('.more-help').length ){
				jQuery(this).append('<a class="toggle-more-help" href="#" title="Show more information" tabindex="-1"><i class="fas fa-fw fa-question-circle"></i></a>');
			}
		});

		jQuery(document).on('click', '.toggle-more-help', function(){
			jQuery(this).closest('.form-group, .multi-form-group').find('.more-help').slideToggle();
			return false;
		});

		//Remove Sass render trigger query
		if ( get('sass') && !get('persistent') && window.history.replaceState ){ //IE10+
			window.history.replaceState({}, document.title, removeQueryParameter('sass', window.location.href));
		}
	}
}); //End Document Ready

jQuery(window).on('load', function(){
	performanceMetrics();
});

jQuery(window).resize(function() {
	//If Nebula Options Page
	if ( window.location.href.indexOf('themes.php?page=nebula_options') > 0 ){
		checkWindowHeightForStickyNav();
	}
});

//Developer Metaboxe functions
function developerMetaboxes(){
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
					jQuery("div.search_results").html(errorThrown).addClass('done');
				},
				timeout: 60000
			});
		} else {
			jQuery('input.findterm').val('').attr('placeholder', 'Minimum 3 characters.');
		}
		e.preventDefault();
		return false;
	});

	jQuery(document).on('click', '.linenumber', function(){
		jQuery(this).parents('.linewrap').find('.precon').slideToggle();
		return false;
	});

	//Dynamic height for TODO results
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

//Modifications to TinyMCE
function wysiwygMods(){
	//Detect external links in the TinyMCE link modal (to check the "new window" box).
	linkTargetUsered = 0;
	jQuery(document).on('click keydown', '#wp-link-target', function(){
		linkTargetUsered = 1;
	});
	jQuery(document).on('click', '#wp-link-submit, #wp-link-close, #wp-link-backdrop, #wp-link-cancel a', function(){ //If clicking the submit button, the close "x", the modal background, or the cancel link.
		linkTargetUsered = 0;
	});
	jQuery(document).on('keydown change focus blur paste', '#wp-link-url', function(){ //@TODO "Nebula" 0: This does not trigger when user does NOT type a protocol and pushes tab (WP adds the protocol automatically). Blur is not triggering...
		currentVal = jQuery(this).val();
		if ( linkTargetUsered === 0 ){
			if ( /(h|ht+|https?)(:|:\/+)?$/.test(currentVal) ){
				jQuery('#wp-link-target').prop('checked', false);
			} else if ( (currentVal.indexOf('http') >= 0 || currentVal.indexOf('www') >= 0) && currentVal.indexOf(location.host) < 0 ){ //If (has "http" or www) && NOT our domain
				jQuery('#wp-link-target').prop('checked', true);
			} else if ( /\.(zip|rar|pdf|doc|xls|txt)(x)?$/.test(currentVal) ){ //If the link is a specific filetype
				jQuery('#wp-link-target').prop('checked', true);
			} else if ( currentVal.indexOf('mailto:') >= 0 || currentVal.indexOf('tel:') >= 0 ){ //If the link is a mailto.
				jQuery('#wp-link-target').prop('checked', true);
			} else {
				jQuery('#wp-link-target').prop('checked', false);
			}
		}
	});
	jQuery(document).on('click', '#most-recent-results *, #search-results *', function(){
		if ( linkTargetUsered === 0 ){
			jQuery('#wp-link-target').prop('checked', false);
		}
	});
}

//Initialization alerts
function initializationStuff(){
	//Re-initialize confirm dialog.
	jQuery('.reinitializenebula').on('click', function(){
		if ( !confirm('This will reset all Nebula options and reset the homepage content! Are you sure you want to re-initialize?') ) {
			return false;
		}
	});

	//Initialize confirm dialog.
	jQuery('#run-nebula-initialization').on('click', function(){
		if ( !confirm('This will reset some WordPress settings, all Nebula options, and reset the homepage content! Are you sure you want to initialize?') ) {
			return false;
		} else {
			jQuery('.nebula-activated-description').html('<i class="fas fa-spinner"></i> Running initialization...');

			jQuery.ajax({
				type: "POST",
				url: nebula.site.ajax.url,
				data: {
					action: 'nebula_initialization',
					ajax: true
				},
				success: function(data){
					if ( data.indexOf('successful-nebula-init') !== -1 ){
						jQuery('.nebula-activated-title').html('<i class="fas fa-check" style="color: green;"></i> Nebula has been initialized!');
						jQuery('.nebula-activated-description').html('Settings have been updated. The home page has been updated and has been set as the static front page in <a href="options-reading.php">Settings > Reading</a>.<br /><strong>Next step:</strong> Configure <a href="themes.php?page=nebula_options">Nebula Options</a>');
						return false;
					} else {
						jQuery('#nebula-activate-success').removeClass('updated').addClass('error');
						jQuery('.nebula-activated-title').html('<i class="fas fa-times" style="color: #dd3d36;"></i> AJAX Initialization Error.');
						jQuery('.nebula-activated-description').html('AJAX initialization has failed. Attempting standard initialization. <strong>This will reload the page in 2 seconds...</strong>');
						setTimeout(function(){
							jQuery('.nebula-activated-title').html('<i class="fas fa-spinner" style="color: #dd3d36;"></i> AJAX Initialization Error.');
							window.location = 'themes.php?nebula-initialization=true';
						}, 2000);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					jQuery('#nebula-activate-success').removeClass('updated').addClass('error');
					jQuery('.nebula-activated-title').html('<i class="fas fa-times" style="color: #dd3d36;"></i> AJAX Initialization Error.');
					jQuery('.nebula-activated-description').html('An AJAX error has occurred. Attempting standard initialization. <strong>This will reload the page in 2 seconds...</strong>');
					setTimeout(function(){
						jQuery('.nebula-activated-title').html('<i class="fas fa-spinner" style="color: #dd3d36;"></i> AJAX Initialization Error.');
						window.location = 'themes.php?nebula-initialization=true';
					}, 2000);
				},
				timeout: 60000
			});

			return false;
		}
	});

	//Remove query string once initialized.
	if ( window.location.href.indexOf('?nebula-initialization=true') >= 0 ){
		cleanURL = window.location.href.split('?');
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
function nebulaUniqueSlugChecker(postType){
	if ( !postType ){
		var postType = 'post/page';
	}

	if ( jQuery("#sample-permalink a").text().match(/(-\d+)\/?$/) ){
    	jQuery('#edit-slug-box strong').html('<span title="This likely indicates a duplicate ' + postType + ', but will not prevent saving or publishing." style="cursor: help;">Possible duplicate ' + postType + '! Updated permalink:</span>');
		jQuery('#sample-permalink a').css('color', 'red');
	}
}

//Allow tab character in textareas
(function($){
    function pasteIntoInput(el, text){
        el.focus();
        var val = el.value;
        if ( typeof el.selectionStart === "number" ){
            var selStart = el.selectionStart;
            el.value = val.slice(0, selStart) + text + val.slice(el.selectionEnd);
            el.selectionEnd = el.selectionStart = selStart + text.length;
        } else if ( typeof document.selection !== "undefined" ){
            var textRange = document.selection.createRange();
            textRange.text = text;
            textRange.collapse(false);
            textRange.select();
        }
    }

    function allowTabChar(el){
        jQuery(el).keydown(function(e){
            if ( e.which === 9 ){
                pasteIntoInput(this, "\t");
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
                    if ( nodeName === "textarea" || (nodeName === "input" && this.type === "text") ){
                        allowTabChar(this);
                    }
                }
            })
        }
        return this;
    }
})(jQuery);

//container is the parent container, parent is the individual item, value is usually the input val.
function keywordSearch(container, parent, value, filteredClass){
	if ( !filteredClass ){
		var filteredClass = 'filtereditem';
	}
	jQuery(container).find("*:not(:Contains(" + value + "))").closest(parent).addClass(filteredClass);
	jQuery(container).find("*:Contains(" + value + ")").closest(parent).removeClass(filteredClass);
}

/*==========================
 Utility Functions
 These functions simplify and enhance other JavaScript functions
 ===========================*/

//Get query string parameters
function getQueryStrings(){
	var queries = {};
	var queryString = document.URL.split('?')[1];

	if ( queryString ){
		queryStrings = queryString.split('&');
		for ( var i = 0; i < queryStrings.length; i++ ){
			hash = queryStrings[i].split('=');
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
function get(parameter){
	var queries = getQueryStrings();

	if ( !parameter ){
		return queries;
	}

	return queries[parameter] || false;
}

//Remove a parameter from the query string.
function removeQueryParameter(key, sourceURL){
	var baseURL = sourceURL.split('?')[0];
	var param;
	var params_arr = [];
	var queryString = ( sourceURL.indexOf('?') !== -1 )? sourceURL.split('?')[1] : '';

	if ( queryString !== '' ){
		params_arr = queryString.split('&');

		for ( i = params_arr.length-1; i >= 0; i -= 1 ){
			param = params_arr[i].split('=')[0];
			if ( param === key ){
				params_arr.splice(i, 1);
			}
		}

		newURL = baseURL + '?' + params_arr.join('&');
	}

	//Check if it is empty after parameter removal
	if ( newURL.split('?')[1] === '' ){
		return newURL.split("?")[0]; //Return the URL without a query
	}

	return newURL;
}

//Custom CSS expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: keywordSearch(container, parent, value);
jQuery.expr[":"].Contains=function(e,n,t){return(e.textContent||e.innerText||"").toUpperCase().indexOf(t[3].toUpperCase())>=0};


//Nebula Options Functions

//Make sure the sticky nav is shorter than the viewport height.
function checkWindowHeightForStickyNav(){
	if ( window.innerHeight > jQuery('#stickynav').outerHeight() ){
		jQuery('#stickynav').addClass('sticky');
	} else {
		jQuery('#stickynav').removeClass('sticky');
	}
}

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

				if ( dependentsChecked == totalDependents ){
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

				if ( dependentsUnchecked == totalDependents ){
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



//Option filter
jQuery('#nebula-option-filter').on('keydown change focus blur', function(e){
	//Prevent the form from submitting if pressing enter after searching
	if ( e.type == 'keydown' && e.keyCode == 13 ){
		e.preventDefault();
		return false;
	}

	if ( jQuery(this).val().length > 0 ){
		jQuery('#reset-filter').removeClass('hidden');

		jQuery('#options-navigation').addClass('inactive').find('li a.active').removeClass('active');

		jQuery('.tab-pane').addClass('active');

		keywordSearch('#nebula-options-section', '.form-group', jQuery(this).val());

		jQuery('.option-group, .option-sub-group').each(function(){
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
		jQuery('#reset-filter').addClass('hidden');

		jQuery('#options-navigation').removeClass('inactive');

		if ( !jQuery('#options-navigation li a.active').length ){
			jQuery('#options-navigation').find('li:first-child a').addClass('active');
		}

		jQuery('.filtereditem').removeClass('filtereditem');
	}
});

jQuery('#reset-filter a').on('click', function(){
	jQuery('#nebula-option-filter').val('').trigger('keydown');
	return false;
});

jQuery('#preset-filters a').on('click', function(){
	jQuery('#nebula-option-filter').val(jQuery(this).attr('filter-text')).trigger('keydown');
	return false;
});









//Functions pulled from nebula.js for various admin usages (mostly Nebula Options)

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
		return rtn.split("?")[0]; //Return the URL without a query
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
		} else if ( strtotime(jQuery(this).val()) && strtotime(jQuery(this).val()) > -2208988800 ){ //Check for textual dates (after 1900) //@TODO "Nebula" 0: The JS version of strtotime() isn't the most accurate function...
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
	if ( window.performance && window.performance.timing ){ //Safari 11+
		setTimeout(function(){
			var responseEnd = Math.round(performance.timing.responseEnd-performance.timing.navigationStart); //Navigation start until server response finishes
			var domReady = Math.round(performance.timing.domContentLoadedEventStart-performance.timing.navigationStart); //Navigation start until DOM ready
			var windowLoaded = Math.round(performance.timing.loadEventStart-performance.timing.navigationStart); //Navigation start until window load

			clientTimings = {
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
			}

			console.groupCollapsed('Performance');
			console.table(jQuery.extend(nebula.site.timings, clientTimings));
			console.groupEnd();
		}, 0);
	}
}