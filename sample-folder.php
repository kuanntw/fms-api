<?php
require_once('api_folder.php');

/////////////////////////////////////////////////
// config
$config = array();
$config['SERVER_URL'] = 'http://tms';
$config['ACCOUNT'] = 'admin';
$config['PASSWORD'] = '1234';


/////////////////////////////////////////////////
$fs_folder = new FS_API_FOLDER($config);


if (!$fs_folder->login()) {
	echo "<BR />\r\nLogin fail: " . $fs_folder->getLastError();
	exit;
}
echo "Login as {$config['ACCOUNT']} success <br />\r\n";


$param = array();
$param['resType'] = _FOLDER;
$param['name'] = 'api-folder-' . time();
$param['priv'] = 3;

$ret = $fs_folder->saveFolder(0, $param);
var_dump($ret);

$ret = $fs_folder->deleteFolder(2070);
var_dump($ret);