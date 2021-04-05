nebula.videos = {};

//Initialize Video Functionality and Tracking
nebula.initVideoTracking = async function(){
	nebula.videos = nebula.videos || {}; //This is likely the first time this gets defined

	nebula.HTML5VideoTracking();
	nebula.youtubeTracking();
	nebula.vimeoTracking();
};

//Track lazy-loaded videos
//Note: element can be anything! Don't assume it is a video.
nebula.lazyVideoTracking = function(element){
	//Re-kick the API for lazy-loaded Youtube and Vimeo videos, and enable tracking for lazy-loaded HTML5 videos.
	if ( element.is('iframe[src*="youtube"]') ){
		nebula.addYoutubePlayer(element.attr('id'), element);
	} else if ( element.is('iframe[src*="vimeo"]') ){
		nebula.createVimeoPlayers();
	} else if ( element.is('video') ){
		nebula.addHTML5VideoPlayer(element.attr('id'), element);
	}
};

//Native HTML5 Videos
nebula.HTML5VideoTracking = function(){
	jQuery('video').each(function(){
		let id = jQuery(this).attr('id'); //An ID is required so HTML5 videos can be properly identified by Nebula and child themes

		if ( typeof nebula.videos[id] === 'object' ){ //If this video is already being tracked ignore it
			return false;
		}

		nebula.addHTML5VideoPlayer(id, jQuery(this));
	});
};

