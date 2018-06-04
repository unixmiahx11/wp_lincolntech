<?php
/**
 * =============================================================================
 * WORDPRESS ADMINISTRATION AREA
 * =============================================================================
 */


/**
 * load in custom .css files for the /wp-admin page for custom styling
 * @see http://torquemag.io/2016/08/customize-wordpress-backend-clients/
 */
function my_login_stylesheet() {
  //wp_enqueue_style( 'custom-login', get_template_directory_uri() . '/css/wp-login.min.css' );
  // wp_enqueue_script( 'custom-login', get_template_directory_uri() . '/custom-login.js' );
}
add_action( 'login_enqueue_scripts', 'my_login_stylesheet' );


/**
 * update the URL and the mouseover text for your /wp-login page
 * @see https://codex.wordpress.org/Customizing_the_Login_Form#mw-content-text
 */
function my_login_logo_url() {                                                  // update the href value for the anchor
  return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {                                            // update the title value for the anchor
  return 'Return to the Lincoln Tech homepage.';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );


/**
 * rewrite the "Thank you for creating with WordPress." text
 * @see http://torquemag.io/2016/08/customize-wordpress-backend-clients/
 */
function rwr__admin_footer_message() {
  echo '<a href="http://www.jellyfish.net" target="_blank">Jellyfish Digital Marketing</a>';
}
add_filter( 'admin_footer_text', 'rwr__admin_footer_message' );


/**
 * show (x) amount of pages when viewing "Pages" in /wp-admin area
 * @see http://torquemag.io/2016/08/customize-wordpress-backend-clients/
 */
add_filter( 'get_user_metadata', 'pages_per_page', 10, 4 );
function pages_per_page( $check, $object_id, $meta_key, $single ) {
  if( 'edit_page_per_page' == $meta_key )
    return 50;                                                                  // define the amount of pages you'd wish to show on each paginated "page"
  return $check;
}

/**
 * Add a parent menu item for Components in the admin menu
 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
 */
function add_admin_menu_component(){
  add_menu_page('Components', 'Components', 'edit_pages', 'components', '', 'dashicons-index-card', '5');
}
add_action('admin_menu', 'add_admin_menu_component');


/**
 * create a separator above "Media" to separate out post types visually
 * @see https://tommcfarlin.com/add-a-separator-to-the-wordpress-menu/
 */
function add_admin_menu_separator( $position ) {
  global $menu;
  $menu[ 9 ] = array(
    0 =>  '',
    1 =>  'read',
    2 =>  'separator' . $position,
    3 =>  '',
    4 =>  'wp-menu-separator'
  );
}
#add_action( 'admin_menu', 'add_admin_menu_separator' );


/**
 * Remove comments in its entirety
 * @see http://kalscheur.info/index.php/2016/07/15/quickly-easily-remove-comments-backend-wordpress/
 */
add_action( 'admin_menu', 'nk_remove_admin_menus' );
function nk_remove_admin_menus() {                                              // remove "Comments" from the admin menu
  remove_menu_page( 'edit-comments.php' );
}

add_action( 'init', 'nk_remove_comment_support', 100 );
function nk_remove_comment_support() {                                          // remove commenting from post and pages
  remove_post_type_support( 'post', 'comments' );
  remove_post_type_support( 'page', 'comments' );
}

add_action( 'wp_before_admin_bar_render', 'nk_remove_comments_admin_bar' );
function nk_remove_comments_admin_bar() {                                       // remove from the admin bar
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('comments');
}

/**
 * Remove the default "Posts"
 * @see https://codex.wordpress.org/Function_Reference/remove_menu_page
 */
add_action( 'admin_menu', 'remove_posts', 99);
function remove_posts() {
  remove_menu_page( 'edit.php' );
}
/**
 * remove the "Tools" menu from the wp-admin
 * @see https://codex.wordpress.org/Function_Reference/remove_menu_page
 */
add_action( 'admin_menu', 'remove_tools', 99 );
function remove_tools() {
  remove_menu_page( 'tools.php' );
}


/**
 * Code to add the icon styles to the admin
 */
/*function insert_icon_css() {
    $theme = wp_get_theme();
    $file = $theme->theme_root.'/'.$theme->template.'/css/style.min.css';
    $content = file_get_contents($file);
    $start = strpos($content,'i[class*=icon--]');
    $t = strrpos($content,'i[class*=icon--][class*=dark-grey]{');
    $end = strpos($content,'}}',$t);
    echo '<style type="text/css">',substr($content,$start,($end-$start+2)),'</style>';
}
add_action('admin_head', 'insert_icon_css');*/

/**
 * Remove admin toolbar items easily
 * @see https://digwp.com/2016/06/remove-toolbar-items/
 * @see https://codex.wordpress.org/Function_Reference/remove_node
 */
function remove_toolbar_nodes($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('customize');
    $wp_admin_bar->remove_node('customize-background');
    $wp_admin_bar->remove_node('customize-header');
    $wp_admin_bar->remove_node('themes');
    $wp_admin_bar->remove_node('new-post');
    $wp_admin_bar->remove_node('new-link');
    $wp_admin_bar->remove_node('new-media');
}
add_action('admin_bar_menu', 'remove_toolbar_nodes', 999);

/**
 * Add ability to upload SVGs
 */
function jf_upload_types($existing_mimes = array()){
    $existing_mimes['svg'] = 'image/svg+xml';
    return $existing_mimes;
}
add_filter('upload_mimes', 'jf_upload_types');


/**
 * Add category functionality to pages
 */
function jf_category_tag_page() {
    register_taxonomy_for_object_type( 'post_tag', 'page' );
    register_taxonomy_for_object_type( 'category', 'page' );
}
add_action('init', 'jf_category_tag_page');
