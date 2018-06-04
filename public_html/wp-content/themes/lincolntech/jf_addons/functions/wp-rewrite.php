<?php
/**
 * =============================================================================
 * WORDPRESS REWRITE RULES
 * =============================================================================
 */

/**
 * Add custom URI routes
 */
function jf_insert_rewrite_rule() {
    add_rewrite_rule(
        '(blog|press)/filter/([a-zA-Z]+?)/page/?([0-9]{1,})/?$',
        'index.php?pagename=$matches[1]&category_slug=$matches[2]&paged=$matches[3]',
        'top'
    );
    add_rewrite_rule(
        '(blog|press)/filter/([a-zA-Z]+?)/?$',
        'index.php?pagename=$matches[1]&category_slug=$matches[2]&paged=1',
        'top'
    );
}
add_action('init', 'jf_insert_rewrite_rule');

/**
 * Make the category_slug variable accessible on pages
 */
function jf_insert_query_vars( $vars ) {
    $vars[] = 'category_slug';
    return $vars;
}
add_filter('query_vars', 'jf_insert_query_vars');

/**
 * For when rules are updated, flush the rules
 */
function jf_flush_rules() {
	$rules = get_option( 'rewrite_rules' );
    $flush = false;
	if (!isset($rules['(blog|press)/filter/([a-zA-Z]+?)/page/?([0-9]{1,})/?$']))
		$flush = true;
    if (!isset($rules['(blog|press)/filter/([a-zA-Z]+?)/?$']))
		$flush = true;
    if ($flush === true) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
add_action('wp_loaded', 'jf_flush_rules');