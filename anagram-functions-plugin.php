<?php
/*
Plugin Name: Anagram Custom Functions
Plugin URI: http://anagr.am
Description: Functions or Anagram
Author: Anagram
Version: 1.0
Author URI: http:/anagr.am
License: GPL2
*/


/* FRONT ONLY */



if (!is_admin()) {

	include_once('inc/Aqua-Resizer.php');
	include_once('inc/mobile_detect.php');
	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

	include_once('anagram-front.php');

}else{

	include_once('anagram-admin.php');

};


add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );






/*
	Change Howdy

*/
add_filter('gettext', 'change_howdy', 10, 3);

function change_howdy($translated, $text, $domain) {

    if (!is_admin() || 'default' != $domain)
        return $translated;

    if (false !== strpos($translated, 'Howdy'))
        return str_replace('Howdy', 'Welcome', $translated);

    return $translated;
}


/*
	Remove Customier from admin bar

*/
add_action( 'wp_before_admin_bar_render', 'anagram_before_admin_bar_render' );

function anagram_before_admin_bar_render()
{
    global $wp_admin_bar;

    $wp_admin_bar->remove_menu('customize');
}



/**
 * Add blank to all external links
 *
 * @param string $user
 * @param string $username
 * @param string $password
 */
add_filter('acf/format_value/type=wysiwyg', 'anagram_add_blank', 10, 3);
add_filter( 'the_content' , 'anagram_add_blank' );
// add_filter( 'comment_text' , 'mh_add_blank' );

function anagram_add_blank( $content ) {

// Regex to put all <a href="http://some-url-here/path/etc" into an array
$mh_url_regex = "/\<a\ href\=\"(http|https)\:\/\/[a-zA-Z0-9\-\.]+[a-zA-Z]{2,3}.*\"[\ >]/";

preg_match_all( $mh_url_regex , $content, $mh_matches );

// Go through that array and add target="_blank" to external links
for ( $mh_count = 0; $mh_count < count( $mh_matches[0] ); $mh_count++ )
        {
        $mh_old_url = $mh_matches[0][$mh_count];
        // $mh_new_url = str_replace( '" ' , '" target="_blank" ' , $mh_matches[0][$mh_count] );
        $mh_new_url = str_replace( '">' , '" target="_blank">' , $mh_matches[0][$mh_count] );

        // Array of destinations we don't want to apply the hack to.
        // Your home URL will get excluded but you can add to this array.
        // Partial matches work here, the more specific the better.

        $mh_ignore = array(
                home_url( '/' ),
                'wordpress.org/'
                );

        // Make the substitution on all links except the ignore list
        if( !anagram_array_find( $mh_old_url , $mh_ignore ) )
                $content = str_replace( $mh_old_url  , $mh_new_url , $content );
        }

return $content;
}

// Only see if the array element is contained in the string
function anagram_array_find( $needle , $haystack ) {
        if(!is_array($haystack)) return false;
        foreach ($haystack as $key=>$item) {
                // See if the item is in the needle
                if (strpos($needle, $item ) !== false) return true;
        }
        return false;
}

/**
 * If an email address is entered in the username box, then look up the matching username and authenticate as per normal, using that.
 *
 * @param string $user
 * @param string $username
 * @param string $password
 * @return Results of autheticating via wp_authenticate_username_password(), using the username found when looking up via email.
 */
function dr_email_login_authenticate( $user, $username, $password ) {
	if ( !empty( $username ) )

		$user = get_user_by('email', $username);
	if ( $user )
		$username = $user->user_login;

	return wp_authenticate_username_password( null, $username, $password );
}
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'dr_email_login_authenticate', 20, 3 );


/**
 * Modify the string on the login page to prompt for username or email address
 */
function username_or_email_login() {
	?><script type="text/javascript">
	// Form Label
	document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = 'Username or Email';

	// Error Messages
	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( 'username', 'Username or Email' );
	</script><?php
}
add_action( 'login_form', 'username_or_email_login' );




/*Front and backend*/

add_action('after_setup_theme', 'anagram_remove_admin_bar');

function anagram_remove_admin_bar() {
	if (!current_user_can( 'edit_others_pages' ) && !is_admin()) {
	  show_admin_bar(false);
	}
}


/**
 * Redirect back to homepage and not allow access to
 * WP admin for Subscribers.
 */
function anagram_redirect_admin(){
	if ( ! defined('DOING_AJAX') && ! current_user_can('edit_posts') ) {
		wp_redirect( site_url() );
		exit;
	}
}
//add_action( 'admin_init', 'anagram_redirect_admin' );

//Remove things form Admin bar
add_action( 'admin_bar_menu', 'anagram_remove_wp_logo', 999 );

function anagram_remove_wp_logo( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
}








