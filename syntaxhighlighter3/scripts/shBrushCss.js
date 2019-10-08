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
 */
;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		function getKeywordsCSS(str)
		{
			return '\\b([a-z_]|)' + str.replace(/ /g, '(?=:)\\b|\\b([a-z_\\*]|\\*|)') + '(?=:)\\b';
		};
	
		function getValuesCSS(str)
		{
			return '\\b' + str.replace(/ /g, '(?!-)(?!:)\\b|\\b()') + '\:\\b';
		};

		var keywords =	'align-content align-items align-self alignement-adjust all anchor-point animation animation-delay animation-direction ' + 
						'animation-duration animation-fill-mode animation-iteration-count animation-name animation-play-state animation-timing-function ' +
						'appearance azimuth backface-visibility background background-attachment background-clip background-color background-image ' +
						'background-origin background-position background-repeat background-size baseline-shift binding bleed bookmark-label bookmark-level ' +
						'bookmark-state bookmark-target border border-bottom border-bottom-color border-bottom-left-radius border-bottom-right-radius ' +
						'border-bottom-style border-bottom-width border-collapse border-color border-image border-image-outset border-image-repeat ' +
						'border-image-slice border-image-source border-image-width border-left border-left-color border-left-style border-left-width ' +
						'border-radius border-right border-right-color border-right-style border-right-width border-spacing border-style border-top ' +
						'border-top-color border-top-left-radius border-top-right-radius border-top-style border-top-width border-width bottom ' +
						'box-decoration-break box-shadow box-sizing break-after break-before break-inside caption-side chains clear clip clip-path ' +
						'clip-rule color color-interpolation-filters color-profile column-count column-fill column-gap column-rule column-rule-color ' +
						'column-rule-style column-rule-width column-span column-width columns contain content counter-increment counter-reset crop ' +
						'cue cue-after cue-before cursor direction display dominant-baseline drop-initial-after-adjust drop-initial-after-align ' +
						'drop-initial-before-adjust drop-initial-before-align drop-initial-size drop-initial-value elevation empty-cells filter flex ' +
						'flex-basis flex-direction flex-flow flex-grow flex-shrink flex-wrap float float-offset flood-color flood-opacity font ' +
						'font-family font-feature-settings font-kerning font-language-override font-size font-size-adjust font-stretch font-style ' +
						'font-synthesis font-variant font-variant-alternates font-variant-caps font-variant-east-asian font-variant-ligatures ' +
						'font-variant-numeric font-variant-position font-weight grid grid-area grid-auto-columns grid-auto-flow grid-auto-position ' +
						'grid-auto-rows grid-column grid-column-end grid-column-start grid-row grid-row-end grid-row-start grid-template grid-template-areas ' +
						'grid-template-columns grid-template-rows hanging-punctuation height hyphens icon image-orientation image-resolution ' +
						'ime-mode inline-box-align justify-content left letter-spacing lighting-color line-break line-height line-stacking line-stacking-ruby ' +
						'line-stacking-shift line-stacking-strategy list-style list-style-image list-style-position list-style-type margin margin-bottom ' +
						'margin-left margin-right margin-top marker-offset marks mask mask-box-image mask-box-image-outset mask-box-image-repeat ' +
						'mask-box-image-slice mask-box-image-source mask-box-image-width mask-clip mask-image mask-origin mask-position mask-repeat ' +
						'mask-size mask-source-type mask-type max-height max-lines max-width min-height min-width move-to nav-down nav-index nav-left ' +
						'nav-right nav-up object-fit object-position opacity order orphans outline outline-color outline-offset outline-style outline-width ' +
						'overflow overflow-wrap overflow-x overflow-y padding padding-bottom padding-left padding-right padding-top page page-break-after ' +
						'page-break-before page-break-inside page-policy pause pause-after pause-before perspective perspective-origin pitch pitch-range ' +
						'play-during position presentation-level punctuation-trim quotes rendering-intent resize rest rest-after rest-before richness ' +
						'right rotation rotation-point ruby-align ruby-overhang ruby-position ruby-span size speak speak-as speak-header speak-numeral ' +
						'speak-punctuation speech-rate stress string-set tab-size table-layout target target-name target-new target-position text-align ' +
						'text-align-last text-combine-horizontal text-decoration text-decoration-color text-decoration-line text-decoration-skip ' +
						'text-decoration-style text-emphasis text-emphasis-color text-emphasis-position text-emphasis-style text-height text-indent ' +
						'text-justify text-orientation text-outline text-overflow text-shadow text-space-collapse text-transform text-underline-position ' +
						'text-wrap top transform transform-origin transform-style transition transition-delay transition-durations transition-property ' +
						'transition-timing-function unicode-bidi vertical-align visibility voice-balance voice-duration voice-family voice-pitch ' +
						'voice-range voice-rate voice-stress voice-volume volume white-space widows width word-break word-spacing word-wrap ' +
						'writing-mode z-index';

		var values =	'above absolute all always aqua armenian attr aural auto avoid baseline behind below bidi-override black blink block blue bold bolder '+
						'border-box both bottom braille capitalize caption center center-left center-right circle close-quote code collapse compact condensed '+
						'continuous content-box counter counters cover crop cross crosshair cursive dashed decimal decimal-leading-zero default digits disc dotted double '+
						'embed embossed e-resize expanded extra-condensed extra-expanded fantasy far-left far-right fast faster fixed flex format fuchsia '+
						'gray green groove handheld hebrew help hidden hide high higher icon infinite inherit inline-block inline-table inline inset inside invert italic '+
						'justify landscape large larger left-side left leftwards level lighter lime linear linear-gradient line-through list-item local loud lower-alpha '+
						'lowercase lower-greek lower-latin lower-roman lower low ltr marker maroon medium message-box middle mix move narrower '+
						'navy ne-resize no-close-quote none no-open-quote no-repeat normal nowrap n-resize nw-resize oblique olive once open-quote outset '+
						'outside overline pointer portrait pre print projection purple red relative repeat repeat-x repeat-y rgb rgba ridge right right-side '+
						'rightwards rotate row rtl run-in scale screen scroll semi-condensed semi-expanded separate se-resize show silent silver skewX skewY slower slow '+
						'small small-caps small-caption smaller space-around soft solid speech spell-out square s-resize static status-bar sub super sw-resize '+
						'table table-caption table-cell table-column table-column-group table-footer-group table-header-group table-row table-row-group teal '+
						'text-bottom text-top thick thin top translate translateX translateY transparent tty tv ultra-condensed ultra-expanded underline upper-alpha uppercase upper-latin '+
						'upper-roman url visible wait webkit-box white wider wrap w-resize x-fast x-high x-large x-loud x-low x-slow x-small x-soft xx-large xx-small yellow';

		var fonts =		'[mM]onospace [tT]ahoma [vV]erdana [aA]rial [hH]elvetica [sS]ans-serif [sS]erif [cC]ourier mono sans serif';
	
		this.regexList = [
			{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comments' },	// multiline comments
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },	// double quoted strings
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },	// single quoted strings
			{ regex: /\#[a-fA-F0-9]{3,6}/g,								css: 'value' },		// html colors
			{ regex: /(-?\d+)(\.\d+)?(px|em|pt|\:|\%|)/g,				css: 'value' },		// sizes
			{ regex: /!important/g,										css: 'color3' },	// !important
			{ regex: new RegExp(getKeywordsCSS(keywords), 'gm'),		css: 'keyword' },	// keywords
			{ regex: new RegExp(getValuesCSS(values), 'g'),				css: 'value' },		// values
			{ regex: new RegExp(this.getKeywords(fonts), 'g'),			css: 'color1' }		// fonts
			];

		this.forHtmlScript({ 
			left: /(&lt;|<)\s*style.*?(&gt;|>)/gi, 
			right: /(&lt;|<)\/\s*style\s*(&gt;|>)/gi 
			});
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['css'];

	SyntaxHighlighter.brushes.CSS = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
