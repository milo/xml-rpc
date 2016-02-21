<?php

/**
 * Test: Helpers
 */

use Tester\Assert;
use Milo\XmlRpc\Helpers;

require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	new Helpers;
}, 'Milo\XmlRpc\LogicException', 'Class Milo\XmlRpc\Helpers is static and cannot be instantized.');