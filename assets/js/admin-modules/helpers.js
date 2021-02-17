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