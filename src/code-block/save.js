/**
 * Internal dependencies
 */
import { escape } from '../utils';

export default function save( { attributes } ) {
	return (
		<pre>{ escape( attributes.content ) }</pre>
	);
}
