<?php
/**
 * Text Area (Half Page) component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_text_area', $page_id);
 if ($component !== false) {
?>

<h2 class="section-heading--center-left">
<?php echo get_field('text_area_h2', $component);?>
</h2>
<?php echo get_field('text_area_content', $component);
 }