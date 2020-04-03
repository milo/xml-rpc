<?php

declare(strict_types=1);

/**
 * Test: recursion
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;



Assert::exception(function() use ($coder, $doc) {
	$var = [];
	$var[0] = & $var;

	$coder->encodeValueNode($doc, $var);
}, Milo\XmlRpc\CoderException::class, 'Nesting level too deep or recursive dependency. Try to increase Milo\XmlRpc\Coder::$maxEncodeDepth');
