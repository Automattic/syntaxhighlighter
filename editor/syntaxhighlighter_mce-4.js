/*
 * SyntaxHighlighter shortcode plugin
 * by Andrew Ozz of Automattic
 */
tinymce.PluginManager.add( 'syntaxhighlighter', function( editor ) {
	editor.on( 'BeforeSetContent', function( event ) {
		var shortcodes = window.syntaxHLcodes || 'sourcecode';

		event.content = event.content.replace( new RegExp( '(?:<p>\\s*)?(?:<pre>\\s*)?(\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\2\\])(?:\\s*<\\/pre>)?(?:\\s*<\\/p>)?', 'gi'),
			function( match, shortcode ) {
				return '<pre>' + shortcode.replace( /<br ?\/?>[\r\n]*/g, '<br />' ).replace( /<\/?p( [^>]*)?>[\r\n]*/g, '<br />' ) + '</pre>';
			}
		);
	});
});