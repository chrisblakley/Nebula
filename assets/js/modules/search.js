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
		//If a regex pattern is specified
		if ( values.length === 1 && values[0].length > 2 && values[0].charAt(0) === '/' && values[0].slice(-1) === '/' ){
			let regex = new RegExp(values[0].substring(1).slice(0, -1), 'i'); //Convert the string to RegEx after removing the first and last /
			jQuery(container).find(parent).each(function(){ //Loop through each element to check against the regex pattern
				let elementText = jQuery(this).text().trim().replaceAll(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
				jQuery(this).addClass(filteredClass);
				if ( regex.test(elementText) ){
					jQuery(this).removeClass(filteredClass);
				}
			});
		} else {
			if ( !operator || operator === 'and' || operator === 'all' ){ //Match only elements that contain all keywords (Default operator is And if not provided)
				jQuery.each(values, function(index, value){ //Loop through the values to search for
					if ( value && value.trim().length ){ //If the value exists and is not empty
						let regex = new RegExp(value, 'i');

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
	}
};

//Menu Search Replacement
nebula.menuSearchReplacement = async function(){
	if ( jQuery('.nebula-search').length ){
		jQuery('.menu .nebula-search').each(function(){
			let randomMenuSearchID = Math.floor((Math.random()*100)+1);
			jQuery(this).html('<form class="wp-menu-nebula-search nebula-search search footer-search" method="get" action="' + nebula.site.home_url + '/"><div class="input-group"><i class="fas fa-search"></i><label class="sr-only" for="nebula-menu-search-' + randomMenuSearchID + '">Search</label><input type="search" id="nebula-menu-search-' + randomMenuSearchID + '" class="nebula-search input search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech /></div></form>');
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
	let autocompleteSearchSelector = wp.hooks.applyFilters('nebulaAutocompleteSearchSelector', '.nebula-search input, input#s, input.search');
	jQuery(autocompleteSearchSelector).on('focus', function(){
		if ( !jQuery(this).hasClass('no-autocomplete') ){ //Use this class to disable or override the default Nebula autocomplete search parameters
			nebula.loadJS(nebula.site.resources.scripts.nebula_jquery_ui, 'jquery-ui').then(function(){
				nebula.dom.document.on('blur', '.nebula-search input', function(){
					jQuery('.nebula-search').removeClass('searching').removeClass('autocompleted');
				});

				//I do not know why this cannot be debounced
				jQuery('input#s, input.search').on('keyup paste change', function(e){
					let allowedKeys = ['Backspace', 'Delete'];

					if ( jQuery(this).val().trim().length && (nebula.isAlphanumeric(e.key) || allowedKeys.includes(e.key) ) ){
						let types = false;
						if ( jQuery(this).is('[data-types]') ){
							types = jQuery(this).attr('data-types');
						}

						nebula.autocompleteSearch(jQuery(this), types);
					}
				});
			});

			nebula.loadCSS(nebula.site.resources.styles.nebula_jquery_ui);
		}
	});
};

//Run an autocomplete search on a passed element.
nebula.autocompleteSearch = function(element, types = ''){
	if ( typeof element === 'string' ){
		element = jQuery(element);
	}

	if ( types && Array.isArray(types) ){
		types = types.join(','); //Convert an array to to a comma-separated string
	}

	nebula.dom.document.trigger('nebula_autocomplete_search_start', element);
	nebula.timer('(Nebula) Autocomplete Search', 'start');
	nebula.timer('(Nebula) Autocomplete Response', 'start');

	if ( element.val().trim().length ){
		if ( element.val().trim().length >= 2 ){ //This checks the length for animation but the minlength (below) handles it for autocomplete
			//Add "searching" class for custom Nebula styled forms
			element.closest('form').addClass('searching');
			setTimeout(function(){
				element.closest('form').removeClass('searching');
			}, 10_000);

			//Swap magnifying glass on Bootstrap input-group
			element.closest('.input-group').find('.fa-search').removeClass('fa-search').addClass('fa-spin fa-spinner');
		} else {
			element.closest('form').removeClass('searching');
			element.closest('.input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-search');
		}

		let typesQuery = '';
		if ( types ){
			typesQuery = '&types=' + types;
		}

		if ( typeof element.autocomplete !== 'function' ){
			nebula.help('nebula.autocompleteSearch requires jQuery UI. Load that library before calling this function', '/functions/autocompletesearch/');
			return false;
		}

		element.autocomplete({ //jQuery UI dependent
			position: {
				my: 'left top-2px',
				at: 'left bottom',
				collision: 'flip',
			},
			source: function(request, sourceResponse){
				fetch(nebula.site.home_url + '/wp-json/nebula/v2/autocomplete_search?term=' + request.term + typesQuery, {importance: 'high'}).then(function(fetchResponse){
					return fetchResponse.json();
				}).then(function(fetchData){
					nebula.dom.document.trigger('nebula_autocomplete_search_success', fetchData);
					ga('set', nebula.analytics.metrics.autocompleteSearches, 1);

					var noSearchResults = ' (No Results)'; //Prep the string

					if ( fetchData ){
						nebula.dom.document.trigger('nebula_autocomplete_search_results', fetchData);
						nebula.prefetch(fetchData[0].link);

						jQuery.each(fetchData, function(index, value){
							value.label = value.label.replaceAll(/&#038;/g, '\&');
						});

						noSearchResults = '';
					} else {
						nebula.dom.document.trigger('nebula_autocomplete_search_no_results');
					}

					nebula.debounce(function(){
						let thisEvent = {
							category: 'Internal Search',
							action: 'Autocomplete Search' + noSearchResults, //GA4 name: "search"
							request: request,
							term: request.term.toLowerCase(),
							noResults: ( noSearchResults )? true : false,
						};

						nebula.dom.document.trigger('nebula_event', thisEvent);
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.term);
						if ( typeof fbq === 'function' ){fbq('track', 'Search', {search_string: thisEvent.term});}
						if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.term);}
						nebula.crm('identify', {internal_search: thisEvent.term});
					}, 1500, 'autocomplete success buffer');

					ga('send', 'timing', 'Autocomplete Search', 'Server Response', Math.round(nebula.timer('(Nebula) Autocomplete Search', 'lap')), 'Each search until server results');
					sourceResponse(fetchData);
					element.closest('form').removeClass('searching').addClass('autocompleted');
					element.closest('.input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-search');
				}).catch(function(XMLHttpRequest, textStatus, errorThrown){
					nebula.dom.document.trigger('nebula_autocomplete_search_error', request.term);
					nebula.debounce(function(){
						ga('send', 'exception', {'exDescription': '(JS) Autocomplete AJAX error: ' + textStatus, 'exFatal': false});
						nebula.crm('event', 'Autocomplete Search AJAX Error');
					}, 1500, 'autocomplete error buffer');
					element.closest('form').removeClass('searching');
					element.closest('.input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-search');
				});
			},
			focus: function(event, ui){
				event.preventDefault(); //Prevent input value from changing.
			},
			select: function(event, ui){
				let thisEvent = {
					category: 'Internal Search',
					action: 'Autocomplete Click', //GA4 name: "select_content"
					ui: ui,
					label: ui.item.label,
					external: ( typeof ui.item.external !== 'undefined' )? true : false,
				};

				ga('set', nebula.analytics.metrics.autocompleteSearchClicks, 1);
				nebula.dom.document.trigger('nebula_autocomplete_search_selected', thisEvent.ui);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				ga('send', 'timing', 'Autocomplete Search', 'Until Navigation', Math.round(nebula.timer('(Nebula) Autocomplete Search', 'end')), 'From first initial search until navigation');

				if ( thisEvent.external ){
					window.open(ui.item.link, '_blank');
				} else {
					window.location.href = ui.item.link;
				}
			},
			open: function(){
				element.closest('form').addClass('autocompleted');
				let heroAutoCompleteDropdown = jQuery('.form-identifier-nebula-hero-search');
				heroAutoCompleteDropdown.css('max-width', element.outerWidth());
			},
			close: function(){
				element.closest('form').removeClass('autocompleted');
			},
			minLength: 3, //Require at least 3 characters
		}).data('ui-autocomplete')._renderItem = function(ul, item){
			let thisSimilarity = ( typeof item.similarity !== 'undefined' )? item.similarity.toFixed(1) + '% Match' : '';
			let listItem = jQuery("<li class='" + item.classes + "' title='" + thisSimilarity + "'></li>").data("item.autocomplete", item).append("<a href='" + item.link + "'> " + item.label.replaceAll(/\\/g, '') + "</a>").appendTo(ul);
			return listItem;
		};

		let thisFormIdentifier = element.closest('form').attr('id') || element.closest('form').attr('name') || element.closest('form').attr('class');
		element.autocomplete('widget').addClass('form-identifier-' + thisFormIdentifier);
	}
};

nebula.wpSearchInput = function(){
	jQuery('#post-0 #s, #nebula-drawer #s, .search-results #s').trigger('focus'); //Automatically focus on specific search inputs

	//Set search value as placeholder
	let searchVal = nebula.get('s') || jQuery('#s').val();
	if ( searchVal ){
		jQuery('#s, .nebula-search input').attr('placeholder', searchVal.replaceAll('+', ' '));
	}
};

//Mobile search placeholder toggle
nebula.mobileSearchPlaceholder = async function(){
	let mobileHeaderSearchInput = jQuery('#mobileheadersearch input');
	let searchPlaceholder = wp.hooks.applyFilters('nebulaMobileSearchPlaceholder', 'What are you looking for?');

	if ( window.matchMedia('(max-width: 410px)').matches ){
		searchPlaceholder = 'Search';
	}
	mobileHeaderSearchInput.attr('placeholder', searchPlaceholder);
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
			} else {
				return true;
			}
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
		jQuery('#searchform input#s').val(searchTerm);

		nebula.dom.document.on('click', '#nebula-drawer .close', function(){
			window.history.replaceState({}, document.title, nebula.removeQueryParameter('rs', window.location.href));
			jQuery('#nebula-drawer').slideUp();
			return false;
		});
	}
};

//Page Suggestions for 404 or no search results pages using Google Custom Search Engine
nebula.pageSuggestion = async function(){
	if ( nebula.dom.body.hasClass('search-no-results') || nebula.dom.body.hasClass('error404') ){
		if ( nebula.site?.options?.nebula_cse_id !== '' && nebula.site?.options?.nebula_google_browser_api_key !== '' ){

			let queryStrings;
			if ( nebula.get().length ){
				queryStrings = nebula.get();
			} else {
				queryStrings = [''];
			}

			let path = window.location.pathname;
			let phrase = decodeURIComponent(path.replaceAll(/\/+/g, ' ')).trim() + ' ' + decodeURIComponent(queryStrings[0].replaceAll(/\+/g, ' ')).trim();
			nebula.tryGCSESearch(phrase);
		}

		nebula.dom.document.on('mousedown', 'a.gcse-suggestion, a.internal-suggestion', function(e){
			let thisEvent = {
				event: e,
				category: 'Page Suggestion',
				action: ( jQuery(this).hasClass('internal-suggestion') )? 'Internal' : 'GCSE', //GA4 name: "select_content"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				suggestion: jQuery(this).text(),
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.suggestion);
			nebula.crm('event', 'Page Suggestion Click');
		});
	}
};

//Run a Google Custom Search Engine query to find relevant pages
nebula.tryGCSESearch = function(phrase){
	if ( nebula.site.options.nebula_cse_id.length && nebula.site.options.nebula_google_browser_api_key.length ){
		let queryParams = {
			cx: nebula.site.options.nebula_cse_id,
			key: nebula.site.options.nebula_google_browser_api_key,
			num: 10,
			q: phrase,
			alt: 'JSON'
		};
		const API_URL = 'https://www.googleapis.com/customsearch/v1?';

		//Send the request to the custom search API
		jQuery.getJSON(API_URL, queryParams, function(response){ //Update this to fetch
			if ( response.items ){
				if ( response.items[0].link !== window.location.href ){ //If the top result does not match the current page URL
					nebula.showSuggestedGCSEPage(response.items[0].title, response.items[0].link); //Show the suggestion
				}
			}
		});
	}
};

nebula.showSuggestedGCSEPage = function(title, url){
	const hostname = new RegExp(location.host);
	if ( hostname.test(url) ){ //Only show results for this same website
		jQuery('.gcse-suggestion').attr('href', url).text(title);
		jQuery('#nebula-drawer.suggestedpage').slideDown();
		nebula.prefetch(url);
	}
};