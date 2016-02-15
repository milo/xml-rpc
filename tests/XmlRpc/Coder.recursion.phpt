<?php

/**
 * Test: recursion
 *
 * @author  Miloslav Hůla
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;



Assert::exception(function() use ($coder, $doc) {
	$var = [];
	$var[0] = & $var;

	$coder->encodeValueNode($doc, $var);
}, 'InvalidArgumentException', 'Nesting level too deep or recursive dependency. Try to increase Milo\XmlRpc\Coder::$maxEncodeDepth');
