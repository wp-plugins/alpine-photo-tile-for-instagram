<?php


class PhotoTileForInstagramBot extends PhotoTileForInstagramBasic{  
  
   /**
   * Alpine PhotoTile for Instagram: Photo Retrieval Function
   * The PHP for retrieving content from Instagram.
   *
   * @since 1.0.0
   * @updated 1.0.3
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
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ///////////////////////////////////////////    Generate Image Content    ////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  function photo_retrieval($id, $instagram_options){
    $defaults = $this->option_defaults();
    
    $instagram_uid = apply_filters( $this->hook, empty($instagram_options['instagram_user_id']) ? 'uid' : $instagram_options['instagram_user_id'], $instagram_options );
    $instagram_uid = @ereg_replace('[[:cntrl:]]', '', $instagram_uid ); // remove ASCII's control characters
    
    // Check if access_token is available for given user
    $users = $this->get_users();
    if( empty( $users[ $instagram_uid ] ) || empty( $users[ $instagram_uid ]['access_token'] )){
      return array();
    }
    
    $token = $users[ $instagram_uid ]['access_token'];

    $key = 'instagram-'.$this->vers.'-'.$instagram_options['instagram_source'].'-'.$instagram_uid.'-'.$instagram_options['instagram_photo_number'].'-'.$instagram_options['instagram_display_link'].'-'.$instagram_options['instagram_photo_size'];

    $disablecache = $this->get_option( 'cache_disable' );
    if ( !$disablecache ) {
      if( $this->cacheExists($key) ) {
        $results = $this->getCache($key);
        $results = @unserialize($results);
        if( count($results) ){
          $results['hidden'] .= '<!-- Retrieved from cache -->';
          return $results;
        }
      }
    }
    
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

    $count = 0;
    
    ///////////////////////////////////////////////////
    ///      Try using wp_remote_get and JSON       ///
    ///////////////////////////////////////////////////
    if ( function_exists('json_decode') ) {
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
            $user_link .='<a href="=http://instagram.com/'.$instagram_uid.'" target="_blank" >';
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
    return $results;
  }
  
  
  
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 */
  function display_vertical($id, $options, $source_results){
    $linkurl = $source_results['image_perms'];
    $photocap = $source_results['image_captions'];
    $photourl = $source_results['image_urls'];
    $userlink = $source_results['user_link'];
    $originalurl = $source_results['image_originals'];

  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($options['instagram_photo_number'] != count($linkurl)){$options['instagram_photo_number']=count($linkurl);}

    $style_width = '306';
    if( 'Th' == $options['instagram_photo_size'] ){
      $style_width = '150';
    }elseif( 'L' == $options['instagram_photo_size'] ){
      $style_width = '612';
    }
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                      
    $output .= '<div id="'.$id.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
    // Align photos
    $output .= '<div id="'.$id.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="width:'.$style_width.'px;max-width:'.$options['widget_max_width'].'%;padding:0px;';
    if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $output .= 'margin:0px auto;text-align:center;';
    }
    else{
      $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
    } 
    $output .= '">';
    
