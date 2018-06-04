<?php
namespace Jellyfish;
/**
 * Replaces the "ver" query parameter with the file's modification time to help prevent caching issues
 *
 * @package Jellyfish
 * @author  Michael Brose <michael.brose@jellyfish.net>
 */

class AlterAssetVersions
{
    /**
     * Adds the filters to override the "ver" variable for styles and scripts
     *
     * @access public
     */
    public function __construct()
    {
        add_filter('style_loader_src', array($this, 'replaceAppendedVersion'), 20000);
        add_filter('script_loader_src', array($this, 'replaceAppendedVersion'), 20000);
    }
    /**
     * Replaces the default "ver" variable (WordPress version) with the modification time of the file
     * to prevent cache issues on deployments
     *
     * @access public
     *
     * @param string $target_url The URL to parse and check for the "ver" query variable
     * @return string The updated URL
     */
    public function replaceAppendedVersion($target_url)
    {
        // Hack for UAT. It shows whole path for some reason
        $remove = [];
        $p = '/plugins';
        if (strpos(WP_CONTENT_DIR, '_oh') !== false) {
            $remove[] = WP_CONTENT_DIR.$p;
            $remove[] = str_replace('_oh', '', WP_CONTENT_DIR.$p);
        } else {
            $remove[] = WP_CONTENT_DIR.$p;
            $remove[] = str_replace('public_html', 'public_html_oh', WP_CONTENT_DIR.$p);
        }
        $target_url = str_replace($remove, '', $target_url);
        $url = parse_url($target_url);
        if (isset($url['query']) && strpos($url['query'], 'ver=') !== false) {
            // Wrap in a try/catch block in case for stat failure
            try {
                // Replace the "ver" variable with the modification time of the file
                $path = ABSPATH.$url['path'];
                if (file_exists($path)) {
                    $target_url = add_query_arg('ver', filemtime($path), $target_url);
                }
            } catch (\Exception $e) {
                // Remove the "ver" variable in case of failure
                $target_url = remove_query_arg('ver', $target_url);
            }
        }
        return $target_url;
	}
}
