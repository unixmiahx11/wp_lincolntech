<?php
/**
 * Accordion list component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }


 $component = get_field('component_accordion_list', $page_id);

 if ($component !== false) {
?>
<h2 class="section-heading--center-left">
     <?php echo is_front_page() ? "AREAS OF STUDY" : get_field('accordion_list_h2', $component);?>

</h2>
<?php if(get_field('accordion_list_description', $component)){
?>
     <p><?php echo is_front_page() ? '' : get_field('accordion_list_description', $component); ?></p>
<?php
}
?>

<div class="accordion">
<?php
	$rows = get_field('accordion_list', $component);
	$leftrows = array_slice($rows, 0, ceil(count($rows)/2));
	$rightrows = array_slice($rows, ceil(count($rows)/2), count($rows));
	$accordionNumber = 1;
?>
	<div class="panel-group accordion-left" id="accordion-left" role="tablist" aria-multiselectable="false">
		<?php foreach($leftrows as $leftrow){
		?>
		<div class="panel">
		    <div class="panel-heading" role="tab" id="<?php echo "state".$accordionNumber;?>">
		      <h4 class="panel-title">
		        <a role="button" data-toggle="collapse" data-parent="#accordion-left, #accordion-right" href="#<?php echo "campuses".$accordionNumber;?>" aria-expanded="true" aria-controls="<?php echo "campuses".$accordionNumber;?>" class="collapsed">
		          	<?php echo $leftrow['accordion_item_title'];?>
		        </a>
		      </h4>
		    </div>
		    <div id="<?php echo "campuses".$accordionNumber;?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?php echo "state".$accordionNumber;?>">
		      <div class="panel-body">
		      	<ul>
		      	<?php 
		      		$campuses = $leftrow['accordion_items'];
		      		foreach($campuses as $campus){
		      	?>
		      			<li><a href="<?php echo $campus['accordion_item_url'];?>"><?php echo $campus['accordion_item_title'];?></a></li>
		      	<?php
		      		} 
		      	?>
		        </ul>
		      </div>
		    </div>
		</div>
		<?php
			$accordionNumber += 1;
		}
		?>
	</div>
	<div class="panel-group accordion-left" id="accordion-right" role="tablist" aria-multiselectable="false">
		<?php foreach($rightrows as $rightrow){
		?>
		<div class="panel">
		    <div class="panel-heading" role="tab" id="<?php echo "state".$accordionNumber;?>">
		      <h4 class="panel-title">
		        <a role="button" data-toggle="collapse" data-parent="#accordion-left, #accordion-right" href="#<?php echo "campuses".$accordionNumber;?>" aria-expanded="true" aria-controls="<?php echo "campuses".$accordionNumber;?>" class="collapsed">
		          <?php echo $rightrow['accordion_item_title'];?>
		        </a>
		      </h4>
		    </div>
		    <div id="<?php echo "campuses".$accordionNumber;?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?php echo "state".$accordionNumber;?>">
		      <div class="panel-body">
		        <ul>
		      	<?php 
		      		$campuses = $rightrow['accordion_items'];
		      		foreach($campuses as $campus){
		      	?>
		      			<li><a href="<?php echo $campus['accordion_item_url'];?>"><?php echo $campus['accordion_item_title'];?></a></li>
		      	<?php
		      		} 
		      	?>
		        </ul>
		      </div>
		    </div>
		</div>
		<?php
			$accordionNumber += 1;
		}
		?>
	</div>
</div>
<?php
 }