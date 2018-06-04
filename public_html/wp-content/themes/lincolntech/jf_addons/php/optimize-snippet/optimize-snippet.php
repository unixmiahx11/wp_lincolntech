<?php

namespace Jellyfish;

/**
 * Handle Optimize Google Tag Manager and Google Analytics
 *
 */
class OptimizeSnippet
{
    /**
     * Adds filters and actions to allow the Optimize code inputs 
     * on the admin and output the appropriate tags
     * on the website <head>
     *
     * @return void
     */
    public function __construct()
    {
        // Disable for Dev containers
        if (!is_development() || is_debug()) {
            add_filter('admin_init', array($this, 'registerFields'));
            add_action('wp_head', array($this, 'addToHead'), 0);
        }
    }

    /**
     * Register the GTM and GA fields in the admin
     *
     * @return void
     */
    public function registerFields()
    {
        register_setting('general', 'optimize_snippet_gtm_id', 'esc_attr');
        add_settings_field('optimize_snippet_gtm_id', '<label for="optimize_snippet_gtm_id">' . __('Optimize Container ID' , 'optimize_snippet') . '</label>' , array($this, 'gtmInput') , 'general');
        register_setting('general', 'optimize_snippet_ua_id', 'esc_attr');
        add_settings_field('optimize_snippet_ua_id', '<label for="optimize_snippet_ua_id">' . __('Google Analytics profile ID' , 'optimize_snippet') . '</label>' , array($this, 'uaInput') , 'general');
    }

    /**
     * HTML input to be display on the settings
     * for the Google Tag Manager field
     *
     * @return void
     */
    public function gtmInput()
    {
        printf(
            '<input type="text" id="optimize_snippet_gtm_id" name="optimize_snippet_gtm_id" placeholder="GTM-XXXX" class="regular-text code" value="%s" /><p class="description">%s<br /><code>&lt;noscript&gt;&lt;iframe src="//www.googletagmanager.com/ns.html?id=<strong style="color:#c00;">GTM-XXXX</strong>"</code></p><p class="description">%s</p>',
            get_option('optimize_snippet_gtm_id', ''),
            __('The ID from Google&rsquo;s provided code (as emphasized):', 'optimize_snippet'),
            __('You can get yours <a href="https://www.google.com/tagmanager/">here</a>!', 'optimize_snippet')
        );
    }

    /**
     * HTML input to be display on the settings
     * for the Google Analytics UA field
     *
     * @return void
     */
    public function uaInput()
    {
        printf(
            '<input type="text" id="optimize_snippet_ua_id" name="optimize_snippet_ua_id" placeholder="UA-XXXXXXXX-X" class="regular-text code" value="%s" /><p class="description">%s</p>',
            get_option('optimize_snippet_ua_id', ''),
            __('The Google Analytics profile ID', 'optimize_snippet')
        );
    }

    /**
     * Adds the appropriate GA script to the <head>
     *
     * @return void
     */
    public function addToHead()
    {
        $gtmId = get_option('optimize_snippet_gtm_id', '');
        $uaId = get_option('optimize_snippet_ua_id', '');

        if ($gtmId && $uaId) {
            printf(
                '<style>.async-hide{opacity: 0 !important}</style>'
                . '<script>(function(a,s,y,n,c,h,i,d,e){s.className+=" "+y;h.start=1*new Date;h.end=i=function(){s.className=s.className.replace(RegExp(" ?"+y),"")};(a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;})(window,document.documentElement,"async-hide","dataLayer",4000,{"%s":true});</script>'
                . '<script>(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,"script","https://www.google-analytics.com/analytics.js","ga");'
                . "ga('create', '%s', 'auto');ga('require', '%s');</script>\n",
                $gtmId,
                $uaId,
                $gtmId
            );
        }
    }
}
