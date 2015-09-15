/**
 * Wordpress SyntaxHighlighter brush for Swift 2.0
 * By Dal Rupnik, unifiedsense.com
 *
 * Copyright (C) 2015 Dal Rupnik
 * 
 * Source for Lexical Structure:
 * https://developer.apple.com/library/prerelease/ios/documentation/Swift/Conceptual/Swift_Programming_Language/LexicalStructure.html
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
	
	var datatypes =	'Array Bool Character Dictionary Double Float Int Int16 Int32 Int64 Int8 Range Set String';
	datatypes += ' UInt UInt16 UInt32 UInt64 UInt8 Unmanaged UnsafeBufferPointer UnsafePointer Optional'
	datatypes += ' AnyObject BooleanType CollectionType Comparable ErrorType Equatable Hashable Indexable'
	datatypes += ' IntegerType OptionSetType SequenceType UnsignedIntegerType'
	
	var declarations = 'class deinit enum extension func import init inout internal let operator private protocol public static struct subscript typealias var';
	
	var statements = 'break case continue default defer do else fallthrough for guard if in repeat return switch where while.';
	
	var expressions = 'as catch dynamicType false is nil rethrows super self Self throw throws true try __COLUMN__ __FILE__ __FUNCTION__  __LINE__'
	
	var specificContexts = 'associativity convenience dynamic didSet final get infix indirect lazy left mutating none nonmutating optional override postfix precedence prefix Protocol required right set Type unowned weak willSet';
	
	var punctuation = '( ) { } [ ] . , : ; = @ # & ( -> ` ? ! _';
	
	var otherKeywords = 'IBAction IBOutlet Selector @availability @objc';
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
