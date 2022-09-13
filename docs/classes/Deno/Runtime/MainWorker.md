***

# MainWorker

The Deno main worker. This includes a JsRuntime along with all the standard ops from Deno CLI,
such as Deno.core.* and the web APIs such as TextEncoder etc. Use the MainWorker if you want to
run programs that are written to run in Deno. The Deno provided ops such as `fetch()` uses it's own
TLS and request stack.



* Full name: `\Deno\Runtime\MainWorker`




## Methods


### __construct



```php
public __construct(): mixed
```











***

### execute_main_module



```php
public execute_main_module(): mixed
```











***

### run_event_loop



```php
public run_event_loop(): mixed
```











***


***
> Automatically generated on 2022-09-13
