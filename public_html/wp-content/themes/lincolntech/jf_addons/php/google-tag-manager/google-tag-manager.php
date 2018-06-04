<?php
namespace Jellyfish;
/**
 * Outputs the Google Tag Manager code in the head and body
 *
 * @package Jellyfish
 * @author  Michael Brose <michael.brose@jellyfish.net>
 */

class GoogleTagManager
{
    /**
     * Adds the filters and actions to allow the input of a GTM code on the admin and output the appropriate tags
     * on the actual site
     *
     * @access public
     */
    public function __construct()
    {
        // Disable for Dev containers
        if (!is_development() || is_debug()) {
            add_filter('admin_init', array($this, 'registerFields'));
            add_action('wp_head', array($this, 'addToHead'), 0);
            // Theme added action. Placed do_action('after_body'); in header.php
            add_action('after_body', array($this, 'addToBody'), 0);
        }
    }
    /**
     * Registers the new GTM field in the admin
     *
     * @access public
     */
    public function registerFields()
    {
		register_setting('general', 'google_tag_manager_id', 'esc_attr');
		add_settings_field('google_tag_manager_id', '<label for="google_tag_manager_id">' . __( 'Google Tag Manager ID' , 'google_tag_manager' ) . '</label>' , array($this, 'fieldsHtml') , 'general');
	}
    /**
     * The HTML displayed in the admin
     *
     * @access public
     */
	public function fieldsHtml()
    {
?>
		<input type="text" id="google_tag_manager_id" name="google_tag_manager_id" placeholder="GTM-XXXX" class="regular-text code" value="<?php echo get_option( 'google_tag_manager_id', '' ); ?>" />
		<p class="description"><?php _e( 'The ID from Google&rsquo;s provided code (as emphasized):', 'google_tag_manager' ); ?><br />
			<code>&lt;noscript&gt;&lt;iframe src="//www.googletagmanager.com/ns.html?id=<strong style="color:#c00;">GTM-XXXX</strong>"</code></p>
		<p class="description"><?php _e( 'You can get yours <a href="https://www.google.com/tagmanager/">here</a>!', 'google_tag_manager' ); ?></p>
<?php
    }
    /**
     * Adds the appropriate GTM script to the <head>
     *
     * @access public
     */
    public function addToHead() 
    {
        if( ! $id = get_option( 'google_tag_manager_id', '' ) ) return;
        $output = <<<GTM
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$id}');</script>
<!-- End Google Tag Manager -->

GTM;
        echo $output;
    }
    /**
     * Adds the appropriate GTM noscript to the <body>
     *
     * @access public
     */
    public function addToBody()
    {
        if( ! $id = get_option( 'google_tag_manager_id', '' ) ) return;
        $output = <<<GTM
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$id}" 
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

GTM;
        echo $output;
    }
}
