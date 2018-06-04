<?php
/**
 * Next Steps component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_next_steps', $page_id);
 if ($component !== false) {
?>
<!-- NEXT STEP -->
<section class="section-wpr stepsboxes">
	<div class="container">
		<div class="row">
			<h2 class="section-heading--center"><?php echo get_field('next_steps_h2',$component);?></h2>

			<?php
				$rows = get_field('next_steps', $component);
				foreach($rows as $row){
			?>
				
				<div class="col-sm-4 stepsbox">
					<div class="steps-icon">
						<span class="icon-step <?php print_r($row['icon']);?>"></span>
					</div>
					<div class="steps-copy">
						<?php echo $row['supporting_content'];?>
					</div>
				</div>

			

			<?php
				}
			?>
			
		</div>
	</div>
</section>
<!-- NEXT STEP ###-->
<?php
 }