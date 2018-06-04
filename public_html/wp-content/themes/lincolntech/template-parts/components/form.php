<?php
/**
 * Form component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
	   $page_id = get_the_ID();
 }
 $component = get_field('component_form', $page_id);
 if ($component !== false) {
    echo do_shortcode('[contact-form-7 id="'.$component.'"]');
 }
 ?>
 <script id="LeadiDscript" type="text/javascript">
    // <!--
    (function() {
        var s = document.createElement('script');
        s.id = 'LeadiDscript_campaign';
        s.type = 'text/javascript';
        s.async = true;
        s.src = '//create.lidstatic.com/campaign/2043d803-1221-46cc-c6cd-db658916caf9.js?snippet_version=2';
        var LeadiDscript = document.getElementById('LeadiDscript');
        LeadiDscript.parentNode.insertBefore(s, LeadiDscript);
    })();
    // -->
 </script>
 <noscript><img src='//create.leadid.com/noscript.gif?lac=2cb0a070-5a62-28df-fcf1-b8f982c42fa0&lck=2043d803-1221-46cc-c6cd-db658916caf9&snippet_version=2' /></noscript>