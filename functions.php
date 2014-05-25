<?php
/**
 * Boilerplate functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, boilerplate_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * We can remove the parent theme's hook only after it is attached, which means we need to
 * wait until setting up the child theme:
 *
 * <code>
 * add_action( 'after_setup_theme', 'my_child_theme_setup' );
 * function my_child_theme_setup() {
 *     // We are providing our own filter for excerpt_length (or using the unfiltered value)
 *     remove_filter( 'excerpt_length', 'boilerplate_excerpt_length' );
 *     ...
 * }
 * </code>
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Boilerplate
 * @since Boilerplate 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 640;

if ( ! function_exists( 'boilerplate_setup' ) ):
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which runs
	 * before the init hook. The init hook is too late for some features, such as indicating
	 * support post thumbnails.
	 *
	 * To override boilerplate_setup() in a child theme, add your own boilerplate_setup to your child theme's
	 * functions.php file.
	 *
	 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
	 * @uses register_nav_menus() To add support for navigation menus.
	 * @uses add_custom_background() To add support for a custom background.
	 * @uses add_editor_style() To style the visual editor.
	 * @uses load_theme_textdomain() For translation/localization support.
	 * @uses add_theme_support()/add_custom_image_header() To add support for a custom header.
	 * @uses register_default_headers() To register the default custom header images provided with the theme.
	 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_setup() {

		// This theme styles the visual editor with editor-style.css to match the theme style.
		add_editor_style();

		// Uncomment if you choose to use post thumbnails; add the_post_thumbnail() wherever thumbnail should appear
		//add_theme_support( 'post-thumbnails' );

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Make theme available for translation
		// Translations can be filed in the /languages/ directory
		load_theme_textdomain( 'boilerplate', get_template_directory() . '/languages' );

		$locale = get_locale();
		$locale_file = get_template_directory() . "/languages/$locale.php";
		if ( is_readable( $locale_file ) )
			require_once( $locale_file );

		// This theme allows users to set a custom background
		// add_custom_background was deprecated as of 3.4, so testing for existence, but keeping add_custom_background for backward-compatibility
		if ( function_exists( 'add_theme_support' ) ) {
			add_theme_support( 'custom-background' );
		} else {
			add_custom_background();
		}

		// Your changeable header business starts here
		define( 'HEADER_TEXTCOLOR', '' );
		// No CSS, just IMG call. The %s is a placeholder for the theme template directory URI.
		define( 'HEADER_IMAGE', '%s/images/headers/path.jpg' );

		// The height and width of your custom header. You can hook into the theme's own filters to change these values.
		// Add a filter to boilerplate_header_image_width and boilerplate_header_image_height to change these values.
		define( 'HEADER_IMAGE_WIDTH', apply_filters( 'boilerplate_header_image_width', 940 ) );
		define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'boilerplate_header_image_height', 198 ) );

		// We'll be using post thumbnails for custom header images on posts and pages.
		// We want them to be 940 pixels wide by 198 pixels tall.
		// Larger images will be auto-cropped to fit, smaller ones will be ignored. See header.php.
		set_post_thumbnail_size( HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true );

		// Don't support text inside the header image.
		define( 'NO_HEADER_TEXT', true );

		// Add a way for the custom header to be styled in the admin panel that controls
		// custom headers. See boilerplate_admin_header_style(), below.
		// add_custom_image_header was deprecated as of 3.4, so testing for existence, but keeping add_custom_image_header for backward-compatibility
		if ( function_exists( 'add_theme_support' ) ) {
			add_theme_support( 'custom-header' );

		} else {
			add_custom_image_header( '', 'boilerplate_admin_header_style' );
		}

		// ... and thus ends the changeable header business.

		// Default custom headers packaged with the theme. %s is a placeholder for the theme template directory URI.
		register_default_headers( array(
			'berries' => array(
				'url' => '%s/images/headers/starkers.png',
				'thumbnail_url' => '%s/images/headers/starkers-thumbnail.png',
				/* translators: header image description */
				'description' => __( 'Boilerplate', 'boilerplate' )
			)
		) );
	}
endif;
add_action( 'after_setup_theme', 'boilerplate_setup' );

if ( ! function_exists( 'boilerplate_admin_header_style' ) ) :
	/**
	 * Styles the header image displayed on the Appearance > Header admin panel.
	 *
	 * Referenced via add_theme_support()/add_custom_image_header() in boilerplate_setup().
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_admin_header_style() {
	?>
	<style type="text/css">
	/* Shows the same border as on front end */
	#headimg {
		border-bottom: 1px solid #000;
		border-top: 4px solid #000;
	}
	/* If NO_HEADER_TEXT is false, you would style the text with these selectors:
		#headimg #name { }
		#headimg #desc { }
	*/
	</style>
	<?php
	}
endif;

