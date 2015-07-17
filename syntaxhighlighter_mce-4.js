/*
 * SyntaxHighlighter shortcode plugin
 * by Andrew Ozz of Automattic
 */
tinymce.PluginManager.add( 'syntaxhighlighter', function( editor ) {
	editor.on( 'BeforeSetContent', function( event ) {
		var shortcodes = window.syntaxHLcodes || 'sourcecode',
			regex = new RegExp( '(?:<p>\\s*)?(?:<pre>\\s*)?(\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\2\\])(?:\\s*<\\/pre>)?(?:\\s*<\\/p>)?', 'gi' );

		if ( event.content && event.content.indexOf( '[' ) !== -1 ) {
			event.content = event.content.replace( regex, function( match, shortcode ) {
				shortcode = shortcode.replace( /\r/, '' );
				shortcode = shortcode.replace( /<br ?\/?>\n?/g, '\n' ).replace( /<\/?p( [^>]*)?>\n?/g, '\n' );

				if ( ! event.initial || ( window.syntaxHLescape && window.syntaxHLescape[ editor.id ] ) ) {
					shortcode = shortcode.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
				}

				return '<pre>' + shortcode + '</pre>';
			});
		}
	});
});
