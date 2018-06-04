<?php
/**
 * =============================================================================
 * WORDPRESS PERFORMANCE
 * =============================================================================
 */

/**
 * clean up the actions and filters that fill up the <head>
 * @see http://cubiq.org/clean-up-and-optimize-wordpress-for-your-next-theme
 */
function wp_cleanup() {
  remove_action( 'wp_head', 'wp_generator' );                                   // removes the “generator” meta tag
  remove_action( 'wp_head', 'wlwmanifest_link' );                               // removes the “wlwmanifest” link
  remove_action( 'wp_head', 'rsd_link' );                                       // remove RSD
  remove_action( 'wp_head', 'wp_shortlink_wp_head' );                           // remove the generation of the shortlink
  remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );            // removes a link to the next and previous post
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );                // removes /wp-includes/js/wp-emoji.release.min.js
  remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' );             // avoid setting useless cookies

  remove_action( 'wp_print_styles', 'print_emoji_styles' );                     // removes /wp-includes/js/wp-emoji.release.min.js
  remove_action( 'wp_head','feed_links', 2 );                                   // remove rss feed links
  remove_action( 'wp_head','feed_links_extra', 3 );                             // disable automatic feeds

  add_filter( 'the_generator', '__return_false' );                              // removes the generator name from the RSS feeds
  add_filter( 'emoji_svg_url', '__return_false' );
  if( isset($_SERVER['SERVER_NAME']) && !fnmatch( '*.dev.jellyfish.*', $_SERVER['SERVER_NAME']) ) {
    add_filter( 'show_admin_bar','__return_false' );                            // removes the administrator’s bar while logged in
  }

  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );                // removes emoji styles and JS
  remove_action( 'wp_print_styles', 'print_emoji_styles' );                     // removes emoji styles and JS
}
add_action( 'after_setup_theme', 'wp_cleanup' );


/**
 * clean up the body_class(); output
 * @see http://wordpress.stackexchange.com/questions/15850/remove-classes-from-body-class
 */
function cleanup_body_class( $wp_classes, $extra_classes ) {
  // list of the only WP generated classes allowed
  $whitelist = array( 'home', 'blog', 'archive', 'single', 'category', 'tag', 'error404', 'logged-in', 'admin-bar' );
  // list of the only WP generated classes that are not allowed
  $blacklist = array( 'home', 'blog', 'archive', 'single', 'category', 'tag', 'error404', 'logged-in', 'admin-bar' );

  // whitelist result: (comment if you want to blacklist classes)
  $wp_classes = array_intersect( $wp_classes, $whitelist );

  // blacklist result: (uncomment if you want to blacklist classes)
  # $wp_classes = array_diff( $wp_classes, $blacklist );

  // add the extra classes back untouched
  return array_merge( $wp_classes, (array) $extra_classes );
}
add_filter( 'body_class', 'cleanup_body_class', 10, 2 );


/**
 * remove the calls for the asset: /wp-includes/js/wp.embed.min.js
 * @see http://wordpress.stackexchange.com/questions/15850/remove-classes-from-body-class
 */