if ( ! function_exists( 'boilerplate_filter_wp_title' ) ) :
	/**
	 * Makes some changes to the <title> tag, by filtering the output of wp_title().
	 *
	 * If we have a site description and we're viewing the home page or a blog posts
	 * page (when using a static front page), then we will add the site description.
	 *
	 * If we're viewing a search result, then we're going to recreate the title entirely.
	 * We're going to add page numbers to all titles as well, to the middle of a search
	 * result title and the end of all other titles.
	 *
	 * The site title also gets added to all titles.
	 *
	 * @since Twenty Ten 1.0
	 *
	 * @param string $title Title generated by wp_title()
	 * @param string $separator The separator passed to wp_title(). Twenty Ten uses a
	 * 	vertical bar, "|", as a separator in header.php.
	 * @return string The new title, ready for the <title> tag.
	 */
	function boilerplate_filter_wp_title( $title, $separator ) {
		// Don't affect wp_title() calls in feeds.
		if ( is_feed() )
			return $title;

		// The $paged global variable contains the page number of a listing of posts.
		// The $page global variable contains the page number of a single post that is paged.
		// We'll display whichever one applies, if we're not looking at the first page.
		global $paged, $page;

		if ( is_search() ) {
			// If we're a search, let's start over:
			$title = sprintf( __( 'Search results for %s', 'boilerplate' ), '"' . get_search_query() . '"' );
			// Add a page number if we're on page 2 or more:
			if ( $paged >= 2 )
				$title .= " $separator " . sprintf( __( 'Page %s', 'boilerplate' ), $paged );
			// Add the site name to the end:
			$title .= " $separator " . get_bloginfo( 'name', 'display' );
			// We're done. Let's send the new title back to wp_title():
			return $title;
		}

		// Otherwise, let's start by adding the site name to the end:
		$title .= get_bloginfo( 'name', 'display' );

		// If we have a site description and we're on the home/front page, add the description:
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			$title .= " $separator " . $site_description;

		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 )
			$title .= " $separator " . sprintf( __( 'Page %s', 'boilerplate' ), max( $paged, $page ) );

		// Return the new title to wp_title():
		return $title;
	}
endif;
add_filter( 'wp_title', 'boilerplate_filter_wp_title', 10, 2 );

if ( ! function_exists( 'boilerplate_page_menu_args' ) ) :
	/**
	 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
	 *
	 * To override this in a child theme, remove the filter and optionally add
	 * your own function tied to the wp_page_menu_args filter hook.
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_page_menu_args( $args ) {
		$args['show_home'] = true;
		return $args;
	}
endif;
add_filter( 'wp_page_menu_args', 'boilerplate_page_menu_args' );

if ( ! function_exists( 'boilerplate_excerpt_length' ) ) :
	/**
	* Sets the post excerpt length to 40 characters.
	*
	* To override this length in a child theme, remove the filter and add your own
	* function tied to the excerpt_length filter hook.
	*
	* @since Twenty Ten 1.0
	* @return int
	*/
   function boilerplate_excerpt_length( $length ) {
	   return 500;
   }
endif;
add_filter( 'excerpt_length', 'boilerplate_excerpt_length' );

if ( ! function_exists( 'boilerplate_continue_reading_link' ) ) :
	/**
	 * Returns a "Continue Reading" link for excerpts
	 *
	 * @since Twenty Ten 1.0
	 * @return string "Continue Reading" link
	 */
	function boilerplate_continue_reading_link() {
		return /* ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'boilerplate' ) . '</a>' */;
	}
endif;

if ( ! function_exists( 'boilerplate_auto_excerpt_more' ) ) :
	/**
	 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and boilerplate_continue_reading_link().
	 *
	 * To override this in a child theme, remove the filter and add your own
	 * function tied to the excerpt_more filter hook.
	 *
	 * @since Twenty Ten 1.0
	 * @return string An ellipsis
	 */
	function boilerplate_auto_excerpt_more( $more ) {
		return /* ' &hellip;' . boilerplate_continue_reading_link() */;
	}
endif;
add_filter( 'excerpt_more', 'boilerplate_auto_excerpt_more' );

if ( ! function_exists( 'boilerplate_custom_excerpt_more' ) ) :
	/**
	 * Adds a pretty "Continue Reading" link to custom post excerpts.
	 *
	 * To override this link in a child theme, remove the filter and add your own
	 * function tied to the get_the_excerpt filter hook.
	 *
	 * @since Twenty Ten 1.0
	 * @return string Excerpt with a pretty "Continue Reading" link
	 */
	function boilerplate_custom_excerpt_more( $output ) {
		if ( has_excerpt() && ! is_attachment() ) {
			$output .= boilerplate_continue_reading_link();
		}
		return $output;
	}
endif;
add_filter( 'get_the_excerpt', 'boilerplate_custom_excerpt_more' );

if ( ! function_exists( 'boilerplate_remove_gallery_css' ) ) :/**
	/**
	 * Remove inline styles printed when the gallery shortcode is used.
	 *
	 * Galleries are styled by the theme in Twenty Ten's style.css.
	 *
	 * @since Twenty Ten 1.0
	 * @return string The gallery style filter, with the styles themselves removed.
	 */
	function boilerplate_remove_gallery_css( $css ) {
		return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
	}
endif;
add_filter( 'gallery_style', 'boilerplate_remove_gallery_css' );

