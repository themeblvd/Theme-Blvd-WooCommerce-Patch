=== Theme Blvd WooCommerce Patch ===
Contributors: themeblvd
Tags: themeblvd, woocommerce, woo, cart, ecommerce
Requires at least: Theme Blvd Framework 2.1
Stable tag: 1.1.1

This plugins adds basic compatibility with Theme Blvd themes and WooCommerce.

== Description ==

This plugins adds basic [WooCommerce](http://wordpress.org/extend/plugins/woocommerce/ "WooCommerce") compatibility to [Responsive Theme Blvd themes](http://themeforest.net/collections/2179645-responsive-theme-blvd-wordpress-themes?ref=themeblvd "Theme Blvd themes") and [Jump Start](http://wpjumpstart.com "Jump Start").

*DISCLAIMER: Please note that the goal of this plugin is not to add anything stylistically to WooCommerce, but just to put the pieces in place, giving you a solid, structural starting point for your WooCommerce shop running on your Theme Blvd theme.*

= What does this plugin actually do? =

1. Adds appropriate Theme Blvd HTML markup to wrap the main content of WooCommerce shop pages.
2. Replaces WooCommerce pagination with Theme Blvd framework's pagination.
3. Adds WooCommerce pages into Theme Blvd breadcrumb structure (only in Theme Blvd framework v2.2+).
4. Adds frontend stylesheet to tame any Bootstrap styles that conflict with WooCommerce (only in Theme Blvd framework v2.2+).
5. Adds options for selecting sidebar layouts for various WooCommerce scenarios (i.e. Sidebar Left, Sidebar Right, or Full Width).
6. Adds custom "WooCommerce Sidebar" widget area that is incorporated into all WooCommerce pages.
7. Adds option to Theme Blvd "Page Options" that allows you to "force" a static page to be a WooCommerce page. -- For example, if you have a page where you want the WooCommerce sidebar layout and sidebar applied that's not a designated WooCommerce page, you can select it here.

== Installation ==

1. Upload `theme-blvd-woocommerce-patch` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

After installing, you'll have a new panel of options added at *Appearance > Theme Options > WooCommerce*.

== Screenshots ==

1. Here's an example of some WooCommerce's sample content being displayed with the plugin and [Alyeska](http://themeforest.net/item/alyeska-responsive-wordpress-theme/164366?ref=themeblvd "Alyeska WordPress Theme").
2. After installing this plugin, you should see a new widget area added above all of your Theme Blvd widget areas called "WooCommerce Sidebar" -- Put all of your widgets here for WooCommerce-related pages.
3. When editing a standard static page, there will now be an option under your theme's standard "Page Options" that will allow you to "force" the current static page as a "WooCommerce page" -- This will allow the WooCommerce Sidebar to be applied. Note that you don't need to do this for pages you've already set in your WooCommerce settings (i.e. cart, checkout, and account pages).
4. This is the panel that gets added to your Theme Options page for determining the sidebar layout on various WooCommerce pages.

== Changelog ==

= 1.1.1 =

* Fixed breadcrumbs bug on product pages using pagination.

= 1.1.0 =

* Added overall compatibility for Theme Blvd framework v2.2+.
* Added options for assigning custom sidebar layouts to WooCommerce pages at Appearance > Theme Options > WooCommerce.
* Added Theme Blvd pagination support on WooCommerce shop/archive pages.
* Added Theme Blvd Breadcrumbs support (Theme Blvd framework v2.2+ only).
* Added frontend stylesheet to tame some Bootstrap styling before WooCommerce styles (Theme Blvd framework v2.2+ only).

= 1.0.0 =

* This is the first release.