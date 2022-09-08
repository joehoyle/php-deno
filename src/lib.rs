use anyhow::Error;
use ext_php_rs::{
    convert::FromZval,
    convert::{IntoZval, IntoZvalDyn},
    prelude::*,
    types::Zval,
};
use futures::future::FutureExt;

#[php_class(name = "Deno\\Runtime\\MainWorker")]
struct MainWorker {
    deno_main_worker: deno_runtime::worker::MainWorker,
    main_module: deno_core::ModuleSpecifier,
}

fn get_error_class_name(e: &deno_core::error::AnyError) -> &'static str {
    deno_runtime::errors::get_error_class_name(e).unwrap_or("Error")
}

#[php_impl(rename_methods = "none")]
impl MainWorker {
    #[constructor]
    fn new() -> Self {
        let module_loader = std::rc::Rc::new(deno_core::FsModuleLoader);
        let create_web_worker_cb = std::sync::Arc::new(|_| {
            todo!("Web workers are not supported in the example");
        });
        let web_worker_event_cb = std::sync::Arc::new(|_| {
            todo!("Web workers are not supported in the example");
        });

        let options = deno_runtime::worker::WorkerOptions {
            bootstrap: deno_runtime::BootstrapOptions {
                args: vec![],
                cpu_count: 1,
                debug_flag: false,
                enable_testing_features: false,
                location: None,
                no_color: false,
                is_tty: false,
                runtime_version: "x".to_string(),
                ts_version: "x".to_string(),
                unstable: false,
                user_agent: "hello_runtime".to_string(),
            },
            extensions: vec![],
            unsafely_ignore_certificate_errors: None,
            root_cert_store: None,
            seed: None,
            source_map_getter: None,
            format_js_error_fn: None,
            web_worker_preload_module_cb: web_worker_event_cb.clone(),
            web_worker_pre_execute_module_cb: web_worker_event_cb,
            create_web_worker_cb,
            maybe_inspector_server: None,
            should_break_on_first_statement: false,
            module_loader,
            npm_resolver: None,
            get_error_class_fn: Some(&get_error_class_name),
            origin_storage_dir: None,
            blob_store: deno_runtime::deno_web::BlobStore::default(),
            broadcast_channel: deno_broadcast_channel::InMemoryBroadcastChannel::default(),
            shared_array_buffer_store: None,
            compiled_wasm_module_store: None,
            stdio: Default::default(),
        };

        let js_path = "/Users/joe/rust/php-deno/mainModule.js";
        let main_module = deno_core::resolve_path(js_path).unwrap();
        let permissions = deno_runtime::permissions::Permissions::allow_all();

        let worker = deno_runtime::worker::MainWorker::bootstrap_from_options(
            main_module.clone(),
            permissions,
            options,
        );
        Self {
            deno_main_worker: worker,
            main_module: main_module,
        }
    }

    pub fn execute_main_module(&mut self) -> PhpResult<()> {
        // todo switch all to use tokio
        let mut rt = tokio::runtime::Runtime::new().unwrap();
        let local = tokio::task::LocalSet::new();
        local.block_on(&mut rt, async {
            match self
                .deno_main_worker
                .execute_main_module(&self.main_module)
                .await
            {
                Ok(()) => Ok(()),
                Err(error) => return Err(error.to_string().into()),
            }
        })
    }

    fn run_event_loop(&mut self) -> PhpResult<()> {
        match futures::executor::block_on(self.deno_main_worker.run_event_loop(false)) {
            Ok(()) => Ok(()),
            Err(error) => Err(error.to_string().into()),
        }
    }
}

#[php_class(name = "Deno\\Core\\RuntimeOptions")]
struct RuntimeOptions {
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    module_loader: Option<CloneableZval>,
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    extensions: Vec<Extension>,
}

#[php_impl(rename_methods = "none")]
impl RuntimeOptions {
    #[constructor]
    fn new() -> Self {
        Self {
            module_loader: None,
            extensions: vec![],
        }
    }
}

