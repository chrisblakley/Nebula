window.performance.mark('(Nebula) Inside /admin-modules/helpers.js');

//Ensure all links in CF7 submission tables open in a new tab
jQuery('.nebula-cf7-submissions a').attr('target', '_blank').attr('rel', 'noopener noreferrer');

//Show relative times in title tooltips
if ( jQuery('.relative-date-tooltip').length ){
	let pageLoadTime = new Date();

	jQuery(document).on('mouseover', '.relative-date-tooltip', function(){
		let relativeDate = pageLoadTime; //Default to page load time

		//Use a provided date if available
		if ( jQuery(this).attr('data-date') ){
			relativeDate = new Date(parseInt(jQuery(this).attr('data-date'))*1000);
		}

		jQuery(this).attr('title', nebula.timeAgo(relativeDate)); // Update the title to show relative time
	});
}

//Notify for possible duplicate post slug
nebula.uniqueSlugChecker = function(){
	if ( jQuery('.edit-post-post-link__link-post-name').length ){
		if ( jQuery('.edit-post-post-link__link-post-name').text().match(/(-\d+)\/?$/) ){
			jQuery('a.edit-post-post-link__link').css('color', 'red');
			jQuery('.edit-post-post-link__preview-label').html('<span title="This likely indicates a duplicate post, but will not prevent saving or publishing." style="cursor: help;">Possible duplicate:</span>');
		}
	}
};

//Copy AI Title and Meta Description Prompts
if ( jQuery('#ai-post-title').length ){
	//if ( nebula.isBrowserAiAvailable() ){ //If the Gemini API is available in this browser
	//	jQuery('#nebula-post .nebula-ai-button').html('Generate <small>(On-Device)</small>');
	//}

	jQuery('#ai-post-title').on('click', function(e){
		e.preventDefault();
		nebula.generatePostMetaPrompt('title');
		return false;
	});

	jQuery('#ai-post-meta-description').on('click', function(e){
		e.preventDefault();
		nebula.generatePostMetaPrompt('description');
		return false;
	});

	jQuery('#ai-post-content').on('click', function(e){
		e.preventDefault();
		nebula.generatePostMetaPrompt('ideas');
		return false;
	});

	//"Tokenless" method of coping a prompt to the clipboard and opening the AI tool in a new tab
	nebula.generatePostMetaPrompt = function(type='meta description'){
		if ( window.wp && wp.data && wp.data?.select ){
			const postTitle = wp.data.select('core/editor').getEditedPostAttribute('title');
			const blocks = wp.data.select('core/block-editor').getBlocks();
			const headings = blocks.filter(b => b.name === 'core/heading').map(b => b.attributes.content).filter(Boolean); //Get the headings from the post content
			const introParagraph = (blocks.find(b => b.name === 'core/paragraph' && b.attributes?.content)?.attributes.content) || ''; //Get the first paragraph from the post content

			//This gets the rest of the content
			const cleanContent = wp.blocks.parse(wp.data.select('core/editor').getEditedPostAttribute('content'))
				.map(b => b.attributes?.content)
				.filter(Boolean)
				.join('\n\n');

			let prompt = '';

			if ( type.includes('description') ){
				type = 'meta description';
			} else if ( type.includes('title') ){
				type = 'SEO title';
			}

			if ( type.includes('idea') ){
				prompt += 'Give me additional content ideas (such as sections, headings, topics, etc.), focusing on SEO, for a web page post';
			} else {
				prompt += 'Create a ' + type + ' for a web page';
			}

			if ( postTitle ){
				prompt += ' currently titled "' + postTitle + '"';
			}

			if ( headings ){
				prompt += ' with the following headings: ' + headings.join(', ');
			}

			if ( introParagraph ){
				prompt += ' with the following intro paragraph: ' + introParagraph;
			}

			//If the Gemini API is available in this browser
			//if ( nebula.isBrowserAiAvailable() ){
				//@todo "Nebula" 0: Run the prompt and show the result (somewhere... modal? textarea?)
				//return;
			//}

			//Otherwise, no API is available, so copy the prompt to the clipboard and open ChatGPT
			navigator.clipboard.writeText(prompt).then(function(){
				window.open('https://chatgpt.com/', '_blank');
			});
		}
	}
}

//Allow tab character in textareas
nebula.pasteIntoInput = function(element, text){
	element.focus();
	var val = element.value;
	if ( typeof element.selectionStart === 'number' ){
		var selStart = element.selectionStart;
		element.value = val.slice(0, selStart) + text + val.slice(element.selectionEnd);
		element.selectionEnd = element.selectionStart = selStart + text.length;
	} else if ( typeof document.selection !== 'undefined' ){
		var textRange = document.selection.createRange();
		textRange.text = text;
		textRange.collapse(false);
		textRange.select();
	}
};

nebula.allowTabChar = function(element){
	jQuery(element).on('keydown', function(e){
		if ( e.key === 'Tab' ){
			nebula.pasteIntoInput(this, '\t');
			return false;
		}
	});
};

jQuery.fn.allowTabChar = function(){
	if ( this.jquery ){
		this.each(function(){
			if ( this.nodeType === 1 ){
				var nodeName = this.nodeName.toLowerCase();
				if ( nodeName === 'textarea' || (nodeName === 'input' && this.type === 'text') ){
					nebula.allowTabChar(this);
				}
			}
		});
	}

	return this;
};

//Countdown any cooldown timers (such as when Sass processing is thresholded)
//Note: this function is defined in both /modules/helpers.js and /admin-modules/helpers.js
nebula.initCooldowns = function(){
	jQuery('[data-cooldown]').each(function(){
		let $oThis = jQuery(this);
		let timeleft = parseInt($oThis.attr('data-cooldown'));
		let cooldownTimer = setInterval(function(){
			timeleft--;

			let units = '';
			if ( $oThis.attr('data-units') && $oThis.attr('data-units').includes('second') ){
				units = ( timeleft === 1 )? ' second' : ' seconds';
			} else if ( $oThis.attr('data-units') && $oThis.attr('data-units') == 's' ){
				units = 's';
			}

			let output = timeleft + units;
			if ( $oThis.attr('data-parenthesis') ){
				output = '(' + timeleft + units + ')';
			}

			$oThis.text(output);

			if ( timeleft <= 0 ){
				$oThis.parent().parent().find('.cooldown-wait').addClass('hidden');
				$oThis.parent().parent().find('.cooldown-again').removeClass('hidden');

				clearInterval(cooldownTimer);
			}
		}, 1000);
	});
};