if ( ! function_exists( 'boilerplate_comment' ) ) :
	/**
	 * Template for comments and pingbacks.
	 *
	 * To override this walker in a child theme without modifying the comments template
	 * simply create your own boilerplate_comment(), and that function will be used instead.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case '' :
		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>">
				<div class="comment-author vcard">
					<?php echo get_avatar( $comment, 40 ); ?>
					<?php printf( __( '%s <span class="says">says:</span>', 'boilerplate' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
				</div><!-- .comment-author .vcard -->
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php _e( 'Your comment is awaiting moderation.', 'boilerplate' ); ?></em>
					<br />
				<?php endif; ?>
				<footer class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '%1$s at %2$s', 'boilerplate' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'boilerplate' ), ' ' );
					?>
				</footer><!-- .comment-meta .commentmetadata -->
				<div class="comment-body"><?php comment_text(); ?></div>
				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->
			</article><!-- #comment-##  -->
		<?php
				break;
			case 'pingback'  :
			case 'trackback' :
		?>
		<li class="post pingback">
			<p><?php _e( 'Pingback:', 'boilerplate' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'boilerplate'), ' ' ); ?></p>
		<?php
				break;
		endswitch;
	}
endif;

if ( ! function_exists( 'boilerplate_widgets_init' ) ) :
	/**
	 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
	 *
	 * To override boilerplate_widgets_init() in a child theme, remove the action hook and add your own
	 * function tied to the init hook.
	 *
	 * @since Twenty Ten 1.0
	 * @uses register_sidebar
	 */
	function boilerplate_widgets_init() {
		// Area 1, located at the top of the sidebar.
		register_sidebar( array(
			'name' => __( 'Primary Widget Area', 'boilerplate' ),
			'id' => 'primary-widget-area',
			'description' => __( 'The primary widget area', 'boilerplate' ),
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );

		// Area 2, located below the Primary Widget Area in the sidebar. Empty by default.
		register_sidebar( array(
			'name' => __( 'Secondary Widget Area', 'boilerplate' ),
			'id' => 'secondary-widget-area',
			'description' => __( 'The secondary widget area', 'boilerplate' ),
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );

		// Area 3, located in the footer. Empty by default.
		register_sidebar( array(
			'name' => __( 'First Footer Widget Area', 'boilerplate' ),
			'id' => 'first-footer-widget-area',
			'description' => __( 'The first footer widget area', 'boilerplate' ),
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );

		// Area 4, located in the footer. Empty by default.
		register_sidebar( array(
			'name' => __( 'Second Footer Widget Area', 'boilerplate' ),
			'id' => 'second-footer-widget-area',
			'description' => __( 'The second footer widget area', 'boilerplate' ),
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );

		// Area 5, located in the footer. Empty by default.
		register_sidebar( array(
			'name' => __( 'Third Footer Widget Area', 'boilerplate' ),
			'id' => 'third-footer-widget-area',
			'description' => __( 'The third footer widget area', 'boilerplate' ),
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );

		// Area 6, located in the footer. Empty by default.
		register_sidebar( array(
			'name' => __( 'Fourth Footer Widget Area', 'boilerplate' ),
			'id' => 'fourth-footer-widget-area',
			'description' => __( 'The fourth footer widget area', 'boilerplate' ),
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );
	}
endif;
add_action( 'widgets_init', 'boilerplate_widgets_init' );

if ( ! function_exists( 'boilerplate_remove_recent_comments_style' ) ) :
	/**
	 * Removes the default styles that are packaged with the Recent Comments widget.
	 *
	 * To override this in a child theme, remove the filter and optionally add your own
	 * function tied to the widgets_init action hook.
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_remove_recent_comments_style() {
		global $wp_widget_factory;
		remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
	}
endif;
add_action( 'widgets_init', 'boilerplate_remove_recent_comments_style' );

if ( ! function_exists( 'boilerplate_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post—date/time and author.
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_posted_on() {
		// BP: slight modification to Twenty Ten function, converting single permalink to multi-archival link
		// Y = 2012
		// F = September
		// m = 01–12
		// j = 1–31
		// d = 01–31
		printf( __( '<i class="icon-calendar"></i> <span class="entry-date">%2$s %3$s, %4$s</span>', 'boilerplate' ),
			// %1$s = container class
			'meta-prep meta-prep-author',
			// %2$s = month: /yyyy/mm/
			sprintf( '<a href="%1$s" title="%2$s" rel="bookmark">%3$s</a>',
				home_url() . '/' . get_the_date( 'Y' ) . '/' . get_the_date( 'm' ) . '/',
				esc_attr( 'View Archives for ' . get_the_date( 'F' ) . ' ' . get_the_date( 'Y' ) ),
				get_the_date( 'F' )
			),
			// %3$s = day: /yyyy/mm/dd/
			sprintf( '<a href="%1$s" title="%2$s" rel="bookmark">%3$s</a>',
				home_url() . '/' . get_the_date( 'Y' ) . '/' . get_the_date( 'm' ) . '/' . get_the_date( 'd' ) . '/',
				esc_attr( 'View Archives for ' . get_the_date( 'F' ) . ' ' . get_the_date( 'j' ) . ' ' . get_the_date( 'Y' ) ),
				get_the_date( 'j' )
			),
			// %4$s = year: /yyyy/
			sprintf( '<a href="%1$s" title="%2$s" rel="bookmark">%3$s</a>',
				home_url() . '/' . get_the_date( 'Y' ) . '/',
				esc_attr( 'View Archives for ' . get_the_date( 'Y' ) ),
				get_the_date( 'Y' )
			),
			// %5$s = author vcard
			sprintf( '',
				get_author_posts_url( get_the_author_meta( 'ID' ) ),
				sprintf( esc_attr__( 'View all posts by %s', 'boilerplate' ), get_the_author() ),
				get_the_author()
			)
		);
	}
endif;

if ( ! function_exists( 'boilerplate_posted_in' ) ) :
	/**
	 * Prints HTML with meta information for the current post (category, tags and permalink).
	 *
	 * @since Twenty Ten 1.0
	 */
	function boilerplate_posted_in() {
		// Retrieves tag list of current post, separated by commas.
		$tag_list = get_the_tag_list( '', ', ' );
		if ( $tag_list ) {
			$posted_in = __( '<i class="icon-bookmarks"></i> %1$s <br/><i class="icon-tag"></i> %2$s', 'boilerplate' );
		} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
			$posted_in = __( '<i class="icon-bookmarks"></i> %1$s', 'boilerplate' );
		} else {
			$posted_in = __( '', 'boilerplate' );
		}
		// Prints the string, replacing the placeholders.
		printf(
			$posted_in,
			get_the_category_list( ', ' ),
			$tag_list,
			get_permalink(),
			the_title_attribute( 'echo=0' )
		);
	}
endif;
/*	End original TwentyTen functions (from Starkers Theme, renamed into this namespace) */

/*	Begin Boilerplate */
	// Add Admin
	//require_once(get_template_directory() . '/boilerplate-admin/admin-menu.php');

	// remove version info from head and feeds (http://digwp.com/2009/07/remove-wordpress-version-number/)
	if ( ! function_exists( 'boilerplate_complete_version_removal' ) ) :
		function boilerplate_complete_version_removal() {
			return '';
		}
	endif;
	add_filter('the_generator', 'boilerplate_complete_version_removal');

	// add thumbnail support
	if ( function_exists( 'add_theme_support' ) ) :
		add_theme_support( 'post-thumbnails' );
	endif;

/*	End Boilerplate */


/*==========================
 
 Wordpress Automations
 
 ===========================*/

//Detect and prompt install of Recommended and Optional plugins
require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );

function my_theme_register_required_plugins() {

    $plugins = array(
        array(
            'name'      => 'Admin Menu Tree Page View',
            'slug'      => 'admin-menu-tree-page-view',
            'required'  => true,
        ),
        array(
            'name'      => 'Custom Post Type UI',
            'slug'      => 'custom-post-type-ui',
            'required'  => false,
        ),
        array(
            'name'      => 'Contact Form 7',
            'slug'      => 'contact-form-7',
            'required'  => true,
        ),
        array(
            'name'      => 'Contact Form DB',
            'slug'      => 'contact-form-7-to-database-extension',
            'required'  => true,
        ),
        array(
            'name'      => 'Custom Field Suite',
            'slug'      => 'custom-field-suite',
            'required'  => false,
        ),
        array(
            'name'      => 'Regenerate Thumbnails',
            'slug'      => 'regenerate-thumbnails',
            'required'  => false,
        ),
        array(
            'name'      => 'Reveal IDs',
            'slug'      => 'reveal-ids-for-wp-admin-25',
            'required'  => true,
        ),
        array(
            'name'      => 'W3 Total Cache',
            'slug'      => 'w3-total-cache',
            'required'  => false,
        ),
        array(
            'name'      => 'WP-PageNavi',
            'slug'      => 'wp-pagenavi',
            'required'  => true,
        ),
        array(
            'name'      => 'Custom Facebook Feed',
            'slug'      => 'custom-facebook-feed',
            'required'  => false,
        ),
        array(
            'name'      => 'Really Simple CAPTCHA',
            'slug'      => 'really-simple-captcha',
            'required'  => false,
        ),
        array(
            'name'      => 'Ultimate TinyMCE',
            'slug'      => 'ultimate-tinymce',
            'required'  => false,
        ),
        array(
            'name'      => 'WooCommerce',
            'slug'      => 'woocommerce',
            'required'  => false,
        ),
        array(
            'name'      => 'Wordpress SEO by Yoast',
            'slug'      => 'wordpress-seo',
            'required'  => false,
        ),
    );

    $config = array(
        'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Recommended Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'WP Nebula recommends the following plugin: %1$s.', 'WP Nebula recommends the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'The following optional plugin can be installed: %1$s.', 'The following optional plugins can be installed: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following optional plugin is currently inactive: %1$s.', 'The following optinal plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with WP Nebula: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with WP Nebula: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );
	
	/* 
		When updating the class file (in the /includes directory, be sure to edit the text on the following line to be 'Recommended' and 'Optional' in the installation table.
		$table_data[$i]['type'] = isset( $plugin['required'] ) && $plugin['required'] ? __( 'Recommended', 'tgmpa' ) : __( 'Optional', 'tgmpa' );
	*/
	
}


function nebulaActivation() {
	$theme = wp_get_theme();
	if ( $theme['Name'] == 'WP Nebula' && (get_post_meta(1, '_wp_page_template', 1) != 'tpl-homepage.php' || isset($_GET['nebula-reset']) ) ) {

		//Create Homepage
		$nebula_home = array(
			'ID' => 1,
			'post_type' => 'page',
			'post_title' => 'Home',
			'post_name' => 'home',
			'post_content'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam fringilla auctor est, non elementum est iaculis id. Suspendisse vel tortor vitae diam dignissim vestibulum. Aliquam auctor est vitae accumsan lacinia. Vivamus dapibus, leo eget eleifend posuere, nunc lacus elementum libero, sed imperdiet ante nunc non dui.',
			'post_status' => 'publish',
			'post_author' => 1,
			'page_template' => 'tpl-homepage.php'
		);
		
		// Insert the post into the database
		wp_insert_post($nebula_home);
		
		add_action('admin_notices','nebulaActivateComplete');
	}
	return;
}
add_action('after_switch_theme', 'nebulaActivation');

if ( is_admin() && isset($_GET['nebula-reset']) ) {
	nebulaActivation();
}

if ( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
	$theme = wp_get_theme();
	if ( $theme['Name'] == 'WP Nebula' ) {
		add_action('admin_notices','nebulaActivateComplete');
	}
}

function nebulaActivateComplete(){
	if ( isset($_GET['nebula-reset']) ) {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been reset!</strong><br/>You have reset WP Nebula. The Home page has been updated. Make sure it is set as the static front page in <a href='options-reading.php'>Settings > Reading</a>.</p></div>";
	} elseif ( get_post_meta(1, '_wp_page_template', 1) == 'tpl-homepage.php' ) {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been re-activated!</strong><br/>The Home page already exists, so it has <strong>not</strong> been updated. Make sure it is set as the static front page in <a href='options-reading.php'>Settings > Reading</a>. <a href='themes.php?activated=true&nebula-reset=true' style='float: right; color: red;'>Reset the Home page.</a></p></div>";
	} else {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been activated!</strong><br/>A new Home page has been created. Be sure to set it as the static front page in <a href='options-reading.php'>Settings > Reading</a>.</p></div>";
	}
}


/*==========================
 
 Custom WP Admin Functions
 
 ===========================*/

//Add custom admin.css stylesheet to WP Admin
function custom_admin_css() {
    echo '<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/css/admin.css" />';
}
add_action('admin_head', 'custom_admin_css');


//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
function are_you_an_admin() {
	$user = get_current_user_id();
	if (!current_user_can('manage_options') || $user == 99999 || TRUE ) { //TRUE=Not Admin (Hide update notification and admin bar), FALSE=Admin (Show update notification and admin bar)
		//For the admin page
		remove_action('admin_footer', 'wp_admin_bar_render', 1000);
		//For the front-end
		remove_action('wp_footer', 'wp_admin_bar_render', 1000);
		//CSS override for the admin page
		function remove_admin_bar_style_backend() { 
			echo '<style>body.admin-bar #wpcontent, body.admin-bar #adminmenu { padding-top: 0px !important; }</style>';
		}	  
		add_filter('admin_head','remove_admin_bar_style_backend');
		//CSS override for the frontend
		function remove_admin_bar_style_frontend() {
			echo '<style type="text/css" media="screen">
			html { margin-top: 0px !important; }
			* html body { margin-top: 0px !important; }
			</style>';
		}
		add_filter('wp_head','remove_admin_bar_style_frontend', 99);
		
		//Disable Wordpress update notification in WP Admin
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
		
	}
}
add_action('init','are_you_an_admin');


//Show update warning on Wordpress Core/Plugin update admin pages
$filename = basename($_SERVER['REQUEST_URI']);
if ( $filename == 'plugins.php' ) {
	function plugin_warning(){
		echo "<div id='pluginwarning' class='error'><p><strong>WARNING:</strong> Updating plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com'>Pinckney Hugo Group</a> if a plugin needs to be updated: <a href='tel:3154786700'>(315) 478-6700</a></p></div>";
	}
	add_action('admin_notices','plugin_warning');
} elseif ( $filename == 'update-core.php') {
	function plugin_warning(){
		echo "<div id='pluginwarning' class='error'><p><strong>WARNING:</strong> Updating Wordpress core or plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com'>Pinckney Hugo Group</a> if a plugin needs to be updated: <a href='tel:3154786700'>(315) 478-6700</a></p></div>";
	}
	add_action('admin_notices','plugin_warning');
}


//Custom login screen
function custom_login_css() {
	//Only use BG image and animation on direct requests (disable for iframe logins after session timeouts).
	if(empty($_POST['signed_request'])) {
		echo '<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/css/login.css" />';
	    echo '<script>window.userIP = "' . $_SERVER["REMOTE_ADDR"] . '";</script>';
	    echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js?ver=3.5.1"></script>';
	    //echo '<script type="text/javascript" src="' . get_bloginfo('template_directory') . '/js/libs/cssbs.js"></script>';
	    echo '<script type="text/javascript" src="' . get_bloginfo('template_directory') . '/js/libs/modernizr.custom.42059.js"></script>';
	    echo '<script type="text/javascript" src="' . get_bloginfo('template_directory') . '/js/login.js"></script>';
	    
	    echo "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-00000000-1', 'domain.com');</script>";
	}
}
add_action('login_head', 'custom_login_css');
//Change link of logo to live site
function custom_login_header_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'custom_login_header_url' );
//Change alt of image
function new_wp_login_title() {
    return get_option('blogname');
}
add_filter('login_headertitle', 'new_wp_login_title');

//Welcome Panel
function nebula_welcome_panel() {
	include('includes/welcome.php');
}
remove_action('welcome_panel','wp_welcome_panel');
add_action('welcome_panel','nebula_welcome_panel');


//Remove unnecessary Dashboard metaboxes
function remove_dashboard_metaboxes() {
    //Globalize the metaboxes array, this holds all the widgets for wp-admin
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
}
add_action('wp_dashboard_setup', 'remove_dashboard_metaboxes' );


//PHG Metabox
/*
function my_custom_dashboard_widgets() {
	global $wp_meta_boxes;
	$theme = wp_get_theme();
	wp_add_dashboard_widget('custom_help_widget', $theme->get('Name') . ' theme by Pinckney Hugo Group', 'custom_dashboard_help');
}
function custom_dashboard_help() {
	$theme = wp_get_theme();
	echo '<p>This theme was designed and developed by <br/><h2><a class="phg" href="#" target="_blank">Pinckney Hugo Group</a><h2> <br/><a href="https://www.google.com/maps?saddr=My+Location&daddr=760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a> <br/><a href="tel:3154786700" target="_blank">(315) 478-6700</a></p>';
}
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
*/


//Admin Footer Enhancements
function change_admin_footer_left() {
    $leftfooter = '<a href="http://www.pinckneyhugo.com" style="color: #0098d7; font-size: 14px; padding-left: 23px;"><img src="'.get_bloginfo('template_directory').'/images/phg/phg-symbol.png" onerror="this.onerror=null; this.src=""'.get_bloginfo('template_directory').'/images/phg/phg-symbol.png" alt="Pinckney Hugo Group" style="position: absolute; margin-left: -20px; margin-top: 4px; max-width: 18px;"/> Pinckney Hugo Group</a> &bull; <a href="https://www.google.com/maps?saddr=My+Location&daddr=760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a> &bull; <a href="tel:3154786700" target="_blank">(315) 478-6700</a>';
    return $leftfooter;
}
add_filter('admin_footer_text', 'change_admin_footer_left');


/*==========================
 
 Custom Functions 
 
 ===========================*/

date_default_timezone_set('America/New_York');


//Disable Pingbacks to prevent security issues
add_filter( 'xmlrpc_methods', function( $methods ) {
   unset( $methods['pingback.ping'] );
   return $methods;
});


//Pull favicon from the theme folder (First is for Frontend, second is for Admin; default is same for both)
function theme_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . get_bloginfo('template_directory') . '/images/favicon.ico" />';
}
add_action('wp_head', 'theme_favicon');
function admin_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . get_bloginfo('template_directory') . '/images/favicon.ico" />';
}
add_action('admin_head', 'admin_favicon');


//Remove Wordpress version info from head and feeds
function complete_version_removal() {
	return '';
}
add_filter('the_generator', 'complete_version_removal');


//Allow pages to have excerpts too
add_post_type_support( 'page', 'excerpt' );


// Add new image sizes
//if (function_exists('add_image_size')) {
 	add_image_size( 'example', 32, 32, 1 );
//}


//Override the default Wordpress search form
function my_search_form( $form ) {
    $form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
	    <div>
		    <input type="text" value="' . get_search_query() . '" name="s" id="s" />
		    <input type="submit" id="searchsubmit" class="wp_search_submit" value="'. esc_attr__( 'Search' ) .'" />
	    </div>
    </form>';
    return $form;
}
add_filter( 'get_search_form', 'my_search_form' );


//Name the locations where Navigation Menus will be located (to avoid duplicate IDs)
function nav_menu_locations() {
	// Register nav menu locations
	register_nav_menus( array(
		'topnav' => 'Top Nav Menu',
		'header' => 'Header Menu [Primary]',
		'mobile' => 'Mobile Menu',
		'sidebar' => 'Sidebar Menu',
		'footer' => 'Footer Menu'
		)
	);
}
add_action( 'after_setup_theme', 'nav_menu_locations' );


//Use this instead of the_excerpt(); and get_the_excerpt(); so we can have better control over the excerpt.
//Several ways to implement this:
	//Inside the loop (or outside the loop for current post/page): nebula_the_excerpt('Read more &raquo;', 20, 1);
	//Outside the loop: nebula_the_excerpt(572, 'Read more &raquo;', 20, 1);
function nebula_the_excerpt( $postID=0, $more=0, $length=55, $hellip=0 ) {
	
	if ( $postID && is_int($postID) ) {
		$the_post = get_post($postID);
	} else {
		if ( $postID != 0 || is_string($postID) ) {
			if ( $length == 0 || $length == 1 ) {
				$hellip = $length;
			} else {
				$hellip = false;
			}
			
			if ( is_int($more) ) {
				$length = $more;
			} else {
				$length = 55;
			}
			
			$more = $postID;
		}
		$postID = get_the_ID();
		$the_post = get_post($postID);
	}
	
	if ( $the_post->post_excerpt ) {
		$string = strip_tags(strip_shortcodes($the_post->post_excerpt), '');
	} else {
		$string = strip_tags(strip_shortcodes($the_post->post_content), '');
	}
	
	$string = string_limit_words($string, $length);
	
	if ( $hellip ) {
		if ( $string[1] == 1 ) {
			$string[0] .= '&hellip; ';
		}
	}
		
	if ( isset($more) && $more != '' ) {
		$string[0] .= ' <a class="nebula_the_excerpt" href="' . get_permalink($postID) . '">' . $more . '</a>';
	}
	
	return $string[0];
}



//Text limiter by words
function string_limit_words($string, $word_limit){
	$limited[0] = $string;
	$limited[1] = 0;
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){
		array_pop($words);
		$limited[0] = implode(' ', $words);
		$limited[1] = 1;
	}
	return $limited;
}


//Word limiter by characters
function word_limit_chars($string, $charlimit, $continue=false){
	// 1 = "Continue Reading", 2 = "Learn More"
	if(strlen(strip_tags($string, '<p><span><a>')) <= $charlimit){
		$newString = strip_tags($string, '<p><span><a>');
	} else{
		$newString = preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($string, '<p><span><a>'), 0, ($charlimit + 1)));
		if($continue == 1){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'boilerplate' ) . '</a>';
		} elseif($continue == 2){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">' . __( 'Learn more &raquo;', 'boilerplate' ) . '</a>';
		} else{
			$newString = $newString . '&hellip;';
		}
	}
	return $newString;
}


