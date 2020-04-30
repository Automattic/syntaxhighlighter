/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/SyntaxHighlighter
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/SyntaxHighlighter/donate.html
 *
 * @version
 * 3.0.83 (Wed, 16 Apr 2014 03:56:09 GMT)
 *
 * @copyright
 * Copyright (C) 2004-2013 Alex Gorbatchev.
 *
 * @license
 * Dual licensed under the MIT and GPL licenses.
 *
 * Original brush carlynorama/wp-syntaxhighlighter-arduino updated April 2020 by https://siytek.com
 */
;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{

	var datatypes =	'boolean char byte int long float double void unsigned volatile word string static const';

	var keywords =	'setup loop if else for switch case default while do break continue return';

	var functions =	'pinMode digitalWrite digitalRead analogRead analogWrite shiftOut pulseIn ' +
			'millis micros delay delayMicroseconds min max abs constrain ' +
			'map pow sq sqrt sin cos tan randomSeed random ' +
			'sizeof lowByte highByte bitRead bitWrite bitSet bitClear bit tone noTone' +
			'attachInterrupt detachInterrupt interrupts noInterrupts ' +
			'Serial\\.begin Serial\\.available Serial\\.read Serial\\.flush ' +
			'Serial\\.print Serial\\.println Serial\\.write ';

	var constants = 'HIGH LOW INPUT OUTPUT true false CHANGE RISING FALLING';


	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comments' }			// one line comments
		,{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comments' }			// multiline comments
		,{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' }			// strings
		,{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' }			// strings
		,{ regex: /^ *#(.)+?\b/gm,									css: 'preprocessor' }		// preprocessor directives
		,{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'color1 bold' } 		// datatypes
		,{ regex: new RegExp(this.getKeywords(functions), 'gm'),		css: 'functions' } 	// functions
		,{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword bold' } 		// control flow
		,{ regex: new RegExp(this.getKeywords(constants), 'gm'),		css: 'constants bold' } 	// predefined constants
		,{ regex: /\b(\d*\.\d+([Ee]-?\d{1,3})?)|(\d+[Ee]-?\d{1,3})\b/gm,	css: 'constants'} // numeric constants (floating point)
		,{ regex: /\b\d+[uU]?[lL]?\b/gm,								css: 'constants'} 	// numeric constants (decimal)
		,{ regex: /\b0x[0-9A-Fa-f]+[uU]?[lL]?\b/gm,					css: 'constants'} 	// numeric constants (hexidecimal)
		,{ regex: /\bB[01]{1,8}\b/gm,								css: 'constants'} 	// numeric constants (binary)
		,{ regex: /\+|\-|\*|\/|\%|!|\||\&amp;|=|\?|\^|~/gm, 			css: 'plain bold' }		// operators
		];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['arduino', 'arduinolite'];

	SyntaxHighlighter.brushes.Arduino = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
