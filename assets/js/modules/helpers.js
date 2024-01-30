window.performance.mark('(Nebula) Inside helpers.js (module)');

//Miscellaneous helper classes and functions
nebula.helpers = async function(){
	if ( typeof window.gtag !== 'function' ){
		window.gtag = function(){}; //Prevent gtag() calls from erroring if GA is off or blocked. This is supplemental to a similar check in analytics.php
	}

	//Remove Sass render trigger query
	if ( nebula.get('sass') && !nebula.get('persistent') ){
		window.history.replaceState({}, document.title, nebula.removeQueryParameter('sass', window.location.href));
	}

	//Empty caches when debugging
	if ( nebula.get('debug') || nebula.dom.html.hasClass('debug') ){
		nebula.emptyCaches(); //Clear the caches
	}

	nebula.dom.html.removeClass('no-js').addClass('js');
	jQuery("a[href^='http']:not([href*='" + nebula.site.domain + "'])").attr('rel', 'external noreferrer noopener'); //Add rel attributes to external links. Although search crawlers do use JavaScript, don't rely on this line to instruct them. Use standard HTML attributes whenever possible.

	//Add general region classes (Note: not done in location.js because it is anonymized and global)
	jQuery('body').addClass('locale-' + Intl.DateTimeFormat().resolvedOptions().locale.split('-').pop().toLowerCase());
	jQuery('body').addClass('timezone-' + Intl.DateTimeFormat().resolvedOptions().timeZone.replaceAll(/[_\/]/gi, '-').toLowerCase());

	//Note the level of RAM available for a "lite" or "full" experience
	if ( 'deviceMemory' in navigator ){ //Device Memory - Chrome 64+
		let deviceMemoryLevel = ( navigator.deviceMemory < 1 )? 'lite' : 'full'; //Possible values (GB of RAM): 0.25, 0.5, 1, 2, 4, 8
		nebula.dom.body.addClass('device-memory-' + deviceMemoryLevel);
	}

	//Skip to Content button clicks - skip to the content section
	jQuery('#skip-to-content-link').on('click', function(){
		nebula.focusOnElement(jQuery('#content-section'));
	});

	//Remove filetype icons from images within <a> tags and buttons. Note: these contribute to CLS because they are not animations
	jQuery('a img').closest('a').addClass('no-icon');
	jQuery('.no-icon:not(a)').find('a').addClass('no-icon');

	jQuery('span.nebula-code').parent('p').css('margin-bottom', '0'); //Fix for <p> tags wrapping Nebula pre spans in the WYSIWYG

	//Maintain tab navigability on hashchange (and when loaded with a hash). This also helps accessibility for things like skip to content links
	if ( document.location.hash ){
		nebula.focusOnElement(jQuery(document.location.hash));
	}

	//If the hash has been changed (activation of an in-page link)
	nebula.dom.window.on('hashchange', function(){
		let hash = window.location.hash.replace(/^#/, '');
		if ( hash ){ //If the hash is not empty (like when clicking on an href="#" link)
			nebula.focusOnElement(jQuery('#' + hash));
		}
	});

	//Change the Bootstrap label for custom file upload inputs on upload
	jQuery('input[type="file"].custom-file-input').on('change', function(){
		if ( jQuery(this).parents('.custom-file').find('.custom-file-label').length ){
			let fileName = jQuery(this).val().split('\\').pop(); //Get the filename without the full path
			jQuery(this).parents('.custom-file').find('.custom-file-label').text(fileName);
		}
	});

	//Add Bootstrap form control to WP search block
	jQuery('.wp-block-search__input').addClass('form-control');
	jQuery('.wp-block-search__button').addClass('btn btn-primary');

	//Deactivate potential active states when the escape key is pressed
	nebula.dom.document.on('keydown', function(e){
		if ( e.key === 'Escape' ){
			nebula.dom.document.trigger('esc'); //Trigger a simpler DOM event. Is this helpful?

			//Close modals
			jQuery('.modal').modal('hide');
		}
	});

	//Nebula preferred default Chosen.js options
	nebula.chosenOptions = wp.hooks.applyFilters('nebulaChosenOptions', {
		disable_search_threshold: 5,
		search_contains: true,
		no_results_text: 'No results found.',
		allow_single_deselect: true,
		width: '100%'
	});

	nebula.dragDropUpload();

	//Remove this once QM allows sortable Timings table
	if ( jQuery('#qm-timing').length ){
		nebula.qmSortableHelper(); //Temporary QM helper.
	}
};

//Sub-menu viewport overflow detector
nebula.overflowDetector = async function(){
	if ( jQuery('.sub-menu').length ){ //Only add the event listener if sub-menus actually exist
		jQuery('.menu li.menu-item').on({
			'mouseenter focus focusin': function(){
				if ( jQuery(this).children('.sub-menu').length ){ //Check if this menu has sub-menus
					let submenuLeft = jQuery(this).children('.sub-menu').offset().left; //Left side of the sub-menu
					let submenuRight = submenuLeft+jQuery(this).children('.sub-menu').width(); //Right side of the sub-menu

					if ( submenuRight > nebula.dom.window.width() ){ //If the right side is greater than the width of the viewport
						jQuery(this).children('.sub-menu').addClass('overflowing overflowing-left');
					} else if ( submenuLeft > nebula.dom.window.width() ){
						jQuery(this).children('.sub-menu').addClass('overflowing overflowing-right');
					} else {
						//jQuery(this).children('.sub-menu').removeClass('overflowing overflowing-left overflowing-right'); //This sometimes causes a movement/overflow on click so seems to work fine/better with it commented out.
					}
				}
			},
			'mouseleave': function(){
				jQuery(this).children('.sub-menu').removeClass('overflowing overflowing-left overflowing-right');
			}
		});
	}
};

//Enable drag and drop uploading for Contact Form 7 file inputs
nebula.dragDropUpload = async function(){
	if ( jQuery('.nebula-drop-area').length ){
		//Activate drag and drop listeners for each drop area class on the page
		document.querySelectorAll('.nebula-drop-area').forEach(function(dropArea){
			let thisEvent = {
				event_category: 'Drag and Drop File Upload',
				form_id: jQuery(dropArea).closest('form').attr('id') || 'form.' + jQuery(dropArea).closest('form').attr('class').replace(/\s/g, '.'),
				file_input_id: jQuery(dropArea).find('input[type="file"]').attr('id'),
			};

			//Drag over
			dropArea.addEventListener('dragover', function(e){ //This gets called every frame of the hover... Can we throttle it without causing a problem?
				e.stopPropagation();
				e.preventDefault();

				jQuery(dropArea).addClass('dragover');
				e.dataTransfer.dropEffect = 'copy'; //Visualize to the user the "copy" cursor

				nebula.debounce(function(){
					thisEvent.event_name = 'file_upload_dragover';
					thisEvent.event_action = 'Drag Over';
					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				}, 500, 'file drag over');
			});

			//Drag out
			dropArea.addEventListener('dragleave', function(e){
				jQuery(dropArea).addClass('dragover');

				thisEvent.event_name = 'file_upload_dragleave';
				thisEvent.event_action = 'Drag Leave';
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			});

			//Drop
			dropArea.addEventListener('drop', function(e){
				e.stopPropagation();
				e.preventDefault();

				jQuery(dropArea).removeClass('dragover');

				let fileInput = dropArea.querySelectorAll('input[type="file"]')[0]; //Find the file input field within this drop area
				let acceptedFiles = jQuery(fileInput).attr('accept').replaceAll(/\s?\./g, '').split(',');
				let thisFileType = e.dataTransfer.files[0].type.replace(/\S+\//, '');

				thisEvent.file_type = thisFileType;
				thisEvent.file = e.dataTransfer.files[0];

				if ( !jQuery(fileInput).attr('accept').length || (e.dataTransfer.files.length === 1 && acceptedFiles.includes(thisFileType)) ){ //If the uploader does not restrict file types, or if only one file was uploaded and that filetype is accepted
					jQuery(dropArea).addClass('dropped is-valid');

					fileInput.files = e.dataTransfer.files; //Fill the file upload input with the uploaded file
					jQuery(fileInput).parents('.custom-file').find('.custom-file-label').text(e.dataTransfer.files[0].name); //Update the Bootstrap label to show the filename

					thisEvent.event_name = 'file_upload_dragdrop_accepted';
					thisEvent.event_action = 'Dropped (Accepted)';
					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));

				} else {
					nebula.temporaryClass(jQuery(dropArea), 'rejected', '', 1500);
					nebula.applyValidationClasses(jQuery(fileInput), 'invalid', true); //Show the invalid message

					thisEvent.event_name = 'file_upload_dragdrop_rejected';
					thisEvent.event_action = 'Dropped (Rejected)';
					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				}
			});
		});
	}
};

