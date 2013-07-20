<?php
/**
 *  AlpineBot Seconday
 * 
 *  ADMIN Functions
 *  Contains ONLY UNIVERSAL ADMIN functions
 * 
 */
class PhotoTileForInstagramAdminSecondary extends PhotoTileForInstagramPrimary{     

//////////////////////////////////////////////////////////////////////////////////////
/////////////////////////      Update Functions       ////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
/**
 * Options Simple Update for Admin Page
 *  
 * @since 1.2.0
 *
 */
  function admin_simple_update( $currenttab, $newoptions, $oldoptions ){
    $options = $this->option_defaults();
    $bytab = $this->admin_get_options_by_tab( $currenttab );
    foreach( $bytab as $id){
      $new = (isset($newoptions[$id])?$newoptions[$id]:'');
      $old = (isset($oldoptions[$id])?$oldoptions[$id]:'');
      $opt = (isset($options[$id])?$options[$id]:'');
      $oldoptions[$id] = $this->MenuOptionsValidate( $new, $old, $opt ); // Make changes to existing options array
    }
    update_option( $this->get_private('settings'), $oldoptions);
    return $oldoptions;
  }  
  
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////      Admin Option Functions      //////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
/**
 *  Create array of option names for a given tab
 *  
 *  @ Since 1.2.0
 */
  function admin_get_options_by_tab( $tab = 'generator' ){
    $default_options = $this->option_defaults();
    $return = array();
    foreach($default_options as $key => $val){
      if( $val['tab'] == $tab ){
        $return[$key] = $key;
      }
    }
    return $return;
  }
/**
 *  Create array of option names and current values for a given tab
 *  
 *  @ Since 1.2.0
 */
  function admin_get_settings_by_tab( $tab = 'generator' ){
    $current = $this->get_all_options();
    $default_options = $this->option_defaults();
    $return = array();
    foreach($default_options as $key => $val){
      if( $val['tab'] == $tab ){
        $return[$key] = $current[$key];
      }
    }
    return $return;
  }
/**
 *  Create array of positions for a given tab along with a list of settings for each position
 *  
 *  @ Since 1.2.0
 *  @ Updated 1.2.3
 */
  function get_option_positions_by_tab( $tab = 'generator' ){
    $positions = $this->admin_option_positions();
    $return = array();
    if( isset($positions[$tab]) ){
      $options = $this->admin_get_options_by_tab( $tab );
      $defaults = $this->option_defaults();
      
      foreach($positions[$tab] as $pos => $info ){
        $return[$pos]['title'] = (isset($info['title'])?$info['title']:'');
        $return[$pos]['description'] = (isset($info['description'])?$info['description']:'');
        $return[$pos]['options'] = array();
      }
      foreach($options as $name){
        $pos = (isset($defaults[$name]['position'])?$defaults[$name]['position']:'none');
        $return[ $pos ]['options'][] = $name;
      }
    }
    return $return;
  }
/**
 *  Create array of positions for each widget along with a list of settings for each position
 *  
 *  @ Since 1.2.0
 */
  function admin_get_widget_options_by_position(){
    $default_options = $this->option_defaults();
    $positions = $this->admin_widget_positions();
    $return = array();
    foreach($positions as $key => $val ){
      $return[$key]['title'] = $val;
      $return[$key]['options'] = array();
    }
    foreach($default_options as $key => $val){
      if(!empty($val['widget'])){
        $return[ $val['position'] ]['options'][] = $key;
      }
    }
    return $return; 
  }
  
//////////////////////////////////////////////////////////////////////////////////////
///////////////////////      Admin Page Functions       //////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////  

/**
 * Create shortcode based on given options
 *  
 * @ Since 1.1.0
 * @ Update 1.2.5
 */
  function admin_generate_shortcode( $options, $optiondetails ){
    $short = '['.$this->get_private('short');
    $trigger = '';
    foreach( $options as $key=>$value ){
      if($value && isset($optiondetails[$key]['short'])){
        if( isset($optiondetails[$key]['child']) && isset($optiondetails[$key]['hidden']) ){
          $hidden = @explode(' ',$optiondetails[$key]['hidden']);
          if( !in_array( $options[ $optiondetails[$key]['child'] ] ,$hidden) ){
            $short .= ' '.$optiondetails[$key]['short'].'="'.$value.'"';
          }
        }else{
          $short .= ' '.$optiondetails[$key]['short'].'="'.$value.'"';
        }
      }
    }
    $short .= ']';
    
    return $short;
  }
/**
 * Define Settings Page Tab Markup
 *  
 * @since 1.1.0
 * @link`http://www.onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs	Daniel Tara
 *
 */
  function admin_options_page_tabs( $current = 'general' ) {
    $tabs = $this->admin_settings_page_tabs();
    $links = array();
    
    foreach( $tabs as $tab ){
      $tabname = $tab['name'];
      $tabtitle = $tab['title'];
      if( $tabname == $current ){
          $links[] = '<a class="nav-tab nav-tab-active" href="?page='.$this->get_private('settings').'&tab='.$tabname.'">'.$tabtitle.'</a>';
      }else{
          $links[] = '<a class="nav-tab" href="?page='.$this->get_private('settings').'&tab='.$tabname.'">'.$tabtitle.'</a>';
      }
    }

    echo '<div class="AlpinePhotoTiles-title"><div class="icon32 icon-alpine"><br></div><h2>'.$this->get_private('name').'</h2></div>';
    echo '<div class="AlpinePhotoTiles-menu"><h2 class="nav-tab-wrapper">';
    foreach ( $links as $link ){
      echo $link;
    }
    echo '</h2></div>';
  }
/**
 * Function for printing general settings page
 *  
 * @ Since 1.2.0
 * @ Updated 1.2.4
 */
  function admin_display_general(){ 
    ?>
      <h3><?php _e("Thank you for downloading the "); echo $this->get_private('name'); _e(", a WordPress plugin by the Alpine Press.");?></h3>
      <?php if( $this->check_private('termsofservice') ) {
        echo '<p>'.$this->get_private('termsofservice').'</p>';
      }?>
      <p><?php _e("On the 'Shortcode Generator' tab you will find an easy to use interface that will help you create shortcodes. These shortcodes make it simple to insert the PhotoTile plugin into posts and pages.");?></p>
      <p><?php _e("The 'Plugin Settings' tab provides additional back-end options.");?></p>
      <p><?php _e("Finally, I am a one man programming team and so if you notice any errors or places for improvement, please let me know."); ?></p>
      <p><?php _e('If you liked this plugin, try out some of the other plugins by ') ?><a href="http://thealpinepress.com/category/plugins/" target="_blank">the Alpine Press</a>.</p>
      <br>
      <h3><?php _e('Try the other free plugins in the Alpine PhotoTile Series:');?></h3>
      <?php 
      if( $this->check_private('plugins') && is_array( $this->get_private('plugins') ) ){
        foreach($this->get_private('plugins') as $each){
          ?><a href="http://wordpress.org/extend/plugins/alpine-photo-tile-for-<?php echo $each;?>/" target="_blank"><img class="image-icon" src="<?php echo $this->get_private('url');?>/css/images/for-<?php echo $each;?>.png" style="width:100px;"></a><?php
        }
      }?>
    <?php
  }
/**
 * Function displays donation request
 *  
 * @ Since 1.2.4
 * @ Updated 1.2.5
 */
  function admin_donate_button(){
    $phrases = array('Pocket change is appreciated.','Buy me a cup of tea?','Help me pay my rent?','You tip your waiter. Why not your WordPress developer?','You tip the pizza deliver boy. Why not your WordPress programmer?');
    ?>
    <div>
      <p>Please support further development of this plugin with a small  <a target="_blank" href="<?php echo $this->get_private('donatelink');?>" title="Donate">donation</a>.
      <br><?php echo $phrases[rand(0,count($phrases)-1)];?></p>
      <p>
        <a target="_blank" href="<?php echo $this->get_private('donatelink');?>" title="Donate">
        <img class="image-icon" src="<?php echo $this->get_private('url');?>/css/images/paypal_donate.png" style="width:150px;">
        </a>
      </p>
    </div>
    <?php
  }
/**
 * First function for printing options page
 *  
 * @ Since 1.1.0
 * @ Updated 1.2.4
 *
 */
  function admin_setup_options_form($currenttab){
    $options = $this->get_all_options();     
    $settings_section = $this->get_private('id'). '_' . $currenttab . '_tab';
    $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );

