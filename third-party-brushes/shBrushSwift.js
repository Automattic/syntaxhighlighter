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
	datatypes += ' AnyObject? T T? Element Key Value ArraySlice ImplicitlyUnwrappedOptional Selector';
	
	var declarations = 'class deinit enum extension func import init inout internal let operator private protocol public static struct subscript typealias var';
	
	var statements = 'break case continue default defer do else fallthrough for guard if in repeat return switch where while.';
	
	var expressions = 'as catch dynamicType false is nil rethrows super self Self throw throws true try __COLUMN__ __FILE__ __FUNCTION__  __LINE__'
	
	var specificContexts = 'associativity convenience dynamic didSet final get infix indirect lazy left mutating none nonmutating optional override postfix precedence prefix Protocol required right set Type unowned weak willSet';
	
	var punctuation = '( ) { } [ ] . , : ; = @ # & ( -> ` ? ! _';
	
	var operators = '++ -- ~ + - << >> * / % &* & &+ &- | ^ ..< ... as? as! ?? < <= > >= == != === !== ~= && || ?: = *= /= %= += -= <<= >>= &= |= ^= &&= ||='
	
	var attributes = '@availability @autoclosure @noescape @noreturn @NSApplicationMain @NSCopying @NSManaged @objc';
	attributes += ' @UIApplicationMain';
	
	var otherKeywords = 'IBAction IBOutlet IBInspectable IBDesignable';
	
	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comment' },		// One line comments
		{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comment' },		// Multiline comments
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },		// Double quoted strings
		{ regex: new RegExp('^ *#.*', 'gm'),				css: 'preprocessor' },		// Preprocessor
		{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'datatypes' },		// Datatypes
		{ regex: new RegExp(this.getKeywords(declarations), 'gm'),	css: 'keyword' },		// Declarations
		{ regex: new RegExp(this.getKeywords(statements), 'gm'),	css: 'keyword' },		// Statements
		{ regex: new RegExp(this.getKeywords(expressions), 'gm'),	css: 'keyword' },		// Expressions
		{ regex: new RegExp(this.getKeywords(specificContexts), 'gm'),	css: 'keyword' },		// Specific Contexts
		//{ regex: new RegExp(this.getKeywords(punctuation), 'gm'),	css: 'constants' },		// Declarations
		//{ regex: new RegExp(this.getKeywords(operators), 'gm'),		css: 'constants' },		// Declarations
		{ regex: new RegExp(this.getKeywords(attributes), 'gm'),	css: 'keyword' },		// Attributes
		{ regex: new RegExp(this.getKeywords(otherKeywords), 'gm'),	css: 'keyword' },		// Other keywords
		{ regex: new RegExp('\\bNS\\w+\\b', 'g'),			css: 'datatypes' },		// Foundation classes
		{ regex: new RegExp('\\bUI\\w+\\b', 'g'),			css: 'datatypes' }, 		// UIKit classes
		{ regex: new RegExp('@\\w+\\b', 'g'),				css: 'keyword' }		// keyword
		];
}

SyntaxHighlighter.brushes.Swift.prototype = new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Swift.aliases = ['swift'];