//Convert img tags with class .svg to raw SVG elements
nebula.svgImgs = async function(){
	jQuery('img.svg').each(function(){
		let $oThis = jQuery(this);

		if ( $oThis.attr('src').includes('.svg') ){ //If the src has a .svg extension
			fetch($oThis.attr('src'), {
				method: 'GET', //Could set a priority here, but should these be high or low?
			}).then(function(response){
				if ( response.ok ){
					return response.text();
				}
			}).then(function(data){
				let $theSVG = jQuery(data); //Get the SVG tag, ignore the rest
				$theSVG = $theSVG.attr('id', $oThis.attr('id')); //Add replaced image's ID to the new SVG
				$theSVG = $theSVG.attr('class', $oThis.attr('class') + ' replaced-svg'); //Add replaced image's classes to the new SVG
				$theSVG = $theSVG.attr('role', 'img');
				$theSVG = $theSVG.attr('alt', nebula.sanitize($oThis.attr('alt'))); //An SVG with a role of img must include an alt attribute
				$theSVG = $theSVG.attr('aria-label', nebula.sanitize($oThis.attr('alt'))); //Add an aria-label attribute as well
				$theSVG = $theSVG.attr('data-originalsrc', $oThis.attr('src')); //Add an attribute of the original SVG location
				$theSVG = $theSVG.removeAttr('xmlns:a'); //Remove invalid XML tags

				$oThis.replaceWith($theSVG); //Replace image with new SVG

				//Use the alt attribute as a title tag within the SVG (title must be the first tag inside the <svg>) as well
				if ( $oThis.attr('alt') ){
					$theSVG.prepend('<title>' + nebula.sanitize($oThis.attr('alt')) + '</title>'); //Sanitized to prevent XSS
				}

				//Move the title attribute to the description element within the SVG
				if ( $oThis.attr('title') ){
					$theSVG.prepend('<description>' + nebula.sanitize($oThis.attr('title')) + '</description>'); //Sanitized to prevent XSS
				}
			});
		}
	});
};

