use anyhow::Error;
use ext_php_rs::{
    convert::FromZval,
    convert::{IntoZval, IntoZvalDyn},
    prelude::*,
    types::Zval,
};
use futures::future::FutureExt;

#[php_class(name = "Deno\\RuntimeOptions")]
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

#[php_class(name = "Deno\\JsRuntime")]
struct JsRuntime(deno_core::JsRuntime);

#[php_impl(rename_methods = "none")]
impl JsRuntime {
    #[constructor]
    fn new(options: &RuntimeOptions) -> Self {
        let extensions = options.extensions.iter().map(|extension| -> deno_core::Extension {
            deno_core::Extension::builder().js(vec![]).build()
        }).collect();
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
        dbg!(id);
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

#[php_class(name = "Deno\\JsFile")]
#[derive(Clone)]
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
        Self {
            filename,
            code,
        }
    }
}

#[php_class(name = "Deno\\Extension")]
#[derive(Clone)]
struct Extension {
    #[prop(flags = ext_php_rs::flags::PropertyFlags::Public)]
    js_files: Vec<JsFile>,
}

#[php_impl(rename_methods = "none")]
impl Extension {
    #[constructor]
    fn new() -> Self {
        Self {
            js_files: vec![],
        }
    }
}

impl FromZval<'_> for Extension {
    const TYPE: ext_php_rs::flags::DataType = ext_php_rs::flags::DataType::Mixed;
    fn from_zval(zval: &'_ Zval) -> Option<Self> {
        let extension: Extension = zval.shallow_clone().extract().unwrap();
        Some(extension)
    }
}

impl FromZval<'_> for JsFile {
    const TYPE: ext_php_rs::flags::DataType = ext_php_rs::flags::DataType::Mixed;
    fn from_zval(zval: &'_ Zval) -> Option<Self> {
        let file: JsFile = zval.shallow_clone().extract().unwrap();
        Some(file)
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
                dbg!(error);
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

#[php_class(name = "Deno\\ModuleSource")]
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
