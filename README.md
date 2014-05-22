#WP-Nebula

A Wordpress theme starting point that utilizes many libraries and custom functions for extremely fast development.


##Documentation

###nebula_the_excerpt()
This function is a replacement for both the_excerpt() and get_the_excerpt() because it can be called both inside or outside the loop! This function queries the specified excerpt of the requested post and if it is empty, it looks for the content instead. Unlike the_excerpt() and get_the_excerpt(), the "Read More" text and word count can be changed on an individual basis (instead of globally).

#####Usage

```html
<?php nebula_the_excerpt( $postID, $more, $length, $hellip ); ?>
```

#####Parameters

**$postID**
(optional) The post ID (integer). Used when outside the loop.
Default: *None* @TODO: Should this default to the current post/page?

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
To call nebula_the_excerpt() from outside the loop, but for the current post/page
```html
<?php echo nebula_the_excerpt(get_the_ID(), 'Read More &raquo;', 30, 1); ?>
```
