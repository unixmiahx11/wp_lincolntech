<?php
/**
 * Text and Media component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_text_and_media', $page_id);
 if ($component !== false) {
?>
<!-- TEXT VIDEO TEASER -->
<section class="section-wpr teaser <?php echo (get_query_var('textmediaShade'))?'teaser--'.get_query_var('textmediaShade').'-shadow' : 'teaser--top-shadow teaser--bottom-shadow';?>">
	<div class="container">
		<div class="row">
			<div class="col-sm-6 teaser-copy add-divider">
				<h2 class="section-heading--center-left"><?php echo get_field('text_and_media_h2', $component);?></h2>
				<p><?php echo get_field('text_and_media_content',$component);?></p>
				</div>
			<div class="col-sm-6 teaser-media-wpr">
				<div class="teaser-media">
				<?php if(get_field('text_and_media_selection' , $component)['value'] == 'image'){
				?>
					<img src="<?php echo get_field('text_and_media_image', $component);?>">
				<?php
				}
        if(get_field('text_and_media_selection' , $component)['value'] == 'video' && get_field('text_and_media_video_source', $component) == 'Youtube'){
					if(get_field('video_thumbnail_image', $component) != null ) {
						$parts = parse_url(get_field('text_and_media_video_url', $component));
						parse_str($parts['query'], $query);
                ?>
                <a href="https://www.youtube.com/watch?v=<?php echo $query['v'];?>" class="video popup-youtube" data-featherlight="#videoContainerIframe">
                  <img src="<?php echo get_field('video_thumbnail_image', $component);?>">
                </a>
                <?php
              } else {
                $parts = parse_url(get_field('text_and_media_video_url', $component));
    						parse_str($parts['query'], $query);
              ?>
						<a href="https://www.youtube.com/watch?v=<?php echo $query['v'];?>" class="video popup-youtube" data-featherlight="#videoContainerIframe">
								<img src="//img.youtube.com/vi/<?php echo $query['v'];?>/maxresdefault.jpg">
							</a>
					<?php

					}
				}
				?>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- TEXT VIDEO TEASER ### -->
<?php
}
