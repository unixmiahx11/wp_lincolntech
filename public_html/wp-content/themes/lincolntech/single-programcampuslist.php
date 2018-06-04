<?php
/**
 * View for programcampuslist post type
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */

if(!get_field('component_icon_and_text')){
	set_query_var('testimonialShade', 'gray');
	set_query_var('textmediaShade', 'top');
}
get_header();
if (empty(get_field('pcl_item_list'))) {
	if (get_field('pcl_choice') == 'Program') {
		$result = new WP_Query([
			'post_type' => 'page',
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'cat' => 6,
			'posts_per_page' => 1,
		]);
		$page_id = $result->posts[0]->ID;
	} else {
		$result = new WP_Query([
			'post_type' => 'page',
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'cat' => 5,
			'posts_per_page' => 1,
		]);
		$page_id = $result->posts[0]->ID;
	}
	set_query_var('pcl_page_id', $page_id);
}
?>
<section id="rfi" class="section-wpr two-columns-rfi">
<div class="container">
<div class="row">
<?php
get_template_part('template-parts/components/program-campus-list');
?>
</div>
</div>
</section>
<?php
get_footer();