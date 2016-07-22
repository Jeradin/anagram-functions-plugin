<?php


	//Add options page to ACF 5
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'page_title' 	=> 'Global Options',
		'menu_title'	=> 'Global Options',
		'menu_slug' 	=> 'global-options',
		'position'      => 3,
		//'capability'	=> 'edit_posts',
		//'redirect'		=> false
	));
}

/**
 * Hide all update notices except for admin
 */
function hide_update_notice_to_all_but_admin_users()
{
    if (!current_user_can('update_core')) {
        remove_action( 'admin_notices', 'update_nag', 3 );
    }
}
add_action( 'admin_notices', 'hide_update_notice_to_all_but_admin_users', 1 );



/*
 * Disable theme switch
 */
add_action( 'admin_init', 'slt_lock_theme' );
function slt_lock_theme() {
global $submenu, $userdata;
if ( $userdata->ID != 1 ) {
unset( $submenu['themes.php'][5] );
unset( $submenu['themes.php'][15] );
}
}

// Add main menu to top menu
if ( ! function_exists( 'toplevel_admin_menu_nav_menus' ) ) {
function toplevel_admin_menu_nav_menus(){
    add_menu_page( 'Menus', 'Menus', 'edit_theme_options', 'nav-menus.php', '', 'dashicons-networking', 6 );
    add_submenu_page('nav-menus.php', 'All Menus', 'All Menus', 'edit_theme_options', 'nav-menus.php' );
   // add_submenu_page('nav-menus.php', 'Add New', 'Add New', 'edit_theme_options', '?action=edit&menu=0' );
    // add_submenu_page('nav-menus.php', 'Locations', 'Locations', 'edit_theme_options', '?action=locations' );
	$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
	foreach ( $menus as $menu ) {
	add_submenu_page(
          'nav-menus.php'
        , esc_attr(ucwords($menu->name))
        , esc_attr(ucwords($menu->name))
        , 'edit_theme_options'
        , 'nav-menus.php?action=edit&amp;menu='.$menu->term_id
        , ''
    );
    }
  }
add_action( 'admin_menu', 'toplevel_admin_menu_nav_menus' );
}


// Edit admin menu
add_action( 'admin_menu', 'anagram_admin_menus' );
function anagram_admin_menus() {
		if (!current_user_can('manage_options')) {

		   //remove_menu_page( 'edit-comments.php' );
		    //remove_menu_page('edit.php');
		    remove_menu_page('tools.php'); // Tools

			remove_menu_page( 'themes.php' );
			//remove_submenu_page( 'themes.php', 'nav-menus.php' );

		}
		//add_menu_page('Nav Menus','Nav Menus', 'edit_theme_options', 'nav-menus.php', '','dashicons-editor-justify', 50);

}
// Removes Comments from post and pages
add_action('init', 'remove_comment_support', 100);

function remove_comment_support() {
    //remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}
// Removes Comments from admin bar
function anagram_admin_bar_render() {
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('comments');
}
//add_action( 'wp_before_admin_bar_render', 'anagram_admin_bar_render' );



//Allow gform for editors
function add_gf_cap()
{
    $role = get_role( 'editor' );
    $role->add_cap( 'gform_full_access' );
    // add $cap capability to this role object
	$role->add_cap( 'edit_theme_options' );
}

add_action( 'admin_init', 'add_gf_cap' );


// Move Yoast to bottom
function yoasttobottom() {
	return 'low';
}
add_filter( 'wpseo_metabox_prio', 'yoasttobottom');







/*
 * Dashboard customization
 */
//hook the administrative header output
// Create the function to use in the action hook

function anagram_remove_dashboard_widgets() {
		global $wp_meta_boxes;
		// Main column (left):
unset($wp_meta_boxes['dashboard']['normal']['high']['dashboard_browser_nag']);
//$wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']
unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);


	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
	//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

// Hoook into the 'wp_dashboard_setup' action to register our function

add_action('wp_dashboard_setup', 'anagram_remove_dashboard_widgets' );


// Add all custom post types to the "Right Now" box on the Dashboard
add_action( 'dashboard_glance_items' , 'ucc_right_now_content_table_end' );

function ucc_right_now_content_table_end() {
  $post_types = get_post_types( array( 'show_in_nav_menus' => true, '_builtin' => false ), 'objects' );

  foreach ( $post_types as $post_type => $post_type_obj ){
                $num_posts = wp_count_posts( $post_type );
                if ( $num_posts && $num_posts->publish ) {
                        printf(
                                '<li class="%1$s-count"><a href="edit.php?post_type=%1$s">%2$s %3$s</a></li>',
                                $post_type,
                                number_format_i18n( $num_posts->publish ),
                                $post_type_obj->label
                        );
                }
        }

};



//Editor double line break
function change_mce_options($init){
    $init["forced_root_block"] = false;
    $init["force_br_newlines"] = true;
    $init["force_p_newlines"] = false;
    $init["convert_newlines_to_brs"] = true;
    return $init;
}
add_filter('tiny_mce_before_init','change_mce_options');




/**
 * Modify the "Enter title here" text when adding new CPT, post or page
*/
function rc_change_default_title( $title ){
     $screen = get_current_screen();

     if  ( 'artist' == $screen->post_type ) {
          $title = 'Enter Artist Name';
     }
     return $title;
}
add_filter( 'enter_title_here', 'rc_change_default_title' );






/*
 * Dashboard customization
 */



//custom Admin footer
function anagram_footer_admin () {
  echo 'Powered by <a href="http://anagr.am">Anagram</a> built on <a href="http://WordPress.org">WordPress</a>.';
}
add_filter('admin_footer_text', 'anagram_footer_admin');

// add a favicon for your admin

function admin_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="'.get_bloginfo('template_directory').'/img/anagram/favicon.ico" />';
}
add_action('admin_head', 'admin_favicon');



