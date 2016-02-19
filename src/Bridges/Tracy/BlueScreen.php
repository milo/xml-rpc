<?php

namespace Milo\XmlRpc\Bridges\Tracy;


class BlueScreen
{

	/**
	 * @return string[]
	 */
	public static function getCollapsePaths()
	{
		return [
			realpath(__DIR__ . '/../../XmlRpc/Strict.php'),
			realpath(__DIR__ . '/../../XmlRpc/ValueValidator.php'),
		];
	}

}
