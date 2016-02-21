<?php

/**
 * Test: Converter basics
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Converter
{
	public function createDom()
	{
		return parent::createDom();
	}
}


$mock = new Mock;
Assert::type('DOMDocument', $mock->createDom());
Assert::type('Milo\XmlRpc\Coder', $mock->getCoder());

$coder = new Milo\XmlRpc\Coder;
$converter = new Milo\XmlRpc\Converter($coder);
Assert::same($coder, $converter->getCoder());
