<?php
/**
 * lincolntech functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package lincolntech
 */
define('GLOBAL_ID', 348);
// Require helper functions
require get_template_directory() . '/jf_addons/functions/jf_helper.php';
if ( ! function_exists( 'lincolntech_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function lincolntech_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on lincolntech, use a find and replace
	 * to change 'lincolntech' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'lincolntech', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'menu-1' => esc_html__( 'Primary', 'lincolntech' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'lincolntech_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );
}
endif;
add_action( 'after_setup_theme', 'lincolntech_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function lincolntech_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'lincolntech_content_width', 640 );
}
add_action( 'after_setup_theme', 'lincolntech_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function lincolntech_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'lincolntech' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'lincolntech' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'lincolntech_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function lincolntech_scripts() {
	//wp_enqueue_style( 'lincolntech-style', get_stylesheet_uri() ); //we are loading this async in head using loadCSS function

	//wp_enqueue_script( 'lincolntech-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true ); //we are not using wp navigation script at all

	//wp_enqueue_script( 'lincolntech-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true ); // we not using skip in the site

	/*if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}*/ //we are not dealing with comment on any page. In case we use single template for any of the page we dont want this js file to laod
}
//add_action( 'wp_enqueue_scripts', 'lincolntech_scripts' );



//Remove jQuery migrate
if (!is_admin())  {
	add_action("wp_enqueue_scripts", "my_jquery_enqueue", 11);
}
function my_jquery_enqueue() {
   wp_deregister_script('jquery');
   //wp_register_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js", false, null, true);
   //wp_register_script('c7', "/wp-content/plugins/contact-form-7/includes/js/scripts.js", false, null, true);
   //wp_enqueue_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js", false, null, true);
   wp_enqueue_script( 'lincolntech-app-scripts', get_template_directory_uri() . '/js/app.min.js', false, '', true );
   //wp_enqueue_script( 'jval', 'https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js', false, '', true );
   //wp_enqueue_script( 'onlyval', get_template_directory_uri() . '/js/onlyval.js', false, '', true );

}

//remove_action('','jvcf7_validation_js');
//remove_action('','jvcf7_adminCsslibs');

/**
 * Load master file
 */
require get_template_directory() . '/jf_addons/_master.php';