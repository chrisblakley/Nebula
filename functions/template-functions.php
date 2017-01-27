<?php
/**
 * Template_Functions
 *
 * @package     Nebula\Template_Functions
 * @since       1.0.0
 * @author      Chris Blakley
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

//Print the PHG logo as text with or without hover animation.
if ( !function_exists('pinckneyhugogroup') ){
    function pinckney_hugo_group($anim){ pinckneyhugogroup($anim); }
    function phg($anim){ pinckneyhugogroup($anim); }
    function pinckneyhugogroup($anim=false, $white=false){
        if ( $anim ){
            $anim = 'anim';
        }
        if ( $white ){
            $white = 'anim';
        }
        return '<a class="phg ' . $anim . ' ' . $white . '" href="http://www.pinckneyhugo.com/" target="_blank"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a>';
    }
}

//Show different meta data information about the post. Typically used inside the loop.
//Example: nebula_meta('by');
function nebula_meta($meta){
    $override = apply_filters('pre_nebula_meta', false, $meta);
    if ( $override !== false ){echo $override; return;}

    if ( $meta == 'date' || $meta == 'time' || $meta == 'on' || $meta == 'day' || $meta == 'when' ){
        echo nebula_post_date();
    } elseif ( $meta == 'author' || $meta == 'by' ){
        echo nebula_post_author();
    } elseif ( $meta == 'type' || $meta == 'cpt' || $meta == 'post_type' ){
        echo nebula_post_type();
    } elseif ( $meta == 'categories' || $meta == 'category' || $meta == 'cat' || $meta == 'cats' || $meta == 'in' ){
        echo nebula_post_categories();
    } elseif ( $meta == 'tags' || $meta == 'tag' ){
        echo nebula_post_tags();
    } elseif ( $meta == 'dimensions' || $meta == 'size' ){
        echo nebula_post_dimensions();
    } elseif ( $meta == 'exif' || $meta == 'camera' ){
        echo nebula_post_exif();
    } elseif ( $meta == 'comments' || $meta == 'comment' ){
        echo nebula_post_comments();
    } elseif ( $meta == 'social' || $meta == 'sharing' || $meta == 'share' ){
        nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), 0);
    }
}

//Date post meta
function nebula_post_date($icon=true, $linked=true, $day=true){
    $the_icon = '';
    if ( $icon ){
        $the_icon = '<i class="fa fa-calendar-o"></i> ';
    }

    $the_day = '';
    if ( $day ){ //If the day should be shown (otherwise, just month and year).
        $the_day = get_the_date('d') . '/';
    }

    if ( $linked ){
        return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date" datetime="' . get_the_time('c') . '" itemprop="datePublished" content="' . get_the_date('c') . '">' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url('/') . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
    } else {
        return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date" datetime="' . get_the_time('c') . '" itemprop="datePublished" content="' . get_the_date('c') . '">' . get_the_date('F j, Y') . '</span></span>';
    }
}

//Author post meta
function nebula_post_author($icon=true, $linked=true, $force=false){
    $the_icon = '';
    if ( $icon ){
        $the_icon = '<i class="fa fa-user"></i> ';
    }

    if ( nebula_option('author_bios', 'enabled') || $force ){
        if ( $linked && !$force ){
            return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $the_icon . '<span class="meta-item entry-author">' . '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '" itemprop="name">' . get_the_author() . '</a></span></span>';
        } else {
            return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $the_icon . '<span class="meta-item entry-author" itemprop="name">' . get_the_author() . '</span></span>';
        }
    }
}

//Post type meta
function nebula_post_type($icon=true){
    $post_icon_img = '<i class="fa fa-thumb-tack"></i>';
    if ( $icon ){
        global $wp_post_types;
        $post_type = get_post_type();

        if ( $post_type == 'post' ){
            $post_icon_img = '<i class="fa fa-thumb-tack"></i>';
        } elseif ( $post_type == 'page' ){
            $post_icon_img = '<i class="fa fa-file-text"></i>';
        } else {
            $post_icon = $wp_post_types[$post_type]->menu_icon;
            if ( !empty($post_icon) ){
                if ( strpos('dashicons-', $post_icon) >= 0 ){
                    $post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
                } else {
                    $post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" />';
                }
            } else {
                $post_icon_img = '<i class="fa fa-thumb-tack"></i>';
            }
        }
    }

    return '<span class="meta-item post-type">' . $post_icon_img . ucwords(get_post_type()) . '</span>';
}

//Categories post meta
function nebula_post_categories($icon=true){
    $the_icon = '';
    if ( $icon ){
        $the_icon = '<i class="fa fa-bookmark"></i> ';
    }

    if ( is_object_in_taxonomy(get_post_type(), 'category') ){
        return '<span class="posted-in meta-item post-categories">' . $the_icon . get_the_category_list(', ') . '</span>';
    }
    return '';
}

//Tags post meta
function nebula_post_tags($icon=true){
    $tag_list = get_the_tag_list('', ', ');
    if ( $tag_list ){
        $the_icon = '';
        if ( $icon ){
            $tag_plural = ( count(get_the_tags()) > 1 )? 'tags' : 'tag';
            $the_icon = '<i class="fa fa-' . $tag_plural . '"></i> ';
        }
        return '<span class="posted-in meta-item post-tags">' . $the_icon . $tag_list . '</span>';
    }
    return '';
}

//Image dimensions post meta
function nebula_post_dimensions($icon=true, $linked=true){
    if ( wp_attachment_is_image() ){
        $the_icon = '';
        if ( $icon ){
            $the_icon = '<i class="fa fa-expand"></i> ';
        }

        $metadata = wp_get_attachment_metadata();
        if ( $linked ){
            echo '<span class="meta-item meta-dimensions">' . $the_icon . '<a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a></span>';
        } else {
            echo '<span class="meta-item meta-dimensions">' . $the_icon . $metadata['width'] . ' &times; ' . $metadata['height'] . '</span>';
        }
    }
}

//Image EXIF post meta
function nebula_post_exif($icon=true){
    $the_icon = '';
    if ( $icon ){
        $the_icon = '<i class="fa fa-camera"></i> ';
    }

    $imgmeta = wp_get_attachment_metadata();
    if ( $imgmeta ){ //Check for Bad Data
        if ( $imgmeta['image_meta']['focal_length'] == 0 || $imgmeta['image_meta']['aperture'] == 0 || $imgmeta['image_meta']['shutter_speed'] == 0 || $imgmeta['image_meta']['iso'] == 0 ){
            $output = 'No valid EXIF data found';
        } else { //Convert the shutter speed retrieve from database to fraction
            if ( (1/$imgmeta['image_meta']['shutter_speed']) > 1 ){
                if ( (number_format((1/$imgmeta['image_meta']['shutter_speed']), 1)) == 1.3 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.5 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.6 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 2.5 ){
                    $pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 1, '.', '') . ' second';
                } else {
                    $pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 0, '.', '') . ' second';
                }
            } else {
                $pshutter = $imgmeta['image_meta']['shutter_speed'] . " seconds";
            }

            $output = '<time datetime="' . date('c', $imgmeta['image_meta']['created_timestamp']) . '"><span class="month">' . date('F', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="day">' . date('j', $imgmeta['image_meta']['created_timestamp']) . '</span><span class="suffix">' . date('S', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="year">' . date('Y', $imgmeta['image_meta']['created_timestamp']) . '</span></time>' . ', ';
            $output .= $imgmeta['image_meta']['camera'] . ', ';
            $output .= $imgmeta['image_meta']['focal_length'] . 'mm' . ', ';
            $output .= '<span style="font-style: italic; font-family: Trebuchet MS, Candara, Georgia; text-transform: lowercase;">f</span>/' . $imgmeta['image_meta']['aperture'] . ', ';
            $output .= $pshutter . ', ';
            $output .= $imgmeta['image_meta']['iso'] .' ISO';
        }
    } else {
        $output = 'No EXIF data found';
    }

    return '<span class="meta-item meta-exif">' . $the_icon . $output . '</span>';
}

//Comments post meta
function nebula_post_comments($icon=true, $linked=true, $empty=true){
    $comments_text = 'Comments';
    if ( get_comments_number() == 0 ){
        $comment_icon = 'fa-comment-o';
        $comment_show = ( $empty )? '' : 'hidden'; //If comment link should show if no comments. True = show, False = hidden
    } elseif ( get_comments_number() == 1 ){
        $comment_icon = 'fa-comment';
        $comments_text = 'Comment';
    } elseif ( get_comments_number() > 1 ){
        $comment_icon = 'fa-comments';
    }

    $the_icon = '';
    if ( $icon ){
        $the_icon = '<i class="fa ' . $comment_icon . '"></i> ';
    }

    if ( $linked ){
        $postlink = ( is_single() )? '' : get_the_permalink();
        return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . '<a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
    } else {
        return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . get_comments_number() . ' ' . $comments_text . '</span>';
    }
}

//Use this instead of the_excerpt(); and get_the_excerpt(); to have better control over the excerpt.
//Inside the loop (or outside the loop for current post/page): nebula_excerpt(array('length' => 20, 'ellipsis' => true));
//Outside the loop: nebula_excerpt(array('id' => 572, 'length' => 20, 'ellipsis' => true));
//Custom text: nebula_excerpt(array('text' => 'Lorem ipsum <strong>dolor</strong> sit amet.', 'more' => 'Continue &raquo;', 'length' => 3, 'ellipsis' => true, 'strip_tags' => true));
function nebula_excerpt($options=array()){
    $override = apply_filters('pre_nebula_excerpt', false, $options);
    if ( $override !== false ){return $override;}

    $defaults = array(
        'id' => false,
        'text' => false,
        'length' => 55,
        'ellipsis' => false,
        'url' => false,
        'more' => 'Read More &raquo;',
        'strip_shortcodes' => true,
        'strip_tags' => true,
    );

    $data = array_merge($defaults, $options);

    //Establish text
    if ( empty($data['text']) ){
        $the_post = ( !empty($data['id']) && is_int($data['id']) )? get_post($data['id']) : get_post(get_the_ID());
        if ( empty($the_post) ){
            return false;
        }
        $data['text'] = ( !empty($the_post->post_excerpt) )? $the_post->post_excerpt : $the_post->post_content;
    }

    //Strip Shortcodes
    if ( $data['strip_shortcodes'] ){
        $data['text'] = strip_shortcodes($data['text']);
    } else {
        $data['text'] = preg_replace('~(?:\[/?)[^/\]]+/?\]~s', ' ', $data['text']);
    }

    //Strip Tags
    if ( $data['strip_tags'] ){
        $data['text'] = strip_tags($data['text'], '');
    }

    //Length
    if ( !empty($data['length']) && is_int($data['length']) ){
        $limited = string_limit_words($data['text'], $data['length']); //Returns array: $limited[0] is the string, $limited[1] is boolean if it was limited or not.
        $data['text'] = $limited['text'];
    }

    //Ellipsis
    if ( $data['ellipsis'] && !empty($limited['is_limited']) ){
        $data['text'] .= '&hellip;';
    }

    //Link
    if ( !empty($data['more']) ){
        if ( empty($data['url']) ){ //If has "more" text, but no link URL
            $data['url'] = ( !empty($data['id']) )? get_permalink($data['id']) : get_permalink(get_the_id()); //Use the ID if available, or use the current ID.
        }

        $data['text'] .= ' <a class="nebula_excerpt" href="' . $data['url'] . '">' . $data['more'] . '</a>';
    }

    return $data['text'];
}

//Display Social Buttons
function nebula_social($networks=array('facebook', 'twitter', 'google+'), $counts=0){
    $override = apply_filters('pre_nebula_social', false, $networks, $counts);
    if ( $override !== false ){echo $override; return;}

    if ( is_string($networks) ){ //if $networks is a string, create an array for the string.
        $networks = array($networks);
    } elseif ( is_int($networks) && ($networks == 1 || $networks == 0) ){ //If it is an integer of 1 or 0, then set it to $counts
        $counts = $networks;
        $networks = array('facebook', 'twitter', 'google+');
    } elseif ( !is_array($networks) ){
        $networks = array('facebook', 'twitter', 'google+');
    }
    $networks = array_map('strtolower', $networks); //Convert $networks to lower case for more flexible string matching later.

    echo '<div class="sharing-links">';
    foreach ( $networks as $network ){
        //Facebook
        if ( in_array($network, array('facebook', 'fb')) ){
            nebula_facebook_share($counts);
        }

        //Twitter
        if ( in_array($network, array('twitter')) ){
            nebula_twitter_tweet($counts);
        }

        //Google+
        if ( in_array($network, array('google_plus', 'google', 'googleplus', 'google+', 'g+', 'gplus', 'g_plus', 'google plus', 'google-plus', 'g-plus')) ){
            nebula_google_plus($counts);
        }

        //LinkedIn
        if ( in_array($network, array('linkedin', 'li', 'linked-in', 'linked_in')) ){
            nebula_linkedin_share($counts);
        }

        //Pinterest
        if ( in_array($network, array('pinterest', 'pin')) ){
            nebula_pinterest_pin($counts);
        }
    }
    echo '</div><!-- /sharing-links -->';
}

/*
	Social Button Functions
	//@TODO "Nebula" 0: Eventually upgrade these to support vertical count bubbles as an option.
*/

