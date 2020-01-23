<?php

// get current url
global $wp;
$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) ); // with args
$url = strtok($current_url, '?'); // without args

// plugin settings
$settings = array(
   'aew_taxonomy_name' => 'category',
   'aew_taxonomy_unfold' => 'current',
   'aew_taxonomy_icon' => 'dashicons dashicons-arrow-right',
   'aew_taxonomy_show_count' => 1,
   'aew_taxonomy_hide_empty' => 0,
);

// query args
$args = array(
   'taxonomy' => $settings['aew_taxonomy_name'],
   // 'orderby' => $settings['aew_taxonomy_order_by'],
   // 'order' => $settings['aew_taxonomy_order'],
   'title_li' => '',
   'echo' => false,
   'hide_empty'  => $settings['aew_taxonomy_hide_empty'],
   // 'show_option_none' => $settings['aew_taxonomy_fallback_message'],
   'show_count' => $settings['aew_taxonomy_show_count'],
   // 'exclude' => $settings['aew_taxonomy_exclude'],
);

$html = wp_list_categories( $args );

// move count inside link
$html = preg_replace('/<\/a> \(([0-9]+)\)/', '&nbsp;(\\1)</a>', $html);

// UNKNOWN CODE 
// $html = str_replace('</a> (', '</a> <span>(', $html);
// $html = str_replace(')', ')</span>', $html);

if ($html) {

   // DOM object to manipulate results of wp_list_categories()
   $DOM = new DOMDocument();
   $DOM->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));


   // change all terms link to wp-admin and add link to term archive
   $lis = $DOM->getElementsByTagName('li');
   foreach($lis as $li) {
      $a = $li->getElementsByTagName('a');
      $a->item(0)->setAttribute('target','_blank');
      $term_text = $a->item(0)->nodeValue; // get old link text
      $a->item(0)->nodeValue = ''; // and clear old link text
      $a->item(0)->setAttribute('class','term_front_link');
      $external_icon = $DOM->createDocumentFragment();
      $external_icon->appendXML('<span class="dashicons dashicons-external"></span>');
      $a->item(0)->appendChild($external_icon);
      $a_front = $DOM->createDocumentFragment();
      $cat_front_slug = basename($a->item(0)->getAttribute('href'));
      $term = get_term_by('slug', $cat_front_slug, $settings['aew_taxonomy_name']);
      $a_front->appendXML('<a href="?cat=' . $term->term_id . '" class="term_admin_link">' . $term_text . '</a>');
      $li->insertBefore( $a_front, $a->item(0)); // insert new link
      
   }

   // All posts top item
   if (!isset($_GET['category_name']) && !isset($_GET['cat'])) {
      $all_posts_class = ' class="current-cat"';
   } else {
      $all_posts_class = '';
   }
   $all_cats = $DOM->createDocumentFragment();
   $all_cats->appendXML('<li' . $all_posts_class . '><a href="' . $url . '">Всі записи</a></li>');
   $firstChild = $DOM->firstChild;
   $DOM->insertBefore($all_cats, $firstChild);

   // uls
   $uls = $DOM->getElementsByTagName('ul');
   foreach($uls as $ul) {
      $parent = $ul->parentNode;
      $firstChild = $parent->firstChild;
      $parent_li_classes = $parent->getAttribute('class');	
      $toggleSpan = $DOM->createDocumentFragment();
      if ($settings['aew_taxonomy_unfold'] == 'all'  || (strpos($parent_li_classes, 'current-cat-ancestor') && $settings['aew_taxonomy_unfold'] == 'current')) {
         $toggleSpan->appendXML('<span class="sub_toggler opened"><span class="ai_unfold_icon"></span></span>');
      } else {
         $toggleSpan->appendXML('<span class="sub_toggler"><span class="ai_unfold_icon"></span></span>');
      }
      $parent->insertBefore($toggleSpan, $firstChild);
   }

   // save DOM
   $html=$DOM->saveHTML();

}

?>

