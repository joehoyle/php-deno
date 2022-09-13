***

# RuntimeOptions

The options provided to the JsRuntime. Pass an instance of this class
to Deno\Core\JsRuntime.



* Full name: `\Deno\Core\RuntimeOptions`



## Properties


### module_loader

The module loader accepts a callable which is responsible for loading
ES6 modules from a given name. The loader is in the form `function ( string $specifier ) : Deno\Core\ModuleSource`

```php
public $module_loader
```






***

### extensions

Extensions allow you to add additional functionality via Deno "ops" to the JsRuntime. `extensions` takes an array of
Deno\Core\Extension instances. See Deno\Core\Extension for details on the PHP <=> JS functions bridge.

```php
public $extensions
```






***

## Methods


### __construct



```php
public __construct(): mixed
```











***


***
> Automatically generated on 2022-09-13