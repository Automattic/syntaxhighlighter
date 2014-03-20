( function($) {
	var shortcodes = window.syntaxHLcodes || 'sourcecode';

	if ( typeof $ === 'undefined' ) {
		return;
	}

	$(document).on( 'afterPreWpautop.syntaxhighlighter', function( event, obj ) {
		obj.data = obj.data.replace( new RegExp( '(?:<pre>\\s*)?(\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\2\\])(?:\\s*<\\/pre>)?', 'gi' ),
			function( match, shortcode ) {
				return '\n' + shortcode.replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' ).replace( /&amp;/g, '&' ) + '\n';
			}
		);
	}).on( 'beforeWpautop.syntaxhighlighter', function( event, obj ) {
		obj.data = obj.data.replace( new RegExp( '\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi' ),
			function( match ) {
				return '<pre>' + match.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + '</pre>';
			}
		);
	});
}( window.jQuery ));