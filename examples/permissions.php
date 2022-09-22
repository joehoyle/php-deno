<?php

class ModuleLoader implements Deno\Core\ModuleLoader {
    function resolve( string $specifier, string $referer ) : string {
        return $specifier;
    }

    function load( string $specifier ) : ?Deno\Core\ModuleSource {
        $source = new Deno\Core\ModuleSource(
           "",
            'application/javascript',
            "file:///bar.js",
            "file:///bar.js"
        );
        return $source;
    }
}

$boostrap_options = new Deno\Runtime\BootstrapOptions();
$options = new Deno\Runtime\WorkerOptions( $boostrap_options , [], new ModuleLoader() );
$permissions = new Deno\Runtime\PermissionsOptions();

$runtime = new Deno\Runtime\MainWorker( 'index.js', $permissions, $options );
$caused_exception = false;

try {
    $runtime->execute_script( 'index.js', 'fetch( "http://example.com" ).then( r => r.text() ).then( r => Deno.core.print(r) );' );
    $runtime->run_event_loop();
} catch ( Exception $e ) {
    $caused_exception = true;
}

assert( $caused_exception === true );

$permissions->allow_net = [];


$runtime = new Deno\Runtime\MainWorker( 'index.js', $permissions, $options );

$runtime->execute_script( 'index.js', 'fetch( "http://example.com" ).then( r => r.text() ).then( r => Deno.core.print(r) );' );
$runtime->run_event_loop();
