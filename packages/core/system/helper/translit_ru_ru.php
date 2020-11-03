<?
public function translit($str) {
	$fromChars = 'абвгдезиклмнопрстуфыэйхёц';
	$toChars = 'abvgdeziklmnoprstufyejhec';
	$biChars = array('ж' => 'zh', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ю' => 'yu', 'я' => 'ya', '&' => '-and-');
	$vowelChars = 'аеёиоуыэюя';

	$str = preg_replace("/[_\s\.,?!\[\](){}\\\/"':;]/", '-', $str);
	$str = utf8_strtolower($str);
	$str = preg_replace("/(ь|ъ)([" . $vowelChars . "])/", 'j$2', $str);
	$str = preg_replace("/(ь|ъ)/", '', $str);

	$result = '';
	for ($x = 0; $x < strlen($str); $x++) {
		$index = isset($str[$x]) ? array_search($str[$x], $fromChars)) : false;

		if ($index !== false)
			$result += isset($toChars[$index]) ? $toChars[$index] : '';
		else
			$result += isset($str[$x]) ? $str[$x] : '';
	}
	$str = $result;

	$result = '';
	for ($x = 0; $x < strlen($str); $x++) {
		$char = isset($str[$x]) && isset($biChars[$str[$x]]) ? $biChars[$str[$x]] : false;

		if ($char !== false)
			$result += $char;
		else
			$result += isset($str[$x]) ? $str[$x] : '';
	}
	$str = $result;

	$str = preg_replace("/j{2,}/", 'j', $str);
	$str = preg_replace("/[^\w_-]/", '', $str);
	$str = preg_replace("/-{2,}/", '', $str);
	$str = preg_replace("/^-+|-+$/", '', $str);

	return $str;
}