//Listen for scrollTo events
nebula.scrollToListeners = function(){
	if ( nebula.dom.html.css('scroll-behavior') !== 'smooth' ){ //If the html has smooth scroll-behavior, use that instead of this
		//An href starts with a hash ID but is not only a hash ("#content" but not "#"). Do not use *="#" to prevent conflicts with other libraries who are linking to separate pages with an anchor on the destination.
		nebula.dom.document.on('click keyup', 'a[href^="#"]:not([href="#"])', function(e){
			if ( e.type === 'click' || (e.type === 'keyup' && (e.key === ' ' || e.key === 'Enter')) ){ //Spacebar or Enter
				let avoid = '.no-scroll, .mm-menu, .carousel, .tab-content, .modal, [data-bs-toggle], #wpadminbar, #query-monitor';
				if ( !jQuery(this).is(avoid) && !jQuery(this).parents(avoid).length ){
					if ( location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname ){ //Ensure the link does not have a protocol and is internal
						let thisHash = this.hash; //Defined here because scope of "this" changes later
						let scrollElement = jQuery.find(thisHash) || jQuery('[name=' + thisHash.slice(1) +']'); //Determine the target

						if ( scrollElement.length ){ //If target exists
							let pOffset = ( jQuery(this).attr('data-offset') )? parseFloat(jQuery(this).attr('data-offset')) : nebula.scroll.offset; //Determine the offset
							let speed = nebula.scroll.speed || 500;

							nebula.scrollTo(scrollElement, pOffset, speed, false, function(){
								history.replaceState({}, '', thisHash); //Add the hash to the URL so it can be refreshed, copied, links, etc. ReplaceState does this without affecting the back button.
							});

							return false;
						}
					}
				}
			}
		});
	}

	//Using the nebula-scrollto class with data-scrollto attribute
	//Note: Unlike the above click listener, this method ignores the browser "smooth scroll" setting and always handles the scroll
	nebula.dom.document.on('click keyup', '.nebula-scrollto', function(e){
		if ( e.type === 'click' || (e.type === 'keyup' && (e.key === ' ' || e.key === 'Enter')) ){ //Spacebar or Enter
			let pOffset = ( jQuery(this).attr('data-offset') )? parseFloat(jQuery(this).attr('data-offset')) : nebula.scroll.offset;

			if ( jQuery(this).attr('data-scrollto') ){
				let scrollElement = jQuery.find(jQuery(this).attr('data-scrollto'));

				if ( scrollElement !== '' ){
					let scrollSpeed = nebula.scroll.speed || 500;
					nebula.scrollTo(scrollElement, pOffset, scrollSpeed);
				}
			}

			return false;
		}
	});
};

