SyntaxHighlighter.brushes.ts = function () {
	const keywords =
		"break case catch class continue " +
		"default delete do else enum export extends false  " +
		"for function if implements import in instanceof " +
		"interface let new null package private protected " +
		"static return super switch " +
		"this throw true try typeof var while with yield" +
		" any bool declare get module never number public readonly set string"; // TypeScript-specific, everything above is common with JavaScript

	this.regexList = [
		{
			regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,
			css: "string",
		},
		{
			regex: SyntaxHighlighter.regexLib.multiLineSingleQuotedString,
			css: "string",
		},
		{
			regex: SyntaxHighlighter.regexLib.singleLineCComments,
			css: "comments",
		},
		{
			regex: SyntaxHighlighter.regexLib.multiLineCComments,
			css: "comments",
		},
		{
			regex: new RegExp(this.getKeywords(keywords), "gm"),
			css: "keyword",
		},
	];

	this.forHtmlScript(SyntaxHighlighter.regexLib.scriptScriptTags);
};

SyntaxHighlighter.brushes.ts.prototype = new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.ts.aliases = ["ts", "typescript"];
