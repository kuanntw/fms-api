<?php
require_once (__DIR__ . '/api_fs.php');
class FS_API_MEDIA extends FS_API {

	public function __construct($config) {
		parent::__construct($config);
	}

	public function saveMedia($mediaID, $param = array()) {
		$ret = $this->callApi('webservice', 'saveMedia', array_merge(array('id' => $mediaID), $param));
		return $ret;
	}

	public function deleteMedia($mediaID) {
		$ret = $this->callApi('webservice', 'deleteMedia', array('id' => $mediaID));
		return $ret;
	}

	public function getMediaShareInfo($mediaID, $param = array()) {
		$ret = $this->callApi('webservice', 'getMediaShareInfo', array_merge(array('id' => $mediaID), $param));
		return $ret;
	}

	public function getDownloadInfo($mediaID, $param = array()) {
		$ret = $this->callApi('webservice', 'getDownloadInfo', array_merge(array('id' => $mediaID), $param));
		return $ret;
	}

	public function getPackageInfo($mediaID, $param = array()) {
		$ret = $this->callApi('webservice', 'getPackageInfo', array_merge(array('id' => $mediaID), $param), array());
		return $ret;
	}

}
