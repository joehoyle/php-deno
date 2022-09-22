<?php

class ModuleLoader implements Deno\Core\ModuleLoader {
    function resolve( string $specifier, string $referer ) : string {
        return $specifier;
    }

    function load( string $specifier ) : ?Deno\Core\ModuleSource {
        $source = new Deno\Core\ModuleSource(
            <<<END
            async function willResolve() {
                return 1;
            }
            Deno.core.print( String( await willResolve() ) );
            END,
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

$module = $runtime->execute_main_module();
