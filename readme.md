## SyntaxHighlighter Evolved

Easily post syntax-highlighted code to your WordPress site without having to modify the code at all. As seen on WordPress.com.

## Development On The Next Version

A major rewrite is currently occurring in the [4.0 branch](https://github.com/Viper007Bond/syntaxhighlighter/tree/4.0). Go there if you want to see the current work in progress code.

## Block Development Workflow

When making changes in the `blocks/` folder,

* Run `npm run dev` in a terminal to automatically re-transpile `blocks/index.min.js` when source files change.
* Run `npm run build` to transpile _and_ minify `blocks/index.min.js` for distribution, as well as automatically update the `locatlizations/_syntaxhighlighter-template.po`.
