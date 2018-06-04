<?php
/**
 * Disable loading of JS/CSS for WPCF7 on all pages.
 * @link         http://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
 */
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );
add_filter( 'wpcf7_validate_configuration', '__return_false' );

/**
 * Function can be called on individual pages to load JS/CSS
 * @link         http://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
 */
function load_contact_form_assets() {
    if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
        wpcf7_enqueue_scripts();
    }
    if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
        wpcf7_enqueue_styles();
    }
    #wp_enqueue_script('casepoint-wpcf7-override', get_template_directory_uri().'/js/wpcf7-override.js', array(), '20151219', true);
}

add_filter('wpcf7_form_action_url', 'wpcf7_custom_form_action_url');
function wpcf7_custom_form_action_url($url)
{
    # /#wpcf7-f234-o1
    $form = wpcf7_get_current_contact_form();
    if ($form->name() == 'main-form') {
        return site_url('thank-you');
    }
}