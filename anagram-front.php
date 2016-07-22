<?php



/**
 * anagram_resize_image function.
 *
 * @access public
 * @param mixed $imgargs
 * @return void
 * anagram_resize_image(array(
		'size' => 'full',
		'url' => false,
		'width' => 9999,
		'height' => 9999,
		'crop' => false,
		'single' => false,
		'upscale' => false
	))
 */
function anagram_resize_image($imgargs){
	global $post;
	/* Define the array of defaults*/
	$defaults = array(
		'image_id' => get_post_thumbnail_id(),
		'size' => 'full',
		'url' => false,
		'width' => null,
		'height' => null,
		'crop' => false,
		'single' => false,
		'upscale' => false,
		'caption' => false,
		'linked' => false,
		'align' => 'none',
		'class' => '',
	);

	/**
	 * Parse incoming $args into an array and merge it with $defaults
	 */
	$args = wp_parse_args( $imgargs, $defaults );

	$img_url = wp_get_attachment_url( $args['image_id'] ); //get full URL to image (use "large" or "medium" if the images too big)

	if(empty($img_url)) return;

	$image = aq_resize( $img_url, $args['width'], $args['height'], $args['crop'], $args['single'], $args['upscale'] ); //resize & crop the image

	//var_dump( $image  );

	if(empty($image) ) return;

	$output ='';
	$class='align'.$args['align'].' '.$args['class'];

	$title = get_post( $args['image_id'] )->post_title;
	$caption = get_post( $args['image_id'] )->post_excerpt;
	if(!empty($caption) && $args['caption'])$class .= ' wp-caption';

	$linkedimg = wp_get_attachment_image_src( $args['image_id'], 'large' );


	if($args['url']){
		$output .= $image[0];
	}else{
		$output .= '<figure class="'.$class.'">';
		if($args['linked'])$output .= '<a href="'.$linkedimg[0].'" rel="lightbox">';
		$output .= '<img src="'.$image[0].'" width="'.$image[1].'" title="'.htmlentities($title).'" height="'.$image[2].'"/>';
		if($args['linked'])$output .= '</a>';
		if(!empty($caption) && $args['caption']) $output .= '<figcaption class="wp-caption-text"><strong>'.htmlentities($title).'</strong><br/> '. htmlentities($caption) . '</figcaption>';
		$output .= '</figure>';
	};


	return $output;

}

/* Filter Archive title*/

