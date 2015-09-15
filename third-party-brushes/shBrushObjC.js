/**
 * Updated for Objective-C as of iOS 9
 * By Dal Rupnik, unifiedsense.com
 *
 * Copyright (C) 2015 Unified Sense
 * 
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

SyntaxHighlighter.brushes.ObjC = function() {
	
	var datatypes =	'char bool BOOL double float int long short id instancetype void ';
	dataTypes += ' Class IMP SEL _cmd';
	
	var keywords = 'YES NO readwrite readonly nonatomic nil NULL ';
	keywords += 'super self copy ';
	keywords += 'break case catch class const copy __finally __exception __try ';
	keywords += 'const_cast continue private public protected __declspec ';
	keywords += 'default delete deprecated dllexport dllimport do dynamic_cast ';
	keywords += 'else enum explicit extern if for friend goto inline ';
	keywords += 'mutable naked namespace new noinline noreturn nothrow ';
	keywords += 'register reinterpret_cast return retain selectany ';
	keywords += 'sizeof static static_cast struct switch template this ';
	keywords += 'thread throw true false try typedef typeid typename union ';
	keywords += 'using uuid virtual volatile whcar_t while ';
	keywords += 'nonnull nullable null_unspecified ';
	keywords += '_Nullable _Nonnull _Null_unspecified ';
	keywords += '__nullable __nonnull __null_unspecified ';
	keywords += '__kindof in inout out ';
	keywords += 'bycopy byref'
	
	var modifiers = 'NS_ASSUME_NONNULL_BEGIN NS_ASSUME_NONNULL_END'
	
	var otherKeywords = 'IBAction IBOutlet IBOutletCollection IBInspectable IBDesignable ';
	otherKeywords = ' Protocol';
	
	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comment' },		// one line comments
		{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comment' },		// multiline comments
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },		// double quoted strings
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },		// single quoted strings
		{ regex: new RegExp('^ *#.*', 'gm'),				css: 'preprocessor' },		// preprocessor
		{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'datatypes' },		// datatypes
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' },		// keyword
		{ regex: new RegExp('\\bNS\\w+\\b', 'g'),			css: 'keyword' },		// keyword
		{ regex: new RegExp('\\bUI\\w+\\b', 'g'),			css: 'keyword' },		// keyword
		{ regex: new RegExp('@\\w+\\b', 'g'),				css: 'keyword' },		// keyword
		{ regex: new RegExp('@"(?:\\.|(\\\\\\")|[^\\""\\n])*"', 'g'),	css: 'string' }			// objc string		
		];
	
}

SyntaxHighlighter.brushes.ObjC.prototype = new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.ObjC.aliases = ['objc', 'obj-c'];
