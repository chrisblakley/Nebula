WP-Nebula
=========

A Wordpress theme starting point that utilizes many libraries and custom functions for extremely fast development.


##Documentation

###nebula_the_excerpt()
This function is a replacement for both the_excerpt() and get_the_excerpt() because it can be called both inside or outside the loop!

#####Examples
To call nebula_the_excerpt() from outside the loop, but for the current post/page
```php
echo nebula_the_excerpt(get_the_ID(), 'Read More &raquo;', 30, 1);
```
