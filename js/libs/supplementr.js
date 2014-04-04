/*
Supplementr.js v1.2
Author: Chris Blakley http://gearside.com/supplementr
Updated: August 21, 2013

Supplementr.js is a jQuery plugin that is meant to supplement other libraries such as modernizr.js < http://modernizr.com > and css_browser_selector.js < http://rafael.adm.br/css_browser_selector > by adding additional classes to <html>. Flash support enabled in conjunction with swfobject.js < http://code.google.com/p/swfobject >. Supplementr.js uses jQuery, so it must be called after jQuery!
*/
(function($) {
	$(function() {

		$('html').addClass(navigator.userAgent.match('CrOS') ? 'chromeos' : ''); //Check user agent to detect ChromeOS
		$('html').addClass(navigator.userAgent.match('Trident') && navigator.userAgent.match('rv:11') || navigator.userAgent.match('IE 11') ? 'ie11' : ''); //Check user agent to detect Internet Explorer 11
		$('html').addClass(window.devicePixelRatio != undefined && window.devicePixelRatio != 1 ? 'ratio' + window.devicePixelRatio : ''); //If pixel ratio is detectable, addClass if it is not "1".
		$('html').addClass(navigator.cookieEnabled === true ? 'cookies' : 'no-cookies'); //Check browser for cookie support
		$('html').addClass('color-' + screen.colorDepth + 'bits'); //Detect color depth
		if ( typeof swfobject != 'undefined' ) {
			$('html').addClass(typeof swfobject != 'undefined' && swfobject.getFlashPlayerVersion().major != 0 ? 'flash' : 'no-flash'); //If using swfobject to detect flash, addClass for flash support.
		}
		//Detect videogame consoles
		if ( navigator.userAgent.match('Xbox') ) {
			$('html').addClass('xbox');
		} else if ( navigator.userAgent.match('WiiU') ) {
			$('html').addClass('nintendo wiiu');
		} else if ( navigator.userAgent.match('Wii') ) {
			$('html').addClass('nintendo wii');
		} else if ( navigator.userAgent.match('3DS') ) {
			$('html').addClass('nintendo 3ds');
		} else if ( navigator.userAgent.match('Nintendo') ) {
			$('html').addClass('nintendo');
		} else if ( navigator.userAgent.match('PLAYSTATION 3') ) {
			$('html').addClass('playstation ps3');
		} else if ( navigator.userAgent.match('Playstation') && navigator.userAgent.match('PSP') || navigator.userAgent.match('Portable') ) {
			$('html').addClass('playstation psp');
		}

	});
})(jQuery);