add_filter( 'get_the_archive_title', function ( $title ) {

    if( is_category() ) {

        $title = sprintf( single_cat_title( '', false ) );

	 } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $tax = get_taxonomy( get_queried_object()->taxonomy );
        /* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
        $title = single_term_title( '', false );
    }else{
        $title = get_the_title( get_option( 'page_for_posts' ) );
    }

    return $title;

});



 /**
 * Add Pagination
 *
 */

//Default WORDPRESS pagation USE INSTEAD

/*
global $wp_query;

$big = 999999999; // need an unlikely integer

echo paginate_links( array(
	'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	'format' => '?paged=%#%',
	'current' => max( 1, get_query_var('paged') ),
	'total' => $wp_query->max_num_pages,
	'show_all' => true
) );


*/






function anagram_base_paginate() {

global $wp_query;

$big = 999999999; // need an unlikely integer

$pages = paginate_links( array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '?paged=%#%',
        'prev_next'          => True,
        'prev_text'          => __('«'),
		'next_text'          => __('»'),
		'show_all'           => true,
        'current' => max( 1, get_query_var('paged') ),
        'total' => $wp_query->max_num_pages,
        'type'  => 'array',
    ) );
    if( is_array( $pages ) ) {
        $paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
        echo '<div class="pagination-wrap"><ul class="pagination">';
        foreach ( $pages as $page ) {
                echo "<li>$page</li>";
        }
       echo '</ul></div>';
        }
}

/**
 * Fix Gravity Form Tabindex Conflicts
 * http://gravitywiz.com/fix-gravity-form-tabindex-conflicts/
 */
add_filter( 'gform_tabindex', 'anagram_gform_tabindexer', 10, 2 );
function anagram_gform_tabindexer( $tab_index, $form = false ) {
    $starting_index = 1000; // if you need a higher tabindex, update this number
    if( $form )
        add_filter( 'gform_tabindex_' . $form['id'], 'gform_tabindexer' );
    return GFCommon::$tab_index >= $starting_index ? GFCommon::$tab_index : $starting_index;
}




function get_share_icons($permalink, $title='', $class='btn sm', $text='content'){
$shareicons = '<a onClick="MyWindow=window.open(\'http://twitter.com/home?status=Checkout this '.$text.' '.$title.' ('.$permalink.')\',\'MyWindow\',\'width=600,height=400\'); return false;" title="Share on Twitter" target="_blank" class="'.$class.'"><i class="fa  fa-lg fa-twitter fa-fw"></i></a>

			<a href="http://www.facebook.com/share.php?u=$permalink" onClick="window.open(\'http://www.facebook.com/sharer.php?u=\'+encodeURIComponent(\''.$permalink.'\')+\'&title\'+encodeURIComponent(\''.$title.'\'),\'sharer\', \'toolbar=no,width=550,height=550\'); return false;" title="Share on Facebook" target="_blank" class="'.$class.'"><i class="fa  fa-lg fa-facebook  fa-fw"></i></a>



			<a onClick="MyWindow=window.open(\'http://pinterest.com/pin/create/button/\',\'MyWindow\',\'width=600,height=400\'); return false;" count-layout="none" target="_blank" class="'.$class.'"><i class="fa  fa-lg fa-pinterest fa-fw"></i></a>

			<a href="https://plus.google.com/share?url='.$permalink.'" onclick="javascript:window.open(this.href,
  \'\', \menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" title="Share on Google+" target="_blank" class="'.$class.'"><i class="fa  fa-lg fa-google-plus fa-fw"></i></a>

			<a href="mailto:?subject=I wanted you to see this site&amp;body=Check out this '.$text.' '.$permalink.'." title="Share by Email"  count-layout="none" class="'.$class.'"><i class="fa  fa-lg fa-envelope-o fa-fw"></i></a>';

	     return $shareicons;

};



/**
 * Used for debugging, uses var_dump to output a variable wrapped in a pre tag.
 * Optionally, will stop PHP execution if $die is set to TRUE. Does nothing iif the current
 * user does not have the right $capability (defaults to 'manage_options').
 *
 * @param mixed $var
 * @param bool $die
 * @param string $capability
 */
function mapi_var_dump($var = NULL, $die = FALSE, $capability = 'manage_options') {
	if(current_user_can($capability)) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		if($die) {
			die;
		}
	}
}




function debug_to_console( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}


function clean_excerpt($text, $after = null , $length = 55 ) {
                $text = apply_filters('the_content', $text);
                $text = str_replace('\]\]\>', ']]&gt;', $text);
                $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
                $text = strip_tags($text, '<p><em><i><b><strong>');
                $words = explode(' ', $text, $length + 1);
                if (count($words)> $length) {
                        array_pop($words);
                        if(!$after) $after = new_excerpt_more();
                        array_push($words, $after);
                        $text = implode(' ', $words);
                }
        return $text;
}




/*
*	Replaces the excerpt "more" text by a link
*/

function excerpt_read_more_link($output) {
       global $post;
	return $output . '<a class="moretag" href="'. get_permalink($post->ID) . '"> Read more....</a>';
}
//add_filter('the_excerpt', 'excerpt_read_more_link');


function new_excerpt_more() {
       global $post;
	return '<a class="moretag" href="'. get_permalink($post->ID) . '"> Read more....</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');


/*
*	Display Caption
*/
function be_display_image_and_caption($size='large',$align='none') {
	$thumb_id = get_post_thumbnail_id();
	//if( $align=='left' || $align=='right'){ $size= 'small-image';};
	$caption = get_post( $thumb_id )->post_excerpt;
	$image = wp_get_attachment_image_src( $thumb_id, $size );
	$class='align'.$align;
	//$timthumb_url =  wp_get_attachment_image_src(  $image_id , array( 240, 180, 'bfi_thumb' => true ) );
	if(!empty($caption))$class .= ' wp-caption';
	//Add this back once sizes work
	//if( $align=='left' || $align=='right'){
	//	echo '<figure style="width:'.$image[1].'px" class="'.$class.'">';
		//}else{
			echo '<figure class="'.$class.'">';
		//};

	echo '<img src="'.$image[0].'">';
	if(!empty($caption)){ echo '<figcaption class="wp-caption-text">' . $caption . '</figcaption>'; };
	echo '</figure>';
}


