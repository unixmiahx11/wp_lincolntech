<?php
/**
 * Testimonial component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_testimonial', $page_id);
 if ($component !== false) {
?>

<!-- TESTIMONIAL -->
<section class="section-wpr testimonial <?php echo (get_query_var('testimonialShade'))? 'testimonial--'.get_query_var('testimonialShade') :'testimonial--white' ?>">
	<h2 class="section-heading--center text--white clearfix"><?php echo get_field('testimonial_h2', $component);?></h2>
	<div class="container">
		<div class="row">
			
			<?php
				if(get_field('testimonial_image', $component)){
			?>
				<div class="col-sm-5 testimonial-image">
					<img src="<?php echo get_field('testimonial_image', $component);?>">
				</div>
				<div class="col-sm-7 testimonial-copy add-divider">
			<?php
				}else{
			?>
				<div class="testimonial-copy testimonial-copy--only">
			<?php
				}
			?>
			
			
					<div>
						<p class="testimonial-quote text--white">“<?php echo get_field('testimonial_quote', $component);?>”</p>
						<p class="testimonial-cite">
							<?php echo get_field('testimonial_information', $component);?>
						</p>
					</div>
				</div>

			
		</div>
	</div>
</section>
<!-- TESTIMONIAL ###-->
<?php
 }