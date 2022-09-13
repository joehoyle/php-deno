
***

# Documentation



This is an automatically generated documentation for **Documentation**.


## Namespaces


### \Deno\Core

#### Classes

| Class | Description |
|-------|-------------|
| [`Extension`](./classes/Deno/Core/Extension.md) | |
| [`JsFile`](./classes/Deno/Core/JsFile.md) | |
| [`JsRuntime`](./classes/Deno/Core/JsRuntime.md) | The JsRuntime is a wrapper around a V8 isolate. It can execute ES6 including ES6 modules. The JsRuntime<br />does not include any of the Deno.core.* ops, and does not provide implementations for web apis, such as<br />fetch(). Use JsRuntime if you want to provide low-level v8 isolates, and implement extensions for all<br />functionality such as local storage, remote requests etc.|
| [`ModuleSource`](./classes/Deno/Core/ModuleSource.md) | |
| [`RuntimeOptions`](./classes/Deno/Core/RuntimeOptions.md) | The options provided to the JsRuntime. Pass an instance of this class<br />to Deno\Core\JsRuntime.|




### \Deno\Runtime

#### Classes

| Class | Description |
|-------|-------------|
| [`MainWorker`](./classes/Deno/Runtime/MainWorker.md) | The Deno main worker. This includes a JsRuntime along with all the standard ops from Deno CLI,<br />such as Deno.core.* and the web APIs such as TextEncoder etc. Use the MainWorker if you want to<br />run programs that are written to run in Deno. The Deno provided ops such as `fetch()` uses it&#039;s own<br />TLS and request stack.|




***
> Automatically generated on 2022-09-13