/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 */
function anagram_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() )
		return $title;

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $sep $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $sep " . sprintf( __( 'Page %s', '_tk' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'anagram_wp_title', 10, 2 );




//Page Slug Body Class
function add_slug_body_class( $classes ) {
	global $post;
	if ( isset( $post ) ) {
	$classes[] = $post->post_type . '-' . $post->post_name;
	}
	return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );





	/**
	 * anagram_get_post_types
	 */
function anagram_get_post_types($items='', $listtype = 'text' ) {
 	$z=1;
 	if( empty($items) )  return false;
 	$count = count($items);
    $theitems = '';
		foreach( $items as $item):
		    $theitems .=  '<a href="'.get_permalink($item->ID).'">'.$item->post_title.'</a>';
			if($listtype == 'text'){
				if($z<$count-1&&!($z>$count)){ $theitems .= ', ';} elseif($z!=$count) { $theitems .= ' & ';};
			}else{
				$theitems .= '<br/>';
			};

			$z++;
		endforeach;

 return $theitems;

}

	/**
	 * get_custom_taxonomy
	 */
function get_custom_taxonomy($taxonomy ='', $separator = ' ', $type='slug', $theid = '' ) {
	global $post;
	$theid = $taxonomy=='' ? $post->ID : $theid;
	$taxonomy = $taxonomy=='' ? 'category' : $taxonomy;
   $terms = get_the_terms($theid , $taxonomy);

	if (  $terms && ! is_wp_error(  $terms ) ) :

    $thetax = array();
    foreach($terms as $terms) {    // concate
        $thetax[] =  $terms->$type;
    }

    return join( $separator, $thetax );

    endif;


}


/**
 * Given a string containing any combination of YouTube and Vimeo video URLs in
 * a variety of formats (iframe, shortened, etc), each separated by a line break,
 * parse the video string and determine it's valid embeddable URL for usage in
 * popular JavaScript lightbox plugins.
 *
 * In addition, this handler grabs both the maximize size and thumbnail versions
 * of video images for your general consumption. In the case of Vimeo, you must
 * have the ability to make remote calls using file_get_contents(), which may be
 * a problem on shared hosts.
 *
 * Data gets returned in the format:
 *
 * array(
 *   array(
 *     'url' => 'http://path.to/embeddable/video',
 *     'thumbnail' => 'http://path.to/thumbnail/image.jpg',
 *     'fullsize' => 'http://path.to/fullsize/image.jpg',
 *   )
 * )
 *
 * @param       string  $videoString
 * @return      array   An array of video metadata if found
 *
 * @author      Corey Ballou http://coreyballou.com
 * @copyright   (c) 2012 Skookum Digital Works http://skookum.com
 * @license
 */
