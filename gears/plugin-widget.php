<?php


class Alpine_PhotoTile_for_Instagram extends WP_Widget { 
  public $alpinebot;
  
	function Alpine_PhotoTile_for_Instagram() {
    $this->alpinebot = new PhotoTileForInstagramBot();
    $bot = $this->alpinebot;
		$widget_ops = array('classname' => $bot->name, 'description' => __($bot->desc));
		$control_ops = array('width' => 550, 'height' => 350);
		$this->WP_Widget($bot->domain, __($bot->name), $widget_ops, $control_ops);
	}
  
	function widget( $args, $options ) {
    $bot = $this->alpinebot;
    
		extract($args);
    if( $options['instagram_image_link_option'] == "fancybox" ){
      wp_enqueue_script( 'fancybox' );
      wp_enqueue_style( 'fancybox-stylesheet' );
    }
    wp_enqueue_style($bot->wcss);
    wp_enqueue_script($bot->wjs);

    // Set Important Widget Options    
    $id = $args["widget_id"];
    $source_results = $bot->photo_retrieval($id, $options);
    
    echo $before_widget . $before_title . $options['widget_title'] . $after_title;
    echo $source_results['hidden'];
    if( $source_results['continue'] ){  
      if( "vertical" == $options['style_option'] ){
        echo $bot->display_vertical($id, $options, $source_results);
      }elseif( "cascade" == $options['style_option'] ){
        echo $bot->display_cascade($id, $options, $source_results);
      }else{
        echo $bot->display_hidden($id, $options, $source_results);
      }
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    else{
      echo 'Sorry:<br>'.$source_results['message'];
    }
    echo $after_widget;
  }
    
	function update( $newoptions, $oldoptions ) {
    $bot = $this->alpinebot;
    $optiondetails = $bot->option_defaults();

    foreach( $newoptions as $id=>$input ){
      $options[$id] = $bot->MenuOptionsValidate( $input,$oldoptions[$id],$optiondetails[$id] );
    }

    return $options;
	}

	function form( $options ) {
    $bot = $this->alpinebot;

    $widget_container = $this->get_field_id( 'AlpinePhotoTiles-container' ); ?>

    <div id="<?php echo $widget_container ?>" class="AlpinePhotoTiles-container <?php echo $bot->domain;?>">
    <?php
      $defaults = $bot->option_defaults();
      $positions = $bot->get_widget_options_by_position();
   
    if( count($positions) ){
      foreach( $positions as $position=>$positionsinfo){
      ?>
        <div class="<?php echo $position ?>"> 
          <?php if( $positionsinfo['title'] ){ ?><h4><?php echo $positionsinfo['title']; ?></h4><?php } ?>
          <table class="form-table">
            <tbody>
              <?php
              if( count($positionsinfo['options']) ){
                foreach( $positionsinfo['options'] as $optionname ){
                  $option = $defaults[$optionname];
                  $fieldname = $this->get_field_name( $option['name'] );
                  $fieldid = $this->get_field_id( $option['name'] );
                  if($option['parent']){
                    $class = $option['parent'];
                  }elseif($option['child']){
                    $class = $this->get_field_id($option['child']);
                  }else{
                    $class = $this->get_field_id('unlinked');
                  }
                  $trigger = ($option['trigger']?('data-trigger="'.($this->get_field_id($option['trigger'])).'"'):'');
                  $hidden = ($option['hidden']?' '.$option['hidden']:'');
                  
                  ?> <tr valign="top"> <td class="<?php echo $class; ?><?php echo $hidden; ?>" <?php echo $trigger; ?>><?php
                    $bot->MenuDisplayCallback($options,$option,$fieldname,$fieldid);
                  ?> </td></tr> <?php
                }
              }?>
            </tbody>  
          </table>
        </div>
      <?php
      }
    }
    ?>
    </div> 
    <div><span><?php _e('Need Help? Visit ') ?><a href="<?php echo $bot->info; ?>" target="_blank">the Alpine Press</a> <?php _e('for more about this plugin.') ?></span></div> 
    <?php
	}
  
}


?>
