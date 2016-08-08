<?php
require_once (__DIR__ . '/api_fs.php');
class FS_API_UPLOADER extends FS_API {

	const DEF_CHUNK_SIZE = 10485760; // 1024 * 1024 * 10

	protected $_uploader_urls = array();

	protected $_chunk_size;
	


	public function __construct($config) {
		parent::__construct($config);
        
		$this->_chunk_size = FS_API_UPLOADER::DEF_CHUNK_SIZE;

	}

	protected function _postFile($postUrl, $file) {

		// process chunked request
		$residual = $filesize = $this->RealFileSize($file);

		$src = fopen($file, 'r');
		$bytes_start = $bytes_end = 0;
		while($bytes_end < $filesize) {

			$post = $param = array();
			$bytes_end += ($this->_chunk_size);

			if ($bytes_end > $filesize) $bytes_end = $filesize;

			$temp_file_name = tempnam($this->_tmp_folder, 'api');

			$dest = fopen($temp_file_name, 'w');

			$range = "Content-Range: bytes {$bytes_start}-" . ($bytes_end-1) . "/{$filesize}";
			$param[CURLOPT_HTTPHEADER] = array($range);

			// prepare a temp file
			stream_copy_to_stream($src, $dest, $this->_chunk_size);
			fclose($dest);
            if (phpversion() >= "5.3.0") {
                clearstatcache(TRUE, $temp_file_name);
            } else {
                clearstatcache();
            }
			
			$post['files'] = "@{$temp_file_name};filename=" . basename($file) . ";type=application/octet-stream";

			$result = $this->request($this->_cfg_SERVER_URL . $postUrl, $post, $param);
			@unlink($temp_file_name);

			$bytes_start = $bytes_end;

			if ($result['files'][0]['error']) {
				$this->_err_msg = $result['files'][0][error];
// var_dump($this->_err_msg);
				return $this->_err_msg;
			}

		}

		return $result['files'][0];
	}

	public function UploadFiles($pageID, $files) {
		$urls = $this->prepUploaderUrl($pageID);

		if (!$urls) {
			$this->_err_msg = 'No permission';
			return array('error' => 'No permission');
		}

		$ret = array();

		foreach($files as $ftype => $file) {

			if ($ftype == '') $ftype = 'video';
			$postUrl = $urls[$ftype]['url'];

			if ($file == '' or !file_exists($file)) {
				$ret[$file] = array('error' => 'wrong file / file not exists');
				continue;
			}

			if ($postUrl == '') {
				$ret[$file] = array('error' => 'wrong file type');
				continue;
			}

			$r = $this->_postFile($postUrl, $file);
			if ($this->_err_msg) {

// var_dump($this->_err_msg);
				$ret[$file] = array('error' => $this->_err_msg);
				continue;
			}

			$ret[$file] = $r;
		}

		return $ret;
	}

	private $_prepUploaderUrl = array();
	private function prepUploaderUrl($pageID) {

		if (isset($this->_prepUploaderUrl[$pageID])) return $this->_prepUploaderUrl[$pageID];
		// get Login / logout url
		$this->_prepUploaderUrl[$pageID] = $this->callApi('webservice', 'getUploadUrl', array('pageID' => $pageID));
		return $this->_prepUploaderUrl[$pageID];
	}


	public function overwriteMedia($id, $file = '', $playtype = 'video') {

		if ($file == '') {
			return $this->callApi('webservice', 'removeMediaVideo', array('id' => $id));
		}

		$url_prop = $this->callApi('webservice', 'getReUploadUrl', array('id' => $id, 'playtype' => $playtype));
		$ret = $this->_postFile($url_prop['link'], $file);

		return $ret;

	}

    public function setChunkSize($size) {
    	$this->_chunk_size = $size;
    }


}
