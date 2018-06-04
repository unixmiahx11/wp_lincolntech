<?php
/**
 * =============================================================================
 * WORDPRESS CORE CUSTOMIZATIONS
 * =============================================================================
 */

/**
 * support PHP parsing in sidebar text widgets
 * @author Qaiser Bashir
 */
function plug_text_replace( $text ) {
  ob_start();
  eval( '?>'. $text );                                                          // this will convert php code from the widget
  $text = ob_get_contents();                                                    // the third parameter of add_filter is used to specify the order in which
  ob_end_clean();
  return $text;                                                                 // the functions associated with a particular action are executed.
}
add_filter( 'widget_text', 'plug_text_replace', 1 );                            // lower numbers correspond with earlier PHP execution


/**
 * change the post menu to article
 * @see https://paulund.co.uk/change-posts-text-in-admin-menu
 */
/*function change_post_menu_text() {
  global $menu;
  global $submenu;
  // change menu item
  $menu[5][0] = 'Blogs';
  // change post submenu
  $submenu['edit.php'][5][0] = 'Blogs';
  $submenu['edit.php'][10][0] = 'Add Blog';
  $submenu['edit.php'][16][0] = 'Blog Tags';
}
add_action( 'admin_menu', 'change_post_menu_text' );*/


/**
 * ajax loader class registration
 * @author Qaiser Bashir
 */
/*require get_template_directory() . '/custom/php/load-more-posts/load-more-posts.php';
new load_more_posts();
$load_more_posts = new load_more_posts();*/


/**
 * custom menu output which allows output of different layers of menus
 * @author Qaiser Bashir
 */
/*require get_template_directory() . '/custom/php/menu-arrays/navigation-arrays.php';*/


/**
 * Disable search functionality for unprivileged users
 *
 * @author Michael Brose
 */
function disable_public_search($query, $error = true) {
    if ($query->is_search && $query->is_main_query()) {
        unset($_GET['s']);
        unset($_POST['s']);
        unset($_REQUEST['s']);
        unset($query->query['s']);
        $query->set('s', '');
        $query->is_search = false;
        $query->query_vars['s'] = false;
        $query->query['s'] = false;
        $query->set_404();
        status_header(404);
        nocache_headers();
    }
}
add_action('parse_query', 'disable_public_search');
add_filter('get_search_form', '__return_null', 999);

/**
 * Disable the WordPress REST API
 *
 * @author Michael Brose
 * @return WP_Error
 */
function disable_rest_api($access) {
    return new WP_Error( 'rest_api_disabled', __( 'The REST API feature has been disabled.', 'disable-json-api'), array('status' => rest_authorization_required_code()));
}
add_filter('rest_authentication_errors', 'disable_rest_api');

/**
 * Returns an array of the first hierachy menu items from the main navigation menu based off the parent or child ID
 * 
 * @author Michael Brose
 * @param int $menu_id The menu ID
 * @return array Menu items that is an array which contains title and url
 */
function get_submenu_from_id($menu_id) {
    $base = wp_get_nav_menu_items('Main Navigation');
    $parent_id = null;
    $return = array();
    $menu_array = array();
    foreach ($base as $m) {
        if ($m->object_id == $menu_id) {
            // Menu is on the root level
            if ($m->menu_item_parent == 0) {
                $parent_id = $m->ID;
                break;
            } else { // Child ID, select the parent
                $parent_id = $m->menu_item_parent;
                break;
            }
        }
    }
    foreach ($base as $m) {
        if ($m->ID == $parent_id) {
            $return['parent'] = array(
                'url' => $m->url,
                'title' => $m->title,
            );
        }
        if ($m->menu_item_parent == $parent_id) {
            // Get the children
            $return['children'][] = array(
                'url' => $m->url,
                'title' => $m->title,
            );
        }
    }
    return $return;
}

/**
 * Truncates a string of text based on characters
 * 
 * @author Michael Brose
 * @param string $string The inputted string to be wrapped
 * @param int $length The length which a string will be wrapped to
 * @return string The newly truncated string
 */
function truncate_string($string, $length = 100) {
    $string = strip_tags(html_entity_decode($string)); // Decode entities and strip tags
    if (strlen($string) > $length) {
        $string = wordwrap($string, $length, '|||');
        $string = substr($string, 0, strpos($string, '|||'));
        $string = htmlentities($string).'&hellip;';
    }
    return $string;
}

/**
 * Replaces the [caption] shortcode with the [figure] shortcode before rendering
 */
function replace_caption_with_figure($value, $post_id, $field) {
    if (strpos($value,'[caption') !== false) {
        $regex = '/\[caption([^\]]+)\]([^<]*)<img.+?src=[\"\'](.+?)[\"\'](.+?alt=[\"\'](.+?)[\"\'])?.*?>([^\w]*)([^\[]*)\[\/caption\]/';
        $replace = '[figure image="$3" alt="$5"]$7[/figure]';
        $value = preg_replace($regex, $replace, $value);
    }
    return $value;
}
add_filter('acf/format_value/type=wysiwyg', 'replace_caption_with_figure', 10, 3);

/**
 * Return an ID of an attachment by searching the database with the file URL.
 *
 * First checks to see if the $url is pointing to a file that exists in
 * the wp-content directory. If so, then we search the database for a
 * partial match consisting of the remaining path AFTER the wp-content
 * directory. Finally, if a match is found the attachment ID will be
 * returned.
 *
 * @param string $url The URL of the image (ex: http://mysite.com/wp-content/uploads/2013/05/test-image.jpg)
 * 
 * @return int|null $attachment Returns an attachment ID, or null if no attachment is found
 *
 * @see https://frankiejarrett.com/2013/05/get-an-attachment-id-by-url-in-wordpress/
 */
function get_attachment_id_by_url( $url ) {
	// Split the $url into two parts with the wp-content directory as the separator
	$parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

	// Get the host of the current site and the host of the $url, ignoring www
	$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
	$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

	// Return nothing if there aren't any $url parts or if the current host and $url host do not match
	if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
		return;
	}

	// Now we're going to quickly search the DB for any attachment GUID with a partial path match
	// Example: /uploads/2013/05/test-image.jpg
	global $wpdb;

	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );

	// Returns null if no attachment is found
	return $attachment[0];
}
/**
 * Force a 404 page when a custom post type is viewed from a non-logged in user
 */
function force_404() {
    global $wp_query;
    $post_types = get_post_types(['public' => true], 'names');
    if (!$wp_query->have_posts() ||
        (!is_user_logged_in() && 
        array_key_exists('post_type', $wp_query->query) &&
        in_array($wp_query->query['post_type'], $post_types) &&
        !is_singular('page'))
    ) {
        $wp_query->set_404();
        status_header(404);
    }
}
add_action( 'wp', 'force_404' );