//Scroll an element into view
//This can eventually be replaced with scrollIntoView() native JS function, but until it has a timing feature it is not as robust. Also smooth scroll-behavior in CSS interferes with this.
//Note: Offset must be an integer
nebula.scrollTo = function($element, offset = 0, speed = 500, onlyWhenBelow = false, callback){
	if ( !offset ){
		offset = nebula.scroll.offset || 0; //Note: This selector should be the height of the fixed header, or a hard-coded offset.
	}

	//Account for the scroll-padding-top CSS property on the body element
	let scrollPaddingTop = parseInt(nebula.dom.body.css('scroll-padding-top'), 10); //Parse the CSS value as a base-10 integer
	if ( !isNaN(scrollPaddingTop) ){
		offset += scrollPaddingTop; //Add the scroll padding top to the offset
	}

	//Call this function with a jQuery object to trigger scroll to an element (not just a selector string).
	if ( $element ){
		if ( typeof $element === 'string' ){
			$element = jQuery.find($element); //Use find here to prevent arbitrary JS execution
		} else if ( !$element.jquery ){ //Check if it is already a jQuery object
			$element = jQuery($element);
		}

		if ( $element.length ){
			let willScroll = true;
			if ( onlyWhenBelow ){
				let elementTop = $element.offset().top-offset;
				let viewportTop = nebula.dom.document.scrollTop();
				if ( viewportTop-elementTop <= 0 ){
					willScroll = false;
				}
			}

			if ( willScroll ){
				if ( !speed ){
					speed = nebula.scroll.speed || 500;
				}

				jQuery('html, body').animate({
					scrollTop: $element.offset().top-offset
				}, speed, function(){
					nebula.focusOnElement($element);

					if ( callback ){
						return callback();
					}
				});
			}
		}

		return false;
	}
};

