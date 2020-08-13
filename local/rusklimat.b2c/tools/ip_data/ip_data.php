<?

$file1 = __DIR__.'/cidr_optim.txt';
$file2 = __DIR__.'/cities.txt';
$file1utf = __DIR__.'/cidr_optim_utf.txt';
$file2utf = __DIR__.'/cities_utf.txt';
$file1utf_data = __DIR__.'/cidr_optim_data.php';
$file2utf_data = __DIR__.'/cities_data.php';

// setlocale(LC_ALL, 'ru_RU.UTF-8');

if (!file_exists($file1utf)) {			
	$file_data = file_get_contents($file1);
	// if (!is_utf8($file_data)) {
		$file_data = iconv("CP1251//TRANSLIT//IGNORE", "UTF-8//TRANSLIT//IGNORE", $file_data);
		file_put_contents($file1utf, $file_data);
	// }
	unset($file_data);
}

if (!file_exists($file1utf_data)) {
	$file_data = file_get_contents($file1utf);
	$arr = explode("\n", $file_data);
	foreach($arr as $i=>$data) {
		$arr[$i] = explode("\t", $data);
	}
	// $cidr_optim = $arr;
	$cidr_optim = [];
	foreach($arr as $i=>$data) {
		if ($data[3] != 'RU' || $data[4] == '-') {
			continue;
		}
		$data[0] = intval($data[0]);
		$data[1] = intval($data[1]);
		if ($data[4] != '-') {
			$data[4] = intval($data[4]);
		}
		unset($data[2]);
		unset($data[3]);
		$cidr_optim[] = $data;
	}
	unset($arr);
	file_put_contents($file1utf_data, '<? $cidr_optim = '.var_export($cidr_optim, 1).';');
} else {
	include($file1utf_data);
}

if (!file_exists($file2utf)) {	
	$file_data = file_get_contents($file2);
	// if (!is_utf8($file_data)) {
		$file_data = iconv("CP1251//TRANSLIT//IGNORE", "UTF-8//TRANSLIT//IGNORE", $file_data);
		file_put_contents($file2utf, $file_data);
	// }
	unset($file_data);
}

if (!file_exists($file2utf_data)) {
	$file_data = file_get_contents($file2utf);
	$arr = explode("\n", $file_data);
	foreach($arr as $i=>$data) {
		$arr[$i] = explode("\t", $data);
	}
	// $cities = $arr;
	$cities = [];
	foreach($arr as $i=>$data) {
		$data[0] = intval($data[0]);
		$cities[$data[0]] = $data;
	}
	unset($arr);
	file_put_contents($file2utf_data, '<? $cities = '.var_export($cities, 1).';');
} else {
	include($file2utf_data);
}