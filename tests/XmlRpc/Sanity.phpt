<?php

declare(strict_types=1);

/**
 * Test: Sanity checks
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



class Mock
{
	use Milo\XmlRpc\Strict;
}

$mock = new Mock;



Assert::exception(function() use ($mock) {
	$mock->undefined;
}, 'LogicException', 'Cannot read an undeclared property Mock::$undefined.');

Assert::exception(function() use ($mock) {
	$mock->undefined = '';
}, 'LogicException', 'Cannot write to an undeclared property Mock::$undefined.');