function parseVideos($videoString = null)
{
    // return data
    $videos = array();

    if (!empty($videoString)) {

        // split on line breaks
        $videoString = stripslashes(trim($videoString));
        $videoString = explode("\n", $videoString);
        $videoString = array_filter($videoString, 'trim');

        // check each video for proper formatting
        foreach ($videoString as $video) {

            // check for iframe to get the video url
            if (strpos($video, 'iframe') !== FALSE) {
                // retrieve the video url
                $anchorRegex = '/src="(.*)?"/isU';
                $results = array();
                if (preg_match($anchorRegex, $video, $results)) {
                    $link = trim($results[1]);
                }
            } else {
                // we already have a url
                $link = $video;
            }

            // if we have a URL, parse it down
            if (!empty($link)) {

                // initial values
                $video_id = NULL;
                $videoIdRegex = NULL;
                $video_source = NULL;
                $results = array();

                // check for type of youtube link
                if (strpos($link, 'youtu') !== FALSE) {

						 $url_string = parse_url($link, PHP_URL_QUERY);
						 parse_str($url_string, $args);
						 $video_source = 'youtube';
						 $video_id = isset($args['v']) ? $args['v'] : false;
                }
                // handle vimeo videos
                else if (strpos($video, 'vimeo') !== FALSE) {
                 $video_source = 'vimeo';
                    if (strpos($video, 'player.vimeo.com') !== FALSE) {
                        // works on:
                        // http://player.vimeo.com/video/37985580?title=0&amp;byline=0&amp;portrait=0
                        $videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
                    } else {
                        // works on:
                        // http://vimeo.com/37985580
                        $videoIdRegex = '/vimeo.com\/([0-9]+)\??/i';
                    }

                    if ($videoIdRegex !== NULL) {
                        if (preg_match($videoIdRegex, $link, $results)) {
                            $video_id = $results[1];

                            // get the thumbnail
                           /* try {
                                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$video_id.php"));
                                if (!empty($hash) && is_array($hash)) {
                                    $video_str = 'http://vimeo.com/moogaloop.swf?clip_id=%s';
                                    $thumbnail_str = $hash[0]['thumbnail_small'];
                                    $fullsize_str = $hash[0]['thumbnail_large'];
                                } else {
                                    // don't use, couldn't find what we need
                                    unset($video_id);
                                }
                            } catch (Exception $e) {
                                unset($video_id);
                            }*/
                        }
                    }
                }

                // check if we have a video id, if so, add the video metadata
                if (!empty($video_id)) {
                    // add to return
                    $videos = array(
                    	'video_id' => $video_id,
                        'source' => $video_source
                    );
                   /* $videos[] = array(
                    	'video_id' => $video_id,
                        'url' => sprintf($video_str, $video_id),
                        'thumbnail' => sprintf($thumbnail_str, $video_id),
                        'fullsize' => sprintf($fullsize_str, $video_id)
                    );*/
                }
            }

        }

    }

    // return array of parsed videos
    return $videos;
}



// redirect /?s to /search/
// http://txfx.net/wordpress-plugins/nice-search/
function anagram_nice_search_redirect() {
	if (is_search() && strpos($_SERVER['REQUEST_URI'], '/wp-admin/') === false && strpos($_SERVER['REQUEST_URI'], '/search/') === false) {
		wp_redirect(home_url('/search/' . str_replace(array(' ', '%20'), array('+', '+'), urlencode(get_query_var( 's' )))), 301);
	    exit();
	}
}
add_action('template_redirect', 'anagram_nice_search_redirect');

function anagram_search_query($escaped = true) {
	$query = apply_filters('anagram_search_query', get_query_var('s'));
	if ($escaped) {
    	$query = esc_attr($query);
	}
 	return urldecode($query);
}
add_filter('get_search_query', 'anagram_search_query');



//Add analytics to head if setup in site options
function anagram_google_analytics() {
if ( class_exists( 'Acf' ) ) {
	$anagram_google_analytics_id = get_field('google_analytics', 'options');
	if (get_field('google_analytics', 'options')) {  ?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?php echo $anagram_google_analytics_id ?>', 'auto');
  ga('send', 'pageview');

</script>


<?php	}
	}//if ACF is active
}
add_action('wp_head', 'anagram_google_analytics');


/* Clean up header  */

remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'start_post_rel_link' );
remove_action( 'wp_head', 'index_rel_link' );
remove_action( 'wp_head', 'adjacent_posts_rel_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );



//Custom Login Screen
function anagram_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'anagram_login_logo_url' );

function anagram_login_logo_url_title() {
    return get_option('blogname');
}
add_filter( 'login_headertitle', 'anagram_login_logo_url_title' );


//Redirect on login
function anagram_redirect_to_front_page() {
	global $redirect_to;
	if (!isset($_GET['redirect_to'])) {
		$redirect_to = get_option('siteurl');
	}
}
add_action('login_form', 'anagram_redirect_to_front_page');

//Redirect on logout
function anagram_redirect_logout_front_page() {
	wp_redirect(get_option('siteurl'));
	die();
}
add_action('wp_logout', 'anagram_redirect_logout_front_page');

// Custom Login Logo //
function anagram_custom_login_logo() {
    echo '<style type="text/css">
	.login h1 a{
background:url("'.plugin_dir_url( __FILE__ ).'img/admin-login-logo.png") no-repeat scroll center top transparent !important;
outline:none;
width:320px;
height: 60px;
}
    </style>'."\n";
}
add_action('login_head', 'anagram_custom_login_logo');


 // add a favicon for your login
function anagram_login_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="'.plugin_dir_url( __FILE__ ).'img/favicon.ico" />';
}
add_action('login_head', 'anagram_login_favicon');

