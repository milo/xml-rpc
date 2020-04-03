<?php

declare(strict_types=1);

/**
 * Test: IValueConvertible conversion
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;


class Value1 implements Milo\XmlRpc\IValueConvertible
{
	public function getXmlRpcValue()
	{
		return [231];
	}
}

$var = new Value1;
Assert::same(
	'<value><array><data><value><int>231</int></value></data></array></value>',
	$doc->saveXML($coder->encodeValueNode($doc, $var))
);
