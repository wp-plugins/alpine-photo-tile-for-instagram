<?php


class PhotoTileForInstagramBasic extends PhotoTileForInstagramBase{  
/**
 *  Simple function to get option setting
 *  
 *  @ Since 1.2.0
 */
  function get_option( $option_string ){
    $options = get_option( $this->settings );
    // No need to initialize options since defaults are applied as needed
    return ( NULL!==$options[$option_string] ? $options[$option_string] : $this->set_default_option( $options, $option_string ) );
  }
/**
 *  Simple function to array of all option settings
 *  
 *  @ Since 1.2.0
 */
  function get_all_options(){
    $options = get_option( $this->settings );
    $defaults = $this->option_defaults(); 
    foreach( $defaults as $option_string => $details ){
      if( NULL === $options[$option_string] && !empty($default_options[$option_string]['default']) ){
        $options[$option_string] = $default_options[$option_string]['default'];
      }
    }
    //update_option( $this->settings, $options ); Unnecessary since options will soon be updated if this fuction was called
    return $options;
  }
/**
 *  Correctly set and save the option's default setting
 *  
 *  @ Since 1.2.0
 */
  function set_default_option( $options, $option_string ){
    $default_options = $this->option_defaults();
    if( NULL !== $default_options[$option_string] ){
      $options[$option_string] = $default_options[$option_string]['default'];
      update_option( $this->settings, $options );
      return $options[$option_string];
    }else{
      return NULL;
    }
  }
/**
 *  Create array of option names for a given tab
 *  
 *  @ Since 1.2.0
 */
  function get_options_by_tab( $tab = 'generator' ){
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
  function get_settings_by_tab( $tab = 'generator' ){
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
 */
  function get_option_positions_by_tab( $tab = 'generator' ){
    $positions = $this->option_positions();
    $return = array();
    if( NULL !== $positions[$tab] ){
      $options = $this->get_options_by_tab( $tab );
      $defaults = $this->option_defaults();
      
      foreach($positions[$tab] as $pos => $title ){
        $return[$pos]['title'] = $title;
        $return[$pos]['options'] = array();
      }
      foreach($options as $name){
        $pos = $defaults[$name]['position'];
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
  function get_widget_options_by_position(){
    $default_options = $this->option_defaults();
    $positions = $this->widget_positions();
    $return = array();
    foreach($positions as $key => $val ){
      $return[$key]['title'] = $val;
      $return[$key]['options'] = array();
    }
    foreach($default_options as $key => $val){
      if($val['widget']){
        $return[ $val['position'] ]['options'][] = $key;
      }
    }
    return $return; 
  }
/**
 * Options Simple Update for Admin Page
 *  
 * @since 1.2.0
 *
 */
  function SimpleUpdate( $currenttab, $newoptions, $oldoptions ){
    $options = $this->option_defaults();
    $bytab = $this->get_options_by_tab( $currenttab );
    foreach( $bytab as $id){
      $oldoptions[$id] = $this->MenuOptionsValidate( $newoptions[$id],$oldoptions[$id],$options[$id] );
    }
    update_option( $this->settings, $oldoptions);
    return $oldoptions;
  }
/**
 * 
 *  
 * @since 1.2.0
 *
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
    if($post_content['access_token'] && $post_content['username']){
      $user = $post_content['username'];
      $oldoptions = $this->get_all_options();
      $currentUsers = $oldoptions['users'];
      if( empty($currentUsers[ $user ]) || ($currentUsers[ $user ]['access_token'] != $post_content['access_token']) ){
        $post_content['name'] = $user;
        $post_content['title'] = $user;
        $currentUsers[ $user ] = $post_content;
        $oldoptions['users'] = $currentUsers;
        update_option( $this->settings, $oldoptions);
      }
    }
    return true;
  } 
  function DeleteUser( $user ){
    $oldoptions = $this->get_all_options();
    $currentUsers = $oldoptions['users'];
    if( !empty($currentUsers[$user]) ){
      unset($currentUsers[$user]);
    }
    $oldoptions['users'] = $currentUsers;
    update_option( $this->settings, $oldoptions);
  }
  function ReAuthorize( $user ){
    $oldoptions = $this->get_all_options();
    $currentUsers = $oldoptions['users'];
    $current = $currentUsers[ $user ];
    if( $current['client_id'] && $current['client_secret'] ){
      $oldoptions['client_id'] = $current['client_id'];
      $oldoptions['client_secret'] = $current['client_secret'];
      update_option( $this->settings, $oldoptions);
    }
  }
/**
  * Function for displaying forms in the widget page
  *
  * @since 1.0.0
  *
  */
  function MenuDisplayCallback($options,$option,$fieldname,$fieldid){
    $default = $option['default'];
    $optionname = $option['name'];
    $optiontitle = $option['title'];
    $optiondescription = $option['description'];
    $fieldtype = $option['type'];
    $value = ( Null !== $options[$optionname] ? $options[$optionname] : $default );
    
     // Output checkbox form field markup
    if ( 'checkbox' == $fieldtype ) {
      ?>
      <input type="checkbox" id="<?php echo $fieldid; ?>" name="<?php echo $fieldname; ?>" <?php checked( $value ); ?> />
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
        <span class="description"><?php echo $optiondescription; ?></span>
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
      <span class="description"><?php echo esc_textarea( $optiondescription ); ?></span>
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
 */
  function AdminDisplayCallback($options,$option,$fieldname,$fieldid){
    $default = $option['default'];
    $optionname = $option['name'];
    $optiontitle = $option['title'];
    $optiondescription = $option['description'];
    $fieldtype = $option['type'];
    $value = ( Null !== $options[$optionname] ? $options[$optionname] : $default );
    
     // Output checkbox form field markup
    if ( 'checkbox' == $fieldtype ) {
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="checkbox" id="<?php echo $fieldid; ?>" name="<?php echo $fieldname; ?>" <?php checked( $value ); ?> />
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
        <span class="description"><?php echo $optiondescription; ?></span>
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
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" value="<?php echo ( $value ); ?>" />
      <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } 
    else if ( 'textarea' == $fieldtype ) {
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
      <span class="description"><?php echo esc_textarea( $optiondescription ); ?></span>
      <?php
    }   
    else if ( 'color' == $fieldtype ) {
      $value = ($value?$value:$default);
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_color"  value="<?php echo ( $value ); ?>" /><span class="description"><?php echo $optiondescription; ?></span></label>
      <div id="<?php echo $fieldid; ?>_picker" class="AlpinePhotoTiles_color_picker" ></div>
      <?php
    }
  }


/**
 * Options Validate Pseudo-Callback
 *
 * @since 1.0.0
 *
 */
  function MenuOptionsValidate( $newinput, $oldinput, $optiondetails ) {
      $valid_input = $oldinput;

      // Validate checkbox fields
      if ( 'checkbox' == $optiondetails['type'] ) {
        // If input value is set and is true, return true; otherwise return false
        $valid_input = ( ( isset( $newinput ) && true == $newinput ) ? true : false );
      }
      // Validate radio button fields
      else if ( 'radio' == $optiondetails['type'] ) {
        // Get the list of valid options
        $valid_options = $optiondetails['valid_options'];
        // Only update setting if input value is in the list of valid options
        $valid_input = ( array_key_exists( $newinput, $valid_options ) ? $newinput : $valid_input );
      }
      // Validate select fields
      else if ( 'select' == $optiondetails['type'] || 'select-trigger' == $optiondetails['type']) {
        // Get the list of valid options
        $valid_options = $optiondetails['valid_options'];
        // Only update setting if input value is in the list of valid options
        $valid_input = ( array_key_exists( $newinput, $valid_options ) ? $newinput : $valid_input );
      }
      else if ( 'range' == $optiondetails['type'] ) {
        // Only update setting if input value is in the list of valid options
        $valid_input = ( ($newinput>=$optiondetails['min'] && $newinput<=$optiondetails['max']) ? $newinput : $valid_input );
      }    
      // Validate text input and textarea fields
      else if ( ( 'text' == $optiondetails['type'] || 'textarea' == $optiondetails['type'] || 'image-upload' == $optiondetails['type']) ) {
        $valid_input = strip_tags( $newinput );
        // Check if numeric
        if ( 'numeric' == $optiondetails['sanitize'] && is_numeric( wp_filter_nohtml_kses( $newinput ) ) ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          if( NULL !== $optiondetails['min'] && $valid_input<$optiondetails['min']){
            $valid_input = $optiondetails['min'];
          }
          if( NULL !== $optiondetails['max'] && $valid_input>$optiondetails['max']){
            $valid_input = $optiondetails['max'];
          }
        }
        if ( 'int' == $optiondetails['sanitize'] && is_numeric( wp_filter_nohtml_kses( $newinput ) ) ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = round( wp_filter_nohtml_kses( $newinput ) );
          if( NULL !== $optiondetails['min'] && $valid_input<$optiondetails['min']){
            $valid_input = $optiondetails['min'];
          }
          if( NULL !== $optiondetails['max'] && $valid_input>$optiondetails['max']){
            $valid_input = $optiondetails['max'];
          }
        }      
        // Validate no-HTML content
        if ( 'nospaces' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          
          if(Null !== $optiondetails['remove']){
            $valid_input = str_replace($optiondetails['remove'],'',$valid_input);
          }
          
          if(Null !== $optiondetails['replace']){
            $valid_input = str_replace(array('  ',' '),$optiondetails['replace'],$valid_input);
          }else{
            $valid_input = str_replace(' ','',$valid_input);
          }
        }           
        if ( 'tag' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          $valid_input = str_replace(' ','-',$valid_input);
        }            
        // Validate no-HTML content
        if ( 'nohtml' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          $valid_input = str_replace(' ','',$valid_input);
        }
        // Validate HTML content
        if ( 'html' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_kses filter using allowed post tags
          $valid_input = wp_kses_post($newinput );
        }
        // Validate URL address
        if( 'url' == $optiondetails['sanitize'] ){
          $valid_input = esc_url( $newinput );
        }
        // Validate URL address
        if( 'css' == $optiondetails['sanitize'] ){
          $valid_input = wp_htmledit_pre( stripslashes( $newinput ) );
        }      
      }else if( 'wp-textarea' == $optiondetails['type'] ){
          // Text area filter
          $valid_input = wp_kses_post( force_balance_tags($newinput) );
      }
      elseif( 'color' == $optiondetails['type'] ){
        $value =  wp_filter_nohtml_kses( $newinput );
        if( '#' == $value ){
          $valid_input = '';
        }else{
          $valid_input = $value;
        }
      }
      return $valid_input;
  }
  
  
  
  /**
   * Alpine PhotoTile: Options Page
   *
   * @since 1.1.1
   *
   */
  function build_settings_page(){
    $optiondetails = $this->option_defaults();
    $currenttab = $this->get_current_tab();
    
    echo '<div class="wrap AlpinePhotoTiles_settings_wrap">';
    $this->admin_options_page_tabs( $currenttab );

      echo '<div class="AlpinePhotoTiles-container '.$this->domain.'">';
      
      if( 'general' == $currenttab ){
        $this->display_general();
      }elseif( 'add' == $currenttab ){
        $this->display_add();
      }elseif( 'preview' == $currenttab ){
        $this->display_preview();
      }else{
        $options = $this->get_all_options();     
        $settings_section = $this->id . '_' . $currenttab . '_tab';
        $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );

        if( $submitted ){
          $options = $this->SimpleUpdate( $currenttab, $_POST, $options );
          if( 'generator' == $currenttab ) {
            $short = $this->generate_shortcode( $options, $optiondetails );
          }
        }
        echo '<div class="AlpinePhotoTiles-'.$currenttab.'">';
          if( $_POST[$this->settings.'_'.$currenttab]['submit-'.$currenttab] == 'Delete Current Cache' ){
            $this->clearAllCache();
            echo '<div class="announcement">'.__("Cache Cleared").'</div>';
          }
          elseif( $_POST[$this->settings.'_'.$currenttab]['submit-'.$currenttab] == 'Save Settings' ){
            $this->clearAllCache();
            echo '<div class="announcement">'.__("Settings Saved").'</div>';
          }
          echo '<form action="" method="post">';
            echo '<input type="hidden" name="hidden" value="Y">';
            $this->display_options_form($options,$currenttab,$short);
          echo '</form>';
        echo '</div>';
      }
      echo '</div>'; // Close Container
    echo '</div>'; // Close wrap
  }
/**
 * Get current settings page tab
 *  
 * @since 1.2.0
 *
 */
  function get_current_tab( $current = 'general' ) {
      if ( isset ( $_GET['tab'] ) ) :
          $current = $_GET['tab'];
      else:
          $current = 'general';
      endif;
    return $current;
  }
/**
 * Create shortcode based on given options
 *  
 * @since 1.1.0
 *
 */
  function generate_shortcode( $options, $optiondetails ){
    $short = '['.$this->short;
    $trigger = '';
    foreach( $options as $key=>$value ){
      if($value && $optiondetails[$key]['short']){
        if( $optiondetails[$key]['child'] && $optiondetails[$key]['hidden'] ){
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

    $tabs = $this->settings_page_tabs();
    $links = array();
    
    foreach( $tabs as $tab ) :
      $tabname = $tab['name'];
      $tabtitle = $tab['title'];
      if ( $tabname == $current ) :
          $links[] = "<a class='nav-tab nav-tab-active' href='?page=".$this->settings."&tab=$tabname'>$tabtitle</a>";
      else :
          $links[] = "<a class='nav-tab' href='?page=".$this->settings."&tab=$tabname'>$tabtitle</a>";
      endif;
    endforeach;

    echo '<div class="AlpinePhotoTiles-title"><div class="icon32 icon-alpine"><br></div><h2>'.$this->name.'</h2></div>';
    echo '<div class="AlpinePhotoTiles-menu"><h2 class="nav-tab-wrapper">';
    foreach ( $links as $link )
        echo $link;
    echo '</h2></div>';
  }
/**
 * Function for printing general settings page
 *  
 * @since 1.2.0
 *
 */
  function display_general(){ 
    ?>
    <div class="AlpinePhotoTiles-general">
      <h3><?php _e("Thank you for downloading the "); echo $this->name; _e(", a WordPress plugin by the Alpine Press.");?></h3>
      <p><?php _e("On the 'Shortcode Generator' tab you will find an easy to use interface that will help you create shortcodes. These shortcodes make it simple to insert the PhotoTile plugin into posts and pages.");?></p>
      <p><?php _e("The 'Plugin Settings' tab provides additional back-end options.");?></p>
      <p><?php _e("Finally, I am a one man programming team and so if you notice any errors or places for improvement, please let me know."); ?></p>
      <p><?php _e('If you liked this plugin, try out some of the other plugins by ') ?><a href="http://thealpinepress.com/category/plugins/" target="_blank">the Alpine Press</a><?php _e(' and please rate us at ') ?><a href="<?php echo $this->wplink;?>" target="_blank">WordPress.org</a>.</p>
      <br>
      <h3><?php _e('Try the other free plugins in the Alpine PhotoTile Series:');?></h3>
      <?php if( is_array($this->plugins) ){
        foreach($this->plugins as $each){
          ?><a href="http://wordpress.org/extend/plugins/alpine-photo-tile-for-<?php echo $each;?>/" target="_blank"><img class="image-icon" src="<?php echo $this->url;?>/css/images/for-<?php echo $each;?>.png" style="width:100px;"></a><?php
        }
      }?>

      <div class="help-link"><p><?php _e('Need Help? Visit ') ?><a href="<?php echo $this->info; ?>" target="_blank">the Alpine Press</a><?php _e(' for more about this plugin.') ?></p></div>
      </p>
    </div>
    <?php
  }
/**
 * Function for printing shortcode preview page
 *  
 * @since 1.2.0
 *
 */
  function display_preview(){ 
    $fieldid = "shortcode-preview";
    $value = '';
    $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );

    if( $submitted ){
      $value = wp_kses_post( str_replace('\"','"', $_POST['shortcode-preview']) );
    }
    ?>
      <div class="AlpinePhotoTiles-preview" style="border-bottom: 1px solid #DDDDDD;margin-bottom:20px;">
        <form action="" method="post">
          <input type="hidden" name="hidden" value="Y">
          <div>
          <h4><?php _e('Paste shortcode and click Preview');?></h4>
          <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldid; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
          <span class="description"><?php echo esc_textarea( $optiondescription ); ?></span>
          <input name="<?php echo $this->settings;?>_preview [submit-preview]" type="submit" class="button-primary" value="Preview" />
          </div>
        </form>
        <br style="clear:both">
      </div>
    <?php 
    
    echo do_shortcode($value);
    
  }
/**
 * Function for printing options page
 *  
 * @since 1.1.0
 *
 */
  function display_options_form($options,$currenttab,$short){

    $defaults = $this->option_defaults();
    $positions = $this->get_option_positions_by_tab( $currenttab );
    
    if( 'generator' == $currenttab ) { 
      echo '<input name="'. $this->settings.'_'.$currenttab .'[submit-'. $currenttab .']" type="submit" class="button-primary topbutton" value="Generate Shortcode" /><br> ';
      if($short){
        echo '<div id="'.$this->settings.'-shortcode" style="position:relative;clear:both;margin-bottom:20px;" ><div class="announcement" style="margin:0 0 10px 0;"> Now, copy (Crtl+C) and paste (Crtl+P) the following shortcode into a page or post. </div>';
        echo '<div><textarea class="auto_select">'.$short.'</textarea></div></div>';
      }
    }
    if( count($positions) ){
      foreach( $positions as $position=>$positionsinfo){
        echo '<div class="'. $position .'">'; 
          if( $positionsinfo['title'] ){ echo '<h4>'. $positionsinfo['title'].'</h4>'; } 
          echo '<table class="form-table">';
            echo '<tbody>';
              if( count($positionsinfo['options']) ){
                foreach( $positionsinfo['options'] as $optionname ){
                  $option = $defaults[$optionname];
                  $fieldname = ( $option['name'] );
                  $fieldid = ( $option['name'] );

                  if( 'generator' == $currenttab ){
                    if($option['parent']){
                      $class = $option['parent'];
                    }elseif($option['child']){
                      $class =($option['child']);
                    }else{
                      $class = ('unlinked');
                    }
                    $trigger = ($option['trigger']?('data-trigger="'.(($option['trigger'])).'"'):'');
                    $hidden = ($option['hidden']?' '.$option['hidden']:'');
                    
                    echo '<tr valign="top"> <td class="'.$class.' '.$hidden.'" '.$trigger.'>';
                      $this->MenuDisplayCallback($options,$option,$fieldname,$fieldid);
                    echo '</td></tr>';   
                  }else{
                    echo '<tr valign="top"><td>';
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
    echo '<div class="help-link"><span>'. __('Need Help? Visit ') .'<a href="' . $this->info . '" target="_blank">the Alpine Press</a>'. __(" for more about this plugin.") .'</span></div>';
    
    if( 'generator' == $currenttab ) {
      echo '<input name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" value="Generate Shortcode" />';
    }elseif( 'plugin-settings' == $currenttab ){
      echo '<input name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" value="Save Settings" />';
      echo '<input name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" style="margin-top:15px;" value="Delete Current Cache" />';
    }

  }
  
  function show_user($info){
    $output = '<div id="user-icon-'.$info['username'].'" class="user-icon">';
    $output .=  '<div><h4>'.$info['username'].'</h4></div>';
    $output .=  '<div><img src="'.$info['picture'].'" style="width:80px;height:80px;"></div>';
    $output .=  '<form id="'.$this->settings.'-user-'.$info['username'].'" action="" method="post">';
    $output .=  '<input type="hidden" name="hidden" value="Y">';
    $output .=  '<input type="hidden" name="user" value="'.$info['username'].'">';
    $output .=  '<input id="'.$this->settings.'-submit" name="'.$this->settings.'_reauthorize[submit-reauthorize]" type="submit" class="button-primary" style="margin-top:15px;float:none;" value="Re-Authorize" />';
    $output .=  '</form>';
    $output .=  '<form id="'.$this->settings.'-delete-'.$info['username'].'" action="" method="post">';
    $output .=  '<input type="hidden" name="hidden" value="Y">';
    $output .=  '<input type="hidden" name="user" value="'.$info['username'].'">';
    $output .=  '<input id="'.$this->settings.'-submit" name="'.$this->settings.'_delete[submit-delete]" type="submit" class="button-primary" style="margin-top:15px;float:none;" value="Delete User" />';
    $output .=  '</form>';
    $output .=  '</div>';
    return $output;
  }
  function show_user_js($info){
    $redirect = admin_url( 'options-general.php?page='.$this->settings.'&tab=add' );
    $output = 'jQuery(document).ready(function() {var url = "https://api.instagram.com/oauth/authorize/"+"?redirect_uri=" + encodeURIComponent("'.$redirect . '")+ "&response_type=code" + "&client_id='.$info['client_id'].'" + "&display=touch";jQuery("#'.$this->settings.'-user-'.$info['username'].'").ajaxForm({ success: function(responseText){  window.location.replace(url); } });  });';
    return $output;
  }
  
  function display_add(){ 
  
    wp_enqueue_script( 'jquery-form');
    
    $currenttab = 'add';
    $options = $this->get_all_options();     
    $settings_section = $this->id . '_'.$currenttab.'_tab';
    $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );
    $redirect = admin_url( 'options-general.php?page='.$this->settings.'&tab='.$currenttab );
    $success = false;
    $errormessage = null;
    $errortype = null;
      
    if (isset($_GET['code'])) {
      $code = $_GET['code'];
      $client_id = $this->get_option('client_id');
      $client_secret = $this->get_option('client_secret');
      $response = wp_remote_post("https://api.instagram.com/oauth/access_token",
        array(
          'body' => array(
            'code' => $code,
            'response_type' => 'authorization_code',
            'redirect_uri' => $redirect,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
          ),
          'sslverify' => apply_filters('https_local_ssl_verify', false)
        )
      );

      $access_token = null;
      $username = null;
      $image = null;

      if(!is_wp_error($response) && $response['response']['code'] < 400 && $response['response']['code'] >= 200) {
        $auth = json_decode($response['body']);
        
        if(isset($auth->access_token)) {
          $access_token = $auth->access_token;
          $user = $auth->user;
          
          $post_content = array(
            'access_token' => $access_token,
            'username' => $user->username,
            'picture' => $user->profile_picture,
            'fullname' => $user->full_name,
            'user_id' => $user->id,
            'client_id' => $client_id,
            'client_secret' => $client_secret
          );
          $success = $this->AddUser($post_content);
          $icon = $this->show_user($post_content);
          $js = $this->show_user_js($post_content);
        }
      }elseif( !is_wp_error($response) && $response['response']['code'] >= 400 ) {
        $error = json_decode($response['body']);
        $errormessage = $error->error_message;
        $errortype = $error->error_type;
      }
    }  
  	
   
    if( $submitted && $_POST[ $this->settings.'_delete']['submit-delete'] == 'Delete User' ){
      $delete = true;
      $user = $_POST['user'];
      $this->DeleteUser( $user );
    }elseif( $submitted && $_POST[ $this->settings.'_reauthorize']['submit-reauthorize'] == 'Re-Authorize' ){
      $user = $_POST['user'];
      $this->ReAuthorize( $user );
    }elseif( $submitted ){
      $options = $this->SimpleUpdate( $currenttab, $_POST, $options ); // Don't display previously input info
    }
    
    $defaults = $this->option_defaults();
    $positions = $this->get_option_positions_by_tab( $currenttab );
    
    echo '<div class="AlpinePhotoTiles-add">';
          if( $success ){
            echo '<div class="announcement"> User successfully authorized. </div>';
          }elseif( $delete ){
            echo '<div class="announcement"> User ('.$user.') deleted. </div>';
          }elseif( $errormessage ){
            echo '<div class="announcement"> An error occured ('.$errormessage.'). </div>';
          }
          if( count($positions) ){
            foreach( $positions as $position=>$positionsinfo){
              if( $position == 'top'){
                echo '<div id="AlpinePhotoTiles-user-list" style="margin-bottom:20px;padding-bottom:20px;overflow:hidden;border-bottom: 1px solid #DDDDDD;">'; 
                if( $positionsinfo['title'] ){ echo '<h4>'. $positionsinfo['title'].'</h4>'; } 
                $users = $this->get_users();
                if( $users['none']['name'] == 'none' ){
                  echo '<p id="AlpinePhotoTiles-user-empty">No users available. Add a user by following the instructions below.</p>';
                }else{
                  foreach($users as $name=>$info){
                    echo $this->show_user($info);
                    echo '<script type = "text/javascript">'.$this->show_user_js($info).'</script>';
                  }
                }
                echo '</div>';
              }else{
                echo '<div id="AlpinePhotoTiles-user-form" style="margin-bottom:20px;padding-bottom:20px;overflow:hidden;border-bottom: 1px solid #DDDDDD;">'; 
                  ?>
                  <form id="<?php echo $this->settings."-add-user";?>" action="" method="post">
                  <input type="hidden" name="hidden" value="Y">
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
                  echo '<input id="'.$this->settings.'-submit" name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" style="margin-top:15px;" value="Add and Authorize New User" />';
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
          <p><?php _e("Instagram is quite protective of its users. Before your WordPress website can retrieve images from Instagram, you must authorize your WordPress site to access your Instagram account. This is done by following these 5 simple steps:");?>
          <ol>
            <li>
              <?php _e("Make sure you are logged into Instagram.com and then visit");?> <a href="http://instagram.com/developer" target="_blank">http://instagram.com/developer</a>.
            </li>
            <li>
              <?php _e('Click on the "Manage Clients" link, as shown below.');?>
              <p><img src="<?php echo $this->url;?>/css/images/manage-clients.png"/></p>
              <p><?php _e('If this is the first time you are adding an app or plugin, Instagram will ask you a few questions. You can enter these responses, click "Sign Up", and then click "Manage Clients" again:');?></p>
              <dt><strong><?php _e('Your website:');?></strong></dt>
              <dd><em><?php _e('Enter your website url');?></em></dd>
              <dt><strong><?php _e('Phone number:');?></strong></dt>
              <dd><em><?php _e('Enter your phone number (They have never called me...)');?></em></dd>
              <dt><strong><?php _e('What do you want to build with the API?');?></strong></dt>
              <dd><em><?php _e('A plugin for my WordPress website.');?></em></dd>
              <p><img src="<?php echo $this->url;?>/css/images/sign-up.png"/></p>
            </li>
            <li>
              <?php _e('Register your WordPress site by click the "Register a New Client" button.');?>
              <p><img src="<?php echo $this->url;?>/css/images/register-client.png"/></p>
            </li>
            <li>
              <p><?php _e('Fill in the "Register new OAuth Client" form with the following infomation and click "Register":');?></p>
              <dl>
                <dt><strong><?php _e('Application name');?></strong></dt>
                <dd><em><?php _e('Enter the name of your WordPress website');?></em></dd>
                <dt><strong><?php _e('Description');?></strong></dt>
                <dd><em><?php echo $this->name;?> WordPress plugin</em></dd>
                <dt><strong><?php _e('Website');?></strong></dt>
                <dd><em><?php _e('Enter your website url');?></em></dd>
                <dt><strong><?php _e('OAuth redirect_url');?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;**<?php _e('This must be copied exactly as shown below');?>**</dt>
                
                <dd><em><?php echo $redirect; ?></em></dd>
              </dl>
              <p><img src="<?php echo $this->url;?>/css/images/register.png"/></p>
            </li>
            <li>
              <?php _e('Enter the Client ID and Client Secret into the form above and click "Add and Authorize New User". You will then be directed to an Instagram page where you can finish the authorization. I hope you enjoy the plugin.');?>
            </li>
          </ol>
        </div>

  
      <script type = "text/javascript">
        var url;
        jQuery(document).ready(function() {
            jQuery("#<?php echo $this->settings;  ?>-add-user").ajaxForm({
                beforeSubmit: function(formData, jqForm, options){
                  
                  var form = jqForm[0];
                  if(form.client_id.value){
                    var id = form.client_id.value;
                    id = id.replace(/\s/g, "");
                    url = 'https://api.instagram.com/oauth/authorize/'
                    + '?redirect_uri=' + encodeURIComponent("<?php echo $redirect; ?>")
                    + '&response_type=code'
                    + '&client_id='+id
                    + '&display=touch';
                    return true; 
                  }
                  return false;
                },
                success: function(responseText){ 
                  window.location.replace(url);
                }
            }); 
        });
      </script>
  
    <?php

  }
  
  
/**
 * Functions for caching results and clearing cache
 *  
 * @since 1.1.0
 *
 */
  public function setCacheDir($val) {  $this->cacheDir = $val; }  
  public function setExpiryInterval($val) {  $this->expiryInterval = $val; }  
  public function getExpiryInterval($val) {  return (int)$this->expiryInterval; }
  
  public function cacheExists($key) {  
    $filename_cache = $this->cacheDir . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->cacheDir . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info)) {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->expiryInterval; //Last update time of the cache file  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time) {//Compare last updated and current time  
        return true;  
      }  
    }
    return false;  
  } 

  public function getCache($key)  {  
    $filename_cache = $this->cacheDir . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->cacheDir . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info))  {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->expiryInterval; //Last update time of the cache file  
      $time = time(); //Current Time  

      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time){ //Compare last updated and current time 
        return file_get_contents ($filename_cache);   //Get contents from file  
      }  
    }
    return null;  
  }  

  public function putCache($key, $content) {  
    $time = time(); //Current Time  
    
    if ( ! file_exists($this->cacheDir) ){  
      @mkdir($this->cacheDir);  
      $cleaning_info = $this->cacheDir . '/cleaning.info'; //Cache info 
      @file_put_contents ($cleaning_info , $time); // save the time of last cache update  
    }
    
    if ( file_exists($this->cacheDir) && is_dir($this->cacheDir) ){ 
      $dir = $this->cacheDir . '/';
      $filename_cache = $dir . $key . '.cache'; //Cache filename  
      $filename_info = $dir . $key . '.info'; //Cache info  
    
      @file_put_contents($filename_cache ,  $content); // save the content  
      @file_put_contents($filename_info , $time); // save the time of last cache update  
    }
  }
  
  public function clearAllCache() {
    $dir = $this->cacheDir . '/';
    if(is_dir($dir)){
      $opendir = @opendir($dir);
      while(false !== ($file = readdir($opendir))) {
        if($file != "." && $file != "..") {
          if(file_exists($dir.$file)) {
            $file_array = @explode('.',$file);
            $file_type = @array_pop( $file_array );
            // only remove cache or info files
            if( 'cache' == $file_type || 'info' == $file_type){
              @chmod($dir.$file, 0777);
              @unlink($dir.$file);
            }
          }
          /*elseif(is_dir($dir.$file)) {
            @chmod($dir.$file, 0777);
            @chdir('.');
            @destroy($dir.$file.'/');
            @rmdir($dir.$file);
          }*/
        }
      }
      @closedir($opendir);
    }
  }
  
  public function cleanCache() {
    $cleaning_info = $this->cacheDir . '/cleaning.info'; //Cache info     
    if (file_exists($cleaning_info))  {  
      $cache_time = file_get_contents ($cleaning_info) + (int)$this->cleaningInterval; //Last update time of the cache cleaning  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  
      if ((int)$cache_time < (int)$expiry_time){ //Compare last updated and current time     
        // Clean old files
        $dir = $this->cacheDir . '/';
        if(is_dir($dir)){
          $opendir = @opendir($dir);
          while(false !== ($file = readdir($opendir))) {                            
            if($file != "." && $file != "..") {
              if(is_dir($dir.$file)) {
                //@chmod($dir.$file, 0777);
                //@chdir('.');
                //@destroy($dir.$file.'/');
                //@rmdir($dir.$file);
              }
              elseif(file_exists($dir.$file)) {
                $file_array = @explode('.',$file);
                $file_type = @array_pop( $file_array );
                $file_key = @implode( $file_array );
                if( $file_type && $file_key && 'info' == $file_type){
                  $filename_cache = $dir . $file_key . '.cache'; //Cache filename  
                  $filename_info = $dir . $file_key . '.info'; //Cache info   
                  if (file_exists($filename_cache) && file_exists($filename_info)) {  
                    $cache_time = file_get_contents ($filename_info) + (int)$this->cleaningInterval; //Last update time of the cache file  
                    $expiry_time = (int)$time; //Expiry time for the cache  
                    if ((int)$cache_time < (int)$expiry_time) {//Compare last updated and current time  
                      @chmod($filename_cache, 0777);
                      @unlink($filename_cache);
                      @chmod($filename_info, 0777);
                      @unlink($filename_info);
                    }  
                  }
                }
              }
            }
          }
          @closedir($opendir);
        }
        @file_put_contents ($cleaning_info , $time); // save the time of last cache cleaning        
      }
    }
  } 
}


?>
