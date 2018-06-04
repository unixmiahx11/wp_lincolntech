<?php
/**
 * View for textarea post type
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */
if(!get_field('component_icon_and_text')){
	set_query_var('testimonialShade', 'gray');
	set_query_var('textmediaShade', 'top');
}
get_header();
?>
<section id="rfi" class="section-wpr two-columns-rfi">
<div class="container">
<div class="row">
<?php
get_template_part('template-parts/components/text-area');
?>
</div>
</div>
</section>
<?php
get_footer();