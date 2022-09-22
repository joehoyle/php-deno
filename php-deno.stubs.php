<?php

// Stubs for php-deno

namespace Deno\AST {
    /**
     * Parse a TypeScript (or similar) module. See ParseParams for options.
     */
    function parse_module(\Deno\AST\ParseParams $params): \Deno\AST\ParsedSource {}

    class ParsedSource {
        /**
         * Transpile the ASP to TypeScript, with the provided EmitOptions. Throws an exception or returns Deno\AST\TranspiledSource
         */
        public function transpile(\Deno\AST\EmitOptions $options): \Deno\AST\TranspiledSource {}
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

    /**
     * ParseParams represent the arguments for Deno\AST\parse_module, which is used to
     * parse TypeScript.
     */
    class ParseParams {
        /**
         * The type of the module, specified as a mime-type such as application/typescript etc.
         * @var string
         */
        public $media_type;

        /**
         * The ES6 module specifier, must be a URL.
         * @var string
         */
        public $specifier;

        /**
         * The source code of the ES6 module.
         * @var string
         */
        public $text_info;

        public function __construct() {}
    }

    /**
     * TypeScript compiler options used when transpiling.
     */
    class EmitOptions {
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
         * Should import declarations be transformed to variable declarations using
         * a dynamic import. This is useful for import & export declaration support
         * in script contexts such as the Deno REPL.  Defaults to `false`.
         * @var bool
         */
        public $var_decl_imports;

        /**
         * When transforming JSX, what value should be used for the JSX fragment
         * factory.  Defaults to `React.Fragment`.
         * @var string
         */
        public $jsx_fragment_factory;

        /**
         * If JSX is automatic, if it is in development mode, meaning that it should
         * import `jsx-dev-runtime` and transform JSX using `jsxDEV` import from the
         * JSX import source as well as provide additional debug information to the
         * JSX factory.
         * @var bool
         */
        public $jsx_development;

        /**
         * When transforming JSX, what value should be used for the JSX factory.
         * Defaults to `React.createElement`.
         * @var string
         */
        public $jsx_factory;

        /**
         * Should the source map be inlined in the emitted code file, or provided
         * as a separate file.  Defaults to `true`.
         * @var bool
         */
        public $inline_source_map;

        /**
         * When emitting a legacy decorator, also emit experimental decorator meta
         * data.  Defaults to `false`.
         * @var bool
         */
        public $emit_metadata;

        /**
         * The string module specifier to implicitly import JSX factories from when
         * transpiling JSX.
         * @var string
         */
        public $jsx_import_source;

        /**
         * Should the sources be inlined in the source map.  Defaults to `true`.
         * @var bool
         */
        public $inline_sources;

        public function __construct() {}
    }
}

namespace Deno\Core {
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
         * The filename for the JS file
         * @var string
         */
        public $filename;

        /**
         * The code for the javascript file
         * @var string
         */
        public $code;

        public function __construct(string $filename, string $code) {}
    }

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
         * The module type, can be "javascript" or "json".
         * @var string
         */
        public $module_type;

        /**
         * The module's source code.
         * @var string
         */
        public $code;

        /**
         * The specified module URL of the import.
         * @var string
         */
        public $module_url_specified;

        public function __construct(string $code, string $module_type, string $module_url_specified, string $module_url_found) {}
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
         * The ops for the extension (bridged to PHP functions)
         * @var array<string, callable>
         */
        public $ops;

        /**
         * The JS files that should be loaded into the V8 Isolate.
         * @var Deno\Core\JsFile[]
         */
        public $js_files;

        public function __construct() {}
    }

    /**
     * The options provided to the JsRuntime. Pass an instance of this class
     * to Deno\Core\JsRuntime.
     *
     */
    class RuntimeOptions {
        /**
         * Prepare runtime to take snapshot of loaded code. The snapshot is determinstic and uses predictable random numbers.
         *
         * Currently can’t be used with startup_snapshot.
         * @var bool
         */
        public $will_snapshot;

        /**
         * The module loader accepts a callable which is responsible for loading
         * ES6 modules from a given name. See `Deno\Core\ModuleLoader` for methods that should be implemented.
         * @var Deno\Core\ModuleLoader
         */
        public $module_loader;

        /**
         * V8 snapshot that should be loaded on startup.
         *
         * Currently can’t be used with will_snapshot.
         * @var string
         */
        public $startup_snapshot;

        /**
         * Extensions allow you to add additional functionality via Deno "ops" to the JsRuntime. `extensions` takes an array of
         * Deno\Core\Extension instances. See Deno\Core\Extension for details on the PHP <=> JS functions bridge.
         * @var Deno\Core\Extension[]
         */
        public $extensions;

        public function __construct() {}
    }
}

