/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/SyntaxHighlighter
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/SyntaxHighlighter/donate.html
 *
 * @version
 * 3.0.83 (Wed, 16 Apr 2014 03:56:09 GMT)
 *
 * @copyright
 * Copyright (C) 2004-2013 Alex Gorbatchev.
 *
 * @license
 * Dual licensed under the MIT and GPL licenses.
 */
(function () {
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush() {
		var keywords =
			'bool break byte case chan complex128 complex64 const continue default defer else ' +
			'fallthrough float32 float64 for func go goto if import int int16 int32 int64 int8 ' +
			'interface map package range return rune select string struct switch type uint ' +
			'uint16 uint32 uint64 uint8 uintptr var';
		var funcs =
			'append cap close complex copy imag len make new panic print println real recover delete';
		var special = 'true false iota nil';

		this.regexList = [
			{
				regex: SyntaxHighlighter.regexLib.singleLineCComments,
				css: 'comments',
			}, // one line comments
			{ regex: /\/\*([^\*][\s\S]*?)?\*\//gm, css: 'comments' }, // multiline comments
			{ regex: /\/\*(?!\*\/)\*[\s\S]*?\*\//gm, css: 'preprocessor' }, // documentation comments
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string' }, // strings
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString, css: 'string' }, // strings
			{ regex: XRegExp('`([^\\\\`]|\\\\.)*`', 'gs'), css: 'string' }, // strings
			{ regex: /\b([\d]+(\.[\d]+)?|0x[a-f0-9]+)\b/gi, css: 'value' }, // numbers
			{ regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'keyword' }, // keywords
			{ regex: new RegExp(this.getKeywords(funcs), 'gmi'), css: 'functions' }, // built-in functions
			{ regex: new RegExp(this.getKeywords(special), 'gm'), css: 'color1' }, // literals
		];

		this.forHtmlScript({
			left: /(&lt;|<)%[@!=]?/g,
			right: /%(&gt;|>)/g,
		});
	}

	Brush.prototype = new SyntaxHighlighter.Highlighter();
	Brush.aliases = ['go', 'golang'];

	SyntaxHighlighter.brushes.Go = Brush;

	// CommonJS
	typeof exports != 'undefined' ? (exports.Brush = Brush) : null;
})();