#[php_class(name = "Deno\\Core\\JsRuntime")]
struct JsRuntime(deno_core::JsRuntime);

#[php_impl(rename_methods = "none")]
impl JsRuntime {
    #[constructor]
    fn new(options: &RuntimeOptions) -> Self {
        let extensions = options
            .extensions
            .iter()
            .map(|extension| -> deno_core::Extension {
                let js_files = extension
                    .js_files
                    .iter()
                    .map(|js_file| -> (&str, &str) {
                        // This causes a memory leak, but the js-files exntesion requires static strings so there's not much we can do.
                        let filename: &'static str =
                            Box::leak(js_file.filename.clone().into_boxed_str());
                        let code: &'static str = Box::leak(js_file.code.clone().into_boxed_str());
                        (filename, code)
                    })
                    .collect();
                deno_core::Extension::builder()
                    .js(js_files)
                    .ops(vec![])
                    .build()
            })
            .collect();
        Self(deno_core::JsRuntime::new(deno_core::RuntimeOptions {
            module_loader: Some(std::rc::Rc::new(ModuleLoader::new(Some(
                options.module_loader.as_ref().unwrap().clone(),
            )))),
            extensions,
            ..Default::default()
        }))
    }

    fn execute_script(&mut self, name: &str, source_code: &str) -> PhpResult<()> {
        match self.0.execute_script(name, source_code) {
            Ok(_) => Ok(()),
            Err(error) => match error.downcast::<deno_core::error::JsError>() {
                Ok(error) => Err(error.exception_message.into()),
                Err(error) => Err(error.to_string().into()),
            },
        }
    }

    fn load_main_module(
        &mut self,
        specifier: &str,
        code: Option<String>,
    ) -> PhpResult<deno_core::ModuleId> {
        match futures::executor::block_on(
            self.0
                .load_main_module(&url::Url::parse(specifier).unwrap(), code),
        ) {
            Ok(module) => Ok(module),
            Err(error) => Err(error.to_string().into()),
        }
    }

    fn mod_evaluate(&mut self, id: deno_core::ModuleId) -> PhpResult<()> {
        let result = self.0.mod_evaluate(id);
        match futures::executor::block_on(self.0.run_event_loop(false)) {
            Ok(()) => (),
            Err(error) => return Err(error.to_string().into()),
        };

        match futures::executor::block_on(result).unwrap() {
            Ok(()) => Ok(()),
            Err(error) => Err(error.to_string().into()),
        }
    }

    fn run_event_loop(&mut self) -> PhpResult<()> {
        match futures::executor::block_on(self.0.run_event_loop(false)) {
            Ok(()) => Ok(()),
            Err(error) => Err(error.to_string().into()),
        }
    }
}

struct ModuleLoader {
    load_callback: Option<CloneableZval>,
}

impl ModuleLoader {
    fn new(load_callback: Option<CloneableZval>) -> Self {
        Self {
            load_callback: load_callback,
        }
    }
}

#[php_class(name = "Deno\\Core\\JsFile")]
#[derive(Clone, Debug)]
struct JsFile {
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    filename: String,
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    code: String,
}

#[php_impl(rename_methods = "none")]
impl JsFile {
    #[constructor]
    fn new(filename: String, code: String) -> Self {
        Self { filename, code }
    }
}

#[php_class(name = "Deno\\Core\\Extension")]
#[derive(Clone)]
struct Extension {
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    js_files: Vec<JsFile>,
}

#[php_impl(rename_methods = "none")]
impl Extension {
    #[constructor]
    fn new() -> Self {
        Self { js_files: vec![] }
    }
}

impl FromZval<'_> for Extension {
    const TYPE: ext_php_rs::flags::DataType = ext_php_rs::flags::DataType::Mixed;
    fn from_zval(zval: &'_ Zval) -> Option<Self> {
        let extension: &Extension = zval.extract().unwrap();
        let new_extension = extension.to_owned();
        Some(new_extension)
    }
}

