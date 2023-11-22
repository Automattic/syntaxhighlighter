/**
 * BLOCK: SyntaxHighlighter Evolved (syntaxhighlighter/code)
 */

import { registerBlockType } from '@wordpress/blocks';

import codeBlock from './code-block';

registerBlockType( 'syntaxhighlighter/code', codeBlock );
