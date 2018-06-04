<?php
/**
 * remove auto formatting on TinyMCE Advanced source code manipulation
 * @see https://paulund.co.uk/change-posts-text-in-admin-menu
 */
function override_mce_options( $options ) {                                     // function to allow all elements in tinymce
  $options['valid_elements'] = $options['extended_valid_elements'] = '*[*]';
  return $options;
}
add_filter( 'tiny_mce_before_init', 'override_mce_options' );
remove_filter( 'the_content', 'wpautop' );                                      // removes wpautop filter on the_content


add_filter( 'mce_buttons_3', 'mce_enable_buttons');
function mce_enable_buttons($buttons) {
  $buttons[] = 'hr';
  #$buttons[] = 'fontselect';
  $buttons[] = 'fontsizeselect';
  $buttons[] = 'cleanup';
  $buttons[] = 'styleselect';
  return $buttons;
}

add_filter('mce_buttons_2', 'mce_disable_buttons');
function mce_disable_buttons($buttons) {
  if (($key = array_search('forecolor', $buttons)) !== false) {
    unset($buttons[$key]);
  }
  $buttons[] = 'sub';
  $buttons[] = 'sup';
  return $buttons;
}