impl FromZval<'_> for JsFile {
    const TYPE: ext_php_rs::flags::DataType = ext_php_rs::flags::DataType::Mixed;
    fn from_zval(zval: &'_ Zval) -> Option<Self> {
        let file: &JsFile = zval.extract().unwrap();
        let new_file = file.to_owned();
        Some(new_file)
    }
}

impl deno_core::ModuleLoader for ModuleLoader {
    fn resolve(
        &self,
        specifier: &str,
        referrer: &str,
        _is_main: bool,
    ) -> Result<deno_core::ModuleSpecifier, Error> {
        Ok(deno_core::resolve_import(specifier, referrer)?)
    }

    fn load(
        &self,
        _module_specifier: &deno_core::ModuleSpecifier,
        _maybe_referrer: Option<deno_core::ModuleSpecifier>,
        _is_dyn_import: bool,
    ) -> core::pin::Pin<Box<deno_core::ModuleSourceFuture>> {
        let c = self.load_callback.as_ref().unwrap();

        let load_callback = c.as_zval(false).unwrap();
        if load_callback.is_callable() == false {
            return async { Err(deno_core::error::generic_error("callback not callable")) }
                .boxed_local();
        }

        let specifier =
            CloneableZval::from_zval(&Zval::try_from(_module_specifier.to_string()).unwrap())
                .unwrap();

        let mut php_args: Vec<&dyn ext_php_rs::convert::IntoZvalDyn> = Vec::new();
        php_args.push(&specifier);

        let return_value = match load_callback.try_call(php_args) {
            Ok(v) => v,
            Err(error) => {
                return async {
                    Err(deno_core::error::generic_error(
                        "Error in callback return value",
                    ))
                }
                .boxed_local()
            }
        };

        let source: &ModuleSource = return_value.extract().unwrap();

        let module_source = deno_core::ModuleSource {
            code: source.code.clone().as_bytes().to_owned().into_boxed_slice(),
            module_type: if source.module_type == "json" {
                deno_core::ModuleType::Json
            } else {
                deno_core::ModuleType::JavaScript
            },
            module_url_specified: source.module_url_specified.clone(),
            module_url_found: source.module_url_found.clone(),
        };

        return async { Ok(module_source) }.boxed_local();
    }
}

#[php_class(name = "Deno\\Core\\ModuleSource")]
#[derive(Debug)]
struct ModuleSource {
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    code: String,
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    module_type: String,
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    module_url_specified: String,
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    module_url_found: String,
}

#[php_impl(rename_methods = "none")]
impl ModuleSource {
    #[constructor]
    fn new(
        code: String,
        module_type: String,
        module_url_specified: String,
        module_url_found: String,
    ) -> Self {
        Self {
            code,
            module_type,
            module_url_specified,
            module_url_found,
        }
    }
}

// Zval doesn't implement Clone, which means that Zval's can not
// be passed to `ZendCallable.try_call()`, so we have to wrap it
// in a Cloneable wrapper.
#[derive(Debug)]
struct CloneableZval(Zval);

impl FromZval<'_> for CloneableZval {
    const TYPE: ext_php_rs::flags::DataType = ext_php_rs::flags::DataType::Mixed;
    fn from_zval(zval: &'_ Zval) -> Option<Self> {
        Some(Self(zval.shallow_clone()))
    }
}

impl IntoZval for CloneableZval {
    const TYPE: ext_php_rs::flags::DataType = ext_php_rs::flags::DataType::Mixed;
    fn set_zval(self, zv: &mut Zval, _: bool) -> ext_php_rs::error::Result<()> {
        *zv = self.0;
        Ok(())
    }
    fn into_zval(self, _persistent: bool) -> ext_php_rs::error::Result<Zval> {
        Ok(self.0)
    }
}

impl Clone for CloneableZval {
    fn clone(&self) -> Self {
        Self(self.0.shallow_clone())
    }
}

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
}
