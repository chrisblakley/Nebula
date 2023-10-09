window.performance.mark('(Nebula) Inside /modules/search.js');

nebula.initSearchFunctions = function(){
	//DOM Ready
	nebula.menuSearchReplacement();
	nebula.singleResultDrawer();

	//Window Load
	nebula.bufferedWindowLoad(function(){
		nebula.wpSearchInput();
		nebula.autocompleteSearchListeners();
		nebula.searchValidator();
		nebula.searchTermHighlighter(); //Move to (or use) requestIdleCallback when Safari supports it? Already is requesting animation frame
	});
};

//Keyword Filter
nebula.keywordSearch = function(container, parent, values, filteredClass, operator){ //Alias for old function name
	nebula.keywordFilter(container, parent, values, filteredClass, operator);
};
nebula.keywordFilter = function(container, parent, values = 'string', filteredClass = 'filtereditem', operator = 'and'){
	if ( typeof values === 'string' ){
		values = [values];
	}
	values = values.filter(String); //Remove any empty values from the array

	jQuery(container).find(parent + '.' + filteredClass).removeClass(filteredClass); //Reset everything for a new search filter

	if ( values.length ){
		if ( values.length === 1 && values[0].length > 2 && values[0].charAt(0) === '/' && values[0].slice(-1) === '/' ){
			let regex = new RegExp(values[0].substring(1).slice(0, -1), 'i'); //Convert the string to RegEx after removing the first and last /
			jQuery(container).find(parent).each(function(){ //Loop through each element to check against the regex pattern
				let elementText = jQuery(this).text().trim().replaceAll(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
				jQuery(this).addClass(filteredClass);
				if ( regex.test(elementText) ){
					jQuery(this).removeClass(filteredClass);
				}
			});
		} else if ( !operator || operator === 'and' || operator === 'all' ){ //Match only elements that contain all keywords (Default operator is And if not provided)
			jQuery.each(values, function(index, value){ //Loop through the values to search for
				if ( value && value.trim().length ){ //If the value exists and is not empty
					//Check if the value is a valid RegEx string
					try {
						var regex = new RegExp(value, 'i'); //Keep var here so variable can be used outside of the try/catch without erroring
					} catch(error){ //This is an invalid RegEx pattern
						return false; //Ignore this search
					}

					jQuery(container).find(parent).not('.' + filteredClass).each(function(){ //Now check elements that have not yet been filtered for this value
						let elementText = jQuery(this).text().trim().replaceAll(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
						if ( !regex.test(elementText) ){
							jQuery(this).addClass(filteredClass);
						}
					});
				}
			});
		} else { //Match elements that contains any keyword
			let pattern = '';
			jQuery.each(values, function(index, value){
				if ( value.trim().length ){ //If the value is not empty, add it to the pattern
					pattern += value + '|';
				}
			});
			pattern = pattern.slice(0, -1); //Remove the last | character
			let regex = new RegExp(pattern, 'i');
			jQuery(container).find(parent).each(function(){ //Loop through each element to check against the regex pattern
				let elementText = jQuery(this).text().trim().replaceAll(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
				jQuery(this).addClass(filteredClass);
				if ( regex.test(elementText) ){
					jQuery(this).removeClass(filteredClass);
				}
			});
		}
	}
};

//Menu Search Replacement
nebula.menuSearchReplacement = async function(){
	if ( jQuery('.nebula-search').length ){
		jQuery('.menu .nebula-search').each(function(){
			let randomMenuSearchID = Math.floor((Math.random()*100)+1); //Why does it need this again? Add comment please.
			jQuery(this).html('<form class="wp-menu-nebula-search nebula-search search footer-search" method="get" action="' + nebula.site.home_url + '/"><div class="nebula-input-group"><i class="fa-solid fa-magnifying-glass"></i><label class="visually-hidden" for="nebula-menu-search-' + randomMenuSearchID + '">Search</label><input type="search" id="nebula-menu-search-' + randomMenuSearchID + '" class="nebula-search input search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech /></div></form>');
		});

		jQuery('.nebula-search input').on('focus', function(){
			jQuery(this).addClass('focus active');
		});

		jQuery('.nebula-search input').on('blur', function(){
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).removeClass('focus active focusError').attr('placeholder', jQuery(this).attr('placeholder'));
			} else {
				jQuery(this).removeClass('active');
			}
		});
	}
};

//Enable autocomplete search on WordPress core selectors
nebula.autocompleteSearchListeners = async function(){
	let autocompleteSearchSelector = wp.hooks.applyFilters('nebulaAutocompleteSearchSelector', '.nebula-search input, input#s, input.search, input[name="s"]');
	jQuery(autocompleteSearchSelector).one('focus', function(){ //Only do this once
		if ( !jQuery(this).hasClass('no-autocomplete') ){ //Use this class to disable or override the default Nebula autocomplete search parameters
			nebula.loadJS(nebula.site.resources.scripts.nebula_jquery_ui, 'jquery-ui').then(function(){
				nebula.dom.document.on('blur', '.nebula-search input', function(){
					jQuery('.nebula-search').removeClass('searching').removeClass('autocompleted');
				});

				//I do not know why this cannot be debounced
				jQuery('input#s, input.search, input[name="s"]').on('keyup paste', function(e){
					let allowedKeys = ['Backspace', 'Delete']; //Non-alphanumeric keys that are still allowed to trigger a search

					if ( jQuery(this).val().trim().length && (nebula.isAlphanumeric(e.key, false) || allowedKeys.includes(e.key) ) ){
						let types = false;
						if ( jQuery(this).is('[data-types]') ){
							types = jQuery(this).attr('data-types');
						}

						nebula.autocompleteSearch(jQuery(this), types);
					} else {
						jQuery(this).closest('form').removeClass('searching');
						jQuery(this).closest('.input-group, .nebula-input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-magnifying-glass');
					}
				});
			});

			nebula.loadCSS(nebula.site.resources.styles.nebula_jquery_ui);
		}
	});
};

//Run an autocomplete search on a passed element.
nebula.autocompleteSearch = function($element, types = ''){
	if ( typeof $element === 'string' ){
		$element = jQuery($element);
	}

	if ( types && Array.isArray(types) ){
		types = types.join(','); //Convert an array to to a comma-separated string
	}

	nebula.dom.document.trigger('nebula_autocomplete_search_start', $element);
	nebula.timer('(Nebula) Autocomplete Search', 'start');
	nebula.timer('(Nebula) Autocomplete Response', 'start');

	if ( $element.val().trim().length ){
		if ( $element.val().trim().length >= 2 ){ //This checks the length for animation but the minlength (below) handles it for autocomplete
			//Add "searching" class for custom Nebula styled forms
			$element.closest('form').addClass('searching');
			setTimeout(function(){
				$element.closest('form').removeClass('searching');
			}, 10_000);

			//Swap magnifying glass on Bootstrap input-group
			$element.closest('.input-group, .nebula-input-group').find('.fa-magnifying-glass').removeClass('fa-magnifying-glass').addClass('fa-spin fa-spinner');
		} else {
			$element.closest('form').removeClass('searching');
			$element.closest('.input-group, .nebula-input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-magnifying-glass');
		}

		let typesQuery = '';
		if ( types ){
			typesQuery = '&types=' + types;
		}

		if ( typeof $element.autocomplete !== 'function' ){
			nebula.help('nebula.autocompleteSearch requires jQuery UI. Load that library before calling this function', '/functions/autocompletesearch/');
			return false;
		}

		let minLength = 3;
		if ( $element.attr('data-min-length') ){
			minLength = $element.attr('data-min-length');
		}

		$element.autocomplete({ //jQuery UI dependent
			position: {
				my: 'left top-2px',
				at: 'left bottom',
				collision: 'flip',
			},
			source: async function(request, sourceResponse){
				let searchResults = nebula.memoize('get', 'autocomplete search (' + request.term.toLowerCase() + ') [' + typesQuery + ']'); //Try from stored memory first

				if ( !searchResults ){
					var fetchResponse = await fetch(nebula.site.home_url + '/wp-json/nebula/v2/autocomplete_search?term=' + request.term + typesQuery, {
						priority: 'high'
					}).then(function(fetchResponse){
						return fetchResponse.json();
					}).then(function(fetchData){
						searchResults = nebula.memoize('set', 'autocomplete search (' + request.term.toLowerCase() + ') [' + typesQuery + ']', fetchData); //Add to stored memory
					}).catch(function(error){
						nebula.dom.document.trigger('nebula_autocomplete_search_error', request.term);
						nebula.debounce(function(){
							gtag('event', 'exception', {
								message: '(JS) Autocomplete AJAX error: ' + error,
								fatal: false
							});
							nebula.crm('event', 'Autocomplete Search AJAX Error');
						}, 1500, 'autocomplete error buffer');
						$element.closest('form').removeClass('searching');
						$element.closest('.input-group, .nebula-input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-magnifying-glass');
					});
				}

				nebula.dom.document.trigger('nebula_autocomplete_search_success', searchResults);

				var noSearchResults = ' (No Results)'; //Prep the string

				if ( searchResults ){
					nebula.dom.document.trigger('nebula_autocomplete_search_results', searchResults);
					nebula.prefetch(searchResults[0].link);

					jQuery.each(searchResults, function(index, value){
						value.label = value.label.replaceAll(/&#038;/g, '\&');
					});

					noSearchResults = '';
				} else {
					nebula.dom.document.trigger('nebula_autocomplete_search_no_results');
				}

				nebula.debounce(function(){
					let thisEvent = {
						event_name: 'search',
						event_category: 'Internal Search',
						event_action: 'Autocomplete Search' + noSearchResults,
						request: request,
						term: request.term.toLowerCase(),
						no_search_results: ( noSearchResults )? true : false,
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					nebula.fbq('track', 'Search', {search_string: thisEvent.term});
					nebula.clarity('set', thisEvent.category, thisEvent.term);
					nebula.crm('identify', {internal_search: thisEvent.term});
				}, 1500, 'autocomplete success buffer');

				gtag('event', 'timing_complete', {
					name: 'Server Response',
					value: Math.round(nebula.timer('(Nebula) Autocomplete Search', 'lap')),
					event_category: 'Autocomplete Search',
					event_label: 'Each search until server results',
				});

				sourceResponse(searchResults); //Respond to the jQuery UI Autocomplete now

				$element.closest('form').removeClass('searching').addClass('autocompleted');
				$element.closest('.input-group, .nebula-input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-magnifying-glass');
			},
			focus: function(event, ui){
				event.preventDefault(); //Prevent input value from changing.
			},
			select: function(event, ui){
				let thisEvent = {
					event_name: 'select_content',
					event_category: 'Internal Search',
					event_action: 'Autocomplete Click',
					ui: ui,
					event_label: ui.item.label,
					external: ( typeof ui.item.external !== 'undefined' )? true : false,
				};

				nebula.dom.document.trigger('nebula_autocomplete_search_selected', thisEvent.ui);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));

				gtag('event', 'timing_complete', {
					name: 'Until Navigation',
					value: Math.round(nebula.timer('(Nebula) Autocomplete Search', 'end')),
					event_category: 'Autocomplete Search',
					event_label: 'From first initial search until navigation',
				});

				if ( thisEvent.external ){
					window.open(ui.item.link, '_blank');
				} else {
					window.location.href = ui.item.link;
				}
			},
			open: function(){
				$element.closest('form').addClass('autocompleted');
				let heroAutoCompleteDropdown = jQuery('.form-identifier-nebula-hero-search');
				heroAutoCompleteDropdown.css('max-width', $element.outerWidth());
			},
			close: function(){
				$element.closest('form').removeClass('autocompleted');
			},
			minLength: minLength, //Require at least 3 characters (unless overridden by an attribute)
		}).data('ui-autocomplete')._renderItem = function(ul, item){
			let thisSimilarity = ( typeof item.similarity !== 'undefined' )? item.similarity.toFixed(1) + '% Match' : '';
			let listItem = jQuery("<li class='" + item.classes + "' title='" + thisSimilarity + "'></li>").data("item.autocomplete", item).append("<a href='" + item.link + "'> " + item.label.replaceAll(/\\/g, '') + "</a>").appendTo(ul);
			return listItem;
		};

		let thisFormIdentifier = $element.closest('form').attr('id') || $element.closest('form').attr('name') || $element.closest('form').attr('class');
		$element.autocomplete('widget').addClass('form-identifier-' + thisFormIdentifier);
	}
};

nebula.wpSearchInput = function(){
	//jQuery('#post-0 input[name="s"], #nebula-drawer input[name="s"], .search-results input[name="s"]').trigger('focus'); //Automatically focus on specific search inputs

	//Set search value as placeholder
	let searchVal = nebula.get('s') || jQuery('input[name="s"]').val();
	if ( searchVal ){
		jQuery('input[name="s"], .nebula-search input').attr('placeholder', searchVal.replaceAll('+', ' '));
	}
};

//Search Validator
nebula.searchValidator = function(){
	//Wrap in requestIdleCallback when Safari supports it
	if ( jQuery('.input.search').length ){
		jQuery('.input.search').each(function(){
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed').val('Search');
				jQuery(this).parent().find('.input.search').removeClass('focusError');
			}
		});

		jQuery('.input.search').on('focus blur change keyup paste cut', function(e){
			let thisPlaceholder = ( jQuery(this).attr('data-prev-placeholder') !== 'undefined' )? jQuery(this).attr('data-prev-placeholder') : 'Search';
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
				jQuery(this).parent().find('.btn.submit').val('Go');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
				jQuery(this).parent().find('.input.search').removeClass('focusError').prop('title', '').attr('placeholder', thisPlaceholder);
				jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
			}
			if ( e.type === 'paste' ){
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
				jQuery(this).parent().find('.input.search').prop('title', '').attr('placeholder', 'Search').removeClass('focusError');
				jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
			}
		});

		jQuery('form.search').on('submit', function(){
			if ( jQuery(this).find('.input.search').val() === '' || jQuery(this).find('.input.search').val().trim().length === 0 ){
				jQuery(this).parent().find('.input.search').prop('title', 'Enter a valid search term.').attr('data-prev-placeholder', jQuery(this).attr('placeholder')).attr('placeholder', 'Enter a valid search term').addClass('focusError').trigger('focus').attr('value', '');
				jQuery(this).parent().find('.btn.submit').prop('title', 'Enter a valid search term.').addClass('notallowed');
				return false;
			}

			return true;
		});
	}
};

