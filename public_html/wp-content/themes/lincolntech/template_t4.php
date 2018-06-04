<?php
/**
 * Template Name: T4 About/Admissions/Other
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */
$formExists = false;
$page_id = get_query_var('page_override_id');
if (empty($page_id)) {
	$page_id = get_the_ID();
}
if (get_field('component_form', $page_id)) {
	$formExists = true;
}
get_header();

get_template_part('template-parts/components/header');
get_template_part('template-parts/components/spotlight');

?>
<section id="rfi" class="section-wpr two-column-rfi">
	<div class="container">
		<div class="row">
			<?php
				if ($formExists) {
			?>
			
			<div class="col-sm-6 add-divider">
				<div class="rfi-form-wpr">
					<div class="rfi-form">
						<?php get_template_part('template-parts/components/form');?>
					</div>
					<span class="rfi-form-shadow"></span>
				</div>
			</div>
			<div class="col-sm-6 text-area--half">
			<?php
				} else {
			?>
			<div class="col-sm-12 text-area--half">
			<?php
				}
			?>
			
				<?php 
					get_template_part('template-parts/components/text-area');
				?>
			</div>
		</div>
	</div>
</section>
<?php

get_template_part('template-parts/components/text-and-media');
get_template_part('template-parts/components/testimonial');
get_template_part('template-parts/components/footer');

get_footer();
