// Swift brush based on contributions by Alex Gorbatchev
// https://github.com/syntaxhighlighter/brush-swift/blob/master/brush.js

;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		function getKeywordsPrependedBy(keywords, by) {
			return '(?:' + keywords.replace(/^\s+|\s+$/g, '').replace(/\s+/g, '|' + by + '\\b').replace(/^/, by + '\\b') + ')\\b';
		}

		function stringAdd(match, regexInfo) {
			var str = match[0],
				result = [],
				pos = 0,
				matchStart = 0,
				level = 0;

			while (pos < str.length - 1) {
				if (level === 0) {
					if (str.substr(pos, 2) === "\\(") {
						result.push(new SyntaxHighlighter.Match(str.substring(matchStart, pos + 2), matchStart + match.index, regexInfo.css));
						level++;
						pos += 2;
					} else {
						pos++;
					}
				} else {
					if (str[pos] === "(") {
						level++;
					}
					if (str[pos] === ")") {
						level--;
						if (level === 0) {
							matchStart = pos;
						}
					}
					pos++;
				}
			}
			if (level === 0) {
				result.push(new SyntaxHighlighter.Match(str.substring(matchStart, str.length), matchStart + match.index, regexInfo.css));
			}

			return result;
		};

		// "Swift-native types" are all the protocols, classes, structs, enums, funcs, vars, and typealiases built into the language
		var swiftTypes = 'AbsoluteValuable Any AnyClass Array ArrayBound ArrayBuffer ArrayBufferType ' +
			'ArrayLiteralConvertible ArrayType AutoreleasingUnsafePointer BidirectionalIndex Bit ' +
			'BitwiseOperations Bool C CBool CChar CChar16 CChar32 CConstPointer CConstVoidPointer ' +
			'CDouble CFloat CInt CLong CLongLong CMutablePointer CMutableVoidPointer COpaquePointer ' +
			'CShort CSignedChar CString CUnsignedChar CUnsignedInt CUnsignedLong CUnsignedLongLong ' +
			'CUnsignedShort CVaListPointer CVarArg CWideChar Character CharacterLiteralConvertible ' +
			'Collection CollectionOfOne Comparable ContiguousArray ContiguousArrayBuffer DebugPrintable ' +
			'Dictionary DictionaryGenerator DictionaryIndex DictionaryLiteralConvertible Double ' +
			'EmptyCollection EmptyGenerator EnumerateGenerator Equatable ExtendedGraphemeClusterLiteralConvertible ' +
			'ExtendedGraphemeClusterType ExtensibleCollection FilterCollectionView FilterCollectionViewIndex ' +
			'FilterGenerator FilterSequenceView Float Float32 Float64 Float80 FloatLiteralConvertible ' +
			'FloatLiteralType FloatingPointClassification FloatingPointNumber ForwardIndex Generator ' +
			'GeneratorOf GeneratorOfOne GeneratorSequence Hashable HeapBuffer HeapBufferStorage ' +
			'HeapBufferStorageBase ImplicitlyUnwrappedOptional IndexingGenerator Int Int16 Int32 Int64 ' +
			'Int8 IntEncoder IntMax Integer IntegerArithmetic IntegerLiteralConvertible IntegerLiteralType ' +
			'Less LifetimeManager LogicValue MapCollectionView MapSequenceGenerator MapSequenceView ' +
			'MaxBuiltinFloatType MaxBuiltinIntegerType Mirror MirrorDisposition MutableCollection ' +
			'MutableSliceable ObjectIdentifier OnHeap Optional OutputStream PermutationGenerator ' +
			'Printable QuickLookObject RandomAccessIndex Range RangeGenerator RawByte RawOptionSet ' +
			'RawRepresentable Reflectable Repeat ReverseIndex ReverseRange ReverseRangeGenerator ' +
			'ReverseView Sequence SequenceOf SignedInteger SignedNumber Sink SinkOf Slice SliceBuffer ' +
			'Sliceable StaticString Streamable StridedRangeGenerator String StringElement ' +
			'StringInterpolationConvertible StringLiteralConvertible StringLiteralType UInt UInt16 ' +
			'UInt32 UInt64 UInt8 UIntMax UTF16 UTF32 UTF8 UWord UnicodeCodec UnicodeScalar Unmanaged ' +
			'UnsafeArray UnsafePointer UnsignedInteger Void Word Zip2 ZipGenerator2 abs advance alignof ' +
			'alignofValue assert bridgeFromObjectiveC bridgeFromObjectiveCUnconditional bridgeToObjectiveC ' +
			'bridgeToObjectiveCUnconditional c contains count countElements countLeadingZeros debugPrint ' +
			'debugPrintln distance dropFirst dropLast dump encodeBitsAsWords enumerate equal false filter ' +
			'find getBridgedObjectiveCType getVaList indices insertionSort isBridgedToObjectiveC ' +
			'isBridgedVerbatimToObjectiveC isUniquelyReferenced join lexicographicalCompare map max ' +
			'maxElement min minElement nil numericCast partition posix print println quickSort reduce ' +
			'reflect reinterpretCast reverse roundUpToAlignment sizeof sizeofValue sort split startsWith ' +
			'strideof strideofValue swap swift toString transcode true underestimateCount unsafeReflect ' +
			'withExtendedLifetime withObjectAtPlusZero withUnsafePointer withUnsafePointerToObject ' +
			'ßwithUnsafePointers withVaList ' +
			'fatalError assert assertionFailure precondition preconditionFailure ' +
			'Set AnyPublisher AnyCancellable sink store compactMap flatMap ObservableObject ';

		var keywords = 'as break catch case class continue default deinit do dynamicType else enum ' +
			'extension fallthrough for forEach func if import in init is let new protocol ' +
			'return self Self static struct subscript super switch Type typealias ' +
			'var where while __COLUMN__ __FILE__ __FUNCTION__ __LINE__ associativity ' +
			'didSet get infix inout left mutating none nonmutating operator override required ' +
			'postfix precedence prefix right set try unowned unowned(safe) unowned(unsafe) weak willSet ' +
			'guard convenience defer dynamic final fileprivate private internal open public repeat throw throws rethrows unowned lazy ' +
			'isolated nonisolated safe unsafe some optional assignment indirect ' +
			// Precedence group
			'precedencegroup higherThan lowerThan ' +
			// Swift concurrency
			'actor associatedtype async await ';

		var attributes = 'assignment class_protocol exported noreturn escaping NSCopying NSManaged objc nonobjc optional required auto_closure IBAction IBDesignable IBInspectable IBOutlet infix prefix postfix unknown available ' +
			'resultBuilder propertyWrapper autoclosure convention ' +
			// SwiftUI property wrappers
			'Binding State StateObject ObservedObject EnvironmentObject ViewBuilder Environment ScaledMetric main ' +
			// Swift concurrency
			'MainActor Sendable globalActor ';

		var datatypes =	'char bool BOOL double float int long short id instancetype void ' +
			' Class IMP SEL _cmd';

		this.regexList = [
			// HTML entities
			{
				regex: new RegExp('\&[a-z]+;', 'gi'),
				css: 'plain'
			},
			// Single line comments
			{
				regex: SyntaxHighlighter.regexLib.singleLineCComments,
				css: 'comments'
			},
			// Multiline comments in Swift
			{
				regex: SyntaxHighlighter.regexLib.multiLineCComments,
				css: 'comments'
			},
			// Nested comments are supported up to 2 levels
			{
				regex: new RegExp(/(^|[^\\:])(?:\/\/.*|\/\*(?:[^/*]|\/(?!\*)|\*(?!\/)|\/\*(?:[^*]|\*(?!\/))*\*\/)*\*\/)/, 'gs'),
				css: 'comments'
			},
			// Multiline strings in Swift
			{
				regex: new RegExp(/"""(?:\\(?:#+\((?:[^()]|\([^()]*\))*\)|[^#])|[^\\])*?"""/, 'gm'),
				css: 'string'
			},
			// Types in Swift foundation
			{
				regex: new RegExp(this.getKeywords(datatypes), 'gm'),
				css: 'datatypes'
			},
			// Handle string interpolation in Swift
			{
				regex: SyntaxHighlighter.regexLib.doubleQuotedString,
				css: 'string',
				func: stringAdd
			},
			// Numeric literals in Swift
			{
				regex: new RegExp('\\b([\\d_]+(\\.[\\de_]+)?|0x[a-f0-9_]+(\\.[a-f0-9p_]+)?|0b[01_]+|0o[0-7_]+)\\b', 'gi'),
				css: 'value'
			},
			// Keywords in Swift
			{
				regex: new RegExp(this.getKeywords(keywords), 'gm'),
				css: 'keyword'
			},
			// Common declaration and type attributes in Swift
			{
				regex: new RegExp(getKeywordsPrependedBy(attributes, '@'), 'gm'),
				css: 'color1'
			},
			// Common native types in Swift
			{
				regex: new RegExp(this.getKeywords(swiftTypes), 'gm'),
				css: 'color2'
			},
			// UIKit/NS/Core Graphics/Core Animation/MapKit/XCFrameworks types
			{
				regex: new RegExp(/\b(?:UI|NS|CG|CA|MK|XC)[a-zA-Z0-9_]+\b/, 'g'),
				css: 'color2'
			},
			// Compiler directives
			{
				regex: new RegExp(/^ *#[a-zA-Z0-9_]+/, 'gm'),
				css: 'preprocessor'
			},
			// Any variables
			{
				regex: new RegExp('\\b([a-zA-Z_][a-zA-Z0-9_]*)\\b', 'gi'),
				css: 'variable'
			}
		];
	}

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['swift'];

	SyntaxHighlighter.brushes.Swift = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
