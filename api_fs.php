<?php

define('_DIR',      0);
define('_FOLDER',   1);


class FS_API {

	protected $_DEBUG = TRUE;
	protected $_cfg_SERVER_URL;
	protected $_cfg_ACCOUNT;
	protected $_cfg_PASSWORD;

	protected $_curl_ch = NULL;

	protected $_cookie_file;
	protected $_login;
	protected $_COOKIE;
	protected $_err_msg;
	protected $_verbose;
	protected $_tmp_folder;
	protected $_req_timeout;
	
	public function __construct($config) {
		// set config
		foreach($config as $k => $v) {
			$name = "_cfg_{$k}";
			if (property_exists(get_class($this), $name)) {
				$this->$name = $v;
			}
		}

		$this->_tmp_folder = sys_get_temp_dir();
		@mkdir($this->_tmp_folder, 0777, TRUE);
		$info = parse_url($this->_cfg_SERVER_URL);
		$this->_req_timeout = 3600;
		$this->_cookie_file = $this->_tmp_folder . DIRECTORY_SEPARATOR . $info['host'] . '-' . $this->_cfg_ACCOUNT . '.cookie';
	}

	public function login() {

		// set cookie file
		// init cookie / session
		foreach (range(0, 5) as $i) {
			$this->request($this->_cfg_SERVER_URL . '/index/ping', array(), array(CURLOPT_HEADER => TRUE, CURLINFO_HEADER_OUT => TRUE, CURLOPT_NOBODY => TRUE));
			$hdr = curl_getinfo($this->_curl_ch, CURLINFO_HEADER_OUT);

			$hdr_ary = explode("\r\n", $hdr);
			// parse cookie
			foreach($hdr_ary as $h) {
				list($name, $val) = explode(':', $h, 2);
				if ($name == '') continue;
				

				if ($name == 'Cookie') {
					
					$items = explode(';', $val);

					foreach($items as $itm) {
						list($c_name, $c_val) = explode('=', $itm, 2);
						$this->_COOKIE[trim($c_name)] = trim($c_val);
					}
				}
			}

			if (isset($this->_COOKIE)) break;
		}


		// get Login / logout url
		$this->_login = $this->callApi('webservice', 'getLogin');

		if (!$this->_login) {
			$this->_err_msg = 'webservice->getLogin fail: ' . $this->_login;
			return FALSE;
		}

		// login
        // ref: this.password.value =  md5(md5(md5(this.password.value)) + '|' + $.cookie('_login_token_'))
		$req = array();
		$req['account'] = $this->_cfg_ACCOUNT;

		if ($this->_login['auth_account_use'] == 'local') {
			$req['password'] = md5( md5(md5($this->_cfg_PASSWORD)) . '|' . $this->_COOKIE['_login_token_'] );
		} else {
			$req['password'] = $this->_cfg_PASSWORD;			
		}


		$result = $this->request($this->_cfg_SERVER_URL . $this->_login['login-url'], $req);
		
		if ($result['ret']['status'] == 'true') {
			return TRUE;
		}

		$this->_err_msg = $result['ret']['msg'];
		return FALSE;
	}


	public function callApi($app, $action, $args = array(), $post = array(), $debug = FALSE) {

		// http://site/api/sys.pages.media/page.action/args/
		$apiUrl = $this->getApiUrl($this->_cfg_SERVER_URL . "/api/sys.app.api_{$app}/{$action}", $args);

		if ($debug) {
			echo PHP_EOL . $apiUrl . PHP_EOL;
			echo PHP_EOL . var_dump($post) . PHP_EOL;
			exit();
		}
		return $this->request($apiUrl, $post);
	}

