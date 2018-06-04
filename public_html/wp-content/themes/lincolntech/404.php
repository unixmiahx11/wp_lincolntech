<?php
/**
 * Based off of the T4 template
 *
 * @package Jellyfish
 * @subpackage lincolntech
 */
$page_id = get_field('error_page', GLOBAL_ID);
set_query_var('page_override_id', $page_id);
get_template_part('template_t4');