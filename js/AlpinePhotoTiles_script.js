/*
 * Alpine PhotoTile : jQuery Tile Display Functions
 * By: Eric Burger, http://thealpinepress.com
 * Version: 1.0.4
 * Updated: December  2013
 * 
 */

(function( w, s, d ) {
  s.fn.AlpinePhotoTilesPlugin = function( options ) {
  
    options = s.extend( {}, s.fn.AlpinePhotoTilesPlugin.options, options );
  
    // IE 7 fallback
    /*if(!d.querySelector){
      if('windows' != options.style){
        options.style = 'rift';
      }
    }*/
    return this.each(function() {  
      var parent = s(this), 
        imageList = s(".AlpinePhotoTiles_image_list_class",parent),
        images = s('.AlpinePhotoTiles-image',imageList),
        allPerms = s('.AlpinePhotoTiles-link',imageList),
        width = parent.width(),
        currentRow,img,newDiv,newDivContainer,src,url,height,theClasses,theHeight,theWidth,perm,nextHeight,tempW,tempH,
        imageRow=[],row,currentImage,sumWidth=0,maxHeight=0,pos,normalWidth,normalHeight;
     
      if( 'square' == options.shape && 'windows' == options.style ){
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if(i%3 == 0){
            
            theClasses = "AlpinePhotoTiles-tile";
            theWidth = (width-8);
            theHeight = theWidth;
            newRow( theHeight, i );
            addDiv(i);
            
          }else if(i%3 == 1){

            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-first";
            theWidth = (width/2-4-4/2);
            theHeight = theWidth;
            newRow( theHeight, i );
            addDiv(i);
     
          }else if(i%3 == 2){
        
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-last";
            theWidth = (width/2-4-4/2);
            theHeight = theWidth;
            addDiv(i);
          }
          
          
        });
      }
      else if( 'rectangle' == options.shape && 'windows' == options.style ){
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if(i%3 == 0){
            theWidth = (width-8);
            height = theWidth*img.naturalHeight/img.naturalWidth;
            height = (height?height:width);
            
            newRow( height, i );
                        
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-tile-rectangle";
            theHeight = (height);

            addDiv(i);
            
          }else if(i%3 == 1){
            theWidth = (width/2-4-4/2);
            height = theWidth*img.naturalHeight/img.naturalWidth;
            height = (height?height:width);
            newRow( height, i );
            
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-first AlpinePhotoTiles-tile-rectangle";
            theHeight = (height);
            theWidth = (width/2-4-4/2);
            addDiv(i);
            
          }else if(i%3 == 2){
            theWidth = (width/2-4-4/2);
            nextHeight = theWidth*img.naturalHeight/img.naturalWidth;
            nextHeight = (nextHeight?nextHeight:theWidth);
            if(nextHeight && nextHeight<height){
              height = nextHeight;
              updateHeight(newDivContainer,height);
              currentRow.css({'height':height+'px'});
            }
                        
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-last AlpinePhotoTiles-tile-rectangle";
            theHeight = (height);
            addDiv(i);
          }

        });
      }      
      else if( 'floor' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        theWidth = (width/options.perRow-4-4/options.perRow);
        theHeight = (width/options.perRow);
          
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if(i%options.perRow == 0){
            newRow( width/options.perRow, i ); 
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-first";            
            addDiv(i);
          }else if(i%options.perRow == (options.perRow -1) ){
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-last";
            addDiv(i);
          }else{    
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile";
            addDiv(i);
          }
        });
      }
      else if( 'wall' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        imageRow=[];sumWidth=0;maxHeight=0;
        theHeight = (width/options.perRow);
        
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];

          tempW = (img.naturalWidth?img.naturalWidth:width);
          tempH = (img.naturalHeight?img.naturalHeight:width);
          
          currentImage = {
            "width":tempW,
            "height":tempH,
            "url":url,
            "perm":perm,
            "src":src,
            "img":img
          } 
          sumWidth += tempW;
          imageRow[imageRow.length] = currentImage;  
          
          if(i%options.perRow == (options.perRow -1) || (images.length-1)==i ){
            if( (images.length-1)==i ){
              sumWidth += (options.perRow - i%options.perRow -1)*imageRow[imageRow.length-1].width;
            }
            
            newRow(theHeight , i );

            pos = 0;
            s.each(imageRow,function(j){
              normalWidth = this.width/sumWidth*width;
              
              img = this.img;  
              url = this.url;
              perm = this.perm;
              src = this.src;
              theClasses = "AlpinePhotoTiles-tile";
              theWidth = (normalWidth-4-4/options.perRow);
              addDiv(j);
              
              newDivContainer.css({
                'left':pos+'px'
              });
              
              pos += normalWidth;
            });
          
            imageRow=[];sumWidth=0;
          } 
        });
      }
      else if( 'bookshelf' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        imageRow=[];sumWidth=0;maxHeight=0;

        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];

          tempW = (img.naturalWidth?img.naturalWidth:width);
          tempH = (img.naturalHeight?img.naturalHeight:width);
          
          currentImage = {
            "width":tempW,
            "height":tempH,
            "url":url,
            "perm":perm,
            "src":src,
            "img":img
          } 
          sumWidth += tempW;
          imageRow[imageRow.length] = currentImage;  
          
          if(i%options.perRow == (options.perRow -1) || (images.length-1)==i ){
            if( (images.length-1)==i ){
              sumWidth += (options.perRow - i%options.perRow -1)*imageRow[imageRow.length-1].width;
            }
            
            newRow( 10, i );
            currentRow.addClass('AlpinePhotoTiles-bookshelf');
            pos = 0;
            s.each(imageRow,function(){
              normalWidth = this.width/sumWidth*width;
              normalHeight = normalWidth*this.height/this.width;
            
              if( normalHeight > maxHeight ){
                maxHeight = normalHeight;
                currentRow.css({'height':normalHeight+"px"});
              }
              img = this.img;  
              url = this.url;
              perm = this.perm;
              src = this.src;
              theClasses = "AlpinePhotoTiles-book";
              theWidth = (normalWidth-4-4/options.perRow);
              theHeight = normalHeight;
              addDiv(i);
              
              newDivContainer.css({
                'left':pos+'px'
              });
              
              pos += normalWidth;
            });
          
            imageRow=[];sumWidth=0;maxHeight=0;
          }          
          
        });
      }      
      else if( 'rift' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        imageRow=[];sumWidth=0;maxHeight=0;row=0;
        
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          tempW = (img.naturalWidth?img.naturalWidth:width);
          tempH = (img.naturalHeight?img.naturalHeight:width);
          
          currentImage = {
            "width":tempW,
            "height":tempH,
            "url":url,
            "perm":perm,
            "src":src,
            "img":img
          } 
          sumWidth += tempW;
          imageRow[imageRow.length] = currentImage;  
          
          if(i%options.perRow == (options.perRow -1) || (images.length-1)==i ){
            if( (images.length-1)==i ){
              sumWidth += (options.perRow - i%options.perRow -1)*imageRow[imageRow.length-1].width;
            }
            newRow( 10, i );
            currentRow.addClass('AlpinePhotoTiles-riftline');
            pos = 0;
            s.each(imageRow,function(){
              normalWidth = this.width/sumWidth*width;
              normalHeight = normalWidth*this.height/this.width;
              if( normalHeight > maxHeight ){
                maxHeight = normalHeight;
                currentRow.css({'height':normalHeight+"px"});
              }
              img = this.img;              
              url = this.url;
              perm = this.perm;
              src = this.src;
              theClasses = 'AlpinePhotoTiles-rift AlpinePhotoTiles-float-'+row;
              theWidth = (normalWidth-4-4/options.perRow);
              theHeight = normalHeight;
              addDiv(i);
              
              newDivContainer.css({
                'left':pos+'px'
              });
              
              pos += normalWidth;
            });
          
            imageRow=[];sumWidth=0;maxHeight=0,row=(row?0:1);
          }          
          
        });
      }   
      else if( 'gallery' == options.style ){
        parent.css({'width':'100%','opacity':0});
        width = parent.width();
        var originalImages = s('img.AlpinePhotoTiles-original-image',parent);
        
        var gallery,galleryContainer,galleryHeight;
        theWidth = (width/options.perRow-4-4/options.perRow);
        theHeight = (width/options.perRow);
             
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if( 0 == i ){
            if( options.galleryHeight ){ // Keep for compatibility
              galleryHeight = width/options.perRow*options.galleryHeight;
            }else if( options.galRatioHeight && options.galRatioWidth ){
              galleryHeight = width*options.galRatioHeight/options.galRatioWidth;
            }else{
              galleryHeight = width*600/800;
            }
            
            newRow( galleryHeight, i ); 
                 
            galleryContainer = s('<div class="AlpinePhotoTiles-image-div-container AlpinePhotoTiles-gallery-container"></div>');
            galleryContainer.css({
              "height":galleryHeight+"px",
              "width":(width-8)+"px"
            });
            
            currentRow.append(galleryContainer);
                             
            if(options.imageBorder){
              galleryContainer.addClass('AlpinePhotoTiles-border-div');
              galleryContainer.width( galleryContainer.width()-10 );
              galleryContainer.height( galleryContainer.height()-10 );
            }
            if(options.imageShadow){
              galleryContainer.addClass('AlpinePhotoTiles-shadow-div');
            }
            if(options.imageCurve){
              galleryContainer.addClass('AlpinePhotoTiles-curve-div');
            }

          }
                    
          if(i%options.perRow == 0){     
            newRow( width/options.perRow, i ); 
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-first";            
            addDiv(i);
          }else if(i%options.perRow == (options.perRow -1) ){           
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile AlpinePhotoTiles-half-tile-last";            
            addDiv(i);
          }else{
            theClasses = "AlpinePhotoTiles-tile AlpinePhotoTiles-half-tile";            
            addDiv(i);
          }
          
          var storeUrl = url;
          if( originalImages[i] ){
            if( originalImages[i].src ){
              storeUrl = 'url("'+originalImages[i].src+'")';
            }
          }

          gallery = s('<div id="'+parent.attr('id')+'-image-'+i+'-gallery" class="AlpinePhotoTiles-image-div AlpinePhotoTiles-image-gallery"></div>');   
          gallery.css({
            'background-image':storeUrl
          });
          if( 0 != i ){
            gallery.hide();
          }
          galleryContainer.append(gallery);
          
          // Prevent Right-Click
          if( img.oncontextmenu ){
            gallery.attr("oncontextmenu","return false;");
          }
          
        });  

        var allThumbs = s('.AlpinePhotoTiles-image-div',parent);
        var allGalleries = s('.AlpinePhotoTiles-image-gallery',parent);
        s.each(allThumbs,function(){
          var theThumb = s(this);
          if( !theThumb.hasClass('AlpinePhotoTiles-image-gallery') ){
            theThumb.hover(function() {
              allGalleries.hide();
              s("#"+theThumb.attr('id')+"-gallery").show();
            }); 
          }
        });
        
        parent.ready(function(){
          parent.css({'opacity':1});
        });
      }
    
      // Lastly, call lighbox if applicable
      if(options.callback){
        options.callback();
      }
      
      function newRow(height,i){
        if(!s.support.leadingWhitespace && !d.querySelector){
          currentRow = s('<div></div>');
          currentRow.css({'height':height+'px'});
          parent.append(currentRow);
          
        }else{
          currentRow = s('<div class="AlpinePhotoTiles-row"></div>');
          currentRow.css({'height':height+'px'});
          parent.append(currentRow);
        }  
      }
      function addDiv(i){
        if(!s.support.leadingWhitespace && !d.querySelector){
          newDiv = s('<div id="'+parent.attr('id')+'-image-'+i+'" class="AlpinePhotoTiles-image-div" style='+"'"+'background:'+url+' no-repeat center center;'+"'"+'></div>');                
        }else{
          newDiv = s('<div id="'+parent.attr('id')+'-image-'+i+'" class="AlpinePhotoTiles-image-div"></div>');   
          newDiv.css({
            'background-image':url
          });  
        }
        // Prevent Right-Click
        if( img.oncontextmenu ){
          newDiv.attr("oncontextmenu","return false;");
        }
        
        newDivContainer = s('<div class="AlpinePhotoTiles-image-div-container '+theClasses+'"></div>');
				
        //options.captions = true;
				if( options.captions ){
					cap = s('<p>'+img.alt+'</p>');
					cap.css({
						"text-align":"left",
						"display": "block",
						"position": "absolute",
						"margin": 0,
						"padding": 5,
						"font-size": "1em",
						"bottom": 0,
						"left": 0,
						"background": "black",
						"color": "white",
						"opacity": 0
					});
					newDiv.append( cap );   
					newDivContainer.hover(function(){
						s('p',this).css({
							"opacity": "0.8"
						});
					},function(){
						s('p',this).css({
							"opacity": 0
						});
					});
				}
				
        if(!s.support.leadingWhitespace && !d.querySelector){
          newDivContainer.css({
            "height":(theHeight*0.99)+"px",
            "width":(theWidth)+"px",
            "overflow":"hidden"
          });
        }else{
          newDivContainer.css({
            "height":theHeight+"px",
            "width":theWidth+"px"
          });
        }
        
        currentRow.append(newDivContainer);
        newDivContainer.append(newDiv);
        
        if(perm){
          if(options.lightbox){
            newDiv.wrap('<a href="'+perm.href+'" title="'+perm.title+'" alt="'+perm.title+'"  class="AlpinePhotoTiles-link AlpinePhotoTiles-lightbox" target="'+perm.target+'"></a>');
            s(perm).removeClass( 'AlpinePhotoTiles-lightbox' );
          }else{
            newDiv.wrap('<a href="'+perm.href+'" class="AlpinePhotoTiles-link" target="'+perm.target+'"></a>');
          }
        }
        /*if( img.title ){
          newDivContainer.append('<div style="position:absolute;bottom:10px;right:10px;background:#fff;padding:3px;border-radius: 2px 2px 2px 2px;opacity:0.85;">'+img.title+'</div>');
        }*/
        if(options.imageBorder){
          newDivContainer.addClass('AlpinePhotoTiles-border-div');
          newDivContainer.width( newDivContainer.width()-10 );
          newDivContainer.height( newDivContainer.height()-10 );
        }
        if(options.imageHighlight){
          if(!options.imageBorder){
            newDivContainer.addClass('AlpinePhotoTiles-highlight-div');
            newDivContainer.width( newDivContainer.width()-4 );
            newDivContainer.height( newDivContainer.height()-4 );
          }
          newDivContainer.hover(function(){
            s(this).css({
              "background": options.highlight
            });
          },function(){
            if( options.imageBorder ){
              s(this).css({
                'background-color': '#fff'
              });
            }else{
              s(this).css({
                'background-color': ''
              });
            }
          });
        }
        if(options.imageShadow){
          newDivContainer.addClass('AlpinePhotoTiles-shadow-div');
        }
        if(options.imageCurve){
          newDivContainer.addClass('AlpinePhotoTiles-curve-div');
        }
        if(options.pinIt){
          var media = s(img).attr('data-original');
          media = (media?media:src);
          newDiv.addClass('AlpinePhotoTiles-pinterest-container');
          var link = s('<div class="AlpinePhotoTiles-pin-it small" ><a href="http://pinterest.com/pin/create/button/?media='+media+'&url='+(options.siteURL)+'" class="pin-it-button" count-layout="horizontal" target="_blank" style="height:100%;width:100%;display:block;"></a></div>');
          newDiv.append(link);
        }
        
      }
      
      function updateHeight(aDiv,aHeight){
        aDiv.height(aHeight);
        if(options.imageBorder){
          aDiv.height( aDiv.height()-10 );
        }
      }

    });
  }
  
  s.fn.AlpinePhotoTilesPlugin.options = {
    id: 'AlpinePress',
    pinIt: false,
    lightbox:false
  }
})( window, jQuery, document );
  
  
(function( w, s ) {
  s.fn.AlpineAdjustBordersPlugin = function( options ) {
    return this.each(function() {  
      var parent = s(this),images = s('img',parent);

      s.each(images,function(){
        var currentImg = s(this);
        var width = currentImg.parent().width();
        var wBorder = false;
        //console.log( this.alt );
        // Remove and replace ! important classes
        if( currentImg.hasClass('AlpinePhotoTiles-img-border') ){
          width -= 10;
          currentImg.removeClass('AlpinePhotoTiles-img-border');
          currentImg.css({
            'max-width':(width)+'px',
            'padding':'4px',
            'margin-left': '1px',
            'margin-right': '1px',
            'background-color':'#fff'
          });
          wBorder = true;
        }else if( currentImg.hasClass('AlpinePhotoTiles-img-noborder') ){
          currentImg.removeClass('AlpinePhotoTiles-img-noborder');
          currentImg.css({
            'max-width':(width)+'px',
            'padding':'0px'
          });
        }
        
        if( currentImg.hasClass('AlpinePhotoTiles-img-shadow') ){
          width -= 2;
          currentImg.removeClass('AlpinePhotoTiles-img-shadow');
          currentImg.css({
            "box-shadow": "0 1px 3px rgba(34, 25, 25, 0.4)",
            "margin-left": "1px",
            "margin-right": "1px",
            'max-width':(width)+'px'
          });
        }else if( currentImg.hasClass('AlpinePhotoTiles-img-noshadow') ){
          currentImg.removeClass('AlpinePhotoTiles-img-noshadow');
          currentImg.css({
            'max-width':(width)+'px',
            "box-shadow":"none"
          });
        }
        
        if( currentImg.hasClass('AlpinePhotoTiles-img-highlight') ){
          currentImg.removeClass('AlpinePhotoTiles-img-highlight');
          
          if( '4px' != currentImg.css('padding-right') ){
            width -= 6;
            currentImg.css({
              'max-width':(width)+'px',
              'padding':'2px',
              "margin-left": "1px",
              "margin-right": "1px"
            });
          }

          currentImg.hover(function(){
            s(this).css({
              "background-color": options.highlight
            });
          },function(){
            if( wBorder ){
              s(this).css({
                'background-color': '#fff'
              });
            }else{
              s(this).css({
                'background-color': ''
              });
            }
          });
        }
				/*
        options.captions = true;
				if( options.captions ){
					cap = s('<p>'+this.alt+'</p>');
					cap.css({
						"text-align":"left",
						"display": "block",
						"position": "absolute",
						"margin": 0,
						"padding": 5,
						"font-size": "1em",
						"bottom": 0,
						"left": 0,
						"background": "black",
						"color": "white",
						"opacity": 0
					});
					currentImg.parent().append( cap );   
					currentImg.parent().hover(function(){
						s('p',this).css({
							"opacity": "0.8"
						});
					},function(){
						s('p',this).css({
							"opacity": 0
						});
					});
				}*/
				
      });
    });
  }
    
})( window, jQuery );