//Temporarily change an element class (like Font Awesome or Bootstrap icon) and then change back after a period of time
nebula.temporaryClass = function($element, activeClass, inactiveClass, period = 1500){
	if ( $element && activeClass ){
		if ( typeof $element === 'string' ){
			$element = jQuery($element);
		}

		if ( !inactiveClass ){
			if ( $element.is('fa, fas, far, fab, fad, fat, fal, fa-solid, fa-regular, fa-brands, fa-duotone, fa-thin, fa-light') ){ //Font Awesome icon element
				inactiveClass = (/fa-(?!fw)\S+/i).test($element.attr('class')); //Match the first Font Awesome icon class that is the actual icon (exclude fa-fw for example)
			} else if ( $element.is('bi') ){ //Bootstrap icon element
				inactiveClass = (/bi-\S+/i).test($element.attr('class')); //Match the first Bootstrap icon class
			} else {
				inactiveClass = ''; //Set to an empty string to only use a temporary active class
			}
		}

		$element.removeClass(inactiveClass).addClass(activeClass + ' temporary-status-active'); //Remove the inactive class and add the active class
		setTimeout(function(){
			$element.removeClass(activeClass + ' temporary-status-active').addClass(inactiveClass); //After the period of time, revert back to the inactive class
		}, period);
	}

	return false;
};

//Vertical subnav expanders
nebula.subnavExpanders = function(){
	if ( nebula.site?.options?.sidebar_expanders && jQuery('#sidebar-section .menu').length ){
		jQuery('#sidebar-section .menu li.menu-item:has(ul)').addClass('has-expander').append('<a class="toplevelvert_expander closed" href="#"><i class="fa-solid fa-caret-left"></i> <span class="visually-hidden">Expand</span></a>');
		jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
		nebula.dom.document.on('click', '.toplevelvert_expander', function(){
			jQuery(this).toggleClass('closed open').parent().children('.sub-menu').slideToggle();
			return false;
		});

		//Automatically expand subnav to show current page
		jQuery('.current-menu-ancestor').children('.toplevelvert_expander').click();
		jQuery('.current-menu-item').children('.toplevelvert_expander').click();
	}
};

//Functionality for selecting and copying text using Nebula Pre tags.
nebula.pre = async function(){
	//Format non-shortcode pre tags to be styled properly
	jQuery('pre.nebula-code').each(function(){
		if ( !jQuery(this).parent('.nebula-code-con').length ){
			let lang = jQuery(this).attr('data-lang') || '';
			if ( lang === '' ){
				let langMatches = jQuery(this).attr('class').match(/lang(?:uage)?-(\S*)/i);
				lang = ( langMatches )? langMatches[0] : ''; //Use a class that starts with "lang-" or "language-" Ex: "lang-JavaScript"
			}
			if ( lang === '' ){
				lang = jQuery(this).attr('class').replace('nebula-code', '').replaceAll(/(\s*)((wp|m.|p.|nebula)-\S+)(\s*)/gi, '').trim(); //Remove expected classes and use remaining class as language
			}

			lang = escape(lang); //Escape for reuse into the DOM

			jQuery(this).addClass(lang.toLowerCase()).wrap('<div class="nebula-code-con clearfix ' + lang.toLowerCase() + '"></div>');
			jQuery(this).closest('.nebula-code-con').prepend('<span class="nebula-code codetitle ' + lang.toLowerCase() + '">' + lang + '</span>');
		}
	});

	//Manage copying snippets to clipboard
	if ( 'clipboard' in navigator ){
		jQuery('.nebula-code-con').each(function(){
			jQuery(this).append('<a href="#" class="nebula-selectcopy-code">Copy to Clipboard</a>');
			jQuery(this).find('p:empty').remove(); //Sometimes WordPress adds extra/empty <p> tags. These mess with spacing, so we remove them.
		});

		nebula.dom.document.on('click', '.nebula-selectcopy-code', function(){
			let $oThis = jQuery(this);
			if ( $oThis.hasClass('error') ){ //If we already errored, stop trying
				return false;
			}

			let text = jQuery(this).closest('.nebula-code-con').find('pre').text();

			navigator.clipboard.writeText(text).then(function(){
				$oThis.text('Copied!').removeClass('error').addClass('success');
				setTimeout(function(){
					$oThis.text('Copy to clipboard').removeClass('success');
				}, 1500);
			}).catch(function(error){ //This can happen if the user denies clipboard permissions
				gtag('event', 'Exception', { //Report the error to Google Analytics to log it
					message: '(JS) Clipboard API error: ' + error,
					fatal: false
				});

				$oThis.text('Unable to copy.').addClass('error');
			});

			return false;
		});
	}
};