<div id="ai_ct_folder_panel">

   <div class="ai_ct_folder_panel_wrap<?php echo ' aew_unfold_' . $settings['aew_taxonomy_unfold']; ?>">

      <?php echo '<ul class="aew_navigation_tree aew_navigation_wrapper">' . $html . '</ul>'; ?>

      <?php 

      
      ?>

   </div>

</div>


<script>
   jQuery(document).ready(function($) {
      var toggler = $('.aew_navigation_tree li .sub_toggler');
      $(toggler).on('click', function(e){
         var $this = $(this);
         if ($this.hasClass('opened')) {
            $this.parent().find('ul').first().css('display', 'block');
         }
         $this.parent().find('ul').first().slideToggle(200);
         $this.toggleClass('opened');
      });
   });
</script>


<style>
   body.wp-admin #wpcontent {
      padding-left: 278px;
   }
   .ai_ct_folder_panel_wrap {
      position: fixed;
      top: 32px;
      left: 160px;
      bottom: 0;
      width: 280px;
      padding: 55px 25px 40px 20px;
      z-index: 2;
      overflow: auto;
      box-sizing: border-box;
   }




   .aew_navigation_tree,
   .aew_navigation_tree ul.menu {
      margin: 0;
   }

   .aew_navigation_tree ul.children,
   .aew_navigation_tree ul.sub-menu {
      display: none;
   }
   .aew_unfold_all .aew_navigation_tree ul.children,
   .aew_unfold_all .aew_navigation_tree ul.sub-menu {
      display: block;
   }
   .aew_unfold_current .aew_navigation_tree .sub_toggler.opened ~ ul.children,
   .aew_unfold_current .aew_navigation_tree .sub_toggler.opened ~ ul.sub-menu {
      display: block;
   }

   .aew_navigation_tree li  {
      list-style-type: none;
      position: relative;
   }

   .aew_navigation_tree .sub_toggler {
      display: block;
      cursor: pointer;
      transition: all 300ms;
      position: absolute;
      width: 1.5em;
      height: 1.5em;
   }
   .aew_navigation_tree .sub_toggler .ai_unfold_icon {
      display: block;
      position: absolute;
      top: 50%;
      left: 10%;
      transform: translateY(-50%);
      width: 9px;
      height: 9px;
      border: 1px solid #000;
   }
   .aew_navigation_tree .sub_toggler .ai_unfold_icon:before {
      content: '';
      position: absolute;
      height: 1px;
      width: 5px;
      background-color: #000;
      top: 4px;
      left: 2px;
   }
   .aew_navigation_tree .sub_toggler .ai_unfold_icon:after {
      content: '';
      position: absolute;
      height: 5px;
      width: 1px;
      background-color: #000;
      top: 2px;
      left: 4px; 
   }
   .aew_navigation_tree .sub_toggler.opened .ai_unfold_icon:after {
      display: none;
   }

   .aew_navigation_tree li a.term_admin_link {
      margin-left: 1.3em;
      text-decoration: none;
      color: #444;
      position: relative;
      padding-left: 1.6em;
   }
   .aew_navigation_tree li a.term_front_link {
      text-decoration: none;
      color: #444;
      opacity: 0;
      transition: opacity 200ms;
      position: absolute;
   }
   .aew_navigation_tree li a.term_admin_link:hover ~ a.term_front_link {
      opacity: 1;
   }
   .aew_navigation_tree li a.term_front_link:hover {
      opacity: 1;
   }
   .aew_navigation_tree li a:focus {
      box-shadow: none;
   }
   .aew_navigation_tree li a.term_admin_link:before {
      content: '';
      position: absolute;
      width: 1.5em;
      height: 1.5em;
      display: inline-block;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath fill='%23444' d='M10 5L8 3H3c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1h-5z'/%3E%3C/svg%3E ");
      background-size: contain;

      left: 0;
   }
   .aew_navigation_tree li ul {
      margin-top: 0.45em;
      margin-left: 0.45em;
      padding-left: 0.5em;
   }

   .aew_navigation_tree li.current-cat > a.term_admin_link {
      text-decoration: underline;
   }
</style>
