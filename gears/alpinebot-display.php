<?php

/** ##############################################################################################################################################
 *    AlpineBot Secondary
 * 
 *    Display functions
 *    Contains ONLY UNIVERSAL functions
 * 
 *  ##########################################################################################
 */

class PhotoTileForInstagramBotSecondary extends PhotoTileForInstagramPrimary{     
   
/**
 *  Update global (non-widget) options
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.5
 */
  function update_global_options(){
    $options = $this->get_all_options();
    $defaults = $this->option_defaults(); 
    foreach( $defaults as $name=>$info ){
      if( empty($info['widget']) && isset($options[$name])){
        // Update non-widget settings only
        $this->set_active_option($name,$options[$name]);
      }
    }
    // Go ahead and reset info also
    $this->set_private('results', array('photos'=>array(),'feed_found'=>false,'success'=>false,'userlink'=>'','hidden'=>'','message'=>'') );
  }
  
//////////////////////////////////////////////////////////////////////////////////////
///////////////////////      Feed Fetch Functions       //////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

/**
 *  Function for creating cache key
 *  
 *  @ Since 1.2.2
 */
  function key_maker( $array ){
    if( isset($array['name']) && is_array( $array['info'] ) ){
      $return = $array['name'];
      foreach( $array['info'] as $key=>$val ){
        $return = $return."-".(!empty($val)?$val:$key);
      }
      $return = $this->filter_filename( $return );
      return $return;
    }
  }
/**
 *  Filter string and remove specified characters
 *  
 *  @ Since 1.2.2
 */  
  function filter_filename( $name ){
    $name = @ereg_replace('[[:cntrl:]]', '', $name ); // remove ASCII's control characters
    $bad = array_merge(
      array_map('chr', range(0,31)),
      array("<",">",":",'"',"/","\\","|","?","*"," ",",","\'",".")); 
    $return = str_replace($bad, "", $name); // Remove Windows filename prohibited characters
    return $return;
  }
  
//////////////////////////////////////////////////////////////////////////////////////
/////////////////////////      Cache Functions       /////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

/**
 * Functions for retrieving results from cache
 *  
 * @ Since 1.2.4
 * @ Updated 1.2.7
 */
  function retrieve_from_cache( $key ){
    if ( !$this->check_active_option('cache_disable') ) {
			// New method (since 1.2.7)
			if( is_callable('get_transient') ){
				$this->echo_point('<span style="color:blue">Using get_transient()...</span>');
				$id = $this->get_private('cacheid');
				$old_key = get_transient( $id.'_key' );
				if( $key != $old_key ){
					// Settings have changed or transient expired.
					$this->echo_point('<span style="color:blue">No cache found</span>');
					return false;
				}else{
					// Retreive from transient
					$results = get_transient( $id.'_record' );
					if( count($results) ){
						$results['hidden'] = $results['hidden'].'<!-- Retrieved from transient -->';
						$this->set_private('results',$results);
						if( $this->check_active_result('photos') ){
							$this->echo_point('Cache found. Returned from transient.');
							return true;
						}
					}else{
						return false;
					}
				}
			}

			// Old method
			if( $this->cacheExists($key) ) {
				$this->echo_point('<span style="color:blue">Using text file as cache...</span>');
        $results = $this->getCache($key);
        $results = @unserialize($results);
        if( count($results) ){
          $results['hidden'] .= '<!-- Retrieved from cache -->';
          $this->set_private('results',$results);
          if( $this->check_active_result('photos') ){
						$this->echo_point('Cache found. Return from text file.');
            return true;
          }
        }
      }
    }
    return false;
  }
/**
 * Functions for storing results in cache
 *  
 * @ Since 1.2.4
 * @ Updated 1.2.7
 *
 */
  function store_in_cache( $key ){
    if( $this->check_active_result('success') && !$this->check_active_option('disable_cache') ){     
      $cache_results = $this->get_private('results');
			$cachetime = $this->get_option('cache_time');

			// New method (since 1.2.7)
			if( is_callable('set_transient') ){
				// Use $cacheid.'_key' as transient key
				$id = $this->get_private('cacheid');
				// Store "key" and "record" as a transient
				if( set_transient( $id.'_key', $key, $cachetime*60*60 ) && set_transient( $id.'_record', $cache_results, $cachetime*60*60 ) ){
					// Value was set successfully
          return;
				}
			}
			
			// Old method
			if(!is_serialized( $cache_results  )) { $cache_results  = @maybe_serialize( $cache_results ); }
			$this->putCache($key, $cache_results);
			if( !empty($cachetime) && is_numeric($cachetime) ){
				$this->setExpiryInterval( $cachetime*60*60 );
			}
    }
  }
/**
 * Functions for clearing cache/transient
 *  
 * @ Since 1.2.7
 *
 */
  function clear_cache( $key ){
		if( is_callable('delete_transient') ){
			$id = $this->get_private('cacheid');
			delete_transient( $id.'_key' );
			delete_transient( $id.'_record' );
		}else{
			$this->clearAllCache();
		}
	}
/**
 * Functions for caching results and clearing cache
 *  
 * @since 1.1.0
 *
 */
  function setCacheDir($val) {  $this->set_private('cacheDir',$val); }  
  function setExpiryInterval($val) {  $this->set_private('expiryInterval',$val); }  
  function getExpiryInterval($val) {  return (int)$this->get_private('expiryInterval'); }
  
