<?php

declare(strict_types=1);

/**
 * Test: An <array> without data.
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$cases = [
	'<value> <array/> </value>',
	'<value> <array> </array> </value>',
	'<value> <array> <data/> </array> </value>',
	'<value> <array> <data> </data> </array> </value>',
];

foreach ($cases as $key => $xml) {
	assertValueElement($xml);
	$coder = new Milo\XmlRpc\Coder;
	$doc = new DOMDocument;
	$doc->preserveWhiteSpace = false;
	$doc->loadXML($xml);

	Assert::equal([], $coder->decodeValueNode($doc->documentElement), "Case: $key");
}
