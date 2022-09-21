<?php

// Stubs for php-deno

namespace Deno\AST {
    /**
     * Parse a TypeScript (or similar) module. See ParseParams for options.
     */
    function parse_module(\Deno\AST\ParseParams $params): \Deno\AST\ParsedSource {}

    /**
     * ParseParams represent the arguments for Deno\AST\parse_module, which is used to
     * parse TypeScript.
     */
    class ParseParams {
        /**
         * The ES6 module specifier, must be a URL.
         * @var string
         */
        public $specifier;

        /**
         * The type of the module, specified as a mime-type such as application/typescript etc.
         * @var string
         */
        public $media_type;

        /**
         * The source code of the ES6 module.
         * @var string
         */
        public $text_info;

        public function __construct() {}
    }

    class ParsedSource {
        /**
         * Transpile the ASP to TypeScript, with the provided EmitOptions. Throws an exception or returns Deno\AST\TranspiledSource
         */
        public function transpile(\Deno\AST\EmitOptions $options): \Deno\AST\TranspiledSource {}
    }

    /**
     * TypeScript compiler options used when transpiling.
     */
    class EmitOptions {
        /**
         * The string module specifier to implicitly import JSX factories from when
         * transpiling JSX.
         * @var string
         */
        public $jsx_import_source;

        /**
         * Should a corresponding .map file be created for the output. This should be
         * false if inline_source_map is true. Defaults to `false`.
         * @var bool
         */
        public $source_map;

        /**
         * Should JSX be transformed or preserved.  Defaults to `true`.
         * @var bool
         */
        public $transform_jsx;

        /**
         * `true` if the program should use an implicit JSX import source/the "new"
         * JSX transforms.
         * @var bool
         */
        public $jsx_automatic;

        /**
         * When emitting a legacy decorator, also emit experimental decorator meta
         * data.  Defaults to `false`.
         * @var bool
         */
        public $emit_metadata;

        /**
         * Should import declarations be transformed to variable declarations using
         * a dynamic import. This is useful for import & export declaration support
         * in script contexts such as the Deno REPL.  Defaults to `false`.
         * @var bool
         */
        public $var_decl_imports;

        /**
         * If JSX is automatic, if it is in development mode, meaning that it should
         * import `jsx-dev-runtime` and transform JSX using `jsxDEV` import from the
         * JSX import source as well as provide additional debug information to the
         * JSX factory.
         * @var bool
         */
        public $jsx_development;

        /**
         * Should the source map be inlined in the emitted code file, or provided
         * as a separate file.  Defaults to `true`.
         * @var bool
         */
        public $inline_source_map;

        /**
         * Should the sources be inlined in the source map.  Defaults to `true`.
         * @var bool
         */
        public $inline_sources;

        /**
         * When transforming JSX, what value should be used for the JSX factory.
         * Defaults to `React.createElement`.
         * @var string
         */
        public $jsx_factory;

        /**
         * When transforming JSX, what value should be used for the JSX fragment
         * factory.  Defaults to `React.Fragment`.
         * @var string
         */
        public $jsx_fragment_factory;

        public function __construct() {}
    }

    /**
     * The transpiled code to TypeScript source code, this is the result of `Deno\AST\ParsedSource::transpile().
     */
    class TranspiledSource {
        /**
         * Transpiled text.
         * @var string
         */
        public $text;

        /**
         * Source map back to the original file.
         * @var string|null
         */
        public $source_map;
    }
}

namespace Deno\Core {
    /**
     * ModuleSource represents an ES6 module, including the source code and type. An ModuleSource should
     * be returned from your module loader passed to JsRuntime's RuntimeOptions::module_loader property.
     */
    class ModuleSource {
        /**
         * The resolved module URL, after things like 301 redrects etc.
         * @var string
         */
        public $module_url_found;

        /**
         * The specified module URL of the import.
         * @var string
         */
        public $module_url_specified;

        /**
         * The module's source code.
         * @var string
         */
        public $code;

        /**
         * The module type, can be "javascript" or "json".
         * @var string
         */
        public $module_type;

        public function __construct(string $code, string $module_type, string $module_url_specified, string $module_url_found) {}
    }

    /**
     * The options provided to the JsRuntime. Pass an instance of this class
     * to Deno\Core\JsRuntime.
     *
     */
    class RuntimeOptions {
        /**
         * The module loader accepts a callable which is responsible for loading
         * ES6 modules from a given name. See `Deno\Core\ModuleLoader` for methods that should be implemented.
         * @var Deno\Core\ModuleLoader
         */
        public $module_loader;