//Highlight search terms
nebula.searchTermHighlighter = async function(){
	window.requestAnimationFrame(function(){
		let searchTerm = nebula.get('s');
		if ( searchTerm ){
			let termPattern = new RegExp('(?![^<]+>)(' + nebula.preg_quote(searchTerm.replaceAll(/(\+|%22|%20)/g, ' ')) + ')', 'ig'); //Find the search term within the text
			jQuery('article .entry-title a, article .entry-summary').each(function(){
				jQuery(this).html(function(i, html){
					return html.replace(termPattern, '<mark class="searchresultword">$1</mark>'); //Wrap each found search term
				});
			});

			nebula.emphasizeSearchTerms();
		}
	});
};

//Emphasize the search Terms
nebula.emphasizeSearchTerms = async function(){
	window.requestAnimationFrame(function(){
		let origBGColor = jQuery('.searchresultword').css('background-color');
		jQuery('.searchresultword').each(function(i){
			let stallFor = 150 * parseInt(i); //This creates the offset "wave" effect
			jQuery(this).delay(stallFor).animate({
				backgroundColor: 'rgba(255, 255, 0, 0.5)',
				borderColor: 'rgba(255, 255, 0, 1)',
			}, 500, 'swing', function(){
				jQuery(this).delay(1000).animate({
					backgroundColor: origBGColor,
				}, 1000, 'swing', function(){
					jQuery(this).addClass('transitionable');
				});
			});
		});
	});
};

//Single search result redirection drawer
nebula.singleResultDrawer = async function(){
	let searchTerm = nebula.get('rs');
	if ( searchTerm ){
		searchTerm = searchTerm.replaceAll(/\%20|\+/g, ' ').replaceAll(/\%22|"|'/g, '');
		jQuery('#searchform input[name="s"]').val(searchTerm);

		nebula.dom.document.on('click', '#nebula-drawer .close', function(){
			window.history.replaceState({}, document.title, nebula.removeQueryParameter('rs', window.location.href));
			jQuery('#nebula-drawer').slideUp();
			return false;
		});
	}
};