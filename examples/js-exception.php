<?php

$runtime_options = new Deno\Core\RuntimeOptions();
$runtime = new Deno\Core\JsRuntime( $runtime_options );
try {
    $runtime->execute_script( 'index.js', 'fetch()' );
} catch ( Deno\Core\JsException $e ) {
    echo $e->getMessage();
    var_dump( $e );
};
