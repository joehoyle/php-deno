<?php

class ModuleLoader implements Deno\Core\ModuleLoader {
    function resolve( string $specifier, string $referer ) : string {
        return $specifier;
    }

    function load( string $specifier ) : ?Deno\Core\ModuleSource {
        $source = new Deno\Core\ModuleSource(
            'export default "HI"',
            'application/javascript',
            "file:///bar.js",
            "file:///bar.js"
        );
        return $source;
    }
}

$options = new Deno\Core\RuntimeOptions;
$options->module_loader = new ModuleLoader;

$runtime = new Deno\Core\JsRuntime( $options );
$module_id = $runtime->load_main_module( 'file:///index.js', 'import foo from "file:///bar.js"; Deno.core.print(foo);' );
$runtime->mod_evaluate( $module_id );
