<?php
/**
 * Display all pages of any post type for a "Post Object" field with the name 'program'
 */
add_filter( 'acf/fields/post_object/query/name=program', 'jf_enable_all' );

function jf_enable_all($args) {
    $args['post_status'] = 'any';
    return $args;
}

wpcf7_add_form_tag('captureurl', 'wpcf7_captureurl_shortcode_handler', true); 

function wpcf7_captureurl_shortcode_handler($tag) {

    if (!is_object($tag)) { 
        return '';
    }
    $name = $tag->name; 
    if (empty($name)) { 
        return '';
    }
  
    return '<input type="hidden" name="' . $name . '" value="http://' . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"] . '" />';
  
}
