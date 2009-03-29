=== SyntaxHighlighter Evolved ===
Contributors: Viper007Bond
Tags: code, sourcecode, php, xhtml, html, css
Requires at least: 2.7
Tested up to: 2.8
Stable tag: trunk

Easily post syntax-highlighted code to your site without having to modify the code at all.

== Description ==

SyntaxHighlighter Evolved allows you to easily post syntax-highlighted code to your site without loosing it's formatting or making any manual changes. It uses the [SyntaxHighlighter JavaScript package by Alex Gorbatchev](http://alexgorbatchev.com/wiki/SyntaxHighlighter) and a bit of code by [Automattic](http://wordpress.com/).

For a list of supported languages (all widely used languages are supported), please [click here](http://alexgorbatchev.com/wiki/SyntaxHighlighter:Brushes).

**SyntaxHighlighter "Evolved"? Why Evolved?**

Starting with v2.0.0, this plugin was renamed from "SyntaxHighlighter" to "SyntaxHighlighter Evolved". This was done to better stand out against the many very poorly named [forks](http://en.wikipedia.org/wiki/Fork_%28software_development%29) of v1.x of this plugin. I am not an author of any of those plugins, they just used my old code as a base for their version. Although I am of course biased, I'd argue this plugin is the best of all of them.

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Uploading The Plugin###

Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then upload it to `/wp-content/plugins/`.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Plugin Activation###

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" for the "SyntaxHighlighter" plugin.

###Plugin Usage###

Just wrap your code in `[language]`, such as `[php]code here[/php]` or `[css]code here[/css]`. For a list of supported languages (all widely used languages are supported), please [click here](http://alexgorbatchev.com/wiki/SyntaxHighlighter:Brushes).

You do not need to escape HTML entities or anything, just post your code as-is. The plugin will handle the rest.

The shortcodes accept a wide variety of parameters. For details, see the bottom of the plugin's settings page.

== Frequently Asked Questions ==

= The code boxes seem to be missing their styling. What's wrong? =

Make sure your theme's `header.php` file has `<?php wp_head(); ?>` somewhere inside of the `<head>`, otherwise the CSS files won't be loaded.

= The code is just being displayed raw. It isn't being converted into a code box or anything. What's wrong?  =

Make sure your theme's `footer.php` file has `<?php wp_footer(); ?>` somewhere inside of it, otherwise the Javascript files won't be loaded.

== Screenshots ==

1. Example code display of some PHP inside some HTML.
2. A part of the Settings page which controls the defaults.

== ChangeLog ==

**Version 2.0.0**

* Complete recode from scratch. Features v2 of Alex Gorbatchev's script, usage of shortcodes, and so much more.

**Version 1.1.1**

* Encode single quotes so `wptexturize()` doesn't transform them into fancy quotes and screw up code.

**Version 1.1.0**

* mdawaffe [fixed](http://dev.wp-plugins.org/ticket/703) an encoding issue relating to kses and users without the `unfiltered_html` capability. Mad props to mdawaffe.

**Version 1.0.1**

* Minor CSS fixes.
* Filter text widgets to allow posting of code.

**Version 1.0.0**

* Initial release!