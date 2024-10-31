=== Print PDF Generator and Publisher ===
Contributors: verkkovaraani
Tags: pdf-generator, pdf-publisher, block-editor
Requires at least: 5.2
Tested up to: 6.6
Requires PHP: 5.6
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate and publish print quality PDF. . 

== Description ==

Generate PDF files from any of your WordPress blog posts / page content with the Print PDF Generator and Publisher plugin. Choose the articles of your liking and compile them into a print-ready publication. 

== Installation ==

1. Upload or extract the `nopeamedia` folder to your site’s /wp-content/plugins/ directory. You can also use the Add new option found in the Plugins- menu in WordPress.
2. Activate the plugin through the ‘Plugins’ menu in WordPress
3. Click the “Get Token” link on the settings page to obtain an API token to use the plugin 🡪 you get directed to our shop where you can place the order on your desired product
4. You will receive the token in a confirmation email
5. On the plugin’s settings page add Primary and Secondary color options, in CMYK format (use the suggested values if you don’t have specific values determined)
6. The plugin has now been activated on your site! 
7. Start generating PDFs in your Gutenberg text editor by:
    * using the PDF Blocks
    * adjusting PDF settings on the right-hand nopea.media column
8. Use the left-hand Publications menu on your WordPress dashboard to compile single articles into a print-ready publication (works on products Basic, Pro 2 and Pro 4)

See a full introduction on the different blocks and PDF settings here https://magazine.nopea.media/create-a-printed-article-with-nopea-media-wordpress-plugin/

For more tips on how to use the plugin, please see https://en.nopea.media/ and https://magazine.nopea.media/

To see the available features and to place an order, please visit our shop https://en.nopea.media/shop/ 

For any questions or comments, kindly contact us! (https://en.nopea.media/#section-contact)

=== Free Features ===

Create PDF files from any of your WordPress posts or pages
* Use PDF Blocks to easily transform your web content into a customized PDF/print layout
* Use PDF Settings to adjust:
    * the position of heading over featured image
    * heading size
    * whether or not to show featured image
    * size of featured image
    * PDF margins (top, right, bottom, left)
    * background image (optional)
* Share the PDFs online or print with home printer
* One layout theme (font style and color)

See a full introduction on all the different blocks and the PDF settings here https://magazine.nopea.media/create-a-printed-article-with-nopea-media-wordpress-plugin/ 

=== Pro Features ===

* All feature of the Free plugin PLUS:
* Choose articles of your liking and compile them into a publication (1-4 annual publications)
* Get print-optimized appearance for professional printing of publications (automatically generated bleeds and print-quality images)
* Three layout themes (font style and color)

=== Why Choose Print PDF Generator and Publisher === 

Compared to other PDF plugins, Print PDF Generator and Publisher gives you the option to fully customize your PDFs’ layout and choose which articles to include in a publication. 

Using the PDF Blocks in your Gutenberg editor is easy, and all adjustments are designed to produce a reader-friendly experience and a stylish appearance for the print audience. 

The PDF adjustments will not affect the article’s online appearance. So, you can keep producing user-friendly, accessible and appealing content both online and offline.


===  Terms of use ===

The Print PDF Generator and Publisher plugin relies on a third party service provided by https://en.nopea.media. 
To generate the PDF document based on your configuration, the data belonging to the post, page, publication, and media post-type is sent to our api https://api.nopea.media/v2/ for processing. This is mainly because there are several system dependencies required for us to efficiently generate the PDF, which may not be available on your hosting service provider.

Nopea.media is a stateful API, which means that we only store data related to the usage statistics, and authorization data (i.e the token).
In other words, the data belonging to the post, page, publication and media post-type are not stored in the external API.
Read more about our terms of use here https://en.nopea.media/usage-terms/

=== Languages ===
The plugin is available in English and in Finnish.

== Changelog ==

= 1.2.0 =
* Added: Support for WP6.5 and WP6.6
* Fixed: Cross Site Scripting (XSS) vulnerability in admin features

= 1.1.6 =
* Added: Improved plugin speed

= 1.1.5 =
* Added "Turn off hyphenation" option

= 1.1.4 =
* Added "primary background - white text" option

= 1.1.3 =
* Added "Support PHP8.0 and WP6.0"


= 1.1.2 =
* Fixed: customizer crashing.

= 1.1.1 =
* Added: Validation for cmyk color settings. 

= 1.1.0 =
* Fixed: error plugin does not have header. 

= 1.0.9 =
* Fixed: Imporove error reporting. 
* Added: Finnish translation.
* Added: Open article pdf in new-tab.

= 1.0.8 =
* Fixed: Enabled saving publication in draft mode.

= 1.0.7 =
* New: Ability to generate separate print & web pdf from publication (paid users).

= 1.0.6 =
* New: Added support for m-chart.
* New: Generate menu from publication items.

= 1.0.5 =
* New: Added support for m-chart.
* New: Generate menu from publication items.

= 1.0.4 =
* Added clearer Installation instruction.
* Fixed article ordering in publication.
* Fixed timeout error when generating large publication.

= 1.0.3 =
* Minor fixes.

= 1.0.2 =
* Minor fixes.

= 1.0.1 =
* Added Finnish language translation.

= 1.0.0 =
* Initial release.
