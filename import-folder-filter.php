<?php
function dataFilter($ary) {

	$ary = array_filter($ary);
	$ary['resType'] = ($ary['resType'] == 'DIR') ? _DIR : _FOLDER;
	if ($ary['read-priv']) {
		list($priv, $users) = explode('|', $ary['read-priv']);
		$ary['priv'] = $priv;
		unset($ary['read-priv']);
	}


	if (empty($ary['parentID'])) {
		$ary['parentID'] = 3;
	}

	// not implemented
	unset($ary['tags']);
	unset($ary['cat']);
	unset($ary['photo']);
	unset($ary['admin']);
	unset($ary['upload-priv']);
	unset($ary['parent']);

	unset($ary['titleType']);
	
	$ary['uploadComplete'] = 1;


	return $ary;
}
