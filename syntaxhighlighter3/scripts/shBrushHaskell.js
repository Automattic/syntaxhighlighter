/**
 * Haskell Brushes for SyntaxHighlighter 3.0
 */
(function () {
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush() {
		var keywords = 'as case of class data default deriving do forall foreign hiding ' +
			'if then else import instance let in mdo module newtype qualified type where';

		this.regexList = [
			{ regex: /{-#[\s\S]*?#-}/g,                                 css: 'preprocessor' },
			{ regex: /--.*/g,                                           css: 'comments' },      // one line comments
			{ regex: /{-(?!\$)[\s\S]*?-}/gm,                            css: 'comments' },      // multiline comments
			{ regex: /'.'/g,                                            css: 'string' },        // chars
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,     css: 'string' },        // strings
			{ regex: /(-|!|#|\$|%|&amp;|\*|\+|\/|&lt;|=|&gt;|\?|@|\^|\||~|:|\.|\\)+/g, css: 'keyword bold' },
			{ regex: /`[a-z][a-z0-9_']*`/g,                             css: 'keyword bold' },  // infix operators
			{ regex: /\b(\d+|0x[0-9a-f]+)\b/gi,                         css: 'value' },         // integer
			{ regex: /\b\d+(\.\d*)?([eE][+-]?\d+)?\b/gi,                css: 'value' },         // floating number
			{ regex: new RegExp(this.getKeywords(keywords), 'gm'),      css: 'keyword bold' }
		];

		this.forHtmlScript({
			left	: /(&lt;|<)%[@!=]?/g, 
			right	: /%(&gt;|>)/g 
		});
	}

	Brush.prototype = new SyntaxHighlighter.Highlighter();
	Brush.aliases   = ['haskell'];

	SyntaxHighlighter.brushes.Haskell = Brush;

	// CommonJS
	typeof exports != 'undefined' ? (exports.Brush = Brush) : null;
})();
