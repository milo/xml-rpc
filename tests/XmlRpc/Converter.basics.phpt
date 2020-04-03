<?php

declare(strict_types=1);

/**
 * Test: Converter basics
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Converter
{
	public function createDom(): \DOMDocument
	{
		return parent::createDom();
	}
}


$mock = new Mock;
Assert::type(DOMDocument::class, $mock->createDom());
Assert::type(Milo\XmlRpc\Coder::class, $mock->getCoder());

$coder = new Milo\XmlRpc\Coder;
$converter = new Milo\XmlRpc\Converter($coder);
Assert::same($coder, $converter->getCoder());
