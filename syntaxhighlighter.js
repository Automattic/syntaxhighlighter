( function($) {
	var shortcodes = window.syntaxHLcodes || 'sourcecode',
		regex = new RegExp( '(?:<pre>\\s*)?(\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\2\\])(?:\\s*<\\/pre>)?', 'gi' );

	window.syntaxHLescape = {};

	var tagsToPreserve = [
		[ 'p', 'wp-p' ],
		[ 'br', 'wp-br' ],
	];

	function replaceTag( code, from, to ) {
		var regex = new RegExp( `<(\/?)${ from }([>\\s\/]+)`, 'gi' );
		return code.replace( regex, `<$1${ to }$2` );
	}

	function replaceTagsToPreserve( code, action ) {
		var newCode = code,
			indexReplaced,
			indexReplacement;

		if ( action === 'preserve' ) {
			indexReplaced = 0;
			indexReplacement = 1;
		} else {
			indexReplaced = 1;
			indexReplacement = 0;
		}

		tagsToPreserve.forEach( function( tags ) {
			newCode = replaceTag( newCode, tags[ indexReplaced ], tags[ indexReplacement ] );
		} );

		return newCode;
	}

	function preserveTags( code ) {
		return replaceTagsToPreserve( code, 'preserve' );
	}

	function restoreTags( code ) {
		return replaceTagsToPreserve( code, 'restore' );
	}

	function unescapeTags( code ) {
		return code.replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' ).replace( /&amp;/g, '&' );
	}

	if ( typeof $ === 'undefined' ) {
		return;
	}

	$( document ).on( 'afterPreWpautop.syntaxhighlighter', function( event, obj ) {
		if ( obj.data && obj.data.indexOf( '[' ) !== -1 ) {
			obj.data = obj.data.replace( regex, function( match, shortcode ) {
					return '\n' + restoreTags( unescapeTags( shortcode ) ) + '\n';
				}
			);
		}
	}).on( 'afterWpautop.syntaxhighlighter', function( event, obj ) {
		if ( obj.data && obj.data.indexOf( '[' ) !== -1 ) {
			obj.data = preserveTags( obj.unfiltered.replace( regex, '<pre>$1</pre>' ) );
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
