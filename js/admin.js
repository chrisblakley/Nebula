jQuery.noConflict();

jQuery(document).ready(function() {

	//Pull query strings from URL
	queries = new Array();
    var q = document.URL.split('?')[1];
    if ( q != undefined ){
        q = q.split('&');
        for ( var i = 0; i < q.length; i++ ){
            hash = q[i].split('=');
            queries.push(hash[1]);
            queries[hash[0]] = hash[1];
        }
	}

	//Search query strings for the passed parameter
	function GET(query) {
		if ( typeof query === 'undefined' ) {
			return queries;
		}

		if ( typeof queries[query] !== 'undefined' ) {
			return queries[query];
		} else if ( queries.hasOwnProperty(query) ) {
			return query;
		}
		return false;
	}

	if ( GET('killall') || GET('kill') || GET('die') ) {
		throw ' (Manually terminated admin.js)';
	}


	if ( clientinfo['remote_addr'] == '72.43.235.106' ) {
		jQuery('html').addClass('phg');
	}

	jQuery(function() {
	    jQuery("#post textarea").allowTabChar();
	});


	if ( jQuery('body').hasClass('profile-php') ) {
		jQuery('#headshot_button').on('click', function() {
			tb_show('Uploading a new headshot!', 'media-upload.php?referer=profile&amp;type=image&amp;TB_iframe=true&amp;post_id=0', false);
			return false;
		});

		window.send_to_editor = function(html) {
			var image_url = jQuery(html).attr('src');
			jQuery('#headshot_url').val(image_url); //updates our hidden field that will update our author's meta when the form is saved
			tb_remove();
			jQuery('#headshot_preview').html('<img src="' + image_url + '" style="max-width: 100%; max-height: 100%;" />');

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
	}



	if ( !jQuery('li#menu-comments').is(':visible') ) {
		jQuery('#dashboard_right_now .main').append('Comments are disabled <small>(via <a href="themes.php?page=nebula_settings">Nebula Settings</a>)</small>.');
	}

	//Developer Info Metabox
	jQuery(document).on("keyup", "input.findterm", function(){
		jQuery("input.findterm").attr("placeholder", "Search files");
	});

	jQuery(document).on("submit", ".searchfiles", function(e){
		if ( jQuery("input.findterm").val().trim().length >= 3 ) {

			jQuery("#searchprogress").removeClass().addClass("fa fa-spinner fa-fw fa-spin");

			jQuery.ajax({
				type: "POST",
				url: bloginfo['admin_ajax'],
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


	if ( jQuery('.flag').is('*') ) {
		Modernizr.load(bloginfo['template_directory'] + '/css/flags.css');
	}

	//Hide TODO files with only hidden items
	jQuery('.todofilewrap').each(function(){
		if ( jQuery(this).find('.linewrap').length == jQuery(this).find('.hidden_todo').length ) {
			jQuery(this).addClass('hidden_file').css('display', 'none');
		}
	});

	jQuery('.togglehiddentodos').on('click', function(){
		jQuery('.hidden_todo, .hidden_file').toggleClass('show-hidden-todos');
		return false;
	});


	//Detect external links in the TinyMCE link modal (to check the "new window" box).
	linkTargetUsered = 0;
	jQuery(document).on('click keyup', '#link-target-checkbox', function(){
		linkTargetUsered = 1;
	});
	jQuery(document).on('click', '#wp-link-submit, #wp-link-close, #wp-link-backdrop, #wp-link-cancel a', function(){
		linkTargetUsered = 0;
	});
	jQuery(document).on('keyup change focus blur', '#url-field', function(){
		if ( linkTargetUsered == 0 ) {
			currentVal = jQuery(this).val();
			if ( currentVal == 'http' || currentVal == 'http:' || currentVal == 'http:/' || currentVal == 'http://' ) { //@TODO "Nebula" 0: This certainly could be written better.
				jQuery('#link-target-checkbox').prop('checked', false);
			} else if ( (currentVal.indexOf('http') >= 0 || currentVal.indexOf('www') >= 0) && currentVal.indexOf(location.host) < 0 ) { //if (has "http" or www) && NOT our domain
				jQuery('#link-target-checkbox').prop('checked', true);
			} else {
				jQuery('#link-target-checkbox').prop('checked', false);
			}
		}
	});
	jQuery(document).on('click', '#most-recent-results *', function(){
		if ( linkTargetUsered == 0 ) {
			jQuery('#link-target-checkbox').prop('checked', false);
		}
	});



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