//Breadcrumbs
function the_breadcrumb() {
  $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  $delimiter = '<span class="arrow">&raquo;</span>'; // delimiter between crumbs
  $home = 'Home'; // text for the 'Home' link
  $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
  $before = '<span class="current">'; // tag before the current crumb
  $after = '</span>'; // tag after the current crumb
  $dontCapThese = array('the', 'and', 'but', 'of');
  global $post;
  $homeLink = get_bloginfo('url');
  if (is_home() || is_front_page()) {
    if ($showOnHome == 1) echo '<div id="bcrumbs"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a></nav>';
  } else {
    echo '<div id="bcrumbs"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
 	if ( function_exists( 'is_pod_page' ) ) {
	    if(  is_pod_page() ) {
	      // This is a Pod page, so we'll  explode the URI and turn each virtual path into a crumb.
	      $url_parts = explode('/', $_SERVER['REQUEST_URI']);
	      $link;
	      // These are specific to LCS, but the principle is the same.
	      $skipThese = array('detail', 'concentration', 'minor', 'bachelor', 'masters', 'doctoral', 'other', 'certificate');
	      $i = 0;
			foreach ($url_parts as $key => $value) {
				// Pulling off the last one because it won't need a link or a delimiter.
				if ($key != (count($url_parts) - 1)) {
					if($value !='' && !in_array($value, $skipThese)){
						$pieces = explode('-', $value);
						$link_str = '';
						$link = ($i == 0) ? $link : $link  . '/' . $value;
						foreach($pieces as $key => $value){
							if(!in_array($value, $dontCapThese)){
								$link_str .= ucfirst($value) . ' ';
							} else{
								$link_str .= $value . ' ';
							}
						}
						echo '<a href="' . get_bloginfo('url') . $link . '/">' . $link_str . '</a> ' . $delimiter . ' ';
					}
					$i++;
				}
				// Finally we'll strip out the <a> tags
				if($key == (count($url_parts) - 1)) {
					$pieces = explode('-', $value);
					foreach($pieces as $key => $value){
						if(!in_array($value, $dontCapThese)){
							$txt_str .= ucfirst($value) . ' ';
						} else{
							$txt_str .= $value . ' ';
						}
					}
					echo $txt_str;
				}
			}
		}
    } elseif ( is_category() ) {
      $thisCat = get_category(get_query_var('cat'), false);
      if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
      echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
    } elseif ( is_search() ) {
      echo $before . 'Search results for "' . get_search_query() . '"' . $after;
    } elseif ( is_day() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
      echo $before . get_the_time('d') . $after;
    } elseif ( is_month() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo $before . get_the_time('F') . $after;
    } elseif ( is_year() ) {
      echo $before . get_the_time('Y') . $after;
    } elseif ( is_single() && !is_attachment() ) {
      if ( get_post_type() != 'post' ) {
        $post_type = get_post_type_object(get_post_type());
        $slug = $post_type->rewrite;
        echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
        if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
      } else {
        $cat = get_the_category(); $cat = $cat[0];
        $cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
        if ($showCurrent == 0) $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
        echo $cats;
        if ($showCurrent == 1) echo $before . get_the_title() . $after;
      }
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
      $post_type = get_post_type_object(get_post_type());
      echo $before . $post_type->labels->singular_name . $after;
    } elseif ( is_attachment() ) {
      echo 'Uploads &raquo; ';
      echo the_title();
    } elseif ( is_page() && !$post->post_parent ) {
      if ($showCurrent == 1) echo $before . get_the_title() . $after;
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      for ($i = 0; $i < count($breadcrumbs); $i++) {
        echo $breadcrumbs[$i];
        if ($i != count($breadcrumbs)-1) echo ' ' . $delimiter . ' ';
      }
      if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
    } elseif ( is_tag() ) {
      echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
    } elseif ( is_author() ) {
       global $author;
      $userdata = get_userdata($author);
      echo $before . 'Articles posted by ' . $userdata->display_name . $after;
    } elseif ( is_404() ) {
      echo $before . 'Error 404' . $after;
    }
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo __('Page') . ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
    echo '</nav></div>';
  }
} // end the_breadcrumbs()


