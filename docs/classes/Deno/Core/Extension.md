***

# Extension

Extension contains PHP functions (ops) and associated js files which are
exposed to JavaScript via the JsRuntime. PHP functions can be called from JavaScript
via `Deno.core.$name` where `$name` is the array key string from the `ops` property.

It's common to provide `ops` and also more user-friendly accessible functions for those
`ops` via the `js_files` property.

* Full name: `\Deno\Core\Extension`




## Properties


### js_files



```php
public $js_files
```






***

### ops



```php
public $ops
```






***

## Methods


### __construct



```php
public __construct(): mixed
```











***


***
> Automatically generated on 2022-09-19
