<?php

$source = new Deno\ModuleSource( 'Deno.core.print("Im a module")', 'javascript', 'foo.ts', 'foo.ts' );

$extension = new Deno\Extension();
$extension->js_files = [
    new Deno\JsFile( 'fetch.js', 'async function fetch() { return "HI" }' ),
];

$runtime_options = new Deno\RuntimeOptions();
$runtime_options->module_loader = function ( string $specifier ) : Deno\ModuleSource {
    if ( strpos( $specifier, 'https://' ) === 0 ) {
        $contents = file_get_contents( $specifier );
    } else {
        $contents = '';
    }
    $source = new Deno\ModuleSource( $contents, 'javascript', $specifier, $specifier );
    return $source;
};

$runtime_options->extensions = [ $extension ];

$js_runtime = new Deno\JsRuntime( $runtime_options );

$module_id = $js_runtime->load_main_module('file:///main.js', 'Deno.core.ops.op_nodp()');

var_dump( $js_runtime->mod_evaluate($module_id) );
// $js_runtime->run_event_loop();
