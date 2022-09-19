<?php

$start = microtime(true);

$parse_params = new Deno\AST\ParseParams;
$parse_params->specifier = 'file:///react.tsx';
$parse_params->text_info = 'function add( a: number, b: number ) : number { return <div>Class</div>; }';
$parse_params->media_type = 'application/javascript';

$module = Deno\AST\parse_module( $parse_params );
var_dump($module);

$emit_options = new Deno\AST\EmitOptions;
var_dump($emit_options);

var_dump( $module->transpile( $emit_options ) );

var_dump( microtime(true) - $start );
