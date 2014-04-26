<?php

/**
 * Test: <struct> conversion
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$xml = '<value><struct><member><name>var</name><value></value></member></struct></value>';
$doc = new DOMDocument;
$doc->loadXML($xml);



Assert::equal( array('var' => ''), $coder->decodeValueNode($doc->documentElement));

$coder->decodeStructAsObject = TRUE;
Assert::equal( (object) array('var' => ''), $coder->decodeValueNode($doc->documentElement));



$var = array('');
$xml = '<value><array><data><value><string/></value></data></array></value>';
Assert::same($xml, $doc->saveXML($coder->encodeValueNode($doc, $var)));

$var = array(1 => '');
$xml = '<value><struct><member><name>1</name><value><string/></value></member></struct></value>';
Assert::same($xml, $doc->saveXML($coder->encodeValueNode($doc, $var)));

$var = (object) array(1 => '');
$xml = '<value><struct><member><name>1</name><value><string/></value></member></struct></value>';
Assert::same($xml, $doc->saveXML($coder->encodeValueNode($doc, $var)));

class ToArray1 {
	public $a = '';
	protected $b = '';
	private $c = '';
}
$var = new ToArray1;
$xml = '<value><struct><member><name>a</name><value><string/></value></member></struct></value>';
Assert::same($xml, $doc->saveXML($coder->encodeValueNode($doc, $var)));

class ToArray2 implements IteratorAggregate {
	private $data = array('');
	function getIterator() {
		return new ArrayIterator($this->data);
	}
}
$var = new ToArray2;
$xml = '<value><struct><member><name>0</name><value><string/></value></member></struct></value>';
Assert::same($xml, $doc->saveXML($coder->encodeValueNode($doc, $var)));
