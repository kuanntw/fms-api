<?php
require_once('api_user.php');

/////////////////////////////////////////////////
// config
$config = array();
$config['SERVER_URL'] = 'http://tms';
$config['ACCOUNT'] = 'admin';
$config['PASSWORD'] = '1234';


/////////////////////////////////////////////////
$fs_user = new FS_API_USER($config);

// initial (optional)


if (!$fs_user->login()) {
	echo "<BR />\r\nLogin fail: " . $fs_user->getLastError();
	exit;
}
echo "Login as {$config['ACCOUNT']} success <br />\r\n";


$csv = file_get_contents('10000users.csv');

$line = explode(PHP_EOL, $csv);

array_shift($line); // remove header

foreach ($line as $l) {

	$d = explode(',', $l);

	$account = $d[0];

	$param = array();
	$param['password'] = $d[1];
	$param['lastname'] = $d[2];
	$param['name'] = $d[3];
	$param['email'] = $d[4];
	$param['division'] = "{$d[5]}:{$d[6]}";
	$param['role'] = "{$d[7]}";
	$param['phone'] = "{$d[8]}";
	$param['takeDate'] = "{$d[9]}";
	$param['leaveDate'] = "{$d[10]}";
	$param['gender'] = "{$d[12]}";
	$param['remote'] = "{$d[13]}";
	$param['addr'] = "{$d[14]}";

// print_r($d) . "<br>";
// echo $account . "<br>";
// print_r($param) . "<br>";

	$ret = $fs_user->updateUser($account, $param);
	print_r($ret);

}