//Cookie notification
nebula.cookieNotification = async function(){
	if ( jQuery('#nebula-cookie-notification').length && !nebula.readCookie('acceptcookies') ){
		//Show the notice as soon as it will not interfere with loading nor become laggy
		window.requestAnimationFrame(function(){ //Change to requestIdleCallback when Safari supports it
			jQuery('#nebula-cookie-notification').addClass('active');
		});

		//Hide the interface upon acceptance
		nebula.dom.document.on('click', '#nebula-cookie-accept', function(){
			nebula.createCookie('acceptcookies', true);

			window.requestAnimationFrame(function(){
				jQuery('#nebula-cookie-notification').removeClass('active');

				//Remove the entire element after the animation completes
				setTimeout(function(){
					jQuery('#nebula-cookie-notification').remove();
				}, 1000); //The animation is set to 750ms
			});

			gtag('consent', 'update', {
				ad_storage: 'granted', //Explicitly allows storage (such as cookies) related to advertising
				ad_user_data: 'granted', //Sets consent for sending user data to Google for advertising purposes (New in 2024)
				ad_personalization: 'granted', //Sets consent for personalized advertising (New in 2024)
				analytics_storage: 'granted', //Explicitly allows storage (such as cookies) related to analytics
				functionality_storage: 'granted', //Explicitly allows storage that supports the functionality of the website
				personalization_storage: 'granted', //Explicitly allows storage related to personalization
				security_storage: 'granted' //Explicitly allows storage related to security such as authentication functionality, fraud prevention, and other user protection
			});

			return false;
		});
	}
};

//Show help messages in the console to assist developers by informing of common issues and guide them to relevant documentation
nebula.help = function(message, path, usage=false){
	let documentationHostname = '';

	if ( !path.includes('http') ){ //If the path is a full URL, use it explicitly
		documentationHostname = 'https://nebula.gearside.com'; //Otherwise start with this hostname

		if ( path.charAt(0) !== '/' ){ //If the path does not begin with a slash, add one
			path = '/' + path;
		}

		let queryChar = ( path.includes('?') )? '&' : '?'; //If the path already has a query string, use an ampersand for ours

		path = path + queryChar + 'utm_source=console';
	}

	let url = documentationHostname + path;

	//console.error('📎 [Nebula Help]', message, 'Docs: ' + url); //Show the message to the developer in the console. Disabled to reduce console clutter.
	gtag('event', 'Exception', { //Report the error to Google Analytics to log it
		message: '(JS) ' + message,
		fatal: false
	});

	if ( usage ){
		nebula.usage(message);
	}
};

//This is only meant to be a temporary solution to allow for sorting the Query Monitor Timings table. Delete this as soon as possible.
nebula.qmSortableHelper = function(){
	if ( jQuery('#qm-timing').length ){
		const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
		const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
			v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
		)(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

		document.querySelectorAll('#qm-timing th').forEach((th) => th.addEventListener('click', (() => {
			jQuery('#qm-timing th').removeAttr('style');
			jQuery(th).attr('style', 'font-weight: bold !important;');
			const table = th.closest('table.qm-sortable');
			const tbody = table.querySelector('tbody');
			Array.from(tbody.querySelectorAll('tr')).sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc)).forEach((tr) => tbody.appendChild(tr) );
		})));
	}
};