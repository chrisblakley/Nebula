(function($) {
$(function() {		
	JQTWEET = {
	    // Set twitter hash/user, number of tweets & id/class to append tweets
	    // You need to clear tweet-date.txt before toggle between hash and user
	    // for multiple hashtags, you can separate the hashtag with OR, eg:
	    // hash: '%23jquery OR %23css'			    
	    search: '', //leave this blank if you want to show user's tweet
	    user: 'pinckneyhugo', //username
	    numTweets: 200, //number of tweets
	    appendTo: '#jstwitter',
	    useGridalicious: true,
	    template: '<div class="item">{IMG}<br />{AVA}<div class="tweet-wrapper"><span class="text">{TEXT}</span>\
	               <span class="time"><a href="{URL}" target="_blank">{AGO}</a></span>\
	               by <span class="user">{USER}</span></div></div>',
	    // core function of jqtweet
	    // https://dev.twitter.com/docs/using-search
	    loadTweets: function() {
	        var request;
	        // different JSON request {hash|user}
	        if (JQTWEET.search) {
	            request = {
	                q: JQTWEET.search,
	                count: JQTWEET.numTweets,
	                api: 'search_tweets'
	            }
	        } else {
	            request = {
	                q: JQTWEET.user,
	                count: JQTWEET.numTweets,
	                //callback: 'readyTweets',
	                api: 'statuses_userTimeline'
	            }
	        }
	        //console.debug(request);
			$.ajax({
				url: 'http://twitter.pinckneyhugo.net/twitter-oauth/grabtweets.php',
				//url: bloginfo['template_directory'] + '/includes/grabtweets.php',
				type: 'GET',
				dataType: 'jsonp',
				contentType: 'application/json',
				data: request,
				success: function(data, textStatus, xhr) {
					if (data.httpstatus == 200) {
						JQTWEET.renderTweets(data, textStatus, xhr);
					} else {
						console.warn('twitter.js: No data returned!');
					}
				}
			});
		}, 
	     
	    /**
	      * relative time calculator FROM TWITTER
	      * @param {string} twitter date string returned from Twitter API
	      * @return {string} relative time like "2 minutes ago"
	      */
	    timeAgo: function(dateString) {
	        var rightNow = new Date();
	        var then = new Date(dateString);
	         
	        if ($.browser.msie) {
	            // IE can't parse these crazy Ruby dates
	            then = Date.parse(dateString.replace(/( \+)/, ' UTC$1'));
	        }
	 
	        var diff = rightNow - then;
	 
	        var second = 1000,
	        minute = second * 60,
	        hour = minute * 60,
	        day = hour * 24,
	        week = day * 7;
	 
	        if (isNaN(diff) || diff < 0) {
	            return ""; // return blank string if unknown
	        }
	 
	        if (diff < second * 2) {
	            // within 2 seconds
	            return "right now";
	        }
	 
	        if (diff < minute) {
	            return Math.floor(diff / second) + " seconds ago";
	        }
	 
	        if (diff < minute * 2) {
	            return "about 1 minute ago";
	        }
	 
	        if (diff < hour) {
	            return Math.floor(diff / minute) + " minutes ago";
	        }
	 
	        if (diff < hour * 2) {
	            return "about 1 hour ago";
	        }
	 
	        if (diff < day) {
	            return  Math.floor(diff / hour) + " hours ago";
	        }
	 
	        if (diff > day && diff < day * 2) {
	            return "yesterday";
	        }
	 
	        if (diff < day * 365) {
	            return Math.floor(diff / day) + " days ago";
	        }
	 
	        else {
	            return "over a year ago";
	        }
	    }, // timeAgo()
	     
	     
	    /**
	      * The Twitalinkahashifyer!
	      * http://www.dustindiaz.com/basement/ify.html
	      * Eg:
	      * ify.clean('your tweet text');
	      */
	    ify:  {
	      link: function(tweet) {
	        return tweet.replace(/\b(((https*\:\/\/)|www\.)[^\"\']+?)(([!?,.\)]+)?(\s|$))/g, function(link, m1, m2, m3, m4) {
	          var http = m2.match(/w/) ? 'http://' : '';
	          return '<a class="twtr-hyperlink" target="_blank" href="' + http + m1 + '">' + ((m1.length > 25) ? m1.substr(0, 24) + '...' : m1) + '</a>' + m4;
	        });
	      },
	 
	      at: function(tweet) {
	        return tweet.replace(/\B[@]([a-zA-Z0-9_]{1,20})/g, function(m, username) {
	          return '<a target="_blank" class="twtr-atreply" href="http://twitter.com/intent/user?screen_name=' + username + '">@' + username + '</a>';
	        });
	      },
	 
	      list: function(tweet) {
	        return tweet.replace(/\B[@]([a-zA-Z0-9_]{1,20}\/\w+)/g, function(m, userlist) {
	          return '<a target="_blank" class="twtr-atreply" href="http://twitter.com/' + userlist + '">@' + userlist + '</a>';
	        });
	      },
	 
	      hash: function(tweet) {
	        return tweet.replace(/(^|\s+)#(\w+)/gi, function(m, before, hash) {
	          return before + '<a target="_blank" class="twtr-hashtag" href="http://twitter.com/search?q=%23' + hash + '">#' + hash + '</a>';
	        });
	      },
	 
	      clean: function(tweet) {
	        return this.hash(this.at(this.list(this.link(tweet))));
	      }
	    }, // ify

	    renderTweets: function(data, textStatus, xhr){
			if (JQTWEET.search) data = data.statuses;
			var text, name, img;       
			try {
				// append tweets into page
				console.log('number of tweets: ' + JQTWEET.numTweets);
				for (var i = 0; i < JQTWEET.numTweets; i++) {
					console.log('inside for loop: ' + i);
					console.debug(data[i]);
					console.log('number of tweets is still: ' + JQTWEET.numTweets);
					img = '';
					url = 'http://twitter.com/' + data[i].user.screen_name + '/status/' + data[i].id_str;
					baseurl = 'http://twitter.com/' + data[i].user.screen_name;
					followurl = 'https://twitter.com/intent/user?screen_name=' + data[i].user.screen_name;
					ava = '<a href="' + baseurl + '" target="_blank" class="tweet_avatar"><img src="' + data[i].user.profile_image_url + '" /></a>';
					try {
						if (data[i].entities['media']) {
							img = '<a href="' + url + '" target="_blank"><img src="' + data[i].entities['media'][0].media_url + '" /></a>';
						}
					} catch (e) {  
						//no media
						//console.log('catch!');
					}
					
					console.log('about to append to: ' + JQTWEET.appendTo);
					console.log('text is: ' + data[i].text);
					$(JQTWEET.appendTo).append( JQTWEET.template.replace('{TEXT}', JQTWEET.ify.clean(data[i].text) )
						.replace(/{USER}/g, data[i].user.screen_name )
						.replace(/{IMG}/g, img )
						.replace(/{AVA}/g, ava )
						.replace(/{AGO}/g, JQTWEET.timeAgo(data[i].created_at) )
						.replace(/{URL}/g, url )
						.replace(/{BURL}/g, baseurl )
						.replace(/{FOLLOW}/g, followurl )
					);
					console.log('done appending for ' + i);
				}
			} catch (e) {
				//item is less than item count
			}
	    } //renderTweets
	     
	};		
	});
})(jQuery);