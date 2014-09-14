jQuery.noConflict();

jQuery(document).ready(function() {	
	
	if ( window.iphpp == '72.43.235.106' ) {
		jQuery('html').addClass('phg');
	}
	
	jQuery(function() {
	    jQuery("#post textarea").allowTabChar();
	});	
	
	
	jQuery('#headshot_button').on('click', function() {
		tb_show('Uploading a new headshot!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
		return false;
	});
	
	window.send_to_editor = function(html) {
		var image_url = jQuery(html).attr('src');
		jQuery('#headshot_url').val(image_url); //updates our hidden field that will update our author's meta when the form is saved
		tb_remove();
		jQuery('#headshot_preview').html('<img src="'+image_url+'" style="max-width:100%; max-height:100%;" />');
				
		jQuery('#submit_options_form').trigger('click');
		jQuery('#upload_success').text('Here is a preview of the profile picture you chose.');
		
	}
	
	jQuery('#headshot_remove').on('click', function(){
		jQuery('#headshot_url').val('');
		jQuery('#headshot_preview').remove();
		jQuery('#upload_success').text('Picture removed.');
	});



	jQuery('#avatar_button').on('click', function() {
		tb_show('Uploading a new avatar!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
		return false;
	});
		
	jQuery('#avatar_remove').on('click', function(){
		jQuery('#avatar_url').val('');
		jQuery('#avatar_preview').remove();
		jQuery('#upload_success').text('Picture removed.');
	});
	
	if ( !jQuery('li.comment-count').is(':visible') ) {
		jQuery('#dashboard_right_now .main').append('Comments are disabled <small>(via <a href="themes.php?page=nebula_settings">Nebula Settings</a>)</small>.');
	}
	
	//PHG Metabox
	jQuery(document).on("keyup", "input.findterm", function(){
		jQuery("input.findterm").attr("placeholder", "Search files");
	});
	
	jQuery(document).on("submit", ".searchfiles", function(e){
		if ( jQuery("input.findterm").val().trim().length >= 3 ) {
			
			jQuery("#searchprogress").removeClass().addClass("fa fa-spinner fa-fw fa-spin");
			
			jQuery.ajax({
				type: "POST",
				url: bloginfo['admin-ajax'],
				data: {
					action: "search_theme_files",
					data: [{
						"directory": jQuery("select.searchdirectory").val(),
						"searchData": jQuery("input.findterm").val()
					}]
				},
				success: function(response){
					jQuery("#searchprogress").removeClass().addClass("fa fa-search fa-fw");					
					jQuery('div.search_results').html(response).addClass('done');
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					jQuery("div.search_results").html(errorThrown).addClass('done');
				},
				timeout: 60000
			});
		} else {
			jQuery("input.findterm").val("").attr("placeholder", "Minimum 3 characters.");
		}
		e.preventDefault();
		return false;
	});

	jQuery(document).on("click", ".linenumber", function(){
		jQuery(this).parents('.linewrap').find('.precon').slideToggle();
		return false;
	});	
	
	//Alert confirmation if "Bulk Action" is selected when "Apply" is submitted.
	if ( jQuery('#bulk-action-selector-top').is('*') ) {
		bulkSubmitError = 0;
		jQuery('form').on('submit', function(){		
			if ( jQuery(this).find('#bulk-action-selector-top').val() == '-1' ) {
				if ( bulkSubmitError == 0 ) {
					jQuery(this).find('.bulkactions').append('<strong title="You have not selected an action to apply to the selected items." style="cursor: default; background: #dd3d36; padding: 2px 10px; border-radius: 10px; line-height: 30px; color: #fff;">Select an option before applying!</strong>');
					bulkSubmitError = 1;
				}
				if ( !confirm('You have not selected an action. Proceed anyway?') ) {
				    return false;
				}
			}
		});
	}
	
	if ( jQuery('.flag').is('*') ) {
		Modernizr.load(bloginfo['template_directory'] + '/css/flags.css');
	}
	
	
}); //End Document Ready

jQuery(window).on('load', function() {	

	//Window load functions here.
	
}); //End Window Load


//Allow tab character in textareas
(function($) {
    function pasteIntoInput(el, text) {
        el.focus();
        var val = el.value;
        if (typeof el.selectionStart == "number") {
            var selStart = el.selectionStart;
            el.value = val.slice(0, selStart) + text + val.slice(el.selectionEnd);
            el.selectionEnd = el.selectionStart = selStart + text.length;
        } else if (typeof document.selection != "undefined") {
            var textRange = document.selection.createRange();
            textRange.text = text;
            textRange.collapse(false);
            textRange.select();
        }
    }

    function allowTabChar(el) {
        jQuery(el).keydown(function(e) {
            if (e.which == 9) {
                pasteIntoInput(this, "\t");
                return false;
            }
        });

        // For Opera, which only allows suppression of keypress events, not keydown
        jQuery(el).keypress(function(e) {
            if (e.which == 9) {
                return false;
            }
        });
    }

    $.fn.allowTabChar = function() {
        if (this.jquery) {
            this.each(function() {
                if (this.nodeType == 1) {
                    var nodeName = this.nodeName.toLowerCase();
                    if (nodeName == "textarea" || (nodeName == "input" && this.type == "text")) {
                        allowTabChar(this);
                    }
                }
            })
        }
        return this;
    }
})(jQuery);