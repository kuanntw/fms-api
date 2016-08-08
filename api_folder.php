<?php
require_once (__DIR__ . '/api_fs.php');
class FS_API_FOLDER extends FS_API {

	public function __construct($config) {
		parent::__construct($config);
	}

	public function saveFolder($id, $param = array()) {

		$ret = $this->callApi('webservice', 'saveFolder', array_merge(array('id' => $id), $param));
		return $ret;
	}

	public function deleteFolder($id) {
		$ret = $this->callApi('webservice', 'deleteFolder', array('id' => $id));
		return $ret;
	}


}
