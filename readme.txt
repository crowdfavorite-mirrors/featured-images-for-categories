=== Featured Images for Categories ===
Contributors: PeterShilling 
Donate link:http://helpforwp.com/donate
Tags: categories, categories images, featured images,custom taxonomy
Requires at least: 3.5
Tested up to: 3.9.2
Stable tag: trunk

Add WordPress Featured Images to both categories & tags, then display via a widget or shortcode on your WordPress site.

== Description ==

Featured Images for Categories will add WordPress Featured Images to both categories & tags. You can easily assign an image to each category or tag and then display a gallery style block of images via a widget or shortcode.

Check out the video here for a quick summary on how the plugin works.

[vimeo https://vimeo.com/74688948]

(NB. Two Pro features are also shown in this video: custom taxonomies and support of registered image sizes).

The plugin has a comprehensive documentation page located here: http://helpforwp.com/plugins/featured-images-for-categories/

**Work with custom taxonomies?**

We also have released a Pro version that supports the following:

* Custom taxonomies
* Additional widget to featured individual terms in a widget area
* Support for all registered image sizes in your WordPress theme
* A PHP function call for developers to access the featured images assigned to a category
* Ability in the shortcodes to specify exactly which categories to display by category ID

More info on the Pro version available here

http://helpforwp.com/downloads/featured-images-for-categories/

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==

Full documentation and FAQs are available on the HelpForWP.com web site here.

http://helpforwp.com/plugins/featured-images-for-categories/

Shortcode reference for the Plugin.

There is a number of shortcode arguments that you can use to control the output of this plugin.

Some are available in both the free version available here on WordPress.org and some are only available in the Pro version - more information on this is available here - http://helpforwp.com/plugins/featured-images-for-categories/

** Example shortcodes for the free version **

[FeaturedImagesCat taxonomy='category' columns='3'] 

Choose to display categories in 3 columns


[FeaturedImagesCat taxonomy='post_tag' columns='3']

Choose to display tags in 3 columns

** Example shortcodes for the Pro version **


[FeaturedImagesCat taxonomy='mytaxonomy' columns='2'] 

Display a custom taxonomy

[FeaturedImagesCat taxonomy='mytaxonomy' columns='3' imagesize='myImageSize'] 

Imagesize can be set to any registered image size in your theme eg thumbnail, medium or a custom size

[FeaturedImagesCat taxonomy='mytaxonomy' columns='2' showCatName='true'] 

Choose to display the name of the category

[FeaturedImagesCat taxonomy='mytaxonomy' columns='2' showCatName='true' showCatDesc='true']

Choose to display the description of the category

[FeaturedImagesCat taxonomy='mytaxonomy' columns='2' include='2,4,6,23'] 

Choose specific category ids

[FeaturedImagesCat taxonomy='mytaxonomy' columns='2' parentcatsonly="true"]

Show only top level categories

[FeaturedImagesCat taxonomy='category' columns='2' childrenonly="1,4,65" ]

Show specific child categories

== Screenshots ==
1. This screen shot shows the new tool added to the categories and tags interface to assign a featured image

2. This screen shot shows the widget the plugin provides to show a gallery of featured images in a widget area

== Changelog ==
Version 1.2.2 Minor bug fixes

Version 1.2 Fixed a bug which may cause featured images to be removed

Version 1.1 Add two new options in shortcode, category name and category description can be displayed under the featured image

Version 1.0 Initial public release