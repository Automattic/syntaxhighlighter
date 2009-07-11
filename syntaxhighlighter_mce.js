/*
 * SyntaxHighlighter shortcode plugin
 * Based on v20090208 from WordPress.com
 * Andrew Ozz kicks ass
 * 
 * Whitespace fixes by Abel Braaksma (marked with "AB")
 * http://www.undermyhat.org/blog/2009/07/fix-for-leading-whitespace-bug-in-syntaxhighlighter-evolved-for-wordpress/
 */

(function() {
	tinymce.create('tinymce.plugins.SyntaxHighlighterPlugin', {
		// AB 20090709: 'magic' constants for use with leading whitespace bug
		__MAGIC_WHITESPACE : '{{__MAGIC_WHITESPACE__}}',
		__MAGIC_WHITESPACE_RE : new RegExp('\{\{__MAGIC_WHITESPACE__\}\}', 'ig'),
		
		init : function(ed, url) {
			var t = this;

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t._htmlToVisual(o.content);
			});
			
			ed.onPostProcess.add(function(ed, o) {
				if ( o.save ) {
					o.content = t._visualToHtml(o.content);
				}
			});
			
			// AB 20090709: fix for leading whitespace problem in TinyMCE
			ed.onSaveContent.add(function(ed, o) {
				o.content = t._fixWhitespaceFromHtml(o.content);
			});
			
			// AB 20090709: fix for leading whitespace problem in TinyMCE
			ed.onPreProcess.add(function(ed, o) {
				if(o.get)
					o.node.innerHTML = t._fixWhitespaceToHtml(o.node.innerHTML);
				else
					o.node.innerHTML = t._fixWhitespaceFromHtml(o.node.innerHTML);
			});
		},

		getInfo : function() {
			return {
				longname : 'SyntaxHighlighter Assister',
				author : 'Automattic',
				authorurl : 'http://wordpress.com/',
				infourl : 'http://wordpress.org/extend/plugins/syntaxhighlighter/',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// AB 20090709: fix for leading whitespace problem 
		// helper function when converting TO html
		_fixWhitespaceToHtml : function(content) {
			var result = '';
			var lastMatchIndex = 0;
			
			// expanded regex for matching "before tag[tag]code section[/tag]after tag"
			// note: "after tag" is not matched, will be added manually after the loop completes
			var re = new RegExp(
					'([\\s\\S]*?)' + 		// $1: anything before [tag]
					'(\\[(' + 				// $2: [
						syntaxHLcodes + 	// $3:   tag
					').*?\\])' +			//     ]
					'([\\s\\S]*?)' + 		// $4: anything inside tag
					'(\\[\\/\\3\\])',		// $5: [/tag]
					'ig');					// /i is necessary for matching the internal 
											//  <BR> in innerHTML on OP/IE, while FF
											// uses the more XHTML correct <br> internally
			
			// first match
			var re_matches = re.exec(content);

			// nothing found, nothing to replace, return content
			if (re_matches == null) {
				return content;
			}
			
			// go through all matches, meaning, go through each [tag]code section[/tag]
			// until no more matches are found
			while (re.lastIndex > 0) {
				lastMatchIndex = re.lastIndex;
				result += re_matches[1] + 
					re_matches[2] + 
					re_matches[4].replace(/(<br[^>]*>) /gi, '$1' + this.__MAGIC_WHITESPACE) + 
					re_matches[5];
				
				// next match
				re_matches = re.exec(content);
			}

			// add the rest of the content, which wasn't matched
			result += content.substring(lastMatchIndex);
			
			return result;
		},
		
		// AB 20090709: fix for leading whitespace problem
		// helper function when converting FROM html
		_fixWhitespaceFromHtml : function(content) {
			return content.replace(this.__MAGIC_WHITESPACE_RE, ' ');
		},

		// Private methods
		_visualToHtml : function(content) {
			content = tinymce.trim(content);	
			// 2 <br> get converted to \n\n and are needed to preserve the next <p>
			content = content.replace(new RegExp('(<pre>\\s*)?(\\[(' + syntaxHLcodes + ').*?\\][\\s\\S]*?\\[\\/\\3\\])(\\s*<\\/pre>)?', 'gi'), '$2<br /><br />');
			content = content.replace(/<\/pre>(<br \/><br \/>)?<pre>/gi, '\n');
			return content;
		},

		_htmlToVisual : function(content) {
			content = tinymce.trim(content);

			content = content.replace(new RegExp('(<p>\\s*)?(<pre>\\s*)?(\\[(' + syntaxHLcodes + ').*?\\][\\s\\S]*?\\[\\/\\4\\])(\\s*<\\/pre>)?(\\s*<\\/p>)?', 'gi'), '<pre>$3</pre>');
			content = content.replace(/<\/pre><pre>/gi,	'\n');

			// Remove anonymous, empty paragraphs.
			content = content.replace(/<p>(\s|&nbsp;)*<\/p>/mg, '');

			// Look for <p> <br> in the [tag]s, replace with <br />
			content = content.replace(new RegExp('\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi'), function(a) {
				return a.replace(/<br ?\/?>[\r\n]*/g, '<br />').replace(/<\/?p( [^>]*)?>[\r\n]*/g, '<br />');
			});

			return content;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('syntaxhighlighter', tinymce.plugins.SyntaxHighlighterPlugin);
})();

var syntaxHLlast = 0;
function pre_wpautop2(content) {
	var d = new Date(), time = d.getTime();

	if ( time - syntaxHLlast < 500 )
		return content;
	
	syntaxHLlast = time;

	content = content.replace(new RegExp('<pre>\\s*\\[(' + syntaxHLcodes + ')', 'gi'), '[$1');
	content = content.replace(new RegExp('\\[\\/(' + syntaxHLcodes + ')\\]\\s*<\\/pre>', 'gi'), '[/$1]');

	content = this._pre_wpautop(content);

	content = content.replace(new RegExp('\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi'), function(a) {
		return a.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
	});

	return content;
}

function wpautop2(content) {

	// js htmlspecialchars
	content = content.replace(new RegExp('\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi'), function(a) {
		return a.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	});

	return this._wpautop(content);
}

switchEditors._pre_wpautop = switchEditors.pre_wpautop;
switchEditors._wpautop = switchEditors.wpautop;
switchEditors.pre_wpautop = pre_wpautop2;
switchEditors.wpautop = wpautop2;