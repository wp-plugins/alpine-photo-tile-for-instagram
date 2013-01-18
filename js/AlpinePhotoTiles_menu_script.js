/*
 * Alpine PhotoTile : Widget Menu Display and Nesting
 * By: Eric Burger, http://thealpinepress.com
 * Version: 1.0.0
 * Updated: August 2012
 * 
 */
jQuery(document).ready(function() {
 // if( jQuery().farbtastic ){
    jQuery('.AlpinePhotoTiles_color_picker').each(function(i){
      var prevId = jQuery(this).attr('id').replace("_picker","");
      jQuery(this).farbtastic('#'+prevId);
    });
    jQuery('.AlpinePhotoTiles_color_picker').hide();

    jQuery(".AlpinePhotoTiles_color").click(function(){
      var colorfield = jQuery(this).attr('id');
      jQuery('#'+colorfield+'_picker').slideToggle();
      if(!jQuery(this).val()){jQuery(this).val("#")};
    });
//}
});

if( !jQuery().AlpineWidgetMenuPlugin ){
  (function( w, s ) {
    s.fn.AlpineWidgetMenuPlugin = function( options ) {
      // Create some defaults, extending them with any options that were provided
      options = s.extend( {}, s.fn.AlpineWidgetMenuPlugin.options, options );

      return this.each(function(i) { 
        var theParent = s(this);
        var triggerClass = theParent.attr('data-trigger');
        
        if(triggerClass){
          var selector = s('select',theParent);
          var theChildren = s('.'+triggerClass);
          var theHidden = s('.'+triggerClass+'.'+selector.val());
          theChildren.show();
          theHidden.hide();
          //theChildren.css({'opacity':'1'});
          //theHidden.css({'opacity':'0.3'});

          selector.change(function(){
            theHidden = s('.'+triggerClass+'.'+selector.val());
            theChildren.show();
            theHidden.hide();
          });
        }
      });
    }
  })( window, jQuery );
}
