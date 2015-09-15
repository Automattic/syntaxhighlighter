/**
 * Wordpress SyntaxHighlighter brush for Swift
 * By Dal Rupnik, unifiedsense.com
 *
 * Copyright (C) 2015 Dal Rupnik
 * 
 * Adapted from:
 * Wordpress SyntaxHighlighter brush for Objective-C
 * By Matej Bukovinski, www.bukovinski.com
 *
 * Copyright (C) 2009 Matej Bukovinski
 * 
 * Licensed under a GNU Lesser General Public License.
 * http://creativecommons.org/licenses/LGPL/2.1/
 * 
 */

SyntaxHighlighter.brushes.Swift = function() {
	
	var datatypes =	'char bool BOOL double float int long short id void';
	
	var keywords = 'IBAction IBOutlet SEL YES NO readwrite readonly nonatomic nil NULL ';
	keywords += 'super self copy ';
	keywords += 'break case catch class const copy __finally __exception __try ';
	keywords += 'const_cast continue private public protected __declspec ';
	keywords += 'default delete deprecated dllexport dllimport do dynamic_cast ';
	keywords += 'else enum explicit extern if for friend goto inline ';
	keywords += 'mutable naked namespace new noinline noreturn nothrow ';
	keywords += 'register reinterpret_cast return selectany ';
	keywords += 'sizeof static static_cast struct switch template this ';
	keywords += 'thread throw true false try typedef typeid typename union ';
	keywords += 'using uuid virtual volatile whcar_t while';
	
	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comment' },		// one line comments
		{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comment' },		// multiline comments
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },		// double quoted strings
		{ regex: new RegExp('^ *#.*', 'gm'),						css: 'preprocessor' },	// preprocessor
		{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'datatypes' },		// datatypes
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' },		// keyword
		{ regex: new RegExp('\\bNS\\w+\\b', 'g'),					css: 'keyword' },		// keyword
		{ regex: new RegExp('@\\w+\\b', 'g'),						css: 'keyword' }		// keyword
		];
}

SyntaxHighlighter.brushes.Swift.prototype = new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Swift.aliases = ['swift'];
