<?php


class PhotoTileForInstagramBase {  

  /* Set constants for plugin */
  public $url;
  public $dir;
  public $cacheDir;
  public $ver = '1.2.3';
  public $vers = '1-2-3';
  public $domain = 'APTFINbyTAP_domain';
  public $settings = 'alpine-photo-tile-for-instagram-settings'; // All lowercase
  public $name = 'Alpine PhotoTile for Instagram';
  public $info = 'http://thealpinepress.com/alpine-phototile-for-instagram/';
  public $wplink = 'http://wordpress.org/extend/plugins/alpine-photo-tile-for-instagram/';
  public $page = 'AlpineTile: Instagram';
  public $hook = 'APTFINbyTAP_hook';
  public $plugins = array('flickr','pinterest','tumblr');

  public $root = 'AlpinePhotoTiles';
  public $wjs = 'AlpinePhotoTiles_script';
  public $wcss = 'AlpinePhotoTiles_style';
  public $wmenujs = 'AlpinePhotoTiles_menu_script';
  public $acss = 'AlpinePhotoTiles_admin_style';
  public $wdesc = 'Add images from Instagram to your sidebar';
//####### DO NOT CHANGE #######//
  public $short = 'alpine-phototile-for-instagram';
  public $id = 'APTFIN_by_TAP';
//#############################//
  public $expiryInterval = 360; //1*60*60;  1 hour
  public $cleaningInterval = 1209600; //14*24*60*60;  2 weeks

