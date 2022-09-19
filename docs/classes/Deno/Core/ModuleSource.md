***

# ModuleSource

ModuleSource represents an ES6 module, including the source code and type. An ModuleSource should
be returned from your module loader passed to JsRuntime's RuntimeOptions::module_loader property.



* Full name: `\Deno\Core\ModuleSource`




## Properties


### code

The module's source code.

```php
public $code
```






***

### module_url_specified



```php
public $module_url_specified
```






***

### module_url_found



```php
public $module_url_found
```






***

### module_type

The module type, can be "javascript" or "json".

```php
public $module_type
```






***

## Methods


### __construct



```php
public __construct(string $code, string $module_type, string $module_url_specified, string $module_url_found): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$code` | **string** |  |
| `$module_type` | **string** |  |
| `$module_url_specified` | **string** |  |
| `$module_url_found` | **string** |  |




***


***
> Automatically generated on 2022-09-19
