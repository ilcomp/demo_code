<?php
namespace Controller\Extension\Language;

class RuRu extends \Controller {
	public function index($str) {
		if ($str) {
			$fromChars = ['а','б','в','г','д','е','з','и','к','л','м','н','о','п','р','с','т','у','ф','ы','э','й','х','ё','ц','ж','ч','ш','щ','ю','я','&'];
			$toChars   = ['a','b','v','g','d','e','z','i','k','l','m','n','o','p','r','s','t','u','f','y','e','j','h','e','c','zh','ch','sh','sch','yu','ya','-and-'];

			$str = preg_replace('/(ь|ъ)/u', '', preg_replace('/(ь|ъ)аеёиоуыэюя/u', 'j$2', mb_strtolower(preg_replace('/[_\s\.,?!\[\](){}\/"\':;\\\\]+/', '-', $str))));

			$str = str_replace($fromChars, $toChars, $str);

			$str = preg_replace('/^-+|-+$/', '', preg_replace('/-{2,}/', '-', preg_replace(array('/j{2,}/', '/[^\w_-]/'), array('j', ''), $str)));
		}

		return $str;
	}
}