<?php
/**
 * PLUGIN OVERRIDES, CUSTOMIZATIONS, AND CLASS CONSTRUCTION
 */

// THIRD PARTY CLASSES
require get_template_directory() . '/jf_addons/php/lead-intercept/mobile-detect.php';

// PLUGINS
require get_template_directory() . '/jf_addons/plugins/contact_form_7.php';
require get_template_directory() . '/jf_addons/plugins/tinymce.php';
require get_template_directory() . '/jf_addons/plugins/advanced_custom_fields.php';
require get_template_directory() . '/jf_addons/plugins/yoast_seo.php';

// FUNCTIONS
require get_template_directory() . '/jf_addons/functions/phone.php';
require get_template_directory() . '/jf_addons/functions/wp-admin.php';
require get_template_directory() . '/jf_addons/functions/wp-core.php';
require get_template_directory() . '/jf_addons/functions/wp-performance.php';

// CLASSES
/**
 * Google Tag Manager
 */
require get_template_directory() . '/jf_addons/php/optimize-snippet/optimize-snippet.php';
$OptimizeSnippet = new \Jellyfish\OptimizeSnippet();
require get_template_directory() . '/jf_addons/php/google-tag-manager/google-tag-manager.php';
$GoogleTagManager = new \Jellyfish\GoogleTagManager();

/**
 * Class to add additional tags in the <head>
 */
#require get_template_directory() . '/jf_addons/php/meta-tag-addition/meta-tag-addition.php';
#$MetaTagAddition = new \Jellyfish\MetaTagAddition();
 
/**
 * Lead interception class
 */
require get_template_directory() . '/jf_addons/php/lead-intercept/lead-intercept.php';
$LeadIntercept = new \Jellyfish\LeadIntercept();

/**
 * Dynamic Select class
 */
require get_template_directory() . '/jf_addons/php/dynamic-select/dynamic-select.php';
$DynamicSelect = new \Jellyfish\DynamicSelect();

/**
 * Alters the "ver" query parameter for .css and .js files
 */
require get_template_directory() . '/jf_addons/php/alter-asset-versions/alter-asset-versions.php';
$alterAssetVersions = new \Jellyfish\AlterAssetVersions(); 