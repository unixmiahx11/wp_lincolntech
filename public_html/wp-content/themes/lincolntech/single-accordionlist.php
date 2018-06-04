<?php
/**
 * View for accordionlist post type
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */
if(get_field('component_text_and_media')){
	set_query_var('testimonialShade', 'gray');
	set_query_var('textmediaShade', 'top');
}

get_header();
?>
<section id="rfi" class="section-wpr two-column-rfi">
<div class="container">
<div class="row">
<?php 
    get_template_part('template-parts/components/accordion-list');
?>
</div>
</div>
</section>
<?php
get_footer();