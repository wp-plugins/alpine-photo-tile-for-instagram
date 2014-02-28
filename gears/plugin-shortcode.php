<?php
/**
 * Alpine PhotoTile for Instagram: Shortcode
 *
 * @ Since 1.1.1
 * @ Updated 1.2.5
 */
 
  function APTFINbyTAP_shortcode_function( $atts ) {
    
    $bot = new PhotoTileForInstagramBot();

    $optiondetails = $bot->option_defaults();
    $options = array();
    foreach( $optiondetails as $opt=>$details ){
      $options[$opt] = $details['default'];
      if( isset($details['short']) && isset($atts[ $details['short'] ]) ){
        $options[$opt] = $atts[ $details['short'] ];
      }
    }
    $id = rand(100, 1000);
    $bot->set_private('wid','id'.$id);
    $bot->set_private('options',$options);
    $bot->do_alpine_method( 'update_global_options' );
    $bot->do_alpine_method( 'enqueue_style_and_script' );  
    // Do the photo search
    $bot->do_alpine_method( 'photo_retrieval' );

    $return = '<div id="'.$bot->get_private('id').'-by-shortcode-'.$id.'" class="AlpinePhotoTiles_inpost_container">';
    $return .= $bot->get_active_result('hidden');
    if( $bot->check_active_result('success') ){
      if( 'vertical' == $options['style_option'] ){
        $bot->do_alpine_method( 'display_vertical' );
      }elseif( 'cascade' == $options['style_option'] ){
        $bot->do_alpine_method( 'display_cascade' );
      }else{
        $bot->do_alpine_method( 'display_hidden' );
      }
      $return .= $bot->get_private('output');
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    elseif( $bot->check_active_option('general_hide_message') ){
      $return .= '<!-- Sorry:<br>'.$bot->get_active_result('message').'-->';
    }else{
      $return .= 'Sorry:<br>'.$bot->get_active_result('message');
    }
    $return .= '</div>';
    
    return $return;
  }
  add_shortcode( 'alpine-phototile-for-instagram', 'APTFINbyTAP_shortcode_function' );
   
?>