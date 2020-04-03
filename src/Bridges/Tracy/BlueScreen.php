<?php

declare(strict_types=1);

namespace Milo\XmlRpc\Bridges\Tracy;


class BlueScreen
{
	public static function getCollapsePaths(): array
	{
		return [
			realpath(__DIR__ . '/../../XmlRpc/Strict.php'),
			realpath(__DIR__ . '/../../XmlRpc/ValueValidator.php'),
		];
	}
}
