<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package lincolntech
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<!-- GTM -->
<style>.async-hide { opacity: 0 !important} </style>
<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.start=1*new Date;
h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
(a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);h.timeout=c;
})(window,document.documentElement,'async-hide','dataLayer',4000,
{'GTM-MGNPMGX':true});</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-701617-23', 'auto');
  ga('require', 'GTM-MGNPMGX');
</script>


<!-- CSS-->
	<script>
    !function(e){"use strict";
    var n=function(n,t,o){function i(e){return f.body?e():void setTimeout(function(){i(e)})}var d,r,a,l,f=e.document,s=f.createElement("link"),u=o||"all";
    return t?d=t:(r=(f.body||f.getElementsByTagName("head")[0]).childNodes,d=r[r.length-1]),a=f.styleSheets,s.rel="stylesheet",s.href=n,s.media="only x",i(function(){d.parentNode.insertBefore(s,t?d:d.nextSibling)}),l=function(e){for(var n=s.href,t=a.length;t--;)if(a[t].href===n)return e();
    setTimeout(function(){l(e)})},s.addEventListener&&s.addEventListener("load",function(){this.media=u}),s.onloadcssdefined=l,l(function(){s.media!==u&&(s.media=u)}),s};
    "undefined"!=typeof exports?exports.loadCSS=n:e.loadCSS=n}("undefined"!=typeof global?global:this)
  	</script>
	<style>
	<?php
		include('css/critical.css');
	?>
	</style>

	<script>
		
    	loadCSS('/wp-content/themes/lincolntech/style.css');
  	</script>
  	<noscript>
  		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri().'/style.css'; ?>">
  	</noscript>
 <!-- CSS end -->
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>


<?php 

$page_url = $_SERVER['REQUEST_URI'];
if(!empty($_SESSION['fname']) && preg_match('/\bthank-you\b/',$page_url)){ ?>
<input type="hidden" name="fname" id="fname" value="<?php echo $_SESSION['fname'];?>">
<input type="hidden" name="lname" id="lname" value="<?php echo $_SESSION['lname'];?>">
<input type="hidden" name="email" id="email" value="<?php echo $_SESSION['email'];?>">
<input type="hidden" name="phone" id="phone" value="<?php echo $_SESSION['user-phone'];?>">
<?php } ?>

<?php
do_action('after_body');