nebula.addHTML5VideoPlayer = function(id, element){
	let videoTitle = element.attr('title') || id || false;
	if ( !videoTitle ){ //An ID or title is required to track HTML5 videos
		return false;
	}

	nebula.videos = nebula.videos || {}; //Always make sure this is defined

	nebula.videos[id] = {
		platform: 'html5', //The platform the video is hosted using.
		player: id, //The player ID of this video. Can access the API here.
		title: videoTitle,
		id: id,
		element: element,
		autoplay: ( element.attr('autoplay') )? true : false,
		percent: 0, //The decimal percent of the current position. Multiply by 100 for actual percent.
		seeker: false, //Whether the viewer has seeked through the video at least once.
		seen: [], //An array of percentages seen by the viewer. This is to roughly estimate how much was watched.
		watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to 1% of video duration. Units: Seconds
		watchedPercent: 0, //The decimal percent of the video watched. Multiply by 100 for actual percent.
		pausedYet: false, //If this video has been paused yet by the user.
		current: 0 //The current position of the video. Units: Seconds
	};

	element.on('loadedmetadata', function(){
		nebula.videos[id].current = this.currentTime;
		nebula.videos[id].duration = this.duration; //The total duration of the video. Units: Seconds
	});

	element.on('play', function(){
		let thisVideo = nebula.videos[id];

		if ( 'mediaSession' in navigator && element.attr('title') ){ //Android Chrome 55+ only
			navigator.mediaSession.metadata = new MediaMetadata({
				title: element.attr('title'),
				artist: element.attr('artist') || '',
				album: element.attr('album') || '',
/*
				artwork: [{
					src: 'https://dummyimage.com/512x512',
					sizes: '512x512',
					type: 'image/png'
				}]
*/
			});
		}

		element.addClass('playing');

		//Only report to GA for non-autoplay videos
		if ( !element.is('[autoplay]') ){
			let thisEvent = {
				category: 'Videos',
				action: ( nebula.isInView(element) )? 'Play' : 'Play (Not In View)',
				title: thisVideo.title,
				autoplay: thisVideo.autoplay
			};

			ga('set', nebula.analytics.metrics.videoStarts, 1);
			ga('set', nebula.analytics.dimensions.videoWatcher, 'Started');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': thisEvent.autoplay});
			if ( !thisVideo.autoplay ){
				nebula.crm('event', 'Video Play Began: ' + thisVideo.title);
			}
		}

		nebula.dom.document.trigger('nebula_playing_video', thisVideo);
	});

	element.on('timeupdate', function(){
		let thisVideo = nebula.videos[id];

		thisVideo.current = this.currentTime; //@todo "Nebula" 0: Still getting NaN on HTML5 autoplay videos sometimes. I think the video begins playing before the metadata is ready...
		thisVideo.percent = thisVideo.current*100/thisVideo.duration; //Determine watched percent by adding current percents to an array, then count the array!
		let nowSeen = Math.ceil(thisVideo.percent);
		if ( thisVideo.seen.indexOf(nowSeen) < 0 ){
			thisVideo.seen.push(nowSeen);
		}

		thisVideo.watchedPercent = thisVideo.seen.length;
		thisVideo.watched = (thisVideo.seen.length/100)*thisVideo.duration; //Roughly calculate time watched based on percent seen

		if ( thisVideo.watchedPercent > 25 && !thisVideo.engaged ){
			if ( nebula.isInView(element) ){
				let thisEvent = {
					category: 'Videos',
					action: ( thisVideo.autoplay )? 'Engaged' : 'Engaged (Autoplay)',
					title: thisVideo.title,
					autoplay: thisVideo.autoplay
				};

				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
				nebula.crm('event', 'Video Engagement: ' + thisEvent.title);
				thisVideo.engaged = true;
				nebula.dom.document.trigger('nebula_engaged_video', thisVideo);
			}
		}
	});

	element.on('pause', function(){
		let thisVideo = nebula.videos[id];
		element.removeClass('playing');

		let thisEvent = {
			category: 'Videos',
			action: 'Paused',
			playTime: Math.round(thisVideo.watched),
			percent: Math.round(thisVideo.percent*100),
			progress:  Math.round(thisVideo.current*1000),
			title: thisVideo.title,
			autoplay: thisVideo.autoplay
		};

		ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
		ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
		ga('set', nebula.analytics.dimensions.videoPercentage, thisEvent.percent);

		if ( !thisVideo.pausedYet ){
			ga('send', 'event', thisEvent.category, 'First Pause', thisEvent.title);
			thisVideo.pausedYet = true;
		}

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
		ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
		nebula.crm('event', 'Video Paused: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_paused_video', thisVideo);
	});

	element.on('seeked', function(){
		let thisVideo = nebula.videos[id];

		if ( thisVideo.current == 0 && element.is('[loop]') ){ //If the video is set to loop and is starting again
			//If it is an autoplay video without controls, don't log loops
			if ( element.is('[autoplay]') && !element.is('[controls]') ){
				return false;
			}

			let thisEvent = {
				category: 'Videos',
				action: ( nebula.isInView(element) )? 'Ended (Looped)' : 'Ended (Looped) (Not In View)',
				title: thisVideo.title,
				autoplay: thisVideo.autoplay
			};

			if ( thisVideo.autoplay ){
				thisEvent.action += ' (Autoplay)';
			}

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
		} else { //Otherwise, the user seeked
			nebula.debounce(function(){
				let thisEvent = {
					category: 'Videos',
					action: 'Seek',
					position: thisVideo.current.toFixed(0),
					title: thisVideo.title,
					autoplay: thisVideo.autoplay
				};

				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title + ' [to: ' + thisEvent.position + ']');
				nebula.crm('event', 'Video Seek: ' + thisEvent.title);
				thisVideo.seeker = true;
				nebula.dom.document.trigger('nebula_seeked_video', thisVideo);
			}, 250, 'video seeking');
		}
	});

	element.on('volumechange', function(){
		let thisVideo = nebula.videos[id];
		//console.debug(this);
	});

	element.on('ended', function(){
		let thisVideo = nebula.videos[id];
		element.removeClass('playing');

		let thisEvent = {
			category: 'Videos',
			action: ( nebula.isInView(element) )? 'Ended' : 'Ended (Not In View)',
			title: thisVideo.title,
			playTime: Math.round(thisVideo.watched),
			progress: Math.round(thisVideo.current*1000),
			autoplay: thisVideo.autoplay
		};

		if ( thisVideo.autoplay ){
			thisEvent.action += ' (Autoplay)';
		}

		ga('set', nebula.analytics.metrics.videoCompletions, 1);
		ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
		ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
		ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
		nebula.crm('event', 'Video Ended: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_ended_video', thisVideo);
	});
};


