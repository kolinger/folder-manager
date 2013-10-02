<?php

namespace App;

use Nette\Callback;
use Nette\Object;

/**
 * @author Tomáš Kolinger <tomas@kolinger.name>
 */
class ArrayHelper extends Object
{

	const SORT_ASC = 0;
	const SORT_DESC = 1;


	/**
	 * @param array $array
	 * @param \Nette\Callback $callback
	 * @param int $sort
	 * @return array
	 */
	public static function sortByCallback(array $array, Callback $callback, $sort = self::SORT_ASC)
	{
		$temp = array();
		foreach ($array as $key => $item) {
			if (!isset($temp[$callback($key, $item)])) {
				$temp[$callback($key, $item)] = array();
			}
			$temp[$callback($key, $item)][] = $item;
		}

		if ($sort == self::SORT_ASC) {
			ksort($temp);
		} else {
			krsort($temp);
		}

		$output = array();
		foreach ($temp as $items) {
			foreach ($items as $item) {
				$output[] = $item;
			}
		}

		return $output;
	}
}