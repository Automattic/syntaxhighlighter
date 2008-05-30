=== SyntaxHighlighter ===
Contributors: matt, Viper007Bond, mdawaffe
Tags: code, sourcecode, php, xhtml, html, css
Requires at least: 2.0
Stable tag: trunk

Easily post source code such as PHP or HTML and display it in a styled box.

== Description ==

SyntaxHighlighter allows you to easily post syntax highlighted code all without loosing it's formatting or making an manual changes.

It supports the following languages (the alias for use in the post is listed next to the name):

* C++ -- `cpp`, `c`, `c++`
* C# -- `c#`, `c-sharp`, `csharp`
* CSS -- `css`
* Delphi -- `delphi`, `pascal`
* Java -- `java`
* JavaScript -- `js`, `jscript`, `javascript`
* PHP -- `php`
* Python -- `py`, `python`
* Ruby -- `rb`, `ruby`, `rails`, `ror`
* SQL -- `sql`
* VB -- `vb`, `vb.net`
* XML/HTML -- `xml`, `html`, `xhtml`, `xslt`

This plugin uses the [SyntaxHighlighter JavaScript package by Alex Gorbatchev](http://code.google.com/p/syntaxhighlighter/).

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Uploading The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - syntaxhighlighter
            | readme.txt
            | screenshot-1.png
            | syntaxhighlighter.php
            - files
                | clipboard.swf
                | shBrushCpp.js
                | shBrushCSharp.js
                | [...]
                | shCore.js
                | SyntaxHighlighter.css`

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Plugin Activation###

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" for the "SyntaxHighlighter" plugin.

###Plugin Usage###

Just wrap your code in `[sourcecode language='css']code here[/sourcecode]`. The language attribute is **required**! See the [plugin's description](http://wordpress.org/extend/plugins/syntaxhighlighter/) for a list of valid language attributes.

== Frequently Asked Questions ==

= The BBCode in my post is being replaced with &lt;pre&gt;'s just fine, but I don't see the syntax highlighting! =

Make sure your theme's footer has `<?php wp_footer(); ?>` somewhere in it, otherwise the JavaScript highlighting files won't be loaded.

= I still see the BBCode in my post. What gives? =

Make sure you correctly use the BBCode with a valid language attribute. A malformed usage of it won't result in replacement.

== Screenshots ==

1. Example code display. In this particular example, the default `wp-config.php` file contents are shown.

== Other BBCode Methods ==

Find `[sourcecode language='css']code here[/sourcecode]` too long to type? Here's some alternative examples:

* `[source language='css']code here[/source]`
* `[code language='css']code here[/code]`


* `[sourcecode lang='css']code here[/sourcecode]`
* `[source lang='css']code here[/source]`
* `[code lang='css']code here[/code]`


* `[sourcecode='css']code here[/sourcecode]`
* `[source='css']code here[/source]`
* `[code='css']code here[/code]`

== ChangeLog ==

**Version 1.1.1**

* Encode single quotes so `wptexturize()` doesn't transform them into fancy quotes and screw up code.

**Version 1.1.0**

* mdawaffe [fixed](http://dev.wp-plugins.org/ticket/703) an encoding issue relating to kses and users without the `unfiltered_html` capability. Mad props to mdawaffe.

**Version 1.0.1**

* Minor CSS fixes.
* Filter text widgets to allow posting of code.

**Version 1.0.0**

* Initial release!