<?php
/**
 * =============================================================================
 * SHORTCODES
 * =============================================================================
 */

/**
 * shortcode construct array
 * @example [template_tip text="Wash your hands after using the bathroom"]
 */
/*
add_shortcode( 'template_tip', function($atts) {
  $atts = shortcode_atts(
    array(
      'tip'       => '',
      'title'     => '',
      'btn_url'   => '',
      'btn_text'  => 'Button',
    ), $atts);
  $html = '<div class="template-tip">
           <span class="tip-heading">' . $atts['title'] .'</span>
           <p>' . $atts['tip'] .'</p>';
  if( strlen($atts['btn_url']) > 0 ) $html .= ' <a href="'.$atts['btn_url'].'" title="Click here to view more cool tips!" class="btn">'.$atts['btn_text'].'</a>';
  $html .= ' </div>';
  return $html;
});
*/

/**
 * Adds the [figure] shortcode
 */
function figure_shortcode($atts, $content = null) {
    $obj = shortcode_atts(
        array(
            'image'   => '',
            'alt'     => '',
        ),
        $atts
    );
    $id = get_attachment_id_by_url($obj['image']);
    if (!empty($id)) {
        $obj['image'] = wp_get_attachment_url($id);
    }
	return '<figure><img src="'.$obj['image'].'" alt="'.esc_attr($obj['alt']).'" /><figcaption>'.$content.'</figcaption></figure>';
}
add_shortcode('figure', 'figure_shortcode');