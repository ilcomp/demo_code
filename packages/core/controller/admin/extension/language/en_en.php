<?php
namespace Controller\Extension\Language;

class EnEn extends \Controller {
	public function index() {
		if ($str) {
			$fromChars = ['&'];
			$toChars   = ['-and-'];

			$str = mb_strtolower(preg_replace('/[_\s\.,?!\[\](){}\/"\':;\\\\]+/', '-', $str));

			$str = str_replace($fromChars, $toChars, $str);

			$str = preg_replace('/^-+|-+$/', '-', preg_replace('/-{2,}/', '-', preg_replace(array('/j{2,}/', '/[^\w_-]/'), array('j', ''), $str)));
		}

		return $str;
	}
}