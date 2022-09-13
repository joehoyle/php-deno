<?php

$source = new Deno\Core\ModuleSource( 'Deno.core.print("Im a module")', 'javascript', 'foo.ts', 'foo.ts' );
$extension = new Deno\Core\Extension();
$extension->ops = [
    'php_callback' => function ( int $num, int $add ) : int {
        return $num + $add;
    },
];

$runtime_options = new Deno\Core\RuntimeOptions();
$runtime_options->module_loader = function ( string $specifier ) : Deno\Core\ModuleSource {
    if ( strpos( $specifier, 'https://' ) === 0 ) {
        $contents = file_get_contents( $specifier );
    } else {
        $contents = '';
    }
    $source = new Deno\Core\ModuleSource( $contents, 'javascript', $specifier, $specifier );
    return $source;
};

$runtime_options->extensions = [ $extension ];

$js_runtime = new Deno\Core\JsRuntime( $runtime_options );

$module_id = $js_runtime->load_main_module('file:///main.js', 'Deno.core.print(String(Deno.core.ops.php_callback(1, 2)))');

$js_runtime->mod_evaluate($module_id);
// $js_runtime->run_event_loop();
