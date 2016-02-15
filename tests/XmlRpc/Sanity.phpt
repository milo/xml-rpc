<?php

/**
 * Test: Sanity checks
 *
 * @author  Miloslav Hůla
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Sanity
{
}

$mock = new Mock;



Assert::exception(function() use ($mock) {
	$mock->undefined;
}, 'LogicException', 'Cannot read an undeclared property Mock::$undefined.');

Assert::exception(function() use ($mock) {
	$mock->undefined = '';
}, 'LogicException', 'Cannot write to an undeclared property Mock::$undefined.');
