<?php
/**
 * Alpine PhotoTile for Instagram: Shortcode
 *
 * @ Since 1.1.1
 * @ Updated 1.2.7
 */
 
  function APTFINbyTAP_shortcode_function( $atts ) {
    $bot = new PhotoTileForInstagramBot();
		
		// ID is used to retreive from transient.
		// Ideally, a unique id is assigned to each shortcode 
		$array_string = implode ( $atts );
		$id = strlen( $array_string );
		if( !empty($atts['id']) ){
			$id = $id."_".$atts['id'];
		}
		
		if( isset( $atts['testmode'] ) && isset( $atts['id'] ) ){
			// Load shortcode in test mode (i.e. echo messages )
			$bot->set_private('testmode',$atts['testmode']);
			$bot->set_private('cacheid',$atts['id']);
			$id = $id."_".$atts['testmode'];
		}else{
			$bot->set_private('cacheid',$id);
		}
		
		$bot->echo_point("Start plugin.");
    $optiondetails = $bot->option_defaults();
    $options = array();
    foreach( $optiondetails as $opt=>$details ){
      $options[$opt] = $details['default'];
      if( isset($details['short']) && isset($atts[ $details['short'] ]) ){
        $options[$opt] = $atts[ $details['short'] ];
      }
    }

    $bot->set_private('wid','id-'.$id);
    $bot->set_private('options',$options);
    $bot->do_alpine_method( 'update_global_options' );
		$bot->echo_point("Enqueue styles and scripts");
    $bot->do_alpine_method( 'enqueue_style_and_script' );  
    // Do the photo search
		$bot->echo_point("Retrieve Photos");
    $bot->do_alpine_method( 'photo_retrieval' );

		$bot->echo_point("Check Results");
    $return = '<div id="'.$bot->get_private('id').'-by-shortcode-'.$id.'" class="AlpinePhotoTiles_inpost_container">';
    $return .= $bot->get_active_result('hidden');
    if( $bot->check_active_result('success') ){
			$bot->echo_point("Prepare HTML Output");
      if( 'vertical' == $options['style_option'] ){
				$bot->echo_point("Setup Vertial Style");
        $bot->do_alpine_method( 'display_vertical' );
      }elseif( 'cascade' == $options['style_option'] ){
				$bot->echo_point("Setup Cascade Style");
        $bot->do_alpine_method( 'display_cascade' );
      }else{
				$bot->echo_point("Setup jQuery Style (Window, Wall, Gallery)");
        $bot->do_alpine_method( 'display_hidden' );
      }
      $return .= $bot->get_private('output');
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    elseif( $bot->check_active_option('general_hide_message') ){
      $return .= '<!-- Sorry:<br>'.$bot->get_active_result('message').'-->';
    }else{
			$bot->echo_point("Print Message");
      $return .= 'Sorry:<br>'.$bot->get_active_result('message');
    }
    $return .= '</div>';
		
    $bot->echo_point("Loading complete");
		
		if( $bot->get_private('testmode') ){
			echo '<br><p><h3>Output</h3></p>';
		}
    return $return;
  }
  add_shortcode( 'alpine-phototile-for-instagram', 'APTFINbyTAP_shortcode_function' );
   
?>