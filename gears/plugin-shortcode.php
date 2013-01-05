<?php
/**
 * Alpine PhotoTile for Instagram: Shortcode
 *
 * @since 1.1.1
 *
 */
 
  function APTFINbyTAP_shortcode_function( $atts ) {
    $bot = new PhotoTileForInstagramBot();
    
    $optiondetails = $bot->option_defaults();
    $options = array();
    foreach( $optiondetails as $opt=>$details ){
      $options[$opt] = $details['default'];
      if( $atts[ $details['short'] ] ){
        $options[$opt] = $atts[ $details['short'] ];
      }
    }
    if( $options['instagram_image_link_option'] == "fancybox" ){
      wp_enqueue_script( 'fancybox' );
      wp_enqueue_style( 'fancybox-stylesheet');
    } 
    wp_enqueue_style($bot->wcss);
    wp_enqueue_script($bot->wjs);

    $id = rand(100, 1000);
    $source_results = $bot->photo_retrieval($id, $options);
    
    $return .= '<div id="'.$bot->id.'-by-shortcode-'.$id.'" class="AlpinePhotoTiles_inpost_container">';
    $return .= $source_results['hidden'];
    if( $source_results['continue'] ){  
      if( "vertical" == $options['style_option'] ){
        $return .= $bot->display_vertical($id, $options, $source_results);
      }elseif( "cascade" == $options['style_option'] ){
        $return .= $bot->display_cascade($id, $options, $source_results);
      }else{
        $return .= $bot->display_hidden($id, $options, $source_results);
      }
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    else{
      $return .= 'Sorry:<br>'.$source_results['message'];
    }
    $return .= $after_widget;
    $return .= '</div>';
    
    return $return;
  }
  add_shortcode( 'alpine-phototile-for-instagram', 'APTFINbyTAP_shortcode_function' );
   
?>