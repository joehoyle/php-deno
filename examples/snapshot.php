<?php

$runtime_options = new \Deno\Core\RuntimeOptions;
$runtime_options->will_snapshot = true;

$js_runtime = new Deno\Core\JsRuntime( $runtime_options );
$js_runtime->execute_script( "index.js", "function foo() { return 1; }" );

$snapshot = $js_runtime->snapshot();

// Create a new Runtime with the snapshot.

$runtime_options = new \Deno\Core\RuntimeOptions;
$runtime_options->startup_snapshot = $snapshot;

$js_runtime = new Deno\Core\JsRuntime( $runtime_options );
$js_runtime->execute_script( "index.js", "foo();" );