//Prepare Youtube Iframe API
nebula.youtubeTracking = function(){
	nebula.once(function(){
		if ( jQuery('iframe[src*="youtube"], .lazy-youtube').length ){
			//Load the Youtube iframe API script
			let tag = document.createElement('script');
			tag.src = 'https://www.youtube.com/iframe_api';
			let firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		}
	}, 'nebula youtube api');
};

window.onYouTubeIframeAPIReady = function(e){ //Not scoped to the nebula object because the Youtube API calls this itself
	window.performance.mark('(Nebula) Loading Youtube Videos [Start]');
	jQuery('iframe[src*="youtube"]').each(function(){
		if ( !jQuery(this).hasClass('ignore') ){ //Use this class to ignore certain videos from tracking
			//If this iframe is using a data-src, make sure the src matches
			if ( !jQuery(this).attr('src').includes('youtube') ){ //If the src does not contain "youtube"
				jQuery(this).attr('src', jQuery(this).attr('data-src')); //Update the src to match the data-src attribute. Note: I cannot think of a better way to do this that actually works with the Youtube Iframe API
			}

			let id = jQuery(this).attr('id');
			if ( !id ){
				id = jQuery(this).attr('src').split('?')[0].split('/').pop();
				jQuery(this).attr('id', id);
			}

			if ( jQuery(this).attr('src').includes('enablejsapi=1') ){ //If the iframe src already has the API enabled
				nebula.addYoutubePlayer(id, jQuery(this));
				nebula.dom.document.trigger('nebula_youtube_players_created', nebula.videos[id]);
			} else {
				console.warn('The enablejsapi parameter was not found for this Youtube iframe. It has been reloaded to enable it. For better optimization, and more accurate analytics, add it to the iframe.');

				//JS API not enabled for this video. Reload the iframe with the correct parameter.
				let delimiter = ( jQuery(this).attr('src').includes('?') )? '&' : '?';
				jQuery(this).attr('src', jQuery(this).attr('src') + delimiter + 'enablejsapi=1').on('load', function(){
					nebula.addYoutubePlayer(id, jQuery(this));
					nebula.dom.document.trigger('nebula_youtube_players_created', nebula.videos[id]);
				});
			}
		}
	});
	window.performance.mark('(Nebula) Loading Youtube Videos [End]');
	window.performance.measure('(Nebula) Loading Youtube Videos', '(Nebula) Loading Youtube Videos [Start]', '(Nebula) Loading Youtube Videos [End]');

	let pauseFlag = false;
};

nebula.addYoutubePlayer = function(id = false, element){
	if ( !id ){
		return false; //A Youtube ID is required to add player
	}

	nebula.videos = nebula.videos || {}; //Always make sure this is defined

	if ( typeof YT !== 'undefined' ){
		nebula.videos[id] = {
			player: new YT.Player(id, { //YT.Player parameter must match the iframe ID!
				events: { //If these events are only showing up as "true", try removing the &origin= parameter from the Youtube iframe src.
					'onReady': nebula.youtubeReady,
					'onStateChange': nebula.youtubeStateChange,
					'onError': nebula.youtubeError
				}
			}),
			platform: 'youtube', //The platform the video is hosted using.
			element: element, //The player iframe.
			autoplay: element.attr('src').includes('autoplay=1'), //Look for the autoplay parameter in the iframe src.
			id: id,
			engaged: false, //Whether the viewer has watched enough of the video to be considered engaged.
			watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to half a second. Units: Seconds
			watchedPercent: 0, //The decimal percentage of the video watched. Multiply by 100 for actual percent.
			pausedYet: 0, //If this video has been paused yet by the user.
		};
	}
};

