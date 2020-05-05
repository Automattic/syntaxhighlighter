( function( $ ) {
	const shortcodes = window.syntaxHLcodes || 'sourcecode',
		regex = new RegExp( '(?:<pre>\\s*)?(\\[(' + shortcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\2\\])(?:\\s*<\\/pre>)?', 'gi' );

	window.syntaxHLescape = {};

	if ( typeof $ === 'undefined' ) {
		return;
	}

	// Constants.
	const $DOC = $( document );
	const PRESERVE = 'PRESERVE';
	const RESTORE = 'RESTORE';

	// Tags that are removed by the core and their replacement to prevent being removed.
	const tagsToPreserve = [
		[ 'p', 'wp-p' ],
		[ 'br', 'wp-br' ],
	];

	function replaceTag( code, from, to ) {
		const tagRegex = new RegExp( `<(\/?)${ from }([>\\s\/]+)`, 'gi' );
		return code.replace( tagRegex, `<$1${ to }$2` );
	}

	function replaceTagsToPreserve( code, action ) {
		const indexReplaced = action === PRESERVE ? 0 : 1;
		const indexReplacement = action === PRESERVE ? 1 : 0;
		let newCode = code;

		tagsToPreserve.forEach( function( tags ) {
			newCode = replaceTag( newCode, tags[ indexReplaced ], tags[ indexReplacement ] );
		} );

		return newCode;
	}

	function preserveTags( code ) {
		return replaceTagsToPreserve( code, PRESERVE );
	}

	function restoreTags( code ) {
		return replaceTagsToPreserve( code, RESTORE );
	}

	function unescapeTags( code ) {
		return code.replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' ).replace( /&amp;/g, '&' );
	}

	const events = {
		afterPreWpautop: function( event, obj ) {
			if ( obj.data && obj.data.indexOf( '[' ) === -1 ) {
				return;
			}

			obj.data = obj.data.replace( regex, function( match, shortcode ) {
				return '\n' + restoreTags( unescapeTags( shortcode ) ) + '\n';
			} );
		},
		afterWpautop: function( event, obj ) {
			if ( obj.data && obj.data.indexOf( '[' ) === -1 ) {
				return;
			}

			const unfilteredCodes = obj.unfiltered.match( regex );
			let i = 0;

			obj.data = obj.data.replace( regex, function() {
				// Replace by the unfiltered code piece.
				const unfilteredCode = unfilteredCodes[ i++ ];
				return `<pre>${ preserveTags( unfilteredCode ) }</pre>`;
			} );
		},
		documentReady: function() {
			$( '.wp-editor-wrap.html-active' ).each( function( i, element ) {
				const id = $( element ).find( 'textarea.wp-editor-area' ).attr( 'id' );

				if ( id ) {
					window.syntaxHLescape[ id ] = true;
				}
			} );
		},
	};

	$DOC.on( 'afterPreWpautop.syntaxhighlighter', events.afterPreWpautop );
	$DOC.on( 'afterWpautop.syntaxhighlighter', events.afterWpautop );
	$DOC.ready( events.documentReady );
}( window.jQuery ) );