    if( $submitted ){
      $options = $this->admin_simple_update( $currenttab, $_POST, $options );
    }

    $buttom = (isset($_POST[$this->get_private('settings').'_'.$currenttab]['submit-'.$currenttab])?$_POST[$this->get_private('settings').'_'.$currenttab]['submit-'.$currenttab]:'');
    if( $buttom == 'Delete Current Cache' ){
      $bot = new PhotoTileForInstagramBot();
      $bot->clearAllCache();
      echo '<div class="announcement">'.__("Cache Cleared").'</div>';
    }
    elseif( $buttom == 'Save Settings' ){
      $bot = new PhotoTileForInstagramBot();
      $bot->clearAllCache();
      echo '<div class="announcement">'.__("Settings Saved").'</div>';
    }
    echo '<form action="" method="post">';
      echo '<input type="hidden" name="hidden" value="Y">';
      $this->admin_display_opt_form($options,$currenttab);
      echo '<div class="AlpinePhotoTiles-breakline"></div>';
    echo '</form>';

  }
/**
 * Second function for printing options page
 *  
 * @ Since 1.1.0
 * @ Updated 1.2.5
 *
 */
  function admin_display_opt_form($options,$currenttab){

    $defaults = $this->option_defaults();
    $positions = $this->get_option_positions_by_tab( $currenttab );
    $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );
    
    if( 'generator' == $currenttab ) { 
      $preview = (isset($_POST[ $this->get_private('settings').'_preview']['submit-preview']) && $_POST[ $this->get_private('settings').'_preview']['submit-preview'] == 'Preview')?true:false;
      if( $submitted && isset($_POST['shortcode']) && $preview ){
        $short = str_replace('\"','"',$_POST['shortcode']);
      }elseif( $submitted ){
        $short = $this->admin_generate_shortcode( $_POST, $defaults );
      }
      ?>
      <div>
        <h3>This tool allows you to create shortcodes for the Alpine PhotoTile plugin.</h3>
        <p>A shortcode is a line of text that tells WordPress how to load a plugin inside the content of a page or post. Rather than explaining how to put together a shortcode, this tool will create the shortcode for you.</p>
      </div>
      <?php 
      if( !empty($short) ){
        ?>
        <div id="<?php echo $this->get_private('settings');?>-shortcode" style="position:relative;clear:both;margin-bottom:20px;" ><div class="announcement" style="margin:0 0 10px 0;">
          Now, copy (Crtl+C) and paste (Crtl+V) the following shortcode into a page or post. Or preview using the button below.</div>
          <div class="AlpinePhotoTiles-preview" style="border-bottom: 1px solid #DDDDDD;">
            <input type="hidden" name="hidden" value="Y">
            <textarea id="shortcode" class="auto_select" name="shortcode" style="margin-bottom:20px;"><?php echo $short;?></textarea>
            <input name="<?php echo $this->get_private('settings');?>_preview[submit-preview]" type="submit" class="button-primary" value="Preview" />
            <br style="clear:both">
          </div>
        </div>
        <?php 

        
        if( $submitted && isset($_POST['shortcode']) && $preview ){       
          echo '<div style="border-bottom: 1px solid #DDDDDD;padding-bottom:10px;margin-bottom:40px;">';
          echo do_shortcode($short);
          echo '</div>';
        }
      }
      echo '<input name="'. $this->get_private('settings').'_'.$currenttab .'[submit-'. $currenttab .']" type="submit" class="button-primary topbutton" value="Generate Shortcode" /><br> ';
    }
    if( !empty($positions) && count($positions) ){
      foreach( $positions as $position=>$positionsinfo){
        echo '<div class="'. $position .'">'; 
          if( !empty($positionsinfo['title']) ){ echo '<h4>'. $positionsinfo['title'].'</h4>'; } 
          if( !empty($positionsinfo['description']) ){ echo '<div style="margin-bottom:15px;"><span class="description" >'. $positionsinfo['description'].'</span></div>'; } 
          echo '<table class="form-table">';
            echo '<tbody>';
              if( !empty($positionsinfo['options']) && count($positionsinfo['options']) ){
                foreach( $positionsinfo['options'] as $optionname ){
                  $option = $defaults[$optionname];
                  $fieldname = ( $option['name'] );
                  $fieldid = ( $option['name'] );

                  if( !empty($option['hidden-option']) && !empty($option['check']) ){
                    $show = $this->get_option( $option['check'] );
                    if( !$show ){ continue; }
                  }
                  
                  if(isset($option['parent'])){
                    $class = $option['parent'];
                  }elseif(isset($option['child'])){
                    $class =($option['child']);
                  }else{
                    $class = ('unlinked');
                  }
                  $trigger = (isset($option['trigger'])?('data-trigger="'.(($option['trigger'])).'"'):'');
                  $hidden = (isset($option['hidden'])?' '.$option['hidden']:'');
                  
                  if( 'generator' == $currenttab ){                  
                    echo '<tr valign="top"> <td class="'.$class.' '.$hidden.'" '.$trigger.'>';
                      $this->MenuDisplayCallback($options,$option,$fieldname,$fieldid);
                    echo '</td></tr>';   
                  }else{
                    echo '<tr valign="top"><td class="'.$class.' '.$hidden.'" '.$trigger.'>';
                      $this->AdminDisplayCallback($options,$option,$fieldname,$fieldid);
                    echo '</td></tr>';   
                  }       
                }
              }
            echo '</tbody>';
          echo '</table>';
        echo '</div>';
      }
    }
    
    if( 'generator' == $currenttab ) {
      echo '<input name="'.$this->get_private('settings').'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" value="Generate Shortcode" />';
    }elseif( 'plugin-settings' == $currenttab ){
      echo '<input name="'.$this->get_private('settings').'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" value="Save Settings" />';
      echo '<input name="'.$this->get_private('settings').'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" style="margin-top:15px;" value="Delete Current Cache" />';
    }
  }
    
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////      Menu Display Functions       /////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////  
/**
  * Function for displaying forms in the widget page
  *
  *  @ Since 1.0.0
  *  @ Updated 1.2.5
  */
  function MenuDisplayCallback($options,$option,$fieldname,$fieldid){
    $default = (isset($option['default'])?$option['default']:'');
    $optionname = (isset($option['name'])?$option['name']:'');
    $optiontitle = (isset($option['title'])?$option['title']:'');
    $optiondescription = (isset($option['description'])?$option['description']:'');
    $fieldtype = (isset($option['type'])?$option['type']:'');
    $value = ( isset($options[$optionname]) ? $options[$optionname] : $default );
    
     // Output checkbox form field markup
    if ( 'checkbox' == $fieldtype ) {
      ?>
      <input type="checkbox" id="<?php echo $fieldid; ?>" name="<?php echo $fieldname; ?>" value="1" <?php checked( $value ); ?> />
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    }
    // Output radio button form field markup
    else if ( 'radio' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options'];
      ?><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label><?php
      foreach ( $valid_options as $valid_option ) {
        ?>
        <input type="radio" name="<?php echo $fieldname; ?>" <?php checked( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" />
        <span class="description"><?php echo $optiondescription; ?></span>
        <?php
      }
    }
    // Output select form field markup
    else if ( 'select' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options']; 
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        foreach ( $valid_options as $valid_option ) {
          ?>
          <option <?php selected( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" ><?php echo $valid_option['title']; ?></option>
          <?php
        }
        ?>
        </select>
        <div class="description"><span class="description"><?php echo $optiondescription; ?></span></div>
      <?php
    } // Output select form field markup
    else if ( 'range' == $fieldtype ) {     
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        for($i = $option['min'];$i <= $option['max']; $i++){
          ?>
          <option <?php selected( $i == $value ); ?> value="<?php echo $i; ?>" ><?php echo $i ?></option>
          <?php
        }
        ?>
        </select>
        <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } 
    // Output text input form field markup
    else if ( 'text' == $fieldtype ) {
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" value="<?php echo ( $value ); ?>" />
      <div class="description"><span class="description"><?php echo $optiondescription; ?></span></div>
      <?php
    } 
    else if ( 'textarea' == $fieldtype ) {
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
      <span class="description"><?php echo (function_exists('esc_textarea')?esc_textarea( $optiondescription ):$optiondescription); ?></span>
      <?php
    }   
    else if ( 'color' == $fieldtype ) {
      $value = ($value?$value:$default);
      ?>    
      <label for="<?php echo $fieldid ?>">
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_color"  value="<?php echo ( $value ); ?>" /><span class="description"><?php echo $optiondescription; ?></span></label>
      <div id="<?php echo $fieldid; ?>_picker" class="AlpinePhotoTiles_color_picker" ></div>
      <?php
    }
  }

/**
 *  Function for displaying forms in the admin page
 *  
 *  @ Since 1.0.0
 *  @ Updated 1.2.6
 */
  function AdminDisplayCallback($options,$option,$fieldname,$fieldid){
    $default = (isset($option['default'])?$option['default']:'');
    $optionname = (isset($option['name'])?$option['name']:'');
    $optiontitle = (isset($option['title'])?$option['title']:'');
    $optiondescription = (isset($option['description'])?$option['description']:'');
    $fieldtype = (isset($option['type'])?$option['type']:'');
    $value = ( isset($options[$optionname]) ? $options[$optionname] : $default );
    
     // Output checkbox form field markup
    if ( 'checkbox' == $fieldtype ) {
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="checkbox" id="<?php echo $fieldid; ?>" name="<?php echo $fieldname; ?>" value="1" <?php checked( $value ); ?> />
      <div class="admin-description" ><?php echo $optiondescription; ?></div>
      <?php
    }
    // Output radio button form field markup
    else if ( 'radio' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options'];
      ?><div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div><?php
      foreach ( $valid_options as $valid_option ) {
        ?>
        <input type="radio" name="<?php echo $fieldname; ?>" <?php checked( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" />
        <span class="admin-description"><?php echo $optiondescription; ?></span>
        <?php
      }
    }
    // Output select form field markup
    else if ( 'select' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options']; 
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        foreach ( $valid_options as $valid_option ) {
          ?>
          <option <?php selected( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" ><?php echo $valid_option['title']; ?></option>
          <?php
        }
        ?>
        </select>
        <div class="admin-description"><?php echo $optiondescription; ?></div>
      <?php
    } // Output select form field markup
    else if ( 'range' == $fieldtype ) {     
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        for($i = $option['min'];$i <= $option['max']; $i++){
          ?>
          <option <?php selected( $i == $value ); ?> value="<?php echo $i; ?>" ><?php echo $i ?></option>
          <?php
        }
        ?>
        </select>
        <div class="admin-description"><?php echo $optiondescription; ?></div>
      <?php
    } 
    // Output text input form field markup
    else if ( 'text' == $fieldtype ) {
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" value="<?php echo ( $value ); ?>" />
      <div class="admin-description" style="width:50%;"><?php echo $optiondescription; ?></div>
      <?php
    } 
    else if ( 'textarea' == $fieldtype ) {
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
      <span class="admin-description"><?php echo (function_exists('esc_textarea')?esc_textarea( $optiondescription ):$optiondescription); ?></span>
      <?php
    }   
    else if ( 'color' == $fieldtype ) {
      $value = ($value?$value:$default);
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_color"  value="<?php echo ( $value ); ?>" /><div class="admin-description" style="width:40%;"><?php echo $optiondescription; ?></div></label>
      <div id="<?php echo $fieldid; ?>_picker" class="AlpinePhotoTiles_color_picker" ></div>
      <?php
    }
  }

//////////////////////////////////////////////////////////////////////////////////////
////////////////////      Input Validation Functions       ///////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
/**
 * Options Validate Pseudo-Callback
 *
 * @ Since 1.0.0
 * @ Updated 1.2.6
 */
  function MenuOptionsValidate( $newinput, $oldinput, $optiondetails ) {
      $valid_input = $oldinput;
      $type = (isset($optiondetails['type'])?$optiondetails['type']:'');
      // Validate checkbox fields
      if ( 'checkbox' == $type ) {
        // If input value is set and is true, return true; otherwise return false
        $valid_input = ( ( isset( $newinput ) && true == $newinput ) ? true : false );
      }
      // Validate radio button fields
      else if ( 'radio' == $type ) {
        // Get the list of valid options
        $valid_options = $optiondetails['valid_options'];
        // Only update setting if input value is in the list of valid options
        $valid_input = ( array_key_exists( $newinput, $valid_options ) ? $newinput : $valid_input );
      }
      // Validate select fields
      else if ( 'select' == $type || 'select-trigger' == $type) {
        // Get the list of valid options
        $valid_options = $optiondetails['valid_options'];
        // Only update setting if input value is in the list of valid options
        $valid_input = ( (isset($newinput) && is_array($valid_options) && array_key_exists( $newinput, $valid_options )) ? $newinput : $valid_input );
      }
      else if ( 'range' == $type ) {
        // Only update setting if input value is in the list of valid options
        $max = (isset($optiondetails['max'])?$optiondetails['max']:100);
        $min = (isset($optiondetails['min'])?$optiondetails['min']:0);
        $valid_input = ( ($newinput>=$min && $newinput<=$max) ? $newinput : $valid_input );
      }    
      // Validate text input and textarea fields
      else if ( ( 'text' == $type || 'textarea' == $type || 'image-upload' == $type) ) {
        $valid_input = strip_tags( $newinput );
        $sanatize = (isset($optiondetails['sanitize'])?$optiondetails['sanitize']:'');
        // Validate no-HTML content
        // nospaces option offers additional filters
        if ( 'nospaces' == $sanatize ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          
          // Remove specified character(s)
          if( isset($optiondetails['remove']) ){
            if( is_array($optiondetails['remove']) ){
              foreach( $optiondetails['remove'] as $remove ){
                $valid_input = str_replace($remove,'',$valid_input);
              }
            }else{
              $valid_input = str_replace($optiondetails['remove'],'',$valid_input);
            }
          }
          // Switch or encode characters
          if( isset($optiondetails['encode']) && is_array( $optiondetails['encode'] ) ){
            foreach( $optiondetails['encode'] as $find=>$replace ){
              $valid_input = str_replace($find,$replace,$valid_input);
            }
          }
          // Replace spaces with provided character or just remove spaces
          if( isset($optiondetails['replace']) ){
            $valid_input = str_replace(array('  ',' '),$optiondetails['replace'],$valid_input);
          }else{
            $valid_input = str_replace(' ','',$valid_input);
          }
        }
        // Check if numeric
        elseif ( 'numeric' == $sanatize && is_numeric( wp_filter_nohtml_kses( $newinput ) ) ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          if( isset($optiondetails['min']) && $valid_input<$optiondetails['min']){
            $valid_input = $optiondetails['min'];
          }
          if( isset($optiondetails['max']) && $valid_input>$optiondetails['max']){
            $valid_input = $optiondetails['max'];
          }
        }
        elseif ( 'int' == $sanatize && is_numeric( wp_filter_nohtml_kses( $newinput ) ) ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = round( wp_filter_nohtml_kses( $newinput ) );
          if( isset($optiondetails['min']) && $valid_input<$optiondetails['min']){
            $valid_input = $optiondetails['min'];
          }
          if( isset($optiondetails['max']) && $valid_input>$optiondetails['max']){
            $valid_input = $optiondetails['max'];
          }
        }
        elseif ( 'tag' == $sanatize ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          $valid_input = str_replace(' ','-',$valid_input);
        }            
        // Validate no-HTML content
        elseif ( 'nohtml' == $sanatize ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          $valid_input = str_replace(' ','',$valid_input);
        }
        // Validate HTML content
        elseif ( 'html' == $sanatize ) {
          // Pass input data through the wp_filter_kses filter using allowed post tags
          $valid_input = wp_kses_post($newinput );
        }
        // Validate URL address
        elseif( 'url' == $sanatize ){
          $valid_input = esc_url( $newinput );
        }
        // Validate CSS
        elseif( 'css' == $sanatize ){
          $valid_input = wp_htmledit_pre( stripslashes( $newinput ) );
        }     
        // Just strip slashes
        elseif( 'stripslashes' == $sanatize ){
          $valid_input = stripslashes( $newinput );
        }
      }else if( 'wp-textarea' == $type ){
          // Text area filter
          $valid_input = wp_kses_post( force_balance_tags($newinput) );
      }
      elseif( 'color' == $type ){
        $value =  wp_filter_nohtml_kses( $newinput );
        if( '#' == $value ){
          $valid_input = '';
        }else{
          $valid_input = $value;
        }
      }
      return $valid_input;
  } 

}  
/** ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *
 *    AlpineBot
 * 
 *    ADMIN Functions
 *    Contains ONLY UNIQUE ADMIN functions
 * 
 * ##########################################################################################
 */
class PhotoTileForInstagramAdmin extends PhotoTileForInstagramAdminSecondary{

//////////////////////////////////////////////////////////////////////////////////////
////////////////////        Unique Admin Functions        ////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////    
  
/**
 * Add User
 *  
 * @ Since 1.2.0
 * @ Updated 1.2.4
 */
  function AddUser( $post_content ){
    /* $post_content = array(
        'access_token' => $access_token,
        'username' => $user->username,
        'picture' => $user->profile_picture,
        'fullname' => $user->full_name,
        'client_id' => $client_id,
        'client_secret' => $client_secret
      );*/
    if( !empty($post_content['access_token']) && !empty($post_content['username']) && !empty($post_content['user_id']) ){
      $user = $post_content['username'];
      $oldoptions = $this->get_all_options();
      $currentUsers = $oldoptions['users'];
      //if( empty($currentUsers[ $user ]) || ($currentUsers[ $user ]['access_token'] != $post_content['access_token']) ){
        $post_content['name'] = $user;
        $post_content['title'] = $user;
        $currentUsers[ $user ] = $post_content;
        $oldoptions['users'] = $currentUsers;
        update_option( $this->get_private('settings'), $oldoptions);
      //}
    }
    return true;
  } 
/**
 * Delete User
 *  
 * @since 1.2.0
 *
 */
  function DeleteUser( $user ){
    $oldoptions = $this->get_all_options();
    $currentUsers = $oldoptions['users'];
    if( !empty($currentUsers[$user]) ){
      unset($currentUsers[$user]);
    }
    $oldoptions['users'] = $currentUsers;
    update_option( $this->get_private('settings'), $oldoptions);
  }
/**
 * Update User
 *  
 * @since 1.2.0
 *
 */
  function UpdateUser( $data ){
    $oldoptions = $this->get_all_options();
    $currentUsers = $oldoptions['users'];

    if( !empty($data['username']) && !empty($oldoptions['users']) && !empty($oldoptions['users'][$data['username']]) ) {
      $current = $oldoptions['users'][$data['username']];
      foreach( $data as $k=>$v ){
        if( !empty($v) ){
          $current[$k] = $v;
        }
      }
      $oldoptions['users'][$data['username']] = $current;
      update_option( $this->get_private('settings'), $oldoptions);
    }
  }
  
 /**
   * Alpine PhotoTile: Options Page
   *
   * @ Since 1.1.1
   * @ Updated 1.2.4
   */
  function admin_build_settings_page(){
    $currenttab = isset( $_GET['tab'] )?$_GET['tab']:'general'; 
    
    echo '<div class="wrap AlpinePhotoTiles_settings_wrap">';
    $this->admin_options_page_tabs( $currenttab );

      echo '<div class="AlpinePhotoTiles-container '.$this->get_private('domain').'">';
        echo '<div class="AlpinePhotoTiles-'.$currenttab.'" style="position:relative;width:100%;">';
          if( 'general' == $currenttab ){
            $this->admin_display_general();
          }elseif( 'add' == $currenttab ){
            $this->admin_display_add();
          }else{
            $this->admin_setup_options_form($currenttab);
          }
        echo '</div>';
        
        echo '<div class="bottom" style="position:relative;width:100%;margin-top:20px;">';
          $this->admin_donate_button();
          echo '<div class="help-link"><p>'.__('Need Help? Visit ').'<a href="'.$this->get_private('info').'" target="_blank">the Alpine Press</a>'.__(' for more about this plugin.').'</p></div>';  
        echo '</div>';
      echo '</div>'; // Close Container
      
    echo '</div>'; // Close wrap
  }
  
/**
 * Show User Function
 *  
 * @ Since 1.2.0
 * @ Updated 1.2.5
 */
  function show_user($info){
    $name = (isset($info['username'])?$info['username']:'user');
    $picture = (isset($info['picture'])?$info['picture']:'user');
    $output = '<div id="user-icon-'.$name.'" class="user-icon" style="padding-bottom:10px;">';
    $output .=  '<div><h4>'.$name.'</h4></div>';
    $output .=  '<div><img src="'.$picture.'" style="width:80px;height:80px;"></div>';
    // Not currently needed
    $output .=  '<form id="'.$this->get_private('settings').'-user-'.$name.'" action="" method="post">';
    $output .=  '<input type="hidden" name="hidden" value="Y">';
    $output .=  '<input type="hidden" name="update-user" value="Y">';
    $output .=  '<input type="hidden" name="user" value="'.$name.'">';
    $output .=  '<input id="'.$this->get_private('settings').'-submit" name="'.$this->get_private('settings').'_update[submit-update]" type="submit" class="button-primary" style="margin-top:15px;float:none;" value="Update User" />';
    $output .=  '</form>';
    $output .=  '<form id="'.$this->get_private('settings').'-delete-'.$name.'" action="" method="post">';
    $output .=  '<input type="hidden" name="hidden" value="Y">';
    $output .=  '<input type="hidden" name="delete-user" value="Y">';
    $output .=  '<input type="hidden" name="user" value="'.$name.'">';
    $output .=  '<input id="'.$this->get_private('settings').'-submit" name="'.$this->get_private('settings').'_delete[submit-delete]" type="submit" class="button-primary" style="margin-top:15px;float:none;" value="Delete User" />';
    $output .=  '</form>';
    $output .=  '</div>';
    return $output;
  }
/**
 * Show User Javascript
 *  
 * @ Since 1.2.0
 *
 * // Not currently needed
 */
  function show_user_js($info){
    $redirect = admin_url( 'options-general.php?page='.$this->get_private('settings').'&tab=add' );
    $output = 'jQuery(document).ready(function() {var url = "https://api.instagram.com/oauth/authorize/"+"?redirect_uri=" + encodeURIComponent("'.$redirect . '")+ "&response_type=code" + "&client_id='.$info['client_id'].'" + "&display=touch";jQuery("#'.$this->get_private('settings').'-user-'.$info['username'].'").ajaxForm({ success: function(responseText){  window.location.replace(url); } });  });';
    return $output;
  }
/**
 * Display Add User Page
 *  
 * @ Since 1.2.0
 * @ Updated 1.2.6
 */
  function admin_display_add(){ 
  
    $currenttab = 'add';
    $options = $this->get_all_options();     
    $settings_section = $this->get_private('id') . '_'.$currenttab.'_tab';
    $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );
    
    $redirect = admin_url( 'options-general.php?page='.$this->get_private('settings').'&tab='.$currenttab );
    $success = false;
    $errormessage = null;
    $errortype = null;
      
      
    if( isset($_POST['add-user']) ){
      if( !empty($_POST['client_id']) && !empty($_POST['client_secret']) ){
        $options = $this->admin_simple_update( $currenttab, $_POST, $options ); // Don't display previously input info
        ?>
        <script>
          var url = 'https://api.instagram.com/oauth/authorize/'
          + '?redirect_uri=' + encodeURIComponent("<?php echo $redirect; ?>")
          + '&response_type=code'
          + '&client_id=<?php echo $_POST['client_id']; ?>'
          + '&display=touch';

          window.location.replace(url);
       </script>
        <?php
      }else{
        $errormessage = 'Please enter both a Client ID and Client Secret';
      }
    }
    elseif( isset($_POST['delete-user']) && isset($_POST['user']) ){
      $delete = true;
      $user = $_POST['user'];
      $this->DeleteUser( $user );
    }
    elseif( isset($_POST['update-user']) && isset($_POST['user']) ){
      $user = $_POST['user'];
      $users = $this->get_instagram_users();
      if( !empty($users) && !empty($users[$user]) && !empty($users[$user]['access_token']) && !empty($users[$user]['user_id']) ){
        $request = 'https://api.instagram.com/v1/users/'.$users[$user]['user_id'].'/?access_token='.$users[$user]['access_token'];
        $response = wp_remote_get($request,
          array(
            'method' => 'GET',
            'timeout' => 10,
            'sslverify' => apply_filters('https_local_ssl_verify', false)
          )
        );
        if( is_wp_error( $response ) || !isset($response['body']) ) {
          // Try again
          if( method_exists( $this, 'manual_cURL' ) ){
            $content = $this->manual_cURL($request);
          }
          
          if( !isset($content) ){
            $errormessage = 'User not updated';
          }
        }else{
          $content = $response['body'];
        }
        
        if( isset( $content ) ){
          if( function_exists('json_decode') ){
            $_instagram_json = @json_decode( $content, true );
          }
          if( empty($_instagram_json) && method_exists( $this, 'json_decoder' ) ){
            $_instagram_json = $this->json_decoder($content);
          }
          if( empty($_instagram_json) || 200 != $_instagram_json['meta']['code'] ){
            $errormessage = 'User not updated';
          }elseif( !empty($_instagram_json['data']) ){
            $data = $_instagram_json['data'];
            $post_content = array(
              'access_token' => $users[$user]['access_token'],
              'username' => $data['username'],
              'picture' => $data['profile_picture'],
              'fullname' => $data['full_name'],
              'user_id' => $users[$user]['user_id']
            );
            $this->UpdateUser( $post_content );
            $update = true;
          }
        }
      }
    }
    elseif( $submitted && isset($_POST['manual-user-form']) && !empty($_POST['access_token']) && !empty($_POST['user_id']) && !empty($_POST['client_id']) && !empty($_POST['username']) ){
      $success = $this->AddUser($_POST);
    }
    elseif( isset($_GET['code']) ) {
      $code = $_GET['code'];
      $client_id = $this->get_option('client_id');
      $client_secret = $this->get_option('client_secret');
      $url = 'https://api.instagram.com/oauth/access_token';
      $fields = array(
            'code' => $code,
            'response_type' => 'authorization_code',
            'redirect_uri' => $redirect,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code'
          );
          
      $response = wp_remote_post($url,
        array(
          'body' => $fields,
          'sslverify' => apply_filters('https_local_ssl_verify', false)
        )
      );
      
      $access_token = null;
      $username = null;
      $image = null;

      if( is_wp_error( $response ) || !isset($response['body']) ) {
        // Try again
        if( method_exists( $this, 'manual_cURL' ) ){
          $content = $this->manual_cURL($url,$fields);
        }
        if( !isset($content) ){
          $errormessage = 'User not added';
        }
      }else{
        $content = $response['body'];
      }
        
      if( isset($content) ) {
        if( function_exists('json_decode') ){
          $auth = @json_decode( $content, true );
        }
        if( empty($auth) && method_exists( $this, 'json_decoder' ) ){
          // Try alternative decode
          $auth = $this->json_decoder($content);
        }
        if( isset($auth['access_token']) ) {
          $access_token = $auth['access_token'];
          $user = $auth['user'];
          
          $post_content = array(
            'access_token' => $access_token,
            'username' => $user['username'],
            'picture' => $user['profile_picture'],
            'fullname' => $user['full_name'],
            'user_id' => $user['id'],
            'client_id' => $client_id,
            'client_secret' => $client_secret
          );
          $success = $this->AddUser($post_content);
        }else{
          $errormessage = 'No access token found';
        }
      }elseif( !is_wp_error($response) && $response['response']['code'] >= 400 ) {
        $error = json_decode($response['body']);
        $errormessage = $error->error_message;
        $errortype = $error->error_type;
      }
    }

    $defaults = $this->option_defaults();
    $positions = $this->get_option_positions_by_tab( $currenttab );
    
    echo '<div class="AlpinePhotoTiles-add">';
        if( !empty($success) ){
          echo '<div class="announcement"> User successfully authorized. </div>';
        }elseif( !empty($update) ){
          echo '<div class="announcement"> User ('.$user.') updated. </div>';
        }elseif( !empty($delete) ){
          echo '<div class="announcement"> User ('.$user.') deleted. </div>';
        }elseif( !empty($errormessage) ){
          echo '<div class="announcement"> An error occured ('.$errormessage.'). </div>';
        }
        if( count($positions) ){
          foreach( $positions as $position=>$positionsinfo){
            if( $position == 'top'){
              echo '<div id="AlpinePhotoTiles-user-list" style="margin-bottom:20px;padding-bottom:20px;overflow:hidden;border-bottom: 1px solid #DDDDDD;">'; 
              if( $positionsinfo['title'] ){ echo '<h4>'. $positionsinfo['title'].'</h4>'; } 
              $users = $this->get_instagram_users();
              if( empty($users) || ( is_array( $users ) && isset($users['none']) && is_array( $users['none'] ) ) ){
                echo '<p id="AlpinePhotoTiles-user-empty">No users available. Add a user by following the instructions below.</p>';
              }elseif( !empty($users) && is_array($users) ){
                foreach($users as $name=>$info){
                  echo $this->show_user($info);
                  //echo '<script type = "text/javascript">'.$this->show_user_js($info).'</script>'; // Not currently needed
                }
              }
              echo '</div>';
            }else{
              echo '<div id="AlpinePhotoTiles-user-form" style="margin-bottom:20px;padding-bottom:20px;overflow:hidden;border-bottom: 1px solid #DDDDDD;">'; 
                ?>
                <form id="<?php echo $this->get_private('settings')."-add-user";?>" action="" method="post">
                <input type="hidden" name="hidden" value="Y">
                <input type="hidden" name="add-user" value="Y">
                  <?php 
                echo '<div class="'. $position .'">'; 
                  if( $positionsinfo['title'] ){ echo '<h4>'. $positionsinfo['title'].'</h4>'; } 
                  echo '<table class="form-table">';
                    echo '<tbody>';
                      if( count($positionsinfo['options']) ){
                        foreach( $positionsinfo['options'] as $optionname ){
                          $option = $defaults[$optionname];
                          $fieldname = ( $option['name'] );
                          $fieldid = ( $option['name'] );

                          echo '<tr valign="top"><td>';
                            $this->AdminDisplayCallback(array() ,$option,$fieldname,$fieldid); // Don't display previously input info
                          echo '</td></tr>';   
                              
                        }
                      }
                    echo '</tbody>';
                  echo '</table>';
                echo '</div>';
                echo '<input id="'.$this->get_private('settings').'-submit" name="'.$this->get_private('settings').'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" style="margin-top:15px;" value="Add and Authorize New User" />';
                echo '</form>';
                echo '<br style="clear:both;">';
              echo '</div>';
            }
          }
        }
    echo '</div>'; // close add div
          ?>
        <div style="max-width:680px;">
          <h1><?php _e('How to get your Instagram Client ID and Client Secret');?> :</h1>
          <h2>(<?php _e("Don't worry. I promise it's EASY");?>!!!)</h2>
          <p><?php _e("Instagram is quite protective of its users. Before your WordPress website can retrieve images from Instagram, you must authorize your WordPress site to access your Instagram account. This is done by following these 5 simple steps: <br>(Please <a href=".$this->get_private('info'). ">let me know</a> if these directions become outdated)");?>
          <ol>
            <li>
              <?php _e('Before starting, go to Instagram.com and make sure you are logged into the account you wish to add. Once you are logged in, visit');?> <a href="http://instagram.com/developer" target="_blank">http://instagram.com/developer</a>.
            </li>
            <li>
              <?php _e('Click on the "Manage Clients" link, as shown below.');?>
              <p><img src="<?php echo $this->get_private('url');?>/css/images/manage-clients.png"/></p>
              <p><?php _e('If this is the first time you are adding an app or plugin, Instagram will ask you a few questions. You can enter these responses, click "Sign Up", and then click "Manage Clients" again:');?></p>
              <dt><strong><?php _e('Your website:');?></strong></dt>
              <dd><em><?php _e('Enter your website url');?></em></dd>
              <dt><strong><?php _e('Phone number:');?></strong></dt>
              <dd><em><?php _e('Enter your phone number (They have never called me...)');?></em></dd>
              <dt><strong><?php _e('What do you want to build with the API?');?></strong></dt>
              <dd><em><?php _e('A plugin for my WordPress website.');?></em></dd>
              <p><img src="<?php echo $this->get_private('url');?>/css/images/sign-up.png"/></p>
            </li>
            <li>
              <?php _e('Register your WordPress site by click the "Register a New Client" button.');?>
              <p><img src="<?php echo $this->get_private('url');?>/css/images/register-client.png"/></p>
            </li>
            <li>
              <p><?php _e('Fill in the "Register new OAuth Client" form with the following infomation and click "Register":');?></p>
              <dl>
                <dt><strong><?php _e('Application name');?></strong></dt>
                <dd><p><?php _e('Enter the name of your WordPress website');?></p></dd>
                <dt><strong><?php _e('Description');?></strong></dt>
                <dd><p><?php echo $this->get_private('name');?> WordPress plugin</p></dd>
                <dt><strong><?php _e('Website');?></strong></dt>
                <dd><p><?php _e('Enter your website url');?></p></dd>
                <dt><strong><?php _e('OAuth redirect_url');?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;**<?php _e('This must be copied exactly as shown below');?>**</dt>
                
                <dd><p style="color:red;"><?php echo $redirect; ?></p></dd>
              </dl>
              <p><img src="<?php echo $this->get_private('url');?>/css/images/register.png"/></p>
            </li>
            <li>
              <?php _e('Enter the Client ID and Client Secret into the form above and click "Add and Authorize New User". You will then be directed to an Instagram page where you can finish the authorization. I hope you enjoy the plugin.');?>
            </li>
          </ol>
        </div>

      <div style="margin-top:80px;border-top: 1px solid #DDDDDD;">
        <h1>If the above method does not seem to be working:</h1>
        <p>I have setup a troubleshooting tool at <a href="http://thealpinepress.com/instagram-tool/" target="_blank">the Alpine Press</a> that you can use to manually retrieve the information you need.</p>
        <p>Once this is done, fill out and submit the form below.</p>
        
         <div id="AlpinePhotoTiles-manual-user-form" style="overflow:hidden;">
            <form id="alpine-photo-tile-for-instagram-settings-add-user" method="post" action="">
              <input type="hidden" value="Y" name="hidden">
                <div class="center">
                  <table class="form-table">
                    <tbody>
                      <?php  $the_content = array('username' => 'Username','user_id' =>'User ID','access_token' => 'Access Token','client_id' => 'Client ID','client_secret' => 'Client Secret','picture' => 'Picture');
                        foreach($the_content as $name=>$title){
                        ?>
                        <tr valign="top">
                        <td>
                          <div class="title">
                          <label for="<?php echo $name;?>"><?php echo $title;?> : </label>
                          </div>
                          <input id="<?php echo $name;?>" type="text" value="" name="<?php echo $name;?>">
                        </td>
                        </tr>

                        <?php }?>
                    </tbody>
                  </table>
                </div>
              <input id="manual-form-submit" class="button-primary" type="submit" value="Add New User" style="margin-top:15px;" name="manual-user-form">
            </form>
          <br style="clear:both;">
        </div>

      </div>
    <?php

  }
}


?>
