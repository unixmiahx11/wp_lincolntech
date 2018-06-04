<?php
/**
 * Button list component
 *
 * @package lincolntech
 */
$page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_button_list', $page_id);
 if ($component !== false) {
?>
<div class="area-of-study">
	<h2 class="section-heading--center-left">
		<?php echo get_field('button_list_h2', $component);?>
	</h2>

	<?php 
		$rows  = get_field('button_list', $component);
		//$leftrows = array_slice($rows, 0, ceil(count($rows)/2));
		//$rightrows = array_slice($rows, ceil(count($rows)/2), count($rows));
	?>
	<div class="button-links">

	<?php
		foreach($rows as $row)
		{
	?>
		<div class="button-link">
			<a href="<?php echo $row['button_list_url'];?>"><?php echo $row['button_list_title'];?></a>
		</div>
	<?php	
		}
	?>
	</div>
</div>
<?php
 }