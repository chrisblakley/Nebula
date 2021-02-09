//Notify for possible duplicate post slug
nebula.uniqueSlugChecker = function(){
	if ( jQuery('.edit-post-post-link__link-post-name').length ){
		if ( jQuery('.edit-post-post-link__link-post-name').text().match(/(-\d+)\/?$/) ){
			jQuery('a.edit-post-post-link__link').css('color', 'red');
			jQuery('.edit-post-post-link__preview-label').html('<span title="This likely indicates a duplicate post, but will not prevent saving or publishing." style="cursor: help;">Possible duplicate:</span>');
		}
	}
};

//Allow tab character in textareas
nebula.pasteIntoInput = function(el, text){
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
};

nebula.allowTabChar = function(el){
	jQuery(el).keydown(function(e){
		if ( e.which === 9 ){
			nebula.pasteIntoInput(this, '\t');
			return false;
		}
	});

	//For Opera, which only allows suppression of keypress events, not keydown
	jQuery(el).keypress(function(e){
		if ( e.which === 9 ){
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