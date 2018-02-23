# DVC usage example

Simple single-file scaffold for building MVC-like applications.

## Structure
* `/code`
  * `/controllers` &ndash; Application controller class containing actions
  * `/models` &ndash; Model and ModelSource classes
  * `/views` &ndash; .phtml templates for actions
* `/data` &ndash; JSON databases structured in folders if needed
* `/vendor` &ndash; External libraries if needed

## Config
You can pass the configuration options into `MyApp::create(...)` call in `index.php`.
#####Supported options
* **`dvcDataPath`** &ndash; Override `/data` path.
* **`dvcViewsPath`** &ndash; Override `/code/views` path.
* **`dvcModelsPath`** &ndash; Override `/code/models` path.
* **`dvcControllersPath`** &ndash; Override `/code/controllers` path.
* **`useLanguage`** &ndash; If option is set, DVC will load `/data/shared/language.json` and `/data/shared/language_{lang}.json`.
* **`language`** &ndash; Language code to use with `useLanguage` option.
