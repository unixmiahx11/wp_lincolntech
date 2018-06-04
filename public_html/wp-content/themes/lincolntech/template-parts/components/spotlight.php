<?php
/**
 * Spotlight component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_spotlight', $page_id);
 if ($component !== false) {
?>

<!-- SPOTLIGHT -->
<section class="spotlight">
	<div class="container">
		<div class="clearfix spotlight__copy-wpr">
			<div class="spotlight__copy">
				<div>
					<h1 class="text--white"><?php echo get_field('spotlight_h1', $component); ?></h1>
					<?php 
					if(get_field('spotlight_supporting_content', $component)){
					?>
					<p class="text--white"><?php echo get_field('spotlight_supporting_content', $component);?></p>
					<?php
					}
					?>	
				</div>
			</div>
		</div>
	</div>
	<div class="spotlight__image" style="background-image: url(<?php echo get_field('spotlight_image', $component);?>)"></div>
</section>
<!-- SPOTLIGHT ###-->
<?php
 }