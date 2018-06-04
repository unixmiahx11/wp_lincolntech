<?php
/**
 * remove all Yoast html comments
 * @see https://gist.github.com/paulcollett/4c81c4f6eb85334ba076
 */
if (defined('WPSEO_VERSION')) {
  add_action('get_header',function () {
    ob_start(function ($o) {
      return preg_replace('/\n?<.*?yoast.*?>/mi','',$o);
    });
  });

  add_action('wp_head',function (){ ob_end_flush(); }, 999);
}


/**
 * force the Yoast seo block location in the wp-admin to ALWAYS be at the bottom
 * @see https://gist.github.com/aderaaij/6767503
 */
function yoasttobottom() {
  return 'low';
}
add_filter('wpseo_metabox_prio', 'yoasttobottom');