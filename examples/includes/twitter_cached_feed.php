<style>
	.example1 {border: 1px solid #aaa; padding: 10px 15px;}
	.example2 {border: 1px solid #aaa; padding: 10px 15px;}
		.example2 li {margin-bottom: 15px;}

	.twitter-user-photo {float: left; margin: 6px 10px 0 0;}
	.twitter-posted-on {font-size: smaller; color: #888; white-space: nowrap;}
</style>


<script>
	jQuery(document).ready(function() {

		//Example 1
		jQuery.getJSON(bloginfo['template_directory'] + '/includes/twitter_cache.php', function(data) {
			jQuery('#tweet_user_photo1').attr('href', 'https://twitter.com/' + data[0].user.screen_name).append('<img src="' + data[0].user.profile_image_url + '" title="' + data[0].user.description + '" />');
			jQuery('#tweet_user1').attr('href', 'https://twitter.com/' + data[0].user.screen_name).text('@' + data[0].user.screen_name);

			var tweetTime = new Date(Date.parse(data[0].created_at));
			jQuery('#tweet_body1').html(tweetLinks(data[0].text)).append(" <span class='twitter-posted-on'><i class='fa fa-clock-o'></i> " + timeAgo(tweetTime) + "</span>");
		});

		//Example 2
		jQuery.getJSON(bloginfo['template_directory'] + '/includes/twitter_cache.php', function(data) {
			jQuery.each(data, function(i) {
				console.debug(data[i]); //Just to show all the data that is available.

				var tweetTime = new Date(Date.parse(data[i].created_at));
				jQuery('.example2').append('<li><a class="twitter-user-photo" href="https://twitter.com/' + data[i].user.screen_name + '" target="_blank"><img src="' + data[i].user.profile_image_url + '" title="' + data[i].user.description + '" /></a><strong><a href="https://twitter.com/' + data[i].user.screen_name + '" target="_blank">@' + data[i].user.screen_name + '</a></strong><br/><span>' + tweetLinks(data[i].text) + ' <span class="twitter-posted-on"><i class="fa fa-clock-o"></i> ' + timeAgo(tweetTime) + '</span></span></li>');
			});
		});

	});

	function tweetLinks(tweet){
		var newString = tweet.replace(/(http(\S)*)/g, '<a href="' + "$1" + '" target="_blank">' + "$1" + '</a>'); //Links that begin with "http"
		newString = newString.replace(/#(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/hashtag/' + "$1" + '" target="_blank">#' + "$1" + '</a>'); //Link hashtags
		newString = newString.replace(/@(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/' + "$1" + '" target="_blank">@' + "$1" + '</a>'); //Link @username mentions
		return newString;
	}
	function timeAgo(time) { //http://af-design.com/blog/2009/02/10/twitter-like-timestamps/
		var system_date = new Date(time);
		var user_date = new Date();
		var diff = Math.floor((user_date-system_date)/1000);
		if (diff <= 1) return "just now";
		if (diff < 20) return diff + " seconds ago";
		if (diff < 60) return "less than a minute ago";
		if (diff <= 90) return "one minute ago";
		if (diff <= 3540) return Math.round(diff/60) + " minutes ago";
		if (diff <= 5400) return "1 hour ago";
		if (diff <= 86400) return Math.round(diff/3600) + " hours ago";
		if (diff <= 129600) return "1 day ago";
		if (diff < 604800) return Math.round(diff/86400) + " days ago";
		if (diff <= 777600) return "1 week ago";
		return "on " + system_date;
	}
</script>


<div class="row">
	<div class="sixteen columns">

		<div>
			<strong>Example 1</strong><br/>
			<span>Fill pre-existing HTML with tweet data. This is good for displaying a single, latest tweet.</span>
		</div>
		<p class="example1">
			<a id="tweet_user_photo1" class="twitter-user-photo" href="#" target="_blank"></a>
			<strong><a id="tweet_user1" target="_blank" href="#">Loading Tweets...</a></strong><br/>
			<span id="tweet_body1"></span>
		</p>

		<br/>

		<div>
			<strong>Example 2</strong><br/>
			<span>Generate the markup within a UL to display tweets. This method is good for showing multiple tweets.</span>
		</div>
		<ul class="example2"></ul>

	</div><!--/columns-->
</div><!--/row-->