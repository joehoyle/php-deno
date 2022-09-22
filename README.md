# PHP-Deno

PHP-Deno provides [`deno_core`](https://crates.io/crates/deno_core) and [`deno_runtime`](https://crates.io/crates/deno_runtime) bindings for PHP, via a PHP module. This allows users to run JavaScript in a V8 Isolate, via the Deno runtime. The extension provides wrappers for `deno_core`, which is a low-level wrapper around V8. This can be used to provide a secure, sandboxed environments to execute user scripts. The higher-level `deno_runtime` wrapper is also provided, which includes the V8 Isolate, the `Deno.core.*` API and web apis (such as `fetch`, `TextEncoder`, etc) to JavaScript modules and scripts.

This project is similar to [V8Js](https://github.com/phpv8/v8js) in that it provides PHP bindings to a V8 Isolate, however it does this _via_ the Deno project. It's analogous to embedding Node in PHP, rather than solely V8.

PHP-Deno also includes a PHP <-> JavaScript bridge, to expose PHP functions to JavaScript. This is achieved via the [`Extension`](./docs/classes/Core/Extension.md) class.

[View the Documentation →](https://joehoyle.github.io/php-deno/)