namespace Deno\Runtime {
    /**
     * The Deno main worker. This includes a JsRuntime along with all the standard ops from Deno CLI,
     * such as Deno.core.* and the web APIs such as TextEncoder etc. Use the MainWorker if you want to
     * run programs that are written to run in Deno. The Deno provided ops such as `fetch()` uses it's own
     * TLS and request stack.
     */
    class MainWorker {
        public function __construct(string $main_module, \Deno\Runtime\PermissionsOptions $permissions, \Deno\Runtime\WorkerOptions $options) {}

        public function execute_main_module(): mixed {}

        public function run_event_loop(): mixed {}

        /**
         * Execute JavaSscript inside the V8 Isolate.
         *
         * This does not support top level await for Es6 imports. use `load_main_module`
         * to execute JavaScript in modules.
         */
        public function execute_script(string $name, string $source_code): mixed {}
    }

    /**
     * The options to provide to Deno\Runtime\MainWorker.
     */
    class WorkerOptions {
        /**
         * The Deno\Runtime\BootstrapOptions containing options for the bootstrap process.
         *
         * @param \Deno\Runtime\BootstrapOptions
         */
        public $bootstrap;

        /**
         * Extensions allow you to add additional functionality via Deno "ops" to the JsRuntime. `extensions` takes an array of
         * Deno\Core\Extension instances. See Deno\Core\Extension for details on the PHP <=> JS functions bridge.
         *
         * @var Deno\Core\Extension[]
         */
        public $extensions;

        /**
         * The module loader accepts a callable which is responsible for loading
         * ES6 modules from a given name. See `Deno\Core\ModuleLoader` for methods that should be implemented.
         *
         * @var Deno\Core\ModuleLoader
         */
        public $module_loader;

        public function __construct(\Deno\Runtime\BootstrapOptions $bootstrap, array $extensions, mixed $module_loader) {}
    }

    /**
     * Common bootstrap options for MainWorker & WebWorker
     */
    class BootstrapOptions {
        /**
         * Sets `Deno.version.deno` in JS runtime.
         *
         * @param string
         */
        public $runtime_version;

        /**
         * @param bool
         */
        public $enable_testing_features;

        /**
         * @param bool
         */
        public $is_tty;

        /**
         * Sets `Deno.args` in JS runtime.
         */
        public $args;

        /**
         * @param int
         */
        public $cpu_count;

        /**
         * Sets `Deno.version.typescript` in JS runtime.
         *
         * @param string
         */
        public $ts_version;

        /**
         * @param bool
         */
        public $unstable;

        /**
         * @param bool
         */
        public $debug_flag;

        /**
         * Sets `Deno.noColor` in JS runtime.
         *
         * @param bool
         */
        public $no_color;

        /**
         * @param string
         */
        public $user_agent;

        /**
         * @param ?string
         */
        public $location;

        public function __construct() {}
    }

    class PermissionsOptions {
        /**
         * Allow network access. You can specify an optional list of IP addresses or hostnames (optionally with ports) to provide an allow-list of allowed network addresses. Pass an empty array to allow all.
         *
         * @param string[]
         */
        public $allow_net;

        /**
         * Allow loading of dynamic libraries. Be aware that dynamic libraries are not run in a sandbox and therefore do not have the same security restrictions as the Deno process. Therefore, use with caution.
         *
         * @param string[]
         */
        public $allow_ffi;

        /**
         * Allow high-resolution time measurement. High-resolution time can be used in timing attacks and fingerprinting.
         *
         * @param bool
         */
        public $allow_hrtime;

        /**
         * Allow file system read access. You can specify an optional list of directories or files to provide an allow-list of allowed file system access. Pass an empty array to allow all.
         *
         * @param string[]
         */
        public $allow_read;

        /**
         * Allow environment access for things like getting and setting of environment variables. You can specify a list of environment variables to provide an allow-list of allowed environment variables. Pass an empty array to allow all.
         *
         * @param string[]
         */
        public $allow_env;

        /**
         * Allow running subprocesses. You can specify an optional list of subprocesses to provide an allow-list of allowed subprocesses.
         * Be aware that subprocesses are not run in a sandbox and therefore do not have the same security restrictions as the Deno process. Therefore, use with caution. Pass an empty array to allow all.
         *
         * @param string[]
         */
        public $allow_run;

        /**
         * Allow file system write access. You can specify an optional list of directories or files to provide an allow-list of allowed file system access. Pass an empty array to allow all.
         *
         * @param string[]
         */
        public $allow_write;

        public function __construct() {}
    }
}
