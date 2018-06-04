<?php
/**
 * =============================================================================
 * CUSTOM QUEUE LOADS
 * =============================================================================
 */

/**
 * custom load into wp_header
 */
function wp_header_customs() {
  wp_enqueue_style( 'casepoint-custom-style', get_template_directory_uri() .'/css/style.min.css' );
  wp_enqueue_script( 'high-bundler', get_template_directory_uri() . '/js/top.min.js' );
}
add_action( 'wp_enqueue_scripts', 'wp_header_customs' );


/**
 * custom load into wp_footer
 */
function wp_footer_customs() {
  wp_enqueue_script( 'low-bundler', get_template_directory_uri() . '/js/bottom.min.js' );
}
add_action( 'wp_footer' , 'wp_footer_customs' );


/**
 * if any of the following conditional arrays are met: page [id], [permalink], [page title]
 * add an additional script to the queue for the condition instance.
 */
// if ( is_page(array(151, 'contact-us', 'Contact Us')) ) {
//   wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false');
// }
