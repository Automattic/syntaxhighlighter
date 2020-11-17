
const parseHighlightLinesAttribute = value => {
	if ( ! value ) {
		return [];
	}
	return value.replace( /\s/, '' ).split( ',' ).map( lines => {
		const [ start, end ] = lines.split( '-' );
		if ( ! start.length ) {
			return;
		}

		return { start: +start, size: ( +end || +start ) - start + 1 };
	} ).filter( l => !! l );
};

const serializeLine = ( { start, size } ) => {
	if ( size < 0 ) {
		start += size;
		size = -size + 1;
	}
	return size > 1 ? `${ start }-${ start + size - 1 }` : start;
};

const serializeLines = lines => lines.map( serializeLine ).join( ',' );

export const useHighlightLinesAttribute = ( { attributes, setAttributes } ) => {
	const lines = parseHighlightLinesAttribute( attributes.highlightLines );
	return {
		lines: [ ...lines ],
		add( line ) {
			const highlightLines = serializeLines( [ ...lines, line ] );
			setAttributes( { highlightLines } );
		},
		remove( start ) {
			const next = lines.filter( line => line.start !== start );
			setAttributes( { highlightLines: serializeLines( next ) } );
		},
	};
};
