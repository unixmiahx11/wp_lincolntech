<?php
/**
 * View for textmedia post type
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */
if(!get_field('component_icon_and_text')){
	set_query_var('testimonialShade', 'gray');
	set_query_var('textmediaShade', 'top');
}
get_header();
get_template_part('template-parts/components/text-and-media');
get_footer();