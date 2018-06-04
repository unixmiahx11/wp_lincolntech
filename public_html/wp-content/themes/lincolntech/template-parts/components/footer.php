<?php
/**
 * Footer component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_footer', $page_id);
 $component_cta =  get_field('component_header', $page_id);
?>
</main> <!--- end main -->
<?php
 if ($component !== false) {
?>
<footer class="section-wpr footer-dark">
	<div class="container">
		<div class="row">
			<div class="clearfix">
				<div class="footer-left add-divider">
					<div class="clearfix">
						<?php if(get_field('footer_logo_cta', $component)){
						?>
							<a href="<?php echo get_field('footer_logo_cta_url', $component);?>" style="display: block;">
						<?php
						} 
						?>
							<?php
								if(get_field_object('footer_logo_type', $component)['value'] == 'inline'){
									echo get_field('footer_logo_inline', $component);
								}else{
									echo "<img src='".get_field('footer_logo', $component)."'>";
								}
								
							?>
						<?php if(get_field('footer_logo_cta', $component)){
								?>
								</a>
						<?php
							}
						?>
					</div>
				</div>
				<div class="footer-right">
					<p><?php echo get_field('footer_social_icons_headline', $component);?></p>
					<div class="social-links">
						<?php
							$rows = get_field('footer_social_icons', $component);

							foreach ($rows as $row){
							?>
							<a href="<?php echo $row['social_url'];?>" class="<?php echo $row['social_icon'];?>" target="_blank" rel="noopener">
							</a>
						<?php
							}
						?>
						
					</div>
				</div>
			</div>
			<div class="footer-copy clearfix">
				<?php the_field('footer_consumer_text', $component); ?>
				<a href="<?php echo get_field('footer_link_text_url',$component);?>"><?php echo get_field('footer_link_text',$component);?></a><span>|</span><?php echo get_field('footer_copyright_text',$component);?>
			</div>
		</div>
	</div>
</footer>

<!-- MOBILE CTAs -->
<div class="mobile-cta">
	<?php
		if (get_field('header_primary_cta', $component_cta)) :
			$mobilePhoneNumber = Phone::phoneSource(
				get_field('header_mobile_phone_number', $component_cta)
			);
	?>
	<div class="mobile-cta--request">
		<a href="<?php echo get_field('header_primary_cta_url', $component_cta); ?>" data-offset="99">
			<span class="icon-chat"></span>
			<?php echo get_field('header_primary_cta_text', $component_cta); ?>
		</a> 
	</div>
	<div class="mobile-cta--phone">
		<a href="tel:<?php echo $mobilePhoneNumber; ?>">
			<span class="icon-phone--white"></span>
			<?php echo $mobilePhoneNumber; ?>
		</a>
	</div>
	<?php else : ?>
	<div class="mobile-cta--full">
		<a href="tel:<?php echo get_field('header_phone_number', $component_cta);?>">
			<span class="icon-phone--white"></span>
			<?php echo get_field('header_phone_number', $component_cta);?>
		</a>
	</div>
	<?php endif; ?>
</div>
<!-- MOBILE CTAs ### -->
<?php
 } 