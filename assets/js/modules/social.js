window.performance.mark('(Nebula) Inside /modules/social.js');

//Load the SDK asynchronously
nebula.facebookSDK = async function(){
	if ( jQuery('[class*="fb-"]:not(.fb-root), .require-fbsdk').length ){ //Only load the Facebook SDK when needed
		(function(d, s, id){
			var js, fjs = d.getElementsByTagName(s)[0];
			if ( d.getElementById(id) ){
				return;
			}
			js = d.createElement(s);
			js.id = id;
			js.src = 'https://connect.facebook.net/' + nebula.site.charset + '/all.js#xfbml=1&version=v3.0';
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	}
};

//Social sharing buttons
nebula.socialSharing = async function(){
	if ( jQuery('[class*="share"]').length ){
		const encloc = encodeURIComponent(window.location.href);
		const enctitle = encodeURIComponent(document.title);
		let popupTop = nebula.dom.window.height()/2-275;
		let popupLeft = nebula.dom.window.width()/2-225;
		let popupAttrs = 'top=' + popupTop + ', left=' + popupLeft + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0, chrome=yes, personalbar=0';

		//These events will need to correspond to the GA4 event name "share" and use "content_type" and "item_id" as parameters: https://support.google.com/analytics/answer/9267735

		//Facebook
		jQuery('a.facebook-share, .facebook-share a, a.nebula-share.facebook, .nebula-share a.facebook').attr('href', 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			let thisEvent = {
				event: e,
				event_name: 'share',
				event_category: 'Social',
				event_action: 'Share',
				network: 'Facebook',
				url: window.location.href,
				title: document.title
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.event_action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'facebookShareWindow', 'width=550, height=450, ' + popupAttrs);
				return false;
			}
		});

		//Twitter
		jQuery('a.twitter-share, .twitter-share a, a.nebula-share.twitter, .nebula-share a.twitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encloc + '&text=' + enctitle).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			let thisEvent = {
				event: e,
				event_name: 'share',
				event_category: 'Social',
				event_action: 'Share',
				network: 'Twitter',
				url: window.location.href,
				title: document.title
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.event_action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'twitterShareWindow', 'width=600, height=254, ' + popupAttrs);
				return false;
			}
		});

		//LinkedIn
		jQuery('a.linkedin-share, .linkedin-share a, a.nebula-share.linkedin, .nebula-share a.linkedin').attr('href', 'http://www.linkedin.com/shareArticle?mini=true&url=' + encloc + '&title=' + enctitle).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			let thisEvent = {
				event: e,
				event_name: 'share',
				event_category: 'Social',
				event_action: 'Share',
				network: 'LinkedIn',
				url: window.location.href,
				title: document.title
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.event_action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'linkedinShareWindow', 'width=600, height=473, ' + popupAttrs);
				return false;
			}
		});

		//Pinterest
		jQuery('a.pinterest-share, .pinterest-share a, a.nebula-share.pinterest, .nebula-share a.pinterest').attr('href', 'http://pinterest.com/pin/create/button/?url=' + encloc).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			let thisEvent = {
				event: e,
				event_name: 'share',
				event_category: 'Social',
				event_action: 'Share',
				network: 'Pinterest',
				url: window.location.href,
				title: document.title
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.event_action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'pinterestShareWindow', 'width=600, height=450, ' + popupAttrs);
				return false;
			}
		});

		//Email
		jQuery('a.email-share, .email-share a, a.nebula-share.email, .nebula-share a.email').each(function(){
			let emailSubject = jQuery(this).attr('data-subject') || document.title; //Use the page title unless a data attribute for the subject exists (Note that we are not using encoded values here)
			let emailBody = jQuery(this).attr('data-body') || window.location.href; //Use the page URL unless a data attribute for the body exists (Note that we are not using encoded values here)
			jQuery(this).attr('href', 'mailto:?subject=' + encodeURIComponent(emailSubject) + '&body=' + encodeURIComponent(emailBody)).attr({'target': '_blank', 'rel': 'noopener'});
		}).on('click', function(e){
			let thisEvent = {
				event: e,
				event_name: 'share',
				event_category: 'Social',
				event_action: 'Share',
				network: 'Email',
				url: window.location.href,
				title: document.title
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.event_action);
		});

		//Web Share API: https://caniuse.com/mdn-api_navigator_share
		if ( 'share' in navigator ){ //Chrome 61+
			nebula.dom.document.on('click', 'a.shareapi, .shareapi a, a.nebula-share.shareapi, .nebula-share a.shareapi', function(e){
				let thisEvent = {
					event: e,
					event_name: 'share',
					event_category: 'Social',
					event_action: 'Share',
					network: 'Share API (Drawer Opened)',
					url: window.location.href,
					title: document.title,
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));

				let $oThis = jQuery(this);
				let originalText = $oThis.html();

				navigator.share({
					title: document.title,
					text: nebula.post.excerpt,
					url: window.location.href
				}).then(function(){
					let thisEvent = {
						event: e,
						event_name: 'share',
						event_category: 'Social',
						event_action: 'Share',
						network: 'Share API (Success)',
						url: window.location.href,
						title: document.title,
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					nebula.crm('event', thisEvent.network);
					$oThis.addClass('success');
					nebula.createCookie('shareapi', true);
				}).catch(function(error){ //This can happen on iOS when the user closes the drawer without sharing
					gtag('event', 'exception', {
						message: '(JS) Share API Error: ' + error,
						fatal: false
					});
					$oThis.addClass('error').html(originalText);
					nebula.createCookie('shareapi', false);
				});

				return false;
			});

			nebula.createCookie('shareapi', true); //Set a cookie to speed up future page loads by not loading third-party share buttons.
		} else {
			jQuery('a.shareapi, .shareapi a, a.nebula-share.shareapi, .nebula-share a.shareapi').addClass('hidden');
		}
	}
};