nebula.youtubeReady = function(e){
	if ( typeof videoProgress === 'undefined' ){
		let videoProgress = {};
	}

	nebula.videos = nebula.videos || {}; //Always make sure this is defined

	let id = nebula.getYoutubeID(e.target);
	if ( id && !nebula.videos.hasOwnProperty(id) ){ //If the video object doesn't use the Youtube video ID, make a new one by duplicating from the Iframe ID
		nebula.videos[id] = nebula.videos[jQuery(e.target.getIframe()).attr('id')];
	}

	nebula.videos[id].title = nebula.getYoutubeTitle(e.target) ?? 'Unkown';
	nebula.videos[id].duration = e.target.getDuration(); //The total duration of the video. Unit: Seconds
	nebula.videos[id].current = e.target.getCurrentTime(); //The current position of the video. Units: Seconds
	nebula.videos[id].percent = e.target.getCurrentTime()/e.target.getDuration(); //The percent of the current position. Multiply by 100 for actual percent.
};

nebula.youtubeStateChange = function(e){
	let thisVideo = nebula.videos[nebula.getYoutubeID(e.target)];
	thisVideo.title = nebula.getYoutubeTitle(e.target) ?? 'Unknown';

	//Playing
	if ( e.data === YT.PlayerState.PLAYING ){
		let thisEvent = {
			category: 'Videos',
			action: ( nebula.isInView(jQuery(thisVideo.element)) )? 'Play' : 'Play (Not In View)',
			title: thisVideo.title,
			autoplay: thisVideo.autoplay
		};

		ga('set', nebula.analytics.metrics.videoStarts, 1);
		ga('set', nebula.analytics.dimensions.videoWatcher, 'Started');

		if ( thisVideo.autoplay ){
			thisEvent.action += ' (Autoplay)';
		} else {
			jQuery(thisVideo.element).addClass('playing');
		}

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
		nebula.crm('event', 'Video Play Began: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_playing_video', thisVideo);
		let pauseFlag = true;
		let updateInterval = 500;

		try {
			thisVideo.current = e.target.getCurrentTime();
			thisVideo.percent = thisVideo.current/thisVideo.duration;

			let youtubePlayProgress = setInterval(function(){
				thisVideo.current = e.target.getCurrentTime();
				thisVideo.percent = thisVideo.current/thisVideo.duration;
				thisVideo.watched = thisVideo.watched+(updateInterval/1000);
				thisVideo.watchedPercent = (thisVideo.watched)/thisVideo.duration;

				if ( thisVideo.watchedPercent > 0.25 && !thisVideo.engaged ){
					if ( nebula.isInView(jQuery(thisVideo.element)) ){
						let thisEvent = {
							category: 'Videos',
							action: ( thisVideo.autoplay )? 'Engaged' : 'Engaged (Autoplay)',
							title: thisVideo.title,
							autoplay: thisVideo.autoplay
						};

						ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
						nebula.dom.document.trigger('nebula_event', thisEvent); //@todo "Nebula" 0: This needs the new nebula_event trigger with thisEvent object
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
						nebula.crm('event', 'Video Engaged: ' + thisEvent.title);
						thisVideo.engaged = true;
						nebula.dom.document.trigger('nebula_engaged_video', thisVideo);
					}
				}
			}, updateInterval);
		} catch {
			//Ignore errors
		}
	}

	//Ended
	if ( e.data === YT.PlayerState.ENDED ){
		jQuery(thisVideo.element).removeClass('playing');
		clearInterval(youtubePlayProgress);

		let thisEvent = {
			category: 'Videos',
			action: ( nebula.isInView(jQuery(thisVideo.element)) )? 'Ended' : 'Ended (Not In View)',
			title: thisVideo.title,
			playTime: Math.round(thisVideo.watched/1000),
			progress: thisVideo.current*1000,
			autoplay: thisVideo.autoplay
		};

		if ( thisVideo.autoplay ){
			thisEvent.action += ' (Autoplay)';
		}

		ga('set', nebula.analytics.metrics.videoCompletions, 1);
		ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
		ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
		ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
		nebula.crm('event', 'Video Ended: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_ended_video', thisVideo);

	//Paused
	} else {
		setTimeout(function(){ //Wait 1 second because seeking will always pause and automatically resume, so check if it is still playing a second from now
			try {
				if ( e.target.getPlayerState() == 2 && pauseFlag ){ //This must use getPlayerState() since e.data is not actually "current" inside of this setTimeout(). Paused = 2
					jQuery(thisVideo.element).removeClass('playing');
					clearInterval(youtubePlayProgress);

					let thisEvent = {
						category: 'Videos',
						action: 'Paused',
						playTime: Math.round(thisVideo.watched),
						percent: Math.round(thisVideo.percent*100),
						progress:  thisVideo.current*1000,
						title: thisVideo.title,
						autoplay: thisVideo.autoplay
					};

					ga('set', nebula.analytics.metrics.videoPlaytime, Math.round(thisVideo.watched));
					ga('set', nebula.analytics.dimensions.videoPercentage, Math.round(thisVideo.percent*100));
					ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

					if ( !thisVideo.pausedYet ){
						ga('send', 'event', thisEvent.category, 'First Pause', thisEvent.title);
						thisVideo.pausedYet = true;
					}

					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
					ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
					nebula.crm('event', 'Video Paused: ' + thisEvent.title);
					nebula.dom.document.trigger('nebula_paused_video', thisVideo);
					pauseFlag = false;
				}
			} catch {
				//Ignore errors
			}
		}, 1000);
	}
}

