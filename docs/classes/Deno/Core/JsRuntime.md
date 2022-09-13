***

# JsRuntime

The JsRuntime is a wrapper around a V8 isolate. It can execute ES6 including ES6 modules. The JsRuntime
does not include any of the Deno.core.* ops, and does not provide implementations for web apis, such as
fetch(). Use JsRuntime if you want to provide low-level v8 isolates, and implement extensions for all
functionality such as local storage, remote requests etc.



* Full name: `\Deno\Core\JsRuntime`




## Methods


### __construct



```php
public __construct(\Deno\Core\Deno\Core\RuntimeOptions $options): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **\Deno\Core\Deno\Core\RuntimeOptions** |  |




***

### execute_script



```php
public execute_script(string $name, string $source_code): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$source_code` | **string** |  |




***

### load_main_module



```php
public load_main_module(string $specifier, ?string $code): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$specifier` | **string** |  |
| `$code` | **?string** |  |




***

### mod_evaluate



```php
public mod_evaluate(int $id): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** |  |




***

### run_event_loop



```php
public run_event_loop(): mixed
```











***


***
> Automatically generated on 2022-09-13
