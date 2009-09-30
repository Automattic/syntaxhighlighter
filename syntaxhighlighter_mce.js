/*
 * SyntaxHighlighter shortcode plugin
 * by Andrew Ozz of Automattic
 */

// Avoid JS errors
if ( typeof syntaxHLcodes == 'undefined' ) {
	var syntaxHLcodes = 'sourcecode';
}

(function() {
	tinymce.create('tinymce.plugins.SyntaxHighlighterPlugin', {

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

		// Private methods
		_visualToHtml : function(content) {
			content = tinymce.trim(content);	
			// 2 <br> get converted to \n\n and are needed to preserve the next <p>
			content = content.replace(new RegExp('(<pre>\\s*)?(\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\3\\])(\\s*<\\/pre>)?', 'gi'),
			function(a) {
				a = a.replace( /<br \/>([\t ])/g, '<br \/><%%KEEPWHITESPACE%%>$1' );
				return a + '<br /><br />';
			});
			content = content.replace(/<\/pre>(<br \/><br \/>)?<pre>/gi, '\n');
			return content;
		},

		_htmlToVisual : function(content) {
			content = tinymce.trim(content);

			content = content.replace(new RegExp('(<p>\\s*)?(<pre>\\s*)?(\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]*?\\[\\/\\4\\])(\\s*<\\/pre>)?(\\s*<\\/p>)?', 'gi'), '<pre>$3</pre>');
			content = content.replace(/<\/pre><pre>/gi,	'\n');

			// Remove anonymous, empty paragraphs.
			content = content.replace(/<p>(\s|&nbsp;)*<\/p>/mg, '');

			// Look for <p> <br> in the [tag]s, replace with <br />
			content = content.replace(new RegExp('\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi'),
			function(a) {
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

	content = content.replace(new RegExp('\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi'),
	function(a) {
		return a.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&').replace(/<%%KEEPWHITESPACE%%>/g, '');
	});

	return content;
}

function wpautop2(content) {
	// js htmlspecialchars
	content = content.replace(new RegExp('\\[(' + syntaxHLcodes + ')[^\\]]*\\][\\s\\S]+?\\[\\/\\1\\]', 'gi'),
	function(a) {
		return a.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	});

	return this._wpautop(content);
}

switchEditors._pre_wpautop = switchEditors.pre_wpautop;
switchEditors._wpautop = switchEditors.wpautop;
switchEditors.pre_wpautop = pre_wpautop2;
switchEditors.wpautop = wpautop2;