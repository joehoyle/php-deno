<?php

// Stubs for php-deno

namespace Deno\Core {
    /**
     * Extension contains PHP functions (ops) and associated js files which are
     * exposed to JavaScript via the JsRuntime. PHP functions can be called from JavaScript
     * via `Deno.core.$name` where `$name` is the array key string from the `ops` property.
     *
     * It's common to provide `ops` and also more user-friendly accessible functions for those
     * `ops` via the `js_files` property.
     */
    class Extension {
        public $ops;

        public $js_files;

        public function __construct() {}
    }

    class ModuleSource {
        public $module_url_found;

        public $module_type;

        public $module_url_specified;

        public $code;

        public function __construct(string $code, string $module_type, string $module_url_specified, string $module_url_found) {}
    }

    /**
     * JsFile is a descriptor for JavaScript files that are loaded as
     * part of the Extension->js_files array. The `code` of `JsFile` is
     * executed when the JsRuntime is initiated.
     */
    class JsFile {
        public $filename;

        public $code;

        public function __construct(string $filename, string $code) {}
    }

    /**
     * The options provided to the JsRuntime. Pass an instance of this class
     * to Deno\Core\JsRuntime.
     */
    class RuntimeOptions {
        /**
         * The module loader accepts a callable which is responsible for loading
         * ES6 modules from a given name. The loader is in the form `function ( string $specifier ) : Deno\Core\ModuleSource`
         */
        public $module_loader;

        /**
         * Extensions allow you to add additional functionality via Deno "ops" to the JsRuntime. `extensions` takes an array of
         * Deno\Core\Extension instances. See Deno\Core\Extension for details on the PHP <=> JS functions bridge.
         */
        public $extensions;

        public function __construct() {}
    }

    /**
     * The JsRuntime is a wrapper around a V8 isolate. It can execute ES6 including ES6 modules. The JsRuntime
     * does not include any of the Deno.core.* ops, and does not provide implementations for web apis, such as
     * fetch(). Use JsRuntime if you want to provide low-level v8 isolates, and implement extensions for all
     * functionality such as local storage, remote requests etc.
     */
    class JsRuntime {
        public function __construct(Deno\Core\RuntimeOptions $options) {}

        public function execute_script(string $name, string $source_code): mixed {}

        public function load_main_module(string $specifier, ?string $code): int {}

        public function mod_evaluate(int $id): mixed {}

        public function run_event_loop(): mixed {}
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
        public function __construct() {}

        public function execute_main_module(): mixed {}

        public function run_event_loop(): mixed {}
    }
}