    $shadow = ($options['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($options['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($options['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $highlight = ($options['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    for($i = 0;$i<$options['instagram_photo_number'];$i++){
      $has_link = false;
      $link = $options['instagram_image_link_option'];
      if( 'original' == $link && !empty($photourl[$i]) ){
        $output .= '<a href="' . $photourl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( 'instagram' == $link && !empty($linkurl[$i]) ){
        $output .= '<a href="' . $linkurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( 'link' == $link && !empty($options['custom_link_url']) ){
        $output .= '<a href="' . $options['custom_link_url'] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }elseif( 'fancybox' == $link && !empty($originalurl[$i]) ){
        $output .= '<a href="' . $originalurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }    
      $output .= '<img id="'.$id.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $photourl[$i] . '" ';
      $output .= 'title='."'". $photocap[$i] ."'".' alt='."'". $photocap[$i] ."' "; // Careful about caps with ""
      $output .= 'border="0" hspace="0" vspace="0" style="margin:1px 0 5px 0;padding:0;max-width:100%;"/>'; // Override the max-width set by theme
      if( $has_link ){ $output .= '</a>'; }
    }
    
    if( !$options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$id.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $output .=  $by_link;    
    }          
    // Close vertical-parent
    $output .= '</div>';    

    if($userlink){ 
      $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
      $output .= 'style="text-align:' . $options['widget_alignment'] . ';">'.$userlink.'</div>'; // Only breakline if floating
    }

    // Close container
    $output .= '</div>';
    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');

    if( $options['style_shadow'] || $options['style_border'] || $options['style_highlight']  ){
      $output .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$id.'-vertical-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }   
    if( $options['instagram_image_link_option'] == "fancybox"  ){
      $output .= '<script>
                  jQuery(window).load(function() {
                    jQuery( "a[rel^=\'fancybox-'.$id.'\']" ).fancybox( { titleShow: false, overlayOpacity: .8, overlayColor: "#000" } );
                  })
                </script>';  
    } 
    return $output;
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 */
  function display_cascade($id, $options, $source_results){
    $linkurl = $source_results['image_perms'];
    $photocap = $source_results['image_captions'];
    $photourl = $source_results['image_urls'];
    $userlink = $source_results['user_link'];
    $originalurl = $source_results['image_originals'];
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($options['instagram_photo_number'] != count($linkurl)){$options['instagram_photo_number']= count($linkurl);}
        
    $style_width = '306';
    if( 'Th' == $options['instagram_photo_size'] ){
      $style_width = '150';
    }elseif( 'L' == $options['instagram_photo_size'] ){
      $style_width = '612';
    }
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
    $output .= '<div id="'.$id.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
    // Align photos
    $output .= '<div id="'.$id.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="width:100%;max-width:'.$options['widget_max_width'].'%;padding:0px;';
    if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $output .= 'margin:0px auto;text-align:center;';
    }
    else{
      $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
    } 
    $output .= '">';
    
    $shadow = ($options['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($options['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($options['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners'); 
    $highlight = ($options['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    for($col = 0; $col<$options['style_column_number'];$col++){
      $output .= '<div class="AlpinePhotoTiles_cascade_column" style="width:'.(100/$options['style_column_number']).'%;float:left;margin:0;">';
      $output .= '<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">';
      for($i = $col;$i<$options['instagram_photo_number'];$i+=$options['style_column_number']){
        $has_link = false;
        $link = $options['instagram_image_link_option'];
        if( 'original' == $link && !empty($photourl[$i]) ){
          $output .= '<a href="' . $photourl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
          $has_link = true;
        }elseif( 'instagram' == $link && !empty($linkurl[$i]) ){
          $output .= '<a href="' . $linkurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
          $has_link = true;
        }elseif( 'link' == $link && !empty($options['custom_link_url']) ){
          $output .= '<a href="' . $options['custom_link_url'] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
          $has_link = true;
        }elseif( 'fancybox' == $link && !empty($originalurl[$i]) ){
          $output .= '<a href="' . $originalurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
          $has_link = true;
        }   
      
        $output .= '<img id="'.$id.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $photourl[$i] . '" ';
        $output .= 'title='."'". $photocap[$i] ."'".' alt='."'". $photocap[$i] ."' "; // Careful about caps with ""
        $output .= 'border="0" hspace="0" vspace="0" style="margin:1px 0 5px 0;padding:0;max-width:100%;"/>'; // Override the max-width set by theme
        if( $has_link ){ $output .= '</a>'; }
      }
      $output .= '</div></div>';
    }
    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
      
    if( !$options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$id.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';      
      $output .=  $by_link;    
    }          
    // Close cascade-parent
    $output .= '</div>';    

    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    if($userlink){ 
      if($options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="float:' . $options['widget_alignment'] . ';width:'.$style_width.'px;max-width:'.$options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; // Only breakline if floating
      } 
    }

    // Close container
    $output .= '</div>';
    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
   
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    if( $options['style_shadow'] || $options['style_border'] || $options['style_highlight']  ){
      $output .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$id.'-cascade-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }   
    if( $options['instagram_image_link_option'] == "fancybox"  ){
      $output .= '<script>
                  jQuery(window).load(function() {
                    jQuery( "a[rel^=\'fancybox-'.$id.'\']" ).fancybox( { titleShow: false, overlayOpacity: .8, overlayColor: "#000" } );
                  })
                </script>';  
    } 
    return $output;
  }

/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 */
  function display_hidden($id, $options, $source_results){
    $linkurl = $source_results['image_perms'];
    $photocap = $source_results['image_captions'];
    $photourl = $source_results['image_urls'];
    $userlink = $source_results['user_link'];
    $originalurl = $source_results['image_originals'];
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($options['instagram_photo_number'] != count($linkurl)){$options['instagram_photo_number']=count($linkurl);}
    
    $style_width = '306';
    if( 'Th' == $options['instagram_photo_size'] ){
      $style_width = '150';
    }elseif( 'L' == $options['instagram_photo_size'] ){
      $style_width = '612';
    }
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
    $output .= '<div id="'.$id.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
    // Align photos
    $output .= '<div id="'.$id.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="width:'.$style_width.'px;max-width:'.$options['widget_max_width'].'%;padding:0px;';
    if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $output .= 'margin:0px auto;text-align:center;';
    }
    else{
      $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
    } 
    $output .= '">';
    
    $output .= '<div id="'.$id.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'; 
    
    $shadow = ($options['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($options['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($options['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    
    for($i = 0;$i<$options['instagram_photo_number'];$i++){
      $has_link = false;
      $link = $options['instagram_image_link_option'];
      if( 'original' == $link && !empty($photourl[$i]) ){
        $output .= '<a href="' . $photourl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( 'instagram' == $link && !empty($linkurl[$i]) ){
        $output .= '<a href="' . $linkurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( 'link' == $link && !empty($options['custom_link_url']) ){
        $output .= '<a href="' . $options['custom_link_url'] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }elseif( 'fancybox' == $link && !empty($originalurl[$i]) ){
        $output .= '<a href="' . $originalurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }  
      $output .= '<img id="'.$id.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.'" src="' . $photourl[$i] . '" ';
      $output .= 'title='."'". $photocap[$i] ."'".' alt='."'". $photocap[$i] ."' "; // Careful about caps with ""
      $output .= 'border="0" hspace="0" vspace="0" />'; // Override the max-width set by theme
      
      // Load original image size
      if( "gallery" == $options['style_option'] && $originalurl[$i] ){
        $output .= '<img class="AlpinePhotoTiles-original-image" src="' . $originalurl[$i]. '" />';
      }
      if( $has_link ){ $output .= '</a>'; }
    }
    $output .= '</div>';
    
    if( !$options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$id.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $output .=  $by_link;    
    }          
    // Close vertical-parent
    $output .= '</div>';      

    if($userlink){ 
      if($options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="float:' . $options['widget_alignment'] . ';width:'.$style_width.'px;max-width:'.$options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; // Only breakline if floating
      } 
    }

    // Close container
    $output .= '</div>';
    $disable = $this->get_option("general_loader");
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $output .= '<script>';
    
    if(!$disable){
      $output .= '
             jQuery(document).ready(function() {
              jQuery("#'.$id.'-AlpinePhotoTiles_container").addClass("loading"); 
             });';
    }
    $output .= '
          jQuery(window).load(function() {
            jQuery("#'.$id.'-AlpinePhotoTiles_container").removeClass("loading");
            if( jQuery().AlpinePhotoTilesPlugin ){
              jQuery("#'.$id.'-hidden-parent").AlpinePhotoTilesPlugin({
                id:"'.$id.'",
                style:"'.($options['style_option']?$options['style_option']:'windows').'",
                shape:"'.($options['style_shape']?$options['style_shape']:'square').'",
                perRow:"'.($options['style_photo_per_row']?$options['style_photo_per_row']:'3').'",
                imageLink:'.($options['instagram_image_link']?'1':'0').',
                imageBorder:'.($options['style_border']?'1':'0').',
                imageShadow:'.($options['style_shadow']?'1':'0').',
                imageCurve:'.($options['style_curve_corners']?'1':'0').',
                imageHighlight:'.($options['style_highlight']?'1':'0').',
                fancybox:'.($options['instagram_image_link_option'] == "fancybox"?'1':'0').',
                galleryHeight:'.($options['style_gallery_height']?$options['style_gallery_height']:'3').',
                highlight:"'.$highlight.'",
                pinIt:'.($options['pinterest_pin_it_button']?'1':'0').',
                siteURL:"'.get_option( 'siteurl' ).'"
              });
            }
          });
        </script>';
        
    return $output; 
  }
 
}

?>