  function cacheExists($key) {  
    $filename_cache = $this->get_private('cacheDir') . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->get_private('cacheDir') . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info)) {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->get_private('expiryInterval'); //Last update time of the cache file  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time) {//Compare last updated and current time  
        return true;  
      }  
    }
    return false;  
  } 

  function getCache($key)  {  
    $filename_cache = $this->get_private('cacheDir') . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->get_private('cacheDir') . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info))  {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->get_private('expiryInterval'); //Last update time of the cache file  
      $time = time(); //Current Time  

      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time){ //Compare last updated and current time 
        return file_get_contents ($filename_cache);   //Get contents from file  
      }  
    }
    return null;  
  }  

  function putCache($key, $content) {  
    $time = time(); //Current Time  
    $dir = $this->get_private('cacheDir');
    if ( !file_exists($dir) ){  
      @mkdir($dir);  
      $cleaning_info = $dir . '/cleaning.info'; //Cache info 
      @file_put_contents ($cleaning_info , $time); // save the time of last cache update  
    }
    
    if ( file_exists($dir) && is_dir($dir) ){
      $filename_cache = $dir . '/' . $key . '.cache'; //Cache filename  
      $filename_info = $dir . '/' . $key . '.info'; //Cache info  
    
      @file_put_contents($filename_cache ,  $content); // save the content  
      @file_put_contents($filename_info , $time); // save the time of last cache update  
    }
  }
  
  function clearAllCache() {
    $dir = $this->get_private('cacheDir') . '/';
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
  
  function cleanCache() {
    $cleaning_info = $this->get_private('cacheDir') . '/cleaning.info'; //Cache info     
    if (file_exists($cleaning_info))  {  
      $cache_time = file_get_contents ($cleaning_info) + (int)$this->cleaningInterval; //Last update time of the cache cleaning  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  
      if ((int)$cache_time < (int)$expiry_time){ //Compare last updated and current time     
        // Clean old files
        $dir = $this->get_private('cacheDir') . '/';
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
                  /*elseif (file_exists($filename_cache) && file_exists($filename_info)) {  
                    $cache_time = file_get_contents ($filename_info) + (int)$this->cleaningInterval; //Last update time of the cache file  
                    $expiry_time = (int)$time; //Expiry time for the cache  
                    if ((int)$cache_time < (int)$expiry_time) {//Compare last updated and current time  
                      @chmod($filename_cache, 0777);
                      @unlink($filename_cache);
                      @chmod($filename_info, 0777);
                      @unlink($filename_info);
                    } 
                  }*/
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

/** ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *  ##############################################################################################################################################
 *   
 *    AlpineBot Tertiary
 * 
 *    Display functions
 *    Contains ONLY UNIQUE functions
 * 
 *  ##########################################################################################
 */
 
class PhotoTileForInstagramBotTertiary extends PhotoTileForInstagramBotSecondary{ 
 
//////////////////////////////////////////////////////////////////////////////////////
//////////////////        Unique Feed Fetch Functions        /////////////////////////
//////////////////////////////////////////////////////////////////////////////////////    
/**
 *  Function for fetching instagram feed
 *  
 *  @ Since 1.2.1
 *  @ Updated 1.2.7
 */
  function fetch_instagram_feed($request){
    // No longer write out curl_init and user WP API instead
		$this->echo_point('<span style="color:blue">Use wp_remote_get() (cURL) to make request</span>');
    $response = wp_remote_get($request,
      array(
        'method' => 'GET',
        'timeout' => 20,
        'sslverify' => false
      )
    );
    $this->append_active_result('hidden','<!-- Request made -->');
    $this->echo_point('Request complete');
		
    if( is_wp_error( $response ) || !isset($response['body']) ) {
			$this->echo_point('An error occured');
      $this->append_active_result('hidden','<!-- An error occured -->');
      if( is_wp_error( $response ) ){
        $this->append_active_result('hidden','<!-- '.$response->get_error_message().' -->');
				$this->echo_point('Error message: '.$response->get_error_message());
      }elseif( !isset($response['body']) ){
				$this->echo_point('Plugin received an empty feed');
        $this->append_active_result('hidden','<!-- No body set -->');
      }
      // Try again
      if( method_exists( $this, 'manual_cURL' ) ){
				$this->echo_point('<span style="color:blue">Try request again using Alpine\'s cURL function</span>');
				$r = $this->manual_cURL($request,true);
				$this->echo_point('Request complete');
				if( isset($r['body']) && !empty( $r['body'] ) ){
					if( isset($r['code']) && !empty( $r['code'] ) && $r['code'] >= 200 && $r['code'] < 300 ){
						// All Good
						$content = $r['body'];
					}else{
						// Error
						$this->echo_point('<span style="color:red">An error occured.</span>');
						if( isset($r['code']) ){
						$this->echo_point('<span style="color:red">Code</span>: '.$r['code']);
						}
						$this->echo_point('Content Received: '.esc_html($r['body']));
						return false;
					}
				}
      }
      if( !isset($content) ){
				$this->echo_point('No content found');
        return false;
      }
		}elseif( empty( $response['body'] ) ){
			$this->echo_point('Plugin received an empty feed');
			$this->append_active_result('hidden','<!-- No body content in response -->');
			return false;
		}elseif( !empty($response['response']) && !empty($response['response']['code']) && $response['response']['code'] != 200 ){
			$this->echo_point('<span style="color:red">An error occured.</span>');
			$this->echo_point('<span style="color:red">Code</span>: '.$response['response']['code']);
			if( !empty($response['response']['message']) ){
				$this->echo_point('<span style="color:red">Message</span>: '.$response['response']['message']);
			}
      // Try again
      if( method_exists( $this, 'manual_cURL' ) ){
				$this->echo_point('<span style="color:blue">Try request again using Alpine\'s cURL function</span>');
				$r = $this->manual_cURL($request,true);
				$this->echo_point('Request complete');
				if( isset($r['body']) && !empty( $r['body'] ) ){
					if( isset($r['code']) && !empty( $r['code'] ) && $r['code'] >= 200 && $r['code'] < 300 ){
						// All Good
						$content = $r['body'];
					}else{
						// Error
						$this->echo_point('<span style="color:red">An error occured.</span>');
						if( isset($r['code']) ){
						$this->echo_point('<span style="color:red">Code</span>: '.$r['code']);
						}
						$this->echo_point('Content Received: '.esc_html($r['body']));
						return false;
					}
				}
      }
      if( !isset($content) || empty($content) ){
				$this->echo_point('No content found');
        return false;
      }
		}else{
			$this->echo_point('Content received from Instagram');
      $content = $response['body'];
    }

		if( function_exists('json_decode') ){
			$this->echo_point('<span style="color:blue">Decode content using json_decode()</span>');
      $_instagram_json = @json_decode( $content, true );
			$this->echo_point('Decode completed');
    }else{
			$this->echo_point('<span style="color:red">Server is missing json_decode(). I recommend contacting your hosting provider.</span>');
			$this->echo_point('Try using alternative json decoder (Services_JSON). <span style="color:red">Loading times will increase significantly.</span>');
			$this->append_active_result('hidden','<!-- Missing json_decode() -->');
			if( function_exists('alpine_json_decode') ) {
				$_instagram_json = @alpine_json_decode($content, true);
			}else{
				$this->echo_point('<span style="color:red">Plugin is missing alpine_json_decode()</span>');
				$this->append_active_result('hidden','<!-- Missing alpine_json_decode() -->');
			}
    }

    if( empty($_instagram_json) || !isset($_instagram_json['meta']['code']) ){
			$this->echo_point('<span style="color:red">An error occured: No JSON Data.</span>');
      $this->append_active_result('hidden','<!-- An error occured: No JSON Data -->');
			$this->echo_point('Content Received: '.esc_html($content));
      return false;
    }elseif( 200 != $_instagram_json['meta']['code'] ){
      $this->append_active_result('hidden','<!-- An error occured: Code '.$_instagram_json['meta']['code'].' -->');
      if( isset( $_instagram_json['meta']['error_message'] ) ){
				$this->echo_point('<span style="color:red">An error occured: '.$_instagram_json['meta']['error_type'].', Message: '.$_instagram_json['meta']['error_message'].'</span>');
        $this->append_active_result('hidden','<!-- An error occured: Type '.$_instagram_json['meta']['error_type'].', Message: '.$_instagram_json['meta']['error_message'].' -->');
        $this->append_active_result('message', '<br>- '.$_instagram_json['meta']['error_message'].'');
      }
      return false;
    }else{
      return $_instagram_json;
    }
    
  }
 
/**
 * Alpine PhotoTile for Instagram: Photo Retrieval Function
 * The PHP for retrieving content from Instagram.
 *
 * @ Since 1.0.0
 * @ Updated 1.2.7
 */
  function photo_retrieval(){
    $options = $this->get_private('options');
    $defaults = $this->option_defaults();
    $instagram_uid = isset($options['instagram_user_id'])?$options['instagram_user_id']:'no_uid';
    if( $instagram_uid == 'none' ){
      $this->append_active_result('message','- You have not yet added an Instagram account to the plugin. Please return to the plugin\'s widget menu and follow the "Add an Instagram user" link.');
      return;
    }elseif( $instagram_uid == 'no_uid' ){
      $this->append_active_result('message','- No Instagram user was specified.');
      return;
    }

    $key_input = array(
      'name' => 'in',
      'info' => array(
        'v' => $this->get_private('vers'),
        's' => isset($options['instagram_source'])?$options['instagram_source']:'s',
        'u' => $instagram_uid,
        't' => isset($options['instagram_tag'])?$options['instagram_tag']:'t',$options['instagram_tag'],
        'n' => isset($options['instagram_photo_number'])?$options['instagram_photo_number']:'n',
        'l' => isset($options['instagram_display_link'])?$options['instagram_display_link']:'l',
        't' => isset($options['instagram_display_link_text'])?$options['instagram_display_link_text']:'t',
        's' => isset($options['instagram_photo_size'])?$options['instagram_photo_size']:'s',
        )
      );
		$this->echo_point('Check cache');
    $key = $this->key_maker( $key_input );
		
		if( $this->get_private('testmode') == 1 ){ $this->clear_cache( $key ); }
		
		if( $this->retrieve_from_cache( $key ) ){  return; } // Check Cache
    
		$this->echo_point('Check for access_token');
    // Check if access_token is available for given user
    $users = $this->get_instagram_users();
    if( empty( $users[ $instagram_uid ] ) || empty( $users[ $instagram_uid ]['access_token'] )){
      $this->append_active_result('hidden','<!-- Could not find user and/or access_token for '.$instagram_uid.' -->');
      $this->append_active_result('message','- Could not find an access token for '.$instagram_uid.'.');
      if( !empty( $users[ $instagram_uid ] ) && is_array( $users[ $instagram_uid ] ) ){
        foreach( $users[ $instagram_uid ] as $key=>$val ){
          $this->hidden .= '<!-- '.$key.' => '.$val.' -->';
        }
      }
      return;
    }
    
    $token = $users[ $instagram_uid ]['access_token'];
		$client_id = $users[ $instagram_uid ]['client_id'];
    $user_id = $users[ $instagram_uid ]['user_id'];

    $blocked = $this->check_active_option('general_block_users') ? explode(',',str_replace(' ','',$this->get_active_option('general_block_users'))) : array();
    if( !empty($blocked) && (in_array($instagram_uid,$blocked)||in_array($user_id,$blocked)) ){
      $this->append_active_result('hidden','<!-- User '.$instagram_uid.' is blocked -->');
      $this->append_active_result('message','- User '.$instagram_uid.' is blocked.');
      return;
    }
    
    $num = $this->get_active_option('instagram_photo_number');
    if( $this->check_active_option('photo_feed_offset') ){
      $off = $this->get_active_option('photo_feed_offset');
      $num = $num + $off;
    }
    if( $this->check_active_option('photo_feed_shuffle') && function_exists('shuffle') ){ // Shuffle the results
      $num = min( 50, $num*4 );
    }
    $request = $this->get_instagram_request( $token, $client_id, $user_id, $num );

    if( $request ) {
			$this->echo_point('Try accessing Instagram feed');
      $this->append_active_result('hidden','<!-- Using AlpinePT for Instagram v'.$this->get_private('ver').' with JSON-->');
      $this->try_json( $request, $num );
    }
    
    if( $this->check_active_result('success') ){
      $src = $this->get_private('src');
      if( $this->check_active_result('userlink') && $this->check_active_option($src.'_display_link') && $this->check_active_option($src.'_display_link_text') ){
        $linkurl = $this->get_active_result('userlink');
        $link = '<div class="AlpinePhotoTiles-display-link" >';
        $link .='<a href="'.$linkurl.'" target="_blank" >';
        $link .= $this->get_active_option($src.'_display_link_text');
        $link .= '</a></div>';
        $this->set_active_result('userlink',$link);
      }else{
        $this->set_active_result('userlink',null);
      }
			// Only store in cache if successful
			$this->echo_point('<span style="color:blue">Store results in cache</span>');
			$this->store_in_cache( $key );  // Store in cache
			$this->echo_point('Store complete');
    }else{
      if( $this->check_active_result('feed_found') ){
        $this->append_active_result('message','<br>- Instagram feed was successfully retrieved, but no photos found.');
      }else{
        $this->append_active_result('message','<br>- Instagram feed not found.');
      }
    }
  }
  
/**
 *  Function for making Instagram request with json return format ( API v1 and v2 )
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.7
 */  
  function try_json( $request, $num ){
    $_instagram_json = $this->fetch_instagram_feed($request);

    $repeat = true;
    $record = array();
		$repeat_limit = 3;
		$loop_count = 0;
    //var_dump($_instagram_json);
    if( empty($_instagram_json) || !isset($_instagram_json['data']) || empty($_instagram_json['data']) ){
			$this->echo_point('<span style="color:red">Failed using wp_remote_get()</span>');
			$this->echo_point('Check your <a href="'.$request.'" target="_blank">Instagram feed</a>. If the feed is empty, then the problem is with Instagram. If you see a full page of text, then the problem is with the Alpine plugin or your web server.');
      $this->append_active_result('hidden','<!-- Failed using wp_remote_get and JSON -->');
      $this->set_active_result('success',false);
      return;
    }else{
			$this->echo_point('Parse/filter results');
      $photos = array();
      $blocked = $this->check_active_option('general_block_users') ? explode(',',str_replace(' ','',$this->get_active_option('general_block_users'))) : array();
			$instagram_tag = $this->check_active_option('instagram_tag') ? $this->get_active_option('instagram_tag') : '';
      while( !empty($repeat) && count($photos)<$num ){
				if( $repeat_limit == $loop_count ){
					$this->echo_point('Request limit reached');
					break;
				}
				$loop_count++;
        $data = $_instagram_json['data'];
        //var_dump( $data );
        foreach( $data as $key=>$imageinfo ){

          $url = isset($imageinfo['images']['low_resolution']['url'])?$imageinfo['images']['low_resolution']['url']:$key;
          
          if( 'Th' == $this->get_active_option('instagram_photo_size') && isset($imageinfo['images']['thumbnail']['url']) ){
            $url = $imageinfo['images']['thumbnail']['url'];
          }elseif( 'L' == $this->get_active_option('instagram_photo_size') && isset($imageinfo['images']['standard_resolution']['url']) ){
            $url = $imageinfo['images']['standard_resolution']['url'];
          }
          
          if( empty($record[ $url ]) && count($photos)<$num ){
            $record[ $url ] = true;
            if( 'user_tag' == $this->get_active_option('instagram_source') && ( empty($imageinfo['tags']) || (is_array($imageinfo['tags']) && !in_array( $instagram_tag, $imageinfo['tags'])) ) ){
              // Do nothing;
            }elseif( 'video' == $imageinfo['type'] ){ // Filter out videos
              // Do nothing
            }elseif( !empty($blocked) && !empty($imageinfo['user']) && ((!empty($imageinfo['user']['username'])&&in_array($imageinfo['user']['username'],$blocked))||(!empty($imageinfo['user']['id'])&&in_array($imageinfo['user']['id'],$blocked))) ){
              // Filter blocked users
              // Do nothing
            }else{
              //var_dump( $imageinfo );
              $the_photo = array();

              $the_photo['image_link'] = (string) isset($imageinfo['link'])?$imageinfo['link']:'';
							
              $the_photo['image_title'] = (string) isset($imageinfo['caption']['text'])?$imageinfo['caption']['text']:'';
              $the_photo['image_title'] = @wp_strip_all_tags( $the_photo['image_title'] , true );
              $the_photo['image_title'] = @strip_tags( $the_photo['image_title'] );  // Strip HTML
              $the_photo['image_title'] = $this->removeEmoji( $the_photo['image_title']  );
              $the_photo['image_title'] = @esc_attr( $the_photo['image_title']  ); // Encodes <, >, &, " and ' characters
              $the_photo['image_title'] = @str_replace('"','',@str_replace("'",'',$the_photo['image_title']));  // Not necessary, but just to be safe.
              
              $the_photo['image_caption'] = "";
      
              $the_photo['image_source'] = (string) $url;
              $the_photo['image_original'] = (string) isset($imageinfo['images']['standard_resolution']['url'])?$imageinfo['images']['standard_resolution']['url']:$the_photo['image_source'];
              $photos[] = $the_photo;
							
              $this->echo_point( '<span style="color:green">Link: '.$the_photo['image_link'].',<br><span style="padding-left:2em">Src: '.$url.'</span></span>');
            }
          }
        } 
        $next_url = (isset($_instagram_json['pagination']['next_url'])) ? $_instagram_json['pagination']['next_url'] : null;
        if( count($photos)<$num && !empty($next_url) ){
					$this->echo_point( count($photos).' of '.$num.' photos found. Make another request.');
          $_instagram_json = $this->fetch_instagram_feed($next_url);
        }elseif( count($photos)<$num && 'global_popular' == $this->get_active_option('instagram_source') ){
					$this->echo_point( count($photos).' of '.$num.' photos found. Make another request.');
          $_instagram_json = $this->fetch_instagram_feed($request);
        }else{
          $repeat = false;
        }
      }

      // Remove offset from photo results
      if( $this->check_active_option('photo_feed_offset') ){
				$this->echo_point( 'Apply photo offset.');
        $offset = $this->get_active_option('photo_feed_offset');
        if( is_numeric($offset) && $offset > 0 ){
          for($j=0;$j<$offset;$j++){
            if( !empty($photos) ){
              array_shift( $photos );
            }
          }
        }
      }
      // Store photo results
      $this->set_active_result('photos',$photos);
      // If set, generate instagram link
      if( $this->check_active_option('instagram_display_link') && $this->check_active_option('instagram_display_link_text')) {
        $this->set_active_result('userlink','http://instagram.com/'.$this->get_active_option('instagram_user_id'));
      }
      
      if( $this->check_active_result('photos') ){
				$this->echo_point( '<span style="color:blue">'.count($photos).' photos found in feed</span>' );
        $this->set_active_result('success',true);
        $this->append_active_result('hidden','<!-- Success using wp_remote_get() and JSON -->');
      }else{
				$this->echo_point( '<span style="color:red">No photos found in feed</span>' );
        $this->set_active_result('success',false);
        $this->set_active_result('feed_found',true);
        $this->append_active_result('hidden','<!-- No photos found using wp_remote_get() and JSON -->');
      }
    }
  }
  
  
/**
 *  Function for forming Instagram request
 *  
 *  @ Since 1.2.4
 *  @ Updated 1.2.7.1
 */ 
  function get_instagram_request( $token, $client_id, $user_id, $num = 5 ){
    $request = false;
    $options = $this->get_private('options');
    //$num = $num; // Instagram often returns less than requested, so increase request
    if( isset($options['instagram_source']) ){
      switch ($options['instagram_source']) {
        case 'user_recent':
          $request = 'https://api.instagram.com/v1/users/'.$user_id.'/media/recent/?access_token='.$token.'&client_id='.$client_id.'&count='.$num;
        break;
        case 'user_feed':
          $request = 'https://api.instagram.com/v1/users/self/feed?access_token='.$token.'&client_id='.$client_id.'&count='.$num.'';
        break;
        case 'user_liked':
          $request = 'https://api.instagram.com/v1/users/self/media/liked?access_token='.$token.'&client_id='.$client_id.'&count='.$num.'';
        break;
        case 'user_tag':
          $instagram_tag = empty($options['instagram_tag']) ? '' : $options['instagram_tag'];
          $request = 'https://api.instagram.com/v1/users/'.$user_id.'/media/recent/?access_token='.$token.'&client_id='.$client_id.'&count='.$num;
					$this->append_active_result('hidden','<!-- with User_Tag: '.$instagram_tag.' -->');
        break;
        case 'global_popular':
          $request = 'https://api.instagram.com/v1/media/popular?access_token='.$token.'&client_id='.$client_id.'&count='.$num;
        break;
        case 'global_tag':
          $instagram_tag = empty($options['instagram_tag']) ? '' : $options['instagram_tag'];
          $request = 'https://api.instagram.com/v1/tags/'.$instagram_tag.'/media/recent?access_token='.$token.'&client_id='.$client_id.'&count='.$num;
        break;
      }
    }
    return $request;
    
    /*    protected $_endpointUrls = array(
        'user' => 'https://api.instagram.com/v1/users/%d/?access_token=%s',
        'user_feed' => 'https://api.instagram.com/v1/users/self/feed?access_token=%s&max_id=%s&min_id=%s&count=%d',
        'user_recent' => 'https://api.instagram.com/v1/users/%s/media/recent/?client_id=%s&max_id=%s&min_id=%d&max_timestamp=%d&min_timestamp=%d&count=%d', // 2011-10-18: Changed %d to %s
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
 *  AlpineBot Display
 * 
 *  Display functions
 *  Try to keep only UNIVERSAL functions
 * 
 */
 
class PhotoTileForInstagramBot extends PhotoTileForInstagramBotTertiary{
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.7
 */
  function display_vertical(){
    $this->set_private('out',''); // Clear any output;
    $this->update_count(); // Check number of images found
    $this->randomize_display(); 
    $opts = $this->get_private('options');
		
		$siteurl = get_option( 'siteurl' );
    $wid = $this->get_private('wid');
    $src = $this->get_private('src');
		$ssl = $this->get_option( 'general_images_ssl' );
    $pin = $this->get_option( 'pinterest_pin_it_button' );
		
		$shadow = ($this->check_active_option('style_shadow')?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($this->check_active_option('style_border')?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($this->check_active_option('style_curve_corners')?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $highlight = ($this->check_active_option('style_highlight')?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    $onContextMenu = ($this->check_active_option('general_disable_right_click')?'onContextMenu="return false;"':'');
		
    $this->add('<div id="'.$wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">');     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->add('<div id="'.$wid.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">');
				$this->echo_point( 'Place photos in HTML' );
				$css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
        for($i = 0;$i<$opts[$src.'_photo_number'];$i++){
					$this->add_image($i,$siteurl,$wid,$src,$shadow,$border,$curves,$highlight,$onContextMenu,$ssl,$pin,$css); // Add image
        }
        $this->echo_point( 'Placement complete' );
        $this->add_credit_link($wid);
      
      $this->add('</div>'); // Close vertical-parent

      $this->add_user_link($wid);

    $this->add('</div>'); // Close container
    $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
    
		$this->echo_point( 'Prepare JS/jQuery code' );
    // Add Lightbox call (if necessary)
		$this->echo_point( 'Check/add lightbox JS' );
    $this->add_lightbox_call();
    $this->echo_point( 'Check/add border style JS' );
    $parentID = $wid."-vertical-parent";
    $borderCall = $this->get_borders_call( $parentID );

    if( !empty($opts['style_shadow']) || !empty($opts['style_border']) || !empty($opts['style_highlight'])  ){
      $this->add("<script type='text/javascript'><!--//--><![CDATA[//><!--".$borderCall."//--><!]]> </script>");  
    }
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.7
 */
  function display_cascade(){
    $this->set_private('out',''); // Clear any output;
    $this->update_count(); // Check number of images found
    $this->randomize_display();
    $opts = $this->get_private('options');

		$siteurl = get_option( 'siteurl' );
    $wid = $this->get_private('wid');
    $src = $this->get_private('src');
		$ssl = $this->get_option( 'general_images_ssl' );
    $pin = $this->get_option( 'pinterest_pin_it_button' );
		
		$shadow = ($this->check_active_option('style_shadow')?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($this->check_active_option('style_border')?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($this->check_active_option('style_curve_corners')?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $highlight = ($this->check_active_option('style_highlight')?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    $onContextMenu = ($this->check_active_option('general_disable_right_click')?'onContextMenu="return false;"':'');
		
    $this->add('<div id="'.$wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">');     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->add('<div id="'.$wid.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">');
				$this->echo_point( 'Place photos in HTML' );
				$css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
				$width = (100/$opts['style_column_number']);
        for($col = 0; $col<$opts['style_column_number'];$col++){
          $this->add('<div class="AlpinePhotoTiles_cascade_column" style="width:'.$width.'%;float:left;margin:0;">');
          $this->add('<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">');
          for($i = $col;$i<$opts[$src.'_photo_number'];$i+=$opts['style_column_number']){
            $this->add_image($i,$siteurl,$wid,$src,$shadow,$border,$curves,$highlight,$onContextMenu,$ssl,$pin,$css); // Add image
          }
          $this->add('</div></div>');
        }
        $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
          
        $this->add_credit_link($wid);
				$this->echo_point( 'Placement complete' );
      $this->add('</div>'); // Close cascade-parent

      $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
      
      $this->add_user_link($wid);

    // Close container
    $this->add('</div>');
    $this->add('<div class="AlpinePhotoTiles_breakline"></div>');
    
		$this->echo_point( 'Prepare JS/jQuery code' );
    // Add Lightbox call (if necessary)
		$this->echo_point( 'Check/add lightbox JS' );
    $this->add_lightbox_call();
    $this->echo_point( 'Check/add border style JS' );
    $parentID = $wid."-cascade-parent";
    $borderCall = $this->get_borders_call( $parentID );

    if( !empty($opts['style_shadow']) || !empty($opts['style_border']) || !empty($opts['style_highlight'])  ){
      $this->add("<script type='text/javascript'><!--//--><![CDATA[//><!--".$borderCall."//--><!]]> </script>");  
    }
  }
/**
 *  Get borders plugin JS string
 *  
 *  @ Since 1.2.6.5
 *  @ Updated 1.2.6.6
 */
  function get_borders_call( $parentID ){
    $highlight = $this->get_option("general_highlight_color");
    $highlight = (!empty($highlight)?$highlight:'#64a2d8');

		// NOTE: BE CAREFUL ABOUT BREAKLINES, && SYMBOLS, AND LEADING SPACES
    $return ="		// Use self invoking function expression
		(function() {// Wait for window to load. Them start plugin.
			var alpinePluginLoadingFunction = function(method){
				// Check for jQuery and on() ( jQuery 1.7+ )
				if( window.jQuery ){
					if(jQuery.isFunction( jQuery(window).on ) ){jQuery(window).on('load', method);
					// Check for jQuery and bind()
					}else{jQuery(window).bind('load', method);}
				// Check for addEventListener
				}else if( window.addEventListener ){window.addEventListener('load', method, false);
				// Check for attachEvent
				}else if ( window.attachEvent ){window.attachEvent('on' + 'load', method);}
		}
			alpinePluginLoadingFunction(function(){
				if( window.jQuery ){
					if( jQuery().AlpineAdjustBordersPlugin ){
						jQuery('#".$parentID."').AlpineAdjustBordersPlugin({highlight:'".$highlight."'});
					}else{
						var css = '".($this->get_private('url').'/css/'.$this->get_private('wcss').'.css')."';
						var link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
						jQuery.getScript('".($this->get_private('url').'/js/'.$this->get_private('wjs').'.js')."', function(){
							if(document.createStyleSheet){
								document.createStyleSheet(css);
							}else{
								jQuery('head').append(link);
							}
							if( jQuery().AlpineAdjustBordersPlugin ){
								jQuery('#".$parentID."').AlpineAdjustBordersPlugin({
									highlight:'".$highlight."'
								});
							}
						}); // Close getScript
					}
				}else{console.log('Alpine plugin failed because jQuery never loaded');}
			});
		})();";
    return $return;
  }
/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.7
 */
  function display_hidden(){
    $this->set_private('out',''); // Clear any output;
    $this->update_count(); // Check number of images found
    $this->randomize_display();
    $opts = $this->get_private('options');
		
		$siteurl = get_option( 'siteurl' );
    $wid = $this->get_private('wid');
    $src = $this->get_private('src');		
    $ssl = $this->get_option( 'general_images_ssl' );
    
		$shadow = ($this->check_active_option('style_shadow')?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($this->check_active_option('style_border')?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($this->check_active_option('style_curve_corners')?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $highlight = ($this->check_active_option('style_highlight')?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    $onContextMenu = ($this->check_active_option('general_disable_right_click')?'onContextMenu="return false;"':'');
		
    $this->add('<div id="'.$wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">');     
      // Align photos
      $css = $this->get_parent_css();
      $this->add('<div id="'.$wid.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">');
				
				$this->echo_point( 'Place photos in HTML' );
        $this->add('<div id="'.$wid.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'); 
          for($i=0;$i<$opts[$src.'_photo_number'];$i++){

						$this->add_image($i,$siteurl,$wid,$src,$shadow,$border,$curves,$highlight,$onContextMenu,$ssl,false,false); // Add image
            
            if( isset($opts['style_option']) && "gallery" == $opts['style_option'] ){
							// Load original image size
							$original = $this->get_photo_info($i,'image_original');
							if( !empty( $original ) ){
								if( $ssl ){
									$original = str_replace("http:", "https:", $original, $temp = 1);
								}
								$this->add('<img class="AlpinePhotoTiles-original-image" src="' . $original . '" />');
							}
            }
          }
        $this->add('</div>');
        $this->echo_point( 'Placement complete' );
        $this->add_credit_link($wid);       
      
      $this->add('</div>'); // Close parent  

      $this->add_user_link($wid);
      
    $this->add('</div>'); // Close container

		$this->echo_point( 'Prepare JS/jQuery code' );
    
		$this->echo_point( 'Check lightbox and link options' );
    $disable = $this->get_option("general_loader");

    $lightbox = $this->get_option('general_lightbox');
    $prevent = $this->get_option('general_lightbox_no_load');    
    $hasLight = false;
    $lightScript = '';
    $lightStyle = '';
    if( !empty($prevent) ){
      $this->add("<!-- Lightbox prevented from loading -->");
    }
    if( empty($prevent) && isset($opts[$this->get_private('src').'_image_link_option']) && $opts[$src.'_image_link_option'] == 'fancybox' ){
      $lightScript = $this->get_script( $lightbox );
      $lightStyle = $this->get_style( $lightbox );
      if( !empty($lightScript) && !empty($lightStyle) ){
        $hasLight = true;
      }
    }

		// NOTE: BE CAREFUL ABOUT BREAKLINES, && SYMBOLS, AND LEADING SPACES
    $this->add("<script type='text/javascript'><!--//--><![CDATA[//><!--");
      if(!$disable){
        $this->add("		//Check for jQuery
		// Note: window.jQuery same as typeof jQuery !== 'undefined'
		if( window.jQuery ){
			jQuery(document).ready(function() {
				jQuery('#".$wid."-AlpinePhotoTiles_container').addClass('loading');
			});
		}");
      }
		$this->echo_point( 'Get Apline JS Plugin call' );  
    $pluginCall = $this->get_loading_call($opts,$wid,$src,$lightbox,$hasLight,$lightScript,$lightStyle);
    $this->echo_point( 'Add JS to HTML page' );
    $this->add("		//Use self invoking function expression
		(function(){
			// Wait for window to load. Them start plugin.
			var alpinePluginLoadingFunction = function(method){
				// Check for jQuery and on() ( jQuery 1.7+ )
				if(window.jQuery){
					if(jQuery.isFunction( jQuery(window).on )){
						jQuery(window).on('load', method);
					// Check for jQuery and bind()
					}else{jQuery(window).bind('load', method);}
				// Check for addEventListener
				}else if( window.addEventListener ){
					window.addEventListener('load', method, false);
				// Check for attachEvent
				}else if ( window.attachEvent ){
					window.attachEvent('on' + 'load', method);
				}
			};
			alpinePluginLoadingFunction( function(){
				if( window.jQuery ){".$pluginCall."}else{ console.log('Alpine plugin failed because jQuery never loaded'); }
			});
		})();//--><!]]> </script>");
	}
/**
 *  Get jQuery loading string
 *  
 *  @ Since 1.2.6.5
 */
  function get_loading_call($opts,$wid,$src,$lightbox,$hasLight,$lightScript,$lightStyle){
		// NOTE: BE CAREFUL ABOUT BREAKLINES, && SYMBOLS, AND LEADING SPACES
    $return = "jQuery('#".$wid."-AlpinePhotoTiles_container').removeClass('loading');
				var alpineLoadPlugin = function(){".$this->get_plugin_call($opts,$wid,$src,$hasLight)."}
				// Load Alpine Plugin
				if( jQuery().AlpinePhotoTilesPlugin ){
					alpineLoadPlugin();
				}else{ // Load Alpine Script and Style
					var css = '".($this->get_private('url').'/css/'.$this->get_private('wcss').'.css')."';
					var link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
					jQuery.getScript('".($this->get_private('url').'/js/'.$this->get_private('wjs').'.js')."', function(){
						if(document.createStyleSheet){
							document.createStyleSheet(css);
						}else{
							jQuery('head').append(link);
						}";
				if( $hasLight ){
					$check = ($lightbox=='fancybox'?'fancybox':($lightbox=='prettyphoto'?'prettyPhoto':($lightbox=='colorbox'?'colorbox':'fancyboxForAlpine')));    
					$return .="if( !jQuery().".$check." ){//Load Lightbox
							jQuery.getScript('".$lightScript."', function(){
								css = '".$lightStyle."';
								link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
								if(document.createStyleSheet){
									document.createStyleSheet(css);
								}else{
									jQuery('head').append(link);
								}
								alpineLoadPlugin();
							}); // Close getScript
						}else{alpineLoadPlugin();}";
					}else{
						$return .= "alpineLoadPlugin();";
					}
						$return .= "});// Close getScript
				}";
    return $return;
  }
/**
 *  Get jQuery plugin string
 *  
 *  @ Since 1.2.6.5
 */
  function get_plugin_call($opts,$wid,$src,$hasLight){
    $highlight = $this->get_option("general_highlight_color");
    $highlight = (!empty($highlight)?$highlight:'#64a2d8');
    $return = "jQuery('#".$wid."-hidden-parent').AlpinePhotoTilesPlugin({
						id:'".$wid."',
						style:'".(isset($opts['style_option'])?$opts['style_option']:'windows')."',
						shape:'".(isset($opts['style_shape'])?$opts['style_shape']:'square')."',
						perRow:".(isset($opts['style_photo_per_row'])?$opts['style_photo_per_row']:'3').",
						imageBorder:".(!empty($opts['style_border'])?'1':'0').",
						imageShadow:".(!empty($opts['style_shadow'])?'1':'0').",
						imageCurve:".(!empty($opts['style_curve_corners'])?'1':'0').",
						imageHighlight:".(!empty($opts['style_highlight'])?'1':'0').",
						lightbox:".((isset($opts[$src.'_image_link_option']) && $opts[$src.'_image_link_option'] == 'fancybox')?'1':'0').",
						galleryHeight:".(isset($opts['style_gallery_height'])?$opts['style_gallery_height']:'0').",//Keep for Compatibility
						galRatioWidth:".(isset($opts['style_gallery_ratio_width'])?$opts['style_gallery_ratio_width']:'800').",
						galRatioHeight:".(isset($opts['style_gallery_ratio_height'])?$opts['style_gallery_ratio_height']:'600').",
						highlight:'".$highlight."',
						pinIt:".(!empty($opts['pinterest_pin_it_button'])?'1':'0').",
						siteURL:'".get_option('siteurl')."',
						callback:".(!empty($hasLight)?'function(){'.$this->get_lightbox_call().'}':"''")."
					});";
    return $return;
  }
 
/**
 *  Update photo number count
 *  
 *  @ Since 1.2.2
 */
  function update_count(){
    $src = $this->get_private('src');
    $found = ( $this->check_active_result('photos') && is_array($this->get_active_result('photos') ))?count( $this->get_active_result('photos') ):0;
    $num = $this->get_active_option( $src.'_photo_number' );
    $this->set_active_option( $src.'_photo_number', min( $num, $found ) );
  }  
/**
 *  Function for shuffleing photo feed
 *  
 *  @ Since 1.2.4
 */
  function randomize_display(){
    if( $this->check_active_option('photo_feed_shuffle') && function_exists('shuffle') ){ // Shuffle the results
      $photos = $this->get_active_result('photos');
      @shuffle( $photos );
      $this->set_active_result('photos',$photos);
    }  
  }  
/**
 *  Get Parent CSS
 *  
 *  @ Since 1.2.2
 *  @ Updated 1.2.5
 */
  function get_parent_css(){
    $max = $this->check_active_option('widget_max_width')?$this->get_active_option('widget_max_width'):100;
    $return = 'width:100%;max-width:'.$max.'%;padding:0px;';
    $align = $this->check_active_option('widget_alignment')?$this->get_active_option('widget_alignment'):'';
    if( 'center' == $align ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'margin:0px auto;text-align:center;';
    }
    elseif( 'right' == $align  || 'left' == $align  ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'float:' . $align  . ';text-align:' . $align  . ';';
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
 *  @ Updated 1.2.7
 *  Possible change: place original image as 'alt' and load image as needed
 */
  function add_image($i,$siteurl,$wid,$src,$shadow,$border,$curves,$highlight,$onContextMenu,$ssl=false,$pin=false,$css=""){
    $imagetitle = $this->get_photo_info($i,'image_title');
    $imagesrc = $this->get_photo_info($i,'image_source');
    
    if( $ssl ){ $imagesrc = str_replace("http:", "https:", $imagesrc, $temp = 1); }
    if( $pin ){ $this->add('<div class="AlpinePhotoTiles-pinterest-container" style="position:relative;display:block;" >'); }

    $has_link = $this->get_link($i,$imagetitle,$src,$ssl); // Add link
		$inside = '<img id="'.$wid.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="'. $imagesrc .'" ';
		$inside .= 'title=" '. $imagetitle .' " alt=" '. $imagetitle .' " border="0" hspace="0" vspace="0" style="'.$css.'" '.$onContextMenu.' />'; // Careful about caps with ""
    $this->add($inside); // Override the max-width set by theme
    if( $has_link ){ $this->add('</a>'); } // Close link

    if( $pin ){ 
      $original = $this->get_photo_info($i,'image_original');
      if( $ssl ){
        $original = str_replace("http:", "https:", $original, $temp = 1);
      }
      $this->add('<a href="http://pinterest.com/pin/create/button/?media='.$original.'&url='.$siteurl.'" class="AlpinePhotoTiles-pin-it-button" count-layout="horizontal" target="_blank">');
      $this->add('<div class="AlpinePhotoTiles-pin-it"></div></a>');
      $this->add('</div>');
    }
  }
/**
 *  Get Image Link
 *  
 *  @ Since 1.2.2
 *  @ Updated 1.2.7
 */
  function get_link($i,$imagetitle,$src,$ssl=false){
    
		$ilink = $this->get_active_option($src.'_image_link_option');

    if( 'fancybox' == $ilink ){
			$originalurl = $this->get_photo_info($i,'image_original');
			if( $ssl ){ $originalurl = str_replace("http:", "https:", $originalurl, $temp = 1); }
			if( !empty($originalurl) ){
				$this->add('<a href="' . $originalurl . '" class="AlpinePhotoTiles-link AlpinePhotoTiles-lightbox" title=" '. $imagetitle .' " alt=" '. $imagetitle .' ">'); 
				return true;
			}
		}elseif( ($src == $ilink || '1' == $ilink) ){
			$linkurl = $this->get_photo_info($i,'image_link');
			if( !empty($linkurl) ){
				$this->add('<a href="' . $linkurl . '" class="AlpinePhotoTiles-link" target="_blank" title=" '. $imagetitle .' " alt=" '. $imagetitle .' ">');
				return true;
			}
    }elseif( 'link' == $ilink ){
			$url = $this->get_active_option('custom_link_url');
			if( !empty($url) ){
				$this->add('<a href="' . $url . '" class="AlpinePhotoTiles-link" title=" '. $imagetitle .' " alt=" '. $imagetitle .' ">'); 
				return true;
			}
    }elseif( 'original' == $ilink ){
			$photourl = $this->get_photo_info($i,'image_source');
			if( $ssl ){ $photourl = str_replace("http:", "https:", $photourl, $temp = 1);	}
			if( !empty($photourl) ){
				$this->add('<a href="' . $photourl . '" class="AlpinePhotoTiles-link" target="_blank" title=" '. $imagetitle .' " alt=" '. $imagetitle .' ">');
				return true;
			}
		}
    return false;    
  }
/**
 *  Credit Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_credit_link($wid){
    if( !$this->get_active_option('widget_disable_credit_link') ){
      $this->add('<div id="'.$wid.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>');
    }  
  }
  
/**
 *  User Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_user_link($wid){
    if( $this->check_active_result('userlink') ){
      $userlink = $this->get_active_result('userlink');
      if($this->get_active_option('widget_alignment') == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $this->add('<div id="'.$wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ');
        $this->add('style="width:100%;margin:0px auto;">'.$userlink.'</div>');
      }
      else{
        $this->add('<div id="'.$wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ');
        $this->add('style="float:'.$this->get_active_option('widget_alignment').';max-width:'.$this->get_active_option('widget_max_width').'%;"><center>'.$userlink.'</center></div>'); 
        $this->add('<div class="AlpinePhotoTiles_breakline"></div>'); // Only breakline if floating
      }
    }
  }
  
/**
 *  Setup Lightbox call
 *  
 *  @ Since 1.2.3
 *  @ Updated 1.2.6.6
 */
  function add_lightbox_call(){
    $src = $this->get_private('src');
    $lightbox = $this->get_option('general_lightbox');
    $prevent = $this->get_option('general_lightbox_no_load');
    $check = ($lightbox=='fancybox'?'fancybox':($lightbox=='prettyphoto'?'prettyPhoto':($lightbox=='colorbox'?'colorbox':'fancyboxForAlpine')));
    if( !empty($prevent) ){
      $this->add("<!-- Lightbox prevented from loading -->");
    }
		if( empty($prevent) && $this->check_active_option($src.'_image_link_option') && $this->get_active_option($src.'_image_link_option') == 'fancybox' ){
			$lightScript = $this->get_script( $lightbox );
			$lightStyle = $this->get_style( $lightbox );
			if( !empty($lightScript) && !empty($lightStyle) ){
				$lightCall = $this->get_lightbox_call();
				// NOTE: BE CAREFUL ABOUT BREAKLINES, && SYMBOLS, AND LEADING SPACES
				$lightboxSetup = "
			if( !jQuery().".$check." ){
				var css = '".$lightStyle."';
				var link = jQuery(document.createElement('link')).attr({'rel':'stylesheet','href':css,'type':'text/css','media':'screen'});
				jQuery.getScript('".($lightScript)."',function(){
					if(document.createStyleSheet){
						document.createStyleSheet(css);
					}else{
						jQuery('head').append(link);
					}".$lightCall."}); // Close getScript
			}else{".$lightCall."}";
				$this->add("<script type='text/javascript'><!--//--><![CDATA[//><!--
		// Use self invoking function expression
		(function(){
			// Wait for window to load. Them start plugin.
			var alpinePluginLoadingFunction = function(method){
				// Check for jQuery and on() ( jQuery 1.7+ )
				if( window.jQuery ){
					if( jQuery.isFunction( jQuery(window).on ) ){
						jQuery(window).on('load', method);
					// Check for jQuery and bind()
					}else{jQuery(window).bind('load', method);}
				// Check for addEventListener
				}else if( window.addEventListener ){
					window.addEventListener('load', method, false);
				// Check for attachEvent
				}else if ( window.attachEvent ){
					window.attachEvent('on' + 'load', method);
				}
			}
			alpinePluginLoadingFunction( function(){
				if( window.jQuery ){".$lightboxSetup."}else{console.log('Alpine plugin failed because jQuery never loaded');}
			});
		})();//--><!]]></script>");
      }
    }
  }
  
/**
 *  Get Lightbox Call
 *  
 *  @ Since 1.2.3
 *  @ Updated 1.2.5
 */
  function get_lightbox_call(){
    $this->set_lightbox_rel();
  
    $lightbox = $this->get_option('general_lightbox');
    $lightbox_style = $this->get_option('general_lightbox_params');
    $lightbox_style = str_replace( array("{","}"), "", $lightbox_style);
    
    $setRel = "jQuery( '#".$this->get_private('wid')."-AlpinePhotoTiles_container a.AlpinePhotoTiles-lightbox' ).attr( 'rel', '".$this->get_active_option('rel')."' );";
    
    if( 'fancybox' == $lightbox ){
      $default = "titleShow: false, overlayOpacity: .8, overlayColor: '#000', titleShow: true, titlePosition: 'inside'";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().fancybox){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).fancybox( { ".$lightbox_style." } );}";  
    }elseif( 'prettyphoto' == $lightbox ){
      //theme: 'pp_default', /* light_rounded / dark_rounded / light_square / dark_square / facebook
      $default = "theme:'facebook',social_tools:false, show_title:true";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().prettyPhoto){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).prettyPhoto({ ".$lightbox_style." });}";  
    }elseif( 'colorbox' == $lightbox ){
      $default = "maxHeight:'85%'";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().colorbox){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).colorbox( {".$lightbox_style."} );}";  
    }elseif( 'alpine-fancybox' == $lightbox ){
      $default = "titleShow: false, overlayOpacity: .8, overlayColor: '#000', titleShow: true, titlePosition: 'inside'";
      $lightbox_style = (!empty($lightbox_style)? $default.','.$lightbox_style : $default );
      return $setRel."if(jQuery().fancyboxForAlpine){jQuery( 'a[rel^=\'".$this->get_active_option('rel')."\']' ).fancyboxForAlpine( { ".$lightbox_style." } );}";  
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
    if( !empty($custom) && $this->check_active_option('custom_lightbox_rel') ){
      $rel = $this->get_active_option('custom_lightbox_rel');
      $rel = str_replace('{rtsq}',']',$rel); // Decode right and left square brackets
      $rel = str_replace('{ltsq}','[',$rel);
    }elseif( 'fancybox' == $lightbox ){
      $rel = 'alpine-fancybox-'.$this->get_private('wid');
    }elseif( 'prettyphoto' == $lightbox ){
      $rel = 'alpine-prettyphoto['.$this->get_private('wid').']';
    }elseif( 'colorbox' == $lightbox ){
      $rel = 'alpine-colorbox['.$this->get_private('wid').']';
    }else{
      $rel = 'alpine-fancybox-safemode-'.$this->get_private('wid');
    }
    $this->set_active_option('rel',$rel);
  }


}





?>
