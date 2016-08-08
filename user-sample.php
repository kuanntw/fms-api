<?php
require_once('api_user.php');

/////////////////////////////////////////////////
// config
$config = array();
$config['SERVER_URL'] = 'http://tms';
$config['ACCOUNT'] = 'admin';
$config['PASSWORD'] = 'powercam';


/////////////////////////////////////////////////
$fs_user = new FS_API_USER($config);

if (!$fs_user->login()) {
	echo "<BR />\r\nLogin fail: " . $fs_user->getLastError();
	exit;
}
echo "Login as {$config['ACCOUNT']} success <br />\r\n";



$ret = $fs_user->deleteUser('kuann1111457939230');
print_r($ret);
exit;


$param = array();
$param['name'] = 'name' . time();
$param['lastname'] = 'lastname ' . time();
$param['email'] = 'kuanntw+' . time() . '@gmail.com';

$ret = $fs_user->addUser('kuann111' . time(), $param);

print_r($ret);