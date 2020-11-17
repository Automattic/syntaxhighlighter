/**
 * External dependencies
 */
import { flow } from 'lodash';

/**
 * WordPress dependencies
 */
import { escapeEditableHTML } from '@wordpress/escape-html';

/**
 * Escapes ampersands, shortcodes, and links.
 *
 * @param {string} content The content of a code block.
 * @return {string} The given content with some characters escaped.
 */
export function escape( content ) {
	return flow(
		escapeEditableHTML,
	)( content || '' );
}