nebula.youtubeError = function(error){
	ga('send', 'exception', {'exDescription': '(JS) Youtube API error: ' + error.data, 'exFatal': false});
	nebula.crm('event', 'Youtube API Error');
}

//Get the ID of the Youtube video (or use best fallback possible)
nebula.getYoutubeID = function(target){
	let id;

	//If getVideoData is available in the API
	if ( target.getVideoData ){
		id = target.getVideoData().id || target.getVideoData().video_id;
	}

	//Make sure the ID was available within the getVideoData() otherwise use alternate methods
	if ( !id ){
		if ( target.getDebugText ){
			id = JSON.parse(target.getDebugText()).debug_videoId;
		} else {
			if ( typeof target.getVideoUrl === 'function' ){
				id = nebula.get('v', target.getVideoUrl()); //Parse the video URL for the ID or use the iframe ID
			} else {
				id = jQuery(target.getIframe()).attr('src').split('?')[0].split('/').pop() || jQuery(target.getIframe()).attr('id'); //Parse the video URL for the ID or use the iframe ID
			}

		}
	}

	return id;
};

//Get the title of a Youtube video (or use best fallback possible)
nebula.getYoutubeTitle = function(target){
	//If getVideoData is available in the API
	if ( target.getVideoData ){
		return target.getVideoData().title;
	}

	//Otherwise use the iframe title attribute (if it exists)
	if ( jQuery(target.getIframe()).attr('title') ){
		return jQuery(target.getIframe()).attr('title').trim();
	}

	//Otherwise use the Youtube ID instead
	let youtubeID = nebula.getYoutubeID(target);
	if ( youtubeID ){
		return youtubeID;
	}

	return false;
};

//Prepare Vimeo API
nebula.vimeoTracking = function(){
	//Load the Vimeo API script (player.js) remotely (with local backup)
	if ( jQuery('iframe[src*="vimeo"], .lazy-vimeo').length ){
		nebula.loadJS(nebula.site.resources.scripts.nebula_vimeo, 'vimeo').then(function(){
			nebula.createVimeoPlayers();
		});
	}
};

