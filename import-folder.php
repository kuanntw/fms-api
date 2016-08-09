<?php
require_once('api_folder.php');
require_once('import-folder-filter.php');
require_once(__DIR__ . '/vendor/autoload.php');

/////////////////////////////////////////////////
// config
$config = array();
$config['SERVER_URL'] = 'http://tms';
$config['ACCOUNT'] = 'admin';
$config['PASSWORD'] = '123456';


/////////////////////////////////////////////////
$fs_folder = new FS_API_FOLDER($config);


	if (!$fs_folder->login()) {
		echo "<BR />\r\nLogin fail: " . $fs_folder->getLastError();
		exit;
	}
	echo "Login as {$config['ACCOUNT']} success <br />\r\n";



	// load excel file
	$inputFileName = 'folder-sample.xlsx';
	try {
	    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
	    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
	    $objPHPExcel = $objReader->load($inputFileName);
	} catch(Exception $e) {
	    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
	}


	$sheet = $objPHPExcel->getSheet(0); 
	$highestRow = $sheet->getHighestRow(); 
	$highestColumn = $sheet->getHighestColumn();

	if ($highestRow <= 2) {
		die('No data exists in the ' . $inputFileName);
	}

	$header = array_shift($sheet->rangeToArray('A2:' . $highestColumn . '2', NULL, TRUE, FALSE));

	$data = array();
	// skip first two lines for description and header
	$data = $sheet->rangeToArray('A3:' . $highestColumn . $highestRow, NULL, TRUE, FALSE);

	////////////////////////////////
	$param = array();
	foreach ($data as $idx => $value) {
		$param[$idx] = array_combine($header, $value);


		// query parentID
		if (!empty($param[$idx]['parent']) && !is_numeric($param[$idx]['parent'])) {

			$ret = $fs_folder->queryFolder($param[$idx]['parent']);

			$parents = ($ret['status']) ? json_decode($ret['msg'], TRUE) : array();

			foreach ($parents as $p) {
				if($p['resType'] == _DIR) {
					$param[$idx]['parentID'] = $first['id'];
					break;
				}
			}

		}
	}

    $param = array_map('dataFilter', $param);


	foreach ($param as $p) {
		$id = isset($p['id']) ? $p['id'] : 0;

		echo "Create folder: {$id} - {$p['name']} <br />" . PHP_EOL;
		$ret = $fs_folder->saveFolder($id, $p);

		var_dump($ret);
	}
