<?php

class ModuleLoader implements Deno\Core\ModuleLoader {
    function resolve( string $specifier, string $referer ) : string {
        return $specifier;
    }

    function load( string $specifier ) : ?Deno\Core\ModuleSource {
        $source = new Deno\Core\ModuleSource(
            <<<END
            const server = Deno.listen({ port: 8090 });
            Deno.core.print( "waiting on port 8090" );
            for await (const conn of server) {
                handle(conn);
            }

            async function handle(conn) {
                const httpConn = Deno.serveHttp(conn);
                for await (const requestEvent of httpConn) {
                    await requestEvent.respondWith(
                        new Response('hello world', {
                            status: 200,
                        })
                    );
                }
            }
            END,
            'application/javascript',
            "file:///index.js",
            "file:///index.js"
        );
        return $source;
    }
}

$boostrap_options = new Deno\Runtime\BootstrapOptions();
$options = new Deno\Runtime\WorkerOptions( $boostrap_options , [], new ModuleLoader() );
$permissions = new Deno\Runtime\PermissionsOptions();
$permissions->allow_net = [];

$main_worker = new Deno\Runtime\MainWorker( 'index.js', $permissions, $options );

$module = $main_worker->execute_main_module();
$main_worker->run_event_loop();


