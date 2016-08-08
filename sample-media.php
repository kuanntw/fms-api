<?php
require_once('api_uploader.php');
require_once('api_media.php');

/////////////////////////////////////////////////
// config
$config = array();
$config['SERVER_URL'] = 'http://tms';
$config['ACCOUNT'] = 'admin';
$config['PASSWORD'] = 'powercam';


/////////////////////////////////////////////////
$uploader = new FS_API_UPLOADER($config);
$fs_media = new FS_API_MEDIA($config);

// initial (optional)
$uploader->setChunkSize( 1024 * 1024 * 128); // default chunk size is 10MB


if (!$uploader->login()) {
	echo "<BR />\r\nLogin fail: " . $uploader->getLastError();
	exit;
}
echo "Login as {$config['ACCOUNT']} success <br />\r\n";


/////////////////////////////////////////////////
// echo "getFolders <br />\r\n";
// $folders = $uploader->getFolders();
// $targetFolder = current($folders);

// foreach($folders as $folder) {
// 	echo "{$folder['id']} : {$folder['name']} <BR />\r\n";
// }
// die();
/////////////////////////////////////////////////

///////////////////////////////////////////////
echo "getDownloadInfo() <br />\r\n";
$info = $fs_media->getDownloadInfo(456);
print_r($info);
$info = $fs_media->getDownloadInfo(457);
print_r($info);
$info = $fs_media->getDownloadInfo(458);
print_r($info);
$info = $fs_media->getDownloadInfo(459);
print_r($info);
$info = $fs_media->getDownloadInfo(460);
print_r($info);

echo "getPackageInfo() <br />\r\n";
$info = $fs_media->getPackageInfo(456);
print_r($info);


die();

// file type: 'video', 'ecm', 'audio', 'pdf', 'html', 'ppt'

$files = array();
$files['ecm'] = realpath("1.ecm");
// $files['ecm'] = realpath("2.ecm");
// $files['video'] = realpath("3.mp4");
// $files['ppt'] = realpath("4.pptx");
// $files['pdf'] = realpath("5.pdf");
// $files['video'] = realpath("E:/4hour.mp4");


echo "Upload to {$targetFolder['id']} : {$targetFolder['name']} <BR />\r\n";
$ret = $uploader->UploadFiles('folder.' . $targetFolder['id'], $files);

foreach($ret as $k => $v) {
	if ($v['url'] != '') {

		print_r($v);
		echo "Upload {$k} ok > {$v['url']} <BR />\r\n";

		$mediaPath = explode('/', $v['url']);
		$mediaID = $mediaPath[2];
		
		// update title
		$param = array();
		$param['title'] = 'api uploader: ' . rand();
		$param['notifyURL'] = 'http://tms/abc/?id=%id%&size=%size%&err=%err%';
		$fs_media->saveMedia($mediaID, $param);
		$info = $fs_media->getMediaShareInfo($mediaID, array('shorten' => 1));
		var_dump($info);

	} else {
		echo "Upload {$k} fail > {$v['error']} <BR />\r\n";
	}
}


// *****************************************
// delete a media via mediaID
// $ret = $fs_media->deleteMedia(3693);
// print_r($ret);

// *****************************************
/// overwrite to a new media
// $ret = $uploader->overwriteMedia(3694, realpath("2.ecm"), 'video');
// print_r($ret);