  function __construct() {
    $this->url = untrailingslashit( plugins_url( '' , dirname(__FILE__) ) );
    $this->dir = untrailingslashit( plugin_dir_path( dirname(__FILE__) ) );
    $this->cacheDir = WP_CONTENT_DIR . '/cache/' . $this->settings;
  }

/**
 * Option positions for widget page
 *  
 * @ Since 1.2.0
 * 
 */
  function widget_positions(){
      $options = array(
      'top' => '',
      'left' => 'Instagram Settings',
      'right' => 'Style Settings',
      'bottom' => 'Format Settings'
    );
    return $options;
  }
  
/**
 * Option positions for settings pages
 *  
 * @ Since 1.2.0
 * @ Updated 1.2.3
 */  
  function option_positions(){
    $positions = array(
      'generator' => array(
        'left' => array( 'title' => 'Instagram Settings' ),
        'right' => array( 'title' => 'Style Settings' ),
        'bottom' => array( 'title' => 'Format Settings')
      ),
      'add' => array(
        'top' => array( 'title' => 'Available Users' ),
        'center' => array( 'title' => 'Add New User (See Instructions Below)' )
      ),
      'plugin-settings' => array(
        'top' => array( 'title' => 'Global Style Options', 'description' => "Below are style settings that will be applied to every instance of the plugin. " ),
        'center' => array( 'title' => 'Hidden Options', 'description' => "Below are additional options that you can choose to enable by checking the box." ),
        'bottom' => array( 'title' => 'Cache Options' ),
      )
    );
    return $positions;
  }
/**
 * Plugin Admin Settings Page Tabs
 *  
 * @ Since 1.2.0
 *
 */
  function settings_page_tabs() {
    $tabs = array( 
      'general' => array(
        'name' => 'general',
        'title' => 'General',
      ),
      'add' => array(
        'name' => 'add',
        'title' => 'Add Instagram User',
      ),      
      'generator' => array(
        'name' => 'generator',
        'title' => 'Shortcode Generator',
      ),
      'preview' => array(
        'name' => 'preview',
        'title' => 'Shortcode Preview',
      ),
      'plugin-settings' => array(
        'name' => 'plugin-settings',
        'title' => 'Plugin Settings',
      )
    );
    return $tabs;
  }

/**
 * Retrieve Available Users
 *  
 * @ Since 1.2.0
 *
 */
  function get_instagram_users(){
    $return = array(
          'none' => array(
            'name' => 'none',
            'title' => 'Please Add a User'
          ));
    // Do not use $this->get_option('users'); since function is secondary, not primary
    // Additionally there is no default value to set and settings aren't update based on this call
    $options = get_option( $this->settings ); 
    if( !empty( $options['users'] ) ){
      $return = $options['users'];
    }
    return $return;
  }
  
/**
 * Option Parameters and Defaults
 *  
 * @ Since 1.0.0
 * @ Updated 1.2.3
 */  
  function option_defaults(){
    $options = array(
      'widget_title' => array(
        'name' => 'widget_title',
        'title' => 'Title : ',
        'type' => 'text',
        'description' => '',
        'since' => '1.1',
        'widget' => true,
        'tab' => '',
        'position' => 'top',
        'default' => ''
      ),
      'instagram_user_id' => array(
        'name' => 'instagram_user_id',
        'short' => 'user',
        'title' => 'Select User: ',
        'type' => 'select',
        'valid_options' => $this->get_instagram_users(),
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'left',
        'default' => ''
      ),
      'instagram_source' => array(
        'name' => 'instagram_source',
        'short' => 'src',
        'title' => 'Retrieve Photos From : ',
        'type' => 'select',
        'valid_options' => array(
          'user_recent' => array(
            'name' => 'user_recent',
            'title' => 'User Recent'
          ),
          'user_feed' => array(
            'name' => 'user_feed',
            'title' => 'User Feed'
          ),
          'user_liked' => array(
            'name' => 'user_liked',
            'title' => 'User Liked'
          ),
          'user_tag' => array(
            'name' => 'user_tag',
            'title' => 'User Tag'
          ),
          'global_popular' => array(
            'name' => 'global_popular',
            'title' => 'Global Popular'
          ),
          'global_tag' => array(
            'name' => 'global_tag',
            'title' => 'Global Tag'
          )     
        ),
        'description' => '',
        'parent' => 'AlpinePhotoTiles-parent', 
        'trigger' => 'instagram_source',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'left',
        'default' => 'user'
      ),
      'instagram_tag' => array(
        'name' => 'instagram_tag',
        'short' => 'tag',
        'title' => 'Tag (without "#"): ',
        'type' => 'text',
        'sanitize' => 'nospaces',
        'remove' => '#',
        'description' => '',
        'child' => 'instagram_source', 
        'hidden' => 'user_recent user_feed user_liked global_popular',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'left',            
        'default' => ''
      ), 
      'instagram_image_link_option' => array(
        'name' => 'instagram_image_link_option',
        'short' => 'imgl',
        'title' => 'Image Links : ',
        'type' => 'select',
        'valid_options' => array(
          'none' => array(
            'name' => 'none',
            'title' => 'Do not link images'
          ),
          'original' => array(
            'name' => 'original',
            'title' => 'Link to Image Source'
          ),
          'instagram' => array(
            'name' => 'instagram',
            'title' => 'Link to Instagram Page'
          ),
          'link' => array(
            'name' => 'link',
            'title' => 'Link to URL Address'
          ),
          'fancybox' => array(
            'name' => 'fancybox',
            'title' => 'Use Lightbox'
          )               
        ),
        'description' => '<br>*Privacy settings may prevent linking to Instagram Page',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'left',
        'parent' => 'AlpinePhotoTiles-parent', 
        'trigger' => 'instagram_image_link_option',
        'default' => 'fancybox'
      ),
      'custom_lightbox_rel' => array(
        'name' => 'custom_lightbox_rel',
        'short' => 'crel',
        'title' => 'Custom Lightbox "rel" (Optional): ',
        'type' => 'text',
        'sanitize' => 'nospaces',
        'encode' => array("["=>"{ltsq}","]"=>"{rtsq}"),
        'description' => '',
        'child' => 'instagram_image_link_option', 
        'hidden' => 'none original instagram link',
        'widget' => true,
        'hidden-option' => true,
        'check' => 'hidden_lightbox_custom_rel',
        'tab' => 'generator',
        'position' => 'left',
        'since' => '1.2.3',
        'default' => ''
      ),   
      'custom_link_url' => array(
        'name' => 'custom_link_url',
        'title' => 'Custom Link URL : ',
        'short' => 'curl',
        'type' => 'text',
        'sanitize' => 'url',
        'description' => '',
        'child' => 'instagram_image_link_option', 
        'hidden' => 'none original instagram fancybox',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'left',
        'default' => ''
      ),
      'instagram_display_link' => array(
        'name' => 'instagram_display_link',
        'short' => 'dl',
        'title' => 'Display link to Instagram page.',
        'type' => 'checkbox',
        'description' => '',
        'child' => 'instagram_source',
        'hidden' => 'community',
        'widget' => true,
        'hidden-option' => true,
        'check' => 'hidden_display_link',
        'tab' => 'generator',
        'position' => 'left',
        'since' => '1.2.3',
        'default' => ''
      ),    
      'instagram_display_link_text' => array(
        'name' => 'instagram_display_link_text',
        'short' => 'dltext',
        'title' => 'Link Text : ',
        'type' => 'text',
        'sanitize' => 'nohtml',
        'description' => '',
        'child' => 'instagram_source', 
        'hidden' => 'community',
        'widget' => true,
        'hidden-option' => true,
        'check' => 'hidden_display_link',
        'tab' => 'generator',
        'position' => 'left',
        'since' => '1.2.3',
        'default' => 'Instagram'
      ),    

      'style_option' => array(
        'name' => 'style_option',
        'short' => 'style',
        'title' => 'Style : ',
        'type' => 'select',
        'valid_options' => array(
          'vertical' => array(
            'name' => 'vertical',
            'title' => 'Vertical'
          ),
          'windows' => array(
            'name' => 'windows',
            'title' => 'Windows'
          ),
          'bookshelf' => array(
            'name' => 'bookshelf',
            'title' => 'Bookshelf'
          ),
          'rift' => array(
            'name' => 'rift',
            'title' => 'Rift'
          ),
          'floor' => array(
            'name' => 'floor',
            'title' => 'Floor'
          ),
          'wall' => array(
            'name' => 'wall',
            'title' => 'Wall'
          ),
          'cascade' => array(
            'name' => 'cascade',
            'title' => 'Cascade'
          ),
          'gallery' => array(
            'name' => 'gallery',
            'title' => 'Gallery'
          )           
        ),
        'description' => '',
        'parent' => 'AlpinePhotoTiles-parent',
        'trigger' => 'style_option',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => 'vertical'
      ),
      'style_shape' => array(
        'name' => 'style_shape',
        'short' => 'shape',
        'title' => 'Shape : ',
        'type' => 'select',
        'valid_options' => array(
          'rectangle' => array(
            'name' => 'rectangle',
            'title' => 'Rectangle'
          ),
          'square' => array(
            'name' => 'square',
            'title' => 'Square'
          )              
        ),
        'description' => '',
        'child' => 'style_option',
        'hidden' => 'vertical cascade floor wall rift bookshelf gallery',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => 'vertical'
      ),          
      'style_photo_per_row' => array(
        'name' => 'style_photo_per_row',
        'short' => 'row',
        'title' => 'Photos per row : ',
        'type' => 'text',
        'sanitize' => 'int',
        'min' => '1',
        'max' => '100',
        'description' => 'Max of 100',
        'child' => 'style_option',
        'hidden' => 'vertical cascade windows',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => '4'
      ),
      'style_column_number' => array(
        'name' => 'style_column_number',
        'short' => 'col',
        'title' => 'Number of columns : ',
        'type' => 'range',
        'min' => '1',
        'max' => '10',
        'description' => '',
        'child' => 'style_option',
        'hidden' => 'vertical floor wall bookshelf windows rift gallery',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => '2'
      ),     
      'style_gallery_ratio_width' => array(
        'name' => 'style_gallery_ratio_width',
        'short' => 'grwidth',
        'title' => 'Aspect Ratio Width : ',
        'type' => 'text',
        'sanitize' => 'numeric',
        'min' => '1',
        'description' => "",
        'child' => 'style_option',
        'hidden' => 'vertical floor wall bookshelf windows rift cascade',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'since' => '1.2.3',
        'default' => '800'
      ),      
      'style_gallery_ratio_height' => array(
        'name' => 'style_gallery_ratio_height',
        'short' => 'grheight',
        'title' => 'Aspect Ratio Height : ',
        'type' => 'text',
        'sanitize' => 'numeric',
        'min' => '1',
        'description' => "Set the Aspect Ratio of the gallery display. <br>(Default: 800 by 600)",
        'widget' => true,
        'child' => 'style_option',
        'hidden' => 'vertical floor wall bookshelf windows rift cascade',
        'tab' => 'generator',
        'position' => 'right',
        'since' => '1.2.3',
        'default' => '600'
      ),  
      'instagram_photo_number' => array(
        'name' => 'instagram_photo_number',
        'short' => 'num',
        'title' => 'Number of photos : ',
        'type' => 'text',
        'sanitize' => 'int',
        'min' => '1',
        'max' => '100',
        'description' => 'Max of 100, though under 20 is recommended',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => '4'
      ),
      'instagram_photo_size' => array(
        'name' => 'instagram_photo_size',
        'short' => 'size',
        'title' => 'Photo Size : ',
        'type' => 'select',
        'valid_options' => array(
          'Th' => array(
            'name' => 'Th',
            'title' => 'Thumb'
          ),
          'M' => array(
            'name' => 'M',
            'title' => 'Medium'
          ),
          'L' => array(
            'name' => 'L',
            'title' => 'Large'
          )  
        ),
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => 'M'
      ),
      'style_shadow' => array(
        'name' => 'style_shadow',
        'short' => 'shadow',
        'title' => 'Add slight image shadow.',
        'type' => 'checkbox',
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => ''
      ),   
      'style_border' => array(
        'name' => 'style_border',
        'short' => 'border',
        'title' => 'Add white image border.',
        'type' => 'checkbox',
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => ''
      ),   
      'style_highlight' => array(
        'name' => 'style_highlight',
        'short' => 'highlight',
        'title' => 'Highlight when hovering.',
        'type' => 'checkbox',
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => ''
      ),
      'style_curve_corners' => array(
        'name' => 'style_curve_corners',
        'short' => 'curve',
        'title' => 'Add slight curve to corners.',
        'type' => 'checkbox',
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'right',
        'default' => ''
      ),          
      'widget_alignment' => array(
        'name' => 'widget_alignment',
        'short' => 'align',
        'title' => 'Photo alignment : ',
        'type' => 'select',
        'valid_options' => array(
          'left' => array(
            'name' => 'left',
            'title' => 'Left'
          ),
          'center' => array(
            'name' => 'center',
            'title' => 'Center'
          ),
          'right' => array(
            'name' => 'right',
            'title' => 'Right'
          )            
        ),
        'hidden-option' => true,
        'check' => 'hidden_widget_alignment',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'bottom',
        'since' => '1.2.3',
        'default' => 'center'
      ),    
      'widget_max_width' => array(
        'name' => 'widget_max_width',
        'short' => 'max',
        'title' => 'Max widget width (%) : ',
        'type' => 'text',
        'sanitize' => 'numeric',
        'min' => '1',
        'max' => '100',
        'description' => "To reduce the widget width, input a percentage (between 1 and 100). <br> If photos are smaller than widget area, reduce percentage until desired width is achieved.",
        'widget' => true,
        'tab' => 'generator',
        'position' => 'bottom',
        'default' => '100'
      ),        
      'widget_disable_credit_link' => array(
        'name' => 'widget_disable_credit_link',
        'short' => 'nocredit',
        'title' => 'Disable the tiny "TAP" link in the bottom left corner, though I have spent several months developing this plugin and would appreciate the credit.',
        'type' => 'checkbox',
        'description' => '',
        'widget' => true,
        'tab' => 'generator',
        'position' => 'bottom',
        'default' => ''
      ), 
      'general_loader' => array(
        'name' => 'general_loader',
        'title' => 'Disable Loading Icon: ',
        'type' => 'checkbox',
        'description' => 'Remove the icon that appears while images are loading.',
        'since' => '1.2.1',
        'tab' => 'plugin-settings',
        'position' => 'top',
        'default' => ''
      ), 
      'general_highlight_color' => array(
        'name' => 'general_highlight_color',
        'title' => 'Highlight Color:',
        'type' => 'color',
        'description' => 'Click to choose link color.',
        'section' => 'settings',
        'tab' => 'general',
        'since' => '1.2.1',
        'tab' => 'plugin-settings',
        'position' => 'top',
        'default' => '#64a2d8'
      ), 
      'general_lightbox' => array(
        'name' => 'general_lightbox',
        'title' => 'Choose jQuery Lightbox Plugin : ',
        'type' => 'select',
        'valid_options' => array(
          'alpine-fancybox' => array(
            'name' => 'alpine-fancybox',
            'title' => 'Fancybox (Safemode)'
          ),
          'fancybox' => array(
            'name' => 'fancybox',
            'title' => 'Fancybox'
          ),
          'colorbox' => array(
            'name' => 'colorbox',
            'title' => 'ColorBox'
          ),
          'prettyphoto' => array(
            'name' => 'prettyphoto',
            'title' => 'prettyPhoto'
          )      
        ),
        'tab' => 'plugin-settings',
        'position' => 'top',
        'default' => 'alpine-fancybox'
      ),
      'general_lightbox_no_load' => array(
        'name' => 'general_lightbox_no_load',
        'title' => 'Prevent Lightbox Loading: ',
        'type' => 'checkbox',
        'description' => 'Already using the above lighbox alternative? Prevent this plugin from loading it again.',
        'since' => '1.2.3',
        'tab' => 'plugin-settings',
        'position' => 'top',
        'default' => ''
      ), 
      'general_lightbox_params' => array(
        'name' => 'general_lightbox_params',
        'title' => 'Custom Lightbox Parameters:',
        'type' => 'textarea',
        'description' => 'Add custom parameters to the lighbox call.',
        'section' => 'settings',
        'tab' => 'general',
        'since' => '1.2.3',
        'tab' => 'plugin-settings',
        'position' => 'top',
        'default' => ''
      ), 
      'general_load_header' => array(
        'name' => 'general_load_header',
        'title' => 'Always Load Styles and Scripts in Header: ',
        'type' => 'checkbox',
        'description' => 'For themes without wp_footer(). Requires that styles and scripts be loaded on every page.',
        'since' => '1.2.3',
        'tab' => 'plugin-settings',
        'position' => 'top',
        'default' => ''
      ), 
      'hidden_display_link' => array(
        'name' => 'hidden_display_link',
        'title' => 'Link Below Widget: ',
        'type' => 'checkbox',
        'description' => 'Add an option to place a link with custom text below widget display.',
        'since' => '1.2.3',
        'tab' => 'plugin-settings',
        'position' => 'center',
        'default' => true
      ), 
      'hidden_widget_alignment' => array(
        'name' => 'hidden_widget_alignment',
        'title' => 'Photo Alignment: ',
        'type' => 'checkbox',
        'description' => 'Add an option to align photos to the left, right, or center.',
        'since' => '1.2.3',
        'tab' => 'plugin-settings',
        'position' => 'center',
        'default' => true
      ), 
      'hidden_lightbox_custom_rel' => array(
        'name' => 'hidden_lightbox_custom_rel',
        'title' => 'Custom "rel" for Lightbox: ',
        'type' => 'checkbox',
        'description' => 'Add an option to set custom "rel" to widget options.',
        'since' => '1.2.3',
        'tab' => 'plugin-settings',
        'position' => 'center',
        'default' => ''
      ), 
      'cache_disable' => array(
        'name' => 'cache_disable',
        'title' => 'Disable feed caching: ',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'tab' => 'plugin-settings',
        'position' => 'bottom',
        'default' => ''
      ), 
      'cache_time' => array(
        'name' => 'cache_time',
        'title' => 'Cache time (hours) : ',
        'type' => 'text',
        'sanitize' => 'numeric',
        'min' => '1',
        'description' => "Set the number of hours that a feed will be stored.",
        'since' => '1.1',
        'tab' => 'plugin-settings',
        'position' => 'bottom',
        'default' => '3'
      ), 
      'client_id' => array(
        'name' => 'client_id',
        'title' => 'Client ID : ',
        'type' => 'text',
        'sanitize' => 'nospaces',
        'description' => '',
        'tab' => 'add',
        'position' => 'center',
        'default' => ''
      ),  
      'client_secret' => array(
        'name' => 'client_secret',
        'title' => 'Client Secret : ',
        'type' => 'text',
        'sanitize' => 'nospaces',
        'description' => '',
        'tab' => 'add',
        'position' => 'center',
        'default' => ''
      ),  
    );
    return $options;
  }
  
// END
}

?>
