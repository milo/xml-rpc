<?php

declare(strict_types=1);

if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
	echo "Tester not found. Install Nette Tester using `composer update`.\n";
	exit(1);
}
include __DIR__ . '/../vendor/autoload.php';


Tester\Environment::setup();
date_default_timezone_set('UTC');


function test(\Closure $cb)
{
	$cb();
}


function assertValueElement($xml)
{
	$doc = new DOMDocument;
	$doc->loadXml("
		<methodCall>
			<methodName>assert.validXmlValue</methodName>
			<params>
				<param>
					$xml
				</param>
			</params>
		</methodCall>
	");

	if (!$doc->relaxNGValidate(__DIR__ . '/../src/XmlRpc/xml-rpc.rng')) {
		throw new LogicException('XML source is not valid to XML-RPC schema.');
	}
}
