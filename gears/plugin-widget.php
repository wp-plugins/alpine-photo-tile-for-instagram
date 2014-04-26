<?php

/**
 * Alpine PhotoTile for Instagram: WP_Widget
 *
 * @ Since 1.1.1
 * @ Updated 1.2.5
 */
 
class Alpine_PhotoTile_for_Instagram extends WP_Widget { 

	function Alpine_PhotoTile_for_Instagram() {
    $this->alpinebot = new PhotoTileForInstagramPrimary();
    $bot = $this->alpinebot;
		$widget_ops = array('classname' => $bot->get_private('id'), 'description' => __($bot->get_private('wdesc')));
		$control_ops = array('width' => 550, 'height' => 350);
		$this->WP_Widget($bot->get_private('domain'), __($bot->get_private('name')), $widget_ops, $control_ops);
	}
/**
 * Widget
 *
 * @ Updated 1.2.7
 */
	function widget( $args, $options ) {
    $bot = new PhotoTileForInstagramBot();
		extract($args);
    
    // Set Important Widget Options    
    $bot->set_private('wid',$args['widget_id']);
		$bot->set_private('cacheid',$args['widget_id']);
    $bot->set_private('options',$options);
    $bot->do_alpine_method( 'update_global_options' );
    $bot->do_alpine_method( 'enqueue_style_and_script' );  
    // Do the photo search
    $bot->do_alpine_method( 'photo_retrieval' );
    
    echo $before_widget . $before_title . $options['widget_title'] . $after_title;
    echo $bot->get_active_result('hidden');
    if( $bot->check_active_result('success') ){  
      if( isset($options['style_option']) && 'vertical' == $options['style_option'] ){
        $bot->do_alpine_method( 'display_vertical' );
      }elseif( isset($options['style_option']) && 'cascade' == $options['style_option'] ){
        $bot->do_alpine_method( 'display_cascade' );
      }else{
        $bot->do_alpine_method( 'display_hidden' );
      }
      echo $bot->get_private('output');
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    elseif( $bot->check_active_option('general_hide_message') ){
      echo '<!-- Sorry:<br>'.$bot->get_active_result('message').'-->';
    }else{
      echo 'Sorry:<br>'.$bot->get_active_result('message');
    }
    echo $after_widget;
  }
/**
 * Update
 *
 * @ Updated 1.2.5
 */
	function update( $newoptions, $oldoptions ) {
    $bot = new PhotoTileForInstagramAdmin();
    $optiondetails = $bot->option_defaults();

    foreach( $newoptions as $id=>$input ){
      $options[$id] = $bot->MenuOptionsValidate( $input,$oldoptions[$id],$optiondetails[$id] );
    }

    return $options;
	}
/**
 * Form
 *
 * @ Updated 1.2.7
 */
	function form( $options ) {

    $bot = new PhotoTileForInstagramAdmin();
    
    $widget_container = $this->get_field_id( 'AlpinePhotoTiles-container' ); ?>

    <div id="<?php echo $widget_container ?>" class="AlpinePhotoTiles-container <?php echo $bot->get_private('domain');?>">
    <?php
      $defaults = $bot->option_defaults();
      $positions = $bot->admin_get_widget_options_by_position();

    if( count($positions) ){
      foreach( $positions as $position=>$positionsinfo){
      ?>
        <div class="<?php echo $position ?>"> 
          <?php if( !empty($positionsinfo['title']) ){ ?><h4><?php echo $positionsinfo['title']; ?></h4><?php } ?>
          <table class="form-table">
            <tbody>
              <?php
              if( count($positionsinfo['options']) ){
                foreach( $positionsinfo['options'] as $optionname ){
                  $option = $defaults[$optionname];
                  $fieldname = $this->get_field_name( $option['name'] );
                  $fieldid = $this->get_field_id( $option['name'] );
                  
                  if( !empty($option['hidden-option']) && !empty($option['check']) ){
                    $show = $bot->get_option( $option['check'] );
                    if( empty($show) ){ continue; }
                  }
                  
                  if( !empty($option['parent']) ){
                    $class = $option['parent'];
                  }elseif( !empty($option['child']) ){
                    $class = $this->get_field_id($option['child']);
                  }else{
                    $class = $this->get_field_id('unlinked');
                  }
                  $trigger = (!empty($option['trigger'])?('data-trigger="'.($this->get_field_id($option['trigger'])).'"'):'');
                  $hidden = (!empty($option['hidden'])?' '.$option['hidden']:'');
                  
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
      <div class="bottom">
        <div><?php $bot->admin_donate_button();?></div>
        <div><?php _e('Add the plugin to a page or post using the ') ?><a href="<?php echo 'options-general.php?page='.$bot->get_private('settings').'&tab=generator' ?>" target="_blank">Shortcode Generator</a>.</div> 
        <div><?php _e('Check the ') ?><a href="<?php echo 'options-general.php?page='.$bot->get_private('settings').'&tab=plugin-settings' ?>" target="_blank">Plugin Settings</a> <?php _e('page for additional options.') ?></div> 
				<div><?php _e('Visit the ') ?><a href="<?php echo 'options-general.php?page='.$bot->get_private('settings').'&tab=plugin-tools' ?>" target="_blank">Plugin Tools</a> <?php _e('page to check the plugin\'s loading time on your server.') ?></div> 
        <div><?php _e('Need Help? Visit ') ?><a href="<?php echo $bot->get_private('info'); ?>" target="_blank">the Alpine Press</a> <?php _e('for more about this plugin.') ?></div>     
      </div>
    </div><?php // Close container
    
	}
  
}


?>
