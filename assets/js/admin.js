jQuery.noConflict();
jQuery(document).on('ready', function(){

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
		jQuery('#dashboard_right_now .main').append('Comments are disabled <small>(via <a href="themes.php?page=nebula_options">Nebula Options</a>)</small>.');
	}

	businessHoursCheck();
	jQuery('.businessday input[type="checkbox"]').on('click tap touch', function(){
		businessHoursCheck();
	});

}); //End Document Ready

//Developer Metaboxe functions
function developerMetaboxes(){
	//Developer Info Metabox
	jQuery(document).on('keyup', 'input.findterm', function(){
		jQuery('input.findterm').attr('placeholder', 'Search files');
	});

	jQuery(document).on('submit', '.searchfiles', function(e){
		if ( jQuery('input.findterm').val().trim().length >= 3 ){
			jQuery('#searchprogress').removeClass().addClass('fa fa-spinner fa-fw fa-spin');

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
					jQuery('#searchprogress').removeClass().addClass('fa fa-search fa-fw');
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
	jQuery(document).on('click tap touch keydown', '#wp-link-target', function(){
		linkTargetUsered = 1;
	});
	jQuery(document).on('click tap touch', '#wp-link-submit, #wp-link-close, #wp-link-backdrop, #wp-link-cancel a', function(){ //If clicking the submit button, the close "x", the modal background, or the cancel link.
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
	jQuery(document).on('click tap touch', '#most-recent-results *, #search-results *', function(){
		if ( linkTargetUsered === 0 ){
			jQuery('#wp-link-target').prop('checked', false);
		}
	});
}

//Initialization alerts
function initializationStuff(){
	//Re-initialize confirm dialog.
	jQuery('.reinitializenebula').on('click tap touch', function(){
		if ( !confirm('This will reset all Nebula options and reset the homepage content! Are you sure you want to re-initialize?') ) {
			return false;
		}
	});

	//Initialize confirm dialog.
	jQuery('#run-nebula-initialization').on('click tap touch', function(){
		if ( !confirm('This will reset some WordPress settings, all Nebula options, and reset the homepage content! Are you sure you want to initialize?') ) {
			return false;
		} else {
			jQuery('.nebula-activated-description').html('<i class="fa fa-spinner fa-spin"></i> Running initialization...');

			jQuery.ajax({
				type: "POST",
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'nebula_initialization'
				},
				success: function(data){
					if ( data.indexOf('successful-nebula-init') !== -1 ){
						jQuery('.nebula-activated-title').html('<i class="fa fa-check" style="color: green;"></i> Nebula has been initialized!');
						jQuery('.nebula-activated-description').html('Settings have been updated. The home page has been updated and has been set as the static front page in <a href="options-reading.php">Settings > Reading</a>.<br /><strong>Next step:</strong> Configure <a href="themes.php?page=nebula_options">Nebula Options</a>');
						return false;
					} else {
						jQuery('#nebula-activate-success').removeClass('updated').addClass('error');
						jQuery('.nebula-activated-title').html('<i class="fa fa-times" style="color: #dd3d36;"></i> AJAX Initialization Error.');
						jQuery('.nebula-activated-description').html('AJAX initialization has failed. Attempting standard initialization. <strong>This will reload the page in 2 seconds...</strong>');
						setTimeout(function(){
							jQuery('.nebula-activated-title').html('<i class="fa fa-spinner fa-spin" style="color: #dd3d36;"></i> AJAX Initialization Error.');
							window.location = 'themes.php?nebula-initialization=true';
						}, 2000);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					jQuery('#nebula-activate-success').removeClass('updated').addClass('error');
					jQuery('.nebula-activated-title').html('<i class="fa fa-times" style="color: #dd3d36;"></i> AJAX Initialization Error.');
					jQuery('.nebula-activated-description').html('An AJAX error has occurred. Attempting standard initialization. <strong>This will reload the page in 2 seconds...</strong>');
					setTimeout(function(){
						jQuery('.nebula-activated-title').html('<i class="fa fa-spinner fa-spin" style="color: #dd3d36;"></i> AJAX Initialization Error.');
						window.location = 'themes.php?nebula-initialization=true';
					}, 2000);
				},
				timeout: 10000
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
		jQuery('#headshot_button').on('click tap touch', function(){
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

		jQuery('#headshot_remove').on('click tap touch', function(){
			jQuery('#headshot_url').val('');
			jQuery('#headshot_preview').remove();
			jQuery('#upload_success').text('Picture removed.');
		});



		jQuery('#avatar_button').on('click tap touch', function(){
			tb_show('Uploading a new avatar!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
			return false;
		});

		jQuery('#avatar_remove').on('click tap touch', function(){
			jQuery('#avatar_url').val('');
			jQuery('#avatar_preview').remove();
			jQuery('#upload_success').text('Picture removed.');
		});
	}
}

//Check business hours for open checkbox
function businessHoursCheck(){
	jQuery('.businessday input[type="checkbox"]').each(function(){
		if ( jQuery(this).prop('checked') ){
			jQuery(this).parents('.businessday').removeClass('closed');
		} else {
			jQuery(this).parents('.businessday').addClass('closed');
		}
	});
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

//Custom CSS expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: keywordSearch(container, parent, value);
jQuery.expr[":"].Contains=function(e,n,t){return(e.textContent||e.innerText||"").toUpperCase().indexOf(t[3].toUpperCase())>=0};