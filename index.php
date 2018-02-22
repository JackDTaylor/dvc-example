<?php
define('APP_URL', '/dvc-example'); // This file's base url
define('APP_ROOT', __DIR__);
require_once APP_ROOT.'/vendor/DVC.php';
require_once APP_ROOT.'/code/models/MyModel.php';
require_once APP_ROOT.'/code/controllers/MyApp.php';

MyApp::create([
	'my_model_db_key' => 'example-model-key' // or $_GET['model_to_load']
	// ...and other params
])->run(\DVC\getAction());