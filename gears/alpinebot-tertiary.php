<?php


class PhotoTileForInstagramBot extends PhotoTileForInstagramBasic{  

/**
 *  Create constants for storing info 
 *  
 *  @ Since 1.2.2
 */
   public $out = "";
   public $options;
   public $wid; // Widget id
   public $results;
   public $shadow;
   public $border;
   public $curves;
   public $highlight;
   public $rel;
   
/**
 *  Function for fetching instagram feed
 *  
 *  @ Since 1.2.1
 */
  function fetch_instagram_feed($request){
    // No longer write out curl_init and user WP API instead
    $response = wp_remote_get($request,
      array(
        'method' => 'GET',
        'timeout' => 20,
        'sslverify' => apply_filters('https_local_ssl_verify', false)
      )
    );
    if( is_wp_error( $response ) || !isset($response['body']) ) {
      return false;
    }else{
      $_instagram_json = @json_decode( $response['body'] );
      if( empty($_instagram_json) || 200 != $_instagram_json->meta->code ){
        return false;
      }else{
        return $_instagram_json;
      }
    }
  }
  
/**
 *  Function for creating cache key
 *  
 *  @ Since 1.2.2
 */
   function key_maker( $array ){
    if( isset($array['name']) && is_array( $array['info'] ) ){
      $return = $array['name'];
      foreach( $array['info'] as $key=>$val ){
        $return = $return."-".($val?$val:$key);
      }
      $return = @ereg_replace('[[:cntrl:]]', '', $return ); // remove ASCII's control characters
      $bad = array_merge(
        array_map('chr', range(0,31)),
        array("<",">",":",'"',"/","\\","|","?","*"," ",",","\'",".")); 
      $return = str_replace($bad, "", $return); // Remove Windows filename prohibited characters
      return $return;
    }
  }
  
/**
 * Alpine PhotoTile for Instagram: Photo Retrieval Function
 * The PHP for retrieving content from Instagram.
 *
 * @ Since 1.0.0
 * @ Updated 1.2.3
 */
  function photo_retrieval(){
    $instagram_options = $this->options;
    $defaults = $this->option_defaults();
    $instagram_uid = $instagram_options['instagram_user_id'];
    
    $key_input = array(
      'name' => 'instagram',
      'info' => array(
        'vers' => $this->vers,
        'src' => $instagram_options['instagram_source'],
        'uid' => $instagram_uid,
        'tag' => $instagram_options['instagram_tag'],
        'num' => $instagram_options['instagram_photo_number'],
        'link' => $instagram_options['instagram_display_link'],
        'text' => $instagram_options['instagram_display_link_text'],
        'size' => $instagram_options['instagram_photo_size']
        )
      );
    $key = $this->key_maker( $key_input );
    
    $disablecache = $this->get_option( 'cache_disable' );
    if ( !$disablecache ) {
      if( $this->cacheExists($key) ) {
        $results = $this->getCache($key);
        $results = @unserialize($results);
        if( count($results) ){
          $results['hidden'] .= '<!-- Retrieved from cache -->';
          $this->results = $results;
          return;
        }
      }
    }
    
    // Check if access_token is available for given user
    $users = $this->get_instagram_users();
    if( empty( $users[ $instagram_uid ] ) || empty( $users[ $instagram_uid ]['access_token'] )){
      $this->results = array('hidden'=>'<!-- Could not find user and/or access_token for '.$instagram_uid.' -->','message'=>'Could not find access token for '.$instagram_uid.'.');
      if( is_array( $users[ $instagram_uid ] ) ){
        foreach( $users[ $instagram_uid ] as $key=>$val ){
          $this->results['hidden'] .= '<!-- '.$key.' => '.$val.' -->';
        }
      }
      return;
    }
    $token = $users[ $instagram_uid ]['access_token'];
    
    $message = '';
    $hidden = '';
    $continue = false;
    $feed_found = false;
    $linkurl = array();
    $photocap = array();
    $photourl = array();
    $originalurl = array();
    $record = array();
                
    /*    protected $_endpointUrls = array(
        'user' => 'https://api.instagram.com/v1/users/%d/?access_token=%s',
        'user_feed' => 'https://api.instagram.com/v1/users/self/feed?access_token=%s&max_id=%s&min_id=%s&count=%d',
        'user_recent' => 'https://api.instagram.com/v1/users/%s/media/recent/?access_token=%s&max_id=%s&min_id=%d&max_timestamp=%d&min_timestamp=%d&count=%d', // 2011-10-18: Changed %d to %s
        'user_search' => 'https://api.instagram.com/v1/users/search?q=%s&access_token=%s',
        'user_follows' => 'https://api.instagram.com/v1/users/%d/follows?access_token=%s',
        'user_followed_by' => 'https://api.instagram.com/v1/users/%d/followed-by?access_token=%s',
        'user_requested_by' => 'https://api.instagram.com/v1/users/self/requested-by?access_token=%s',
        'user_relationship' => 'https://api.instagram.com/v1/users/%d/relationship?access_token=%s',
        'modify_user_relationship' => 'https://api.instagram.com/v1/users/%d/relationship?action=%s&access_token=%s',
        'media' => 'https://api.instagram.com/v1/media/%d?access_token=%s',
        'media_search' => 'https://api.instagram.com/v1/media/search?lat=%s&lng=%s&max_timestamp=%d&min_timestamp=%d&distance=%d&access_token=%s',
        'media_popular' => 'https://api.instagram.com/v1/media/popular?access_token=%s',
        'media_comments' => 'https://api.instagram.com/v1/media/%d/comments?access_token=%s',
        'post_media_comment' => 'https://api.instagram.com/v1/media/%d/comments?access_token=%s',
        'delete_media_comment' => 'https://api.instagram.com/v1/media/%d/comments?comment_id=%d&access_token=%s',
        'likes' => 'https://api.instagram.com/v1/media/%d/likes?access_token=%s',
        'post_like' => 'https://api.instagram.com/v1/media/%d/likes,',
        'remove_like' => 'https://api.instagram.com/v1/media/%d/likes?access_token=%s',
        'tags' => 'https://api.instagram.com/v1/tags/%s?access_token=%s',
        'tags_recent' => 'https://api.instagram.com/v1/tags/%s/media/recent?max_id=%d&min_id=%d&access_token=%s',
        'tags_search' => 'https://api.instagram.com/v1/tags/search?q=%s&access_token=%s',
        'locations' => 'https://api.instagram.com/v1/locations/%d?access_token=%s',
        'locations_recent' => 'https://api.instagram.com/v1/locations/%d/media/recent/?max_id=%d&min_id=%d&max_timestamp=%d&min_timestamp=%d&access_token=%s',
        'locations_search' => 'https://api.instagram.com/v1/locations/search?lat=%s&lng=%s&foursquare_id=%d&distance=%d&access_token=%s',
    );*/
    
    
    switch ($instagram_options['instagram_source']) {
      case 'user_recent':
        $request = 'https://api.instagram.com/v1/users/'.$users[ $instagram_uid ]['user_id'].'/media/recent/?access_token='.$token.'&count='.$instagram_options['instagram_photo_number'];
      break;
      case 'user_feed':
        $request = 'https://api.instagram.com/v1/users/self/feed?access_token='.$token.'&count='.$instagram_options['instagram_photo_number'].'';
      break;
      case 'user_liked':
        $request = 'https://api.instagram.com/v1/users/self/media/liked?access_token='.$token.'&count='.$instagram_options['instagram_photo_number'].'';
      break;
      case 'user_tag':
        $instagram_tag = apply_filters( $this->hook, empty($instagram_options['instagram_tag']) ? '' : $instagram_options['instagram_tag'], $instagram_options );
        $request = 'https://api.instagram.com/v1/users/'.$users[ $instagram_uid ]['user_id'].'/media/recent/?access_token='.$token.'&count='.$instagram_options['instagram_photo_number'];
      break;
      case 'global_popular':
        $request = 'https://api.instagram.com/v1/media/popular?access_token='.$token.'&count='.$instagram_options['instagram_photo_number'];
      break;
      case 'global_tag':
        $instagram_tag = apply_filters( $this->hook, empty($instagram_options['instagram_tag']) ? '' : $instagram_options['instagram_tag'], $instagram_options );
        $request = 'https://api.instagram.com/v1/tags/'.$instagram_tag.'/media/recent?access_token='.$token.'&count='.$instagram_options['instagram_photo_number'];
      break;
    } 
    
    ///////////////////////////////////////////////////
    ///      Try using wp_remote_get and JSON       ///
    ///////////////////////////////////////////////////
    if( function_exists('json_decode') ) {
      $i = 0;
      $_instagram_json = $this->fetch_instagram_feed($request);
      $repeat = true;
      //print_r($_instagram_json);
      if( !$_instagram_json || empty($_instagram_json->data) ){
        $hidden .= '<!-- Failed using wp_remote_get and JSON @ '.$request.' -->';
        $continue = false;
      }else{
        while( $repeat && count($photourl)<$instagram_options['instagram_photo_number'] ){
          $data = $_instagram_json->data;
          foreach( $data as $imageinfo ){
            $url = $imageinfo->images->low_resolution->url;
            if( 'Th' == $instagram_options['instagram_photo_size'] ){
              $url = $imageinfo->images->thumbnail->url;
            }elseif( 'L' == $instagram_options['instagram_photo_size'] ){
              $url = $imageinfo->images->standard_resolution->url;
            }
            
            if( !$record[ $url ] && count($photourl)<$instagram_options['instagram_photo_number'] ){
              if( 'user_tag' == $instagram_options['instagram_source'] && !in_array( $instagram_tag, $imageinfo->tags ) ){
                // Do nothing;
              }else{
                $record[$url] = true;
                $photourl[$i] = $url;
                $linkurl[$i] = $imageinfo->link;
                $photocap[$i] = $imageinfo->caption->text;
                $originalurl[$i] = $imageinfo->images->standard_resolution->url;
                $i++;
              }
            }
          }  
          $next_url = ($_instagram_json->pagination->next_url) ? $_instagram_json->pagination->next_url : null;
          if( $next_url ){
            $_instagram_json = $this->fetch_instagram_feed($next_url);
          }else{
            $repeat = false;
          }
        }
        if(!empty($linkurl) && !empty($photourl)){
          // If set, generate instagram link
          if( $instagram_options['instagram_display_link'] ) {
            $user_link = '<div class="AlpinePhotoTiles-display-link" >';
            $user_link .='<a href="http://instagram.com/'.$instagram_uid.'" target="_blank" >';
            $user_link .= $instagram_options['instagram_display_link_text'];
            $user_link .= '</a></div>';
          }
          // If content successfully fetched, generate output...
          $continue = true;    
          $hidden .= '<!-- Success using wp_remote_get and JSON  -->';
        }else{
          $hidden .= '<!-- No photos found using wp_remote_get and JSON  @ '.$request.' -->';  
          $continue = false;
        }
      }
    }
          
    ///////////////////////////////////////////////////////////////////////
    //// If STILL!!! nothing found, report that Instagram ID must be wrong ///
    ///////////////////////////////////////////////////////////////////////
    if( false == $continue ) {
      if($feed_found ){
        $message .= '- Instagram feed was successfully retrieved, but no photos found.';
      }else{
        $message .= '- Instagram feed not found. Please recheck your ID.';
      }
    }
      
    $results = array('continue'=>$continue,'message'=>$message,'hidden'=>$hidden,'user_link'=>$user_link,'image_captions'=>$photocap,'image_urls'=>$photourl,'image_perms'=>$linkurl,'image_originals'=>$originalurl);
    
    if( true == $continue && !$disablecache ){     
      $cache_results = $results;
      if(!is_serialized( $cache_results  )) { $cache_results  = maybe_serialize( $cache_results ); }
      $this->putCache($key, $cache_results);
      $cachetime = $this->get_option( 'cache_time' );
      if( $cachetime && is_numeric($cachetime) ){
        $this->setExpiryInterval( $cachetime*60*60 );
      }
    }
    $this->results = $results;
  }
  
  
/**
 *  Get Image Link
 *  
 *  @ Since 1.2.2
 */
  function get_link($i){
    $link = $this->options['instagram_image_link_option'];
    $photocap = $this->results['image_captions'][$i];
    $photourl = $this->results['image_urls'][$i];
    $linkurl = $this->results['image_perms'][$i];
    $url = $this->options['custom_link_url'];
    $originalurl = $this->results['image_originals'][$i];
    
    if( 'original' == $link && !empty($photourl) ){
      $this->out .= '<a href="' . $photourl . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>';
      return true;
    }elseif( ('instagram' == $link || '1' == $link)&& !empty($linkurl) ){
      $this->out .= '<a href="' . $linkurl . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>';
      return true;
    }elseif( 'link' == $link && !empty($url) ){
      $this->out .= '<a href="' . $url . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>'; 
      return true;
    }elseif( 'fancybox' == $link && !empty($originalurl) ){
      $this->out .= '<a href="' . $originalurl . '" class="AlpinePhotoTiles-link AlpinePhotoTiles-lightbox" title='."'". $photocap ."'".'>'; 
      return true;
    }  
    return false;    
  }
  
/**
 *  Update photo number count
 *  
 *  @ Since 1.2.2
 */
  function updateCount(){
    if( $this->options['instagram_photo_number'] != count( $this->results['image_urls'] ) ){
      $this->options['instagram_photo_number'] = count( $this->results['image_urls'] );
    }
  }

/**
 *  Get Parent CSS
 *  
 *  @ Since 1.2.2
 */
  function get_parent_css(){
    $opts = $this->options;
    $return = 'width:100%;max-width:'.$opts['widget_max_width'].'%;padding:0px;';
    if( 'center' == $opts['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'margin:0px auto;text-align:center;';
    }
    elseif( 'right' == $opts['widget_alignment'] || 'left' == $opts['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'float:' . $opts['widget_alignment'] . ';text-align:' . $opts['widget_alignment'] . ';';
    }
    else{
      $return .= 'margin:0px auto;text-align:center;';
    }
    return $return;
 }
 
/**
 *  Add Image Function
 *  
 *  @ Since 1.2.2
 *
 ** Possible change: place original image as 'alt' and load image as needed
 */
  function add_image($i,$css=""){
    $this->out .= '<img id="'.$this->wid.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$this->shadow.' '.$this->border.' '.$this->curves.' '.$this->highlight.'" src="' . $this->results['image_urls'][$i] . '" ';
    $this->out .= 'title='."'". $this->results['image_captions'][$i] ."'".' alt='."'". $this->results['image_captions'][$i] ."' "; // Careful about caps with ""
    $this->out .= 'border="0" hspace="0" vspace="0" style="'.$css.'"/>'; // Override the max-width set by theme
  }
  
/**
 *  Credit Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_credit_link(){
    if( !$this->options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$this->wid.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $this->out .=  $by_link;    
    }  
  }
  
/**
 *  User Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_user_link(){
    $userlink = $this->results['user_link'];
    if($userlink){ 
      if($this->options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $this->out .= '<div id="'.$this->wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $this->out .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $this->out .= '<div id="'.$this->wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $this->out .= 'style="float:'.$this->options['widget_alignment'].';max-width:'.$this->options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; 
        $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>'; // Only breakline if floating
      }
    }
  }
  
/**
 *  Setup Lightbox Call
 *  
 *  @ Since 1.2.3
 */
  function add_lightbox_call(){
    if( "fancybox" == $this->options['instagram_image_link_option'] ){
      $this->out .= '<script>jQuery(window).load(function() {'.$this->get_lightbox_call().'})</script>';
    }   
  }
  
/**
 *  Get Lightbox Call
 *  
 *  @ Since 1.2.3
 */
  function get_lightbox_call(){
    $this->set_lightbox_rel();
  
    $lightbox = $this->get_option('general_lightbox');
    $lightbox_style = $this->get_option('general_lightbox_params');
    $lightbox_style = str_replace( array("{","}"), "", $lightbox_style);
    $lightbox_style = str_replace( "'", "\'", $lightbox_style);
    
    $setRel = 'jQuery( "#'.$this->wid.'-AlpinePhotoTiles_container a.AlpinePhotoTiles-lightbox" ).attr( "rel", "'.$this->rel.'" );';
    
    if( 'fancybox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'titleShow: false, overlayOpacity: .8, overlayColor: "#000"');
      return $setRel.'if(jQuery().fancybox){jQuery( "a[rel^=\''.$this->rel.'\']" ).fancybox( { '.$lightbox_style.' } );}';  
    }elseif( 'prettyphoto' == $lightbox ){
      //theme: 'pp_default', /* light_rounded / dark_rounded / light_square / dark_square / facebook
      $lightbox_style = ($lightbox_style?$lightbox_style:'theme:"facebook",social_tools:false');
      return $setRel.'if(jQuery().prettyPhoto){jQuery( "a[rel^=\''.$this->rel.'\']" ).prettyPhoto({ '.$lightbox_style.' });}';  
    }elseif( 'colorbox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'height:"80%"');
      return $setRel.'if(jQuery().colorbox){jQuery( "a[rel^=\''.$this->rel.'\']" ).colorbox( {'.$lightbox_style.'} );}';  
    }elseif( 'alpine-fancybox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'titleShow: false, overlayOpacity: .8, overlayColor: "#000"');
      return $setRel.'if(jQuery().fancyboxForAlpine){jQuery( "a[rel^=\''.$this->rel.'\']" ).fancyboxForAlpine( { '.$lightbox_style.' } );}';  
    }
    return "";
  }
  
/**
 *  Set Lightbox "rel"
 *  
 *  @ Since 1.2.3
 */
 function set_lightbox_rel(){
    $lightbox = $this->get_option('general_lightbox');
    $custom = $this->get_option('hidden_lightbox_custom_rel');
    
    if( $custom && !empty($this->options['custom_lightbox_rel']) ){
      $this->rel = $this->options['custom_lightbox_rel'];
      $this->rel = str_replace('{rtsq}',']',$this->rel); // Decode right and left square brackets
      $this->rel = str_replace('{ltsq}','[',$this->rel);
    }elseif( 'fancybox' == $lightbox ){
      $this->rel = 'alpine-fancybox-'.$this->wid;
    }elseif( 'prettyphoto' == $lightbox ){
      $this->rel = 'alpine-prettyphoto['.$this->wid.']';
    }elseif( 'colorbox' == $lightbox ){
      $this->rel = 'alpine-colorbox['.$this->wid.']';
    }else{
      $this->rel = 'alpine-fancybox-safemode-'.$this->wid;
    }
 }
  
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_vertical(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
                      
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';

        for($i = 0;$i<$opts['instagram_photo_number'];$i++){
          $has_link = $this->get_link($i);  // Add link
          $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
          $this->add_image($i,$css); // Add image
          if( $has_link ){ $this->out .= '</a>'; } // Close link
        }
        
        $this->add_credit_link();
      
      $this->out .= '</div>'; // Close vertical-parent

      $this->add_user_link();

    $this->out .= '</div>'; // Close container
    $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');

    $this->add_lightbox_call();
    
    if( $opts['style_shadow'] || $opts['style_border'] || $opts['style_highlight']  ){
      $this->out .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$this->wid.'-vertical-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_cascade(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';
      
        for($col = 0; $col<$opts['style_column_number'];$col++){
          $this->out .= '<div class="AlpinePhotoTiles_cascade_column" style="width:'.(100/$opts['style_column_number']).'%;float:left;margin:0;">';
          $this->out .= '<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">';
          for($i = $col;$i<$opts['instagram_photo_number'];$i+=$opts['style_column_number']){
            $has_link = $this->get_link($i); // Add link
            $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
            $this->add_image($i,$css); // Add image
            if( $has_link ){ $this->out .= '</a>'; } // Close link
          }
          $this->out .= '</div></div>';
        }
        $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
          
        $this->add_credit_link();
      
      $this->out .= '</div>'; // Close cascade-parent

      $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
      
      $this->add_user_link();

    // Close container
    $this->out .= '</div>';
    $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
   
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $this->add_lightbox_call();
    
    if( $opts['style_shadow'] || $opts['style_border'] || $opts['style_highlight']  ){
      $this->out .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$this->wid.'-cascade-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }
  }

/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_hidden(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';
      
        $this->out .= '<div id="'.$this->wid.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'; 
        
          for($i = 0;$i<$opts['instagram_photo_number'];$i++){
            $has_link = $this->get_link($i); // Add link
            $css = "";
            $this->add_image($i,$css); // Add image
            
            // Load original image size
            if( "gallery" == $opts['style_option'] && !empty( $this->results['image_originals'][$i] ) ){
              $this->out .= '<img class="AlpinePhotoTiles-original-image" src="' . $this->results['image_originals'][$i]. '" />';
            }
            if( $has_link ){ $this->out .= '</a>'; } // Close link
          }
        $this->out .= '</div>';
        
        $this->add_credit_link();       
      
      $this->out .= '</div>'; // Close parent  

      $this->add_user_link();
      
    $this->out .= '</div>'; // Close container
    
    $disable = $this->get_option("general_loader");
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $this->out .= '<script>';
      if(!$disable){
        $this->out .= '
               jQuery(document).ready(function() {
                jQuery("#'.$this->wid.'-AlpinePhotoTiles_container").addClass("loading"); 
               });';
      }
    $this->out .= '
           jQuery(window).load(function() {
            jQuery("#'.$this->wid.'-AlpinePhotoTiles_container").removeClass("loading");
            if( jQuery().AlpinePhotoTilesPlugin ){
              jQuery("#'.$this->wid.'-hidden-parent").AlpinePhotoTilesPlugin({
                id:"'.$this->wid.'",
                style:"'.($opts['style_option']?$opts['style_option']:'windows').'",
                shape:"'.($opts['style_shape']?$opts['style_shape']:'square').'",
                perRow:"'.($opts['style_photo_per_row']?$opts['style_photo_per_row']:'3').'",
                imageLink:'.($opts['instagram_image_link']?'1':'0').',
                imageBorder:'.($opts['style_border']?'1':'0').',
                imageShadow:'.($opts['style_shadow']?'1':'0').',
                imageCurve:'.($opts['style_curve_corners']?'1':'0').',
                imageHighlight:'.($opts['style_highlight']?'1':'0').',
                lightbox:'.($opts['instagram_image_link_option'] == "fancybox"?'1':'0').',
                galleryHeight:'.($opts['style_gallery_height']?$opts['style_gallery_height']:'0').', // Keep for Compatibility
                galRatioWidth:'.($opts['style_gallery_ratio_width']?$opts['style_gallery_ratio_width']:'800').',
                galRatioHeight:'.($opts['style_gallery_ratio_height']?$opts['style_gallery_ratio_height']:'600').',
                highlight:"'.$highlight.'",
                pinIt:'.($opts['pinterest_pin_it_button']?'1':'0').',
                siteURL:"'.get_option( 'siteurl' ).'",
                callback: '.($opts['instagram_image_link_option'] == "fancybox"?'function(){'.$this->get_lightbox_call().'}':'""').'
              });
            }
          });
        </script>';
        
  }
 
}

?>
