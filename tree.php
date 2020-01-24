<?php


class WPQuestions_Walker extends Walker_Category {
   function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

      $cat_name = apply_filters( 'list_cats', esc_attr( $category->name ), $category );

		if ( '' === $cat_name )
			return;
		

      
      if (!empty($args['show_count'])) {
			$cat_name .= '&nbsp(' . number_format_i18n( $category->count ) . ')';
      }
      
      // query srting for admin link
      // post
      if ($args['post_type'] == 'post') { 
         $link_admin = '<a class="term_admin_link" href="?cat=' . $category->term_id . '">' . $cat_name . '</a>';
      }
      // product
      if ($args['post_type'] == 'product') {
         $link_admin = '<a class="term_admin_link" href="?post_type=product&product_cat=' . $category->slug . '">' . $cat_name . '</a>';
      } 

      

      if (!empty($args['term_front_link_text'])) {
			$link_front = '<a class="term_front_link" href="' . get_term_link($category) . '" target="_blank">' . $args['term_front_link_text'] . '</a>';
		}  


      $output     .= "\t<li";
      $css_classes = array(
         'cat-item',
         'cat-item-' . $category->term_id,
      );

      if ( !empty( $args['current_category'] ) ) {
         // 'current_category' can be an array, so we use `get_terms()`.
         $_current_terms = get_terms(
            array(
               'taxonomy'   => $category->taxonomy,
               'include'    => $args['current_category'],
               'hide_empty' => false,
            )
         );

         foreach ( $_current_terms as $_current_term ) {

            if ( $category->term_id == $_current_term->term_id ) {
               $css_classes[] = 'current-cat';
               $link          = str_replace( '<a', '<a aria-current="page"', $link );
            } elseif ( $category->term_id == $_current_term->parent ) {
               $css_classes[] = 'current-cat-parent';
            }
            while ( $_current_term->parent ) {
               if ( $category->term_id == $_current_term->parent ) {
                  $css_classes[] = 'current-cat-ancestor';
                  if($args['unfold'] == 'current') {
                     $opened = ' opened';
                  }
                  
                  break;
               }
               $_current_term = get_term( $_current_term->parent, $category->taxonomy );
            }
         }
      }


      $css_classes = implode( ' ', apply_filters( 'category_css_class', $css_classes, $category, $depth, $args ) );
      $css_classes = $css_classes ? ' class="' . esc_attr( $css_classes ) . '"' : '';

      $output .= $css_classes;
      $output .= ">\n";


      // open sub icon
      if($args['unfold'] == 'all') {
         $opened = ' opened';
      }
      $term_children = get_term_children( $category->term_id, $category->taxonomy );
      if (count($term_children) > 0) {
         $output .= "<span class='sub_toggler" . $opened . "'>" . $args['unfold_icon'] . "</span>";
      }

      // folder icon
      if (!empty($args['folder_icon'])) {
         $output .= $args['folder_icon'];
      }
      
      
      $output .= "$link_admin";
      $output .= "$link_front\n";

	}

	public function end_el( &$output, $page, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] ) {
			return;
		}

		$output .= "</li>\n";
	}

}

// get current url
global $wp;

$post_type = $wp->query_vars['post_type'];
if ($post_type == 'post') {
   $taxonomy = 'category';
}
if ($post_type == 'product') {
   $taxonomy = 'product_cat';
   // var_dump(get_query_var('product_cat'));
}


// query args
$args = array(
   'taxonomy' => $taxonomy,
   'post_type' => $post_type,
   'unfold' => 'current', // current | all,
   'unfold_icon' => '<span class="ai_unfold_icon"></span>',
   'term_front_link_text' => '<span class="dashicons dashicons-external"></span>',
   'folder_icon' => '<span class="dashicons dashicons-category"></span>',
   'title_li' => '',
   'echo' => false,
   'hide_empty'  => 0,
   'current_category' => '',
   // 'all_posts' => 'Всі записи';
   'show_count' => 1,
   // 'exclude' => '',
   // 'show_option_none' => '',
   // 'orderby' => '',
   // 'order' => '',
   
   'walker' => new WPQuestions_Walker,

);


$html = wp_list_categories( $args );

?>

<div id="ai_ct_folder_panel">
   <div class="ai_ct_folder_panel_wrap">
      <?php echo '<ul class="aew_navigation_tree aew_navigation_wrapper">' . $html . '</ul>'; ?>
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
      padding: 55px 25px 10px 20px;
      z-index: 2;
      overflow: auto;
      box-sizing: border-box;
      margin-bottom: 35px;
   }




   .aew_navigation_tree,
   .aew_navigation_tree ul.menu {
      margin: 0;
   }

   .aew_navigation_tree ul.children,
   .aew_navigation_tree ul.sub-menu {
      display: none;
   }

   .aew_navigation_tree .sub_toggler.opened ~ ul.children,
   .aew_navigation_tree .sub_toggler.opened ~ ul.sub-menu {
      display: block;
   }

   .ai_ct_folder_panel_wrap ul {
      margin-top: 0.45em;
      padding-left: 1.4em;
   }

   .aew_navigation_tree li  {
      list-style-type: none;
      position: relative;
      margin-bottom: 0.5em;
      line-height: 1.2em;
   }


   .aew_navigation_tree li a {
      text-decoration: none;
      color: #444;
      vertical-align: middle;
   }
   .aew_navigation_tree li a.term_admin_link {
      margin-left: 0.2em;
   }
   .aew_navigation_tree li a.term_front_link {
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
   .aew_navigation_tree li.current-cat > a.term_admin_link {
      text-decoration: underline;
   }
   .aew_navigation_tree li .dashicons {
      font-size: 1.5em;
   }
   



   .aew_navigation_tree .sub_toggler {
      display: inline-block;
      cursor: pointer;
      transition: all 300ms;
      position: absolute;
      width: 1.5em;
      height: 1.5em;
      left: -1.5em;
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



</style>