function nebula_facebook_share($counts=0, $url=false){
    $override = apply_filters('pre_nebula_facebook_share', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button facebook-share require-fbsdk">
        <div class="fb-share-button" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>"></div>
    </div>
<?php }


function nebula_facebook_like($counts=0, $url=false){
    $override = apply_filters('pre_nebula_facebook_like', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button facebook-like require-fbsdk">
        <div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="false"></div>
    </div>
<?php }

function nebula_facebook_both($counts=0, $url=false){
    $override = apply_filters('pre_nebula_facebook_both', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button facebook-both require-fbsdk">
        <div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="true"></div>
    </div>
<?php }

$nebula_twitter_widget_loaded = false;
function nebula_twitter_tweet($counts=0){
    $override = apply_filters('pre_nebula_twitter_tweet', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button twitter-tweet">
        <a href="https://twitter.com/share" class="twitter-share-button" <?php echo ( $counts != 0 )? '': 'data-count="none"'; ?>>Tweet</a>
        <?php twitter_widget_script(); ?>
    </div>
    <?php
}

function nebula_twitter_follow($counts=0, $username=false){
    $override = apply_filters('pre_nebula_twitter_follow', false, $counts, $username);
    if ( $override !== false ){echo $override; return;}

    if ( empty($username) && !nebula_option('twitter_username') ){
        return false;
    } elseif ( empty($username) && nebula_option('twitter_username') ){
        $username = nebula_option('twitter_username');
    } elseif ( strpos($username, '@') === false ){
        $username = '@' . $username;
    }
    ?>
    <div class="nebula-social-button twitter-follow">
        <a href="https://twitter.com/<?php echo str_replace('@', '', $username); ?>" class="twitter-follow-button" <?php echo ( $counts != 0 )? '': 'data-show-count="false"'; ?> <?php echo ( !empty($username) )? '': 'data-show-screen-name="false"'; ?>>Follow <?php echo $username; ?></a>
        <?php twitter_widget_script(); ?>
    </div>
    <?php
}

function twitter_widget_script(){
    if ( empty($nebula_twitter_widget_loaded) ){
        ?>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        <?php
        $nebula_twitter_widget_loaded = true;
    }
}

$nebula_google_plus_widget_loaded = true;
function nebula_google_plus($counts=0){
    $override = apply_filters('pre_nebula_google_plus', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button google-plus-plus-one">
        <div class="g-plusone" data-size="medium" <?php echo ( $counts != 0 )? '' : 'data-annotation="none"'; ?>></div>
        <?php if ( empty($nebula_google_plus_widget_loaded) ) : ?>
            <script src="https://apis.google.com/js/platform.js" async defer></script>
            <?php $nebula_google_plus_widget_loaded = true; ?>
        <?php endif; ?>
    </div>
    <?php
}

$nebula_linkedin_widget_loaded = false;
function nebula_linkedin_share($counts=0){
    $override = apply_filters('pre_nebula_linkedin_share', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button linkedin-share">
        <?php linkedin_widget_script(); ?>
        <script type="IN/Share" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
    </div>
    <?php
}

function nebula_linkedin_follow($counts=0){
    $override = apply_filters('pre_nebula_linkedin_follow', false, $counts);
    if ( $override !== false ){echo $override; return;}
    ?>
    <div class="nebula-social-button linkedin-follow">
        <?php linkedin_widget_script(); ?>
        <script type="IN/FollowCompany" data-id="1337" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
    </div>
    <?php
}

function linkedin_widget_script(){
    if ( empty($nebula_linkedin_widget_loaded) ){
        ?>
        <script type="text/javascript" src="//platform.linkedin.com/in.js" async defer> lang: en_US</script>
        <?php
        $nebula_linkedin_widget_loaded = true;
    }
}

$nebula_pinterest_pin_widget_loaded = false;
function nebula_pinterest_pin($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up...
    $override = apply_filters('pre_nebula_pinterest_pin', false, $counts);
    if ( $override !== false ){echo $override; return;}

    if ( has_post_thumbnail() ){
        $featured_image = get_the_post_thumbnail();
    } else {
        $featured_image = get_template_directory_uri() . '/images/meta/og-thumb.png'; //@TODO "Nebula" 0: This should probably be a square? Check the recommended dimensions.
    }
    ?>
    <div class="nebula-social-button pinterest-pin">
        <a href="//www.pinterest.com/pin/create/button/?url=<?php echo get_page_link(); ?>&media=<?php echo $featured_image; ?>&description=<?php echo urlencode(get_the_title()); ?>" data-pin-do="buttonPin" data-pin-config="<?php echo ( $counts != 0 )? 'beside' : 'none'; ?>" data-pin-color="red">
            <img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" />
        </a>
        <?php if ( empty($nebula_pinterest_pin_widget_loaded) ): ?>
            <script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>
            <?php $nebula_pinterest_pin_widget_loaded = true; ?>
        <?php endif; ?>
    </div>
    <?php
}

//Modified WordPress search form using Bootstrap components
function nebula_search_form($placeholder=''){
    $override = apply_filters('pre_nebula_search_form', false, $placeholder);
    if ( $override !== false ){echo $override; return;}

    $value = $placeholder;
    if ( empty($placeholder) ){
        $placeholder = 'Search';
        if ( get_search_query() ){
            $value = get_search_query();
            $placeholder = get_search_query();
        }
    }

    $form = '<form id="searchform" class="form-inline" role="search" method="get" action="' . home_url('/') . '">
				<div class="input-group mb-2 mr-sm-2 mb-sm-0">
					<div class="input-group-addon"><i class="fa fa-search"></i></div>
					<input id="s" class="form-control" type="text" name="s" value="' . $value . '" placeholder="' . $placeholder . '">
				</div>

				<button id="searchsubmit" class="btn btn-brand wp_search_submit" type="submit">Submit</button>
			</form>';

    return $form;
}

//Easily create markup for a Hero area search input
function nebula_hero_search($placeholder='What are you looking for?'){
    $override = apply_filters('pre_nebula_hero_search', false, $placeholder);
    if ( $override !== false ){echo $override; return;}

    $form = '<div id="nebula-hero-formcon">
			<form id="nebula-hero-search" class="nebula-search-iconable search" method="get" action="' . home_url('/') . '">
				<input type="search" class="nebula-search open input search nofade" name="s" placeholder="' . $placeholder . '" autocomplete="off" x-webkit-speech />
			</form>
		</div>';
    return $form;
}

//Infinite Load
// Ajax call handle in nebula()->functions->infinite_load();
function nebula_infinite_load_query($args=array('post_status' => 'publish', 'showposts' => 4), $loop=false){
    $override = apply_filters('pre_nebula_infinite_load_query', false);
    if ( $override !== false ){return;}

    global $wp_query;
    if ( empty($args['paged']) ){
        $args['paged'] = 1;
        if ( get_query_var('paged') ){
            $args['paged'] = get_query_var('paged');
            ?>
            <div class="infinite-start-note">
                <a href="<?php echo get_the_permalink(); ?>">&laquo; Back to page 1</a>
            </div>
            <?php
        } elseif ( !empty($wp_query->query['paged']) ){
            $args['paged'] = $wp_query->query['paged'];
            ?>
            <div class="infinite-start-note">
                <a href="<?php echo get_the_permalink(); ?>">&laquo; Back to page 1</a>
            </div>
            <?php
        }
    }

    query_posts($args);

    if ( empty($args['post_type']) ){
        $post_type_label = 'posts';
    } else {
        $post_type = ( is_array($args['post_type']) )? $args['post_type'][0] : $args['post_type'];
        $post_type_obj = get_post_type_object($args['post_type']);
        $post_type_label = lcfirst($post_type_obj->label);
    }
    ?>

    <div id="infinite-posts-list" data-max-pages="<?php echo $wp_query->max_num_pages; ?>" data-max-posts="<?php echo $wp_query->found_posts; ?>">
        <?php
        $loop = sanitize_text_field($loop);
        if ( !$loop ){
            get_template_part('loop');
        } else {
            if ( function_exists($loop) ){
                call_user_func($loop);
            } elseif ( locate_template($loop . '.php') ){
                get_template_part($loop);
            } else {
                if ( is_dev() ){
                    echo '<strong>Warning:</strong> The custom loop template or function ' . $loop . ' does not exist! Falling back to loop.php.';
                }
                get_template_part('loop');
            }
        }
        ?>
    </div>

    <?php do_action('nebula_infinite_before_load_more'); ?>

    <div class="loadmorecon <?php echo ( $args['paged'] >= $wp_query->max_num_pages )? 'disabled' : ''; ?>">
        <a class="infinite-load-more" href="#"><?php echo ( $args['paged'] >= $wp_query->max_num_pages )? 'No more ' . $post_type_label . '.' : 'Load More'; ?></a>
        <div class="infinite-loading">
            <div class="a"></div> <div class="b"></div> <div class="c"></div>
        </div>
    </div>

    <script><?php //Must be in PHP so $args can be encoded. ?>
        jQuery(document).on('ready', function(){
            var pageNumber = <?php echo $args['paged']; ?>+1;

            jQuery('.infinite-load-more').on('click touch tap', function(){
                var maxPages = jQuery('#infinite-posts-list').attr('data-max-pages');
                var maxPosts = jQuery('#infinite-posts-list').attr('data-max-posts');

                if ( pageNumber <= maxPages ){
                    jQuery('.loadmorecon').addClass('loading');
                    jQuery.ajax({
                        type: "POST",
                        url: nebula.site.ajax.url,
                        data: {
                            nonce: nebula.site.ajax.nonce,
                            action: 'nebula_infinite_load',
                            page: pageNumber,
                            args: <?php echo json_encode($args); ?>,
                            loop: <?php echo json_encode($loop); ?>,
                        },
                        success: function(response){
                            jQuery("#infinite-posts-list").append('<div class="clearfix infinite-page infinite-page-' + (pageNumber-1) + ' sliding" style="display: none;">' + response + '</div>');
                            jQuery('.infinite-page-' + (pageNumber-1)).slideDown({
                                duration: 750,
                                easing: 'easeInOutQuad',
                                complete: function(){
                                    jQuery('.loadmorecon').removeClass('loading');
                                    jQuery('.infinite-page.sliding').removeClass('sliding');
                                    nebula.dom.document.trigger('nebula_infinite_slidedown_complete');
                                }
                            });

                            if ( pageNumber >= maxPages ){
                                jQuery('.loadmorecon').addClass('disabled').find('a').text('No more <?php echo $post_type_label; ?>.');
                            }

                            var newQueryStrings = '';
                            if ( typeof document.URL.split('?')[1] !== 'undefined' ){
                                newQueryStrings = '?' + document.URL.split('?')[1].replace(/[?&]paged=\d+/, '');
                            }

                            history.replaceState(null, document.title, nebula.post.permalink + 'page/' + pageNumber + newQueryStrings);
                            nebula.dom.document.trigger('nebula_infinite_finish');
                            ga('set', gaCustomDimensions['timestamp'], localTimestamp());
                            ga('send', 'event', 'Infinite Query', 'Load More', 'Loaded page ' + pageNumber);
                            nv('increment', 'infinite_query_loads');
                            pageNumber++;
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown){
                            jQuery(document).trigger('nebula_infinite_finish');
                            ga('set', gaCustomDimensions['timestamp'], localTimestamp());
                            ga('send', 'event', 'Error', 'AJAX Error', 'Infinite Query Load More AJAX');
                            nv('increment', 'ajax_error');
                        },
                        timeout: 60000
                    });
                }
                return false;
            });
        });
    </script>
    <?php
}

//Check if business hours exist in Nebula Options
function has_business_hours(){
    foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
        if ( nebula_option('business_hours_' . $weekday . '_enabled') || nebula_option('business_hours_' . $weekday . '_open') || nebula_option('business_hours_' . $weekday . '_close') ){
            return true;
        }
    }
    return false;
}

//Check if the requested datetime is within business hours.
//If $general is true this function returns true if the business is open at all on that day
function is_business_open($date=null, $general=false){ return business_open($date, $general); }
function is_business_closed($date=null, $general=false){ return !business_open($date, $general); }
function business_open($date=null, $general=false){
    $override = apply_filters('pre_business_open', false, $date, $general);
    if ( $override !== false ){return $override;}

    if ( empty($date) || $date == 'now' ){
        $date = time();
    } elseif ( strtotime($date) ){
        $date = strtotime($date . ' ' . date('g:ia', strtotime('now')));
    }
    $today = strtolower(date('l', $date));

    $businessHours = array();
    foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
        $businessHours[$weekday] = array(
            'enabled' => nebula_option('business_hours_' . $weekday . '_enabled'),
            'open' => nebula_option('business_hours_' . $weekday . '_open'),
            'close' => nebula_option('business_hours_' . $weekday . '_close')
        );
    }

    $days_off = array_filter(explode(', ', nebula_option('business_hours_closed')));
    if ( !empty($days_off) ){
        foreach ( $days_off as $key => $day_off ){
            $days_off[$key] = strtotime($day_off . ' ' . date('Y', $date));

            if ( date('N', $days_off[$key]) == 6 ){ //If the date is a Saturday
                $days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' -1 day');
            } elseif ( date('N', $days_off[$key]) == 7 ){ //If the date is a Sunday
                $days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' +1 day');
            }

            if ( date('Ymd', $days_off[$key]) == date('Ymd', $date) ){
                return false;
            }
        }
    }

    if ( $businessHours[$today]['enabled'] == '1' ){ //If the Nebula Options checkmark is checked for this day of the week.
        if ( !empty($general) ){
            return true;
        }

        $openToday = date('Gi', strtotime($businessHours[$today]['open']));
        $closeToday = date('Gi', strtotime($businessHours[$today]['close'])-1); //Subtract one second to ensure midnight represents the same day
        if ( date('Gi', $date) >= $openToday && date('Gi', $date) <= $closeToday ){
            return true;
        }
    }

    return false;
}

//If the business is open, return the time that the business closes today
function business_open_until(){
    if ( is_business_open() ){
        return nebula_option('business_hours_' . $weekday . '_close');
    }

    return false;
}


//Get the relative time of day
function nebula_relative_time($format=null){
    $override = apply_filters('pre_nebula_relative_time', false, $format);
    if ( $override !== false ){return $override;}

    if ( contains(date('H'), array('00', '01', '02')) ){
        $relative_time = array(
            'description' => array('early', 'night'),
            'standard' => array(0, 1, 2),
            'military' => array(0, 1, 2),
            'ampm' => 'am'
        );
    } elseif ( contains(date('H'), array('03', '04', '05')) ){
        $relative_time = array(
            'description' => array('late', 'night'),
            'standard' => array(3, 4, 5),
            'military' => array(3, 4, 5),
            'ampm' => 'am'
        );
    } elseif ( contains(date('H'), array('06', '07', '08')) ){
        $relative_time = array(
            'description' => array('early', 'morning'),
            'standard' => array(6, 7, 8),
            'military' => array(6, 7, 8),
            'ampm' => 'am'
        );
    } elseif ( contains(date('H'), array('09', '10', '11')) ){
        $relative_time = array(
            'description' => array('late', 'morning'),
            'standard' => array(9, 10, 11),
            'military' => array(9, 10, 11),
            'ampm' => 'am'
        );
    } elseif ( contains(date('H'), array('12', '13', '14')) ){
        $relative_time = array(
            'description' => array('early', 'afternoon'),
            'standard' => array(12, 1, 2),
            'military' => array(12, 13, 14),
            'ampm' => 'pm'
        );
    } elseif ( contains(date('H'), array('15', '16', '17')) ){
        $relative_time = array(
            'description' => array('late', 'afternoon'),
            'standard' => array(3, 4, 5),
            'military' => array(15, 16, 17),
            'ampm' => 'pm'
        );
    } elseif ( contains(date('H'), array('18', '19', '20')) ){
        $relative_time = array(
            'description' => array('early', 'evening'),
            'standard' => array(6, 7, 8),
            'military' => array(18, 19, 20),
            'ampm' => 'pm'
        );
    } elseif ( contains(date('H'), array('21', '22', '23')) ){
        $relative_time = array(
            'description' => array('late', 'evening'),
            'standard' => array(9, 10, 11),
            'military' => array(21, 22, 23),
            'ampm' => 'pm'
        );
    }

    if ( !empty($format) ){
        return $relative_time[$format];
    } else {
        return $relative_time;
    }
}

//Detect location from IP address using https://freegeoip.net/
function nebula_ip_location($data=null, $ip=false){
    if ( nebula_option('ip_geolocation') ){
        if ( empty($ip) ){
            $ip = $_SERVER['REMOTE_ADDR'];

            if ( empty($data) ){
                return true; //If passed with no parameters, simply check if Nebula Option is enabled
            }
        }

        if ( !empty($_SESSION['nebulageoip']) && !is_array($_SESSION['nebulageoip']) ){
            return false;
        }

        if ( empty($_SESSION['nebulageoip']) && nebula_is_available('http://freegeoip.net') ){
            $response = wp_remote_get('http://freegeoip.net/json/' . $ip);
            if ( is_wp_error($response) || !is_array($response) || strpos($response['body'], 'Rate limit') === 0 ){
                set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'http://freegeoip.net/json/')), 'Unavailable', 60*5); //5 minute expiration
                return false;
            }

            $ip_geo_data = $response['body'];
            $_SESSION['nebulageoip'] = $ip_geo_data;
        } else {
            $ip_geo_data = $_SESSION['nebulageoip'];
        }
        $ip_geo_data = json_decode($ip_geo_data);

        if ( !empty($ip_geo_data) ){
            switch ( str_replace(array(' ', '_', '-'), '', $data) ){
                case 'country':
                case 'countryname':
                    return $ip_geo_data->country_name;
                    break;
                case 'countrycode':
                    return $ip_geo_data->country_code;
                    break;
                case 'region':
                case 'state':
                case 'regionname':
                case 'statename':
                    return $ip_geo_data->region_name;
                    break;
                case 'regioncode':
                case 'statecode':
                    return $ip_geo_data->country_code;
                    break;
                case 'city':
                    return $ip_geo_data->city;
                    break;
                case 'zip':
                case 'zipcode':
                    return $ip_geo_data->zip_code;
                    break;
                case 'lat':
                case 'latitude':
                    return $ip_geo_data->latitude;
                    break;
                case 'lng':
                case 'longitude':
                    return $ip_geo_data->longitude;
                    break;
                case 'geo':
                case 'coordinates':
                    return $ip_geo_data->latitude . ',' . $ip_geo_data->longitude;
                    break;
                case 'timezone':
                    return $ip_geo_data->time_zone;
                    break;
                default:
                    return false;
                    break;
            }
        }
        return false;
    }
}