//To trigger events on these videos, use the syntax: nebula.videos['208432684'].player.play();
nebula.createVimeoPlayers = function(){
	jQuery('iframe[src*="vimeo"]').each(function(){ //This is not finding lazy loaded videos
		if ( !jQuery(this).hasClass('ignore') ){ //Use this class to ignore certain videos from tracking
			let id = jQuery(this).attr('data-video-id') || jQuery(this).attr('data-vimeo-id') || jQuery(this).attr('id') || false;
			if ( !id ){
				if ( jQuery(this).attr('src').includes('player_id') ){
					id = jQuery(this).attr('src').split('player_id=').pop().split('&')[0]; //Use the player_id parameter. Note: This is no longer used by the Vimeo API!
				} else {
					id = jQuery(this).attr('src').split('/').pop().split('?')[0]; //Grab the ID off the end of the URL (ignoring query parameters)
				}

				if ( id && !parseInt(id) ){ //If the ID is a not number try to find a number in the iframe src
					id = (/\d{6,}/g).exec(jQuery(this).attr('src'))[0];
				}

				jQuery(this).attr('id', id);
			}

			nebula.videos = nebula.videos || {}; //Always make sure this is defined

			if ( typeof nebula.videos[id] === 'object' ){ //If this video is already being tracked ignore it
				return; //Continue the loop
			}

			//Fill in the data object here
			nebula.videos[id] = {
				player: new Vimeo.Player(jQuery(this)),
				element: jQuery(this),
				platform: 'vimeo', //The platform the video is hosted using.
				autoplay: jQuery(this).attr('src').includes('autoplay=1'), //Look for the autoplay parameter in the iframe src.
				id: id,
				current: 0, //The current position of the video. Units: Seconds
				percent: 0, //The percent of the current position. Multiply by 100 for actual percent.
				engaged: false, //Whether the viewer has watched enough of the video to be considered engaged.
				seeker: false, //Whether the viewer has seeked through the video at least once.
				seen: [], //An array of percentages seen by the viewer. This is to roughly estimate how much was watched.
				watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to 1% of video duration. Units: Seconds
				watchedPercent: 0, //The decimal percentage of the video watched. Multiply by 100 for actual percent.
				pausedYet: false, //If this video has been paused yet by the user.
			};

			//Title
			nebula.videos[id].player.getVideoTitle().then(function(title){
				nebula.videos[id].title = title; //The title of the video
			});

			//Duration
			nebula.videos[id].player.getDuration().then(function(duration){
				nebula.videos[id].duration = duration; //The total duration of the video. Units: Seconds
			});

			//Play
			nebula.videos[id].player.on('play', function(e){
				let thisEvent = {
					category: 'Videos',
					action: ( nebula.isInView(jQuery(nebula.videos[id].element)) )? 'Play' : 'Play (Not In View)',
					title: nebula.videos[id].title,
					autoplay: nebula.videos[id].autoplay
				};

				ga('set', nebula.analytics.metrics.videoStarts, 1);
				ga('set', nebula.analytics.dimensions.videoWatcher, 'Started');

				if ( nebula.videos[id].autoplay ){
					thisEvent.action += ' (Autoplay)';
				} else {
					nebula.videos[id].element.addClass('playing');
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
				nebula.crm('event', 'Video Play Began: ' + thisEvent.title);
				nebula.dom.document.trigger('nebula_playing_video', nebula.videos[id].title);
			});

			//Time Update
			nebula.videos[id].player.on('timeupdate', function(e){
				nebula.videos[id].duration = e.duration;
				nebula.videos[id].current = e.seconds;
				nebula.videos[id].percent = e.percent;

				//Determine watched percent by adding current percents to an array, then count the array!
				nowSeen = Math.ceil(nebula.videos[id].percent*100);
				if ( nebula.videos[id].seen.indexOf(nowSeen) < 0 ){
					nebula.videos[id].seen.push(nowSeen);
				}
				nebula.videos[id].watchedPercent = nebula.videos[id].seen.length;
				nebula.videos[id].watched = (nebula.videos[id].seen.length/100)*nebula.videos[id].duration; //Roughly calculate time watched based on percent seen

				if ( nebula.videos[id].watchedPercent > 25 && !nebula.videos[id].engaged ){
					if ( nebula.isInView(jQuery(nebula.videos[id].element)) ){
						let thisEvent = {
							category: 'Videos',
							action: ( nebula.videos[id].autoplay )? 'Engaged' : 'Engaged (Autoplay)',
							title: nebula.videos[id].title,
							autoplay: nebula.videos[id].autoplay
						};

						ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
						nebula.dom.document.trigger('nebula_event', thisEvent);
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
						nebula.crm('event', 'Video Engaged: ' + thisEvent.title);
						nebula.videos[id].engaged = true;
						nebula.dom.document.trigger('nebula_engaged_video', nebula.videos[id].title);
					}
				}
			});

			//Pause
			nebula.videos[id].player.on('pause', function(e){
				jQuery(this).removeClass('playing');

				let thisEvent = {
					category: 'Videos',
					action: 'Paused',
					playTime: Math.round(nebula.videos[id].watched),
					percent: Math.round(e.percent*100),
					title: nebula.videos[id].title,
					autoplay: nebula.videos[id].autoplay
				};

				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
				ga('set', nebula.analytics.dimensions.videoPercentage, thisEvent.percent);

				if ( !nebula.videos[id].pausedYet && !nebula.videos[id].seeker ){ //Only capture first pause if they didn't seek
					ga('send', 'event', thisEvent.category, 'First Pause', thisEvent.title);
					nebula.videos[id].pausedYet = true;
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
				ga('send', 'timing', thisEvent.category, thisEvent.action, Math.round(e.seconds*1000), thisEvent.title);
				nebula.crm('event', 'Video Paused: ' + thisEvent.title);
				nebula.dom.document.trigger('nebula_paused_video', nebula.videos[id]);
			});

			//Seeked
			nebula.videos[id].player.on('seeked', function(e){
				let thisEvent = {
					category: 'Videos',
					action: 'Seek',
					position: e.seconds,
					title: nebula.videos[id].title,
					autoplay: nebula.videos[id].autoplay
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title + ' [to: ' + thisEvent.position + ']');
				nebula.crm('event', 'Video Seeked: ' + thisEvent.title);
				nebula.videos[id].seeker = true;
				nebula.dom.document.trigger('nebula_seeked_video', nebula.videos[id]);
			});

			//Ended
			nebula.videos[id].player.on('ended', function(e){
				jQuery(this).removeClass('playing');

				let thisEvent = {
					category: 'Videos',
					action: ( nebula.isInView(jQuery(nebula.videos[id].element)) )? 'Ended' : 'Ended (Not In View)',
					title: nebula.videos[id].title,
					playTime: Math.round(nebula.videos[id].watched),
					progress: Math.round(nebula.videos[id].watched*1000),
					autoplay: nebula.videos[id].autoplay
				};

				ga('set', nebula.analytics.metrics.videoCompletions, 1);
				ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

				if ( nebula.videos[id].autoplay ){
					thisEvent.action += ' (Autoplay)';
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
				ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title); //Roughly amount of time watched (Can not be over 100% for Vimeo)
				nebula.crm('event', 'Video Ended: ' + thisEvent.title);
				nebula.dom.document.trigger('nebula_ended_video', nebula.videos[id]);
			});

			nebula.dom.document.trigger('nebula_vimeo_player_created', nebula.videos[id]);
		}
	});

	if ( typeof videoProgress === 'undefined' ){
		let videoProgress = {};
	}
};

//Pause all videos
//Use class "ignore-visibility" on iframes to allow specific videos to continue playing regardless of page visibility
//Pass force as true to pause no matter what.
nebula.pauseAllVideos = function(force = false){
	if ( typeof nebula.videos === 'undefined' ){
		return false; //If videos don't exist, then no need to pause
	}

	jQuery.each(nebula.videos, function(){
		if ( this.platform === 'html5' ){
			if ( (force || !jQuery(this.element).hasClass('ignore-visibility')) ){
				jQuery(this.element)[0].pause(); //Pause HTML5 Videos
			}
		}

		if ( this.platform === 'youtube' ){
			if ( (force || !jQuery(this.element).hasClass('ignore-visibility')) ){
				this.player.pauseVideo(); //Pause Youtube Videos
			}
		}

		if ( this.platform === 'vimeo' ){
			if ( (force || !jQuery(this.element).hasClass('ignore-visibility')) ){
				this.player.pause(); //Pause Vimeo Videos
			}
		}
	});
};