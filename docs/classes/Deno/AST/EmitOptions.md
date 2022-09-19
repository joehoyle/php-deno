***

# EmitOptions

TypeScript compiler options used when transpiling.



* Full name: `\Deno\AST\EmitOptions`




## Properties


### var_decl_imports

Should import declarations be transformed to variable declarations using
a dynamic import. This is useful for import & export declaration support
in script contexts such as the Deno REPL.  Defaults to `false`.

```php
public $var_decl_imports
```






***

### jsx_import_source

The string module specifier to implicitly import JSX factories from when
transpiling JSX.

```php
public $jsx_import_source
```






***

### jsx_automatic

`true` if the program should use an implicit JSX import source/the "new"
JSX transforms.

```php
public $jsx_automatic
```






***

### inline_sources

Should the sources be inlined in the source map.  Defaults to `true`.

```php
public $inline_sources
```






***

### inline_source_map

Should the source map be inlined in the emitted code file, or provided
as a separate file.  Defaults to `true`.

```php
public $inline_source_map
```






***

### jsx_fragment_factory

When transforming JSX, what value should be used for the JSX fragment
factory.  Defaults to `React.Fragment`.

```php
public $jsx_fragment_factory
```






***

### source_map

Should a corresponding .map file be created for the output. This should be
false if inline_source_map is true. Defaults to `false`.

```php
public $source_map
```






***

### transform_jsx

Should JSX be transformed or preserved.  Defaults to `true`.

```php
public $transform_jsx
```






***

### jsx_development

If JSX is automatic, if it is in development mode, meaning that it should
import `jsx-dev-runtime` and transform JSX using `jsxDEV` import from the
JSX import source as well as provide additional debug information to the
JSX factory.

```php
public $jsx_development
```






***

### jsx_factory

When transforming JSX, what value should be used for the JSX factory.

```php
public $jsx_factory
```

Defaults to `React.createElement`.




***

### emit_metadata

When emitting a legacy decorator, also emit experimental decorator meta
data.  Defaults to `false`.

```php
public $emit_metadata
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
