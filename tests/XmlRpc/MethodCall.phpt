<?php

/**
 * Test: MethodCall
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';

$convertor = new Milo\XmlRpc\Convertor;


# From XML
$methodCall = $convertor->fromXml(file_get_contents(__DIR__ . '/files/MethodCall.xml'));

Assert::type( 'Milo\XmlRpc\MethodCall', $methodCall );
Assert::same( 'namespace.myMethod', $methodCall->getName() );
Assert::same( array(''), $methodCall->getParameters() );
Assert::same( 1, $methodCall->getParameterCount() );


# To XML
$methodCall = new Milo\XmlRpc\MethodCall('mc');
Assert::same( '<methodCall><methodName>mc</methodName></methodCall>', $convertor->toXml($methodCall) );

$methodCall = new Milo\XmlRpc\MethodCall('mc', array(1));
Assert::same( '<methodCall><methodName>mc</methodName><params><param><value><int>1</int></value></param></params></methodCall>', $convertor->toXml($methodCall) );
