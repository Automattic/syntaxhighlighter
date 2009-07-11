/**
 * Wordpress SyntaxHighlighter brush for Objective-C
 * By Matej Bukovinski, www.bukovinski.com
 *
 * Copyright (C) 2009 Matej Bukovinski
 * 
 * Adapted from:
 * SyntaxHighlighter - Objective-C Brush, version 1.0.0
 * http://codepirate.seaandco.com/
 * Copyright (C) 2009 Geoffrey Byers.
 * 
 * Licensed under a GNU Lesser General Public License.
 * http://creativecommons.org/licenses/LGPL/2.1/
 * 
 */

dp.sh.Brushes.ObjC = function() {
	
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
		{ regex: dp.sh.RegexLib.SingleLineCComments,	css: 'comment' },		// one line comments
		{ regex: dp.sh.RegexLib.MultiLineCComments,		css: 'comment' },		// multiline comments
		{ regex: dp.sh.RegexLib.DoubleQuotedString,		css: 'string' },		// double quoted strings
		{ regex: dp.sh.RegexLib.SingleQuotedString,		css: 'string' },		// single quoted strings
		{ regex: new RegExp('^ *#.*', 'gm'),						css: 'preprocessor' },	// preprocessor
		{ regex: new RegExp(this.GetKeywords(datatypes), 'gm'),		css: 'datatypes' },		// datatypes
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' },		// keyword
		{ regex: new RegExp('\\bNS\\w+\\b', 'g'),					css: 'keyword' },		// keyword
		{ regex: new RegExp('@\\w+\\b', 'g'),						css: 'keyword' },		// keyword
		{ regex: new RegExp('@"(?:\\.|(\\\\\\")|[^\\""\\n])*"', 'g'),	css: 'string' }	// objc string		
		];
	
}

dp.sh.Brushes.ObjC.prototype = new dp.sh.Highlighter();
dp.sh.Brushes.ObjC.Aliases = ['objc', 'obj-c'];