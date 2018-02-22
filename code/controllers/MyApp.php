<?php
class MyApp extends \DVC\Controller {
	/** @var MyModel */
	protected $my_model;

	/** @var MyModelSource */
	protected $my_source;

	protected function init() {
		$this->my_source = new MyModelSource;
		$this->my_model = $this->my_source->getByKey($this->config['my_model_db_key']);
	}

	protected function indexAction() {
		$this->view->model = $this->my_model;
	}
}