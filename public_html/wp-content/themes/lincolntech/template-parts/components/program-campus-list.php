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
 if ($component !== false) {
?>
<section class="section-wpr program-campus-list">
<div class="container">

<div class="row">
<div class="col-sm-12">
	<h2 class="section-heading--center">
		<?php echo get_field('pcl_list_h2', $component);?>
	</h2>

	<?php 
		$rows  = get_field('pcl_item_list', $component);
		if (empty($rows)) {
			// Check current page, determine if Campus or Program, prefill $rows
			$page_id = get_query_var('pcl_page_id', get_the_ID());
			#$page_id = get_the_ID();
			$term = false;
			$rows = [];
			$categories = get_the_category($page_id);
            if (!empty($categories)) {
                if (5 == $categories[0]->term_id) {
                    $term = 'campus';
                } elseif (6 == $categories[0]->term_id) {
                    $term = 'program';
                }
            }
			if ('program' == $term) {
				$data = get_field('global_programs', GLOBAL_ID);
				foreach ($data as $item) {
					if ($item['program'] == $page_id) {
						foreach ($item['campuses'] as $campus) {
							$row = [];
							$row['item_campus'] = $campus['campus'];
							$row['title'] = (!empty($campus['campus_title_override']) ? $campus['campus_title_override'] : null);
							$rows[] = $row;
						}
						break;
					}
				}
			} elseif ('campus' == $term) {
				$data = get_field('global_campuses', GLOBAL_ID);
				foreach ($data as $item) {
					if ($item['campus'] == $page_id) {
						foreach ($item['programs'] as $program) {
							$row = [];
							$row['item_program'] = $program['program'];
							$row['title'] = (!empty($program['program_title_override']) ? $program['program_title_override'] : null);
							$rows[] = $row;
						}
						break;
					}
				}
			}
		}
		//$leftrows = array_slice($rows, 0, ceil(count($rows)/2));
		//$rightrows = array_slice($rows, ceil(count($rows)/2), count($rows));
		$has_link = get_field('pcl_enable_links', $component);
	?>
	<div class="button-links">

	<?php
		$item_key = null;
		foreach($rows as $row) {
			if ($item_key === null) {
				if (!empty($row['item_program'])) {
					$item_key = 'item_program';
				} else {
					$item_key = 'item_campus';
				}
			}
			$title = (!empty($row['title']) ? $row['title'] : get_the_title($row[$item_key]));
	?>
		
	<?php
			if ($has_link === true) {
	?>
		<div class="button-link">
			<a href="<?php echo esc_url( get_permalink($row[$item_key]) ); ?>"><?php echo $title; ?></a>
		</div>
	<?php
			} else {
	?>
		<div class="button-link--disabled">
			<?php echo $title; ?>
		</div>
	<?php
			}
	?>
	
	<?php	
		}
	?>
	</div>
</div>
</div>
</div>
</section>
<?php
 }