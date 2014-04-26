<?php
/*
Plugin Name: Alpine PhotoTile for Instagram
Plugin URI: http://thealpinepress.com/alpine-phototile-for-instagram/
Description: The Alpine PhotoTile for Instagram is capable of retrieving photos from a particular Instagram user or tag. The photos can be linked to the your Instagram page, a specific URL, or to a Fancybox slideshow. Also, the Shortcode Generator makes it easy to insert the widget into posts without learning any of the code. This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek presentation that I hope you will like.
Version: 1.2.7.1
Author: the Alpine Press
Author URI: http://thealpinepress.com/
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html


Copyright 2014  Eric Burger

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
*/
  
  // Prevent direct access to the plugin 
  if (!defined('ABSPATH')) {
    exit(__( "Sorry, you are not allowed to access this page directly." ));
  }

  // Pre-2.6 compatibility to find directories
  if ( ! defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
  if ( ! defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
  if ( ! defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
  if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
  
/**
 * Register Widget
 *  
 * @ Since 1.0.0
 * @ Updated 1.2.7
 */
  function APTFINbyTAP_widget_register() {

    include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/alpinebot-primary.php' );
    include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/alpinebot-display.php' );
    if( is_admin() ){
      include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/alpinebot-admin.php' );
    }
    include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/plugin-widget.php' );
    include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/plugin-shortcode.php' );
		
		// Add JSON encoding functions, if necessary
		if (!function_exists('json_decode')) {
				// Check if already added
				if (!function_exists('alpine_json_decode')) {
					// Use Services_JSON by PEAR, http://pear.php.net/package/Services_JSON/
					include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/JSON.php' );
					function alpine_json_decode($content, $assoc=false) {
							if( class_exists('Services_JSON') ){
									if ($assoc) {
											$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
									}
									else {
											$json = new Services_JSON;
									}
									$result = $json->decode($content);
									return $result;
							}
							return false;
					}
				}
		}		
    register_widget( 'Alpine_PhotoTile_for_Instagram' );
  }
  add_action('widgets_init','APTFINbyTAP_widget_register');

/**
 * Load Admin JS and CSS
 *  
 * @ Since 1.0.0
 * @ Updated 1.2.3
 */
	function APTFINbyTAP_admin_widget_script($hook){ 
    $bot = new PhotoTileForInstagramBot(); // Bot needed to clean cache
    wp_register_script($bot->get_private('ajs'),$bot->get_script('admin'),'',$bot->get_private('ver') ); 
    wp_register_style($bot->get_private('acss'),$bot->get_style('admin'),'',$bot->get_private('ver') );
    
    $bot->do_alpine_method('register_style_and_script'); // Register widget styles and scripts
        
    if( 'widgets.php' != $hook ){ return; }
    
    wp_enqueue_script( 'jquery');
    wp_enqueue_script($bot->get_private('ajs'));
    wp_enqueue_style($bot->get_private('acss'));
    add_action('admin_print_footer_scripts', 'APTFINbyTAP_menu_toggles');
    
    // Only admin can trigger two week cache cleaning by visiting widgets.php
    $disablecache = $bot->get_option( 'cache_disable' );
    if ( empty($disablecache) ) { $bot->do_alpine_method('cleanCache'); }
	}
  add_action('admin_enqueue_scripts', 'APTFINbyTAP_admin_widget_script'); 
  
/**
 * Load JS to activate menu toggles
 *  
 * @ Since 1.0.0
 *
 */
  function APTFINbyTAP_menu_toggles(){
    $bot = new PhotoTileForInstagramPrimary();
    ?>
    <script type="text/javascript">
    if( jQuery().AlpineWidgetMenuPlugin  ){
      jQuery(document).ready(function(){
        jQuery('.AlpinePhotoTiles-container.<?php echo $bot->get_private('domain');?> .AlpinePhotoTiles-parent').AlpineWidgetMenuPlugin();
        jQuery(document).ajaxComplete(function() {
          jQuery('.AlpinePhotoTiles-container.<?php echo $bot->get_private('domain');?> .AlpinePhotoTiles-parent').AlpineWidgetMenuPlugin();
        });
      });
    }
    </script>  
    <?php   
  }
/**
 * Load JS to highlight and select shortcode upon hovering
 *  
 * @ Since 1.0.0
 * @ Updated 1.2.4
 */
  function APTFINbyTAP_shortcode_select(){
    $bot = new PhotoTileForInstagramPrimary();
    ?>
    <script type="text/javascript">
    jQuery(".auto_select").mouseenter(function(){
      jQuery(this).select();
    }); 
    var div = jQuery('#<?php echo $bot->get_private('settings'); ?>-shortcode #shortcode');
    var contain = jQuery('.AlpinePhotoTiles_container_class');
    if( div.length && !contain.length ){
      for(i=0;i<3;i++) {
        div.animate({'opacity':'.7'}, 500).animate({'opacity':'1'}, 500);
      }
    } 
    </script>  
    <?php
  }

/**
 * Load Display JS and CSS
 *  
 * @ Since 1.0.0
 * @ Updated 1.2.3
 */
  function APTFINbyTAP_enqueue_display_scripts() {
    $bot = new PhotoTileForInstagramPrimary();
    wp_enqueue_script( 'jquery' );
    //wp_deregister_script('jquery');
    //wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"), false, '1.3.2', true);
    //wp_enqueue_script('jquery');
    $bot->do_alpine_method('register_style_and_script'); // Register widget styles and scripts
  }
  add_action('wp_enqueue_scripts', 'APTFINbyTAP_enqueue_display_scripts');
  
/**
 * Setup the Theme Admin Settings Page
 *
 * @ Since 1.0.1 
 */
  function APTFINbyTAP_admin_options() {
    $bot = new PhotoTileForInstagramPrimary();
    $page = add_options_page(__($bot->get_private('page')), __($bot->get_private('page')), 'manage_options', $bot->get_private('settings') , 'APTFINbyTAP_admin_options_page');
    /* Using registered $page handle to hook script load */
    add_action('admin_print_scripts-' . $page, 'APTFINbyTAP_enqueue_admin_scripts');
  }
  // Load the Admin Options page
  add_action('admin_menu', 'APTFINbyTAP_admin_options');
  
/**
 * Enqueue admin scripts (and related stylesheets)
 *
 * @ Since 1.0.0
 */
  function APTFINbyTAP_enqueue_admin_scripts() {
    $bot = new PhotoTileForInstagramPrimary();
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );
    wp_enqueue_script($bot->get_private('ajs'));
    wp_enqueue_style($bot->get_private('acss'));
    add_action('admin_print_footer_scripts', 'APTFINbyTAP_menu_toggles'); 
    add_action('admin_print_footer_scripts', 'APTFINbyTAP_shortcode_select'); 
  }
/**
 * Settings Page Markup
 *
 * @ Since 1.0.2
 */
  function APTFINbyTAP_admin_options_page() { 
    if (!current_user_can('manage_options')) {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    $adminbot = new PhotoTileForInstagramAdmin();
    $adminbot->do_alpine_method('admin_build_settings_page');
  }  
/**
 * Settings link on plugin page
 *
 * @ Since 1.2.5
 * @ Updated 1.2.7
 */
  function APTFINbyTAP_plugin_settings_link($links) { 
    $bot = new PhotoTileForInstagramPrimary();
    $user_link = '<a href="options-general.php?page='.$bot->get_private('settings').'&tab=add">'. __('Add User') .'</a>'; 
    array_push($links, $user_link);
    $generator_link = '<a href="options-general.php?page='.$bot->get_private('settings').'&tab=generator">'. __('Shortcode') .'</a>'; 
    array_push($links, $generator_link); 
    $settings_link = '<a href="options-general.php?page='.$bot->get_private('settings').'&tab=plugin-settings">'. __('Settings') .'</a>'; 
    array_push($links, $settings_link);     
    $tools_link = '<a href="options-general.php?page='.$bot->get_private('settings').'&tab=plugin-tools">'. __('Tools') .'</a>'; 
    array_push($links, $tools_link);  
    return $links; 
  }
  $plugin = plugin_basename(__FILE__); 
  add_filter("plugin_action_links_$plugin", 'APTFINbyTAP_plugin_settings_link' );

/**
 * Meta link on plugin page
 *
 * @ Since 1.2.5
 */
  function APTFINbyTAP_plugin_meta_links($links, $file) {  
    $plugin = plugin_basename(__FILE__);
    $bot = new PhotoTileForInstagramPrimary();
    if ($file == $plugin){ // only for this plugin  
      $donate_link =  '<a href="'.$bot->get_private('donatelink').'" target="_blank">' . __('Donate') . '</a>';
      array_push($links, $donate_link);
    }
    return $links;  
  }  
  add_filter( 'plugin_row_meta', 'APTFINbyTAP_plugin_meta_links', 10, 2 );  

  ?>
