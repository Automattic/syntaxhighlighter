import { useState, useRef, useEffect } from '@wordpress/element';
import { useHighlightLinesAttribute } from './highlight.attributes';

export const HighlightLines = ( { attributes, setAttributes, isSelected } ) => {
	const lineHeight = 24;
	const topOffset = 14;

	const [ selecting, setSelecting ] = useState( false );
	const [ selection, setSelection ] = useState( null );
	const [ lineCount, setLineCount ] = useState( 0 );

	const linesRef = useRef();

	useEffect( ()=> {
		setLineCount( Math.floor( linesRef.current ? ( linesRef.current.offsetHeight - ( topOffset * 2 ) ) / lineHeight : 0 ) );
	}, [ linesRef.current && linesRef.current.offsetHeight, isSelected ] );

	const lineNumbers = [];
	for ( let line = 1; line <= lineCount; line++ ) {
		lineNumbers.push( <div key={ line }> { line }</div> );
	}

	const { lines, add, remove } = useHighlightLinesAttribute( { attributes, setAttributes } );

	function selectLines( e ) {
		const rect = e.target.getBoundingClientRect();
		const pos = e.clientY - rect.top;

		const line = Math.floor( ( pos - topOffset ) / lineHeight ) + 1;

		const start = selecting && selection.start ? selection.start : +line;
		const diff = +line - start;
		const size = selecting ? ( diff + ( diff > 0 ? 1 : 0 ) ) : 1;

		if ( ! line ) {
			return clearSelection();
		}

		setSelection( { start, size, isSelection: true } );
	}

	function clearSelection() {
		setSelecting( false );
		setSelection( null );
	}

	function startSelecting() {
		setSelecting( true );
	}

	function finishSelecting() {
		if ( getHighlightForLine( selection.start ) ) {
			remove( getHighlightForLine( selection.start ).start );
		} else {
			add( selection );
		}
		setSelecting( false );
	}

	function getHighlightForLine( line ) {
		return lines.find( ( { start, size, isSelection } ) => ! isSelection && start <= line && start + size - 1 >= line );
	}

	if ( selection && ! getHighlightForLine( selection.start ) ) {
		lines.push( selection );
	}

	const Line = ( line ) => {
		let { start, size, isSelection } = line;
		const isRemoving = selection && ! isSelection && line === getHighlightForLine( selection.start );

		if ( size < 0 ) {
			start += size;
			size = -size + 1;
		}

		return (
			<div
				className={ [ 'wp-block-syntaxhighlighter__line ', isSelection ? 'is-selection' : '', isRemoving ? 'is-removing' : '' ].join( ' ' ) }
				style={ {
					top: ( ( start - 1 ) * lineHeight ) + topOffset,
					height: size * lineHeight,
				} } /> );
	};

	return <div>
		{ isSelected && <div role="button" className="wp-block-syntaxhighlighter__lines"
			onMouseDown={ () => startSelecting() }
			onMouseUp={ () => finishSelecting() }
			onMouseMove={ selectLines }
			onMouseLeave={ clearSelection }
			ref={ linesRef }>
			{ lineNumbers }
		</div> }
		{ lines.map( line => <Line key={ line.start } { ...line } /> ) }
	</div>;
};