        /**
         * Prepare runtime to take snapshot of loaded code. The snapshot is determinstic and uses predictable random numbers.
         *
         * Currently can’t be used with startup_snapshot.
         * @var bool
         */
        public $will_snapshot;

        /**
         * Extensions allow you to add additional functionality via Deno "ops" to the JsRuntime. `extensions` takes an array of
         * Deno\Core\Extension instances. See Deno\Core\Extension for details on the PHP <=> JS functions bridge.
         * @var Deno\Core\Extension[]
         */
        public $extensions;

        /**
         * V8 snapshot that should be loaded on startup.
         *
         * Currently can’t be used with will_snapshot.
         * @var string
         */
        public $startup_snapshot;

        public function __construct() {}
    }

    /**
     * The JsRuntime is a wrapper around a V8 isolate. It can execute ES6 including ES6 modules. The JsRuntime
     * does not include any of the Deno.core.* ops, and does not provide implementations for web apis, such as
     * fetch(). Use JsRuntime if you want to provide low-level v8 isolates, and implement extensions for all
     * functionality such as local storage, remote requests etc.
     */
    class JsRuntime {
        public function __construct(\Deno\Core\RuntimeOptions $options) {}

        /**
         * Execute JavaSscript inside the V8 Isolate.
         *
         * This does not support top level await for Es6 imports. use `load_main_module`
         * to execute JavaScript in modules.
         */
        public function execute_script(string $name, string $source_code): mixed {}

        /**
         * Load an ES6 module as the main starting module.
         *
         * This function returns a module ID which should be passed to `mod_evaluate()`.
         *
         * @return int
         */
        public function load_main_module(string $specifier, ?string $code): int {}

        /**
         * Evaluate a given module ID. This will run all schyonous code in the module.
         * If there are pending Promises or async axtions, use `run_event_loop()` to
         * wait until all async actions complete.
         */
        public function mod_evaluate(int $id): mixed {}

        /**
         * Wait for the event loop to run all pending async actions.
         */
        public function run_event_loop(): mixed {}

        /**
         * Takes a snapshot. The isolate should have been created with will_snapshot set to true.
         *
         * @return string
         */
        public function snapshot(): mixed {}
    }

    /**
     * JsFile is a descriptor for JavaScript files that are loaded as
     * part of the Extension->js_files array. The `code` of `JsFile` is
     * executed when the JsRuntime is initiated.
     */
    class JsFile {
        /**
         * The code for the javascript file
         * @var string
         */
        public $code;

        /**
         * The filename for the JS file
         * @var string
         */
        public $filename;

        public function __construct(string $filename, string $code) {}
    }

    /**
     * Extension contains PHP functions (ops) and associated js files which are
     * exposed to JavaScript via the JsRuntime. PHP functions can be called from JavaScript
     * via `Deno.core.$name` where `$name` is the array key string from the `ops` property.
     *
     * It's common to provide `ops` and also more user-friendly accessible functions for those
     * `ops` via the `js_files` property.
     */
    class Extension {
        /**
         * The JS files that should be loaded into the V8 Isolate.
         * @var Deno\Core\JsFile[]
         */
        public $js_files;

        /**
         * The ops for the extension (bridged to PHP functions)
         * @var array<string, callable>
         */
        public $ops;

        public function __construct() {}
    }

    /**
     * The module loader interface (don't trust the docs, this is an interface not a class!)
     * Pass an instance of your class that implements `Deno\Core\ModuleLoader` to the `module_loader`
     * property of `Deno\Runtime\WorkerOptions` or `Deno\Core\RuntimeOptions`
     */
    class ModuleLoader {
        /**
         * The `resolve` method should take a module specifier and normalize it to a canonical URL.
         * @return string
         */
        public function resolve(string $_specifier, string $_referrer): string {}

        /**
         * The `load` method takes a module specifier and should return the contents for a module.
         * See `Deno\Core\ModuleSource` for the specifics.
         * @return \Deno\Core\ModuleSource
         */
        public function load(string $_specifier): ?\Deno\Core\ModuleSource {}
    }
}

namespace Deno\Runtime {
    class WorkerOptions {
    }

    /**
     * The Deno main worker. This includes a JsRuntime along with all the standard ops from Deno CLI,
     * such as Deno.core.* and the web APIs such as TextEncoder etc. Use the MainWorker if you want to
     * run programs that are written to run in Deno. The Deno provided ops such as `fetch()` uses it's own
     * TLS and request stack.
     */
    class MainWorker {
        public function __construct(string $main_module, \Deno\Runtime\WorkerOptions $options) {}

        public function execute_main_module(): mixed {}

        public function run_event_loop(): mixed {}
    }

    class BootstrapOptions {
    }
}
