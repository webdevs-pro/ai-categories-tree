<?php
/**
 * Plugin Name: AI Categories Tree
 * Plugin URI: https://github.com/webdevs-pro/ai-categories-tree
 * Description: Taxonomy tree for admin posts list
 * Author: Alex Ischenko
 * Version: 1.0
 * Text Domain: ai-categories-tree
 */

add_action( 'admin_footer', 'ai_ct_admin_footer');
function ai_ct_admin_footer() {

   $screen = get_current_screen();

   $allowed_post_types = array('post','product');

   if (  $screen->base == 'edit' && in_array($screen->post_type, $allowed_post_types)) {
      include( 'tree.php' );
   }

}


// plugin updates
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/webdevs-pro/ai-categories-tree',
	__FILE__,
	'ai-categories-tree'
);
