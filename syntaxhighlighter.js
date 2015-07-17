( function($) {
	var shortcodes = window.syntaxHLcodes || 'sourcecode',
		regex = new RegExp( '(?:<pre>\\s*)?(\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\2\\])(?:\\s*<\\/pre>)?', 'gi' );

	window.syntaxHLescape = {};

	if ( typeof $ === 'undefined' ) {
		return;
	}

	$( document ).on( 'afterPreWpautop.syntaxhighlighter', function( event, obj ) {
		if ( obj.data && obj.data.indexOf( '[' ) !== -1 ) {
			obj.data = obj.data.replace( regex, function( match, shortcode ) {
					return '\n' + shortcode.replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' ).replace( /&amp;/g, '&' ) + '\n';
				}
			);
		}
	}).on( 'beforeWpautop.syntaxhighlighter', function( event, obj ) {
		if ( obj.data && obj.data.indexOf( '[' ) !== -1 ) {
			obj.data = obj.data.replace( regex, '<pre>$1</pre>' );
		}
	}).ready( function() {
		$( '.wp-editor-wrap.html-active' ).each( function( i, element ) {
			var id = $( element ).find( 'textarea.wp-editor-area' ).attr( 'id' );

			if ( id ) {
				window.syntaxHLescape[id] = true;
			}
		});
	});
}( window.jQuery ));