//Prevent empty search query error (Show all results instead)
function fix_empty_search($query){
    global $wp_query;
    if (isset($_GET['s']) && $_GET['s']==''){ //if search parameter is blank, do not return false
        $wp_query->set('s',' ');
        $wp_query->is_search=true;
    }
    return $query;
}
add_action('pre_get_posts','fix_empty_search');


//Redirect if only single search result
function redirect_single_post() {
    if (is_search()) {
        global $wp_query;
        if ($wp_query->post_count == 1 && $wp_query->max_num_pages == 1) {
            wp_redirect( get_permalink( $wp_query->posts['0']->ID ) );
            exit;
        }
    }
}
add_action('template_redirect', 'redirect_single_post');


//Remove extraneous <head> from Wordpress
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);


//Declare support for WooCommerce
/***** Uncomment only if using WooCommerce
add_theme_support('woocommerce');
//Remove existing WooCommerce hooks to be replaced with our own
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
//Replace WooCommerce hooks at our own declared locations
add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);
function my_theme_wrapper_start() {
	echo '<section id="WooCommerce">';
}
function my_theme_wrapper_end() {
	echo '</section>';
}
*****/

//PHP-Mobile-Detect - https://github.com/serbanghita/Mobile-Detect/wiki/Code-examples
//Before running conditions using this, you must have $detect = new Mobile_Detect(); before the logic.
//Logic can fire from "isMobile()" or "isTablet()" or "is('AndroidOS')".
require_once 'includes/Mobile_Detect.php';


