<?php
/**
 * Icon and Text component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_icon_and_text', $page_id);
 if ($component !== false) {
?>
<!-- TEXT ICON -->
<section class="section-wpr iconboxes">
	<div class="container">
		<div class="row">

			<?php
				$rows = get_field('icon_and_text', $component);

				foreach ($rows as $row){
				?>
					<div class="col-sm-4 iconbox">
						<div class="iconbox-icon">
							<?php
							if($row['icon_and_text_headline_url']){
							?>
							<a href="<?php echo $row['icon_and_text_headline_url'];?>">
							<?php	
							}
							?>
								<span class="<?php echo $row['icon_and_text_icon'];?>"></span>
							<?php
							if($row['icon_and_text_headline_url']){
							?>
							</a>
							<?php	
							}
							?>
						</div>
						<div class="iconbox-copy">
							<h2 class="section-heading--left-center">
								<?php
								if($row['icon_and_text_headline_url']){
								?>
								<a href="<?php echo $row['icon_and_text_headline_url'];?>">
								<?php	
								}
								?>
									<?php echo $row['icon_and_text_h2'];?>
								<?php
								if($row['icon_and_text_headline_url']){
								?>
								</a>
								<?php	
								}
								?>
							</h2>
							<?php echo $row['icon_and_text_content'];?>
						</div>
					</div>
				<?php
				}
			?>
			

			
		</div>
	</div>
</section>
<!-- TEXT ICON ###-->
<?php
 }