//Detect weather for Zip Code (using Yahoo! Weather)
//https://developer.yahoo.com/weather/
function nebula_weather($zipcode=null, $data=''){
    if ( nebula_option('weather') ){
        $override = apply_filters('pre_nebula_weather', false, $zipcode, $data);
        if ( $override !== false ){return $override;}

        if ( !empty($zipcode) && is_string($zipcode) && !ctype_digit($zipcode) ){ //ctype_alpha($zipcode)
            $data = $zipcode;
            $zipcode = nebula_option('postal_code', '13204');
        } elseif ( empty($zipcode) ){
            $zipcode = nebula_option('postal_code', '13204');
        }

        $weather_json = get_transient('nebula_weather_' . $zipcode);
        if ( empty($weather_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
            $yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text=' . $zipcode . ')';

            if ( !nebula_is_available('http://query.yahooapis.com/v1/public/yql?q=' . urlencode($yql_query) . '&format=json') ){
                trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
                return false;
            }
            $response = wp_remote_get('http://query.yahooapis.com/v1/public/yql?q=' . urlencode($yql_query) . '&format=json');
            if ( is_wp_error($response) ){
                set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'http://query.yahooapis.com/v1/public/yql')), 'Unavailable', 60*5); //5 minute expiration
                trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
                return false;
            }

            $weather_json = $response['body'];
            set_transient('nebula_weather_' . $zipcode, $weather_json, 60*15); //15 minute expiration
        }
        $weather_json = json_decode($weather_json);

        if ( !$weather_json || empty($weather_json) || empty($weather_json->query->results) ){
            trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
            return false;
        } elseif ( $data == '' ){
            return true;
        }

        switch ( str_replace(' ', '', $data) ){
            case 'json':
                return $weather_json;
                break;
            case 'reported':
            case 'build':
            case 'lastBuildDate':
                return $weather_json->query->results->channel->lastBuildDate;
                break;
            case 'city':
                return $weather_json->query->results->channel->location->city;
                break;
            case 'state':
            case 'region':
                return $weather_json->query->results->channel->location->region;
                break;
            case 'country':
                return $weather_json->query->results->channel->location->country;
                break;
            case 'location':
                return $weather_json->query->results->channel->location->city . ', ' . $weather_json->query->results->channel->location->region;
                break;
            case 'latitude':
            case 'lat':
                return $weather_json->query->results->channel->item->lat;
                break;
            case 'longitude':
            case 'long':
            case 'lng':
                return $weather_json->query->results->channel->item->long;
                break;
            case 'geo':
            case 'geolocation':
            case 'coordinates':
                return $weather_json->query->results->channel->item->lat . ',' . $weather_json->query->results->channel->item->lat;
                break;
            case 'windchill':
            case 'chill':
                return $weather_json->query->results->channel->wind->chill;
                break;
            case 'windspeed':
                return $weather_json->query->results->channel->wind->speed;
                break;
            case 'sunrise':
                return $weather_json->query->results->channel->astronomy->sunrise;
                break;
            case 'sunset':
                return $weather_json->query->results->channel->astronomy->sunset;
                break;
            case 'temp':
            case 'temperature':
                return $weather_json->query->results->channel->item->condition->temp;
                break;
            case 'condition':
            case 'conditions':
            case 'current':
            case 'currently':
                return $weather_json->query->results->channel->item->condition->text;
                break;
            case 'forecast':
                return $weather_json->query->results->channel->item->forecast;
                break;
            default:
                break;
        }
    }

    return false;
}

