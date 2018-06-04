<?php
/**
 * Template Name: T5 Thank You
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */

get_header();

get_template_part('template-parts/components/header');
get_template_part('template-parts/components/spotlight');

?>
<section id="rfi" class="section-wpr one-column-rfi">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="rfi-form-wpr">
					<div class="rfi-form">
						<?php get_template_part('template-parts/components/form');?>
					</div>
					<span class="rfi-form-shadow"></span>
				</div>
			</div>
		</div>
	</div>
</section>
<?php

get_template_part('template-parts/components/next-steps');
get_template_part('template-parts/components/testimonial');
get_template_part('template-parts/components/footer');

get_footer();