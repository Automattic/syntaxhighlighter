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
; (function ()
{
    // CommonJS
    SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined' ? require('shCore').SyntaxHighlighter : null);

    function Brush()
    {
        var keywords = 'as as? break class continue do else false for fun if in !in interface ' +
            'is !is null object package return super this throw true ' +
            'try typealias typeof val var when while by catch constructor ' +
            'delegate dynamic field file finally get import init param property ' +
            'receiver set setparam value where actual abstract annotation companion ' +
            'const crossinline data enum expect external final infix inline ' +
            'inner internal lateinit noinline open operator out override private ' +
            'protected public reified sealed suspend tailrec vararg field it ';

        this.regexList = [
            { regex: SyntaxHighlighter.regexLib.singleLineCComments, css: 'comments' },		// one line comments
            { regex: /\/\*([^\*][\s\S]*?)?\*\//gm, css: 'comments' },	 	// multiline comments
            { regex: /\/\*(?!\*\/)\*[\s\S]*?\*\//gm, css: 'preprocessor' },	// documentation comments
            { regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string' },		// strings
            { regex: SyntaxHighlighter.regexLib.singleQuotedString, css: 'string' },		// strings
            { regex: /\b([\d]+(\.[\d]+)?|0x[a-f0-9]+)\b/gi, css: 'value' },			// numbers
            { regex: /(?!\@interface\b)\@[\$\w]+\b/g, css: 'color1' },		// annotation @anno
            { regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'keyword' }		// kotlin keyword
        ];

        this.forHtmlScript({
            left: /(&lt;|<)%[@!=]?/g,
            right: /%(&gt;|>)/g
        });
    };

    Brush.prototype = new SyntaxHighlighter.Highlighter();
    Brush.aliases = ['kotlin'];

    SyntaxHighlighter.brushes.Kotlin = Brush;

    // CommonJS
    typeof (exports) != 'undefined' ? exports.Brush = Brush : null;
})();
