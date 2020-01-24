<?php
/**
 * Plugin Name: AI Categories Tree
 * Plugin URI: 
 * Description: 
 * Author: 
 * Author 
 * Version: 1
 * Text Domain: 
 */

add_action( 'admin_footer', 'ai_ct_admin_footer');
function ai_ct_admin_footer() {
   $screen = get_current_screen();

   if ( 'upload' == $screen->base ) return;
   // if (isset($_GET['post_type']) && $_GET['post_type'] != 'post') return;

   $allowed_post_types = array('post','product');
   if (  $screen->base == 'edit' && in_array($screen->post_type, $allowed_post_types)) {
      include( 'tree.php' );
   }
}