<?php
/*
Plugin Name: Alpine PhotoTile for Instagram
Plugin URI: http://thealpinepress.com/alpine-phototile-for-instagram/
Description: The Alpine PhotoTile for Instagram is capable of retrieving photos from a particular Instagram user or tag. The photos can be linked to the your Instagram page, a specific URL, or to a Fancybox slideshow. Also, the Shortcode Generator makes it easy to insert the widget into posts without learning any of the code. This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek presentation that I hope you will like.
Version: 1.2.3.3
Author: the Alpine Press
Author URI: http://thealpinepress.com/
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

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

  if(!defined('ALPINE_INSTAGRAM_PLUGIN_URL')) {
    define('ALPINE_INSTAGRAM_PLUGIN_URL', plugins_url() . '/' . basename(dirname(__FILE__)));
  }
/**
 * Clear cache upon deactivation
 *  
 * @since 1.0.1
 *
 */
  function APTFINbyTAP_remove(){
    if ( class_exists( 'PhotoTileForInstagramBot' ) ) {
      $bot = new PhotoTileForInstagramBot();
      $bot->clearAllCache();
    }
  }
  register_deactivation_hook( __FILE__, 'APTFINbyTAP_remove' );
/**
 * Register Widget
 *  
 * @since 1.0.0
 *
 */
  function APTFINbyTAP_widget_register() {register_widget( 'Alpine_PhotoTile_for_Instagram' );}
  add_action('widgets_init','APTFINbyTAP_widget_register');

  include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/alpinebot-primary.php' );
  include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/alpinebot-secondary.php' );
  include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/alpinebot-tertiary.php' );
  include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/plugin-widget.php' );
  include_once( WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/gears/plugin-shortcode.php' );

/**
 * Load Admin JS and CSS
 *  
 * @ Since 1.0.0
 * @ Updated 1.2.3
 */
	function APTFINbyTAP_admin_widget_script($hook){ 
    $bot = new PhotoTileForInstagramBot();
    wp_register_script($bot->wmenujs,$bot->url.'/js/'.$bot->wmenujs.'.js','',$bot->ver); 
    wp_register_style($bot->acss,$bot->url.'/css/'.$bot->acss.'.css','',$bot->ver);
    
    $bot->register_style_and_script(); // Register widget styles and scripts

    wp_register_script( 'jquery-form', $bot->url.'/js/form/jquery.form.js', '', '1.0', true );
        
    if( 'widgets.php' != $hook ){ return; }
    
    wp_enqueue_script( 'jquery');
    wp_enqueue_script($bot->wmenujs);
    wp_enqueue_style($bot->acss);
    add_action('admin_print_footer_scripts', 'APTFINbyTAP_menu_toggles');
    
    // Only admin can trigger two week cache cleaning by visiting widgets.php
    $disablecache = $bot->get_option( 'cache_disable' );
    if ( !$disablecache ) { $bot->cleanCache(); }
	}
  add_action('admin_enqueue_scripts', 'APTFINbyTAP_admin_widget_script'); 
  
/**
 * Load JS to activate menu toggles
 *  
 * @ Since 1.0.0
 *
 */
  function APTFINbyTAP_menu_toggles(){
    $bot = new PhotoTileForInstagramBot();
    ?>
    <script type="text/javascript">
    if( jQuery().AlpineWidgetMenuPlugin  ){
      jQuery(document).ready(function(){
        jQuery('.AlpinePhotoTiles-container.<?php echo $bot->domain;?> .AlpinePhotoTiles-parent').AlpineWidgetMenuPlugin();
        jQuery(document).ajaxComplete(function() {
          jQuery('.AlpinePhotoTiles-container.<?php echo $bot->domain;?> .AlpinePhotoTiles-parent').AlpineWidgetMenuPlugin();
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
 *
 */
  function APTFINbyTAP_shortcode_select(){
    $bot = new PhotoTileForInstagramBot();
    ?>
    <script type="text/javascript">
      jQuery(".auto_select").mouseenter(function(){
        jQuery(this).select();
      }); 
      var div = jQuery('#<?php echo $bot->settings; ?>-shortcode');
      if( div && div.offset() ){
        jQuery("html,body").animate({ scrollTop: (40) }, 2000);
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
    $bot = new PhotoTileForInstagramBot();
    wp_enqueue_script( 'jquery' );
    
    $bot->register_style_and_script(); // Register widget styles and scripts
  }
  add_action('wp_enqueue_scripts', 'APTFINbyTAP_enqueue_display_scripts');
  
/**
 * Setup the Theme Admin Settings Page
 *
 * @ Since 1.0.1 
 */
  function APTFINbyTAP_admin_options() {
    $bot = new PhotoTileForInstagramBot();
    $page = add_options_page(__($bot->page), __($bot->page), 'manage_options', $bot->settings , 'APTFINbyTAP_admin_options_page');
    // Using registered $page handle to hook script load
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
    $bot = new PhotoTileForInstagramBot();
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'farbtastic' ); 
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script($bot->wmenujs);
    wp_enqueue_style($bot->acss);
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
    $bot = new PhotoTileForInstagramBot();
    $bot->build_settings_page();
  }  
  
/**
 * Display a notice to add Instagram user that can be dismissed
 *
 * @ Since 1.2.1
 */ 
  function APTFINbyTAP_admin_notice() {
    global $current_user ;
    $user_id = $current_user->ID;
    /* Check that the user hasn't already clicked to ignore the message */
    if ( ! get_user_meta($user_id, 'APTFINbyTAP_ignore_notice') ) {
      $bot = new PhotoTileForInstagramBot();
      $users = $bot->get_instagram_users();
      if( $users['none'] ){
        echo '<div class="updated"><p>';
        echo 'Add user to Photo Tile for Instagram <a href="'.admin_url().'options-general.php?page='.$bot->settings.'&tab=add">here</a>';
        printf(__(' | <a href="%1$s">Hide Notice</a>'), '?APTFINbyTAP_nag_ignore=0');
        echo "</p></div>";
      }
    }
  }
  add_action('admin_notices', 'APTFINbyTAP_admin_notice');
  
/**
 * Notice dismiss function
 *
 * @ Since 1.2.1
 */
  add_action('admin_init', 'APTFINbyTAP_nag_ignore');
  function APTFINbyTAP_nag_ignore() {
      global $current_user;
          $user_id = $current_user->ID;
          /* If user clicks to ignore the notice, add that to their user meta */
          if ( isset($_GET['APTFINbyTAP_nag_ignore']) && '0' == $_GET['APTFINbyTAP_nag_ignore'] ) {
               add_user_meta($user_id, 'eAPTFINbyTAP_ignore_notice', 'true', true);
      }
  }
  
?>