	public function request($url, $post=array(), $param=array()) {

		if ($this->_curl_ch) { curl_close($this->_curl_ch); }
		$this->_curl_ch = curl_init();

		curl_setopt($this->_curl_ch, CURLOPT_URL, $url);
		$default = array(
			CURLOPT_SSL_VERIFYPEER  => 0,
			CURLOPT_SSL_VERIFYHOST  => 0,
			CURLOPT_FOLLOWLOCATION	=> 1,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_TIMEOUT			=> $this->_req_timeout
		);

		// if has post 
		if (count($post)>0) {
			curl_setopt($this->_curl_ch, CURLOPT_POST, true);
			curl_setopt($this->_curl_ch, CURLOPT_POSTFIELDS, $post);
		}

		// set cookie file
		curl_setopt($this->_curl_ch, CURLOPT_COOKIEFILE, $this->_cookie_file); 
		curl_setopt($this->_curl_ch, CURLOPT_COOKIEJAR, $this->_cookie_file); 

		$param += $default;
		foreach ($param as $idx=>$val) @ curl_setopt($this->_curl_ch, $idx, $val);


		if ($this->_DEBUG) {
			//curl_setopt($this->_curl_ch, CURLOPT_VERBOSE, true);
			//$verbose = fopen('php://temp', 'w+');
			//curl_setopt($this->_curl_ch, CURLOPT_STDERR, $verbose);
		}

		$data = curl_exec($this->_curl_ch);
   

		if ($data === false) {
			$this->_err_msg = curl_error($this->_curl_ch);
			$this->print_var($this->_err_msg, 'Curl error: ');
			$this->print_var($url, 'url');
			$this->print_var($post, 'post');
			$this->print_var($param, 'param');
		}

		if ($this->_DEBUG) {
			//rewind($verbose);
			//$this->_verbose = stream_get_contents($verbose);
		}


    	return json_decode($data, TRUE);
	}



	public function printVerbose() {
		$this->print_var($this->_verbose);
	}

    public function __destruct() {

    	// logout
    	if ($this->_login['logout-url']) {
    		$this->request($this->_cfg_SERVER_URL . $this->_login['logout-url']);
    	}

		if ($this->_curl_ch) { curl_close($this->_curl_ch); }
    	
    }

    public function getLastError() {
    	return $this->_err_msg;
    }



	public function print_var($data, $title="") {
		$caller = array_shift(debug_backtrace());	

		$info = "file: {$caller['file']} line: {$caller['line']} ";
        echo <<<DIV
		<div style='text-align:left; margin:10px; border:1px solid #f00; padding:5px; background:#ffc; z-index:99999; position:relative; max-height:500px; overflow: auto'>
			<i>{$info}</i><BR /><b>$title</b>
DIV;
            echo "  <i>(type: " . gettype($data) . ")</i>";
            echo "<pre>";
                print_r($data);
            echo "</pre>";
        echo "</div>";

    }

    // http://site/api/sys.pages.media/page.action/args/
	public function getApiUrl($url, $vals=array()) {
		foreach($vals as &$v) {
			if (is_null($v)) $v = '';
		}

		if (substr($url, -1) !== '/') $url .= '/';

		$qry = $vals;
		if (count($vals)>0) $qry['_lock']  	 = join(',', array_keys($vals) );
		$qry['_auth'] = md5( join('|', array_merge( (array)$url, (array)$vals) ) );

		$concatStr = ( strpos($url, '?') === FALSE) ? "?" : "&";
		return $url . $concatStr . http_build_query($qry);
	}

    public function curl_file_create($filename, $mimetype = '', $postname = '') {
	        return "@{$filename};filename="
	            . ($postname ? $postname : basename($filename))
	            . ($mimetype ? ";type=$mimetype" : '');
	}

	public function RealFileSize($file)
	{
		$fp = fopen($file, 'rb');
	    $pos = 0;
	    $size = 1073741824;
	    fseek($fp, 0, SEEK_SET);
	    while ($size > 1)
	    {
	        fseek($fp, $size, SEEK_CUR);

	        if (fgetc($fp) === false)
	        {
	            fseek($fp, -$size, SEEK_CUR);
	            $size = (int)($size / 2);
	        }
	        else
	        {
	            fseek($fp, -1, SEEK_CUR);
	            $pos += $size;
	        }
	    }

	    while (fgetc($fp) !== false)  $pos++;

	    fclose($fp);
	    return $pos;
	}


	public function getFolders() {

		// get Login / logout url
		return $this->callApi('webservice', 'getFolders');

	}

 	protected function filelog($text) {
		$caller = array_shift(debug_backtrace());

		$info = "file: {$caller['file']} line: {$caller['line']} var type: " . gettype($text);

		if (is_array($text) || is_object($text)) $text = print_r($text, TRUE);
        $fp = fopen("log.txt", "a");
        fwrite($fp, "[{$info}] " . date("Y-m-d H:i:s") . ":\n{$text}\n");
        fclose($fp);
    }


    public function setTempFolder($folder) {
    	$this->_tmp_folder = $folder;
    }

    public function setReqTimeout($time) {
    	$this->_req_timeout = $time;
    }
    
}