function my_deregister_scripts() {
  wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_deregister_scripts' );


/**
 * remove the generation of the /wp-json/ file
 * @see https://thomas.vanhoutte.be/miniblog/remove-api-w-org-rest-api-from-wordpress-header/
 */
function remove_wpjson () {
  remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
  remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
}
add_action( 'after_setup_theme', 'remove_wpjson' );


/**
 * remove the <style> tag with the .recentcomments element from the <head>
 * @see http://wpsnipp.com/index.php/comment/remove-recent-comments-wp_head-css/
 */
function remove_recent_comments_style() {
  global $wp_widget_factory;
  remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
}
add_action( 'widgets_init', 'remove_recent_comments_style' );


/**
 * save images uploaded to the MEDIA section at a lower rate
 */
add_filter( 'jpeg_quality', create_function( '', 'return 85;' ) );              // default is 90


/**
 * remove the unwanted attachment pages created for each file
 */
function attachment_redirect() {
  global $wp_query, $post;

  if ( is_attachment() ) {
    $post_parent = $post->post_parent;
    if ( $post_parent ) {
      wp_redirect( get_permalink($post->post_parent), 301 );
      exit;
    }
    $wp_query->set_404();
    return;
  }
  if ( is_author() || is_date() ) {
    $wp_query->set_404();
  }
}
add_action( 'template_redirect', 'attachment_redirect' );


/**
 * minify HTML without a plugin
 * @see https://gist.github.com/unfulvio/5889564
 */
class wp_html_compression {
  protected $compress_css     = true;
  protected $compress_js      = true;
  protected $info_comment     = true;
  protected $remove_comments  = true;
  protected $html;

  public function __construct($html) {
    if (!empty($html)) {
      $this->parseHTML($html);
    }
  }

  public function __toString() {
    return $this->html;
  }

  // output the compression results on the second line of the HTML
  protected function bottomComment($raw, $compressed) {
    $raw = strlen($raw);
    $compressed = strlen($compressed);
    $savings = ($raw-$compressed) / $raw * 100;
    $savings = round($savings, 2);
    return '<!–- HTML compressed! | size saved: '.$savings.'% from '.$raw.' bytes | new size: '.$compressed.' bytes -–>';
  }

  protected function minifyHTML($html) {
    $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
    $overriding = false;
    $raw_tag = false;
    // variable reused for output
    $html = '';
    foreach ($matches as $token) {
      $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
      $content = $token[0];
      if (is_null($tag)) {
        if ( !empty($token['script']) ) {
          $strip = $this->compress_js;
        }
        else if ( !empty($token['style']) ) {
          $strip = $this->compress_css;
        }
        else if ($content == '<!-- no compression! -->') {
          $overriding = !$overriding;
          // don't print the comment
          continue;
        }
        else if ($this->remove_comments) {
          if (!$overriding && $raw_tag != 'textarea') {
            // remove any HTML comments, except MSIE conditional comments
            $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
          }
        }
      }
      else {
        if ($tag == 'pre' || $tag == 'textarea') {
          $raw_tag = $tag;
        }
        else if ($tag == '/pre' || $tag == '/textarea') {
          $raw_tag = false;
        }
        else {
          if ($raw_tag || $overriding) {
            $strip = false;
          }
          else {
            $strip = true;
            // remove any empty attributes, except: action, alt, content, src
            $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc|\bvalue)="")/', '$1', $content);

            // remove any space before the end of self-closing XHTML tags (JavaScript excluded)
            $content = str_replace(' />', '/>', $content);
          }
        }
      }
      if ($strip) {
        $content = $this->removeWhiteSpace($content);
      }
      $html .= $content;
    }
    return $html;
  }

  public function parseHTML($html) {
    $this->html = $this->minifyHTML($html);
    if ($this->info_comment && is_development()) {
      $this->html .= "\n" . $this->bottomComment($html, $this->html);
    }
  }

  protected function removeWhiteSpace($str) {
    $str = str_replace("\t", ' ', $str);
    $str = str_replace("\n",  '', $str);
    $str = str_replace("\r",  '', $str);
    while (stristr($str, '  ')) {
      $str = str_replace('  ', ' ', $str);
    }
    return $str;
  }
}
function wp_html_compression_finish($html) {
  return new wp_html_compression($html);
}
function wp_html_compression_start() {
  ob_start('wp_html_compression_finish');
}
/* Disabled. W3 Total Cache has the ability to handle this. Retaining for future uses where the plugin is not used. */
/*if (!is_development() || is_debug()) {
    add_action('get_header', 'wp_html_compression_start');
}*/


/**
 * remove the jquery migrate introduced in the WP Core 4.5 update
 * @see http://wordpress.stackexchange.com/questions/224661/annoying-jqmigrate-migrate-is-in-console-after-update-to-wp-4-5
 */
add_action('wp_default_scripts', function($scripts) {
  if ( ! empty( $scripts->registered['jquery'] ) ) {
    $jquery_dependencies = $scripts->registered['jquery']->deps;
    $scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
  }
} );