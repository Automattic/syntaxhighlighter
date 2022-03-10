=== SyntaxHighlighter Evolved ===
Contributors: Viper007Bond, automattic, donncha
Donate link: https://alex.blog/2019/03/13/in-memory-of-alex-donation-link-update/
Tags: code, source, sourcecode, php, syntax highlighting, syntax, highlight, highlighting, highlighter, WordPress.com
Requires at least: 5.6
Tested up to: 5.8
Stable tag: 3.6.1

Easily post syntax-highlighted code to your site without having to modify the code at all. As seen on WordPress.com.

== Description ==

*Now with support for the new block editor in WordPress 5.0!*

SyntaxHighlighter Evolved allows you to easily post syntax-highlighted code to your site without losing its formatting or making any manual changes. It uses the [SyntaxHighlighter JavaScript package by Alex Gorbatchev](http://alexgorbatchev.com/wiki/SyntaxHighlighter).

For a live demo, see [this plugin's homepage](https://alex.blog/wordpress-plugins/syntaxhighlighter/).

For a list of supported languages (most widely used languages are supported), see the [WordPress.com support document](http://en.support.wordpress.com/code/posting-source-code/).

Development of this plugin is [on GitHub](https://github.com/Automattic/syntaxhighlighter).
Translation of the plugin into different languages is on the [translation page](https://translate.wordpress.org/projects/wp-plugins/syntaxhighlighter).

*[As seen on WordPress.com.](http://en.blog.wordpress.com/2009/12/02/better-source-code-posting/)*

== Frequently Asked Questions ==

= The code is just being displayed raw. It isn't being converted into a code box or anything. What's wrong?  =

Make sure your theme's `footer.php` file has `<?php wp_footer(); ?>` somewhere inside of it, otherwise the plugin won't be able to do it's thing.

= Highlighting doesn't work and my browser hangs, why?

Try excluding this plugin's Javascript from any performance optimizations your site may be doing that involve minifying or concatenating JS.

== Screenshots ==

1. Example display of some PHP code.
2. An example use of the editor block.

== ChangeLog ==

= Version 3.6.1 =

* New: Set code block language when pasting into editor. #215
* New: Add syntax highlight for Haskell. #210
* New: Display the list of available brushes. #221
* Fix: Line alignment for bootstrap themes. #222
* Fix: Add space around code. #223
* Fix: XML brush - use the last occurrence of the tag. #225

= Version 3.6.0 =

* New: Add ```language shortcut. #180
* New: Add block alignment support. #179
* New: Add language selector to block toolbar. #172
* Fix: transform issue. #201
* Fix: toolbar code selection for latest Gutenberg versions #200
* Fix: Content attribute selector. #178
* Fix: Escaping for shortcodes and URLs. #177
* Fix: Escaping issues with HTML entities. #160
* Fix: MatLab brush key in brush map. #188
* Fix: Remove <code> wrapper. #163
* Fix: escaping for non-admin authored posts. #187
* Tweak: Bump "tested up to" version. #183
* Tweak: Refactor block JS code. #171
* Tweak: Update plugin tags. #181
* Tweak: Use tab size from plugin settings in block editor. #174

= Version 3.5.5 =

* Allow setting text to include br and code tags. #144
* Add Arduino Brush. #136
* Fix tags issue while alternating visual and text mode in the classic editor. #139
* Fix adding classname when using SyntaxHighlighter block. #138

= Version 3.5.4 =

* Add missing shBrushYaml file.

= Version 3.5.3 =

* Add "edit mode on double click" option to settings, shortcode parameters and block editor. #126
* Add YAML highlighter. #129
* Update plugin dependencies to the latest version. #132

= Version 3.5.2 =

* Update plugin dependencies to the latest version.

= Version 3.5.1 =

* Fix stored XSS by tightening up the autolinking code so it only does http/https text. #1
* Add more CSS keywords and values. #6
* Fork GH repository: https://github.com/Automattic/syntaxhighlighter/

= Version 3.5.0 =

* Block: Add a bunch of display settings.
* Block: Switch to server-side rendering so that the same code that renders the shortcode will render the block.

= Version 3.4.2 =

* Blocks: Support SyntaxHighlighter block being used as a reusable block.

= Version 3.4.1 =

* Bring back support for the Gutenberg plugin for the people that are still on older versions of WordPress (pre-5.0) and are using the new block editor via the Gutenberg plugin.

= Version 3.4.0 =

* Drop support for the Gutenberg plugin in favor of WordPress 5.0's native functionality (different function names).

= Version 3.3.2 =

* Gutenberg: If a language isn't selected in the block configuration, default to plain text.

= Version 3.3.1 =

* Make sure `wp-editor` script is loaded for Gutenberg.

= Version 3.3.0 =

* Gutenberg block thanks to @iandunn.
* Add a few extra keywords to the JavaScript highlighter. Props @caitp.

= Version 3.2.1 =

* Fix shortcode issues that would occur during post editing if the code contained what looked like opening HTML tags such as `<?php`. See [this forum thread](https://wordpress.org/support/topic/php-opening-closing-tags-break-code-blocks) for details.

= Version 3.2.0 =

* Don't parse shortcodes inside of HTML entities, which could result in broken HTML.
* Drop PHP4 support. This should result in PHP7 support.

= Version 3.1.13 =

* Fix setting sanitization bug. Props Alexander Concha (@xknown).
* Don't encode shortcode contents on (un)trash. Props Andrew Ozz (@azaozz).

= Version 3.1.12 =

* Updated editor JavaScript for WordPress 4.3. Props Andrew Ozz (@azaozz).

= Version 3.1.11 =

* SyntaxHighlighter 3.x: Fix table layout issue. Props jeherve.
* Fix sanitization bug for "classname" parameter.
* Fix a bug that prevented highlighting a range of lines, such as "5-10".

= Version 3.1.10 =

* New version of SyntaxHighlighter 3.x library to address XSS security issue. Props Ben Bidner for finding the bug and Alex Gorbatchev working with us (Automattic) to patch the issue.
* Compatibility with moving the plugins folder to a location other than the default one.
* Updated Japanese translation file thanks to Daisuke Takahashi.

= Version 3.1.9 =

* Reset box-shadow property for better theme support. Props Michael Fields.
* Crush images for smaller filesizes.

= Version 3.1.8 =

* TinyMCE 4.x compatibility. Props azaozz.
* Added German translation thanks to [Michael Berger](http://bitespresso.de/).

= Version 3.1.7 =

* XHTML validation fix by adding `type` attribute to `<style>` tag. Props [NoMad1337](http://www.tacticalcode.de/).

= Version 3.1.6 =

* Kill off v2 copy-to-clipboard SWF file due to XSS security issue with the file. If you want to be able to copy/paste, use the better v3.
* Switch from using a `<meta>` tag to a `<style>` tag as the JavaScript anchor in the `<head>`. This should fix the validation error under HTML5.
* Remove an old forced font-size function -- it wasn't actually used.
* Remove references.

= Version 3.1.5 =

* The slashing changes made in WordPress 3.6 (alpha) have been reverted -- we're back to the old way again. This release restores the code back to Syntaxhighlighter v3.1.3. See [ticket #21767](http://core.trac.wordpress.org/ticket/21767).

= Version 3.1.4 =

* WordPress 3.6 (alpha) compatibility. Content is now being stripped of slashes before being run through filters so this plugin needs to stop trying to strip slashes and then adding them back. See [ticket #21767](http://core.trac.wordpress.org/ticket/21767).

= Version 3.1.3 =

* Hide vertical overflow so that a vertical scrollbar doesn't show up in Chrome. Props Caio Proiete. Bump SH script version to bust browser caches.
* Allow the plugin's shortcodes to be escaped using double brackets like so: `[[code]Foobar[/code]]`. This is a core feature, but calling `do_shortcode()` on the content as it went into the database caused the extra brackets to be stripped.

= Version 3.1.2 =

* Register some placeholder shortcodes so that `strip_shortcodes()` and such work properly. Previously the shortcodes were only registered during the brief moment that they were processed by one of this plugin's filters.
* Add a `notranslate` CSS class to the `<pre>` output so that Google won't attempt to translate it. Props [Otto](http://ottopress.com/2011/google-translation-tip/).
* Run the CSS classes aka SyntaxHighlighter script parameters through a filter.

= Version 3.1.1 =

* Fix default title setting not working.
* Force `<code>` to be inline. Fixes issues with themes that do `code { display: block; }`.
* Added Japanese translation thanks to [Makoto](http://projects.makotokw.com/redmine/projects/wp-plugins/wiki/Translation).

= Version 3.1.0 =

* Allow choosing between v2.x and v3.x of the SyntaxHighlighting package. Some people prefer the old one and there's nothing wrong with it really.
* Fix broken TinyMCE plugin URL.
* Force LTR text in v3.x.
* If global collapse default is on, force on the toolbar and force off light mode to avoid the code block from disappearing.
* Make the demo on the settings page the width of the content area on the front end so it's a better example of what the code will look like.
* Bring back v2.x specific settings like `linewrap`.

= Version 3.0.0 =

* Updated to SyntaxHighlighter v3.0.83. See [changes](http://alexgorbatchev.com/SyntaxHighlighter/whatsnew.html). Main highlight is the ability to directly copy the code or double-click it to highlight it all.
* Allow specifying a line highlight range in the format of `5-10` (will highlight lines 5 through 10). Idea thanks to someone who's name I forgot. :(
* BuddyPress support! Props [Rich](http://blog.etiviti.com/).
* Add third-party Clojure brush by [Travis Whitton](http://travis-whitton.blogspot.com/2009/06/syntaxhighlighter-for-clojure.html).
* Add third-party R language brush by [Yihui Xie](http://yihui.name/en/2010/09/syntaxhighlighter-brush-for-the-r-language).
* Ukrainian transation update thanks to [AzzePis](http://wordpress.co.ua/).
* An updated Italian transation thanks to [gidibao](http://gidibao.net/).

= Version 2.3.8 =

* Disable `[latex]` to avoid collisions with LaTeX rendering plugins. Use `[tex]` instead if you want to post LaTeX source.

= Version 2.3.7 =

* Add a checkbox to settings page to force all language brushes to always be loaded, even if they aren't needed. This is incase anyone wants to use the `<pre>` tags directly (without the shortcode), for example if they use [this Windows Live Writer plugin](http://sourcecodeplugin.codeplex.com/).

= Version 2.3.6 =

* Add third-party F-Sharp brush by [Steve Gilham](http://stevegilham.blogspot.com/2009/10/syntaxhighlighter-20-brushes-for-f-and.html).
* Add third-partyMATLAB brush by [Will Schleter](http://ef.engr.utk.edu/matlab/syntaxhighlighter/) (only highlights popular keywords to avoid browser lockups).
* Prevent double-encoding of shortcode contents save. I still have no idea why the `content_save_pre` filter is sometimes run twice.

= Version 2.3.5 =

* Move third-party brushes to their own folder to make it easier to keep track of them.
* Fix broken Objective-C brush. Props Yoav.
* Add third-party LaTeX brush. This won't render LaTex, it will merely allow you to post LaTeX souce. Props [Jorge Martinez de Salinas](http://www.jorgemarsal.com/blog/2009/06/13/latex-brush-for-syntax-highlighter-plus-wordpress-plugin/).

= Version 2.3.4 =

* Use the `get_comment_text` filter instead of the `comment_text` filter to make sure to catch the output everywhere.
* Allow `on` and `off` as parameter values.

= Version 2.3.3 =

* Add `print` to PHP keywords. Props bundyxc.
* Changes to make this plugin work properly with code that creates posts from outside of the admin area, namely the [P2 theme](http://p2theme.com/).

= Version 2.3.2 =

* Allow `\0` inside of code posts for low-access users. Previously it was stripped by KSES. Also introduces new filter that can be used to escape other similar types of strings.
* Remove `min-height` CSS. I don't see the point of it and it's screwing it up in certain themes.

= Version 2.3.1 =

* Additional CSS to help prevent themes from breaking SyntaxHighlighter (stopping `code { display: block; }`).
* Add a grey border to the default theme when line numbering is enabled.
* Italian transation update thanks to [gidibao](http://gidibao.net/).
* Minor code improvements.

= Version 2.3.0 =

Major overhaul, mainly to extend flexibility so that this plugin could be used on WordPress.com without any more plugin code modification (only actions/filters are used instead to modify it).

* Updated SyntaxHighlighter package to v2.1.364. Highlights of the [changelog](http://alexgorbatchev.com/wiki/SyntaxHighlighter:Changes:2.1.364) include:
	* ColdFusion brush (aliases: `coldfusion`, `cf`)
	* Erlang brush (aliases: `erl`, `erlang`)
	* Objective-C brush (aliases: `objc`, `obj-c`)
	* Eclipse theme
	* `padlinenumbers` parameter. Set it to `false` for no line number padding, `true` for automatic padding, or an integer (number) for forced padding.
	* `rb` alias for Ruby
* Commenters can now use this plugin to post code.
* Plugin's shortcodes now work inside of the text widget again. Requires WordPress 2.9+ though.
* Overhaul of the TinyMCE plugin that assists in keeping your code sound when switching editor views. Thanks to Andrew Ozz!
* This plugin's stylesheets are now dynamically loaded. If they aren't needed, they aren't loaded.
* Lots of sanitization of shortcode attributes. Invalid keys/values are no longer used.
* New filter to control what shortcodes are registered. Used by WordPress.com to trim down the number of them.
* Saving of user's settings is now done using `register_setting()` instead of manually handing `$_POST`. Yay!
* By default, a post meta is used to mark posts as being encoded using the 2.x encoding format. This is bad for a site like WordPress.com. You can use the new `syntaxhighlighter_pre_getcodeformat` filter to return `1` or `2` (based on say `post_modified`). See `SyntaxHighlighter:get_code_format()` for more details. Don't forget to `remove_action( 'save_post', array(&$SyntaxHighlighter, 'mark_as_encoded'), 10, 2 );` to stop the post meta from being added.
* New `syntaxhighlighter_precode` filter to modify raw code before it's highlighted.
* New `syntaxhighlighter_democode` filter to modify example code on the settings page.

Localizations:

* Danish translation update thanks to [Georg S. Adamsen](http://wordpress.blogos.dk/).
* Chinese translation thanks to Hinker Liu. Will need updating for v2.3.0.

= Version 2.2.1 =

* Italian transation thanks to [gidibao](http://gidibao.net/index.php/2009/07/22/syntaxhighlighter-evolved-in-italiano/).

= Version 2.2.0 =

* Stop whitespace from being stripped when switching editor views. Props [Abel Braaksma](http://www.undermyhat.org/blog/2009/07/fix-for-leading-whitespace-bug-in-syntaxhighlighter-evolved-for-wordpress/).
* Fixed an issue with SyntaxHighlighter itself in which the Bash highlighter had issues with `<` and `>`.
* Force a specific font size for the code so themes don't mess with it.
* Allow the usage of custom aliases that aren't allowed by the highlighting package. Props [Anton Shevchuk](http://anton.shevchuk.name/).
* Danish translation thanks to [Georg S. Adamsen](http://wordpress.blogos.dk/2009/05/07/syntaks-farvning-%E2%80%93-syntax-highlighting/).
* Turkish translation thanks to [Alper](http://turkcekaynak.net/).

= Version 2.1.0 =

* Updated to reflect the new features of [v2.0.320 of Alex's script](http://alexgorbatchev.com/wiki/SyntaxHighlighter:Changes:2.0.320). Note the `stripBrs` parameter is not supported in my plugin as it is not needed in this implementation.

= Version 2.0.1 =

* Andrew Ozz was kind enough to fix a bug related to `<p>`'s being stripped when switching from the Visual to HTML tab
* Added a link to the settings page to the plugins page in the admin area

= Version 2.0.0 =

* Complete recode from scratch. Features v2 of Alex Gorbatchev's script, usage of shortcodes, and so much more.

= Version 1.1.1 =

* Encode single quotes so `wptexturize()` doesn't transform them into fancy quotes and screw up code.

= Version 1.1.0 =

* mdawaffe [fixed](http://dev.wp-plugins.org/ticket/703) an encoding issue relating to kses and users without the `unfiltered_html` capability. Mad props to mdawaffe.

= Version 1.0.1 =

* Minor CSS fixes.
* Filter text widgets to allow posting of code.

= Version 1.0.0 =

* Initial release!

= Upgrade Notice =
Security fix for stored XSS in comments.
