<?php
class MyModel extends \DVC\Model {}

class MyModelSource extends \DVC\Source {
	public function __construct() {
		return parent::__construct("common/my_model"); // path to JSON file in /data folder
	}

	protected function prepareModel($key, $data) {
		$data['name'] = trim($data['name']);

		// Example collection loading
		// $data['some_collection'] = \DVC\loadDatabase("some_collections/{$key}");

		return new MyModel($data);
	}
}