<?php
/*
 * Simple single-file scaffold for MVC-application based on JSON database
 */
namespace DVC;

function loadDatabase($name) {
	$name = preg_replace('#[^-a-zA-Z_0-9/]#', '', $name);

	if(!$name) {
		throw new Exception("Unable to load database by empty name");
	}

	$file = "data/{$name}.json";

	if(!is_readable($file)) {
		throw new Exception("Unable to locate database `{$name}`");
	}

	$json = json_decode(file_get_contents($file), true);

	if(!$json) {
		throw new Exception("Unable to parse database `{$name}`");
	}

	return $json;
}

function getAction(&$tokens = null) {
	$url = pos(explode('?', $_SERVER['REQUEST_URI']));

	if(APP_URL) {
		$url = preg_replace('#^'.preg_quote(APP_URL, '#').'#', '', $url);
	}

	$tokens = explode('/', trim($url, '/'));
	$action = $tokens[0];
	$tokens = array_slice($tokens, 1);

	return $action;
}


class Exception extends \Exception {};

class Lang {
	const DEFAULT_LANGUAGE = 'en';

	protected static $db = null;

	public static function load($language = null) {
		$language = $language ?: self::DEFAULT_LANGUAGE;

		self::$db = call_user_func_array('array_replace_recursive', [
			loadDatabase('shared/language'),
			loadDatabase('shared/language_' . $language),
		]);
	}

	public static function T($type, $value) {
		$key = implode('.', func_get_args());

		if(isset(self::$db[$key])) {
			return self::$db[$key];
		}

		return '*' . $key;
	}
}

abstract class Source {
	private $_path;
	private $_database;

	public function __construct($path) {
		$this->_path = $path;
	}

	protected function getMapped(callable $fn) {
		$this->load();

		$result = [];
		foreach($this->_database as $key => $value) {
			$result[] = $fn($value, $key);
		}

		return $result;
	}

	public function load() {
		if(is_null($this->_database)) {
			$this->_database = loadDatabase($this->_path);
		}
	}

	abstract protected function prepareModel($key, $data);

	public function getByKey($key) {
		$this->load();

		if(!isset($this->_database[$key])) {
			return null;
		}

		return $this->prepareModel($key, $this->_database[$key]);
	}
}

abstract class Model {
	private $__data = [];

	public function __construct(array $data) {
		foreach($data as $key => $value) {
			$this->__data[$key] = $value;
		}
	}

	public function __get($name) {
		return $this->__data[$name];
	}

	public function __set($name, $value) {
		$this->__data[$name] = $value;
	}
}

/**
 * @property string error
 */
class View {
	const VIEW_PATH = './view';

	protected $_controller;
	protected $_action;

	public function __construct(Controller $controller, $action) {
		$this->_controller = $controller;
		$this->_action = $action;
	}

	public function __invoke() {
		include $this->_controller->getViewsPath() . "/{$this->_action}.phtml";
	}
}

abstract class Controller {
	/** @var Controller */
	protected static $instance = null;
	protected $view;

	public static function fatalError($error, $code = 500) {
		$code = (int)$code ?: 500;
		header("HTTP/1.1 {$code} Application Error");

		$error_view = new View(static::get(), 'error');
		$error_view->error = $error;
		$error_view();

		exit;
	}

	public static function create(array $config = []) {
		static::$instance = new static($config);

		return static::$instance;
	}

	public static function get() {
		if(is_null(static::$instance)) {
			throw new Exception("Application is not running");
		}

		return static::$instance;
	}

	protected $config = [];

	public function getViewsPath() {
		return APP_ROOT . $this->config['dvcViewsPath'];
	}

	public function __construct(array $config) {
		$this->config = array_replace_recursive([
			'dvcDataPath'        => '/data',
			'dvcViewsPath'       => '/code/views',
			'dvcModelsPath'      => '/code/models',
			'dvcControllersPath' => '/code/controllers',
		], $config);
	}

	public function run($action = 'index') {
		try {
			header('Content-Type: text/html; charset=utf-8');

			ob_start();

			if(!empty($this->config['language'])) {
				Lang::load($this->config['language']);
			}

			$this->init();
			$this->route($action);

			ob_end_flush();
		} catch(\Exception $e) {
			static::fatalError($e->getMessage());
		}
	}

	protected function init() {}

	protected function route($action) {
		$action = preg_replace('#[^a-z0-9]#i', '', $action) ?: 'index';
		$method = "{$action}Action";

		if(!in_array($method, get_class_methods($this))) {
			static::fatalError("Action `{$action}` not exists", 404);
		}

		$this->view = new View($this, $action);
		$this->$method();
		$this->view->__invoke();
	}
}