//Add additional body classes including ancestor IDs and directory structures
function page_name_class($classes) {
	global $post;
	$segments = explode('/', trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ));
	$parents = get_post_ancestors( $post->ID );
	foreach ( $parents as $parent ) {
		$classes[] = 'ancestor-id-' . $parent;
	}
	foreach ( $segments as $segment ) {
		$classes[] = $segment;
	}
	return $classes;
}
add_filter('body_class', 'page_name_class');




function youtubeMeta($vidID) {
	global $vidMeta;
	$xml = simplexml_load_string(file_get_contents("http://gdata.youtube.com/feeds/api/videos/" . $vidID));
	$vidMeta['origin'] = baseDomain();
	$vidMeta['id'] = $vidID;
	$vidMeta['title'] = $xml->title;
	$vidMeta['safetitle'] = str_replace(" ", "-", $vidMeta['title']);
	$vidMeta['content'] = $xml->content;
	$vidMeta['href'] = $xml->link['href'];
	$vidMeta['author'] = $xml->author->name;
	$vidMeta['seconds'] = strval($xml->xpath('//yt:duration[@seconds]')[0]->attributes()->seconds);
	$vidMeta['duration'] = intval(gmdate("i", $vidMeta['seconds'])) . gmdate(":s", $vidMeta['seconds']);
	return $vidMeta;
}


function baseDomain( $str='' ) {
	if ( $str == '' ) {
		$str = home_url();
	}
    $url = @parse_url( $str );
    if ( empty( $url['host'] ) ) return;
    $parts = explode( '.', $url['host'] );
    $slice = ( strlen( reset( array_slice( $parts, -2, 1 ) ) ) == 2 ) && ( count( $parts ) > 2 ) ? 3 : 2;
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    return $protocol . implode( '.', array_slice( $parts, ( 0 - $slice ), $slice ) );
}

//Traverse multidimensional arrays
function in_array_r($needle, $haystack, $strict = true) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}


//Automatically convert HEX colors to RGB.
function hex2rgb( $colour ) {
	if ( $colour[0] == '#' ) {
		$colour = substr( $colour, 1 );
	}
	if ( strlen( $colour ) == 6 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
	} elseif ( strlen( $colour ) == 3 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
	} else {
		return false;
	}
	$r = hexdec( $r );
	$g = hexdec( $g );
	$b = hexdec( $b );
	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}


//Footer Widget Counter
function footerWidgetCounter() {
	$footerWidgetCount = 0;
	if ( is_active_sidebar('First Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Second Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Third Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Fourth Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	return $footerWidgetCount;
}


?>
