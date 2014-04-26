=== Alpine PhotoTile for Instagram ===
Contributors: theAlpinePress
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=eric%40thealpinepress%2ecom&lc=US&item_name=Alpine%20PhotoTile%20for%20Instagram%20Donation&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: photos, instagram, photostream, javascript, jQuery, stylish, pictures, images, widget, sidebar, display, gallery, wall, lightbox, fancybox, colorbox
Requires at least: 3.2
Tested up to: 3.8
Stable tag: 1.2.7.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Simple, stylish, and compact plugin for displaying Instagram photos in a sidebar, post, or page. 

== Description == 
Retrieve photos from a particular Instagram user or tag and display them on your WordPress site using the Alpine PhotoTile for Instagram. 
The photos can be linked to the your Instagram page, a specific URL, or to a Lightbox slideshow. 
Also, the Shortcode Generator makes it easy to insert the widget into posts without learning any of the code. 
This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek 
presentation that I hope you will like. A full description and demonstration is available at 
[the Alpine Press](http://thealpinepress.com/alpine-phototile-for-instagram/ "Plugin Demo").

**Alpine PhotoTile for Instagram Features:**

* Display photos in a sidebar, post, or page
* Multiple styles to allow for customization
* Lighbox feature for interactive slideshow (Fancybox, prettyBox, or ColorBox)
* Simple instructions for adding users and customizing features
* Widget & shortcode options
* Feed caching/storage for improved page loading

**Quick Start Guide:**

1. After installing the Alpine PhotoTile plugin on your WordPress site, make sure it is activated by logging into your admin area and going to Plugins in the left menu.
2. Before using the plugin, you must authorize your WordPress website to access your Instagram account by adding a user. This can be done by going to *Settings->AlpineTile: Instagram->Add User* and following the directions on the page.
3. To add the plugin to a sidebar, go to *Appearance->Widgets* in the left menu.
4. Find the rectangle labeled Alpine PhotoTile. Click and drag the rectangle to one of the sidebar containers on the right.
5. Once you drop the rectangle in a sidebar area, it should open to reveal a menu of options. The only required information for the plugin to work is a Username. Select an available ID and click save in the right bottom corner of the menu.
6. Open another page/window in your web browser and navigate to your WordPress site to see how the sidebar looks with the Alpine PhotoTile included.
7. Play around with the various styles and options to find what works best for your site.

== Installation ==

**Plugin Installation**

1. Go to the *Plugins->Add New* section of the Admin Panel.
2. Either search for "Alpine PhotoTile for Instagram" or upload the `alpine-photo-tile-for-instagram` folder using the upload tool.
3. Go to the *Plugins->Installed Plugins* and activate the plugin.

**Add a User**

4. Instagram is quite protective of its users. Before your WordPress website can retrieve images from Instagram, you must authorize your WordPress site to access your Instagram account. On the *Plugins->Installed Plugins* page, click on "Add User" under "Alpine PhotoTile for Instagram". You will be directed to one of the Instagram plugin's "Add Instagram User" page.
5. Follow the directions on the "Add User" page to add and save a user to the plugin. I have included the directions here for reference:
6. Before starting, go to *Instagram.com* and make sure you are logged into the account you wish to add. Once you are logged into Instagram, visit the [Instagram Developer](http://instagram.com/developer/ "Instagram Developer") page.
7. Once on the Instagram website, click on the "Manage Clients" link. If this is the first time you are adding an app or plugin, Instagram will ask you a few questions. Enter the answers, click "Sign Up", and then click "Manage Clients" again.
8. Register your WordPress site by click the "Register a New Client" button.
9. Fill in the "Register new OAuth Client" form with the infomation shown on the plugin's "Add User" page and click "Register":
10. The "Instagram Developer" page will shown a Client ID and Client Secret. Enter the Client ID and Client Secret into the form and click "Add and Authorize New User". You will then be directed to an Instagram page where you can finish the authorization. 

**Using the Widget**

11. Use the widget like any other widget. Go to *Appearance->Widgets* in the left menu. Find the rectangle labeled "Alpine PhotoTile". Click and drag the rectangle to one of the sidebar containers on the right.
12. Customize Alpine PhotoTile plugin based on your preference.

**Using the Shortcode**

13. A shortcode is a line of texted used for loading plugins within WordPress pages or posts. Rather than explaining how to setup the shortcode, I have added a tool to the Alpine PhotoTile plugin that generates the shortcode for you. Visit the "Shortcode Generator" on the plugin's settings page (*Settings->AlpineTile: Instagram->Shortcode Generator*).

== Frequently Asked Questions ==

**I'm having trouble adding a user and I keep getting an error message. Can you help?**

There are a number of reasons that the plugin might be unable to retrieve your user information. The most common problems I have seen are due to the settings on the server that is hosting your WordPress website. To try and deal with these issues, I have created the [Instagram Tool](http://thealpinepress.com/instagram-tool/ "Instagram Tool") and hosted it on my website. The Instagram tools allows you to manually retrieve the information you need and to enter it into the plugin on the bottom of the Add Instagram User page.

**I'm getting the message "Instagram feed was successfully retrieved, but no photos found". What does that mean?**

This message simply means that while no distinguishable errors occurred, the plugin found your feed to be empty.

**Can I insert the plugin in posts or pages? Is there a shortcode function?**

Yes, you can display photos in posts or pages using what is called a shortcode. Rather than explaining how to setup the shortcode, I have added a tool to the Alpine PhotoTile plugin that generates the shortcode for you. Visit the "Shortcode Generator" on the plugin's settings page (*Settings->AlpineTile: Instagram->Shortcode Generator*).

**Why doesn't the widget show my most recent photos?**

By default, the plugin caches or stores the feed for three hours (see Caching above). If the new photos have still not appeared after this time, it is possible that Instagram is responsible for the delay.

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
* Removed several style options because all images are squares

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

= 1.2.6.1  =
* Check compatibility with WP 3.8
* Small CSS changes
* Replaced deprecated jQuery APIs  ( .load() and .browser )
* Updated prettyPhoto and colorbox

= 1.2.6.2  =
* Change to option functions ( added isset() )
* Rewrote AddUser() function
* Add Emoji filter to "Add User" page. Because feed is in JSON, filter must remove all unicode characters written as strings (\u0000 to \uffff)

= 1.2.6.3 =
* Reorganized "Add User" page
* Add Emoji filter and <, >, &, " and ' encoding (esc_attr function) to photo titles

= 1.2.6.5 =
* Added wp_strip_all_tags and strip_tags functions to titles
* jQuery backwards compatibility ( .bind() function for jQuery v1.6.3 and less )
* Rewrote js functions

= 1.2.6.6 =
* Add <!--//--><![CDATA[//><!-- to JavaScript
* Reduced ReadMe
* Add addEventListener and attachEvent calls to JS loading (for jQuery in footer)
* Add SSL option

= 1.2.7 =
* Replaced local JSON decoder (with slower but more reliable library).
* Add Transient and cacheid
* Add echo_point function
* Add Plugin Tools page with plugin loading speed test.
* Disabled Delete Cache button (will not work with transient)
* Updated manual_cURL function

= 1.2.7.1 =
* Small change to endpoints
* Set limit on number of repeated calls

= TODO =
* Move JS to seperate file
* Change to FancyBox 2
* Add caption to display
* Rebuild jQuery display
* Check with Contact Form 7