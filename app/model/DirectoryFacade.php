<?php

namespace App;

use Nette\Object;
use Nette\Utils\Finder;

/**
 * @author Tomáš Kolinger <tomas@kolinger.name>
 */
class DirectoryFacade extends Object
{

	/**
	 * @param string $path
	 * @param string $sort
	 * @param string $sortType
	 * @param bool $ignoreHidden
	 * @return array
	 */
	public function findAll($path, $sort, $sortType, $ignoreHidden = FALSE)
	{
		if ($sortType == 'asc') {
			$sortType = ArrayHelper::SORT_ASC;
		} else {
			$sortType = ArrayHelper::SORT_DESC;
		}

		$items = Finder::find('*')->in($path);
		if ($ignoreHidden) {
			$items->exclude('.*');
		}
		$callback = callback(function ($key, $value) use ($sort) {
			/** @var \SplFileInfo $value */
			if ($sort === 'size') {
				$key = $value->isDir() ? 0 : $value->getSize();
			} else {
				if ($sort === 'date') {
					$key = $value->getCTime();
				} else {
					$key = $value->getFilename();
				}
			}
			return strtolower($key);
		});

		$items = ArrayHelper::sortByCallback(iterator_to_array($items), $callback, $sortType);
		return $items;
	}


	/**
	 * @param string $root
	 * @param string $path
	 * @return bool
	 */
	public static function isPathInJail($root, $path)
	{
		$root = realpath($root);

		$path = $root . DIRECTORY_SEPARATOR . $path;
		$path = realpath($path);

		return strpos($path, $root) === FALSE ? FALSE : TRUE;
	}


	/**
	 * @param string $object
	 */
	public static function remove($object)
	{
		if (is_dir($object)) {
			self::removeFolder($object);
		} else {
			unlink($object);
		}
	}


	/**
	 * @param string $object
	 * @link http://www.php.net/manual/en/function.rmdir.php#110489
	 */
	private static function removeFolder($object)
	{
		$files = array_diff(scandir($object), array('.', '..'));
		foreach ($files as $file) {
			$item = $object . '/' . $file;
			if (is_dir($item)) {
				self::removeFolder($item);
			} else {
				unlink($item);
			}
		}
		rmdir($object);
	}
}