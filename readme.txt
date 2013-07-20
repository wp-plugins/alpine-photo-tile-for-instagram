=== Alpine PhotoTile for Instagram ===
Contributors: theAlpinePress
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=eric%40thealpinepress%2ecom&lc=US&item_name=Alpine%20PhotoTile%20for%20Instagram%20Donation&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: photos, instagram, photostream, javascript, jQuery, stylish, pictures, images, widget, sidebar, display, gallery, wall, lightbox, fancybox, colorbox
Requires at least: 2.8
Tested up to: 3.5.2
Stable tag: 1.2.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Stylish and compact plugin for displaying Instagram images in a sidebar, post, or page. 

== Description == 
The Alpine PhotoTile for Instagram is capable of retrieving photos from a particular Instagram user or tag. 
The photos can be linked to the your Instagram page, a specific URL, or to a Lightbox slideshow. 
Also, the Shortcode Generator makes it easy to insert the widget into posts without learning any of the code. 
This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek 
presentation that I hope you will like. A full description and demonstration is available at 
[the Alpine Press](http://thealpinepress.com/alpine-phototile-for-instagram/ "Plugin Demo").

**Features:**

* Display Instagram images in a sidebar, post, or page
* Multiple styles to allow for customization
* Lighbox feature for interactive slideshow (Fancybox, prettyBox, or ColorBox)
* Simple instructions
* Widget & shortcode options
* Feed caching/storage for improved page loading

**Quick Start Guide:**

1. After installing the plugin on your WordPress site, make sure it is activated by logging into your admin area and going to Plugins in the left menu.
2. Before using the plugin, you must authorize your WordPress website to access your Instagram account. This can be done by going to Settings->AlpineTile: Instagram->Add Instagram User and following the directions on the page.
3. To add the plugin to a sidebar, go to Appearance->Widgets in the left menu.
4. Find the rectangle labeled Alpine PhotoTile for Instagram. Click and drag the rectangle to one of the sidebar containers on the right.
5. Once you drop the rectangle in a sidebar area, it should open to reveal a menu of options. The only required information for the plugin to work is an Instagram Username. Select an available ID and click save in the right bottom corner of the menu.
6. Open another page/window in your web browser and navigate to your WordPress site to see how the sidebar looks with the Alpine PhotoTile for Instagram included.
7. Play around with the various styles and options to find what works best for your site.

== Installation ==

1. Upload `alpine-photo-tile-for-instagram` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the widget like any other widget.
4. Customize based on your preference.

== Frequently Asked Questions ==

**I'm getting the message "Instagram feed was successfully retrieved, but no photos found". What does that mean?**

This message simply means that while no distinguishable errors occurred, the plugin found your feed to be empty.

**Can I insert the plugin in posts or pages? Is there a shortcode function?**

Yes, you can display photos in posts or pages using what is called a shortcode. Rather than explaining how to setup the shortcode, I have created a method of generating the shortcode. Check out the Shortcode Generator on the plugin's settings page (Settings->AlpineTile: Instagram->Shortcode Generator).

**Why doesn't the widget show my most recent photos?**

By default, the plugin caches or stores the Instagram feed for three hours (see Caching above). If the new photos have still not appeared after this time, it is possible that Instagram is responsible for the delay.

**How many photos can I display?**

The plugin can retrieve and display up to 100 photos.

If you have any more questions, please leave a message at [the Alpine Press](http://thealpinepress.com/alpine-phototile-for-instagram/ "Plugin Demo").
I am a one-man development team and I distribute these plugins for free, so please be patient with me.

== Changelog ==

= 1.2.0 =
* Rebuilt Alpine PhotoTile series to work with Instagram
* Rebuilt plugin structure into OBJECT
* Combined all Alpine Photo Tiles scripts and styles into identical files
* Improved IE 7 compatibility
* Added custom image link options
* Added Fancybox jQuery option
* Fixed galleryHeight bug
* Implemented fetch with wp_remote_get()

= 1.2.1 =
* Rebuilt admin div structure
* Fixed admin css issues

= 1.2.2 =
* Added aspect ratio options for gallery style
* Add key generator function
* Add get_image_url() functions
* Object oriented id, options, results, and output storage
* Object oriented display generation

= 1.2.3 =
* Add FancyboxForAlpine (Fancybox Safemode)
* Add choice between Fancybox, prettyBox, and ColorBox
* Add hidden options, including custom rel for lightbox

 = 1.2.3.1 =
* Fixed cache retrieval
* Removed several style options because all instagram images are squares

= 1.2.4 =
* Restructured plugin objects and reassinged functions
* Object oriented message, hidden, etc.
* Added option to disable right-clicking on images
* Added updateGlobalOptions and removed individual option calls
* Added donate button
* Fixed lightbox param option

= 1.2.5 =
* Added fallback to dynamic style and script loading using jQuery
* Various small fixes
* Moved cache location
* Updated ColorBox plugin
* Set Object params to private and implemeted set, check, and get function
* Implemeted do_alpine_method call
* Created active options and results functions
* Improved dynamic script loading

= 1.2.6 =
* Fixed jQuery bug (Removed all <> tags from inline scripts)
* Change json_decode from object to array return
* Added json_decoder function
* Added additional error reporting
* Fixed false "feed successfully retrieved" message
* Added manual_cURL function and edited relevant admin and display functions
* Add stripslashes text sanitization
* Changed lightbox parameters option from CSS to stripslashes sanitization
* Filter out videos
* Block users


= TODO =
* Add caption to display
* Rebuild jQuery display
* Check with Contact Form 7