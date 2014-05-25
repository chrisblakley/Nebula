#WP-Nebula

A Wordpress theme starting point that utilizes many libraries and custom functions for extremely fast development by acting as a "Living Repository".

##Installation

To install, simply download the .zip, extract its contents, and upload to the /themes directory via FTP.

##Setup

- Activate theme
- Rename theme in style.css
- Move .htaccess to root (or combine with existing)
- Install prompted plugins (as needed)
- General Settings
	- Remove Tagline
	- Timezone "New York" (or appropriate timezone)
	- Week Starts On Sunday
- Reading Settings
	- Front page displays "A static page" > Front page: "Home"
	- Check "Discourage search engines from indexing this site"
- Permalinks Settings
	- Select "Post name"
- Set (at least) Primary Menu (Appearance > Menus)
- logo.svg and logo.png (and edit alt tag in header.php)
- Social sharing thumbnails *(Minimum Size: 560x560px with a 246px tall safezone in the center. Use fb-temp.png as a template. Then delete from header.php!)*
- Windows 8 Tiles *(128x128px "tiny.png", 270x270px "square.png", 558x270px "wide.png", 517x516px "large.png")*
- Google Analytics tracking number/domain (header.php)
- Facebook appID (if applicable) (header.php)
- [Theme development goes here]
- Update screenshot.png
- Search for any remaining @TODO tags
- [Testing](https://docs.google.com/document/d/17nmeSsa-4SSfX8bzWWUXcFDTyz3o2-oSgX4ayCgGm7Y/)
- Uncheck "Discourage search engines from indexing this site" (Reading Settings)
- [Launch/Migration](https://docs.google.com/document/d/1jEaImmelk5bitFdh01WU_vm0WctYfhYEAKNZfwWJY0M/)
- Enable W3 Total Cache


##Recommended Plugins
When activated, WP Nebula will prompt to install the recommended and optional plugins. Following the instructions will install them all (or selected ones) at once. These can also be downloaded from Wordpress.org, or manually installed from the Wordpress Admin under the Plugins > Add New.
- [Admin Menu Tree Page View](http://wordpress.org/plugins/admin-menu-tree-page-view/)
- [Custom Post Type UI](https://wordpress.org/plugins/custom-post-type-ui/)
- [Contact Form 7](http://wordpress.org/plugins/contact-form-7/)
- [Contact Form 7 DB](http://wordpress.org/plugins/contact-form-7-to-database-extension/)
- [Custom Field Suite](http://wordpress.org/plugins/custom-field-suite/) or [Advanced Custom Fields](http://wordpress.org/plugins/advanced-custom-fields/)
- [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/)
- [Reveal IDs](http://wordpress.org/plugins/reveal-ids-for-wp-admin-25/)
- [W3 Total Cache](http://wordpress.org/plugins/w3-total-cache/)
- [WP-PageNavi](http://wordpress.org/plugins/wp-pagenavi/)

##Optional Plugins
- [Custom Facebook Feed](https://wordpress.org/plugins/custom-facebook-feed/)
- [Really Simple CAPTCHA](http://wordpress.org/plugins/really-simple-captcha/)
- [Ultimate TinyMCE](https://wordpress.org/plugins/ultimate-tinymce/)
- [WooCommerce](https://wordpress.org/plugins/woocommerce/)
- [Wordpress SEO by Yoast](http://wordpress.org/plugins/wordpress-seo/)

##Included Libraries
#####HTML/CSS
- [Entypo](http://www.entypo.com/)
- [Font Awesome](http://fortawesome.github.io/Font-Awesome/)
- [HTML5 Boilerplate](http://html5boilerplate.com/)
- [Normalize](http://necolas.github.io/normalize.css/)

#####JavaScript
- [CSS Browser Selector](http://cssbs.altervista.org/css-browser-selector.html)
- [DataTables](https://datatables.net/)
- [Gumby](http://gumbyframework.com/)
- [HTML5 Shiv](https://github.com/aFarkas/html5shiv)
- [jQuery](http://jquery.com/)
- [jQuery Mobile](http://jquerymobile.com/)
- [jQuery UI](http://jqueryui.com/)
- [Masked Input](http://digitalbush.com/projects/masked-input-plugin/)
- [Mmenu](http://mmenu.frebsite.nl/)
- [Modernizr](http://modernizr.com/)
- [Respond](https://github.com/scottjehl/Respond)
- [SWF Object](https://code.google.com/p/swfobject/)

#####PHP
- [Mobile Detect](http://mobiledetect.net/)

##Optional Libraries
#####JavaScript
- [Favico.js](http://lab.ejci.net/favico.js/)
- [Noty](http://ned.im/noty/)
- [HoverIntent](http://cherne.net/brian/resources/jquery.hoverIntent.html)
- [Skrollr](https://github.com/Prinzhorn/skrollr)

#####PHP
- [PHP Mailer](https://github.com/PHPMailer/PHPMailer)

##Included API Integrations
- [Facebook SDK for JavaScript](https://developers.facebook.com/docs/javascript)
- [Google Analytics (analytics.js)](https://developers.google.com/analytics/devguides/collection/analyticsjs/)
- [Google Maps API](https://developers.google.com/maps/)
- [Twitter API](https://dev.twitter.com/docs)
- [Youtube Iframe API](https://developers.google.com/youtube/iframe_api_reference)

##Documentation

##nebula_the_excerpt()

#####Description
This function is a replacement for both the_excerpt() and get_the_excerpt() because it can be called both inside or outside the loop! This function queries the specified excerpt of the requested post and if it is empty, it looks for the content instead. Unlike the_excerpt() and get_the_excerpt(), the "Read More" text and word count can be changed on an individual basis (instead of globally).

#####Usage
```html
<?php echo nebula_the_excerpt( $postID, $more, $length, $hellip ); ?>
```

#####Parameters
**$postID**
(optional) The post ID (integer). Used when outside the loop.
Default: *None*

**$more**
(optional) The linked string for the custom "Continue Reading" text.
Default: *None*

**$length**
(optional) How many words are pulled for the excerpt (integer).
Default: 55

**$hellip**
(optional) Whether to show an ellipses at the end of the excerpt if there are more words than the $length specifies (boolean).
Default: 0

#####Examples
To call nebula_the_excerpt() from inside the loop, or outside the loop (for current post/page)
```html
<?php echo nebula_the_excerpt('Read More &raquo;', 30, 1); ?>
```

To call nebula_the_excerpt() from outside the loop (for a specific post/page)
```html
<?php echo nebula_the_excerpt(572, 'Read More &raquo;', 30, 1); ?>
```

##youtubeMeta()

#####Description
This function pulls the metadata from the passed Youtube video ID. This metadata is stored in a global associative array where things like the video title, author, description, duration, etc. can be accessed.

#####Usage
```html
<?php youtubeMeta( $vidID ); ?>
```

#####Parameters
**$vidID**
(required) The Youtube video ID (string).
Default: *None*

#####Examples
Call the function once, and then use the variables as needed.
```html
<?php youtubeMeta('jtip7Gdcf0Q'); ?>
<article class="youtube video">
	<iframe id="<?php echo $vidMeta['safetitle']; ?>" class="youtubeplayer" width="560" height="315" src="http://www.youtube.com/embed/<?php echo $vidMeta['id']; ?>?wmode=transparent&enablejsapi=1&origin=<?php echo $vidMeta['origin']; ?>" frameborder="0" allowfullscreen=""></iframe>
</article>
```
Note that the automatic Google Analytics tracking requires the class "youtubeplayer" and uses the iframe ID as the title of the video (for the event label).
