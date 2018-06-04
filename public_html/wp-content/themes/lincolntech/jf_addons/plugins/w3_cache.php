<?php
/**
 * remove the W3 total cache footer html comment
 * @see https://www.dylanbarlett.com/2014/01/disabling-w3-total-cache-footer-comment/
 */
add_filter('w3tc_can_print_comment', '__return_false', 10, 1);                  // disable W3TC footer comment for all users
if ( !current_user_can('unfiltered_html') ) {                                   // disable W3TC footer comment for everyone but Admins (single site) / Super Admins (network mode)
  add_filter('w3tc_can_print_comment', '__return_false', 10, 1);
}
if ( !current_user_can('activate_plugins') ) {                                  // disable W3TC footer comment for everyone but Admins (single site & network mode)
  add_filter('w3tc_can_print_comment', '__return_false', 10, 1);
}