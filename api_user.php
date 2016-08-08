<?php
require_once (__DIR__ . '/api_fs.php');
class FS_API_USER extends FS_API {

	public function __construct($config) {
		parent::__construct($config);
	}

	public function updateUser($account, $param) {
		// $param['account'] = $account;

		$ret = $this->callApi('webservice', 'updateUser', array('account' => $account), array('param' => json_encode($param)) );

		return $ret;
	}

	public function addUser($account, $param) {
		// $param['account'] = $account;

		$ret = $this->callApi('webservice', 'addUser', array('account' => $account), array('param' => json_encode($param)) );
		return $ret;
	}

	// public function updateUser($account, $param) {
	// 	$ret = $this->callApi('webservice', 'deleteMedia', array('id' => $mediaID));
	// 	return $ret;
	// }

	public function deleteUser($account) {
		$ret = $this->callApi('webservice', 'deleteUser',  array('account' => $account));
		return $ret;
	}

}
