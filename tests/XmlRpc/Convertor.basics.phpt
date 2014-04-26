<?php

/**
 * Test: Convertor basics
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Convertor
{
	public function createDom()
	{
		return parent::createDom();
	}
}


$mock = new Mock;
Assert::type( 'DOMDocument', $mock->createDom() );