//Get metadata from Youtube or Vimeo
function vimeo_meta($id, $meta=''){return video_meta('vimeo', $id);}
function youtube_meta($id, $meta=''){return video_meta('youtube', $id);}
function video_meta($provider, $id){
    $override = apply_filters('pre_video_meta', false, $provider, $id);
    if ( $override !== false ){return $override;}

    $video_metadata = array(
        'origin' => nebula_url_components('basedomain'),
        'id' => $id,
        'error' => false
    );

    if ( !empty($provider) ){
        $provider = strtolower($provider);
    } else {
        $video_metadata['error'] = 'Video provider is required.';
        return $video_metadata;
    }

    //Get Transients
    $video_json = get_transient('nebula_' . $provider . '_' . $id);
    if ( empty($video_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
        if ( $provider == 'youtube' ){
            if ( !nebula_is_available('https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&part=snippet,contentDetails,statistics&key=' . nebula_option('google_server_api_key')) ){
                $video_metadata['error'] = 'Youtube video is unavailable.';
                return $video_metadata;
            }
            $response = wp_remote_get('https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&part=snippet,contentDetails,statistics&key=' . nebula_option('google_server_api_key'));
            if ( is_wp_error($response) ){
                set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'https://www.googleapis.com/youtube/v3/videos')), 'Unavailable', 60*5); //5 minute expiration
                $video_metadata['error'] = 'Youtube video is unavailable.';
                return $video_metadata;
            }

            $video_json = $response['body'];
        } elseif ( $provider == 'vimeo' ){
            if ( !nebula_is_available('http://vimeo.com/api/v2/video/' . $id . '.json') ){
                $video_metadata['error'] = 'Vimeo video is unavailable.';
                return $video_metadata;
            }
            $response = wp_remote_get('http://vimeo.com/api/v2/video/' . $id . '.json');
            if ( is_wp_error($response) ){
                $video_metadata['error'] = 'Vimeo video is unavailable.';
                return $video_metadata;
            }

            $video_json = $response['body'];
        }

        set_transient('nebula_' . $provider . '_' . $id, $video_json, 60*60); //1 hour expiration
    }
    $video_json = json_decode($video_json);

    //Check for errors
    if ( empty($video_json) ){
        if ( current_user_can('manage_options') || is_dev() ){
            if ( $provider == 'youtube' ){
                $video_metadata['error'] = 'A Youtube Data API error occurred. Make sure the Youtube Data API is enabled in the Google Developer Console and the server key is saved in Nebula Options.';
            } else {
                $video_metadata['error'] = 'A Vimeo API error occurred (A video with ID ' . $id . ' may not exist). Tracking will not be possible.';
            }
        }
        return $video_metadata;
    } elseif ( $provider == 'youtube' && !empty($video_json->error) ){
        if ( current_user_can('manage_options') || is_dev() ){
            $video_metadata['error'] = 'Youtube API Error: ' . $video_json->error->message;
        }
        return $video_metadata;
    } elseif ( $provider == 'youtube' && empty($video_json->items) ){
        if ( current_user_can('manage_options') || is_dev() ){
            $video_metadata['error'] = 'A Youtube video with ID ' . $id . ' does not exist.';
        }
        return $video_metadata;
    } elseif ( $provider == 'vimeo' && is_array($video_json) && empty($video_json[0]) ){
        $video_metadata['error'] = 'A Vimeo video with ID ' . $id . ' does not exist.';
    }

    //Build Data
    if ( $provider == 'youtube' ){
        $video_metadata['raw'] = $video_json->items[0];
        $video_metadata['title'] = $video_json->items[0]->snippet->title;
        $video_metadata['safetitle'] = str_replace(array(" ", "'", '"'), array("-", "", ""), $video_json->items[0]->snippet->title);
        $video_metadata['description'] = $video_json->items[0]->snippet->description;
        $video_metadata['thumbnail'] = $video_json->items[0]->snippet->thumbnails->high->url;
        $video_metadata['author'] = $video_json->items[0]->snippet->channelTitle;
        $video_metadata['date'] = $video_json->items[0]->snippet->publishedAt;
        $video_metadata['url'] = 'https://www.youtube.com/watch?v=' . $id;
        $start = new DateTime('@0'); //Unix epoch
        $start->add(new DateInterval($video_json->items[0]->contentDetails->duration));
        $duration_seconds = intval($start->format('H'))*60*60 + intval($start->format('i'))*60 + intval($start->format('s'));
    } elseif ( $provider == 'vimeo' ){
        $video_metadata['raw'] = $video_json[0];
        $video_metadata['title'] = $video_json[0]->title;
        $video_metadata['safetitle'] = str_replace(array(" ", "'", '"'), array("-", "", ""), $video_json[0]->title);
        $video_metadata['description'] = $video_json[0]->description;
        $video_metadata['thumbnail'] = $video_json[0]->thumbnail_large;
        $video_metadata['author'] = $video_json[0]->user_name;
        $video_metadata['date'] = $video_json[0]->upload_date;
        $video_metadata['url'] = $video_json[0]->url;
        $duration_seconds = strval($video_json[0]->duration);
    }
    $video_metadata['duration'] = array(
        'time' => intval(gmdate("i", $duration_seconds)) . gmdate(":s", $duration_seconds),
        'seconds' => $duration_seconds
    );

    return $video_metadata;
}

//Footer Widget Counter
function footer_widget_counter(){
    $footerWidgetCount = 0;
    if ( is_active_sidebar('First Footer Widget Area') ){
        $footerWidgetCount++;
    }
    if ( is_active_sidebar('Second Footer Widget Area') ){
        $footerWidgetCount++;
    }
    if ( is_active_sidebar('Third Footer Widget Area') ){
        $footerWidgetCount++;
    }
    if ( is_active_sidebar('Fourth Footer Widget Area') ){
        $footerWidgetCount++;
    }
    return $footerWidgetCount;
}