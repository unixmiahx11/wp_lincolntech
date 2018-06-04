<?php
/**
 * Program/Campus list component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_programcampus_list', $page_id);
 /**
  * <?php the_field('field_name', postID); ?>
  */
?>