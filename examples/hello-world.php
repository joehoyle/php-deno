<?php

$runtime_options = new Deno\Core\RuntimeOptions();
$runtime = new Deno\Core\JsRuntime( $runtime_options );
$runtime->execute_script( 'Deno.core.print( "Hello World" )' );
